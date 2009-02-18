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
		
		DBO()->AccountToApplyTo->Id->RenderHidden();
		DBO()->AccountToApplyTo->IsGroup->RenderHidden();
		
		// create a combobox containing all Accounts or groups that the payment can be applied to
		$intAccountGroup = DBO()->Account->AccountGroup->Value;
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Account :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='AccountCombo' onchange='Vixen.PaymentPopup.DeclareAccount(this)' style='width:100%'>\n";
		
		// only give the option to apply the payment against all the accounts in the group, if there is more than 1 account in the group
		if (DBL()->AvailableAccounts->RecordCount() > 1)
		{
			// check if this is the record that is the currently selected row
			$strSelected = (DBO()->AccountToApplyTo->IsGroup->Value) ? "selected='selected'" : "";
			
			echo "         <option id='Account.Group' value='$intAccountGroup' IsAccountGroup='IsAccountGroup' $strSelected style='width:100%'>Account Group: $intAccountGroup</option>\n";
		}
		
		// add each account that belongs to the account group
		foreach (DBL()->AvailableAccounts as $dboAccount)
		{
			// check if the row that you are adding is the currently selected row
			$strSelected = (($dboAccount->Id->Value == DBO()->AccountToApplyTo->Id->Value) && (!DBO()->AccountToApplyTo->IsGroup->Value)) ? "selected='selected'" : "";

			if ($dboAccount->BusinessName->Value)
			{
				$strAccountName = ": " . $dboAccount->BusinessName->Value;
			}
			elseif ($dboAccount->TradingName->Value)
			{
				$strAccountName = ": " . $dboAccount->TradingName->Value;
			}
			else
			{
				$strAccountName = "";
			}
			
			$intAccountId = $dboAccount->Id->Value;
			$strDescription = $dboAccount->Id->Value . $strAccountName;
			echo "         <option id='Account.$intAccountId' value='$intAccountId' $strSelected>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";
		
		// Payment Type combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Payment Type :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Payment.PaymentType' style='width: 250px' name='Payment.PaymentType' onchange='Vixen.PaymentPopup.DeclarePaymentType(this.value)'>\n";
		foreach ($GLOBALS['*arrConstant']['payment_type'] as $intPaymentType=>$arrPaymentType)
		{
			$strDescription = $arrPaymentType['Description'];

			// Check if this Payment Type was the last one selected
			$strSelected = ($intPaymentType == DBO()->Payment->PaymentType->Value) ? "selected='selected'" : "";
			
			echo "         <option id='Payment.$intPaymentType' value='$intPaymentType' $strSelected>$strDescription</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		DBO()->Payment->Amount->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask, Array("style:width"=>"250px"));
		DBO()->Payment->TXNReference->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask, Array("style:width"=>"250px", "attribute:maxlength"=>100));
		
		// Draw the Extra Detail Divs
		// If any other Payment methods require extra Input controls then add them here, in exactly the same way that the credit card
		// details have been added
		//TODO! when required
		
		// Credit Card Details
		$strShowCreditCardDetail = (DBO()->Payment->PaymentType->Value == PAYMENT_TYPE_CREDIT_CARD) ? "display:inline;" : "display:none;";
		$strCreditCardDetailId = "MakePayment_CreditCardDetails";
		
		// Initialise the credit card type, if it has not been set yet (it will default to VISA)
		DBO()->Payment->CreditCardType = (DBO()->Payment->CreditCardType->Value) ? DBO()->Payment->CreditCardType->Value : CREDIT_CARD_VISA;
		
		echo "<div id='$strCreditCardDetailId' style='$strShowCreditCardDetail'>\n";
		
		DBO()->Payment->ChargeSurcharge->RenderInput();
		
		// Load the surcharges for the various Credit Card Types
		$objCreditCardTypes = Credit_Card_Type::listAll();
		
		foreach ($objCreditCardTypes as $objCreditCardType)
		{
			// This will build an array specifying the the surcharge for each Credit Card type, with the Description of the Credit Card as the key
			$arrCCSurcharges[$objCreditCardType->id] = $objCreditCardType->surcharge;
		}
		
		// CreditCardType combobox
		echo "<div class='DefaultElement'>\n";
		echo "   <div class='DefaultLabel'>&nbsp;&nbsp;Credit Card Type :</div>\n";
		echo "   <div class='DefaultOutput'>\n";
		echo "      <select id='Payment.CreditCardType' style='width:250px' name='Payment.CreditCardType' onchange='Vixen.PaymentPopup.DeclareCreditCardType()'>\n";
		foreach ($GLOBALS['*arrConstant']['credit_card_type'] as $intCreditCard=>$arrCreditCard)
		{
			// Check if this Credit Card Type was the last one selected
			$strSelected = "";
			if (DBO()->Payment->CreditCardType->Value == $intCreditCard)
			{
				$strSelected = "selected='selected'";
				$fltCurrentSurcharge = $arrCCSurcharges[$intCreditCard];
			}
			
			$fltSurcharge = $arrCCSurcharges[$intCreditCard];
			
			echo "         <option value='$intCreditCard' surcharge='$fltSurcharge' $strSelected>{$arrCreditCard['Description']}</option>\n";
		}
		echo "      </select>\n";
		echo "   </div>\n";
		echo "</div>\n";

		// Render the hidden input to store the CreditCardSurchargePercentage
		DBO()->Payment->CreditCardSurchargePercentage = $fltCurrentSurcharge;
		DBO()->Payment->CreditCardSurchargePercentage->RenderHidden();

		// Render the Textbox for the credit card number
		DBO()->Payment->CreditCardNum->RenderInput(CONTEXT_DEFAULT, TRUE, $bolApplyOutputMask, Array("style:width"=>"250px"));

		// Output message describing Credit Card Surcharge
		$strCreditCardType = GetConstantDescription(DBO()->Payment->CreditCardType->Value, "CreditCard");
		echo "<div class='ContentSeparator'></div>\n";
		echo "<span id='MakePayment_CreditCardSurchargeMsg' style='line-height: 1.2'>$strSurchargeMsg</span>\n";
		
		echo "</div>\n"; //Payment_CreditCardDetails
		
		
		echo "</div>\n"; //WideForm
		
		// Create the buttons
		echo "<div class='ButtonContainer'><div class='Right'>\n";
		$this->Button("Cancel", "Vixen.Popup.Close(this);");
		$this->Button("Make Payment", "Vixen.PaymentPopup.MakePayment();");
		echo "</div></div>\n";	
		
		// Build data for the DOM object
		$intPaymentType = (DBO()->Payment->PaymentType->Value) ? DBO()->Payment->PaymentType->Value : 0;
		$arrExtraDetail[PAYMENT_TYPE_CREDIT_CARD]['ExtraDetailDivId']	= $strCreditCardDetailId;
		$jsonExtraDetail = Json()->encode($arrExtraDetail);
		
		// Initilise the popup
		echo "<script type='text/javascript'>Vixen.PaymentPopup.Initialise($jsonExtraDetail, $intPaymentType, '{$this->_objAjax->strId}', '{$this->_strContainerDivId}')</script>\n";

		$this->FormEnd();
	}
}

?>
