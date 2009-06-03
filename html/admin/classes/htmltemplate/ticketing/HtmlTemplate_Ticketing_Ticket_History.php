<?php

class HtmlTemplate_Ticketing_Ticket_History extends FlexHtmlTemplate
{
	private $ticket	= NULL;
	
	private $currentAccount = NULL;
	private $strCurrentAccountGetVar = NULL;
	
	private $arrAllowableActions = array();
	
	private $arrCachedContacts = array();

	// Current ticketing user
	private $currentTicketingUser = NULL;
	
	// Ticketing_Attachment_Blacklist_Status's
	private $arrAttachmentStatuses = NULL;
	
	// Ticketing_Attachment_Type's
	private $arrAttachmentTypes = NULL;

	public function __construct($intContext=NULL, $strId=NULL, $mxdDataToRender=NULL)
	{
		parent::__construct($intContext, $strId, $mxdDataToRender);
		
		$this->LoadJavascript('ticketing_ticket_history');
	}

	public function Render()
	{
		$this->ticket = $this->mxdDataToRender['ticket'];
		
		$this->currentTicketingUser = Ticketing_User::getCurrentUser();
	
		$this->currentAccount		= $this->mxdDataToRender['currentAccount'];
		$this->strCurrentAccountGetVar	= ($this->currentAccount)? "Account={$this->currentAccount->id}" : "";
		
		if (!$this->ticket || !$this->ticket->isSaved())
		{
			return;
		}
		
		// Define All Allowable actions for this page 
		$this->arrAllowableActions = array('edit', 'send');

		// Build the high-level history for the account
		$arrHistory = $this->buildHistory();
		
		$actionLinks = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/Create/{$this->ticket->id}/?{$this->strCurrentAccountGetVar}\">Create Correspondence</a>";

		?>
		<br/>
		<table class="reflex" id='ticket_history_table'>
			<caption>
				<div id="caption_bar" class="caption_bar">
				<div id="caption_title" class="caption_title">
					History
				</div>
				<div id="caption_options" class="caption_options">
					<?=$actionLinks?>
				</div>
				</div>
			</caption>
			<thead class="header">
				<tr>
					<th>Time</th>
					<th>Event</th>
					<th>Parties Responsible</th>
					<th>Summary</th>
					<th>Actions</th>
					<th><input type='button' onclick='toggleShowAllHistoryRecordDetails(this)' class='expand-button-expanded' style='float:right'></input></th>
				</tr>
			</thead>
			<tbody>
		<?php
					if (count($arrHistory) == 0)
					{
		?>
				<tr class="alt">
					<td colspan="6">There are no significant historical records (which is very odd because there should always be at least one).</td>
				</tr>
		<?php
					}
					else
					{
						$alt = FALSE;
						//$bolFirst = TRUE;
						
						foreach($arrHistory as $i=>$ticketHistoryRecord)
						{
							$altClass				= $alt ? 'alt' : '';
							// When we were only expanding the first record
							//$strSummaryRowClass		= ($bolFirst)? "ticket-history-summary-row-displaying-detail-row" : "ticket-history-summary-row";
							//$strDetailsRowClass		= ($bolFirst)? "ticket-history-details-row-displayed" : "ticket-history-details-row-hidden";
							//$strExpandButtonClass	= ($bolFirst)? "expand-button-expanded" : "expand-button-retracted";
							//$bolFirst				= FALSE;
							
							// Expand all correspondence items
							$strSummaryRowClass		= ($ticketHistoryRecord->bolIsCorrespondence)? "ticket-history-summary-row-displaying-detail-row" : "ticket-history-summary-row";
							$strDetailsRowClass		= ($ticketHistoryRecord->bolIsCorrespondence)? "ticket-history-details-row-displayed" : "ticket-history-details-row-hidden";
							$strExpandButtonClass	= ($ticketHistoryRecord->bolIsCorrespondence)? "expand-button-expanded" : "expand-button-retracted";
							$alt					= !$alt;
							$strTimestamp			= date("H:i:s d-m-Y", $ticketHistoryRecord->intTimestamp);
							$strExpandButton		= "<input type='button' onclick='toggleShowHistoryRecordDetails({$i}, this)' class='$strExpandButtonClass' style='float:right'></input>";
														
		?>
				<tr class="<?=$strSummaryRowClass?> <?=$altClass?>" altClass='<?=$altClass?>' id='ticket_history_record_<?=$i?>'>
					<td><?=$strTimestamp?></td>
					<td><?=$ticketHistoryRecord->strEvent?></td>
					<td><?=$ticketHistoryRecord->strPartiesResponsible?></td>
					<td><?=$ticketHistoryRecord->strSummary?></td>
					<td><?=$ticketHistoryRecord->strActions?></td>
					<td><?=$strExpandButton?></td>
				</tr>
				<tr class='<?=$strDetailsRowClass?> <?=$altClass?>' altClass='<?=$altClass?>' id='ticket_history_record_details_<?=$i?>'>
					<td colspan='6' class='ticket-history-record-details'>
						<?=$ticketHistoryRecord->strDetails?>
					</td>
				</tr>
		<?php
						}
					}
		?>
			</tbody>
			<tfoot class="footer">
				<tr>
					<th colspan='6'>&nbsp;</th>
				</tr>
			</tfoot>
		</table>

		<?php
		
	}

