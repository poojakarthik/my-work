//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// payment_popup.js
//----------------------------------------------------------------------------//
/**
 * payment_popup
 *
 * javascript required of the Make Payment popup
 *
 * javascript required of the Make Payment popup
 * 
 * 
 *
 * @file		payment_popup.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// VixenPaymentPopupClass
//----------------------------------------------------------------------------//
/**
 * VixenPaymentPopupClass
 *
 * Encapsulates all javascript required of the Make Payment popup
 *
 * Encapsulates all javascript required of the Make Payment popup
 * 
 *
 * @package	ui_app
 * @class	VixenPaymentPopupClass
 * 
 */
function VixenPaymentPopupClass()
{
	// This constant is required.  It should match the PAYMENT_TYPE_CREDIT_CARD constant defined in framework/definitions.php
	var PAYMENT_TYPE_CREDIT_CARD = 5;

	// This is an associative array where the key is the PaymentType Constant.
	// It is used to show the extra details required of the various Payment Types
	this._arrExtraDetail = {};
	
	// References to the various elements on the popup, which are delt with regularly
	this._elmAmount							= null;
	this._elmChargeSurcharge				= null;
	this._elmCreditCardType					= null;
	this._elmCreditCardMsg					= null;
	this._elmPaymentType					= null;
	this._elmCreditCardSurchargePercentage	= null;
	
	this._strPopupId = null;
	this._strContainerDivId = null;
	
	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the "Make Payment" popup and this DOM object that facilitates it
	 *  
	 * Initialises the "Make Payment" popup and this DOM object that facilitates it
	 *
	 * @param	array	arrExtraDetail			stores data required to render the various extra detail container divs
	 *											specific to the Payment Type declared
	 * @param	int		intInitialPaymentType	The initial Payment Type, which is displayed in the popup
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(arrExtraDetail, intInitialPaymentType, strPopupId, strContainerDivId)
	{
		// Store the Extra Detail information
		this._arrExtraDetail = arrExtraDetail;
		
		// Store the popup Id
		this._strPopupId = strPopupId;
		this._strContainerDivId = strContainerDivId;
		
		// Set the focus to the first input element
		$ID('AccountCombo').focus();
		
		// Retrieve references to the controls that are accessed often
		this._elmAmount				= $ID('Payment.Amount');
		this._elmChargeSurcharge	= $ID('Payment.ChargeSurcharge');
		this._elmCreditCardMsg		= $ID('MakePayment_CreditCardSurchargeMsg');
		this._elmCreditCardType		= $ID('Payment.CreditCardType');
		this._elmPaymentType		= $ID('Payment.PaymentType');
		this._elmCreditCardSurchargePercentage = $ID('Payment.CreditCardSurchargePercentage');
		
		// Initialise the Payment Type extra detail container
		this.DeclarePaymentType(intInitialPaymentType);
		
		// Register event listeners
		this._elmAmount.addEventListener("change", function(){Vixen.PaymentPopup.UpdateCreditMsg();}, true);
		this._elmChargeSurcharge.addEventListener("change", function(){Vixen.PaymentPopup.UpdateCreditMsg();}, true);
	}
	
	
	//------------------------------------------------------------------------//
	// DeclareAccount
	//------------------------------------------------------------------------//
	/**
	 * DeclareAccount
	 *
	 * Sets various related hidden controls when a selection is made from the Account combo box
	 *  
	 * Sets various related hidden controls when a selection is made from the Account combo box
	 *
	 * @param	obj		objComboBox		The HTML element that calls this method (the account combobox)
	 *
	 * @return	void
	 * @method
	 */
	this.DeclareAccount = function(objComboBox)
	{
		var strSelection;

		// Make sure there is a value specified
		if (!objComboBox.value)
		{
			return;
		}
		
		// Retrieve the value of the combo box
		intSelection = objComboBox.value;
		if (objComboBox.options[objComboBox.selectedIndex].hasAttribute("IsAccountGroup"))
		{
			// The payment is being applied to an account group
			document.getElementById("AccountToApplyTo.Id").value = intSelection;
			document.getElementById("AccountToApplyTo.IsGroup").value = 1;
		}
		else
		{
			// The payment is being applied to an individual account
			document.getElementById("AccountToApplyTo.Id").value = intSelection;
			document.getElementById("AccountToApplyTo.IsGroup").value = 0;
		}
	}
	
	//------------------------------------------------------------------------//
	// DeclarePaymentType
	//------------------------------------------------------------------------//
	/**
	 * DeclarePaymentType
	 *
	 * Event Handler for the OnChange event of the Payment Type Combobox
	 *  
	 * Event Handler for the OnChange event of the Payment Type Combobox
	 * Shows the specific extra detail input elements required of the currently selected Payment Type
	 *
	 * @param	int		intPaymentType	The selected Payment Type
	 *
	 * @return	void
	 * @method
	 */
	this.DeclarePaymentType = function(intPaymentType)
	{
		// Hide all the Extra Detail divs except the one relating to the new PaymentType, if it is visible
		var elmExtraDetailContainer = null;
		for (intKey in this._arrExtraDetail)
		{
			if (intKey != intPaymentType)
			{
				// Hide the div
				document.getElementById(this._arrExtraDetail[intKey]['ExtraDetailDivId']).style.display = "none";
			}
			else
			{
				// Retrieve the container div element
				elmExtraDetailContainer = document.getElementById(this._arrExtraDetail[intKey]['ExtraDetailDivId']);
			}
		}
		
		// Show the extra details relating to the PaymentType
		if (elmExtraDetailContainer)
		{
			elmExtraDetailContainer.style.display = "inline";
		}
		
		// Update the CreditCard msg
		this.UpdateCreditMsg();
	}

	//------------------------------------------------------------------------//
	// DeclareCreditCardType
	//------------------------------------------------------------------------//
	/**
	 * DeclareCreditCardType
	 *
	 * Event Handler for the OnChange event of the CreditCardType Combobox
	 *  
	 * Event Handler for the OnChange event of the CreditCardType Combobox
	 * Updates the Surcharge message as the surcharge is specific to the CreditCard Type
	 *
	 * @param	elment		elmOption	The currently selected option in the CreditCardType Combobox
	 *
	 * @return	void
	 * @method
	 */
	this.DeclareCreditCardType = function()
	{
		// Update the Surcharge hidden element
		this._elmCreditCardSurchargePercentage.value = this._elmCreditCardType.options[this._elmCreditCardType.selectedIndex].getAttribute("surcharge");
	
		// Update the Surcharge message
		this.UpdateCreditMsg();
	}
	
	this.UpdateCreditMsg = function()
	{
		var strCreditCard			= this._elmCreditCardType.options[this._elmCreditCardType.selectedIndex].innerHTML;
		var fltSurchargePercentage	= parseFloat(this._elmCreditCardSurchargePercentage.value);
		var strSurchargePercentage 	= Number(fltSurchargePercentage * 100);
		var fltAmount				= this.GetAmount();
		var fltSurcharge			= fltAmount * fltSurchargePercentage;
		var strSurcharge			= fltSurcharge.toFixed(2);
		
		var strMsg = "";
	
		if (isNaN(fltAmount))
		{
			// The value entered into the Amount textbox is not a number
			return;
		}
	
		if (this._elmChargeSurcharge.checked == true)
		{
			// The user wants to charge a surcharge
			var fltTotalCharge	= fltAmount + fltSurcharge;
			
			var strTotalCharge	= fltTotalCharge.toFixed(2);
			/*
			strMsg = 	strCreditCard + " payments incur a " + strSurchargePercentage + "% surcharge." +
						"<br />A payment of $" + fltAmount.toFixed(2) + " will incur a surcharge of $" + strSurcharge + "." +
						"<br /><span class='Green'>The amount to be entered into the EFTPOS machine is $"+ strTotalCharge +"</span>";
			*/
			strMsg = 	"<table align='center' width='300px' border='0' cellspacing='0' cellpadding='0'>" +
						"<tr><td align='left'>Payment Amount:</td><td align='right'><span class='Currency'>"+ fltAmount.toFixed(2) +"</span></td><td>&nbsp;</td></tr>" +
						"<tr><td>" + strSurchargePercentage + "% "+ strCreditCard +" Surcharge:</td><td align='right'><span class='Currency'>"+ strSurcharge +"</span></td><td>&nbsp;+</td></tr>" +
						"<tr><td></td><td align='right'>-----------------</td><td></td></tr>" +
						"<tr><td>Total Payment Amount:</td><td align='right'><span class='Currency'>"+ strTotalCharge +"</span></td><td></td></tr></table>" +
						"<br /><span align='center'>The amount to be entered into the EFTPOS machine is $"+ strTotalCharge +"</span>";
		}
		else
		{
			// The user does not want to charge a surcharge
			/*
			strMsg = 	strCreditCard + " payments incur a " + strSurchargePercentage + "% surcharge." +
						"<br />You have chosen to waive this surcharge." +
						"<br /><span class='Green'>The amount to be entered into the EFTPOS machine is $" + fltAmount.toFixed(2) + "</span>";
			*/
			strMsg = 	"<table align='center' width='300px' border='0' cellspacing='0' cellpadding='0'>" +
						"<tr><td align='left'>Payment Amount:</td><td align='right'><span class='Currency'>"+ fltAmount.toFixed(2) +"</span></td><td>&nbsp;</td></tr>" +
						"<tr><td>" + strSurchargePercentage + "% "+ strCreditCard +" Surcharge:</td><td align='right'><span class='Currency'>"+ strSurcharge +"</span></td><td>&nbsp;+</td></tr>" +
						"<tr><td>Surcharge Waived:</td><td align='right'><span class='Currency'>"+ strSurcharge +"</span></td><td>&nbsp;-</td></tr>" +
						"<tr><td></td><td align='right'>-----------------</td><td></td></tr>" +
						"<tr><td>Total Payment Amount:</td><td align='right'><span class='Currency'>"+ fltAmount.toFixed(2) +"</span></td><td></td></tr></table>" +
						"<br /><span style='align:center'>The amount to be entered into the EFTPOS machine is $"+ fltAmount.toFixed(2) + "</span>";
		}
	
		this._elmCreditCardMsg.innerHTML = strMsg;
	}
	
	// returns the payment amount as a float or NaN
	this.GetAmount = function()
	{
		var strAmount = this._elmAmount.value;
		if (strAmount[0] == "$")
		{
			// Strip the dollar sign from the amount
			strAmount =  strAmount.substr(1);
		}
		
		return parseFloat(strAmount);
	}
	
	// Event handler for the "Make Payment" button
	// This prompts the user to make sure that they want to make the payment
	this.MakePayment = function(bolConfirmed)
	{
		
		// Check that the Plan Change has been confirmed
		if (bolConfirmed == null)
		{
			var strMsg = "";
			// First check that an amount has been specified
			var fltAmount = this.GetAmount();
			
			if (isNaN(fltAmount))
			{
				// The amount is not a number
				Vixen.Popup.Alert("Please specify a payment amount");
				return;
			}
		
			// The payment amount is valid
			if (this._elmPaymentType.value == PAYMENT_TYPE_CREDIT_CARD)
			{
				var strCreditCard			= this._elmCreditCardType.options[this._elmCreditCardType.selectedIndex].innerHTML;
				var fltSurchargePercentage	= parseFloat(this._elmCreditCardSurchargePercentage.value);
				var strSurchargePercentage 	= Number(fltSurchargePercentage * 100);
				var fltSurcharge			= fltAmount * fltSurchargePercentage;

				// We are dealing with a Credit Card Payment
				if (this._elmChargeSurcharge.checked == true)
				{
					// The user wants to charge the surcharge amount
					var fltTotal = fltAmount + fltSurcharge;
					strMsg = 	strCreditCard + " payments incur a " + strSurchargePercentage +"% surcharge." +
								"<br />The payment of $" + fltAmount.toFixed(2) +" will incur a surcharge of $"+ fltSurcharge.toFixed(2) + "."+
								"<br /><span class='Green'>The amount to be entered into the EFTPOS machine is $" + fltTotal.toFixed(2) + "</span>"+
								"<br />The surcharge will be automatically added as a debit adjustment." +
								"<br /><br />Are you sure you want to commit this payment of $"+ fltTotal.toFixed(2) +"?";
				}
				else
				{
					// The user does not want to charge a surcharge amount
					strMsg = 	strCreditCard + " payments incur a " + strSurchargePercentage +"% surcharge."+
								"<br />You have chosen not to charge this." +
								"<br /><span class='Green'>The amount to be entered into the EFTPOS machine is $" + fltAmount.toFixed(2) + "</span>" +
								"<br /><br />Are you sure you want to commit this payment of $"+ fltAmount.toFixed(2) +"?";
				}
			}
			else
			{
				// We are not dealing with a Credit Card Payment
				var strMsg = 	"Are you sure you want to commit this payment of $" + fltAmount.toFixed(2) +"?";
			}
			
			Vixen.Popup.Confirm(strMsg, function(){Vixen.PaymentPopup.MakePayment(true);});
			return;
		}
		
		// Submit the form data
		Vixen.Ajax.SendForm("VixenForm_MakePayment", "Make Payment", "Payment", "Add", "", this._strPopupId, null, this._strContainerDivId);
	}


}

// Instanciate the object, if it doesn't already exist
if (Vixen.PaymentPopup == undefined)
{
	Vixen.PaymentPopup = new VixenPaymentPopupClass;
}
