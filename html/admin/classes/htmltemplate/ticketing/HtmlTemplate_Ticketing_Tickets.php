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
		$nrTickets		= $this->mxdDataToRender['ticket_count'];
		$count			= count($this->mxdDataToRender['tickets']);
		$startOffset	= ($this->mxdDataToRender['offset'] + ($count ? 1 : 0));
		$endOffset		= $this->mxdDataToRender['offset'] + $count;
		$limit			= $this->mxdDataToRender['limit'];
		$quickSearch	= $this->mxdDataToRender['quickSearch'];
		$objAccount		= $this->mxdDataToRender['account'];

		$strAccountGetVar = ($objAccount)? "Account={$objAccount->id}" : "";

		if ($quickSearch === NULL)
		{
			$quickSearch = '';
		}
		$title = "Viewing $startOffset to $endOffset of $nrTickets Tickets";

		$ownerId = array_key_exists('ownerId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['ownerId']['value'] : NULL;
		$categoryId = array_key_exists('categoryId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['categoryId']['value'] : NULL;
		$statusId = array_key_exists('statusId', $this->mxdDataToRender['filter']) ? $this->mxdDataToRender['filter']['statusId']['value'] : NULL;
		if (is_array($statusId))
		{
			$statusId = implode(',', $statusId);
		}

		$currentUser = Ticketing_User::getCurrentUser();

		$possibleActions = array('Edit', 'Take', 'Assign');
		if ($currentUser->isAdminUser())
		{
			$possibleActions[] = 'Delete';
		}
		$nrPossibleActions = count($possibleActions);

		$selected = ' SELECTED="SELECTED"';

		$target = MenuItems::TicketingConsole(NULL);

		$arrNavLinks = array();

		if ($this->mxdDataToRender['offset'] > 0)
		{
			$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=0&{$strAccountGetVar}\">First</a>";
			$offset = $this->mxdDataToRender['offset'] - $limit;
			$offset = $offset < 0 ? 0 : $offset;
			$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=$offset&{$strAccountGetVar}\">Previous</a>";
		}

		if ($nrTickets > $endOffset)
		{
			$nrLastPage = $nrTickets % $limit;
			$nrLastPage = $nrLastPage ? $nrLastPage : $limit;
			if ($nrLastPage)
			{
				$offset = $nrTickets - $nrLastPage;
				$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=$endOffset&{$strAccountGetVar}\">Next</a>";
				$arrNavLinks[] = "<a href=\"reflex.php/Ticketing/Tickets/Last/?offset=$offset&{$strAccountGetVar}\">Last</a>";
			}
		}

		$sort = $this->mxdDataToRender['sort']; //?sort['modifiedDatetime']=d

		$navLinks = implode('&nbsp;&nbsp;', $arrNavLinks);

?>
<script>

	var autorefresh = null;
	var ticker = null;
	var countdownFrom = 150;
	var countdown = countdownFrom;
	var cookieName = 'autoRefreshTicketList';
	var timeRemaining = null;

	function toggleAutorefresh()
	{
		countdown = countdownFrom;
		if (autorefresh.checked)
		{
			ticker = window.setInterval("refreshCountdown()", 1000);
		}
		else if (ticker != null)
		{
			window.clearTimeout(ticker);
		}
		timeRemaining.innerHTML = '';
		timeRemaining.appendChild(document.createTextNode(countdown));
		Flex.cookie.create(cookieName, autorefresh.checked ? 'true' : 'false', 30);
	}

	function refreshCountdown()
	{
		countdown = countdown - 1;
		if (autorefresh.checked && countdown <= 0) //>
		{
			window.clearTimeout(ticker);
			document.location.reload();
			timeRemaining.innerHTML = '';
			timeRemaining.appendChild(document.createTextNode('0'));
		}
		else
		{
			timeRemaining.innerHTML = '';
			timeRemaining.appendChild(document.createTextNode(countdown));
		}
	}

	function startCountdown()
	{
		var refreshPanel = $ID('refreshPanel');
		autorefresh = document.createElement('input');
		autorefresh.type = 'checkbox';
		Event.observe(autorefresh, 'click', toggleAutorefresh);
		refreshPanel.appendChild(autorefresh);
		refreshPanel.appendChild(document.createElement('span'));
		refreshPanel.childNodes[1].appendChild(document.createTextNode('Auto-refresh in '));
		timeRemaining = document.createElement('span');
		timeRemaining.appendChild(document.createTextNode(countdown));
		refreshPanel.appendChild(timeRemaining);
		refreshPanel.appendChild(document.createElement('span'));
		refreshPanel.childNodes[3].appendChild(document.createTextNode(' seconds'));
		var cookieValue = Flex.cookie.read(cookieName);
		autorefresh.checked = (cookieValue == null || cookieValue == 'true');
		toggleAutorefresh();
	}
	Event.observe(window, 'load', startCountdown);

</script>
<?php

		?>
<form method="GET" action="<?=$target?>">
	<?php
		if ($objAccount)
		{
			echo "\t<input type='hidden' name='Account' value='{$objAccount->id}'></input>";
		}
	?>
	<table id="ticketing" name="ticketing" class="reflex highlight-rows">
		<caption>
			<div id="caption_bar" class="caption_bar">
				<div id="caption_title" class="caption_title">
					<?=$title?>
				</div>
				<div id="caption_options" class="caption_options">

				Filter:
				<input type="text" id="ticketFilter" name="quickSearch" value="<?=htmlspecialchars($quickSearch)?>"/>

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
					$sortDirection = 'd';
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
					$link = Flex::getUrlBase() . "reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Subject</th>
				<th<?php
					$col = 'modifiedDatetime';
					$sortDirection = 'd';
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
					echo ' onclick="document.location = \''. $link . '\'"';
				?> title="Last time the ticket details were modified">Last Modified</th>
				<th<?php
					$col = 'userCorrespondenceLastActionedOn';
					$sortDirection = 'd';
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
					echo ' onclick="document.location = \''. $link . '\'"';
				?> title="Last time a user created or sent correspondence">Last Actioned</th>
				<th<?php
					$col = 'creationDatetime';
					$sortDirection = 'd';
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
					echo ' onclick="document.location = \''. $link . '\'"';
				?> title='Ticket creation timestamp'>Received</th>
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
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
					$link = Flex::getUrlBase() . "/reflex.php/Ticketing/Tickets/Last/?sort[\\'$col\\']=$sortDirection&{$strAccountGetVar}";
					echo ' onclick="document.location = \''. $link . '\'"';
				?>>Priority</th>
				<th colspan="<?=$nrPossibleActions?>">Actions</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th id='refreshPanel' align='left' colspan="2"></th>
				<th align='right' colspan="<?=(7+$nrPossibleActions)?>"><?=$navLinks?></th>
			</tr>
		</tfoot>
		<tbody style='vertical-align:top'>

		<?php

		$i = 0;
		$noRecords = TRUE;
		$strToday = date("d-m-Y", strtotime(Data_Source_Time::currentDate()));
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
			$actionCells = array();

			foreach($possibleActions as $action)
			{
				$actionLink = $actions[$action] ? "<a href='$base$action/?{$strAccountGetVar}'>" . htmlspecialchars($action) . "</a>" : "&nbsp;";
				if ($action == 'Assign' && !$actions[$action])
				{
					$action = 'Reassign';
					$actionLink = $actions[$action] ? "<a href='$base$action/?{$strAccountGetVar}'>" . htmlspecialchars($action) . "</a>" : "&nbsp;";
				}
				$actionCells[] = "<td>$actionLink</td>";
			}

			$intModifiedOn		= strtotime($ticket->modifiedDatetime);
			$strModifiedOnDate	= date("d-m-Y", $intModifiedOn);
			if ($strModifiedOnDate == $strToday)
			{
				// The ticket was last modified today
				$strModifiedOn = "<span title='today'>". date("g:i:sa", $intModifiedOn) ."</span>";
			}
			else
			{
				$strModifiedOn = "<span title='$strModifiedOnDate ". date("g:i:s a", $intModifiedOn) ."'>$strModifiedOnDate</span>";
			}

			$intCreatedOn		= strtotime($ticket->creationDatetime);
			$strCreatedOnDate	= date("d-m-Y", $intCreatedOn);
			if ($strCreatedOnDate == $strToday)
			{
				// The ticket was Created today
				$strCreatedOn = "<span title='today'>". date("g:i:sa", $intCreatedOn) ."</span>";
			}
			else
			{
				$strCreatedOn = "<span title='$strCreatedOnDate ". date("g:i:s a", $intCreatedOn) ."'>$strCreatedOnDate</span>";
			}

			// Process the "Last Actioned" value
			$objLastUserCorrespondence = Ticketing_Correspondance::getMostRecentlyActionedUserCreatedCorrespondenceForTicketId($ticket->id);
			if ($objLastUserCorrespondence !== null)
			{
				// There was a user-created correspondence record
				$intLastActionedOn		= ($objLastUserCorrespondence->deliveryDatetime !== null)? strtotime($objLastUserCorrespondence->deliveryDatetime) : strtotime($objLastUserCorrespondence->creationDatetime);
				$strLastActionedOnDate	= date("d-m-Y", $intLastActionedOn);
				if ($strLastActionedOnDate == $strToday)
				{
					// The correspondence was created today
					$strLastActionedOn = "<span title='today'>". date("g:i:sa", $intLastActionedOn) ."</span>";
				}
				else
				{
					$strLastActionedOn = "<span title='$strLastActionedOnDate ". date("g:i:s a", $intLastActionedOn) ."'>$strLastActionedOnDate</span>";
				}
			}
			else
			{
				// No user created correspondence
				$strLastActionedOn = "";
			}

		?>

			<tr class="<?=$tr_alt?>">
				<td><a href="reflex.php/Ticketing/Ticket/<?=$ticket->id?>/View/<?php echo "?{$strAccountGetVar}"?>"><?php echo $ticket->id; ?></a></td>
				<td><?php echo (trim($ticket->subject) ? htmlspecialchars($ticket->subject) : '<em>[No Subject]</em>'); ?></td>
				<td><?php echo $strModifiedOn; ?></td>
				<td><?php echo $strLastActionedOn; ?></td>
				<td><?php echo $strCreatedOn; ?></td>
				<td><?php echo $ownerName; ?></td>
				<td class="<?=$category->cssClass?>"><?php echo $category->name; ?></td>
				<td class="<?=$status->cssClass?>"><?php echo $status->name; ?></td>
				<td class="<?=$priority->cssClass?>"><?php echo $priority->name; ?></td>
				<?=implode('', $actionCells)?>
			</tr>
		<?php
		}

		if ($noRecords)
		{
			?>
			<tr><td colspan="<?=(9+$nrPossibleActions)?>">No tickets match your current filter.</td></tr>
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
		$permittedActions = array('View' => FALSE, 'Edit' => FALSE, 'Take' => FALSE, 'Reassign' => FALSE, 'Assign' => FALSE, 'Delete' => FALSE);
		if ($ticket && $ticket->isSaved())
		{
			$permittedActions['View'] = TRUE;

			if ($user->isNormalUser() || $user->isAdminUser())
			{
				$permittedActions['Edit'] = TRUE;
			}

			/* Only ticketing admins can take/assign/reassign tickets.  If everyone needs to be able to, then uncomment this and remove it from the other section below
			if ($ticket->isAssigned())
			{
				if (!$ticket->isAssignedTo($user))
				{
					$permittedActions['Take'] = TRUE;
				}
				$permittedActions['Reassign'] = TRUE;
			}
			else
			{
				$permittedActions['Take'] = TRUE;
				$permittedActions['Assign'] = TRUE;
			}
			*/

			if ($user->isAdminUser())
			{
				if ($ticket->isAssigned())
				{
					if (!$ticket->isAssignedTo($user))
					{
						$permittedActions['Take'] = TRUE;
					}
					$permittedActions['Reassign'] = TRUE;
				}
				else
				{
					$permittedActions['Take'] = TRUE;
					$permittedActions['Assign'] = TRUE;
				}

				// Admin users
				$permittedActions['Delete'] = TRUE;
			}
			else
			{
				// Normal Users
				// Nother to add
			}
		}

		return $permittedActions;
	}

}

?>
