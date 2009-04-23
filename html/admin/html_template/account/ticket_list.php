<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// HtmlTemplateAccountTicketList
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountTicketList
 *
 * HTML Template class for the AccountTicketList HTML object
 *
 * HTML Template class for the AccountTicketList HTML object
 * Lists Tickets associated with the account in question
 *
 * @class	HtmlTemplateAccountTicketList
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountTicketList extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct
	 *
	 * Constructor
	 *
	 * Constructor - java script required by the HTML object is loaded here
	 *
	 * @param	int		$intContext		context in which the html object will be rendered
	 * @param	string	$strId			the id of the div that this HtmlTemplate is rendered in
	 *
	 * @method
	 */
	function __construct($intContext, $strId)
	{
		$this->_intContext = $intContext;
		$this->_strContainerDivId = $strId;
		
		$this->LoadJavascript("highlight");
	}

	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @property
	 */
	function Render()
	{
		$intAccountId = $this->_intContext;
		
		Table()->Tickets->SetHeader("Ticket", "Subject");
		Table()->Tickets->SetAlignment("Left", "Left");
		echo "<h2 class='Tickets'>Tickets</h2>\n";
		
		// Retrieve the last 3 tickets (that aren't deleted) associated with this account
		try
		{
			// Only find tickets that aren't deleted and belong to the account
			$arrFilter				= array();
			
			// The following 2 lines have been commented out, incase management want this list to only show Open or Pending tickets
			//$objOpenPending			= Ticketing_Status_Type_Conglomerate::getForId(Ticketing_Status_Type_Conglomerate::TICKETING_STATUS_TYPE_CONGLOMERATE_OPEN_OR_PENDING);
			//$arrTicketStatusIds		= $objOpenPending->listStatusIds();
			
			$arrTicketStatuses		= Ticketing_Status::listAll();
			unset($arrTicketStatuses[TICKETING_STATUS_DELETED]);
			$arrTicketStatusIds		= array_keys($arrTicketStatuses);
			$arrFilter['statusId']	= array('value' => $arrTicketStatusIds, 'comparison' => '=');
			$arrFilter['accountId']	= array('value' => $intAccountId, 'comparison' => '=');
			
			// Sort by id desc
			$arrSort		= array();
			$arrSort['id']	= FALSE;
			
			// Limit to 3 tickets max
			$intOffset	= 0;
			$intLimit	= 3;

			// I don't even know why this is an option
			$arrColumns	= NULL;
			
			$arrTickets	= Ticketing_Ticket::findMatching($arrColumns, $arrSort, $arrFilter, $intOffset, $intLimit);

			if (count($arrTickets) == 0)
			{
				// No tickets to display, so don't display the component at all
				Table()->Tickets->AddRow("No open or pending tickets to display");
				Table()->Tickets->SetRowAlignment("left");
				Table()->Tickets->SetRowColumnSpan(2);
			}
			else
			{
				Table()->Tickets->RowHighlighting = TRUE;
				
				// Add each row to the table
				foreach ($arrTickets as $objTicket)
				{
					$strIdCell		= "<a href='". Href()->TicketingTicket($objTicket->id, $intAccountId) ."' title='View Ticket'>{$objTicket->id}</a>";
					$strSubjectCell	= htmlspecialchars($objTicket->subject);
					
					Table()->Tickets->AddRow($strIdCell, $strSubjectCell);
				}
			}
		}
		catch (Exception $e)
		{
			Table()->Tickets->AddRow("An error occurred when compiling this component<br />Error: ". htmlspecialchars($e->getMessage()));
			Table()->Tickets->SetRowAlignment("left");
			Table()->Tickets->SetRowColumnSpan(2);
		} 
		
		Table()->Tickets->Render();

		$strAllTicketsLink = Href()->ViewTicketsForAccount($intAccountId);
		$strAddTicketLink = Href()->AddTicket($intAccountId);

		echo "
<div style='text-align: right'>
	<div class='SmallSeparator' style='clear:both'></div>
	<a class='button' href='$strAllTicketsLink'>View All Tickets</a> 
	<a class='button' href='$strAddTicketLink'>Create Ticket</a>
	<br />
	<div class='SmallSeparator' style='clear:both'></div>
</div>
<div class='SmallSeparator' style='clear:both'></div>";
	}
}

?>
