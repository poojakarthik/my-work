//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

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
 * @file		validate_adjustment.php
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
	// _strErrorMsg
	//------------------------------------------------------------------------//
	/**
	 * _strErrorMsg
	 *
	 * Stores a specific error message describing why validation failed
	 *
	 * Stores a specific error message describing why validation failed
	 * 
	 * @type		string
	 *
	 * @property
	 */
	this._strErrorMsg = "";
	
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

		// add details regarding what should be displayed when the blank option of the ChargeType combobox is selected
		this._objChargeTypeData.NoSelection = {};
		this._objChargeTypeData.NoSelection.Nature = "&nbsp;";
		this._objChargeTypeData.NoSelection.Fixed = "&nbsp;";
		this._objChargeTypeData.NoSelection.Amount = "";
		this._objChargeTypeData.NoSelection.Id = "";	
		this._objChargeTypeData.NoSelection.Description = "&nbsp;";
		this._objChargeTypeData.NoSelection.NoSelection = true;
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
		
		// make sure there is a value specificed
		if (!objComboBox.value)
		{
			return;
		}

		strChargeType = objComboBox.value;
		strDefaultAmount = this._objChargeTypeData[strChargeType].Amount;
		strDescription = this._objChargeTypeData[strChargeType].Description;
		
		if (this._objChargeTypeData[strChargeType].Nature == "CR")
		{
			strNature = "Credit";
		}
		else if (this._objChargeTypeData[strChargeType].Nature == "DR")
		{
			strNature = "Debit";
		}
		else
		{
			strNature = this._objChargeTypeData[strChargeType].Nature;
		}
		
		// setup values on the form
		if (this._objChargeTypeData[strChargeType].NoSelection)
		{
			document.getElementById('ChargeType.ChargeType').innerHTML = "&nbsp;";
		}
		else
		{
			document.getElementById('ChargeType.ChargeType').innerHTML = strChargeType;
		}
		
		document.getElementById('ChargeType.Description').innerHTML = strDescription;
		document.getElementById('ChargeType.Nature').innerHTML = strNature;
		document.getElementById('Charge.Amount').value = strDefaultAmount;
		document.getElementById('ChargeType.Id').value = this._objChargeTypeData[strChargeType].Id;
		
		// If the charge type has a fixed amount then disable the amount textbox, else enable it
		if (this._objChargeTypeData[strChargeType].Fixed == 1)
		{
			// disable the charge amount textbox
			document.getElementById('Charge.Amount').disabled = true;
			document.getElementById('InvoiceComboBox').focus();
		}
		else if (this._objChargeTypeData[strChargeType].Fixed == 0)
		{	
			// enable the charge amount textbox
			document.getElementById('Charge.Amount').disabled = false;
			document.getElementById('Charge.Amount').focus();
		}
		else
		{
			// for when "NoSelection" has been selected as the ChargeType
			document.getElementById('Charge.Amount').disabled = false;
		}

	}

}

// instanciate the object
Vixen.ValidateAdjustment = new VixenValidateAdjustmentClass;

/*
window.addEventListener (
	'load',
	function ()
	{
		if (document.getElementById ('ChargeType.ChargeType'))
		{
			ValidateAdjustment.DeclareChargeType(document.getElementById ('ChargeType.ChargeType'));
		}
	},
	true
);
*/
