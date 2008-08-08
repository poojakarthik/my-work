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

		$target = MenuItems::TicketingConsole(NULL);

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

		$sort = $this->mxdDataToRender['sort']; //?sort['modifiedDatetime']=d

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
				<th<?php
					$col = 'id';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>ID</th>
				<th<?php 
					$col = 'subject';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Subject</th>
				<th<?php 
					$col = 'modifiedDatetime';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Last Actioned</th>
				<th<?php 
					$col = 'creationDatetime';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Received</th>
				<th<?php 
					$col = 'ownerId';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Owner</th>
				<th<?php 
					$col = 'categoryId';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Category</th>
				<th<?php 
					$col = 'statusId';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Status</th>
				<th<?php 
					$col = 'priorityId';
					$sortDirection = 'a';
					if (array_key_exists($col, $sort))
					{
						$sortDirection = $sort[$col] ? 'd' : 'a'; // i.e. Current values toggled!
						$sortClass = $sort[$col] ? "reflex-sorted-ascending" : "reflex-sorted-descending";
						echo " class=\"$sortClass\"";
					}
					else
					{
						echo " class=\"reflex-unsorted\"";
					}
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Priority</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th colspan=9 align=right>&nbsp;<?=$navLinks?></th>
			</tr>
		</tfoot>
		<tbody>

		<?php

		$currentUser = Ticketing_User::getCurrentUser();

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

			$base = Flex::getUrlBase() . '/reflex.php/Ticketing/Ticket/' . $ticket->id . '/';
			$actions = $this->getPermittedTicketActions($currentUser, $ticket);

			foreach ($actions as $a => $action)
			{
				$actions[$a] = "<a href='$base$action'>" . htmlspecialchars($action) . "</a>";
			}

			$actions = implode(' ', $actions);
			$actions = $actions ? $actions : '&nbsp;';
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
				<td><?=$actions?></td>
			</tr>
		<?php
		}

		if ($noRecords)
		{
			?>
			<tr><td colspan="9">No tickets match your current filter.</td></tr>
			<?php
		}

		?>
		</tbody>
	</table>
</form>
		<?php
	}

	private function getPermittedTicketActions($user, $ticket)
	{
		$permittedActions = array();
		if ($ticket && $ticket->isSaved())
		{
			$permittedActions[] = 'View';
			$permittedActions[] = 'Edit';

			if (!$ticket->isAssigned() || ($user->isAdminUser() && !$ticket->isAssignedTo($user)))
			{
				$permittedActions[] = 'Take';
			}

			if ($user->isAdminUser())
			{
				if ($ticket->isAssigned())
				{
					$permittedActions[] = 'Reassign';
				}
				else
				{
					$permittedActions[] = 'Assign';
				}
				$permittedActions[] = 'Delete';
			}
		}

		return $permittedActions;
	}

}

?>
