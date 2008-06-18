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
		$intPaymentTerms = DBO()->payment_terms->payment_terms->Value - DBO()->payment_terms->invoice_day->Value;
		$intOverdueNoticeDays = DBO()->payment_terms->overdue_notice_days->Value - DBO()->payment_terms->payment_terms->Value;
		$intSuspensionNoticeDays = DBO()->payment_terms->suspension_notice_days->Value - DBO()->payment_terms->overdue_notice_days->Value;
		$intFinalDemandNoticeDays = DBO()->payment_terms->final_demand_notice_days->Value - DBO()->payment_terms->suspension_notice_days->Value;
		$decMinimumBalanceToPursue = DBO()->payment_terms->minimum_balance_to_pursue->Value;

		$position = $this->getOrdinal($intInvoiceDay);

		// Render the details of the PaymentTerms
		echo "

<div class='DefaultElement'>
	<div id='payment_terms.invoice_day.Output' name='payment_terms.invoice_day' class='DefaultOutput Default '>$intInvoiceDay$position day of month.</div>
	<div id='payment_terms.invoice_day.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.invoice_day.Label.Text'>Invoice Day : </span>
	</div>
</div>
<div class='DefaultElement'>
	<div id='payment_terms.payment_terms.Output' name='payment_terms.payment_terms' class='DefaultOutput Default '>$intPaymentTerms days from Invoice Day.</div>
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"payment_terms_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->payment_terms->Value . "</span> days from start of month.</span>
	<div id='payment_terms.payment_terms.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.payment_terms.Label.Text'>Payment Terms : </span>
	</div>
</div>
<div class='DefaultElement'>
	<div id='payment_terms.overdue_notice_days.Output' name='payment_terms.overdue_notice_days' class='DefaultOutput Default '>$intOverdueNoticeDays days after Payment Terms have passed.</div>
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"payment_terms_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->overdue_notice_days->Value . "</span> days from start of month.</span>
	<div id='payment_terms.overdue_notice_days.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.overdue_notice_days.Label.Text'>Overdue Notices Issued : </span>
	</div>
</div>
<div class='DefaultElement'>
	<div id='payment_terms.suspension_notice_days.Output' name='payment_terms.suspension_notice_days' class='DefaultOutput Default '>$intSuspensionNoticeDays days after issuing Outstanding (Late) Notices.</div>
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"payment_terms_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->suspension_notice_days->Value . "</span> days from start of month.</span>
	<div id='payment_terms.suspension_notice_days.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.suspension_notice_days.Label.Text'>Suspension Notices Issued : </span>

	</div>
</div>
<div class='DefaultElement'>
	<div id='payment_terms.final_demand_notice_days.Output' name='payment_terms.final_demand_notice_days' class='DefaultOutput Default '>$intFinalDemandNoticeDays days after issuing Suspension Notices.</div>
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"payment_terms_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->final_demand_notice_days->Value . "</span> days from start of month.</span>
	<div id='payment_terms.final_demand_notice_days.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.final_demand_notice_days.Label.Text'>Final Demand Issued : </span>
	</div>

</div>
<div class='DefaultElement'>
	<div id='payment_terms.minimum_balance_to_pursue.Output' name='payment_terms.minimum_balance_to_pursue' class='DefaultOutput Default '>\$$decMinimumBalanceToPursue</div>
	<div id='payment_terms.final_demand_notice_days.Label' class='DefaultLabel'>
		<span> &nbsp;</span>
		<span id='payment_terms.final_demand_notice_days.Label.Text'>Minimum Balance to Pursue : </span>
	</div>

</div>

		";

		echo "</div>\n"; // GroupedContent

		// Render the buttons
		if ($bolUserIsSuperAdmin)
		{
			echo "<div class='ButtonContainer'><div class='Right'>\n";
			$this->Button("Edit Details", "Vixen.PaymentTermsDisplay.RenderDetailsForEditing();");
			echo "</div></div>\n";
		}
		else
		{
			echo "<div class='SmallSeparator'></div>\n";
		}

		// Initialise the PaymentTerms object and register the OnPaymentTermsUpdate Listener
		$strJavascript = "function _payment_terms_onload() {Vixen.PaymentTermsDisplay.InitialiseView('{$this->_strContainerDivId}');}window.addEventListener('load', _payment_terms_onload, false)";
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
		$intPaymentTerms = DBO()->payment_terms->payment_terms->Value - DBO()->payment_terms->invoice_day->Value;
		$intOverdueNoticeDays = DBO()->payment_terms->overdue_notice_days->Value - DBO()->payment_terms->payment_terms->Value;
		$intSuspensionNoticeDays = DBO()->payment_terms->suspension_notice_days->Value - DBO()->payment_terms->overdue_notice_days->Value;
		$intFinalDemandNoticeDays = DBO()->payment_terms->final_demand_notice_days->Value - DBO()->payment_terms->suspension_notice_days->Value;
		$decMinimumBalanceToPursue = DBO()->payment_terms->minimum_balance_to_pursue->Value;

		$position = $this->getOrdinal($intInvoiceDay);

		echo "

