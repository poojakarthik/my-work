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
	 *
	 * @method
	 */
	function __construct($intContext)
	{
		$this->_intContext = $intContext;
		
		// Load all java script specific to the page here
		// Note that if you execute any javascript in the Render function, that is included here, it will not have physically included it
		// in time to execute it.  In that case you will have to explicitly include the javascript file in the Render method
		// For example: echo "<script type='text/javascript' src='javascript/payment_popup.js'></script>\n";
		
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
		echo "<div class='PopupLarge'>\n";
		echo "<h2 class='Payment'>Make Payment</h2>\n";
		
		$this->FormStart("MakePayment", "Payment", "Add");
		
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
		echo "   <div class='DefaultLabel'>Account(s):</div>\n";
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
		echo "   <div class='DefaultLabel'>Payment Type:</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Payment.PaymentType' name='Payment.PaymentType'>\n";
		foreach ($GLOBALS['*arrConstant']['PaymentType'] as $intPaymentType=>$arrPaymentType)
		{
			$strDescription = $arrPaymentType['Description'];

			// Check if this Payment Type was the last one selected
			if ($intPaymentType == DBO()->Payment->PaymentType->Value)
			{
				$strSelected = "selected='selected'";
			}
			else
			{
				$strSelected = "";
			}
			
			echo "         <option id='Payment.$intPaymentType' value='$intPaymentType' $strSelected>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		DBO()->Payment->Amount->RenderInput(CONTEXT_DEFAULT, TRUE);
		DBO()->Payment->TXNReference->RenderInput(CONTEXT_DEFAULT, TRUE);
		
		// output the manditory field message
		echo "<div class='DefaultElement'><span class='RequiredInput'>*</span> : Required Field</div>\n";

		// Render the status message, if there is one
		DBO()->Status->Message->RenderOutput();
		
		// create the buttons
		echo "<div class='SmallSeperator'></div>\n";
		echo "<div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(\"{$this->_objAjax->strId}\");");
		$this->AjaxSubmit("Make Payment");
		echo "</div>\n";
		
		$this->FormEnd();
		
		// give the AccountCombo initial focus
		echo "<script type='text/javascript'>document.getElementById('AccountCombo').focus();</script>\n";
		echo "</div>\n";
	}
}

?>
