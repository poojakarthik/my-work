//----------------------------------------------------------------------------//
// validate_adjustment.js
//----------------------------------------------------------------------------//
/**
 * validate_adjustment
 *
 * javascript required to validate a new adjustment
 *
 * javascript required to validate a new adjustment
 * This class is currently used by the "Add Adjustment" popup
 * 
 *
 * @file		validate_adjustment.js
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenValidateAdjustmentClass
//----------------------------------------------------------------------------//
/**
 * VixenValidateAdjustmentClass
 *
 * Encapsulates all validation and inserting/updating of adjustments
 *
 * Encapsulates all validation and inserting/updating of adjustments
 * 
 *
 * @package	ui_app
 * @class	VixenValidateAdjustmentClass
 * 
 */
function VixenValidateAdjustmentClass()
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
		var elmChargeAmount = document.getElementById('Charge.Amount');
		elmChargeAmount.value = strDefaultAmount;
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
			// Check if the adjustment is a debit or credit
			var intChargeTypeId = $ID('Charge.charge_type_id').value;
			var strMsg = "";
			if (this._objChargeTypeData[intChargeTypeId].Nature == "CR")
			{
				// Credit adjustmemt
				strMsg = "<strong>Please Note:</strong>" +
						"<ol>" +
						"   <li>You are requesting a credit adjustment for approval</li>" +
						"   <li>Ensure you have not notified the customer that this credit is approved</li>" +
						"   <li>Deliberation of a credit request can take up to 28 days</li>" +
						"</ol>" +
						"Are you sure you want to submit this request?";
			}
			else if (this._objChargeTypeData[intChargeTypeId].Nature == "DR")
			{
				// Debit adjustment
				strMsg = "Are you requesting a Debit Adjustment.  " +
						"While requests for Debit Adjustments are usually approved, the deliberation process can still take up to 28 days." +
						"<br /><br />Are you sure you want to submit this request?";
			}
			else
			{
				// This case should never occur
				alert("ERROR: Unknown Charge Type Nature: '" + this._objChargeTypeData[intChargeTypeId].Nature + "'");
				return;
			}
		
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ValidateAdjustment.SubmitRequest(true)}, null, null, "Yes", "No", "Request Adjustment");
			return;
		}
		
		var elmRealSubmitButton = $ID("AddAdjustmentSubmitButton");
		elmRealSubmitButton.click();
	}
}

// instantiate the object if it hasn't already been instantiated
if (Vixen.ValidateAdjustment == undefined)
{
	Vixen.ValidateAdjustment = new VixenValidateAdjustmentClass;
}