<input id=\"payment_terms.Id\" name=\"payment_terms.Id\" value=\"\" type=\"hidden\">
<input id=\"payment_terms.invoice_day\" name=\"payment_terms.invoice_day\" value=\"" . DBO()->payment_terms->invoice_day->Value . "\" type=\"hidden\">
<input id=\"payment_terms.payment_terms\" name=\"payment_terms.payment_terms\" value=\"" . DBO()->payment_terms->payment_terms->Value . "\" type=\"hidden\">
<input id=\"payment_terms.overdue_notice_days\" name=\"payment_terms.overdue_notice_days\" value=\"" . DBO()->payment_terms->overdue_notice_days->Value . "\" type=\"hidden\">
<input id=\"payment_terms.suspension_notice_days\" name=\"payment_terms.suspension_notice_days\" value=\"" . DBO()->payment_terms->suspension_notice_days->Value . "\" type=\"hidden\">
<input id=\"payment_terms.final_demand_notice_days\" name=\"payment_terms.final_demand_notice_days\" value=\"" . DBO()->payment_terms->final_demand_notice_days->Value . "\" type=\"hidden\">
<input id=\"payment_terms.minimum_balance_to_pursue\" name=\"payment_terms.minimum_balance_to_pursue\" value=\"" . DBO()->payment_terms->minimum_balance_to_pursue->Value . "\" type=\"hidden\">

<div class=\"DefaultElement\">
	<input id=\"invoice_day\" value=\"$intInvoiceDay\" class=\"DefaultInputText Default\" style=\"width: 50px;\" maxlength=\"255\" type=\"text\">
	<span style=\"margin-left: 200px; \"><span id=\"invoice_day_display\">" . $position . "</span> day of month.</span>
	<div id=\"payment_terms.invoice_day.Label\" class=\"DefaultLabel\">
	<span class=\"RequiredInput\">*</span>
	<span id=\"payment_terms.invoice_day.Label.Text\">Invoice Day : </span></div>
</div>

<div class=\"DefaultElement\">
	<input id=\"payment_terms\" value=\"$intPaymentTerms\" class=\"DefaultInputText Default\" style=\"width: 50px;\" maxlength=\"255\" type=\"text\">
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"payment_terms_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->payment_terms->Value . "</span> days from start of month.</span>
	<span style=\"margin-left: 200px;\"> days from Invoice Day.</span>
	<div id=\"payment_terms.payment_terms.Label\" class=\"DefaultLabel\">
	<span class=\"RequiredInput\">*</span>
	<span id=\"payment_terms.payment_terms.Label.Text\">Payment Terms : </span></div>
</div>

<div class=\"DefaultElement\">
	<input id=\"overdue_notice_days\" value=\"$intOverdueNoticeDays\" class=\"DefaultInputText Default\" style=\"width: 50px;\" maxlength=\"255\" type=\"text\">
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"overdue_notice_days_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->overdue_notice_days->Value . "</span> days from start of month.</span>
	<span style=\"margin-left: 200px;\"> days after Payment Terms have passed.</span>
	<div id=\"payment_terms.overdue_notice_days.Label\" class=\"DefaultLabel\">
	<span class=\"RequiredInput\">*</span>
	<span id=\"payment_terms.overdue_notice_days.Label.Text\">Overdue Notices Issued : </span></div>
</div>

<div class=\"DefaultElement\">
	<input id=\"suspension_notice_days\" value=\"$intSuspensionNoticeDays\" class=\"DefaultInputText Default\" style=\"width: 50px;\" maxlength=\"255\" type=\"text\">
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"suspension_notice_days_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->suspension_notice_days->Value . "</span> days from start of month.</span>
	<span style=\"margin-left: 200px;\"> days after issuing Outstanding (Late) Notices.</span>
	<div id=\"payment_terms.suspension_notice_days.Label\" class=\"DefaultLabel\">
	<span class=\"RequiredInput\">*</span>
	<span id=\"payment_terms.suspension_notice_days.Label.Text\">Suspension Notices Issued : </span></div>
</div>

<div class=\"DefaultElement\">
	<input id=\"final_demand_notice_days\" value=\"$intFinalDemandNoticeDays\" class=\"DefaultInputText Default\" style=\"width: 50px;\" maxlength=\"255\" type=\"text\">
	<span style=\"position: absolute; left: 550px; top: 4px;\">= <span id=\"final_demand_notice_days_display\" style=\"font-weight: bold;\">" . DBO()->payment_terms->final_demand_notice_days->Value . "</span> days from start of month.</span>
	<span style=\"margin-left: 200px;\"> days after issuing Suspension Notices.</span>
	<div id=\"payment_terms.final_demand_notice_days.Label\" class=\"DefaultLabel\">
	<span class=\"RequiredInput\">*</span>
	<span id=\"payment_terms.final_demand_notice_days.Label.Text\">Final Demands Issued : </span></div>
</div>

<div class=\"DefaultElement\">
	<input id=\"minimum_balance_to_pursue\" value=\"$decMinimumBalanceToPursue\" class=\"DefaultInputText Default\" style=\"width: 50px;\" maxlength=\"255\" type=\"text\">
	<div id=\"payment_terms.minimum_balance_to_pursue.Label\" class=\"DefaultLabel\">
	<span class=\"RequiredInput\">*</span>
	<span id=\"payment_terms.minimum_balance_to_pursue.Label.Text\">Minimum Balance to Pursue : </span></div>
</div>

		";

		echo "</div>\n"; // GroupedContent

		// Render the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.PaymentTermsDisplay.CancelEdit();");
		$this->AjaxSubmit("Commit Changes");
		echo "</div></div>\n";

		// Initialise the PaymentTermsDisplay object
		$strJavascript = "Vixen.PaymentTermsDisplay.InitialiseEdit('{$this->_strContainerDivId}');";
		echo "<script type='text/javascript'>$strJavascript</script>\n";

		$this->FormEnd();
	}
}

?>
