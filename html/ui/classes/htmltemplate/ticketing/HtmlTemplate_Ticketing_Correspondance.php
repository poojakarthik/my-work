<?php

class HtmlTemplate_Ticketing_Correspondance extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript('reflex_popup');
		$this->LoadJavascript('ticketing_contact');
	}

	public function Render()
	{
		$correspondance = $this->mxdDataToRender['correspondance'];
		$renderer = strtolower('render_'.str_replace('-', '', $this->mxdDataToRender['action']));
		if (method_exists($this, $renderer))
		{
			$this->{$renderer}($correspondance);
		}
	}

	private function render_delete($correspondance)
	{
		// This method would never be called - A deleted correspondance would not be viewed.
		// Instead, the user will have been redirected to the 'view ticket' page.
		return $this->render_view($correspondance, 'The correspondance has been deleted.');
	}

	private function render_create($correspondance)
	{
		return $this->render_edit($correspondance, 'Create');
	}

	private function render_send($correspondance)
	{
		return $this->render_view($correspondance, 'The email has been sent.');
	}

	private function render_resend($correspondance)
	{
		return $this->render_view($correspondance, 'The email has been re-sent.');
	}

	private function render_error($ticket)
	{
		$errorMessage = $this->mxdDataToRender['error'];
		$this->render_view($ticket, $errorMessage, TRUE);
	}

	private function render_view($correspondance, $message=NULL, $bolIsError=FALSE)
	{
		if (!$correspondance)
		{
			return $this->no_correspondance($message);
		}

		if ($message)
		{
			?>
		<div class="message<?=($bolIsError ? " error" : "")?>"><?=$message?></div><?php
		}

		$actionLinks = array();
		foreach($this->mxdDataToRender['permitted_actions'] as $action)
		{
			$id = '';
			$tid = '';
			if ($action !== 'create')
			{
				$id = $correspondance->id . '/';
			}
			else
			{
				$tid = '/' . $correspondance->ticketId;
			}
			$action[0] = strtoupper($action[0]);
			$action = htmlspecialchars($action);
			$actionLinks[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondance/$id$action$tid\">$action</a>";
		}
		$actionLinks = implode(' | ', $actionLinks);

		?>

<table class="reflex">
	<caption>
		<div id="caption_bar" name="caption_bar">
		<div id="caption_title" name="caption_title">
			Viewing Correspondance
		</div>
		<div id="caption_options" name="caption_options"><?=$actionLinks?>
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
			<td class="title">Created: </td>
			<td><?=htmlspecialchars($correspondance->creationDatetime)?></td>
		</tr>
		<tr class="alt">
			<td class="title">Summary: </td>
			<td><?=htmlspecialchars($correspondance->summary)?></td>
		</tr>
		<tr class="alt">
			<td class="title">User: </td>
			<td><?php
				$user = $correspondance->getUser();
				$userName = $user ? $user->getName() : '[No user selected]';
				echo htmlspecialchars($userName);
			?></td>
		</tr>
		<tr class="alt">
			<td class="title">Contact: </td>
			<td><?php
				$contact = $correspondance->getContact();
				$contactName = $contact ? $contact->getName() : '[No contact selected]';
				echo htmlspecialchars($contactName);
				if ($contact)
				{
					?><input type="button" class="reflex-button" onclick='Ticketing_Contact.displayContact(<?=$contact->id?>, null);return false;' value='view' /><?php
				}
			?></td>
		</tr>
		<tr class="alt">
			<td class="title">Customer Group Email: </td>
			<td><?php
				$customerGroupEmail = $correspondance->getCustomerGroupEmail();
				echo htmlspecialchars($customerGroupEmail ? $customerGroupEmail->email : '[No customer group email address selected]');
			?></td>
		</tr>
		<tr class="alt">
			<td class="title">Source: </td>
			<td class="<?=$correspondance->getSource()->cssClass?>"><?=htmlspecialchars($correspondance->getSource()->name)?></td>
		</tr>
		<tr class="alt">
			<td class="title">Delivery Status: </td>
			<td class="<?=$correspondance->getDeliveryStatus()->cssClass?>"><?php
				echo htmlspecialchars($correspondance->getDeliveryStatus()->name);
			?></td>
		</tr>
		<tr class="alt">
			<td class="title">Delivery Date/Time: </td>
			<td><?=htmlspecialchars($correspondance->deliveryDatetime)?></td>
		</tr>
		<tr class="alt">
			<td class="title">Details: </td>
			<td><pre><?=htmlspecialchars($correspondance->details)?></pre></td>
		</tr>
	</tbody>
</table>

		<?php

		$this->render_attachments($correspondance);
	}

	private function render_save($correspondance)
	{
		$message = array_key_exists('email_sent', $this->mxdDataToRender) && $this->mxdDataToRender['email_sent'] 
			? "The correspondance has been saved and the email sent." 
			: "The correspondance has been saved.";
		$this->render_view($correspondance, $message);
	}


	private function render_edit($correspondance, $requestedAction="Edit")
	{
		if (!$correspondance)
		{
			return $this->no_correspondance();
		}

		$message = array_key_exists('error', $this->mxdDataToRender) ? $this->mxdDataToRender['error'] : '';

		if ($message)
		{
			?>
		<div class="message error"><?=$message?></div><?php
		}

		$actionLinks = array();
		foreach($this->mxdDataToRender['permitted_actions'] as $action)
		{
			$id = '';
			if ($action !== 'create')
			{
				$id = $correspondance->id . '/';
			}
			else
			{
				$tid = '/' . $correspondance->ticketId;
			}
			$action[0] = strtoupper($action[0]);
			$action = htmlspecialchars($action);
			$actionLinks[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondance/$id$action$tid\">$action</a>";
		}
		$actionLinks = implode(' | ', $actionLinks);

		$editableValues = $this->mxdDataToRender['editable_values'];

		$invalidValues = $this->mxdDataToRender['invalid_values'];

		$cancel = Flex::getUrlBase() . '/reflex.php/Ticketing/Ticket/' . $correspondance->ticketId . '/View';

		$editing = $requestedAction == 'Edit';
		$title = ($editing ? 'Editing Correspondance' : 'Creating Correspondance') . ' for Ticket ' . $correspondance->ticketId;

		$ticket = array_key_exists('ticket', $this->mxdDataToRender) ? $this->mxdDataToRender['ticket'] : NULL;

		?>
		<script>
//<!--
			$selectedContactValue = null;

			function onTicketingLoad()
			{
				var accountId = $ID('accountId');

				if (accountId == undefined || accountId == null)
				{
					return;
				}

				remoteClass = 'Ticketing';
				remoteMethod = 'validateAccount';
				$updateContacts = jQuery.json.jsonFunction(updatedContacts, null, remoteClass, remoteMethod);
			}
		
			Event.observe(window, 'load', onTicketingLoad, false);

			function viewContactDetails()
			{
				var accountIdInput = $ID('accountId');
				var accountId = accountIdInput ? accountIdInput.value : null;

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

			function addContact()
			{
				var accountIdInput = $ID('accountId');
				if (!accountIdInput) return;
				var accountId = accountIdInput.value;;
				Ticketing_Contact.displayContact(null, accountId, addContactCallBack);
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

			function emptyElement(el)
			{
				for (var i = el.length - 1; i >= 0; i--)
				{
					el.removeChild(el.childNodes[i]);
				}
			}

//-->		
		</script>
		<form id="edit_ticket" method="POST" name="edit_correspondance" action="<?php echo Flex::getUrlBase() . "reflex.php/Ticketing/Correspondance/" . ($correspondance->isSaved() ? $correspondance->id . '/' : '') . $requestedAction . ($correspondance->isSaved() ? '' : '/' . $correspondance->ticketId); ?>">
			<input type="hidden" name="save" value="1" />
			<input type="hidden" id="ticketId" value="<?php echo $correspondance->ticketId; ?>" />
			<table class="reflex">
				<caption>
					<div id="caption_bar" name="caption_bar">
					<div id="caption_title" name="caption_title">
						<?=$title?>
					</div>
					<div id="caption_options" name="caption_options"><?=$actionLinks?>
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
						<th colspan="2">
							<input type="button" class="reflex-button" value="Cancel" onclick="document.location='<?=$cancel?>'" />
							<input type="button" class="reflex-button" value="Save" onclick="this.form.submit()" />
						</th>
					</tr>
				</tfoot>
				<tbody>
<?php
	if ($correspondance->isSaved())
	{
?>
					<tr class="alt">
						<td class="title">Created: </td>
						<td><?=htmlspecialchars($correspondance->creationDatetime)?></td>
					</tr>
<?php
	}
?>
					<tr class="alt">
						<td class="title">Summary: </td>
						<td>
							<?php
								$invalid = array_key_exists('summary', $invalidValues) ? 'invalid' : '';
								if (array_search('summary', $editableValues) !== FALSE)
								{
									?><input type="text" id="summary" name="summary" class="<?=$invalid?>" size="50" value="<?=htmlspecialchars($correspondance->summary)?>" /><?php
								}
								else
								{
									echo htmlspecialchars($correspondance->summary);
								}
							?>
						</td>
					</tr>
					<tr class="alt">
						<td class="title">User: </td>
						<td>
							<?php
								$invalid = array_key_exists('userId', $invalidValues) ? 'invalid' : '';
								if (array_search('userId', $editableValues) !== FALSE)
								{
									?><select name='userId' class="<?=$invalid?>"><?php
									$owners = Ticketing_User::listAll();
									foreach ($owners as $owner)
									{
										$selected = $correspondance->userId == $owner->id ? ' selected="selected"' : '';
										?><option value="<?=$owner->id?>"<?=$selected?>><?=$owner->getName()?></option><?php
									}
									?></select><?php
								}
								else
								{
									$user = $correspondance->getUser();
									$userName = $user ? $user->getName() : '[No user selected]';
									echo htmlspecialchars($userName);
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
									$contactId = $correspondance->contactId ? $correspondance->contactId : NULL;
									$ticket = $ticket ? $ticket : $correspondance->getTicket();
									$accountId = $ticket->accountId ? $ticket->accountId : 'null';
									$contacts = Ticketing_Contact::listForAccountAndTicket($ticket->accountId, $ticket);
									foreach ($contacts as $contact)
									{
										$selected = $contactId == $contact->id ? ' selected="selected"' : '';
										?><option value="<?=$contact->id?>"<?=$selected?>><?=htmlspecialchars($contact->getName())?></option><?php
									}
									?></select>
									<input type="button" class="reflex-button" onclick='viewContactDetails();return false;' value='view' />
									<?php
									if ($accountId)
									{
										?>
										<input type="hidden" id="accountId" value="<?=$accountId?>" />
										<input type="button" class="reflex-button" onclick='addContact();return false;' value='add' />
										<?php
									}
								}
								else
								{
									$contact = $correspondance->getContact();
									$contactName = $contact ? $contact->getName() : '[No contact selected]';
									$ticket = $ticket ? $ticket : $correspondance->getTicket();
									$accountId = $ticket->accountId ? $ticket->accountId : 'null';
									echo htmlspecialchars($contactName);
									if ($contact)
									{
										?><input type="button" class="reflex-button" onclick='Ticketing_Contact.displayContact(<?=$ticket->contactId?>, <?=$accountId?>);return false;' value='view' /><?php
									}
								}
							?>
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Customer Group Email: </td>
						<td>
							<?php
								$invalid = array_key_exists('customerGroupEmailId', $invalidValues) ? 'invalid' : '';
								if (array_search('customerGroupEmailId', $editableValues) !== FALSE)
								{
									?><select id="customerGroupEmailId" name='customerGroupEmailId' class="<?=$invalid?>"><?php
									$custGroupEmailId = $correspondance->customerGroupEmailId ? $correspondance->customerGroupEmailId : NULL;
									$ticket = $ticket ? $ticket : $correspondance->getTicket();
									$custGroupEmails = Ticketing_Customer_Group_Email::listForCustomerGroupId($ticket->customerGroupId);
									foreach ($custGroupEmails as $custGroupEmail)
									{
										$selected = $custGroupEmailId == $custGroupEmail->id ? ' selected="selected"' : '';
										?><option value="<?=$custGroupEmail->id?>"<?=$selected?>><?=htmlspecialchars($custGroupEmail->name . ' (' . $custGroupEmail->email . ')')?></option><?php
									}
									?></select><?php
								}
								else
								{
									$customerGroupEmail = $correspondance->getCustomerGroupEmail();
									echo htmlspecialchars($customerGroupEmail ? $customerGroupEmail->email : '[No customer group email address selected]');
								}
							?>
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Source: </td>
						<td>
							<?php
								$invalid = array_key_exists('sourceId', $invalidValues) ? 'invalid' : '';
								if (array_search('sourceId', $editableValues) !== FALSE)
								{
									?><select id="sourceId" name='sourceId' class="<?=$invalid?>"><?php
									$sourceId = $correspondance->sourceId ? $correspondance->sourceId : NULL;
									$sources = Ticketing_Correspondance_Source::getAvailableSourcesForUser();
									foreach ($sources as $source)
									{
										$selected = $sourceId == $source->id ? ' selected="selected"' : '';
										?><option class="<?=$source->cssClass?>" value="<?=$source->id?>"<?=$selected?>><?=htmlspecialchars($source->name)?></option><?php
									}
									?></select><?php
								}
								else
								{
									echo htmlspecialchars($correspondance->getSource()->name);
								}
							?>
						</td>
					</tr>
					<tr class="alt">
						<td class="title">Delivery Status: </td>
						<td>
							<?php
								$invalid = array_key_exists('deliveryStatusId', $invalidValues) ? 'invalid' : '';
								if (array_search('deliveryStatusId', $editableValues) !== FALSE)
								{
									?><select id="deliveryStatusId" name='deliveryStatusId' class="<?=$invalid?>"><?php
									$deliveryStatusId = $correspondance->deliveryStatusId ? $correspondance->deliveryStatusId : NULL;
									$deliveryStatuses = Ticketing_Correspondance_Delivery_Status::getAvailableSourcesForUser();
									foreach ($deliveryStatuses as $deliveryStatus)
									{
										$selected = $deliveryStatusId == $deliveryStatus->id ? ' selected="selected"' : '';
										?><option class="<?=$deliveryStatus->cssClass?>" value="<?=$deliveryStatus->id?>"<?=$selected?>><?=htmlspecialchars($deliveryStatus->name)?></option><?php
									}
									?></select><?php
								}
								else
								{
									echo htmlspecialchars($correspondance->getDeliveryStatus()->name);
								}
							?>
						</td>
					</tr>
<?php
	if ($correspondance->isSaved())
	{
?>
					<tr class="alt">
						<td class="title">Delivery Date/Time: </td>
						<td>
							<?php
								$invalid = array_key_exists('deliveryDatetime', $invalidValues) ? 'invalid' : '';
								if (array_search('deliveryDatetime', $editableValues) !== FALSE)
								{
									?><input type="text" id="deliveryDatetime" name="deliveryDatetime" class="<?=$invalid?>" size="50" value="<?=htmlspecialchars($correspondance->deliveryDatetime)?>" /><?php
								}
								else
								{
									echo htmlspecialchars($correspondance->deliveryDatetime);
								}
							?>
						</td>
					</tr>
<?php
	}
?>
					<tr class="alt">
						<td class="title">Details: </td>
						<td>
							<?php
								$invalid = array_key_exists('details', $invalidValues) ? 'invalid' : '';
								if (array_search('details', $editableValues) !== FALSE)
								{
									?><textarea type="text" id="details" name="details" class="<?=$invalid?>" style="position: relative; width: 100%; height: 24em;"><?=htmlspecialchars($correspondance->details)?></textarea><?php
								}
								else
								{
									echo htmlspecialchars($correspondance->details);
								}
							?>
						</td>
					</tr>
				</tbody>
			</table>

		<?php

		return $this->render_attachments($correspondance);
	}

	private function render_attachments($correspondance)
	{
		if (!$correspondance || !$correspondance->isSaved())
		{
			return;
		}

		$attachments = $correspondance->getAttachments();
		$noAttachments = count($attachments) ? FALSE : TRUE;
		?>
<br/>
<table class="reflex">
	<caption>
		<div id="caption_bar" name="caption_bar">
		<div id="caption_title" name="caption_title">
			Attachments
		</div>
		<div id="caption_options" name="caption_options">
		</div>
		</div>
	</caption>
	<thead class="header">
		<tr>
			<th>File Name</th>
			<th>Type</th>
			<th>Status</th>
		</tr>
	</thead>
	<tfoot class="footer">
		<tr>
			<th colspan="3">&nbsp;</th>
		</tr>
	</tfoot>
	<tbody>
<?php
		if ($noAttachments)
		{
?>
		<tr class="alt">
			<td colspan="3">[There are no attachments]</td>
		</tr>
<?php
		}
		else
		{
			$alt = FALSE;
			foreach ($attachments as $attachment)
			{
				$altClass = $alt ? ' class="alt"' : '';
				$alt = !$alt;
				$attachmentTypeName = $attachment->getType()->mimeType;
				$blacklistStatus = $attachment->getAppliedBlacklistStatus();
				$blacklistStatusName = $blacklistStatus->name;
?>
		<tr<?=$altClass?>>
			<td><?php
				$content = htmlspecialchars($attachment->fileName);
				if (!$blacklistStatus->isBlacklisted())
				{
					$warning = '';
					if ($blacklistStatus->isGreylisted())
					{
						$warning = ' onclick="return window.confirm(\'This file may contain harmful content.\n\nClick confirm to download, or cancel.\');" ';
					}
					$content = '<a href="' . Flex::getUrlBase() . '/reflex.php/Ticketing/Attachment/' . $attachment->id . '" '.$warning.'/>' . $content . '</a>';
				}
				echo $content;
			?></td>
			<td><?=$attachmentTypeName?></td>
			<td class="<?=$blacklistStatus->cssClass?>"><?=$blacklistStatusName?></td>
		</tr>
<?php
			}
		}
?>
	</tbody>
</table>

		<?php
	}

	private function no_correspondance($message=NULL)
	{
		$error = $message ? $message : 'No correspondance selected.';
		?>

<table class="reflex">
	<caption>
		<div id="caption_bar" name="caption_bar">
		<div id="caption_title" name="caption_title">
			Error
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
			<td colspan="2" class="title"><?=$error?></td>
		</tr>
	</tbody>
</table>

		<?php
	}
}

?>