	// Returns ordered list of HistoryRecord objects
	private function buildHistory()
	{
		$arrModifications	= $this->buildTicketModificationHistoryRecords();
		$arrCorrespondences	= $this->buildCorrespondenceHistoryRecords();
		
		// Compile these 2 lists into 1 sorted from newest to oldest, with Modifications taking precedence over Correspondences
		// That is to say, if a Modification Record has the same timestamp as a correspondence record, then the modification record will be considered to be the older of the 2
		$arrHistory = array();
		reset($arrModifications);
		reset($arrCorrespondences);
		$thrModification = (count($arrModifications))? current($arrModifications) : FALSE;
		$thrCorrespondence = (count($arrCorrespondences))? current($arrCorrespondences) : FALSE;
		
		while ($thrModification !== FALSE && $thrCorrespondence !== FALSE)
		{
			if ($thrCorrespondence->intTimestamp >= $thrModification->intTimestamp)
			{
				// The correspondence TicketHistoryRecord is newer than the modification one
				$arrHistory[] = $thrCorrespondence;
				$thrCorrespondence = next($arrCorrespondences);
			}
			else
			{
				// The modification TicketHistoryRecord is newer than the correspondence one
				$arrHistory[] = $thrModification;
				$thrModification = next($arrModifications);
			}
		}
		
		if ($thrModification !== FALSE)
		{
			// Add the remainder of the modification TicketHistoryRecords
			while ($thrModification !== FALSE) 
			{
				$arrHistory[] = $thrModification;
				$thrModification = next($arrModifications);
			}
		}
		
		if ($thrCorrespondence !== FALSE)
		{
			// Add the remainder of the correspondence TicketHistoryRecords
			while ($thrCorrespondence !== FALSE) 
			{
				$arrHistory[] = $thrCorrespondence;
				$thrCorrespondence = next($arrCorrespondences);
			}
		}
		
		return $arrHistory;
	}

	// Returns ordered list of HistoryRecord objects (ordered newest to oldest)
	private function buildCorrespondenceHistoryRecords()
	{
		$this->arrAttachmentStatuses = Ticketing_Attachment_Blacklist_Status::listAll();
		$this->arrAttachmentTypes = Ticketing_Attachment_Type::listAll();

		$arrCorrespondences = $this->ticket->getCorrespondences();
		$arrCorrespondenceHistoryRecords = array();

		foreach ($arrCorrespondences as $correspondence)
		{
			$arrCorrespondenceHistoryRecords[] = $this->buildCorrespondenceHistoryRecord($correspondence);
		}
		
		return $arrCorrespondenceHistoryRecords;
	}

	// Returns ordered list of HistoryRecord objects (ordered newest to oldest)
	private function buildTicketModificationHistoryRecords()
	{
		$arrTicketHistory = $this->ticket->getHistory();
		
		$arrModificationHistoryRecords = array();

		for ($i=0, $j=count($arrTicketHistory); $i<$j; $i++)
		{
			if (array_key_exists($i+1, $arrTicketHistory))
			{
				// There is a modification record that is older than this one
				$arrModificationHistoryRecords[] = $this->buildTicketModificationHistoryRecord($arrTicketHistory[$i], $arrTicketHistory[($i+1)]);
			}
			else
			{
				// This is the oldest modification record (would relate to the creation of the ticket)
				$arrModificationHistoryRecords[] = $this->buildTicketModificationHistoryRecord($arrTicketHistory[$i], NULL);
			}
		}
		
		return $arrModificationHistoryRecords;
	}

