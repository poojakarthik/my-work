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

		// make sure there is a value specificed
		if (!objComboBox.value)
		{
			return;
		}
		
		// retrieve the value of the combo box
		strSelection = objComboBox.value;
		if (strSelection.substr(0, 13) == "AccountGroup:")
		{
			// the payment is being applied to an account group
			document.getElementById("AccountToApplyTo.Id").value = strSelection.substr(13);
			document.getElementById("AccountToApplyTo.IsGroup").value = 1;
		}
		else
		{
			// the payment is being applied to an individual account
			document.getElementById("AccountToApplyTo.Id").value = strSelection;
			document.getElementById("AccountToApplyTo.IsGroup").value = 0;
		}
		
		document.getElementById("Payment.PaymentType").focus();
		return;
	}

}

// Instanciate the object, if it doesn't already exist
if (Vixen.PaymentPopup == undefined)
{
	Vixen.PaymentPopup = new VixenPaymentPopupClass;
}
