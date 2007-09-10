<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// payment_add.php
//----------------------------------------------------------------------------//
/**
 * payment_add
 *
 * HTML Template for the Make Payment HTML object
 *
 * HTML Template for the Make Payment HTML object
 * This class is responsible for defining and rendering the layout of the HTML Template object
 * which displays the form used to make a payment.
 *
 * @file		payment_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// HtmlTemplateAccountPaymentAdd
//----------------------------------------------------------------------------//
/**
 * HtmlTemplateAccountPaymentAdd
 *
 * HTML Template class for the Make Payment HTML object
 *
 * HTML Template class for the Make Payment HTML object
 * displays the form used to add an adjustment
 *
 * @package	ui_app
 * @class	HtmlTemplateAccountPaymentAdd
 * @extends	HtmlTemplate
 */
class HtmlTemplateAccountPaymentAdd extends HtmlTemplate
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
		
		// Load all java script specific to the page here
		$this->LoadJavascript("payment_popup");
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
		// Only apply the output mask if the DBO()->Payment is not invalid
		$bolApplyOutputMask = !DBO()->Payment->IsInvalid();
	
		$this->FormStart("MakePayment", "Payment", "Add");

		echo "<div class='WideForm'>\n";
		
		// include all the properties necessary to add the record, which shouldn't have controls visible on the form
		DBO()->Account->Id->RenderHidden();
		
		// check if the popup is being opened or being redrawn 
		if (!DBO()->AccountToApplyTo->Id->Value)
		{
			// The popup has just been opened and nothing has been sent yet, so set 
			DBO()->AccountToApplyTo->Id			= DBO()->Account->Id->Value;
			DBO()->AccountToApplyTo->IsGroup	= 0;
		}
		DBO()->AccountToApplyTo->Id->RenderHidden();
		DBO()->AccountToApplyTo->IsGroup->RenderHidden();
		
		// create a combobox containing all Accounts or groups that the payment can be applied to
		$intAccountGroup = DBO()->Account->AccountGroup->Value;
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Account:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='AccountCombo' onchange='Vixen.PaymentPopup.DeclareAccount(this)'>\n";
		
		// only give the option to apply the payment against all the accounts in the group, if there is more than 1 account in the group
		if (DBL()->AvailableAccounts->RecordCount() > 1)
		{
			// check if this is the record that is currently selected row
			if (DBO()->AccountToApplyTo->IsGroup->Value)
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}
			
			echo "         <option id='Account.Group' value='AccountGroup:$intAccountGroup' $strSelected>Account Group: $intAccountGroup</option>\n";
		}
		
		// add each account that belongs to the account group
		foreach (DBL()->AvailableAccounts as $dboAccount)
		{
			// check if the row that you are adding is the currently selected row
			if (($dboAccount->Id->Value == DBO()->AccountToApplyTo->Id->Value) && (!DBO()->AccountToApplyTo->IsGroup->Value))
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}

			$intAccountId = $dboAccount->Id->Value;
			$strDescription = $dboAccount->Id->Value .": ". $dboAccount->BusinessName->Value;
			echo "         <option id='Account.$intAccountId' value='$intAccountId' $strSelected>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// Payment Type combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Payment Type:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Payment.PaymentType' name='Payment.PaymentType'>\n";
		foreach ($GLOBALS['*arrConstant']['PaymentType'] as $intPaymentType=>$arrPaymentType)
		{
			$strDescription = $arrPaymentType['Description'];

			// Check if this Payment Type was the last one selected
			$strSelected = ($intPaymentType == DBO()->Payment->PaymentType->Value) ? "selected='selected'" : "";
			
			echo "         <option id='Payment.$intPaymentType' value='$intPaymentType' $strSelected>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		DBO()->Payment->Amount->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		DBO()->Payment->TXNReference->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask);
		
		// output the manditory field message
		echo "<div class='DefaultElement'><span class='RequiredInput'>*</span> : Required Field</div>\n";
		
		echo "</div>\n";  //WideForm
		
		// create the buttons
		echo "<div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		$this->AjaxSubmit("Make Payment");
		echo "</div>\n";
		
		
		// give the AccountCombo initial focus
		echo "<script type='text/javascript'>document.getElementById('AccountCombo').focus();</script>\n";

		$this->FormEnd();
	}
}

?>