	// Returns single HistoryRecord object representing the passed Correspondance object
	private function buildCorrespondenceHistoryRecord(Ticketing_Correspondance $correspondence)
	{
		// Compile the Parties Responsible
		$arrPartiesResponsible = array();
		// Retrieve the contact
		if ($correspondence->contactId)
		{
			// There is a ticketing contact associated with this correspondence
			if (!array_key_exists($correspondence->contactId, $this->arrCachedContacts))
			{
				$this->arrCachedContacts[$correspondence->contactId] = $correspondence->getContact();
			}
			
			$contact = $this->arrCachedContacts[$correspondence->contactId];
			$arrPartiesResponsible[] = ($contact)? "<a onclick='Ticketing_Contact.displayContact({$contact->id}, null);return false;' title='View'>". htmlspecialchars($contact->getName()) ."</a>" : "(Ticket Contact with id: {$correspondence->contactId} could not be found)";
		}
		
		if ($correspondence->userId)
		{
			// There is a ticketing user associated with this correspondence (The Ticketing_User class will cache these objects so I don't have to do it manually)
			$correspondenceUser = $correspondence->getUser();
			
			$arrPartiesResponsible[] = ($correspondenceUser)? htmlspecialchars($correspondenceUser->getName()) : "Could not find ticketing user with id: {$correspondence->userId}";
		}
		
		$strPartiesResponsible = (count($arrPartiesResponsible))? implode(" / ", $arrPartiesResponsible) : NULL;
		
		
		// Compile the Event
		$sourceName			= $correspondence->getSource()->name;
		$deliveryStatusName	= $correspondence->getDeliveryStatus()->name;
		$strEvent			= "$sourceName - $deliveryStatusName";
		
		
		// Compile the Summary of the event (the correspondence summary)
		$link = Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/{$correspondence->id}/View/?{$this->strCurrentAccountGetVar}";
		$strSummary = "<a href='$link' title='View'>". htmlspecialchars($correspondence->summary) ."</a>";
		
		// Compile the Timestamp
		$intTimestamp = strtotime($correspondence->creationDatetime);
		
		
		// Compile Actions
		$arrPossibleActions = Application_Handler_Ticketing::getPermittedCorrespondenceActions($this->currentTicketingUser, $correspondence);
		
		$arrActions = array();
		foreach ($this->arrAllowableActions as $strAction)
		{
			if (in_array($strAction, $arrPossibleActions))
			{
				$strAction = ucfirst($strAction);
				$arrActions[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/{$correspondence->id}/{$strAction}/?{$this->strCurrentAccountGetVar}\">{$strAction}</a>";
			}
			elseif ($strAction == 'send' && in_array('resend', $arrPossibleActions))
			{
				$arrActions[] = "<a href=\"" . Flex::getUrlBase() . "reflex.php/Ticketing/Correspondence/{$correspondence->id}/Resend/?{$this->strCurrentAccountGetVar}\">Resend</a>";
			}
		}
		$strActions = (count($arrActions))? implode(" | ", $arrActions) : NULL;
		
		// Compile the 'details'
		$strDeliveryTimestamp	= ($correspondence->deliveryDatetime === NULL)? "" : date("H:i:s d-m-Y", strtotime($correspondence->deliveryDatetime));
		$strCreationTimestamp	= ($correspondence->creationDatetime === NULL)? "" : date("H:i:s d-m-Y", $intTimestamp);
		$strDetails = "";
		if ($correspondence->deliveryDatetime !== NULL)
		{
			$strDetails .= "<em>Delivered: $strDeliveryTimestamp</em><br />\n";
		}
		$strDetails .= "<em>Content:</em><div style='padding-left:3em'>". nl2br(htmlspecialchars(trim($correspondence->details))) ."</div>";
		
		// Grab the Attachments (minus the actual file so as not to unneccessarily slow things down)
		$strGenericAttachmentLink	= Flex::getUrlBase() . "reflex.php/Ticketing/Attachment/";
		$arrAttachments				= Ticketing_Attachment::listForCorrespondence($correspondence);
		$arrAttachmentRows			= array();
		foreach ($arrAttachments as $attachment)
		{
			$strFilename	= htmlspecialchars($attachment->fileName);
			$attachmentType	= $this->arrAttachmentTypes[$attachment->attachmentTypeId];
			$strType		= htmlspecialchars($attachmentType->mimeType);
			$status			= $this->arrAttachmentStatuses[$attachmentType->blacklistStatusId];
			
			$bolIsDownloadable		= (!$status->isBlacklisted() || $attachment->allowBlacklistOverride()) ? TRUE : FALSE;
			$strFilenameGroupClass	= ($bolIsDownloadable) ? "downloadable-attachment" : "blocked-downloadable-attachment";
			
			if ($status->isBlacklisted() && $this->currentTicketingUser->isAdminUser())
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
			$strAttachments = "<em>Attachments:</em><div style='padding-left:3em'>". implode('<br />', $arrAttachmentRows) ."</div>";
		}
		else
		{
			$strAttachments = "";
		}
		
		$strDetails .= $strAttachments;

		
		return new TicketHistoryRecord($intTimestamp, $strEvent, $strPartiesResponsible, $strSummary, $strActions, $strDetails, TRUE);
	}
	
	// Returns single HistoryRecord object representing modifications in the state of the ticket, to arive at $ticketState
	private function buildTicketModificationHistoryRecord(Ticketing_Ticket_History $ticketState, Ticketing_Ticket_History $previousTicketState=NULL)
	{
		$intTimestamp = strtotime($ticketState->modifiedDatetime);
		
		// Find the Party Responsible (ticket user)
		$strUser = "";
		if ($ticketState->modifiedByUserId === NULL)
		{
			$strUser = "System";
		}
		else
		{
			$user = Ticketing_User::getForId($ticketState->modifiedByUserId);
			
			$strUser = ($user)? htmlspecialchars($user->getName()) : "Ticketing User with id: {$ticketState->modifiedByUserId} could not be found";
		}
		
		if (!array_key_exists($ticketState->contactId, $this->arrCachedContacts))
		{
			$this->arrCachedContacts[$ticketState->contactId] = Ticketing_Contact::getForId($ticketState->contactId);
		}
		
		$arrDetails			= array();
		$owner				= ($ticketState->ownerId !== NULL)? Ticketing_User::getForId($ticketState->ownerId) : NULL;
		$priority			= Ticketing_Priority::getForId($ticketState->priorityId);
		$customerGroup		= ($ticketState->customerGroupId !== NULL)? Customer_Group::getForId($ticketState->customerGroupId) : NULL;
		$account			= ($ticketState->accountId !== NULL)? Account::getForId($ticketState->accountId) : NULL;
		$contact			= $this->arrCachedContacts[$ticketState->contactId];
		$status				= Ticketing_Status::getForId($ticketState->statusId);
		$category			= Ticketing_Category::getForId($ticketState->categoryId);
		
		$arrDetails["Subject"] = array(	"property"	=> "Subject",
										"value"		=> htmlspecialchars($ticketState->subject)
									);
		$arrDetails["Owner"] = array(	"property"	=> "Owner",
										"value"		=> ($owner ? htmlspecialchars($owner->getName()) : "[Unassigned]") 
									);
		$arrDetails["Account"] = array(	"property"	=> "Account",
										"value"		=> ($account ? $account->id : "[Not matched to an account]") 
									);
		$arrDetails["Customer Group"] = array(	"property"	=> "Customer Group",
												"value"		=> ($customerGroup ? htmlspecialchars($customerGroup->internalName) : NULL) 
											);
		$arrDetails["Contact"] = array(	"property"	=> "Contact",
										"value"		=> "<a onclick='Ticketing_Contact.displayContact({$contact->id}, null);return false;' title='View'>". htmlspecialchars($contact->getName()) ."</a>"
									);
		$arrDetails["Category"] = array("property"	=> "Category",
										"value"		=> htmlspecialchars($category->name) 
									);
		$arrDetails["Status"] = array(	"property"	=> "Status",
										"value"		=> htmlspecialchars($status->name) 
									);
		$arrDetails["Priority"] = array("property"	=> "Priority",
										"value"		=> htmlspecialchars($priority->name) 
									);
		if ($previousTicketState === NULL)
		{
			// Assume $ticketState models the original state of the ticket (creation of the ticket)
			$strEvent	= "Creation";
			$strSummary	= htmlspecialchars($ticketState->subject);
		}
		else
		{
			// $ticketState will declare what has been updated since $previousTicketState
			$strEvent = "Update";
			$arrDetailsChanged = array();
			
			if (!array_key_exists($previousTicketState->contactId, $this->arrCachedContacts))
			{
				$this->arrCachedContacts[$previousTicketState->contactId] = Ticketing_Contact::getForId($previousTicketState->contactId);
			}
				
			$prevOwner				= ($previousTicketState->ownerId !== NULL)? Ticketing_User::getForId($previousTicketState->ownerId) : NULL;
			$prevPriority			= Ticketing_Priority::getForId($previousTicketState->priorityId);
			$prevCustomerGroup		= ($previousTicketState->customerGroupId !== NULL)? Customer_Group::getForId($previousTicketState->customerGroupId) : NULL;
			$prevAccount			= ($previousTicketState->accountId !== NULL)? Account::getForId($previousTicketState->accountId) : NULL;
			$prevContact			= $this->arrCachedContacts[$previousTicketState->contactId];
			$prevStatus				= Ticketing_Status::getForId($previousTicketState->statusId);
			$prevCategory			= Ticketing_Category::getForId($previousTicketState->categoryId);
			
			// ticketing_ticket_history.subject is truncated to 50 chars
			if (substr($ticketState->subject, 0, 50) != $previousTicketState->subject)
			{
				// The subject has changed
				$arrDetails['Subject']['previousValue'] = $previousTicketState->subject;
				$arrDetailsChanged[] = "Subject";
			}
			
			if ($ticketState->ownerId !== $previousTicketState->ownerId)
			{
				$arrDetails['Owner']['previousValue'] = $prevOwner ? htmlspecialchars($prevOwner->getName()) : NULL;
				$arrDetailsChanged[] = "Owner";
			}
			
			if ($ticketState->accountId !== $previousTicketState->accountId)
			{
				$arrDetails['Account']['previousValue'] = $prevAccount ? $prevAccount->id : NULL;
				$arrDetailsChanged[] = "Account";
			}

			if ($ticketState->customerGroupId !== $previousTicketState->customerGroupId)
			{
				$arrDetails['Customer Group']['previousValue'] = $prevCustomerGroup ? htmlspecialchars($prevCustomerGroup->internalName) : NULL;
				$arrDetailsChanged[] = "CustomerGroup";
			}
			
			if ($ticketState->contactId !== $previousTicketState->contactId)
			{
				$arrDetails['Contact']['previousValue'] = "<a onclick='Ticketing_Contact.displayContact({$prevContact->id}, null);return false;' title='View'>". htmlspecialchars($prevContact->getName()) ."</a>";
				$arrDetailsChanged[] = "Contact";
			}
			
			if ($ticketState->categoryId !== $previousTicketState->categoryId)
			{
				$arrDetails['Category']['previousValue'] = htmlspecialchars($prevCategory->name);
				$arrDetailsChanged[] = "Category";
			}

			if ($ticketState->statusId !== $previousTicketState->statusId)
			{
				$arrDetails['Status']['previousValue'] = htmlspecialchars($prevStatus->name);
				$arrDetailsChanged[] = "Status";
			}

			if ($ticketState->priortyId !== $previousTicketState->priortyId)
			{
				$arrDetails['Priority']['previousValue'] = htmlspecialchars($prevPriority->name);
				$arrDetailsChanged[] = "Priority";
			}

			if (count($arrDetailsChanged))
			{
				$strSummary = "Updated - ". implode(", ", $arrDetailsChanged);
			}
			else
			{
				$strSummary = "No tracked details were modified";
			}
		}

		$strDetails = "";
		$strValueColumnColSpan = ($previousTicketState)? "" : "colspan='2'";
		foreach ($arrDetails as $arrDetail)
		{
			$strProperty	= $arrDetail['property'];
			$strValue		= $arrDetail['value'];
			if ($previousTicketState)
			{
				if (array_key_exists('previousValue', $arrDetail))
				{
					$strPreviousValue = "\n\t<td>". (($arrDetail['previousValue'] !== NULL)? "(<em>Changed from:</em> {$arrDetail['previousValue']})" : "(<em>Was previously not set</em>)") ."</td>";
				}
				else
				{
					$strPreviousValue = "\n\t<td></td>";
				}
			}
			else
			{
				$strPreviousValue = "";
			}
			
			$strDetails .= "
<tr>
	<td><em>$strProperty:</em></td>
	<td $strValueColumnColSpan>$strValue</td>$strPreviousValue
</tr>";
		}		
		
		$strDetails = "<table cellspacing='0' cellpadding='0'>$strDetails</table>";
		
		$strActions = NULL;
		
		return new TicketHistoryRecord($intTimestamp, $strEvent, $strUser, $strSummary, $strActions, $strDetails, FALSE);
	}
}

// This class is used to generically model an Event in the history of a ticket
class TicketHistoryRecord extends stdClass
{
	public $intTimestamp;
	public $strEvent;
	public $strPartiesResponsible;
	public $strSummary;
	public $strActions;
	public $strDetails;
	public $bolIsCorrespondence;
	
	public function __construct($intTimestamp, $strEvent, $strPartiesResponsible, $strSummary, $strActions, $strDetails, $bolIsCorrespondence)
	{
		$this->intTimestamp = $intTimestamp;
		$this->strEvent = $strEvent;
		$this->strPartiesResponsible = $strPartiesResponsible;
		$this->strSummary = $strSummary;
		$this->strActions = $strActions;
		$this->strDetails = $strDetails;
		$this->bolIsCorrespondence = $bolIsCorrespondence;
	}
}

?>
