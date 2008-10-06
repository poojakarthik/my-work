<?php

class HtmlTemplate_Ticketing_Ticket extends FlexHtmlTemplate
{

	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript('reflex_popup');
		$this->LoadJavascript('ticketing_contact');
	}

	public function Render()
	{
		$ticket = $this->mxdDataToRender['ticket'];
		$renderer = strtolower('render_'.str_replace('-', '', $this->mxdDataToRender['action']));
		if (method_exists($this, $renderer))
		{
			$this->{$renderer}($ticket);
		}
	}

	private function render_save($ticket)
	{
		$this->render_view($ticket, "The ticket has been saved.");
	}

	private function render_delete($ticket)
	{
		$this->render_view($ticket, "This ticket has been marked as deleted.");
	}

	private function render_assign($ticket)
	{
		$this->render_reassign($ticket, TRUE, "Assign");
	}

	private function render_reassign($ticket, $assigned=FALSE, $actionName="Re-assign")
	{
		$targetUser = $this->mxdDataToRender['targetUser'];
		if ($targetUser)
		{
			$this->render_view($ticket, "This ticket has been {$re}assigned to " . $targetUser->getName() . ".");
		}
		else
		{
			$this->render_edit($ticket, $actionName);
		}
	}

	private function render_take($ticket)
	{
		$this->render_view($ticket, "This ticket has been assigned to you.");
	}

	private function render_create($ticket)
	{
		$this->render_edit($ticket, "Create");
	}

	private function render_search()
	{
		$errorMessage = $this->mxdDataToRender['message'];
		$this->no_ticket('Quick Search', $errorMessage, TRUE);
	}

	private function render_error($ticket)
	{
		$errorMessage = $this->mxdDataToRender['error'];
		$this->render_view($ticket, $errorMessage, TRUE);
	}

	private function render_view($ticket, $message=NULL, $bolIsError=FALSE)
	{
		// If there is no ticket, we need to tell the user as much
		if (!$ticket)
		{
			return $this->no_ticket();
		}

		$owner = $ticket->getOwner();
		$ownerName = htmlspecialchars($owner ? $owner->getName() : '['.Ticketing_Status::getForId(TICKETING_STATUS_UNASSIGNED)->name.']');
		$contact = $ticket->getContact();
		$contactName = htmlspecialchars($contact ? $contact->getName() : '[No associated contact]');

		if ($message)
		{
			?>
		<div class="message<?=($bolIsError ? " error" : "")?>"><?=$message?></div><?php
		}

		$actionLinks = array();
		foreach($this->mxdDataToRender['permitted_actions'] as $action)
		{
			$id = '';
			if ($action !== 'create')
			{
				$id = $ticket->id . '/';
			}
			$action[0] = strtoupper($action[0]);
			$action = htmlspecialchars($action);
			$actionLinks[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Ticket/$id$action\">$action</a>";
		}
		$actionLinks = implode(' | ', $actionLinks);

		?>

		<form id="view_ticket" name="view_ticket" method="POST">
			<table id="ticketing" name="ticketing" class="reflex">
				<caption>
					<div id="caption_bar" name="caption_bar">
					<div id="caption_title" name="caption_title">
						Viewing ticket: <?=$ticket->id?>
					</div>
					<div id="caption_options" name="caption_options">
						<?=$actionLinks?>
					</div>
					</div>
				</caption>
				<thead>
					<tr>
						<th colspan="2">&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="2">
							&nbsp;
						</th>
					</tr>
				</tfoot>
				<tbody>
					<tr class="alt">
						<td class="title">Subject: </td>
						<td><?=htmlspecialchars(trim($ticket->subject) ? $ticket->subject : '<em>[No Subject]</em>')?></td>
					</tr>
					<tr class="alt">
						<td class="title">Owner: </td>
						<td><?=$ownerName?></td>
					</tr>
					<tr class="alt">
						<td class="title">Priority: </td>
						<td class="<?=$ticket->getPriority()->cssClass?>"><?=$ticket->getPriority()->name?></td>
					</tr>
					<tr class="alt">
						<td class="title">Customer Group: </td>
						<td><?=$ticket->getCustomerGroup()->name?></td>
					</tr>
					<tr class="alt">
						<td class="title">Account: </td>
						<td><?=$ticket->accountId ? ('<a href="' . Href()->AccountOverview($ticket->accountId) . '">' . $ticket->accountId . '</a>') : '[Not matched to an account]'?></td>
					</tr>
					<tr class="alt">
						<td class="title">Contact: </td>
						<td><?=$contactName?>
							<input type="button" class="reflex-button" onclick='Ticketing_Contact.displayContact(<?=$ticket->contactId?>, null);return false;' value='view' />
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Status: </td>
						<td class="<?=$ticket->getStatus()->cssClass?>"><?=$ticket->getStatus()->name?></td>
					</tr>
					<tr class="alt">
						<td class="title">Category: </td>
						<td class="<?=$ticket->getCategory()->cssClass?>"><?=$ticket->getCategory()->name?></td>
					</tr>
					<tr class="alt">
						<td class="title">Services: </td>
						<td><?php
							$ticketServices = $ticket->getServices();
							$output = array();
							foreach ($ticketServices as $service)
							{
								$output[] = htmlspecialchars($service->getFNN());
							}
							$output = implode('<br/>', $output);
							echo $output ? $output : '[No related services]';
						?></td>
					</tr>
					<tr class="alt">
						<td class="title">Created: </td>
						<td><?=$ticket->creationDatetime?></td>
					</tr>
					<tr class="alt">
						<td class="title">Last Modified: </td>
						<td><?=$ticket->modifiedDatetime?></td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php

		$this->render_correspondences($ticket);
	}

	private function render_edit($ticket=FALSE, $requestedAction="Edit")
	{
		if($ticket === FALSE)
		{
			$ticket = $this->mxdDataToRender['ticket'];
		}

		$owner = $ticket->getOwner();
		$ownerName = htmlspecialchars($owner ? $owner->getName() : '['.Ticketing_Status::getForId(TICKETING_STATUS_UNASSIGNED)->name.']');
		$contact = $ticket->getContact();
		$contactName = htmlspecialchars($contact ? $contact->getName() : '[No associated contact]');

		$message = array_key_exists('error', $this->mxdDataToRender) ? $this->mxdDataToRender['error'] : '';

		if ($message)
		{
			?>
		<div class="message error"><?=$message?></div><?php
		}

		$editing = $requestedAction == 'Edit';

		$actionLinks = array();
		foreach($this->mxdDataToRender['permitted_actions'] as $action)
		{
			$id = '';
			if ($action !== 'create')
			{
				$id = $ticket->id . '/';
			}
			$action[0] = strtoupper($action[0]);
			$action = htmlspecialchars($action);
			$actionLinks[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Ticket/$id$action\">$action</a>";
		}
		$actionLinks = implode(' | ', $actionLinks);

		$editableValues = $this->mxdDataToRender['editable_values'];

		$invalidValues = $this->mxdDataToRender['invalid_values'];

		$cancel = Flex::getUrlBase() . '/reflex.php/Ticketing/' . ($ticket->isSaved() ? 'Ticket/' . $ticket->id . '/View' : 'System/?last=true');

		?>
			<script>
			//<!--
				$validateAccounts = null;
				$selectedContactValue = null;
	
				function accountNumberChange()
				{
					var accountId = $ID('accountId');
					var ticketId = $ID('ticketId');
					var serviceId = $ID('serviceId');
					if (accountId.value.length != 10) // WIP: HACK
					{
						accountId.className = 'invalid';
						return;
					}
					if (accountId.lastAjax == accountId.value)
					{
						return;
					}
					accountId.lastAjax = accountId.value;
					accountId.className = '';
					$validateAccounts(accountId.value, ticketId.value);
				}
	
				function validatedAccount(response)
				{
					var accountId = $ID('accountId');
					if (!response['isValid'])
					{
						accountId.className = 'invalid';
					}
					else
					{
						accountId.className = 'valid';
					}
					populateContactList(response['contacts']);
					populateServiceList(response['services']);
				}
	
				function populateContactList(contacts)
				{
					var contactId = $ID('contactId');
					emptyElement(contactId);
					for (var i = 0, l = contacts.length; i < l; i++)
					{
						var id = contacts[i]['id'];
						var name = contacts[i]['name'];
						var option = document.createElement('option');
						option.value = id;
						option.selected = id == $selectedContactValue;
						option.appendChild(document.createTextNode(name));
						contactId.appendChild(option);
					}
				}
	
				function populateServiceList(services)
				{
					var serviceId = $ID('serviceId');
					emptyElement(serviceId);
					for (var i = 0, l = services.length; i < l; i++)
					{
						var id = services[i]['service_id'];
						var name = services[i]['fnn'];
						var option = document.createElement('option');
						option.value = id;
						option.appendChild(document.createTextNode(name));
						serviceId.appendChild(option);
					}
				}
	
				function emptyElement(el)
				{
					for (var i = el.length - 1; i >= 0; i--)
					{
						el.removeChild(el.childNodes[i]);
					}
				}
	
				function viewContactDetails()
				{
					var accountIdInput = $ID('accountId');
					var accountId = null;
					if (accountIdInput.className != 'invalid')
					{
						accountId = accountIdInput.value;;
					}
	
					var contactIdInput = $ID('contactId');
					var contactId = null;
					if (contactIdInput.tagName == 'SELECT')
					{
						var selectedIndex = contactIdInput.selectedIndex;
						if (selectedIndex >= 0 && selectedIndex < contactIdInput.childNodes.length)
						{
							contactId = contactIdInput.childNodes[selectedIndex].value;
						}
					}
					else
					{
						contactId = contactIdInput.value;
					}
	
					if (contactId == null)
					{
						alert('Please select a contact.');
						return false;
					}
	
					Ticketing_Contact.displayContact(contactId, accountId);
					return false;
				}
	
				function addContactCallBack(newContact)
				{
					$selectedContactValue = newContact['contactId'];
					$ID('accountId').lastAjax = null;
					$updateContacts($ID('accountId').value, $ID('ticketId').value);
				}
	
				function updatedContacts(response)
				{
					populateContactList(response['contacts']);
				}
	
				function addContact()
				{
					var accountIdInput = $ID('accountId');
					if (accountIdInput.className == 'invalid' || accountIdInput.value == '')
					{
						alert('Please enter a valid account number.');
						return false;
					}
					var accountId = accountIdInput.value;
					Ticketing_Contact.displayContact(null, accountId, addContactCallBack);
					return false;
				}
	
				function onTicketingLoad()
				{
					var accountId = $ID('accountId');
	
					if (accountId == undefined || accountId == null)
					{
						return;
					}
	
					remoteClass = 'Ticketing';
					remoteMethod = 'validateAccount';
					$validateAccounts = jQuery.json.jsonFunction(validatedAccount, null, remoteClass, remoteMethod);
					$updateContacts = jQuery.json.jsonFunction(updatedContacts, null, remoteClass, remoteMethod);
	
					Event.observe(accountId, 'blur', accountNumberChange);
					Event.observe(accountId, 'keyup', accountNumberChange);
					Event.observe(accountId, 'change', accountNumberChange);
				}
			
				Event.observe(window, 'load', onTicketingLoad, false);
				//-->
			</script>
			<form id="edit_ticket" method="POST" name="edit_ticket" action="<?php echo Flex::getUrlBase() . "reflex.php/Ticketing/Ticket/" . ($ticket->isSaved() ? $ticket->id . '/' : '') . $requestedAction; ?>">
				<input type="hidden" name="save" value="1" />
				<input type="hidden" id="ticketId" value="<?php echo $ticket->id ? $ticket->id : ''; ?>" />
				<table id="ticketing" name="ticketing" class="reflex">
					<caption>
						<div id="caption_bar" name="caption_bar">
						<div id="caption_title" name="caption_title">
							Viewing ticket: <?=$ticket->id?>
						</div>
						<div id="caption_options" name="caption_options">
							<?=$actionLinks?>
						</div>
						</div>
					</caption>
					<thead>
						<tr>
							<th colspan="2">&nbsp;</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="2">
								<input type="button" class="reflex-button" value="Cancel" onclick="document.location='<?=$cancel?>'" />
								<input type="button" class="reflex-button" value="Save" onclick="$ID('edit_ticket').submit()" />
							</th>
						</tr>
					</tfoot>
					<tbody>
						<tr class="alt">
							<td class="title">Subject: </td>
							<td>
								<?php
								$invalid = array_key_exists('subject', $invalidValues) ? 'invalid' : '';
								if (array_search('subject', $editableValues) !== FALSE)
								{
									?><input type="text" id="subject" name="subject" class="<?=$invalid?>" size="50" value="<?=htmlspecialchars($ticket->subject)?>" /><?php
								}
								else
								{
									echo htmlspecialchars(trim($ticket->subject) ? $ticket->subject : '<em>[No Subject]</em>');
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Owner: </td>
							<td>
								<?php
								$invalid = array_key_exists('ownerId', $invalidValues) ? 'invalid' : '';
								if (array_search('ownerId', $editableValues) !== FALSE)
								{
									?><select name='ownerId' class="<?=$invalid?>"><?php
									if (!$ticket->ownerId)
									{
										?><option value="" selected="selected">Unassigned</option><?php
									}
									$owners = Ticketing_User::listAll();
									foreach ($owners as $owner)
									{
										$selected = $ticket->ownerId == $owner->id ? ' selected="selected"' : '';
										?><option value="<?=$owner->id?>"<?=$selected?>><?=$owner->getName()?></option><?php
									}
									?></select><?php
								}
								else
								{
									echo htmlspecialchars($ownerName);
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Priority: </td>
							<td>
								<?php
								$invalid = array_key_exists('priorityId', $invalidValues) ? 'invalid' : '';
								if (array_search('priorityId', $editableValues) !== FALSE)
								{
									?><select name='priorityId' class="<?=$invalid?>"><?php
									$priorities = Ticketing_Priority::getAvailablePrioritiesForUser(Ticketing_User::getCurrentUser());
									foreach ($priorities as $priority)
									{
										$selected = $ticket->priorityId == $priority->id ? ' selected="selected"' : '';
										?><option class="<?=$priority->cssClass?>" value="<?=$priority->id?>"<?=$selected?>><?=$priority->name?></option><?php
									}
									?></select><?php
								}
								else
								{
									echo $ticket->getPriority()->name;
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Customer Group: </td>
							<td>
								<?php
								$invalid = array_key_exists('customerGroupId', $invalidValues) ? 'invalid' : '';
								if (array_search('customerGroupId', $editableValues) !== FALSE)
								{
									?><select name='customerGroupId' class="<?=$invalid?>"><?php
									$customerGroups = Customer_Group::listAll();
									foreach ($customerGroups as $customerGroup)
									{
										$selected = $ticket->customerGroupId == $customerGroup->id ? ' selected="selected"' : '';
										?><option value="<?=$customerGroup->id?>"<?=$selected?>><?=$customerGroup->name?></option><?php
									}
									?></select><?php
								}
								else
								{
									echo $ticket->getCustomerGroup()->name;
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Account: </td>
							<td>
								<?php
								$allowCreation = FALSE;
								$invalid = array_key_exists('accountId', $invalidValues) ? 'invalid' : '';
								if (array_search('accountId', $editableValues) !== FALSE)
								{
									$accountId = $ticket && $ticket->accountId ?$ticket->accountId : ''; 
									?><input type="text" id="accountId" name="accountId" class="<?=$invalid?>" size="50" value="<?=$accountId?>" /><?php
									$allowCreation = TRUE;
								}
								else
								{
									echo $ticket->accountId ? ('<a href="' . Href()->AccountOverview($ticket->accountId) . '">' . $ticket->accountId . '</a>') : '[Not matched to an account]';
									//echo $ticket->accountId ? $ticket->accountId : '[Not matched to an account]';
									if ($ticket->accountId)
									{
										echo '<input type="hidden" id="accountId" value="' . $ticket->accountId . '" />';
										$allowCreation = TRUE;
									}
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Contact: </td>
							<td>
								<?php
								$invalid = array_key_exists('contactId', $invalidValues) ? 'invalid' : '';
								if (array_search('contactId', $editableValues) !== FALSE)
								{
									?><select id="contactId" name='contactId' class="<?=$invalid?>"><?php
									$contactId = $ticket && $ticket->contactId ? $ticket->contactId : NULL;
									$contacts = Ticketing_Contact::listForAccountAndTicket($ticket->accountId, $ticket);
									foreach ($contacts as $contact)
									{
										$selected = $contactId == $contact->id ? ' selected="selected"' : '';
										?><option value="<?=$contact->id?>"<?=$selected?>><?=htmlspecialchars($contact->getName())?></option><?php
									}
									?></select>
									<input type="button" class="reflex-button" onclick='viewContactDetails();return false;' value='view' />
									<?php
									if ($allowCreation)
									{
										?>
										<input type="button" class="reflex-button" onclick='addContact();return false;' value='add' />
										<?php
									}
									if (array_search('accountId', $editableValues) !== FALSE)
									{
										echo htmlspecialchars(' (Change account to see available contacts)');
									}
								}
								else
								{
									echo $contactName;
									$allowCreation = FALSE;
									echo '<input type="hidden" id="contactId" value="' . $ticket->contactId . '" />';
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Status: </td>
							<td>
								<?php
								$invalid = array_key_exists('statusId', $invalidValues) ? 'invalid' : '';
								if (array_search('statusId', $editableValues) !== FALSE)
								{
									?><select name='statusId' class="<?=$invalid?>"><?php
									$statuses = Ticketing_Status::getAvailableStatusesForUserAndTicket(Ticketing_User::getCurrentUser(), $ticket);
									foreach ($statuses as $status)
									{
										$selected = $ticket->statusId == $status->id ? ' selected="selected"' : '';
										?><option class="<?=$status->cssClass?>" value="<?=$status->id?>"<?=$selected?>><?=htmlspecialchars($status->name)?></option><?php
									}
									?></select><?php
								}
								else
								{
									echo $ticket->getStatus()->name;
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Category: </td>
							<td>
								<?php
								$invalid = array_key_exists('categoryId', $invalidValues) ? 'invalid' : '';
								if (array_search('categoryId', $editableValues) !== FALSE)
								{
									echo "<select name='categoryId' class=\"$invalid\">";
									$categories = Ticketing_Category::getAvailableCategoriesForUser(Ticketing_User::getCurrentUser());
									foreach ($categories as $category)
									{
										$selected = $ticket->categoryId == $category->id ? ' selected="selected"' : '';
										echo "<option class=\"" . $category->cssClass . "\" value=\"$category->id\"$selected\">" . htmlspecialchars($category->name) . "</option>";
									}
									echo "</select>";
								}
								else
								{
									echo $ticket->getCategory()->name;
								}
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Services: </td>
							<td>
								<?php
								$invalid = array_key_exists('serviceId', $invalidValues) ? 'invalid' : '';
								if (array_search('serviceId', $editableValues) !== FALSE)
								{
									echo "<select id=\"serviceId\" multiple=\"multiple\" name=\"serviceId[]\" size=\"5\" class=\"$invalid\">";
									$services = $this->mxdDataToRender['services'];
									$ticketServices = $ticket->getServices();
									foreach ($services as $service)
									{
										$serviceId = $service['service_id'];
										$fnn = $service['fnn'];
										$selected = array_key_exists($serviceId, $ticketServices) ? ' selected="selected"' : '';
										echo "<option value=\"$serviceId\"$selected\">" . htmlspecialchars($fnn) . "</option>";
									}
									echo "</select>";
									if (array_search('accountId', $editableValues) !== FALSE)
									{
										echo htmlspecialchars(' (Change account to see available services)');
									}
								}
								else
								{
									$ticketServices = $ticket->getServices();
									$output = array();
									foreach ($ticketServices as $service)
									{
										$output[] = htmlspecialchars($service->getFNN());
									}
									$output = implode('<br/>', $output);
									echo $output ? $output : '[No related services]';
								}
								?>
							</td>
						</tr>
		<?php
		if($ticket->isSaved())
		{
		?>
						<tr class="alt">
							<td class="title">Created: </td>
							<td><?=$ticket->creationDatetime?></td>
						</tr>
						<tr class="alt">
							<td class="title">Last Modified: </td>
							<td><?=$ticket->modifiedDatetime?></td>
						</tr>
		<?php
		}
		?>
					</tbody>
				</table>
			</form>
	


		<?php

		$this->render_correspondences($ticket);
	}

	private function no_ticket($header='Error', $message='No ticket selected.')
	{
		?>

		<table class="reflex">
			<caption>
				<div id="caption_bar" name="caption_bar">
					<div id="caption_title" name="caption_title">
						<?=htmlspecialchars($header)?>
					</div>
					<div id="caption_options" name="caption_options">
					</div>
				</div>
			</caption>
			<thead class="header">
				<tr>
					<th colspan="2">&nbsp;</th>
				</tr>
			</thead>
			<tfoot class="footer">
				<tr>
					<th colspan="2">&nbsp;</th>
				</tr>
			</tfoot>
			<tbody>
				<tr class="alt">
					<td colspan="2" class="title"><?=htmlspecialchars($message)?></td>
				</tr>
			</tbody>
		</table>

		<?php
	}


	private function render_correspondences($ticket)
	{
		if (!$ticket || !$ticket->isSaved())
		{
			return;
		}

		$correspondences = $ticket->getCorrespondences();
		$noCorrespondences = count($correspondences) == 0;

		$actionLinks = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/Create/" . $ticket->id . "\">Create</a>";

		?>
		<br/>
		<table class="reflex">
			<caption>
				<div id="caption_bar" name="caption_bar">
				<div id="caption_title" name="caption_title">
					Correspondences
				</div>
				<div id="caption_options" name="caption_options">
					<?=$actionLinks?>
				</div>
				</div>
			</caption>
			<thead class="header">
				<tr>
					<th>Subject</th>
					<th>Contact</th>
					<th>Source</th>
					<th>Delivery Status</th>
					<th>Delivery Date/Time</th>
					<th>Creation Date/Time</th>
				</tr>
			</thead>
			<tfoot class="footer">
				<tr>
					<th colspan="6">&nbsp;</th>
				</tr>
			</tfoot>
			<tbody>
		<?php
					if ($noCorrespondences)
					{
		?>
				<tr class="alt">
					<td colspan="6">There are no correspondences for this ticket.</td>
				</tr>
		<?php
					}
					else
					{
						$alt = FALSE;
						foreach($correspondences as $correspondence)
						{
							$altClass = $alt ? ' class="alt"' : '';
							$alt = !$alt;
							$contact = $correspondence->getContact();
							$contactName = $contact ? $contact->getName() : '[No contact]';
							$sourceName = $correspondence->getSource()->name;
							$deliveryStatusName = $correspondence->getDeliveryStatus()->name;
							$link = Flex::getUrlBase() . 'reflex.php/Ticketing/Correspondence/' . $correspondence->id . '/View';
		?>
				<tr<?=$altClass?>>
					<td><a href="<?=$link?>"><?=$correspondence->summary ? $correspondence->summary : '<em>[No Subject]</em>'?></a></td>
					<td><?=$contactName?></td>
					<td><?=$sourceName?></td>
					<td><?=$deliveryStatusName?></td>
					<td><?=$correspondence->deliveryDatetime?></td>
					<td><?=$correspondence->creationDatetime?></td>
				</tr>
		<?php
						}
					}
		?>
			</tbody>
		</table>

		<?php
	}
}

?>
