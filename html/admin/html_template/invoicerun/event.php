<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// terms_display.php
//----------------------------------------------------------------------------//
/**
 * terms_display
 *
 * HTML Template for the details of Invoice Run Event
 *
 * HTML Template for the details of Invoice Run Event
 *
 * @file		terms_display.php
 * @language	PHP
 * @package		ui_app
 * @author		Hadrian Oliver
 * @version		0.1
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
//----------------------------------------------------------------------------//
// HtmlTemplatePaymentTermsDisplay
//----------------------------------------------------------------------------//
/**
 * HtmlTemplatePaymentTermsDisplay
 *
 * HTML Template for the details of Invoice Run Event
 *
 * HTML Template for the details of Invoice Run Event
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplateInvoiceRunEvent
 * @extends	HtmlTemplate
 */
class HtmlTemplateInvoiceRunEvent extends HtmlTemplate
{
	//------------------------------------------------------------------------//
	// _intContext
	//------------------------------------------------------------------------//
	/**
	 * _intContext
	 *
	 * the context in which the html object will be rendered
	 *
	 * the context in which the html object will be rendered
	 *
	 * @type		integer
	 *
	 * @property
	 */
	public $_intContext;

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
		
		$this->LoadJavascript("table_sort");
		$this->LoadJavascript("invoice_run_event");
		$this->LoadJavascript("date_time_picker_dynamic");
	}


	//------------------------------------------------------------------------//
	// Render
	//------------------------------------------------------------------------//
	/**
	 * Render()
	 *
	 * Render this HTML Template
	 *
	 * Render this HTML Template
	 *
	 * @method
	 */
	function Render()
	{
		$bolUserIsAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_ADMIN);

		switch ($this->_intContext)
		{
			case HTML_CONTEXT_EDIT:
				if ($bolUserIsAdmin)
				{
					$this->_RenderForEditing();
					break;
				}
			case HTML_CONTEXT_VIEW:
				$this->_RenderForViewing();
				break;
			case HTML_CONTEXT_DEFAULT:
			default:
				$this->_RenderList();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderList
	//------------------------------------------------------------------------//
	/**
	 * _RenderList()
	 *
	 * Render this HTML Template with full detail
	 *
	 * Render this HTML Template with full detail
	 *
	 * @method
	 */
	private function _RenderList()
	{
		// Need to itterate through the list of loaded invoice runs, if there are any
		echo "<!-- START HtmlTemplateInvoiceRunList -->\n";
		echo "<div id='InvoiceRunDiv'>\n";
		echo "<div style='margin: 0px; width: 500px;'>\n";
		
		echo "<div id='InvoiceRunTableDiv'>";
		$this->_RenderInvoiceRunTable();
		echo "</div>";
		
		// End narrow table
		echo "</div>\n";
		echo "</div>\n";

		echo "<!-- END HtmlTemplateInvoiceRunList -->\n";
		// Initialise the PaymentTerms object and register the OnPaymentTermsUpdate Listener
		$strJavascript = "Vixen.InvoiceRunEvents.InitialiseManage('{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}

	function _RenderInvoiceRunTable()
	{
		Table()->InvoiceRunTable->SetHeader("Billing Date", "Invoice Run", "Customer Group", "Actions");
		Table()->InvoiceRunTable->SetWidth("30%", "30%", "30%", "10%");
		Table()->InvoiceRunTable->SetAlignment("Left", "Left", "Left", "Center");
		Table()->InvoiceRunTable->SetSortable(TRUE);
		Table()->InvoiceRunTable->SetSortFields("BillingDate", "Id", "customer_group_id", null);
		Table()->InvoiceRunTable->SetPageSize(24);
		foreach (DBL()->InvoiceRun as $dboInvoiceRun)
		{
			$strViewHref = Href()->EditInvoiceRunEvents($dboInvoiceRun->Id->Value);

			$strView = "<img src='img/template/view.png' onclick='$strViewHref' alt='View Invoice Run'></img>";

			Table()->InvoiceRunTable->AddRow(OutputMask()->LongDate($dboInvoiceRun->BillingDate->Value),
											$dboInvoiceRun->Id->AsValue(),
											$dboInvoiceRun->customer_group_id->Value ? Customer_Group::getForId($dboInvoiceRun->customer_group_id->Value)->name : '[Mixed]',
											$strView);
		}

		Table()->InvoiceRunTable->Render();
	}



	//------------------------------------------------------------------------//
	// _RenderForViewing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForViewing()
	 *
	 * Renders the Invoice Run Events in "View" mode
	 *
	 * Renders the Invoice Run Events in "View" mode
	 *
	 * @method
	 */
	private function _RenderForViewing()
	{
		// Grab the invoice run details for the header summary
		$intInvoiceRunId = DBO()->InvoiceRun->Id->Value;

		$this->FormStart("InvoiceRunEvents", "InvoiceRunEvents", "x");

		$customerGroup = DBO()->InvoiceRun->customer_group_id->Value ? Customer_Group::getForId(1)->name : '[Mixed]'; 

		echo "<div class='GroupedContent'>\n";
		echo "
<input type=hidden id='InvoiceRun.Id' name='InvoiceRun.Id' value='$intInvoiceRunId'/>
<div class='DefaultElement'>
	<div class='DefaultOutput Default ' style='margin-left: 60;'><span class=\"DefaultOutputSpan Default\">" . OutputMask()->LongDate(DBO()->InvoiceRun->BillingDate->Value) . "</span></div>
	<div class='DefaultLabel'>
		<span id='invoiceActions.$id.Label.Text'>Billing Date : </span>
	</div>
</div>
<div class='DefaultElement'>
	<div class='DefaultOutput Default ' style='margin-left: 60;'>" . DBO()->InvoiceRun->Id->AsValue() . "</div>
	<div class='DefaultLabel'>
		<span id='invoiceActions.$id.Label.Text'>Invoice Run : </span>
	</div>
</div>
<div class='DefaultElement'>
	<div class='DefaultOutput Default ' style='margin-left: 60;'><span class=\"DefaultOutputSpan Default\">" . htmlspecialchars($customerGroup) . "</span>
</div>
	<div class='DefaultLabel'>
		<span id='invoiceActions.$id.Label.Text'>Customer Group : </span>
	</div>
</div>
";
		echo "</div>\n"; // GroupedContent
		echo "<div class='SmallSeparator'></div>\n";

		// Render the details of the invoice run events.
		// These need to be in the same order as the loaded automatic_invoice_action DBL.
		// To make this easy we should first create an array of event DBOs indexed on the automatic_invoice_action_id
		$arrEvents = array();
		foreach(DBL()->automatic_invoice_run_event as $dboInvoiceRunEvent)
		{
			$arrEvents[$dboInvoiceRunEvent->automatic_invoice_action_id->Value] = $dboInvoiceRunEvent;
		}


		Table()->InvoiceRunTable->SetHeader("Event", "Scheduled", "Actioned");
		Table()->InvoiceRunTable->SetWidth("40%", "40%", "20%");
		Table()->InvoiceRunTable->SetAlignment("Left", "Left", "Center");
		foreach (DBL()->automatic_invoice_action_config as $invoiceAction)
		{
			$id = $invoiceAction->automatic_invoice_action_id->Value;
			//$name = $invoiceAction->name->Value;
			$name = GetConstantDescription($id, 'AUTOMATIC_INVOICE_ACTION');//$invoiceAction->name->Value;

			// Get the event for this action
			$dboEvent = $arrEvents[$id];

			$strScheduled = $dboEvent->scheduled_datetime->Value ? date('H:i d/m/Y', strtotime($dboEvent->scheduled_datetime->Value)) : "Not Scheduled";
			$strActioned = $dboEvent->actioned_datetime->Value ? date('H:i d/m/Y', strtotime($dboEvent->actioned_datetime->Value)) : "Not Actioned";

			$strView = "<a href='$strViewHref' title='View Invoice Run'><img src='img/template/view.png'></img></a>";

			//$strEditLabel = "<span class='DefaultOutputSpan Default'><a href='$strEditHref'>Edit Employee</a></span>";	
			
			Table()->InvoiceRunTable->AddRow("$name : ", $strScheduled, $strActioned);
		}

		Table()->InvoiceRunTable->Render();

		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";

		$this->Button("Cancel", "Vixen.InvoiceRunEvents.RenderDetailsForManaging();");
		$this->Button("Edit Details", "Vixen.InvoiceRunEvents.RenderDetailsForEditing($intInvoiceRunId);");
		echo "</div></div>\n";

		// Initialise the PaymentTerms object and register the OnPaymentTermsUpdate Listener
		$strJavascript = "Vixen.InvoiceRunEvents.InitialiseView('{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";

		$this->FormEnd();
	}

	//------------------------------------------------------------------------//
	// _RenderForEditing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForEditing()
	 *
	 * Renders the Invoice Run Events in "Edit" mode
	 *
	 * Renders the Invoice Run Events in "Edit" mode
	 *
	 * @method
	 */
	private function _RenderForEditing()
	{
		// Grab the invoice run details for the header summary
		$intInvoiceRunId = DBO()->InvoiceRun->Id->Value;

		$this->FormStart("InvoiceRunEvents", "InvoiceRunEvents", "SaveDetails");

		echo "<div class='GroupedContent'>\n";
		echo "
<input type=hidden id='InvoiceRun.Id' name='InvoiceRun.Id' value='$intInvoiceRunId'/>
<div class='DefaultElement'>
	<div class='DefaultOutput Default ' style='margin-left: 60;'>" . OutputMask()->LongDate(DBO()->InvoiceRun->BillingDate->Value) . "</div>
	<div class='DefaultLabel'>
		<span id='invoiceActions.$id.Label.Text'>Billing Date : </span>
	</div>
</div>
<div class='DefaultElement'>
	<div class='DefaultOutput Default ' style='margin-left: 60;'>" . DBO()->InvoiceRun->InvoiceRun->AsValue() . "</div>
	<div class='DefaultLabel'>
		<span id='invoiceActions.$id.Label.Text'>Invoice Run : </span>
	</div>
</div>
";
		echo "</div>\n"; // GroupedContent
		echo "<div class='SmallSeparator'></div>\n";

		// Render the details of the invoice run events.
		// These need to be in the same order as the loaded automatic_invoice_action DBL.
		// To make this easy we should first create an array of event DBOs indexed on the automatic_invoice_action_id
		$arrEvents = array();
		$arrEventIds = array();
		foreach(DBL()->automatic_invoice_run_event as $dboInvoiceRunEvent)
		{
			$arrEvents[$dboInvoiceRunEvent->automatic_invoice_action_id->Value] = $dboInvoiceRunEvent;
		}

		Table()->InvoiceRunTable->SetHeader("Event", "Scheduled", "Actioned");
		Table()->InvoiceRunTable->SetWidth("40%", "40%", "20%");
		Table()->InvoiceRunTable->SetAlignment("Left", "Left", "Center");
		$now = time();
		foreach (DBL()->automatic_invoice_action_config as $invoiceActionConfig)
		{
			$id = $invoiceActionConfig->automatic_invoice_action_id->Value;
			$name = GetConstantDescription($id, 'AUTOMATIC_INVOICE_ACTION');//$invoiceAction->name->Value;

			// Get the event for this action
			$dboEvent = $arrEvents[$id];

			if (!$dboEvent->actioned_datetime->Value)
			{
				$fromYear = intval(date('Y'));
				$toYear = $fromYear + 1;

				$arrPaymentTerms = GetPaymentTerms(DBO()->InvoiceRun->customer_group_id->Value);
				$daysFromInvoice = $invoiceActionConfig->days_from_invoice->Value;
				$daysFromStartOfMonth = $daysFromInvoice + $arrPaymentTerms['invoice_day'];
				$startOfMonth = mktime(0, 0, 0, intval(date('m')), 0, intval(date('y')));
				$defaultTime = $startOfMonth + (24 * 60 * 60 * $daysFromStartOfMonth);

				if ($defaultTime < $now)
				{
					$defaultTime = $now;
				}

				$defaultYear = intval(date('Y', $defaultTime));
				$defaultMonth = intval(date('m', $defaultTime));
				$defaultDay = intval(date('d', $defaultTime));

				$eventId = $dboEvent->id->Value;
				$arrEventIds[] = $eventId;
				$strId = "automatic_invoice_run_event_$eventId.scheduled_datetime";
				$strHiddenId = "automatic_invoice_run_event_$eventId.Id";
				if (!$dboEvent->scheduled_datetime->Value)
				{
					$strValue = '';
				}
				else
				{
					$strValue = date('H:i d/m/Y', strtotime($dboEvent->scheduled_datetime->Value));
				}
				$strClass = 'ScheduledDatetime';

				$strScheduled  = "<div class='ScheduledDatetimeElement'>\n";
				$strScheduled .= "	<input type='hidden' id='$strHiddenId' name='$strHiddenId' value='$eventId'/>\n";
				$strScheduled .= "	<input type='text' id='$strId' name='$strId' value='$strValue' class='$strClass' style='width: 160px;'/>\n";
				$strScheduled .= "   <a style='position: relative;' href='javascript:DateChooser.showChooser(\"$strId\", $fromYear, $toYear, \"H:i d/m/Y\", true, true, true, $defaultYear, $defaultMonth, $defaultDay);'><img src='img/template/calendar_small.png' width='16' height='16' title='Calendar date picker' /></a>";
				$strScheduled .= "</div>\n";

				$strActioned = "Not Actioned";
			}
			else
			{
				$strScheduled = date('H:i d/m/Y', strtotime($dboEvent->scheduled_datetime->Value));
				$strActioned = date('H:i d/m/Y', strtotime($dboEvent->actioned_datetime->Value));
			}

			$strView = "<a href='$strViewHref' title='View Invoice Run'><img src='img/template/view.png'></img></a>";

			Table()->InvoiceRunTable->AddRow("$name : ", $strScheduled, $strActioned);
		}

		Table()->InvoiceRunTable->Render();

		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.InvoiceRunEvents.RenderDetailsForViewing($intInvoiceRunId);");
		$this->AjaxSubmit("Commit Changes", NULL, NULL, NULL, "InputSubmit", "InvoiceRunEventsSubmit");

		echo "</div></div>\n";

		// Initialise the InvoiceRunEvents object
		$strJavascript = "Vixen.InvoiceRunEvents.InitialiseEdit('{$this->_strContainerDivId}', new Array(" . implode(',', $arrEventIds) . "));";
		echo "<script type='text/javascript'>$strJavascript</script>\n";

		$this->FormEnd();
	}
}

?>
