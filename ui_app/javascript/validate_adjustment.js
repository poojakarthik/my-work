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
 * javascript required to validate and insert/update a newly added adjustment, or modified adjustment
 *
 * javascript required to validate and insert/update a newly added adjustment, or modified adjustment
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
	// _objAdjustmentData
	//------------------------------------------------------------------------//
	/**
	 * _objAdjustmentData
	 *
	 * Stores data relating to the adjustment which is either being added or modified
	 *
	 * Stores data relating to the adjustment which is either being added or modified
	 * 
	 * @type		object
	 *
	 * @property
	 */
	this._objAdjustmentData = {};
	
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
	// SetAdjustmentData
	//------------------------------------------------------------------------//
	/**
	 * SetAdjustmentData
	 *
	 * Sets the member variable storing data relating to the current adjustment
	 *
	 * Sets the member variable storing data relating to the current adjustment
	 *
	 * @param	obj		objAdjustmentData		object storing adjustment data
	 *											structure:
	 *											objAdjustmentData.AccountGroup
	 *															 .Account
	 *															 .Service
	 *															 .InvoiceRun
	 *															 .CreatedBy
	 *															 .CreatedOn
	 *															 .ApprovedBy
	 * @return	void
	 * @method
	 */
	this.SetAdjustmentData = function(objAdjustmentData)
	{
		this._objAdjustmentData = objAdjustmentData;

		// set default values
		this._objAdjustmentData.ChargeType = null;
		this._objAdjustmentData.Description = null;
		this._objAdjustmentData.Nature = null;
		this._objAdjustmentData.Amount = null;
	}
	
	/*
	this.Test = function()
	{
		alert("This is just a test\nto make sure it's\nloading the right\njavascript file");
	}
	*/
	
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
			document.getElementById('Charge.ChargeType').innerHTML = "&nbsp;";
		}
		else
		{
			document.getElementById('Charge.ChargeType').innerHTML = strChargeType;
		}
		
		document.getElementById('ChargeType.Description').innerHTML = strDescription;
		document.getElementById('Charge.Nature').innerHTML = strNature;
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

		// set the charge type details for this._objAdjustmentData
		if (this._objChargeTypeData[strChargeType].NoSelection)
		{
			this._objAdjustmentData.ChargeType = null;
			this._objAdjustmentData.Description = null;
			this._objAdjustmentData.Nature = null;
		}
		else
		{
			this._objAdjustmentData.ChargeType = strChargeType;
			this._objAdjustmentData.Description = strDescription;
			this._objAdjustmentData.Nature = this._objChargeTypeData[strChargeType].Nature;
		}
	}

	//------------------------------------------------------------------------//
	// IsValidForm
	//------------------------------------------------------------------------//
	/**
	 * IsValidForm
	 *
	 * Checks if the form data is valid 
	 *  
	 * Checks if the form data is valid
	 * If it is invalid then this._strErrorMsg is set appropriately and the function returns false
	 *
	 * @return	bool
	 * @method
	 */
	this.IsValidForm = function()
	{
		var strAmount;
		var fltAmount;
		
		// check that a charge type has been declared
		if (this._objAdjustmentData.ChargeType == null)
		{
			this._strErrorMsg = "Charge Type must be specified";
			document.getElementById("ChargeType.ChargeType").focus();
			return false;
		}
		
		// check that the adjustment amount is valid
		strAmount = document.getElementById("Charge.Amount").value;
		if (!strAmount)
		{
			// No amount has been specified
			this._errorMsg = "An amount must be defined";
			document.getElementById("Charge.Amount").focus();
			return false;
		}
		if (strAmount[0] == '$')
		{
			strAmount = String(strAmount).substr(1,50);
		}
		
		fltAmount = parseFloat(strAmount);
		
		
		
		return true;
	}
	

	//------------------------------------------------------------------------//
	// AddAdjustment
	//------------------------------------------------------------------------//
	/**
	 * AddAdjustment
	 *
	 * Adds the new adjustment if the form data is valid
	 *  
	 * Adds the new adjustment if the form data is valid
	 * 
	 *
	 * @return	void
	 * @method
	 */
	this.AddAdjustment = function()
	{
		// Validate the data in the form
		/*
		if (!this.IsValidForm())
		{
			// The data in the form is currenly invalid
			// Output some sort of error message within the form, to that effect
			document.getElementById('StatusMsg').innerHTML = this._strErrorMsg;
			document.getElementById('StatusMsg').class = "DefaultElement";  //this line currently isn't working
			
			alert(this._strErrorMsg);
			return;
		}
		*/
		
		// retrieve all data required to add the record (Validation is not required at this stage)
		// we need to grab Amount, Invoice and Notes
		this._objAdjustmentData.Amount = document.getElementById('Charge.Amount').value;
		this._objAdjustmentData.Invoice = document.getElementById('InvoiceComboBox').value;
		if (this._objAdjustmentData.Invoice == 0)
		{
			this._objAdjustmentData.Invoice = null;
		}

		this._objAdjustmentData.Notes = document.getElementById('Charge.Notes').value;
		
		//TODO! Joel
		//make a call to AJAXLoad to run AppTemplateAdjustment.InsertAdjustment
		//which assigns a value for each property of DBO()->Adjustment
		//and then validate it and save it
		
		var objSendData			= {};
		objSendData.HtmlMode	= false;
		objSendData.Application	= "Adjustment.InsertAdjustment";
		
		Vixen.Ajax.Send(this._objAdjustmentData);
		
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
