<?php

class HtmlTemplate_Ticketing_Ticket extends FlexHtmlTemplate
{
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
		$this->render_reassign($ticket, TRUE);
	}

	private function render_reassign($ticket, $assigned=FALSE)
	{
		$targetUser = $this->mxdDataToRender['targetUser'];
		if ($targetUser)
		{
			$this->render_view($ticket, "This ticket has been {$re}assigned to " . $targetUser->getName() . ".");
		}
		else
		{
			$this->render_edit($ticket, "Re-assign");
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

		<form id="view_ticket" name="view_ticket" target="">
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
						<td><?=htmlspecialchars($ticket->subject)?></td>
					</tr>
					<tr class="alt">
						<td class="title">Owner: </td>
						<td><?=$ownerName?></td>
					</tr>
					<tr class="alt">
						<td class="title">Priority: </td>
						<td><?=$ticket->getPriority()->name?></td>
					</tr>
					<tr class="alt">
						<td class="title">Customer Group: </td>
						<td><?=$ticket->getCustomerGroup()->name?></td>
					</tr>
					<tr class="alt">
						<td class="title">Account: </td>
						<td><?=$ticket->accountId ? $ticket->accountId : '[Not matched to an account]'?></td>
					</tr>
					<tr class="alt">
						<td class="title">Contact: </td>
						<td><?=$contactName?></td>
					</tr>
					<tr class="alt">
						<td class="title">Status: </td>
						<td><?=$ticket->getStatus()->name?></td>
					</tr>
					<tr class="alt">
						<td class="title">Category: </td>
						<td><?=$ticket->getCategory()->name?></td>
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

		$actionLinks = array();
		foreach($this->mxdDataToRender['permitted_actions'] as $action)
		{
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

		// WIP :: Sort this out properly!!
		/*
		<script>
		
			function onTicketingLoad()
			{
		
				funcSuccess = function() 
				{
					for (var i = 0, l = arguments.length; i < l; i++)
					{
						alert(i + " = " + arguments[i]);
					}
				}
		
				funcFailure = function() {
					alert('failed!');
				}
		
				remoteClass = 'Ticketing';
				remoteMethod = 'validateAccount';
				jsonFunc = jQuery.json.jsonFunction(funcSuccess, funcFailure, remoteClass, remoteMethod);
				jsonFunc('1', 2, 'Hello world!');
			}
		
			Event.observe(window, 'load', onTicketingLoad, false);
		
		</script>
		*/

		?>
		<script>

			$validateAccounts = null;

			function accountNumberChange()
			{
				var accountId = $ID('accountId');
				var ticketId = $ID('ticketId');
				if (accountId.value.length != 10) // WIP: HACK
				{
					accountIsInvalid();
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
					accountIsInvalid();
				}
				else
				{
					accountId.className = 'valid';
				}
				populateContactList(response['contacts']);
			}

			function populateContactList(contacts)
			{
				emptyContactList();
				var contactId = $ID('contactId');
				for (var i = 0, l = contacts.length; i < l; i++)
				{
					var id = contacts[i]['id'];
					var name = contacts[i]['name'];
					var option = document.createElement('option');
					option.value = id;
					option.appendChild(document.createTextNode(name));
					contactId.appendChild(option);
				}
			}

			function emptyContactList()
			{
				var contactId = $ID('contactId');
				for (var i = contactId.childNodes.length; i >= 1; i--)
				{
					contactId.removeChild(contactId.childNodes[i-1]);
				}
			}

			function accountIsInvalid()
			{
				var accountId = $ID('accountId');
				accountId.className = 'invalid';
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

				Event.observe(accountId, 'blur', accountNumberChange);
				Event.observe(accountId, 'keyup', accountNumberChange);
				Event.observe(accountId, 'change', accountNumberChange);
			}
		
			Event.observe(window, 'load', onTicketingLoad, false);
		
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
							<input type="button" id="cancelButton" value="Cancel" onclick="document.location='<?=$cancel?>'" />
							<input type="button" id="submitButton" value="Save" onclick="$ID('edit_ticket').submit()" />
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
								echo htmlspecialchars($ticket->subject);
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
									?><option value="<?=$priority->id?>"<?=$selected?>><?=$priority->name?></option><?php
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
							$invalid = array_key_exists('accountId', $invalidValues) ? 'invalid' : '';
							if (array_search('accountId', $editableValues) !== FALSE)
							{
								$accountId = $ticket && $ticket->accountId ?$ticket->accountId : ''; 
								?><input type="text" id="accountId" name="accountId" class="<?=$invalid?>" size="50" value="<?=$accountId?>" /><?php
							}
							else
							{
								echo $ticket->accountId ? $ticket->accountId : '[Not matched to an account]';
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
								?></select><?php
								if (array_search('accountId', $editableValues) !== FALSE)
								{
									echo htmlspecialchars(' (Change account to see available contacts)');
								}
							}
							else
							{
								echo $contactName;
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
								$statuses = Ticketing_Status::getAvailableStatusesForUser(Ticketing_User::getCurrentUser());
								foreach ($statuses as $status)
								{
									$selected = $ticket->statusId == $status->id ? ' selected="selected"' : '';
									?><option value="<?=$status->id?>"<?=$selected?>><?=htmlspecialchars($status->name)?></option><?php
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
									echo "<option value=\"$category->id\"$selected\">" . htmlspecialchars($category->name) . "</option>";
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
	}

	private function no_ticket()
	{
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
			<td colspan="2" class="title">No ticket selected.</td>
		</tr>
	</tbody>
</table>

		<?php
	}
}

?>
