<?php

class HtmlTemplate_Ticketing_Ticket extends FlexHtmlTemplate
{

	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('ticketing_ticket');
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
		// Check if the initial correspondence was an email that couldn't be sent
		$strWarning = "";
		if (array_key_exists("notes", $this->mxdDataToRender) && count($this->mxdDataToRender['notes']))
		{
			$arrNotes = $this->mxdDataToRender['notes'];
			foreach ($arrNotes as &$strNote)
			{
				$strNote = htmlspecialchars($strNote);
			}
			
			$strNotes = count($arrNotes)? ("<br />". implode("<br />", $arrNotes)) : "";
		}
		$this->render_view($ticket, "The ticket has been saved.{$strNotes}");
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
		$objCurrentAccount			= $this->mxdDataToRender['currentAccount'];
		$strCurrentAccountGetVar	= ($objCurrentAccount)? "Account={$objCurrentAccount->id}" : "";
		
		// If there is no ticket, we need to tell the user as much
		if (!$ticket)
		{
			return $this->no_ticket();
		}

		$owner			= $ticket->getOwner();
		$ownerName		= htmlspecialchars($owner ? $owner->getName() : '['.Ticketing_Status::getForId(TICKETING_STATUS_UNASSIGNED)->name.']');
		$contact		= $ticket->getContact();
		$contactName	= htmlspecialchars($contact ? $contact->getName() : '[No associated contact]');

		$lastModifiedByUser = Ticketing_User::getForId($ticket->modifiedByUserId);

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
			$actionLinks[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Ticket/$id$action/?{$strCurrentAccountGetVar}\">$action</a>";
		}
		$actionLinks = implode(' | ', $actionLinks);

