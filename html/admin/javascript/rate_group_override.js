//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_override.js
//----------------------------------------------------------------------------//
/**
 * rate_group_override
 *
 * javascript required to facilitate the "Rate Group Override" popup
 *
 * javascript required to facilitate the "Rate Group Override" popup
 * 
 *
 * @file		rate_group_override.js
 * @language	PHP
 * @package		ui_app
 * @author		Ross 'Spudnik' Mullen
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRateGroupOverrideClass
//----------------------------------------------------------------------------//
/**
 * VixenRateGroupOverrideClass
 *
 * Facilitates the "Rate Group Override" popup
 *
 * Facilitates the "Rate Group Override" popup
 *
 * @package	ui_app
 * @class	VixenValidateAdjustmentClass
 * 
 */
function VixenRateGroupOverrideClass()
{
	// This will be multidimensional array storing all the RateGroup data (Id, Name, Description)
	// for each RateGroup associated with the ServiceType of the Service which a RateGroup override is being performed on
	this._arrRateGroups = {};
	
	this.Initialise = function(arrRateGroups)
	{
		//TODO!
	}
	

//----------------------------------------------------------------------------//
// The following is code from the validate_adjustment.js file.  This object
// will work similarly to it
//----------------------------------------------------------------------------//
 


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
}

// instantiate the object if it hasn't already been instantiated
if (Vixen.RateGroupOverride == undefined)
{
	Vixen.RateGroupOverride = new VixenRateGroupOverrideClass;
}
