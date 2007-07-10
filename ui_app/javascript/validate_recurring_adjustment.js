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
	var _elmRecursionCharge;
	var _elmMinCharge;
	var _elmTimesToCharge;
	var _elmRecurringChargeTypeId;
	
	var _fltRecursionCharge;
	var _fltMinCharge;
	var _intTimesToCharge;

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
	 *																		  .Description FIX THIS DESCRIPTION
	 * @return	void
	 * @method
	 */
	this.InitialiseForm = function(objChargeTypeData)
	{
alert("RecurringsAdjustments.InitialiseForm()");
		var intKey;
		this._objChargeTypeData = objChargeTypeData;
		
		// retrieve references to the elements
		this._elmRecursionCharge		= document.getElementById("RecurringCharge.RecursionCharge");
		this._elmMinCharge				= document.getElementById("RecurringCharge.MinCharge");
		this._elmTimesToCharge			= document.getElementById("TimesToCharge");
		this._elmRecurringChargeTypeId	= document.getElementById("RecurringChargeType.Id");

		//add event listeners
		//TODO! I don't know how to add event listeners to elements
		//this._elmRecursionCharge.onkeyup	= "Vixen.ValidateRecurringAdjustment.RecursionChargeChanged()";
		//this._elmRecursionCharge.addEventListener("keyup", "Vixen.ValidateRecurringAdjustment.RecursionChargeChanged", TRUE);
		
		// set up the form to display the details of the first item in the Charge Type Combobox
		for (intKey in this._objChargeTypeData)
		{
			var intFirstChargeTypeId = intKey;
			break;
		}
		this.DeclareChargeType(intFirstChargeTypeId);
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
	this.DeclareChargeType = function(intChargeTypeId)
	{
		// retrieve values relating to the Recurring Charge Type selected
		//var intChargeTypeId		= objComboBox.value;
		var strRecursionCharge	= this._objChargeTypeData[intChargeTypeId].RecursionCharge;
		var strMinCharge		= this._objChargeTypeData[intChargeTypeId].MinCharge;
		var strCancellationFee	= this._objChargeTypeData[intChargeTypeId].CancellationFee;
		var strDescription		= this._objChargeTypeData[intChargeTypeId].Description;
		var strChargeType		= this._objChargeTypeData[intChargeTypeId].ChargeType;
		var strRecurringFreq	= this._objChargeTypeData[intChargeTypeId].RecurringFreq +" "+ this._objChargeTypeData[intChargeTypeId].RecurringFreqTypeAsText;
		
		
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
		
		this._elmRecursionCharge.value = strRecursionCharge;
		this._elmMinCharge.value = strMinCharge;

		document.getElementById('RecurringChargeType.Id').value = intChargeTypeId;
		
		// Set TimesToCharge
		this.SetTimesToCharge();
		
		// Set EndDate
		this.SetEndDate();
		
		// If the charge type has a fixed amount then disable the textboxes, else enable them
		if (this._objChargeTypeData[intChargeTypeId].Fixed == 1)
		{
			// disable the textboxes
			this._elmRecursionCharge.disabled = TRUE;
			this._elmMinCharge.disabled = TRUE;
			this._elmTimesToCharge.disabled = TRUE;
		}
		else
		{
			// enable the textboxes
			this._elmRecursionCharge.disabled = FALSE;
			this._elmMinCharge.disabled = FALSE;
			this._elmTimesToCharge.disabled = FALSE;
		}
	}
	
	//Sets the TimesToCharge textbox, based on the RecursionCharge and the MinCharge
	this.SetTimesToCharge = function()
	{
		this.GetTextFields();

		if (this._fltRecursionCharge > this._fltMinCharge)
		{
			this._elmTimesToCharge.value = 1;
			return;
		}
		
		// Work out number of times charged
		this._intTimesCharged = Math.ceil(this._fltMinCharge / this._fltRecursionCharge);
		
		// Set the TimesToCharge textbox
		if (isNaN(this._intTimesCharged))
		{
			this._elmTimesToCharge.value = "";
		}
		else
		{
			this._elmTimesToCharge.value = this._intTimesCharged;
		}
	}
	
	this.SetEndDate = function()
	{
		var intTimesCharged = parseInt(this._elmTimesToCharge.value);
		var intRecurringFreq = this._objChargeTypeData[this._elmRecurringChargeTypeId.value].RecurringFreq;
		var intRecurringFreqType = this._objChargeTypeData[this._elmRecurringChargeTypeId.value].RecurringFreqType;

		if (isNaN(intTimesCharged))
		{
			document.getElementById("EndDate").innerHTML = "&nbsp;";
		}
		else
		{
			var strEndDate = this.CalculateEndDate(intRecurringFreq, intRecurringFreqType, intTimesCharged);
			document.getElementById("EndDate").innerHTML = strEndDate;
		}
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


	//Event handler for when the text within the Times to Charge text box, is changed
	this.TimesChargedChanged = function()
	{
		this.GetTextFields();
	
		if ((isNaN(this._intTimesToCharge)) || (this._intTimesToCharge <= 0))
		{
			return;
		}
		
		this._fltRecursionCharge = this._fltMinCharge / this._intTimesToCharge;

		this.SetTextFields();
		
		this.SetEndDate();
	}
	
	//Event handler for when the text within the Recursion charge text box, is changed
	this.RecursionChargeChanged = function()
	{
		alert("RecursionChargeChanged() has been executed");
		return;
	
		//TODO! Fix this method.  This has just been copied from the original system
		var eAmount = document.getElementById("Amount");
		var eMinCharge = document.getElementById("MinCharge");
		var eNumOfCharges = document.getElementById("NumOfCharges");
		var eEndDate = document.getElementById("EndDate");
		var eRecurFreq = document.getElementById("recurringfrequency");
		
		var RecurFreq = eRecurFreq.innerHTML.split(" ")[0];
		var RecurFreqType = eRecurFreq.innerHTML.split(" ")[1];
		
		// check if it is fixed or not
		var fixed = document.getElementById('NumOfChargesFixed');
		if (fixed)
		{
			stripDollars();
			fixed.innerHTML = Math.ceil(eMinCharge.value / eAmount.value);
			endDate = calculateEndDate(RecurFreq, RecurFreqType, fixed.innerHTML);
			eEndDate.innerHTML = endDate;
			
			addDollars;
			
			return;
		}
		
		stripDollars();
		
		// Work out number of times charged
		eNumOfCharges.value = Math.ceil(eMinCharge.value / eAmount.value);
		
		endDate = calculateEndDate(RecurFreq, RecurFreqType, eNumOfCharges.value);
		eEndDate.innerHTML = endDate;
		
		addDollars();
	}
	

	this.GetTextFields = function()
	{
		this._fltRecursionCharge = parseFloat(this.StripDollars(this._elmRecursionCharge.value));
		this._fltMinCharge = parseFloat(this.StripDollars(this._elmMinCharge.value));
		this._intTimesToCharge = parseInt(this._elmTimesToCharge.value);
	}

	this.SetTextFields = function()
	{
		this._elmRecursionCharge.value	= "$" + (this._fltRecursionCharge).toFixed(4);
		this._elmMinCharge.value		= "$" + (this._fltMinCharge).toFixed(2);
		this._elmTimesToCharge.value	= this._intTimesToCharge;
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
