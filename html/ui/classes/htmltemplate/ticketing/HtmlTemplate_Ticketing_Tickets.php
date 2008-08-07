<?php

class HtmlTemplate_Ticketing_Tickets extends FlexHtmlTemplate
{
	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		$this->LoadJavascript('reflex_popup');
		$this->LoadJavascript('ticketing_contact');
	}

	public function Render()
	{
		$nrTickets = $this->mxdDataToRender['ticket_count'];
		$count = count($this->mxdDataToRender['tickets']);
		$startOffset = ($this->mxdDataToRender['offset'] + ($count ? 1 : 0));
		$endOffset = $this->mxdDataToRender['offset'] + $count;
		$limit = $this->mxdDataToRender['limit'];
		$title = "Viewing $startOffset to $endOffset of $nrTickets Tickets";

		$ownerId = array_key_exists('ownerId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['ownerId']['value'] : NULL;
		$categoryId = array_key_exists('categoryId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['categoryId']['value'] : NULL;
		$statusId = array_key_exists('statusId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['statusId']['value'] : NULL;
		if (is_array($statusId))
		{
			$statusId = implode(',', $statusId);
		}

		$selected = ' SELECTED="SELECTED"';

		$target = MenuItems::TicketingConsole();

		$arrNavLinks = array();

		if ($this->mxdDataToRender['offset'] > 0)
		{
			$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=0\">First</a>";
			$offset = $this->mxdDataToRender['offset'] - $limit;
			$offset = $offset < 0 ? 0 : $offset;
			$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=$offset\">Previous</a>";
		}

		if ($nrTickets > $endOffset)
		{
			$nrLastPage = $nrTickets % $limit;
			$nrLastPage = $nrLastPage ? $nrLastPage : $limit;
			if ($nrLastPage)
			{
				$offset = $nrTickets - $nrLastPage;
				$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=$endOffset\">Next</a>";
				$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=$offset\">Last</a>";
			}
		}

		$navLinks = implode('&nbsp;&nbsp;', $arrNavLinks);

		?>
<form method="GET" action="<?=$target?>">
	<table id="ticketing" name="ticketing" class="reflex">
		<caption>
			<div id="caption_bar" name="caption_bar">
				<div id="caption_title" name="caption_title">
					<?=$title?>
				</div>
				<div id="caption_options" name="caption_options">
				Owner: 
				<select id="ownerId" name="ownerId">
					<option value=""<?=($ownerId===NULL ? $selected : '')?>>all</option>

<?php
		foreach ($this->mxdDataToRender['users'] as $user)
		{
			$id = $user->id;
			$name = htmlspecialchars($user->getName());
			$class = $item->cssClass;
			$strSelected = $ownerId === $id ? $selected : '';
			echo "\t\t\t\t\t<option class=\"$class\" value=\"$id\"$strSelected>$name</option>\n";
		}
?>
				</select>
				Category: 
				<select id="categoryId" name="categoryId">
					<option value=""<?=($categoryId===NULL ? $selected : '')?>>all</option>
<?php
		foreach ($this->mxdDataToRender['categories'] as $item)
		{
			$id = $item->id;
			$name = htmlspecialchars($item->name);
			$class = $item->cssClass;
			$strSelected = $categoryId === $id ? $selected : '';
			echo "\t\t\t\t\t<option class=\"$class\" value=\"$id\"$strSelected>$name</option>\n";
		}
?>
				</select>
				Status: 
				<select id="statusId" name="statusId">
					<option value=""<?=($statusId===NULL ? $selected : '')?>>all</option>
<?php
		foreach ($this->mxdDataToRender['statuses'] as $item)
		{
			$id = $item->getStatusIds();
			$name = htmlspecialchars($item->name);
			$class = $item->cssClass;
			$strSelected = $statusId === $id ? $selected : '';
			echo "\t\t\t\t\t<option class=\"$class\" value=\"$id\"$strSelected>$name</option>\n";
		}
?>
				</select>
				<input type="submit" value="Go"/>
				</div>
			</div>
		</caption>
		<thead>
			<tr>
				<th>ID</th>
				<th>Subject</th>
				<th>Last Actioned</th>
				<th>Received</th>
				<th>Owner</th>
				<th>Category</th>
				<th>Status</th>
				<th>Priority</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan=9 align=right>&nbsp;<?=$navLinks?></th>
			</tr>
		</tfoot>
		<tbody>

		<?

		$i = 0;
		$noRecords = TRUE;
		foreach ($this->mxdDataToRender['tickets'] as $ticket) 
		{
			$noRecords = FALSE;
			$tr_alt = ($i++ % 2) ? "alt" : "";
			$owner = $ticket->getOwner();
			$ownerName = $owner ? $owner->getName() : "&nbsp;";
			$category = $ticket->getCategory();
			$status = $ticket->getStatus();
			$priority = $ticket->getPriority();
		?>

			<tr class="<?=$tr_alt?>">
				<td><a href="reflex.php/Ticketing/Ticket/<?=$ticket->id?>/View"><?php echo $ticket->id; ?></a></td>
				<td><?php echo $ticket->subject; ?></td>
				<td><?php echo $ticket->modifiedDatetime; ?></td>
				<td><?php echo $ticket->creationDatetime; ?></td>
				<td><?php echo $ownerName; ?></td>
				<td class="<?=$category->cssClass?>"><?php echo $category->name; ?></td>
				<td class="<?=$status->cssClass?>"><?php echo $status->name; ?></td>
				<td class="<?=$priority->cssClass?>"><?php echo $priority->name; ?></td>
				<td>[actions]</td>
			</tr>
		<? 
		}

		if ($noRecords)
		{
			?>
			<tr><td colspan="8">No tickets match your current filter.</td></tr>
			<?php
		}

		?>
		</tbody>
	</table>
</form>
		<?php
	}
}

?>
