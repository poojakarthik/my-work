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
 * HTML Template for the details of PaymentTerms 
 *
 * HTML Template for the details of PaymentTerms 
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
 * HTML Template for the details of PaymentTerms
 *
 * HTML Template for the details of PaymentTerms
 *
 * @prefix	<prefix>
 *
 * @package	ui_app
 * @class	HtmlTemplatePaymentTermsDisplay
 * @extends	HtmlTemplate
 */
class HtmlTemplatePaymentTermsDisplay extends HtmlTemplate
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
		
		$this->LoadJavascript("payment_terms_display");
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
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);

		switch ($this->_intContext)
		{
			case HTML_CONTEXT_EDIT:
				if ($bolUserIsSuperAdmin)
				{
					$this->_RenderForEditing();
					break;
				}
			case HTML_CONTEXT_VIEW:
			default:
				$this->_RenderForViewing();
				break;
		}
	}

	//------------------------------------------------------------------------//
	// _RenderForViewing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForViewing()
	 *
	 * Renders the PaymentTermsDisplay in "View" mode
	 *
	 * Renders the PaymentTermsDisplay in "View" mode
	 *
	 * @method
	 */
	private function _RenderForViewing()
	{
		$bolUserIsSuperAdmin = AuthenticatedUser()->UserHasPerm(PERMISSION_SUPER_ADMIN);
		
		echo "<div class='GroupedContent'>\n";

		$intInvoiceDay = DBO()->payment_terms->invoice_day->Value;
		$intPaymentTerms = DBO()->payment_terms->payment_terms->Value;
		$decMinimumBalanceToPursue = DBO()->payment_terms->minimum_balance_to_pursue->Value;
		$decLatePaymentFee = DBO()->payment_terms->late_payment_fee->Value;

		$position = $this->getOrdinal($intInvoiceDay);

		// Render the details of the PaymentTerms
		echo "

<div class='DefaultElement'>
	<div id='payment_terms.invoice_day.Output' name='payment_terms.invoice_day' class='DefaultOutput Default ' style='margin-left: 60;'>$intInvoiceDay$position day of month.</div>
	<div id='payment_terms.invoice_day.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.invoice_day.Label.Text'>Invoice Day : </span>
	</div>
</div>
<div class='DefaultElement'>
	<div id='payment_terms.payment_terms.Output' name='payment_terms.payment_terms' class='DefaultOutput Default ' style='margin-left: 60;'>$intPaymentTerms days from Invoice Day.</div>
	<div id='payment_terms.payment_terms.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.payment_terms.Label.Text'>Payment Terms : </span>
	</div>
</div>

<div class='DefaultElement'>
	<div id='payment_terms.minimum_balance_to_pursue.Output' name='payment_terms.minimum_balance_to_pursue' class='DefaultOutput Default ' style='margin-left: 60;'>\$$decMinimumBalanceToPursue</div>
	<div id='payment_terms.minimum_balance_to_pursue.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.minimum_balance_to_pursue.Label.Text'>Minimum Balance to Pursue : </span>
	</div>

</div>
<div class='DefaultElement'>
	<div id='payment_terms.late_payment_fee.Output' name='payment_terms.late_payment_fee' class='DefaultOutput Default' style='margin-left: 60;'>\$$decLatePaymentFee</div>
	<div id='payment_terms.late_payment_fee.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.late_payment_fee.Label.Text'>Late Payment Fee (excl. GST) : </span>
	</div>

</div>

		";

		foreach (DBL()->automatic_invoice_action_config as $invoiceAction)
		{
			$id = $invoiceAction->id->Value;
			$name = GetConstantDescription($invoiceAction->automatic_invoice_action_id->Value, 'AUTOMATIC_INVOICE_ACTION');
			$daysFromInvoice = $invoiceAction->days_from_invoice->Value;
			$responseDays = $invoiceAction->response_days->Value;

			echo "

<div class='DefaultElement'>
	<div id='invoiceActions.$id.Output' name='payment_terms.payment_terms' class='DefaultOutput Default ' style='margin-left: 60;'>$daysFromInvoice days from Invoice Day.
		<span name='payment_terms.payment_terms' class='DefaultOutput Default ' style='position: absolute; left: 175px;'>Response time: $responseDays days.</span>
	</div>
	
	<div id='invoiceActions.$id.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='invoiceActions.$id.Label.Text'>$name : </span>
	</div>
</div>

";
		}

		echo "</div>\n"; // GroupedContent

		// Render the buttons
		if ($bolUserIsSuperAdmin)
		{
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Edit Details", "Vixen.PaymentTermsDisplay.RenderDetailsForEditing(" . DBO()->payment_terms->customer_group_id->Value . ");");
			echo "</div></div>\n";
		}
		else
		{
			echo "<div class='SmallSeparator'></div>\n";
		}

		// Initialise the PaymentTerms object and register the OnPaymentTermsUpdate Listener
		$strJavascript = "function _payment_terms_onload() {Vixen.PaymentTermsDisplay.InitialiseView('{$this->_strContainerDivId}', " . DBO()->payment_terms->customer_group_id->Value . ");}window.addEventListener('load', _payment_terms_onload, false)";
		echo "<script type='text/javascript'>$strJavascript</script>\n";
	}

	private function getOrdinal($intNumber)
	{
		$strOrdinal = 'th';
		$intEnd = $intNumber%10;
		if (($intNumber%100) - $intEnd != 10)
		{
			if ($intEnd == 1)
			{
				$strOrdinal = 'st';
			}
			elseif ($intEnd == 2)
			{
				$strOrdinal = 'nd';
			}
			elseif ($intEnd == 3)
			{
				$strOrdinal = 'rd';
			}
		}
		return $strOrdinal;
	}
	
	//------------------------------------------------------------------------//
	// _RenderForEditing
	//------------------------------------------------------------------------//
	/**
	 * _RenderForEditing()
	 *
	 * Renders the PaymentTermsDisplay in "Edit" mode
	 *
	 * Renders the PaymentTermsDisplay in "Edit" mode
	 *
	 * @method
	 */
	private function _RenderForEditing()
	{
	
		$this->FormStart("PaymentTermsDisplay", "PaymentTerms", "SaveDetails");

		echo "<div class='GroupedContent'>\n";

		// Render hidden values
		$intInvoiceDay = DBO()->payment_terms->invoice_day->Value;
		$intPaymentTerms = DBO()->payment_terms->payment_terms->Value;

		$decMinimumBalanceToPursue = DBO()->payment_terms->minimum_balance_to_pursue->Value;
		$decLatePaymentFee = DBO()->payment_terms->late_payment_fee->Value;

		$position = $this->getOrdinal($intInvoiceDay);
		$arrInvoiceActionIds = array();

		echo "
<input id=\"payment_terms.Id\" name=\"payment_terms.Id\" value=\"\" type=\"hidden\">
<input id=\"payment_terms.customer_group_id\" name=\"payment_terms.customer_group_id\" value=\"" . DBO()->payment_terms->customer_group_id->Value . "\" type=\"hidden\">
<input id=\"payment_terms.invoice_day\" name=\"payment_terms.invoice_day\" value=\"" . DBO()->payment_terms->invoice_day->Value . "\" type=\"hidden\">
<input id=\"payment_terms.payment_terms\" name=\"payment_terms.payment_terms\" value=\"" . DBO()->payment_terms->payment_terms->Value . "\" type=\"hidden\">
<input id=\"payment_terms.minimum_balance_to_pursue\" name=\"payment_terms.minimum_balance_to_pursue\" value=\"" . DBO()->payment_terms->minimum_balance_to_pursue->Value . "\" type=\"hidden\">
<input id=\"payment_terms.late_payment_fee\" name=\"payment_terms.late_payment_fee\" value=\"" . DBO()->payment_terms->late_payment_fee->Value . "\" type=\"hidden\">

<div class=\"DefaultElement\">
	<input id=\"invoice_day\" value=\"$intInvoiceDay\" class=\"DefaultInputText Default\" style=\"width: 50px; margin-left: 60;\" maxlength=\"255\" type=\"text\">
	<span style=\"margin-left: 200px; \"><span id=\"invoice_day_display\">" . $position . "</span> day of month.</span>
	<div id=\"payment_terms.invoice_day.Label\" class=\"DefaultLabel\">
		<span class=\"RequiredInput\">*</span>
		<span id=\"payment_terms.invoice_day.Label.Text\">Invoice Day : </span>
	</div>
</div>

<div class=\"DefaultElement\">
	<input id=\"payment_terms\" value=\"$intPaymentTerms\" class=\"DefaultInputText Default\" style=\"width: 50px; margin-left: 60;\" maxlength=\"255\" type=\"text\">
	<span style=\"margin-left: 200px;\"> days from Invoice Day.</span>
	<div id=\"payment_terms.payment_terms.Label\" class=\"DefaultLabel\">
		<span class=\"RequiredInput\">*</span>
		<span id=\"payment_terms.payment_terms.Label.Text\">Payment Terms : </span>
	</div>
</div>


<div class=\"DefaultElement\">
	<input id=\"minimum_balance_to_pursue\" value=\"$decMinimumBalanceToPursue\" class=\"DefaultInputText Default\" style=\"width: 50px; margin-left: 60;\" maxlength=\"255\" type=\"text\">
	<div id=\"payment_terms.minimum_balance_to_pursue.Label\" class=\"DefaultLabel\">
		<span class=\"RequiredInput\">*</span>
		<span id=\"payment_terms.minimum_balance_to_pursue.Label.Text\">Minimum Balance to Pursue : </span>
	</div>
</div>

<div class=\"DefaultElement\">
	<input id=\"late_payment_fee\" value=\"$decLatePaymentFee\" class=\"DefaultInputText Default\" style=\"width: 50px; margin-left: 60;\" maxlength=\"255\" type=\"text\">
	<div id=\"payment_terms.late_payment_fee.Label\" class=\"DefaultLabel\">
		<span class=\"RequiredInput\">*</span>
		<span id=\"payment_terms.late_payment_fee.Label.Text\">Late Payment Fee (excl. GST) : </span>
	</div>
</div>

		";

		foreach (DBL()->automatic_invoice_action_config as $invoiceAction)
		{
			$id = $invoiceAction->automatic_invoice_action_id->Value;
			$name = GetConstantDescription($invoiceAction->automatic_invoice_action_id->Value, 'AUTOMATIC_INVOICE_ACTION');
			$daysFromInvoice = $invoiceAction->days_from_invoice->Value;
			$responseDays = $invoiceAction->response_days->Value;
			$arrInvoiceActionIds[] = $id;
			echo "<input name=\"automatic_invoice_action_$id.Id\" value=\"$id\" type=\"hidden\">\n";
			echo "<input id=\"invoiceActions[$id]\" name=\"automatic_invoice_action_$id.days_from_invoice\" value=\"$daysFromInvoice\" type=\"hidden\">\n";
			echo "<input id=\"invoiceActionResponses[$id]\" name=\"automatic_invoice_action_$id.response_days\" value=\"$responseDays\" type=\"hidden\">\n";
			
			echo "

<div class=\"DefaultElement\">
	<input id=\"invoiceActions.$id\" value=\"$daysFromInvoice\" class=\"DefaultInputText Default\" style=\"width: 50px; margin-left: 60;\" maxlength=\"255\" type=\"text\">
	<span style=\"margin-left: 200px;\"> days from Invoice Day.</span>

	<div style='position: absolute; display: inline; left: 470px;'>
		<span>Response time : </span>
	<input id=\"invoiceActionResponses.$id\" value=\"$responseDays\" class=\"DefaultInputText Default\" style=\"left: 0; width: 50px; margin-left: 0;\" maxlength=\"255\" type=\"text\">
	<span style=\"\"> days</span>
	</div>

	<div id=\"invoiceActions.$id.Label\" class=\"DefaultLabel\">
		<span class=\"RequiredInput\">*</span>
		<span id=\"invoiceActions.$id.Label.Text\">$name</span><span> : </span>
	</div>
</div>

			";
		}


		echo "</div>\n"; // GroupedContent

		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.PaymentTermsDisplay.CancelEdit(" . DBO()->payment_terms->customer_group_id->Value . ");");
		$this->AjaxSubmit("Commit Changes", NULL, NULL, NULL, "InputSubmit", "PaymentTermsSubmit");

		echo "</div></div>\n";

		// Initialise the PaymentTermsDisplay object
		$strJavascript = "Vixen.PaymentTermsDisplay.InitialiseEdit('{$this->_strContainerDivId}', new Array(" . implode(',', $arrInvoiceActionIds) . "),  " . DBO()->payment_terms->customer_group_id->Value . ");";
		echo "<script type='text/javascript'>$strJavascript</script>\n";

		$this->FormEnd();
	}
}

?>
