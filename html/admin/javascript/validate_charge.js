//----------------------------------------------------------------------------//
// validate_charge.js
//----------------------------------------------------------------------------//
/**
 * validate_charge
 *
 * javascript required to validate a new charge
 *
 * javascript required to validate a new charge
 * This class is currently used by the "Add Charge" popup
 * 
 *
 * @file		validate_charge.js
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenValidateChargeClass
//----------------------------------------------------------------------------//
/**
 * VixenValidateChargeClass
 *
 * Encapsulates all validation and inserting/updating of charges
 *
 * Encapsulates all validation and inserting/updating of charges
 * 
 *
 * @package	ui_app
 * @class	VixenValidateChargeClass
 * 
 */
function VixenValidateChargeClass()
{
	//------------------------------------------------------------------------//
	// _objChargeTypeData
	//------------------------------------------------------------------------//
	/**
	 * _objChargeTypeData
	 *
	 * Stores data relating to each unarchived Charge Type from the database table "ChargeType"
	 *
	 * Stores data relating to each unarchived Charge Type from the database table "ChargeType"
	 * 
	 * @type		object
	 *
	 * @property
	 */
	this._objChargeTypeData = {};
	
	// Holds a string representation of an optional override amount for the charge that is to be requested
	this._sOverrideAmount	= null;
	
	//------------------------------------------------------------------------//
	// SetChargeTypes
	//------------------------------------------------------------------------//
	/**
	 * SetChargeTypes
	 *
	 * Sets the member variable storing data relating to all unarchived Charge Types
	 *
	 * Sets the member variable storing data relating to all unarchived Charge Types
	 *
	 * @param	obj		objChargeTypeData		object storing all Charge Type data
	 *											structure:
	 *											objChargeTypeData.{ChargeType}.Nature
	 *																		  .Fixed
	 *																		  .Amount
	 *																		  .Description
	 * @return	void
	 * @method
	 */
	this.SetChargeTypes = function(objChargeTypeData)
	{
		this._objChargeTypeData = objChargeTypeData;
	}
	
	// SetOverrideAmount: Sets the string charge amount override value
	this.SetOverrideAmount	= function(sOverrideAmount)
	{
		this._sOverrideAmount	= sOverrideAmount;
	}
	
	//------------------------------------------------------------------------//
	// DeclareChargeType
	//------------------------------------------------------------------------//
	/**
	 * DeclareChargeType
	 *
	 * Sets various related controls when a Charge Type has been chosen from the combobox
	 *  
	 * Sets various related controls when a Charge Type has been chosen from the combobox
	 * It sets the ChargeType label, Description label, Nature label, and the Amount textbox.
	 * If the charge type has a fixed value, then the Amount textbox id disabled
	 *
	 * @param	obj		objComboBox		The HTML element that calls this method (the Charge Type combobox)
	 *
	 * @return	void
	 * @method
	 */
	this.DeclareChargeType = function(objComboBox)
	{
		var strChargeType;
		var strDefaultAmount;
		var strDescription;
		var strNature;
		var strFixed;
		var intChargeTypeId
		
		// make sure there is a value specificed
		if (!objComboBox.value)
		{
			return;
		}
		
		// retrieve values relating to the Charge Type selected
		intChargeTypeId		= objComboBox.value;
		strDefaultAmount	= this._objChargeTypeData[intChargeTypeId].Amount;
		strDescription		= this._objChargeTypeData[intChargeTypeId].Description;
		strChargeType		= this._objChargeTypeData[intChargeTypeId].ChargeType;
		
		if (this._objChargeTypeData[intChargeTypeId].Nature == "CR")
		{
			strNature = "Credit";
		}
		else if (this._objChargeTypeData[intChargeTypeId].Nature == "DR")
		{
			strNature = "Debit";
		}
		else
		{
			strNature = this._objChargeTypeData[intChargeTypeId].Nature;
		}
		
		// setup values on the form
		document.getElementById('ChargeType.ChargeType.Output').innerHTML = strChargeType;
		document.getElementById('ChargeType.Description.Output').innerHTML = strDescription;
		document.getElementById('ChargeType.Nature.Output').innerHTML = strNature;
		var elmChargeAmount 		= document.getElementById('Charge.Amount');
		if ((this._objChargeTypeData[intChargeTypeId].Fixed == 0) && (this._sOverrideAmount !== null))
		{
			// A charge override has been specified and the charge type is not fixed, 
			// use the override amount
			elmChargeAmount.value = this._sOverrideAmount;
		}
		else
		{
			// Use the default amount of the charge type
			elmChargeAmount.value = strDefaultAmount;
		}
		elmChargeAmount.style.backgroundColor = "#FFFFFF";
		document.getElementById('ChargeType.Id').value = intChargeTypeId;
		
		// If the charge type has a fixed amount then disable the amount textbox, else enable it
		if (this._objChargeTypeData[intChargeTypeId].Fixed == 1)
		{
			// disable the charge amount textbox
			elmChargeAmount.disabled = TRUE;
			document.getElementById('InvoiceComboBox').focus();
		}
		else
		{
			// enable the charge amount textbox
			elmChargeAmount.disabled = FALSE;
			elmChargeAmount.focus();
		}
	}
	
	this.SubmitRequest = function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			// Check if the charge is a debit or credit
			var intChargeTypeId = $ID('Charge.charge_type_id').value;
			var strMsg = "";
			if (this._objChargeTypeData[intChargeTypeId].Nature == "CR")
			{
				// Credit adjustmemt
				strMsg = "<strong>Please Note:</strong>" +
						"<ol>" +
						"   <li>You are requesting a Credit Charge for approval</li>" +
						"   <li>Ensure you have <strong>NOT</strong> notified the customer that this credit is approved</li>" +
						"   <li>The credit request can take up to 28 days to be assessed</li>" +
						"</ol>" +
						"Are you sure you want to submit this request?";
			}
			else if (this._objChargeTypeData[intChargeTypeId].Nature == "DR")
			{
				// Debit charge
				strMsg = "You are requesting a Debit Charge." +
						"<br /><br />Are you sure you want to submit this request?";
			}
			else
			{
				// This case should never occur
				alert("ERROR: Unknown Charge Type Nature: '" + this._objChargeTypeData[intChargeTypeId].Nature + "'");
				return;
			}
		
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ValidateCharge.SubmitRequest(true)}, null, null, "Yes", "No", "Request Charge");
			return;
		}
		
		var elmRealSubmitButton = $ID("AddChargeSubmitButton");
		elmRealSubmitButton.click();
	}
}

// instantiate the object if it hasn't already been instantiated
if (Vixen.ValidateCharge == undefined)
{
	Vixen.ValidateCharge = new VixenValidateChargeClass;
}