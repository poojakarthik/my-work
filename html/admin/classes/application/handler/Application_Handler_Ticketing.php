<?php

class Application_Handler_Ticketing extends Application_Handler
{
	// Handle a request for the home page of the ticketing system
	public function System($subPath)
	{
		return $this->Tickets($subPath, FALSE);
	}

	// Handle a request for the home page of the ticketing system
	public function Tickets($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		// We need to load all of the tickets matching any passed criteria (in $_POST)
		// Criteria include columns (array(field)), sorting (array(field=>direction)), offset (int, default=0) and limit (int, default=30) as well as:
		// owner_id (=user_id), ticket_id, account_id, ticketing_status_id

		$defaultLimit = 24;

		$pathToken = count($subPath) ? strtolower($subPath[0]) : NULL;

		if ($pathToken == 'all' || $pathToken == 'mine')
		{
			$id = Ticketing_Status_Type_Conglomerate::TICKETING_STATUS_TYPE_CONGLOMERATE_OPEN_OR_PENDING;
			$status = Ticketing_Status_Type_Conglomerate::getForId($id);
			$_REQUEST['statusId'] = $status->getStatusIds();
		}

		// Default all search settings to be 'blank'
		$offset = 0;
		$limit = $defaultLimit;
		$sort = array();
		$columns = array();
		$ownerId = NULL;
		$statusId = NULL;
		$categoryId = NULL;
		$quickSearch = NULL;

		// If viewing own tickets, default owner id to be the id of the currently logged in user
		$bolOwnTickets = $pathToken == 'mine';
		if ($bolOwnTickets)
		{
			$user = Ticketing_User::getCurrentUser();
			$ownerId = $user->id;
		}

		// If this search is based on last search, default all search settings to be those of the last search
		if (($pathToken == 'last' || array_key_exists('last', $_REQUEST)) && array_key_exists('ticketing', $_SESSION) && array_key_exists('lastTicketList', $_SESSION['ticketing']))
		{
			$lastQuery = unserialize($_SESSION['ticketing']['lastTicketList']);
			$sort = $lastQuery['sort'];
			$columns = $lastQuery['columns'];
			$quickSearch = $lastQuery['quickSearch'];
			$oldFilter = $lastQuery['filter'];
			$ownerId = array_key_exists('ownerId', $oldFilter) ? $oldFilter['ownerId']['value'] : NULL;
			$statusId = array_key_exists('statusId', $oldFilter) ? $oldFilter['statusId']['value'] : NULL;
			$categoryId = array_key_exists('categoryId', $oldFilter) ? $oldFilter['categoryId']['value'] : NULL;
			$limit = $lastQuery['limit'];
			$offset = $lastQuery['offset'];
		}

		if (array_key_exists('offset', $_REQUEST))
		{
			$offset = max(intval($_REQUEST['offset']), 0);
		}

		if (array_key_exists('limit', $_REQUEST))
		{
			$limit = intval($_REQUEST['limit']); 
		}

		if ($limit <= 0)
		{
			$limit = $defaultLimit;
		}

		if (array_key_exists('sort', $_REQUEST))
		{
			$sort = array();
			foreach ($_REQUEST['sort'] as $column => $ascDesc)
			{
				if (!$ascDesc)
				{
					continue;
				}
				$column = preg_replace("/[^a-z_]+/i", '', $column);
				if (!$column)
				{
					continue;
				}
				$sort[$column] = ($ascDesc[0]) == 'a';
			}
		}

		if (array_key_exists('columns', $_REQUEST))
		{
			$column = array();
			foreach($_REQUEST['columns'] as $column)
			{
				if (!$column)
				{
					continue;
				}
				$column = preg_replace("/[^a-z]+/i", '', $column);
				if (!$column)
				{
					continue;
				}
				$columns[] = $column;
			}
		}

		$ownerId = array_key_exists('ownerId', $_REQUEST) ? (strlen($_REQUEST['ownerId']) ? intval($_REQUEST['ownerId']) : NULL) : $ownerId;

		$statusId = array_key_exists('statusId', $_REQUEST) ? (strlen($_REQUEST['statusId']) ? $_REQUEST['statusId'] : NULL) : $statusId;
		if ($statusId !== NULL && !is_array($statusId))
		{
			if (strpos($statusId, ',') !== FALSE)
			{
				$statusId = explode(',', $statusId);
				foreach($statusId as $i => $v)
				{
					$statusId[$i] = $v;
				}
			}
			else
			{
				$statusId = $statusId;
			}
		}

		$categoryId = array_key_exists('categoryId', $_REQUEST) ? (strlen($_REQUEST['categoryId']) ? intval($_REQUEST['categoryId']) : NULL) : $categoryId;

		$filter = array();
		if ($ownerId !== NULL)
		{
			$filter['ownerId'] = array('value' => $ownerId, 'comparison' => '=');
		}

		if ($statusId !== NULL)
		{
			$filter['statusId'] = array('value' => $statusId, 'comparison' => '=');
		}

		if ($categoryId !== NULL)
		{
			$filter['categoryId'] = array('value' => $categoryId, 'comparison' => '=');
		}


		$detailsToRender = array();
		$detailsToRender['columns'] = $columns;
		$detailsToRender['sort'] = $sort;
		$detailsToRender['filter'] = $filter;

		$quickSearch = array_key_exists('quickSearch', $_REQUEST) ? trim($_REQUEST['quickSearch']) : $quickSearch;
		
		if ($quickSearch)
		{
			$quickSearch = trim(implode(' ', array_slice(array_unique(explode(' ', $quickSearch)), 0, 8)));
		}

		$detailsToRender['ticket_count'] = Ticketing_Ticket::countMatching($filter, $quickSearch);

		if ($detailsToRender['ticket_count'] <= $offset)
		{
			$offset = $detailsToRender['ticket_count'] - ($detailsToRender['ticket_count'] % $limit);
		}

		$detailsToRender['quickSearch'] = $quickSearch;
		
		BreadCrumb()->EmployeeConsole();
		BreadCrumb()->SetCurrentPage($bolOwnTickets ? "My Tickets" : "Tickets");


		$detailsToRender['offset'] = $offset;
		$detailsToRender['limit'] = $limit;

		$lastTicketList = serialize($detailsToRender);
		$_SESSION['ticketing']['lastTicketList'] = $lastTicketList;

		$detailsToRender['tickets'] = Ticketing_Ticket::findMatching($columns, $sort, $filter, $offset, $limit, $quickSearch);
		$detailsToRender['users'] = Ticketing_User::listAllWithTickets();
		
		$detailsToRender['statuses'] = array_merge(Ticketing_Status_Type_Conglomerate::listAll(), Ticketing_Status::listAll());
		$detailsToRender['categories'] = Ticketing_Category::listAll();

		$this->LoadPage('ticketing_tickets', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Ticket($subPath, $ticket=NULL)
	{
		$currentUser = Ticketing_User::getCurrentUser();
		if (!$currentUser->isUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		BreadCrumb()->EmployeeConsole();
		BreadCrumb()->TicketingConsole(TRUE);

		$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';

		if (is_numeric($action))
		{
			$_REQUEST['ticketId'] = $action;
			$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';
		}

		$action = str_replace('-', '', $action);

		$ticket = $ticket ? $ticket : (array_key_exists('ticketId', $_REQUEST) ? Ticketing_Ticket::getForId($_REQUEST['ticketId']) : NULL);

		$editableValues = array();

		$permittedActions = $this->getPermittedTicketActions($currentUser, $ticket);

		$detailsToRender = array();

		$detailsToRender['services'] = array();

		// Default the action if the selected action is not permitted
		if ($action !== 'create' && array_search($action, $permittedActions) === FALSE)
		{
			$action = 'error';
			$detailsToRender['error'] = 'You are not authorised to perform that action.';
		}

		$invalidValues = array();

		try
		{
			if (!$ticket && $action != 'create')
			{
				$detailsToRender['error'] = 'Unable to perform action';
				throw new Exception('No ticket selected.');
			}

			$actionLabel = $action;
			$actionLabel[0] = strtoupper($actionLabel);
			$tid = $ticket ? ' ' . $ticket->id : '';
			BreadCrumb()->SetCurrentPage("$actionLabel Ticket$tid");

			$bolChangeOwner	= false;
			switch ($action)
			{
				case 'delete':
					$detailsToRender['error'] = 'Failed to mark ticket as deleted';
					$ticket->delete();
					break;

				case 'take':
					$detailsToRender['error'] = 'Failed to assign ticket to you';
					$ticket->assignTo($currentUser);
					break;

				case 'assign':
				case 'reassign':
					$editableValues[] = 'ownerId';
					$targetUser = NULL;
					$targetUserId = count($subPath) ? $subPath[0] : NULL;
					if (!is_numeric($targetUserId)) $targetUserId = NULL;
					if ($targetUserId === NULL)
					{
						if (array_key_exists('ownerId', $_REQUEST) && is_numeric($_REQUEST['ownerId']))
						{
							$targetUserId = $_REQUEST['ownerId'];
						}
					}
					if ($targetUserId !== NULL)
					{
						$targetUserId = intval($targetUserId);
						$targetUser = Ticketing_User::getForId($targetUserId);
					}
					if ($targetUser && !$targetUser->isUser())
					{
						throw new Exception($targetUser->getName() . ' is not an authorised ticketing system user.');
					}
					if ($targetUser)
					{
						$detailsToRender['error'] = 'Failed to assign ticket to ' . $targetUser->getName();
						$detailsToRender['targetUser'] = $targetUser;
						$ticket->assignTo($targetUser);
					}
					break;

				case 'create':
					$ticket = Ticketing_Ticket::createBlank();
					$ticket->owner = $currentUser;
					$editableValues[] = 'accountId';
					
					$bolChangeOwner	= true;

				case 'edit':

					if ($action == 'edit')
					{
						if (!$ticket->accountId)
						{
							$editableValues[] = 'accountId';
						}
					}

					if ($currentUser->isAdminUser())
					{
						$editableValues[] = 'ownerId';
					}
					$editableValues[] = 'priorityId';
					$editableValues[] = 'subject';
					$editableValues[] = 'contactId';
					if ($ticket->isAssigned() || $currentUser->isAdminUser())
					{
						$editableValues[] = 'statusId';
					}
					//$editableValues[] = 'customerGroupId'; // Customer group should come from account
					$editableValues[] = 'categoryId';
					$editableValues[] = 'serviceId';

					$ticketServices = $ticket->getServiceIds();
					
					if (array_key_exists('save', $_REQUEST))
					{
						// Validate the passed details and save if valid
						$validatedValues = array();
						foreach ($editableValues as $editableValue)
						{
							$value = array_key_exists($editableValue, $_REQUEST) ? $_REQUEST[$editableValue] : NULL;
							if ($value !== NULL && !is_array($value))
							{
								$value = trim($value);
							}
							switch ($editableValue)
							{
								case 'ownerId':
									$value =  $currentUser->isAdminUser() ? Ticketing_User::getForId(intval($value)) : NULL;
									if (!$value && $currentUser->isAdminUser())
									{
										if ($action == 'edit' && $ticket->ownerId)
										{
											$ticket->ownerId = NULL;
											$invalidValues[$editableValue] = 'You cannot unassign a ticket. Please specify an owner for the ticket.';
										}
										else if ($action == 'create')
										{
											$ticket->ownerId = NULL;
											$invalidValues[$editableValue] = 'Please specify an owner for the ticket.';
										}
									}
									else if ($currentUser->isAdminUser())
									{
										$bolChangeOwner	= ($bolChangeOwner) ? $bolChangeOwner : ($ticket->ownerId !== $value->id) ? true : false;
										$ticket->ownerId = $value->id;
									}
									else
									{
										$bolChangeOwner	= ($bolChangeOwner) ? $bolChangeOwner : ($ticket->ownerId !== $currentUser->id) ? true : false;
										$ticket->ownerId = $currentUser->id;
									}
									break;

								case 'priorityId':
									$value = Ticketing_Priority::getForId(intval($value));
									if (!$value)
									{
										$ticket->priorityId = NULL;
										$invalidValues[$editableValue] = 'You must specify a priority for the ticket.';
									}
									else
									{
										$ticket->priorityId = $value->id;
									}
									break;

								case 'subject':
									if (!$value)
									{
										$ticket->subject = $value;
										$invalidValues[$editableValue] = 'Subject cannot be blank.';
									}
									else
									{
										$ticket->subject = $value;
									}
									break;

								case 'accountId':
									if (!$ticket->accountId && !$value)
									{
										$ticket->accountId = NULL;
										//$invalidValues[$editableValue] = 'You must specify an account for the ticket.';
										break;
									}
									if (!$value)
									{
										$ticket->accountId = NULL;
										$invalidValues[$editableValue] = 'You cannot remove a ticket from an account.';
										break;
									}
									// Need to check that the account exists
									$value = Account::getForId(intval($value));
									if (!$value)
									{
										$ticket->accountId = $value->id;
										$invalidValues[$editableValue] = 'The account number is invalid.';
									}
									else
									{
										$ticket->accountId = $value->id;
										$ticket->customerGroupId = $value->customerGroup;
									}
									break;

								case 'contactId':
									$value = Ticketing_Contact::getForId(intval($value));
									if (!$value)
									{
										$ticket->contactId = NULL;
										$invalidValues[$editableValue] = 'You must specify a contact for the ticket.';
									}
									else
									{
										$ticket->contactId = $value->id;
									}
									break;

								case 'statusId':
									// Statuses that can be set are limited! Don't accept just because it's valid!!!
									$value = Ticketing_Status::getForId(intval($value));
									if (!$value)
									{
										$ticket->statusId = NULL;
										$invalidValues[$editableValue] = 'You must specify a status for the ticket.';
									}
									else
									{
										if (($ticket->ownerId == NULL) && ($value->id != TICKETING_STATUS_UNASSIGNED))
										{
											$invalidValues[$editableValue] = "Unassigned tickets cannot have their status changed.";
										}
										elseif (!$value->validForUserAndTicket($currentUser, $ticket))
										{
											$ticket->statusId = NULL;
											$invalidValues[$editableValue] = 'You are not allowed to select that status.';
										}
										else
										{
											$ticket->statusId = $value->id;
										}
									}
									break;

								case 'categoryId':
									$value = Ticketing_Category::getForId(intval($value));
									if (!$value)
									{
										$ticket->categoryId = NULL;
										$invalidValues[$editableValue] = 'You must specify a category for the ticket.';
									}
									else
									{
										$ticket->categoryId = $value->id;
									}
									break;

								case 'serviceId':
									$ticketServices = is_array($value) ? $value : array();
									break;
							}
						}

						if (!empty($invalidValues))
						{
							//$detailsToRender['error'] = 'Please complete all mandatory fields.';
							$detailsToRender['error'] = "Please fix the following issues...\n". implode("\n", $invalidValues);
							$detailsToRender['saved'] = FALSE;
							if ($ticket)
							{
								$account = $ticket->getAccount();
								if ($account)
								{
									$detailsToRender['services'] = $account->listServices();
								}
							}
						}
						else
						{
							// If the ticket has not yet been assigned to anyone, it must be left in the unstarted state.
							if (!$ticket->ownerId)
							{
								$ticket->statusId = TICKETING_STATUS_UNASSIGNED;
							}

							$ticket->save();
							$ticket->setServices($ticketServices);
							$detailsToRender['saved'] = TRUE;
							$action = 'save';
							
							// Hacky check to see if we had a change of ownership
							if ($bolChangeOwner)
							{
								$ticket->assignTo(Ticketing_User::getForId($ticket->ownerId));
							}
						}
					}
					else
					{
						if ($ticket)
						{
							$account = $ticket->getAccount();
							if ($account)
							{
								$detailsToRender['services'] = $account->listServices();
							}
						}
						$detailsToRender['saved'] = FALSE;
					}

					break;

				case 'error':
				case 'view':
				default:
			}
		}
		catch(Exception $exception)
		{
			$action = 'error';
			$detailsToRender['error'] .= ($detailsToRender['error'] ? ': ' : '') . $exception->getMessage();
		}

		// We need to load the details of the ticket specified by a ticket_id in $_REQUEST
		$detailsToRender['action'] = $action;
		$detailsToRender['ticket'] = $ticket;
		$detailsToRender['permitted_actions'] = $this->getPermittedTicketActions($currentUser, $ticket);
		$detailsToRender['editable_values'] = $editableValues;
		$detailsToRender['invalid_values'] = $invalidValues;

		$this->LoadPage('ticketing_ticket', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	private function getPermittedTicketActions($user, $ticket)
	{
		$permittedActions = array();

		if ($ticket && $ticket->isSaved())
		{
			$permittedActions[] = 'view';
			$permittedActions[] = 'edit';

			if ($user->isAdminUser() && !$ticket->isAssignedTo($user))
			{
				$permittedActions[] = 'take';
			}

			if ($user->isAdminUser())
			{
				if ($ticket->isAssigned())
				{
					$permittedActions[] = 'reassign';
				}
				else
				{
					$permittedActions[] = 'assign';
				}
				$permittedActions[] = 'delete';
			}
		}

		return $permittedActions;
	}

	public function Contact($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		// We need to load the details of the contact specified by a contact_id in $_REQUEST
		$detailsToRender = array();
		$detailsToRender['ticketId'] = array_key_exists('ticketId', $_REQUEST) ? $_REQUEST['ticketId'] : NULL;
		$detailsToRender['correspondance_id'] = array_key_exists('correspondanceId', $_REQUEST) ? $_REQUEST['correspondanceId'] : NULL;
		$detailsToRender['contact'] = Ticketing_Contact::getForId($_REQUEST['contactId']);

		$this->LoadPage('ticketing_contact', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}

	public function Correspondence($subPath)
	{
		$currentUser = Ticketing_User::getCurrentUser();
		if (!$currentUser->isUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';

		if (is_numeric($action))
		{
			$_REQUEST['correspondanceId'] = $action;
			$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';
		}

		$action = str_replace('-', '', $action);

		// We need to load the details of the contact specified by a contact_id in $_REQUEST
		$detailsToRender = array();

		$correspondence = Ticketing_Correspondance::getForId($_REQUEST['correspondanceId']);

		$permittedActions = $this->getPermittedCorrespondenceActions($currentUser, $correspondence);

		// Default the action if the selected action is not permitted
		if (array_search($action, $permittedActions) === FALSE)
		{
			$action = 'error';
			$detailsToRender['error'] = 'You are not authorised to perform that action.';
		}

		$editableValues = array();
		$invalidValues = array();

		$ticketId = array_key_exists('ticketId', $_REQUEST) ? intval($_REQUEST['ticketId']) : (count($subPath) && is_numeric($subPath[0]) ? intval($subPath[0]) : NULL);

		try
		{
			if (!$correspondence && $action != 'create')
			{
				$detailsToRender['error'] = 'Unable to perform action';
				throw new Exception('No correspondence selected.');
			}

			$sendError = '';

			switch ($action)
			{
				case 'delete':

					$correspondence->delete();

					// Deleted the correspondence. Where to now? The ticket?
					return $this->Ticket(array($correspondence->ticketId, 'view'));

				case 'send':
				case 'resend':

					try
					{
						$correspondence->emailToCustomer();
					}
					catch (Exception $e)
					{
						$sendError = $e->getMessage();
					}
					$ticketId = $correspondence->ticketId;

					break;

				case 'create':

					if ($ticketId === NULL)
					{
						throw new Exception('No ticket specified for adding correspondence to.');
					}

					$correspondence = Ticketing_Correspondance::createBlank();
					$correspondence->user = $currentUser;
					$correspondence->ticketId = $ticketId;
					$detailsToRender['ticket'] = $correspondence->getTicket();
					$correspondence->summary = $detailsToRender['ticket']->subject;
					$correspondence->sourceId = TICKETING_CORRESPONDANCE_SOURCE_PHONE;
					$correspondence->deliveryStatusId = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED;
					$customerGroupEmail = $detailsToRender['ticket']->getCustomerGroupEmail();
					$correspondence->customerGroupEmailId = $customerGroupEmail->id;

					$oldDeliveryStatus = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT;

				case 'edit':
					$editableValues[] = 'summary';
					$editableValues[] = 'details';
					$editableValues[] = 'contactId';

					$ticketId = $correspondence->ticketId;

					if ($action == 'edit')
					{
						$oldDeliveryStatus = $correspondence->deliveryStatusId;
					}

					if (!$correspondence->isSaved() || ($correspondence->isOutgoing() && $correspondence->isNotSent()))
					{
						$editableValues[] = 'sourceId';
						$editableValues[] = 'customerGroupEmailId';
					}

					if ($action == 'create' || ($correspondence->isOutgoing() && $correspondence->isNotSent() && !$correspondence->isEmail()))
					{
						$editableValues[] = 'deliveryStatusId';
					}

					// Now need to validate the passed values and save if appropriate
					if (array_key_exists('save', $_REQUEST))
					{
						// Validate the passed details and save if valid
						$validatedValues = array();
						foreach ($editableValues as $editableValue)
						{
							$value = array_key_exists($editableValue, $_REQUEST) ? $_REQUEST[$editableValue] : NULL;
							if ($value !== NULL && !is_array($value))
							{
								$value = trim($value);
							}
							switch ($editableValue)
							{
								case 'summary':
									$value =  trim($value);
									if (!$value)
									{
										$correspondence->summary = '';
										$invalidValues[$editableValue] = 'You must specify a subject for the correspondence.';
									}
									else
									{
										$correspondence->summary = $value;
									}
									break;

								case 'details':
									$value =  trim($value);
									if (!$value)
									{
										$correspondence->details = '';
										$invalidValues[$editableValue] = 'You must provide details of the correspondence.';
									}
									else
									{
										$correspondence->details = $value;
									}
									break;

								case 'contactId':
									$value = Ticketing_Contact::getForId(intval($value));
									if (!$value)
									{
										$correspondence->contactId = NULL;
										$invalidValues[$editableValue] = 'You must select a contact.';
									}
									else
									{
										$correspondence->contactId = $value->id;
									}
									break;

								case 'sourceId':
									$value = Ticketing_Correspondance_Source::getForId(intval($value));
									if (!$value)
									{
										$correspondence->sourceId = NULL;
										$invalidValues[$editableValue] = 'You must specify a source for the correspondence.';
										break;
									}
									else
									{
										$correspondence->sourceId = $value->id;
									}
									break;

								case 'customerGroupEmailId':
									$value = Ticketing_Customer_Group_Email::getForId(intval($value));
									if (!$value)
									{
										$correspondence->customerGroupEmailId = NULL;
										$invalidValues[$editableValue] = 'You must specify a customer group email address.';
									}
									else
									{
										$correspondence->customerGroupEmailId = $value->id;
									}
									break;

								case 'deliveryStatusId':
									$value = Ticketing_Correspondance_Delivery_Status::getForId(intval($value));
									if (!$value)
									{
										$correspondence->deliveryStatusId = NULL;
										$invalidValues[$editableValue] = 'You must specify a delivery status.';
									}
									else
									{
										$correspondence->deliveryStatusId = $value->id;
									}
									break;
							}
						}

						if (!empty($invalidValues))
						{
							$detailsToRender['error'] = 'Please complete all mandatory fields.';
							$detailsToRender['saved'] = FALSE;
						}
						else
						{
							$sendAfterSave = FALSE;
							if ($oldDeliveryStatus == TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT && 
								$correspondence->deliveryStatusId == TICKETING_CORRESPONDANCE_DELIVERY_STATUS_SENT &&
								$correspondence->isEmail() && $correspondence->isOutgoing())
							{
								$correspondence->deliveryStatusId = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT;
								$sendAfterSave = TRUE;
							}
							if (!$correspondence->deliveryDatetime && (!$correspondence->isOutgoing() || $correspondence->isSent()))
							{
								$correspondence->deliveryDatetime = date('Y-m-d H:i:s');;
							}
							$correspondence->save();
							if ($sendAfterSave)
							{
								// We need to ensure that the email is sent
								$detailsToRender['email_not_sent'] = TRUE;
							}
							$detailsToRender['saved'] = TRUE;
							$action = 'save';
						}
					}
					else
					{
						$detailsToRender['saved'] = FALSE;
					}

					break;
	
				case 'error':
				case 'view':
				default:
					$ticketId = $correspondence ? $correspondence->ticketId : $ticketId;
					break;
			}
		}
		catch(Exception $exception)
		{
			$action = 'error';
			$detailsToRender['error'] .= ($detailsToRender['error'] ? ': ' : '') . $exception->getMessage();
		}

		BreadCrumb()->EmployeeConsole();
		BreadCrumb()->TicketingConsole(TRUE);
		if ($ticketId) 
		{
			BreadCrumb()->TicketingTicket($ticketId);
		}
		$actionLabel = $action;
		$actionLabel[0] = strtoupper($actionLabel[0]);
		BreadCrumb()->SetCurrentPage("$actionLabel Correspondence");


		$detailsToRender['correspondence'] = $correspondence;
		$detailsToRender['action'] = $action;
		$detailsToRender['send_error'] = $sendError;
		$detailsToRender['permitted_actions'] = $this->getPermittedCorrespondenceActions($currentUser, $correspondence);
		$detailsToRender['ticketId'] = $ticketId;
		$detailsToRender['editable_values'] = $editableValues;
		$detailsToRender['invalid_values'] = $invalidValues;

		$this->LoadPage('ticketing_correspondance', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}


	private function getPermittedCorrespondenceActions($user, $correspondence)
	{
		$permittedActions = array();

		if ($correspondence)
		{
			if ($correspondence->isSaved())
			{
				$permittedActions[] = 'view';
			}

			if ($user->isAdminUser())
			{
				if ($correspondence->isSaved())
				{
					$permittedActions[] = 'edit';

					if ($correspondence->isOutgoing())
					{
						if ($correspondence->isNotSent())
						{
							$permittedActions[] = 'delete';
						}
					}
					else
					{
						$permittedActions[] = 'delete';
					}
				}
			}
			else
			{
				// Allow non-admin users to email anything OTHER than incomming emails or emails that have already been sent emails
				if ($correspondence->isSaved() && (!$correspondence->isEmail() || ($correspondence->isOutgoing() && !$correspondence->isSent())))
				{
					$permittedActions[] = 'edit';
				}

				if ($correspondence->isOutgoing() && $correspondence->isNotSent() && $correspondence->isSaved())
				{
					$permittedActions[] = 'delete';
				}
			}

			if ($correspondence->isOutgoing() && $correspondence->isEmail() && $correspondence->isSaved())
			{
				if ($correspondence->isNotSent())
				{
					$permittedActions[] = 'send';
				}
				else
				{
					$permittedActions[] = 'resend';
				}
			}
		}

		$permittedActions[] = 'create';

		return $permittedActions;
	}


	public function Attachment($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		$detailsToRender = array();
		$detailsToRender['ticketId'] = array_key_exists('ticketId', $_REQUEST) ? $_REQUEST['ticketId'] : NULL;

		$attachmentId = count($subPath) ? $subPath[0] : NULL;
		if (is_numeric($attachmentId))
		{
			$_REQUEST['attachmentId'] = $attachmentId;
		}

		$attachment = Ticketing_Attachment::getForId($_REQUEST['attachmentId']);

		header("Content-Type: " . $attachment->mimeType);
		header("Content-Disposition: download; filename=\"" . $attachment->fileName . "\"");
		echo $attachment->getFileContent();
		exit;
	}


	public function AttachmentTypes($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		$detailsToRender = array();
		$this->LoadPage('ticketing_attachment_types', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}


	// Manages the Ticketing Summary Report functionality
	public function SummaryReport($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}
		
		BreadCrumb()->EmployeeConsole();
		BreadCrumb()->TicketingConsole();
		BreadCrumb()->SetCurrentPage("Summary Report");

		if (is_array($subPath) && count($subPath) == 1)
		{
			$strAction = strtolower(array_shift($subPath));
			if ($strAction == "getreport")
			{
				// The user wants to retrieve the cached SummaryReport
				if (	is_array($_SESSION['Ticketing']) && 
						is_array($_SESSION['Ticketing']['SummaryReport']) && 
						array_key_exists("Content", $_SESSION['Ticketing']['SummaryReport'])
					)
				{
					// A report ha been cached
					// Send it to the user
					header("Content-Type: application/excel");
					header("Content-Disposition: attachment; filename=\"" . "ticketing_summary_report_". date("Y_m_d") . ".xls" . "\"");
					echo $_SESSION['Ticketing']['SummaryReport']['Content'];
					
					// Remove it from the Session
					unset($_SESSION['Ticketing']['SummaryReport']['Content']);
					exit;
				}
			}
		}
		
		
		// Build Owner combo box data
		$arrOwners = array();
		$arrOwners[] = array(	"Id"	=> "all",
								"Name"	=> "all"
							);
		
		$arrTicketUsers = Ticketing_User::listAll();
		foreach ($arrTicketUsers as $objUser)
		{
			$arrOwners[] = array(	"Id"	=> $objUser->id,
									"Name"	=> $objUser->getName()
								); 
		}
		
		// Build Category combo box data
		$arrCategories = array();
		$arrCategories[] = array(	"Id"		=> "all",
									"Name"		=> "all"
									);
		$arrAllCategories = Ticketing_Category::listAll();
		foreach ($arrAllCategories as $intCategory=>$objCategory)
		{
			$arrCategories[] = array(	"Id"		=> $intCategory,
										"Name"		=> $objCategory->description
									);
		}
		
		// Build Status combo box data
		$arrStatuses = array();
		foreach ($GLOBALS['*arrConstant']['ticketing_status_type'] as $intStatusType=>$arrStatusType)
		{
			$arrStatuses[] = array(	"Id"			=> $intStatusType,
									"Name"			=> $arrStatusType['Description'],
									"IsStatusType"	=> TRUE
									);
		}
		foreach ($GLOBALS['*arrConstant']['ticketing_status'] as $intStatus=>$arrStatus)
		{
			$arrStatuses[] = array(	"Id"			=> $intStatus,
									"Name"			=> $arrStatus['Description'],
									"IsStatusType"	=> FALSE
									);
		}
		
		$arrTimeRange		= array(	//"Earliest"	=> date("00:00:00 d/m/Y", strtotime("-3 months")),
										"Earliest"		=> NULL,
										"Latest"	=> date("23:59:59 d/m/Y"),
										"FromYear"	=> 2008,
										"ToYear"	=> intval(date("Y")),
										"DefaultYear"	=> intval(date("Y")),
										"DefaultMonth"	=> intval(date("m")),
										"DefaultDay"	=> intval(date("d")),
										
									);
		
		$arrData = array(
							"Owners"		=> $arrOwners,
							"Categories"	=> $arrCategories,
							"Statuses"		=> $arrStatuses,
							"TimeRange"		=> $arrTimeRange
						);
		
		$this->LoadPage('ticketing_summary_report', HTML_CONTEXT_DEFAULT, $arrData);
	}

	public function QuickSearch($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		$for = trim($_REQUEST['for']);
		$isTicketNumber = FALSE;
		if (preg_match("/^T[0-9\t ]*[0-9]+[0-9\t ]*Z\$/i", $for))
		{
			$cleansed = preg_replace("/[^0-9]+/", '', $for);
			// Check to see if it is a ticket number
			$match = Ticketing_Ticket::getForId(intval($cleansed));
			if ($match)
			{
				return $this->Ticket(array(), $match);
			}
		}
		if (preg_match("/^[0-9\t ]*[0-9]+[0-9\t ]*\$/i", $for))
		{
			$cleansed = preg_replace("/[^0-9]+/", '', $for);
			// Check to see if it is a ticket number
			$match = Ticketing_Ticket::getForId(intval($cleansed));
			if ($match)
			{
				return $this->Ticket(array(), $match);
			}
		}

		// OK, looks like it's not a ticket number.
		// We should now try to find all tickets that match on subject or contact or ...
		unset($_REQUEST['last']);
		unset($_REQUEST['offset']);
		unset($_REQUEST['limit']);
		unset($_REQUEST['ownerId']);
		unset($_REQUEST['statusId']);
		unset($_REQUEST['categoryId']);
		$_REQUEST['quickSearch'] = $for;
		return $this->Tickets($subPath);
	}


	public function Admin($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';

		$config = Ticketing_Config::load();

		$detailsToRender = array();
		$detailsToRender['error'] = '';
		$invalidValues = array();

		// Need to check to see if we are editing general settings
		if ($action == 'edit')
		{
			$detailsToRender['saved'] = FALSE;
			if (array_key_exists('save', $_REQUEST))
			{
				// Cannot validate the submitted paths before saving as they are on the backend 
				$strSourcePath = array_key_exists('sourcePath', $_REQUEST) ? trim($_REQUEST['sourcePath']) : NULL;
				$strBackupPath = array_key_exists('backupPath', $_REQUEST) ? trim($_REQUEST['backupPath']) : NULL;
				$strJunkPath = array_key_exists('junkPath', $_REQUEST) ? trim($_REQUEST['junkPath']) : NULL;

				// MUST NOT BE BLANK AND MUST BE VALID
				if (!$strSourcePath)
				{
					$invalidValues['sourcePath'] = 'You must specify a source directory.';
				}
				$config->setSourceDirectory($strSourcePath ? $strSourcePath : NULL);

				// CAN BE BLANK BUT IF SET MUST BE VALID
				$config->setBackupDirectory($strBackupPath ? $strBackupPath : NULL);

				// CAN BE BLANK BUT IF SET MUST BE VALID
				$config->setJunkDirectory($strJunkPath ? $strJunkPath : NULL);

				if (!empty($invalidValues))
				{
					$detailsToRender['error'] = 'Please correct all highlighted fields.';
					
				}
				else
				{
					$config->save();
					$detailsToRender['saved'] = TRUE;
					$action = 'save';
				}
			}
		}

		BreadCrumb()->EmployeeConsole();
		BreadCrumb()->SetCurrentPage("Ticketing Administration");

		// The admin interface allows the setting of the mail paths and settings per customer group
		$detailsToRender['action'] = $action;
		$detailsToRender['config'] = $config;
		$detailsToRender['invalid_values'] = $invalidValues;
		$this->LoadPage('ticketing_admin', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}


	public function GroupAdmin($subPath)
	{
		if (!Ticketing_User::currentUserIsTicketingAdminUser())
		{
			AuthenticatedUser()->InsufficientPrivilegeDie();
		}

		$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';

		if (is_numeric($action))
		{
			$_REQUEST['customerGroupId'] = $action;
			$action = count($subPath) ? strtolower(array_shift($subPath)) : 'view';
		}

		// Customer group id
		$customerGroupId = array_key_exists('customerGroupId', $_REQUEST) ? $_REQUEST['customerGroupId'] : NULL;

		// Check that the customer group id is valid
		$customerGroup = $customerGroupId ? Customer_Group::getForId($customerGroupId) : NULL;

		$detailsToRender = array();
		$detailsToRender['error'] = '';
		$invalidValues = array();

		try 
		{
			// Return an error if there is no customer group
			if (!$customerGroup)
			{
				throw new Exception('No customer group selected.');
			}

			// Need to check to see if we are editing general settings
			$customerGroupConfig = Ticketing_Customer_Group_Config::getForCustomerGroup($customerGroup);

			if ($action == 'edit')
			{
				// Check to see if we are editing and saving
				// The details are: acknowledge_email_receipts, email_receipt_acknowledgement, default_email_id
				if (array_key_exists('save', $_REQUEST))
				{
					$acknowledgeEmailReceipts = array_key_exists('acknowledgeEmailReceipts', $_REQUEST) ? intval($_REQUEST['acknowledgeEmailReceipts']) : FALSE;
					$customerGroupConfig->setAcknowledgeEmailReceipts($acknowledgeEmailReceipts);

					if ($customerGroupConfig->acknowledgeEmailReceipts())
					{
						$emailReceiptAcknowledgement = array_key_exists('emailReceiptAcknowledgement', $_REQUEST) ? trim($_REQUEST['emailReceiptAcknowledgement']) : NULL;
						$customerGroupConfig->emailReceiptAcknowledgement = $emailReceiptAcknowledgement;
						if (!$emailReceiptAcknowledgement)
						{
							$invalidValues['emailReceiptAcknowledgement'] = 'To acknowledge email recipts you must specify an acknowledgement email.';
						}
					}

					$defaultEmailId = array_key_exists('defaultEmailId', $_REQUEST) ? intval($_REQUEST['defaultEmailId']) : 0;
					$defaultEmail = $defaultEmailId ? Ticketing_Customer_Group_Email::getForId($defaultEmailId) : NULL;
					if (!$defaultEmail)
					{
						$invalidValues['defaultEmailId'] = 'You must specify a default email address to use for this customer group.';
					}
					else
					{
						$customerGroupConfig->defaultEmailId = $defaultEmail->id;
					}

					if (!empty($invalidValues))
					{
						$detailsToRender['error'] = implode(" \n", $invalidValues);
					}
					else
					{
						$customerGroupConfig->save();
						$detailsToRender['saved'] = TRUE;
						$action = 'save';
					}
				}
			}
		}
		catch (Exception $e)
		{
			$action = 'error';
			$detailsToRender['error'] .= ($detailsToRender['error'] ? ': ' : '') . $e->getMessage();
		}

		BreadCrumb()->EmployeeConsole();
		BreadCrumb()->TicketingAdmin();
		BreadCrumb()->SetCurrentPage("Customer Group: " . $customerGroup->name);

		// The admin interface allows the setting of the mail paths and settings per customer group
		$detailsToRender['action'] = $action;
		$detailsToRender['customer_group'] = $customerGroup;
		$detailsToRender['customer_group_config'] = $customerGroupConfig;
		$detailsToRender['invalid_values'] = $invalidValues;
		$this->LoadPage('ticketing_group_admin', HTML_CONTEXT_DEFAULT, $detailsToRender);
	}


}

?>
