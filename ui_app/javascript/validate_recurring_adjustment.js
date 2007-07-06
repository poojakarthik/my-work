//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// validate_recurring_adjustment.js
//----------------------------------------------------------------------------//
/**
 * validate_recurring_adjustment
 *
 * javascript required to validate a new recurring adjustment
 *
 * javascript required to validate a new recurring adjustment
 * This class is currently used by the "Add Recurring Adjustment" popup
 * 
 *
 * @file		validate_recurring_adjustment.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenValidateRecurringAdjustmentClass
//----------------------------------------------------------------------------//
/**
 * VixenValidateRecurringAdjustmentClass
 *
 * Encapsulates all validation and inserting/updating of recurring adjustments
 *
 * Encapsulates all validation and inserting/updating of recurring adjustments
 * 
 *
 * @package	ui_app
 * @class	VixenValidateRecurringAdjustmentClass
 * 
 */
function VixenValidateRecurringAdjustmentClass()
{
	var elmRecursionCharge;
	var elmMinCharge;
	var elmTimesToCharge;
	var elmRecurringChargeTypeId;

	//------------------------------------------------------------------------//
	// _objChargeTypeData
	//------------------------------------------------------------------------//
	/**
	 * _objChargeTypeData
	 *
	 * Stores data relating to each unarchived Recurring Charge Type from the database table "RecurringChargeType"
	 *
	 * Stores data relating to each unarchived Recurring Charge Type from the database table "RecurringChargeType"
	 * 
	 * @type		object
	 *
	 * @property
	 */
	this._objChargeTypeData = {};

	//------------------------------------------------------------------------//
	// InitialiseForm
	//------------------------------------------------------------------------//
	/**
	 * InitialiseForm
	 *
	 * Sets the member variable storing data relating to all unarchived Charge Types, and initialises the properties on the form
	 *
	 * Sets the member variable storing data relating to all unarchived Charge Types, and initialises the properties on the form
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
	this.InitialiseForm = function(objChargeTypeData)
	{
		this._objChargeTypeData = objChargeTypeData;
		
		// attach events to the input textboxes
		//TODO!
		this.elmRecursionCharge	= document.getElementById("RecurringCharge.RecursionCharge");
		this.elmMinCharge		= document.getElementById("RecurringCharge.MinCharge");
		this.elmTimesToCharge	= document.getElementById("TimesToCharge");
		this.elmRecurringChargeTypeId = document.getElementById("RecurringChargeType.Id");
		
		//var elmTemp	= document.getElementById("ChargeTypeCombo");
		
		//this.DisplayChargeType(elmTemp.value);
		
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
	 *
	 * @param	obj		objComboBox		The HTML element that calls this method (the Charge Type combobox)
	 *
	 * @return	void
	 * @method
	 */
	this.DeclareChargeType = function(objComboBox)
	{
		// make sure there is a value specificed
		if (!objComboBox.value)
		{
			return;
		}

		// retrieve values relating to the Recurring Charge Type selected
		var intChargeTypeId		= objComboBox.value;
		var strRecursionCharge	= this._objChargeTypeData[intChargeTypeId].RecursionCharge;
		var strMinCharge		= this._objChargeTypeData[intChargeTypeId].MinCharge;
		var strCancellationFee	= this._objChargeTypeData[intChargeTypeId].CancellationFee;
		var strDescription		= this._objChargeTypeData[intChargeTypeId].Description;
		var strChargeType		= this._objChargeTypeData[intChargeTypeId].ChargeType;
		var strRecurringFreq	= this._objChargeTypeData[intChargeTypeId].RecurringFreq +" "+ this._objChargeTypeData[intChargeTypeId].RecurringFreqType;
		
		
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
		document.getElementById('RecurringChargeType.ChargeType.Output').innerHTML = strChargeType;
		document.getElementById('RecurringChargeType.Description.Output').innerHTML = strDescription;
		document.getElementById('RecurringChargeType.Nature.Output').innerHTML = strNature;
		document.getElementById('RecurringChargeType.CancellationFee.Output').innerHTML = strCancellationFee;
		document.getElementById('RecurringChargeType.RecurringFreq.Output').innerHTML = strRecurringFreq;
		
		
		document.getElementById('RecurringCharge.RecursionCharge').value = strRecursionCharge;
		document.getElementById('RecurringCharge.MinCharge').value = strMinCharge;
		
		document.getElementById('RecurringChargeType.Id').value = intChargeTypeId;
		
		// Set TimesToCharge
		this.SetTimesToCharge();
		
		// Set EndDate
		this.SetEndDate();
		
		// If the charge type has a fixed amount then disable the textboxes, else enable them
		if (this._objChargeTypeData[intChargeTypeId].Fixed == 1)
		{
			// disable the textboxes
			document.getElementById('RecurringCharge.RecursionCharge').disabled = TRUE;
			document.getElementById('RecurringCharge.MinCharge').disabled = TRUE;
			document.getElementById('TimesToCharge').disabled = TRUE;
			
			//document.getElementById('InvoiceComboBox').focus();
		}
		else
		{
			// enable the textboxes
			document.getElementById('RecurringCharge.RecursionCharge').disabled = FALSE;
			document.getElementById('RecurringCharge.MinCharge').disabled = FALSE;
			document.getElementById('TimesToCharge').disabled = FALSE;
			
			//document.getElementById('Charge.Amount').focus();
		}
	}
	
	//Sets the TimesToCharge textbox, based on the RecursionCharge and the MinCharge
	this.SetTimesToCharge = function()
	{
		var fltRecursionCharge	= this.StripDollars(this.elmRecursionCharge.value);
		var fltMinCharge		= this.StripDollars(this.elmMinCharge.value);
		var intTimesCharged;
		
		if (fltRecursionCharge > fltMinCharge)
		{
			this.elmTimesToCharge.value = 0;
			return;
		}
		
		// Work out number of times charged
		intTimesCharged = Math.ceil(fltMinCharge / fltRecursionCharge);
		
		// Set the TimesToCharge textbox
		this.elmTimesToCharge.value = intTimesCharged;
	}
	
	this.SetEndDate = function()
	{
		var intTimesCharged = this.elmTimesToCharge.value;
		var intRecurringFreq = this._objChargeTypeData[this.elmRecurringChargeTypeId.value].RecurringFreq;
		var intRecurringFreqType = this._objChargeTypeData[this.elmRecurringChargeTypeId.value].RecurringFreqType;
		
		var strEndDate = this.CalculateEndDate(intRecurringFreq, intRecurringFreqType, intTimesCharged);

		document.getElementById("EndDate").innerHTML = strEndDate;
	}
	
	
	
	//strips the dollar sign off the parameter
	this.StripDollars = function(mixMoneyValue)
	{
		if (mixMoneyValue[0] == "$")
		{
			//return mixMoneyValue minus its dollar sign
			return mixMoneyValue.substr(1);
		}
		return mixMoneyValue;
	}

	this.CalculateEndDate = function(recurringfrequency, recurringfrequencytype, timescharged)
	{
		//HACK HACK HACK!!!
		//These constants are defined in vixen/framework/definitions.php but are not defined anywhere in the Vixen javascript object
		var BILLING_FREQ_DAY		= 1;
		var BILLING_FREQ_MONTH		= 2;
		var BILLING_FREQ_HALF_MONTH	= 3;
		
		// Take number of days/months/half-months, and number of times to charge
		// and return the last charge date
		var monthname= new Array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG", "SEP","OCT","NOV","DEC");
		var now = new Date();
		var future = new Date();
		if (recurringfrequencytype == BILLING_FREQ_DAY)
		{
			var daysinfuture = timescharged * recurringfrequency;
			
			//Add days and format
			future.setDate(now.getDate()+daysinfuture);
			var endDate = future.getDate() + " " + monthname[future.getMonth()] + ", " + future.getFullYear();
			return endDate;
		}
		else if (recurringfrequencytype == BILLING_FREQ_MONTH)
		{
			var monthsinfuture = timescharged * recurringfrequency;
			//Change the day number so that we won't have issues with being charged on say the 31st of Feb
			if (now.getDate() > 28)
			{
				future.setDate(28);
			}
			//Add months and format
			future.setMonth(now.getMonth()+monthsinfuture);
			var endDate = future.getDate() + " " + monthname[future.getMonth()] + ", " + future.getFullYear();
			return endDate;
		}
		else if (recurringfrequencytype == BILLING_FREQ_HALF_MONTH)
		{
			// half months... umm, lets just pretend half a month if 14 days
			var halfmonthsinfuture = timescharged * recurringfrequency;
			
			//Change the day number to get around silly dates
			if (now.getDate() > 28)
			{
				future.setDate(28);
			}
			
			//If it's an odd number of half months, then add the corresponding 
			//number of whole months, and 14 days
			if ((halfmonthsinfuture % 2) == 1)
			{
				halfmonthsinfuture--;
				future.setMonth(now.getMonth()+(halfmonthsinfuture / 2));
				future.setDate(now.getDate()+14);
			}
			
			//If not, just add corresponding number of months
			else
			{
				future.setMonth(now.getMonth()+(halfmonthsinfuture / 2));
			}			
			var endDate = future.getDate() + " " + monthname[future.getMonth()] + ", " + future.getFullYear();
			return endDate;
		}
	}


}

// instanciate the objects
Vixen.ValidateRecurringAdjustment = new VixenValidateRecurringAdjustmentClass;

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