		?>
		<form id="view_ticket" name="view_ticket" method="POST">
		<?php
			if ($objCurrentAccount)
			{
				echo "\t\t\t<input type='hidden' name='Account' value='{$objCurrentAccount->id}'></input>";
			}
		?>
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
								$strLink = Href()->ViewService($service->serviceId);
								$output[] = "<a href='$strLink' title='View Service'>". htmlspecialchars($service->getFNN()) ."</a>";
							}
							$output = implode(', ', $output);
							echo $output ? $output : '[No related services]';
						?></td>
					</tr>
					<tr class="alt">
						<td class="title">Created: </td>
						<td><?=date('H:i:s M j, Y', strtotime($ticket->creationDatetime))?></td>
					</tr>
					<tr class="alt">
						<td class="title">Last Modified: </td>
						<td><?=date('H:i:s M j, Y', strtotime($ticket->modifiedDatetime))?></td>
					</tr>
					<tr class="alt">
						<td class="title">Last Modified By: </td>
						<td><?=($lastModifiedByUser !== NULL? htmlspecialchars($lastModifiedByUser->getName()) : "System")?></td>
					</tr>
				</tbody>
			</table>
		</form>

		<?php

		$this->render_correspondences($ticket);
	}

	private function render_edit($ticket=FALSE, $requestedAction="Edit")
	{
		$objCurrentAccount			= $this->mxdDataToRender['currentAccount'];
		$strCurrentAccountGetVar	= ($objCurrentAccount)? "Account={$objCurrentAccount->id}" : "";
		
		if($ticket === FALSE)
		{
			$ticket = $this->mxdDataToRender['ticket'];
		}

		$owner			= $ticket->getOwner();
		$ownerName		= htmlspecialchars($owner ? $owner->getName() : '['.Ticketing_Status::getForId(TICKETING_STATUS_UNASSIGNED)->name.']');
		$contact		= $ticket->getContact();
		$contactName	= htmlspecialchars($contact ? $contact->getName() : '[No associated contact]');
		$lastModifiedByUser = Ticketing_User::getForId($ticket->modifiedByUserId);

		$message		= array_key_exists('error', $this->mxdDataToRender) ? $this->mxdDataToRender['error'] : '';

		if ($message)
		{
			$message = str_replace("\n", "<br />", htmlspecialchars($message));
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
			$actionLinks[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Ticket/$id$action/?{$strCurrentAccountGetVar}\">$action</a>";
		}
		$actionLinks = implode(' | ', $actionLinks);

		$editableValues = $this->mxdDataToRender['editable_values'];

		$invalidValues = $this->mxdDataToRender['invalid_values'];

		$cancel = Flex::getUrlBase() . '/reflex.php/Ticketing/' . ($ticket->isSaved() ? "Ticket/{$ticket->id}/View/?{$strCurrentAccountGetVar}" : "System/Last/?{$strCurrentAccountGetVar}");

		?>
			<form id="edit_ticket" method="POST" name="edit_ticket" action="<?php echo Flex::getUrlBase() . "reflex.php/Ticketing/Ticket/" . ($ticket->isSaved() ? $ticket->id . '/' : '') . $requestedAction; ?>">
				<?php
					if ($objCurrentAccount)
					{
						echo "\t\t\t\t<input type='hidden' name='Account' value='{$objCurrentAccount->id}'></input>";
					}
				?>
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
					<tbody>
						<tr class="alt">
							<td class="title">Subject: </td>
							<td>
								<?php
								$invalid = array_key_exists('subject', $invalidValues) ? 'invalid' : '';
								if (array_search('subject', $editableValues) !== FALSE)
								{
									?><input type="text" id="subject" name="subject" class="<?=$invalid?>" style='width:100%' value="<?=htmlspecialchars($ticket->subject)?>" /><?php
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
									$owners = Ticketing_User::listAllActive();
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
								$objCustomerGroup		= $ticket->getCustomerGroup();
								$strCustomerGroupName	= ($objCustomerGroup)? $objCustomerGroup->name : "";
								echo "<span id='customerGroupName'>$strCustomerGroupName</span>";
								?>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Account: </td>
							<td>
								<?php
								$allowCreation = FALSE;
								$invalid = array_key_exists('accountId', $invalidValues) ? 'invalid' : '';
								if ($objCurrentAccount)
								{
									// Don't allow them to change the account
									echo "<a href='". Href()->AccountOverview($objCurrentAccount->id) . "'>{$objCurrentAccount->id}</a>\n";
									echo "<input type='hidden' id='accountId' name='accountId' value='{$objCurrentAccount->id}' />";
									$allowCreation = TRUE;
								}
								elseif (array_search('accountId', $editableValues) !== FALSE)
								/*if (array_search('accountId', $editableValues) !== FALSE)*/
								{
									$accountId = $ticket && $ticket->accountId ?$ticket->accountId : ''; 
									?><input type="text" id="accountId" name="accountId" class="<?=$invalid?>" size="50" value="<?=$accountId?>" /><?php
									if ($ticket && $ticket->id)
									{
										echo "<input type='button' class='reflex-button' onclick='revertTicketDetails({$ticket->id})' value='Revert to saved account details' />";
									}
									$allowCreation = TRUE;
								}
								else
								{
									echo $ticket->accountId ? ('<a href="' . Href()->AccountOverview($ticket->accountId) . '">' . $ticket->accountId . '</a>') : '[Not matched to an account]';
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
									echo "<select name='categoryId' id='categoryId' class=\"$invalid\">";
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
									if (array_key_exists('serviceId', $_REQUEST))
									{
										$ticketServices = (is_array($_REQUEST['serviceId']))? array_combine($_REQUEST['serviceId'], $_REQUEST['serviceId']) : array($_REQUEST['serviceId']=>$_REQUEST['serviceId']);
									}
									else
									{
										$ticketServices = $ticket->getServices();
									}
									foreach ($services as $service)
									{
										$serviceId					= $service['service_id'];
										$fnn						= $service['fnn'];
										$serviceStatusDescription	= $service['status_description'];
										$selected					= array_key_exists($serviceId, $ticketServices) ? ' selected="selected"' : '';
										echo "<option value=\"$serviceId\"$selected>" . htmlspecialchars($fnn ." (". $serviceStatusDescription .")") ."</option>";
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
						<tr class="alt">
							<td class="title">Last Modified By: </td>
							<td><?=($lastModifiedByUser !== NULL? htmlspecialchars($lastModifiedByUser->getName()) : "System")?></td>
						</tr>
		<?php
		}
		if ($requestedAction == "Create")
		{
			// Include fields for the initial correspondence object
			?>
						<tr class="alt">
							<td class="title" colspan='2'>Initial Correspondence: </td>
						</tr>
						<tr class="alt">
							<td class="title">Customer Group Email: </td>
							<td>
								<?php
									$invalid = array_key_exists('customerGroupEmailId', $invalidValues) ? 'invalid' : '';
									?><select id="customerGroupEmailId" name='customerGroupEmailId' class="<?=$invalid?>"><?php
									$arrCustomerGroupEmails	= array_key_exists('customerGroupEmails', $this->mxdDataToRender)? $this->mxdDataToRender['customerGroupEmails'] : array();
									$objCustomerGroupConfig	= array_key_exists('customerGroupConfig', $this->mxdDataToRender)? $this->mxdDataToRender['customerGroupConfig'] : NULL;
									$intCustomerGroupEmailId = NULL;
									if (array_key_exists('customerGroupEmailId', $_REQUEST))
									{
										$intCustomerGroupEmailId = $_REQUEST['customerGroupEmailId'];
									}
									elseif ($objCustomerGroupConfig != NULL)
									{
										$intCustomerGroupEmailId = $objCustomerGroupConfig->defaultEmailId;
									}
									
									foreach ($arrCustomerGroupEmails as $objCustomerGroupEmail)
									{
										$selected = $intCustomerGroupEmailId == $objCustomerGroupEmail->id ? ' selected="selected"' : '';
										?><option value="<?=$objCustomerGroupEmail->id?>"<?=$selected?>><?=htmlspecialchars($objCustomerGroupEmail->name . ' (' . $objCustomerGroupEmail->email . ')')?></option><?php
									}
									?></select>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Source: </td>
							<td>
								<?php
									$invalid = array_key_exists('sourceId', $invalidValues) ? 'invalid' : '';
									?><select id="sourceId" name='sourceId' class="<?=$invalid?>"><?php
									$intSourceId = NULL;
									if (array_key_exists('sourceId', $_REQUEST))
									{
										$intSourceId = $_REQUEST['sourceId'];
									}
									
									$arrSources = Ticketing_Correspondance_Source::getAvailableSourcesForUser();
									foreach ($arrSources as $objSource)
									{
										$selected = $intSourceId == $objSource->id ? ' selected="selected"' : '';
										?><option class="<?=$objSource->cssClass?>" value="<?=$objSource->id?>"<?=$selected?>><?=htmlspecialchars($objSource->name)?></option><?php
									}
									?></select>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Delivery Status: </td>
							<td>
								<?php
									$invalid = array_key_exists('deliveryStatusId', $invalidValues) ? 'invalid' : '';
									?><select id="deliveryStatusId" name='deliveryStatusId' class="<?=$invalid?>"><?php
									$intDeliveryStatusId = NULL;
									if (array_key_exists('deliveryStatusId', $_REQUEST))
									{
										$intDeliveryStatusId = $_REQUEST['deliveryStatusId'];
									}
									$arrDeliveryStatuses = Ticketing_Correspondance_Delivery_Status::getAvailableStatusesForUser();
									foreach ($arrDeliveryStatuses as $objDeliveryStatus)
									{
										$selected = $intDeliveryStatusId == $objDeliveryStatus->id ? ' selected="selected"' : '';
										?><option class="<?=$objDeliveryStatus->cssClass?>" value="<?=$objDeliveryStatus->id?>"<?=$selected?>><?=htmlspecialchars($objDeliveryStatus->name)?></option><?php
									}
									?></select>
							</td>
						</tr>
						<?php
							// Handle the displaying of notes specific to the Category (but only for new tickets)
							// We currently only have notes for the "Fault" category
							$strDisplay = ($ticket->categoryId == TICKETING_CATEGORY_FAULTS)? "display:table-row;" : "display:none";
						?>
						<tr class="alt TicketCategoryNotes" style='<?=$strDisplay?>' id='<?=TICKETING_CATEGORY_FAULTS?>'>
							<td class="title">Details to Record: </td>
							<td style='width:100%'>
								<ul>
									<li>Fault Description - What is the issue the customer is experiencing?</li>
									<li>Has an isoloation test been completed?</li>
									<li>Has a VA been completed for incorrect call out fee?</li>
									<li>Does the customer require a diversion?
										<ul>
											<li>To which number?</li>
											<li>Have you advised customer of applicable diversion call charges</li>
										</ul>
									</li>
									<li>Is this number in a line hunt?
										<ul>
											<li>Do we need to isolate the number from the line hunt?</li>
										</ul>
									</li>
									<li>What is the contact's availability if a technician is required to attend onsite?</li>
									<li>Advise customer that the Estimated Time of Restoration is 2 business days and we will provide updates as they become available.</li>
								</ul>
							</td>
						</tr>
						<tr class="alt">
							<td class="title">Details: </td>
							<td style='width:100%'>
								<?php
									$invalid = array_key_exists('details', $invalidValues) ? 'invalid' : '';
									$strDetails = array_key_exists("details", $_REQUEST)? $_REQUEST['details'] : "";
									?>
									<textarea id="details" name="details" class="<?=$invalid?>" style="width:100%;position:relative; min-height: 16em"><?=htmlspecialchars($strDetails)?></textarea>
							</td>
						</tr>
			<?php
		}
		
		?>
					</tbody>
					<tfoot>
						<tr>
							<th colspan="2">
								<input type="button" class="reflex-button" value="Cancel" onclick="document.location='<?=$cancel?>'" />
								<input type="button" class="reflex-button" value="Save" onclick="$ID('edit_ticket').submit()" />
							</th>
						</tr>
					</tfoot>
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
		$objCurrentAccount			= $this->mxdDataToRender['currentAccount'];
		$strCurrentAccountGetVar	= ($objCurrentAccount)? "Account={$objCurrentAccount->id}" : "";
		
		if (!$ticket || !$ticket->isSaved())
		{
			return;
		}

		$correspondences = $ticket->getCorrespondences();
		$noCorrespondences = count($correspondences) == 0;

		$actionLinks = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/Create/{$ticket->id}/?{$strCurrentAccountGetVar}\">Create</a>";

		$strGenericAttachmentLink = Flex::getUrlBase() . "reflex.php/Ticketing/Attachment/";

		$arrAttachmentStatuses = Ticketing_Attachment_Blacklist_Status::listAll();
		
		$bolIsTicketingAdminUser = Ticketing_User::currentUserIsTicketingAdminUser();

		?>
		<br/>
		<table class="reflex" id='ticket_correspondence_table'>
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
					<th>Delivered</th>
					<th>Created</th>
					<th>Actions</th>
					<th><input type='button' onclick='toggleShowAllCorrespondenceDetails(this)' class='expand-button-expanded' style='float:right'></input></th>
				</tr>
			</thead>
			<tbody>
		<?php
					if ($noCorrespondences)
					{
		?>
				<tr class="alt">
					<td colspan="8">There are no correspondences for this ticket.</td>
				</tr>
		<?php
					}
					else
					{
						$alt = FALSE;
						$bolFirst = TRUE;
						foreach($correspondences as $correspondence)
						{
							$altClass = $alt ? 'alt' : '';
							
							$strContentClass = ($bolFirst)? "displayed-ticket-correspondence-content" : "hidden-ticket-correspondence-content";
							$strCorrespondenceClass = ($bolFirst)? "ticket-correspondence-summary-with-content" : "ticket-correspondence-summary-without-content";
							$strExpandButtonClass = ($bolFirst)? "expand-button-expanded" : "expand-button-retracted";
							$bolFirst = FALSE;
							
							$alt = !$alt;
							
							$contact			= $correspondence->getContact();
							$contactName		= $contact ? $contact->getName() : '[No contact]';
							$sourceName			= $correspondence->getSource()->name;
							$deliveryStatusName	= $correspondence->getDeliveryStatus()->name;
							$link				= Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/{$correspondence->id}/View/?{$strCurrentAccountGetVar}";
							
							$strDeliveryTimestamp	= ($correspondence->deliveryDatetime === NULL)? "" : date("H:i:s d-m-Y", strtotime($correspondence->deliveryDatetime));
							$strCreationTimestamp	= ($correspondence->creationDatetime === NULL)? "" : date("H:i:s d-m-Y", strtotime($correspondence->creationDatetime));
							
							$strExpandButton = "<input type='button' onclick='toggleShowCorrespondenceDetails({$correspondence->id}, this)' class='$strExpandButtonClass' style='float:right'></input>";
							
							$arrActions = array();
							if ($correspondence->deliveryStatusId == TICKETING_CORRESPONDANCE_DELIVERY_STATUS_NOT_SENT && $correspondence->isEmail() && $correspondence->isOutgoing())
							{
								// The user can send the email
								$arrActions[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/{$correspondence->id}/Send/?{$strCurrentAccountGetVar}\">Send</a>";
							}
							$strActions = implode(" | ", $arrActions);
							
							$strDetails = "<em>Content:</em><div class='details'>". nl2br(htmlspecialchars(trim($correspondence->details))) ."</div>";
							
							// Grab the Attachments (minus the actual file so as not to unneccessarily slow things down)
							$arrAttachments = Ticketing_Attachment::listForCorrespondence($correspondence);
							$attachmentTypes = Ticketing_Attachment_Type::listAll();
							$arrAttachmentRows = array();
							foreach ($arrAttachments as $attachment)
							{
								$strFilename	= htmlspecialchars($attachment->fileName);
								$attachmentType	= $attachmentTypes[$attachment->attachmentTypeId];
								$strType		= htmlspecialchars($attachmentType->mimeType);
								$status			= $arrAttachmentStatuses[$attachmentType->blacklistStatusId];
								
								$bolIsDownloadable		= (!$status->isBlacklisted() || $attachment->allowBlacklistOverride()) ? TRUE : FALSE;
								$strFilenameGroupClass	= ($bolIsDownloadable) ? "downloadable-attachment" : "blocked-downloadable-attachment";
								
								if ($status->isBlacklisted() && $bolIsTicketingAdminUser)
								{
									// The ticket is blacklisted and the user can toggle whether or not to override this
									$strToggleLabel = ($bolIsDownloadable)? "Block Download" : "Unblock Download";
									$strToggleOverrideButtton = "<input type='button' id='correspondence_attachment_toggle_{$attachment->id}' class='reflex-button' attachmentId='{$attachment->id}' onclick='toggleAttachmentBlacklistOverride(this)' value='$strToggleLabel'></input>";
								}
								else
								{
									$strToggleOverrideButtton = "";
								}
								
								$strFilenameGroup = "<span id='correspondence_attachment_{$attachment->id}' class='{$strFilenameGroupClass}'>
														<a class='active' href='{$strGenericAttachmentLink}{$attachment->id}'>$strFilename</a>
														<span class='inactive'>$strFilename</span>
													</span>";
													
								$arrAttachmentRows[] = "$strFilenameGroup - $strType - {$status->name} $strToggleOverrideButtton\n";
							}
							
							if (count($arrAttachmentRows))
							{
								$strAttachments = "<em>Attachments:</em><div class='attachments'>". implode('<br />', $arrAttachmentRows) ."</div>";
							}
							else
							{
								$strAttachments = "";
							}
							
							
		?>
				<tr class="<?=$strCorrespondenceClass?> <?=$altClass?>" altClass='<?=$altClass?>' id='ticket_correspondence_<?=$correspondence->id?>'>
					<td><a href="<?=$link?>"><?=$correspondence->summary ? $correspondence->summary : '<em>[No Subject]</em>'?></a></td>
					<td><?=$contactName?></td>
					<td><?=$sourceName?></td>
					<td><?=$deliveryStatusName?></td>
					<td><?=$strDeliveryTimestamp?></td>
					<td><?=$strCreationTimestamp?></td>
					<td><?=$strActions?></td>
					<td><?=$strExpandButton?></td>
				</tr>
				<tr class='<?=$strContentClass?> <?=$altClass?>' altClass='<?=$altClass?>' id='ticket_correspondence_content_<?=$correspondence->id?>'>
					<td colspan='8' class='ticket-correspondence-content'>
						<?=$strDetails?>
						<?=$strAttachments?>
					</td>
				</tr>
		<?php
						}
					}
		?>
			</tbody>
			<tfoot class="footer">
				<tr>
					<th colspan="8">&nbsp;</th>
				</tr>
			</tfoot>
		</table>

		<?php
	}
}

?>
