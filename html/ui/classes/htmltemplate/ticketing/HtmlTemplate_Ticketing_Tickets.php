<?php

class HtmlTemplate_Ticketing_Tickets extends FlexHtmlTemplate
{
	public function Render()
	{
		$nrTickets = $this->mxdDataToRender['ticket_count'];
		$count = count($this->mxdDataToRender['tickets']);
		$startOffset = ($this->mxdDataToRender['offset'] + ($count ? 1 : 0));
		$endOffset = $this->mxdDataToRender['offset'] + $count;
		$title = "Viewing $startOffset to $endOffset of $nrTickets Tickets";

		$ownerId = array_key_exists('ownerId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['ownerId']['value'] : NULL;
		$categoryId = array_key_exists('categoryId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['categoryId']['value'] : NULL;
		$statusId = array_key_exists('statusId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['statusId']['value'] : NULL;

		$selected = ' SELECTED="SELECTED"';
		?>
<form method="GET">
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
			$strSelected = $ownerId === $id ? $selected : '';
			echo "\t\t\t\t\t<option value=\"$id\"$strSelected>$name</option>\n";
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
			$strSelected = $categoryId === $id ? $selected : '';
			echo "\t\t\t\t\t<option value=\"$id\"$strSelected>$name</option>\n";
		}
?>
				</select>
				Status: 
				<select id="statusId" name="statusId">
					<option value=""<?=($statusId===NULL ? $selected : '')?>>all</option>
<?php
		foreach ($this->mxdDataToRender['statuses'] as $item)
		{
			$id = $item->id;
			$name = htmlspecialchars($item->name);
			$strSelected = $statusId === $id ? $selected : '';
			echo "\t\t\t\t\t<option value=\"$id\"$strSelected>$name</option>\n";
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
				<th>Actions</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan=8 align=right> <a href="reflex.php/Ticketing/System/?last=1&offset=48">Next</a> Total Tickets: <?php echo $this->mxdDataToRender['ticket_count']; ?></th>
			</tr>
		</tfoot>
		<tbody>
	
		<?

		$i = 0;
		foreach ($this->mxdDataToRender['tickets'] as $ticket) 
		{
			$tr_alt = ($i++ % 2) ? "alt" : "";
			$owner = $ticket->getOwner();
			$ownerName = $owner ? $owner->getName() : "&nbsp;";
			$category = $ticket->getCategory();
			$categoryColClass = strtolower(str_replace('_', '-', $category->constant));
			$status = $ticket->getStatus();
			$statusColClass = strtolower(str_replace('_', '-', $status->constant));
		?>

			<tr class="<?=$tr_alt?>">
				<td><a href="reflex.php/Ticketing/Ticket/View?ticketId=<?php echo $ticket->id; ?>"><?php echo $ticket->id; ?></a></td>
				<td><?php echo $ticket->subject; ?></td>
				<td><?php echo $ticket->modifiedDatetime; ?></td>
				<td><?php echo $ticket->creationDatetime; ?></td>
				<td><?php echo $ownerName; ?></td>
				<td class="<?=$categoryColClass?>"><?php echo $category->name; ?></td>
				<td class="<?=$statusColClass?>"><?php echo $status->name; ?></td>
				<td>[actions]</td>
			</tr>
		<? 
		}

		?>
		</tbody>
	</table>
</form>
		<?php
	}
}

?>
