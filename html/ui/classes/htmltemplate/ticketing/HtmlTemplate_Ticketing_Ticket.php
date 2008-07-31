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
		$this->render_view($ticket, "The changes have been saved.");
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
			$this->render_edit($ticket);
		}
	}

	private function render_take($ticket)
	{
		$this->render_view($ticket, "This ticket has been assigned to you.");
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

		<form id="view_ticket" name="view_ticket">
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
							<input type="submit" id="submit" value="Edit" />
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
						<td class="title">Contact: </td>
						<td><?=$contactName?></td>
					</tr>
					<tr class="alt">
						<td class="title">Status: </td>
						<td><?=$ticket->getStatus()->name?></td>
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

	private function render_edit($ticket)
	{
		$owner = $ticket->getOwner();
		?>

<table class="reflex">
	<caption>
		<div id="caption_bar" name="caption_bar">
		<div id="caption_title" name="caption_title">
			Add a new ticket
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
			<th colspan="2"><input type="submit" id="submit" name="submit" value="Add" /></th>
		</tr>
	</tfoot>
	<tbody>
		<form id="add_ticket" name"add_ticket">
		<tr class="alt">
			<td class="title">Subject: </td>
			<td><input type="text" id="subject" name="subject" size="50"/></td>
		</tr>
		<tr class="alt">
			<td class="title">Account: </td>
			<td><input type="text" id="subject" name="subject" size="50"/></td>
		</tr>
		</form>
	</tbody>
</table>

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
