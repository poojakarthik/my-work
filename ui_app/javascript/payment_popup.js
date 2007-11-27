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
	// This is an associative array where the key is the PaymentType Constant.
	// It is used to show the extra details required of the various Payment Types
	this.arrExtraDetail = {};
	
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
	this.Initialise = function(arrExtraDetail, intInitialPaymentType)
	{
		// Store the Extra Detail information
		this.arrExtraDetail = arrExtraDetail;
		
		// Set the focus to the first input element
		document.getElementById('AccountCombo').focus();
		
		// Initialise the width of all the input elements, so that they are uniform
		var intWidth = 250;
		document.getElementById("Payment.PaymentType").style.width = intWidth;
		document.getElementById("Payment.Amount").style.width = intWidth;
		document.getElementById("Payment.TXNReference").style.width = intWidth;
		document.getElementById("Payment.CreditCardNum").style.width = intWidth;
		
		// Initialise the Payment Type extra detail container
		this.DeclarePaymentType(intInitialPaymentType);
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
		for (intKey in this.arrExtraDetail)
		{
			if (intKey != intPaymentType)
			{
				// Hide the div
				document.getElementById(this.arrExtraDetail[intKey]['ExtraDetailDivId']).style.display = "none";
			}
			else
			{
				// Retrieve the container div element
				elmExtraDetailContainer = document.getElementById(this.arrExtraDetail[intKey]['ExtraDetailDivId']);
			}
		}
		
		// Show the extra details relating to the PaymentType
		if (elmExtraDetailContainer)
		{
			elmExtraDetailContainer.style.display = "inline";
		}
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
	this.DeclareCreditCardType = function(elmOption)
	{
		// Update the Surcharge message
		var strCreditCard = elmOption.innerHTML
		var strSurchargePercentage = elmOption.getAttribute("surcharge");
		var strSurchargeMsg = strCreditCard + " payments incur a " + strSurchargePercentage + "% surcharge.  This will be automatically added as an adjustment";
		
		document.getElementById("MakePayment_CreditCardSurchargeMsg").innerHTML = strSurchargeMsg;
	}
}

// Instanciate the object, if it doesn't already exist
if (Vixen.PaymentPopup == undefined)
{
	Vixen.PaymentPopup = new VixenPaymentPopupClass;
}
