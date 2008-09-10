//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// recurring_adjustment_add.js
//----------------------------------------------------------------------------//
/**
 * recurring_adjustment_add
 *
 * javascript required to validate a new recurring adjustment
 *
 * javascript required to validate a new recurring adjustment
 * This class is currently used by the "Add Recurring Adjustment" popup
 * 
 *
 * @file		recurring_adjustment_add.php
 * @language	PHP
 * @package		ui_app
 * @author		Joel Dawkins
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRecurringAdjustmentAddClass
//----------------------------------------------------------------------------//
/**
 * VixenRecurringAdjustmentAddClass
 *
 * Encapsulates all validation and inserting/updating of recurring adjustments
 *
 * Encapsulates all validation and inserting/updating of recurring adjustments
 * 
 *
 * @package	ui_app
 * @class	VixenRecurringAdjustmentAddClass
 * 
 */
function VixenRecurringAdjustmentAddClass()
{
	this.elmRecursionCharge			= null;
	this.elmMinCharge				= null;
	this.elmTimesToCharge			= null;
	this.elmRecurringChargeTypeId	= null;
	
	this.fltRecursionCharge			= null;
	this.fltMinCharge				= null;
	this.intTimesToCharge			= null;
	
	this.fltCurrentRecursionCharge	= null;
	this.fltCurrentMinCharge		= null;
	this.intCurrentTimesToCharge	= null;
	
	this.elmStartDateSnapCombo		= null;
	this.objDetailNodes				= {};
	
	this.today = new Date();

	//------------------------------------------------------------------------//
	// objChargeTypeData
	//------------------------------------------------------------------------//
	/**
	 * objChargeTypeData
	 *
	 * Stores data relating to each unarchived Recurring Charge Type from the database table "RecurringChargeType"
	 *
	 * Stores data relating to each unarchived Recurring Charge Type from the database table "RecurringChargeType"
	 * 
	 * @type		object
	 *
	 * @property
	 */
	this.objChargeTypeData = {};

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
	 * @param	int		intCurrentChargeTypeId	id of the charge type that should have its details listed in the popup
	 * @return	void
	 * @method
	 */
	this.InitialiseForm = function(objChargeTypeData, intCurrentChargeTypeId)
	{
		var intKey;
		this.objChargeTypeData = objChargeTypeData;
		
		// retrieve references to the elements
		this.elmRecursionCharge			= $ID("RecurringCharge.RecursionCharge");
		this.elmMinCharge				= $ID("RecurringCharge.MinCharge");
		this.elmTimesToCharge			= $ID("TimesToCharge");
		this.elmRecurringChargeTypeId	= $ID("RecurringChargeType.Id");

		this.objDetailNodes.ChargeType		= $ID("RecurringChargeType.ChargeType.Output");
		this.objDetailNodes.Description		= $ID("RecurringChargeType.Description.Output");
		this.objDetailNodes.Nature			= $ID("RecurringChargeType.Nature.Output");
		this.objDetailNodes.CancellationFee	= $ID("RecurringChargeType.CancellationFee.Output");
		this.objDetailNodes.RecurringFreq	= $ID("RecurringChargeType.RecurringFreq.Output");
		this.objDetailNodes.EndDate			= $ID("EndDate");
		this.objDetailNodes.ChargeTypeId	= $ID("RecurringChargeType.Id");
		this.objDetailNodes.Continuable		= $ID("RecurringChargeType.Continuable.Output");

		this.elmStartDateSnapCombo			= $ID("RecurringCharge.SnapToDayOfMonth");
		if (this.elmStartDateSnapCombo != null)
		{
			// The StartDateSnap control is present on the popup
			Event.startObserving(this.elmStartDateSnapCombo, "change", this.StartDateSnapComboOnChange.bind(this), true);
		}

		//add event listeners
		this.elmRecursionCharge.onkeyup		= function(){Vixen.RecurringAdjustmentAdd.RecursionChargeChanged()};
		this.elmRecursionCharge.onblur		= function(){Vixen.RecurringAdjustmentAdd.RecursionChargeLostFocus()};
		this.elmMinCharge.onkeyup			= function(){Vixen.RecurringAdjustmentAdd.MinChargeChanged()};
		this.elmMinCharge.onblur			= function(){Vixen.RecurringAdjustmentAdd.MinChargeLostFocus()};

		this.DeclareChargeType(intCurrentChargeTypeId);

		$ID("ChargeTypeCombo").focus();
	}
	
	// OnChange handler for the DateSnapCombo
	this.StartDateSnapComboOnChange = function(objEvent)
	{
		// Update the end Date label
		this.SetEndDate();
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
		var strRecursionCharge	= this.objChargeTypeData[intChargeTypeId].RecursionCharge;
		var strMinCharge		= this.objChargeTypeData[intChargeTypeId].MinCharge;
		var strCancellationFee	= this.objChargeTypeData[intChargeTypeId].CancellationFee;
		var strDescription		= this.objChargeTypeData[intChargeTypeId].Description;
		var strChargeType		= this.objChargeTypeData[intChargeTypeId].ChargeType;
		var strRecurringFreq	= this.objChargeTypeData[intChargeTypeId].RecurringFreq +" "+ this.objChargeTypeData[intChargeTypeId].RecurringFreqTypeAsText;
		var strContinuable		= (this.objChargeTypeData[intChargeTypeId].Continuable == true)? "Yes" : "No";
		
		if (this.objChargeTypeData[intChargeTypeId].Nature == "CR")
		{
			strNature = "Credit";
		}
		else if (this.objChargeTypeData[intChargeTypeId].Nature == "DR")
		{
			strNature = "Debit";
		}
		else
		{
			strNature = this.objChargeTypeData[intChargeTypeId].Nature;
		}
		
		// setup values on the form
		this.objDetailNodes.ChargeType.innerHTML		= strChargeType;
		this.objDetailNodes.Description.innerHTML		= strDescription;
		this.objDetailNodes.Nature.innerHTML			= strNature;
		this.objDetailNodes.CancellationFee.innerHTML	= strCancellationFee;
		this.objDetailNodes.RecurringFreq.innerHTML		= strRecurringFreq;
		this.objDetailNodes.Continuable.innerHTML		= strContinuable;
		
		this.elmRecursionCharge.value	= strRecursionCharge;
		this.fltCurrentRecursionCharge	= parseFloat(this.StripDollars(strRecursionCharge));
		this.elmMinCharge.value			= strMinCharge;
		this.fltCurrentMinCharge		= parseFloat(this.StripDollars(strMinCharge));
		
		this.objDetailNodes.ChargeTypeId.value = intChargeTypeId;
		
		// Set TimesToCharge
		this.SetTimesToCharge();
		
		// Set EndDate
		this.SetEndDate();
		
		// If the charge type has a fixed amount then disable the textboxes, else enable them
		if (this.objChargeTypeData[intChargeTypeId].Fixed == 1)
		{
			// disable the textboxes
			this.elmRecursionCharge.disabled	= true;
			this.elmMinCharge.disabled			= true;
			this.elmTimesToCharge.disabled		= true;
		}
		else
		{
			// enable the textboxes
			this.elmRecursionCharge.disabled	= false;
			this.elmMinCharge.disabled			= false;
			this.elmTimesToCharge.disabled		= false;
		}
	}
	
	//Sets the TimesToCharge textbox, based on the RecursionCharge and the MinCharge
	this.SetTimesToCharge = function()
	{
		this.GetTextFields();
		
		if (this.fltRecursionCharge > this.fltMinCharge)
		{
			this.elmTimesToCharge.value = 1;
			this.intCurrentTimesToCharge = 1;
			return;
		}
		
		// Work out number of times charged
		this.intTimesToCharge = Math.ceil(this.fltMinCharge / this.fltRecursionCharge);
		
		// Set the TimesToCharge textbox
		if (isNaN(this.intTimesToCharge))
		{
			this.elmTimesToCharge.value = "";
			this.intCurrentTimesToCharge = null;
		}
		else
		{
			this.SetTimesToChargeTextField();
			this.intCurrentTimesToCharge 	= this.intTimesToCharge;
		}
	}
	
	this.SetEndDate = function()
	{
		var intTimesCharged			= parseInt(this.elmTimesToCharge.value);
		var intRecurringFreq		= this.objChargeTypeData[this.elmRecurringChargeTypeId.value].RecurringFreq;
		var intRecurringFreqType	= this.objChargeTypeData[this.elmRecurringChargeTypeId.value].RecurringFreqType;
		var strEndDate;
		
		if (isNaN(intTimesCharged))
		{
			this.objDetailNodes.EndDate.innerHTML = "&nbsp;";
		}
		else
		{
			
			// Check if the SnapToStartDate combo is defined
			if (this.elmStartDateSnapCombo != null)
			{
				// It is defined
				var intSnapDay = parseInt(this.elmStartDateSnapCombo.value);
				var objStartDate;
				if (intSnapDay == 1)
				{
					// Snapping to the 1st of next month
					objStartDate = new Date(this.today.getFullYear(), this.today.getMonth() + 1, 1);
				}
				else
				{
					// Snapping to the 28th of the current month
					objStartDate = new Date(this.today.getFullYear(), this.today.getMonth(), 28);
				}
				strEndDate = this.CalculateEndDate(intRecurringFreq, intRecurringFreqType, intTimesCharged, objStartDate);
			}
			else
			{
				strEndDate = this.CalculateEndDate(intRecurringFreq, intRecurringFreqType, intTimesCharged);
			}
			this.objDetailNodes.EndDate.innerHTML = strEndDate;
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

	this.CalculateEndDate = function(intRecFreq, intRecFreqType, intTimesCharged, objStartDate)
	{
		//HACK HACK HACK!!!
		//These constants are defined in vixen/framework/definitions.php but are not defined anywhere in the Vixen javascript object
		var BILLING_FREQ_DAY		= 1;
		var BILLING_FREQ_MONTH		= 2;
		var BILLING_FREQ_HALF_MONTH	= 3;
		
		// Take number of days/months/half-months, and number of times to charge
		// and return the last charge date
		var objEndDate;
		var arrMonthName = new Array("JAN","FEB","MAR","APR","MAY","JUN","JUL","AUG", "SEP","OCT","NOV","DEC");
		if (objStartDate == null)
		{
			// Use today as the start date
			objEndDate = new Date(this.today.getFullYear(), this.today.getMonth(), this.today.getDate());
		}
		else
		{
			objEndDate = new Date(objStartDate.getFullYear(), objStartDate.getMonth(), objStartDate.getDate());
		}
		
		if (intRecFreqType == BILLING_FREQ_DAY)
		{
			//Add days and format
			objEndDate.setDate(objEndDate.getDate() + (intTimesCharged * intRecFreq));
		}
		else if (intRecFreqType == BILLING_FREQ_MONTH)
		{
			//Add months and format
			objEndDate.setMonth(objEndDate.getMonth() + (intTimesCharged * intRecFreq));
		}
		else if (intRecFreqType == BILLING_FREQ_HALF_MONTH)
		{
			// half months... umm, lets just pretend half a month is 14 days
			var intHalfMonthsInFuture = intTimesCharged * intRecFreq;
			
			//If it's an odd number of half months, then add the corresponding 
			//number of whole months, and 14 days
			if ((intHalfMonthsInFuture % 2) == 1)
			{
				intHalfMonthsInFuture--;
				objEndDate.setDate(objEndDate.getDate() + 14);
			}
			
			objEndDate.setMonth(objEndDate.getMonth() + (intHalfMonthsInFuture / 2));
		}
		var strEndDate = objEndDate.getDate() + " " + arrMonthName[objEndDate.getMonth()] + ", " + objEndDate.getFullYear();
		return strEndDate;
	}

	//Event handler for when the text within the Times to Charge text box, is changed
	this.TimesChargedChanged = function(objEvent)
	{
		this.GetTextFields();
	
		// if the event did not actually change the value (time to charge) then don't do anything
		if (this.intCurrentTimesToCharge == this.intTimesToCharge)
		{
			return;
		}
		
		// if the value is not a number or less than 1 then don't do anything
		if ((isNaN(this.intTimesToCharge)) || (this.intTimesToCharge <= 0))
		{
			this.intCurrentTimesToCharge = null;
			return;
		}
		
		this.fltRecursionCharge = this.fltMinCharge / this.intTimesToCharge;

		this.SetTextFields();
		
		this.SetEndDate();
	}
	
	//Event handler for when the text within the Recursion charge text box, is changed
	this.RecursionChargeChanged = function()
	{
		this.GetTextFields();
		
		// if the event did not actually change the value (recursion charge) then don't do anything
		if (this.fltCurrentRecursionCharge == this.fltRecursionCharge)
		{
			return;
		}
		
		if ((isNaN(this.fltRecursionCharge)) || (this.fltRecursionCharge <= 0))
		{
			this.fltCurrentRecursionCharge = null;
			return;
		}
		
		this.intTimesToCharge = Math.ceil(this.fltMinCharge / this.fltRecursionCharge);
		
		this.SetTimesToChargeTextField();
		this.SetEndDate();
		
		//TODO!!!
		// Make sure this field isn't updated if you tab off it and onto the Times to Charge field
		// Make sure that when you tab off the Times to Charge field without editing it, it doesn't update the recursionCharge 
		// Make sure when you tab off the MinCharge field it doesn't update the recursionCharge 
		//Maybe you could check if the key is a TAB key and disregard it if it is
	}
	
	this.MinChargeChanged = function(objEvent)
	{
		this.GetTextFields();

		// check if the Minimum charge has actually changed
		if (this.fltCurrentMinCharge == this.fltMinCharge)
		{
			// the value has not changed.
			return;
		}

		if ((isNaN(this.fltMinCharge)) || (this.fltMinCharge <= 0))
		{
			this.fltCurrentMinCharge = null;
			return;
		}
		
		if (isNaN(this.intTimesToCharge))
		{
			// Times to charge is NaN so set it to 1
			this.intTimesToCharge = 1;
		}

		// Calculate the new RecursionCharge based on the min charge and the times to charge
		this.fltRecursionCharge = this.fltMinCharge / this.intTimesToCharge;
		
		this.SetTimesToChargeTextField();
		this.SetRecursionChargeTextField();
		this.SetEndDate();
	}
	
	this.MinChargeLostFocus = function()
	{
		this.SetMinChargeTextField();
	}
	
	this.RecursionChargeLostFocus = function()
	{
		this.SetRecursionChargeTextField();
	}
	

	this.GetTextFields = function()
	{
		this.fltRecursionCharge	= parseFloat(this.StripDollars(this.elmRecursionCharge.value));
		this.fltMinCharge		= parseFloat(this.StripDollars(this.elmMinCharge.value));
		this.intTimesToCharge	= parseInt(this.elmTimesToCharge.value);
	}

	this.SetTextFields = function()
	{
		this.elmRecursionCharge.value	= "$" + (this.fltRecursionCharge).toFixed(2);
		this.elmMinCharge.value			= "$" + (this.fltMinCharge).toFixed(2);
		this.elmTimesToCharge.value		= this.intTimesToCharge;
		
		// store the current values of the text fields
		this.fltCurrentMinCharge		= this.fltMinCharge;
		this.fltCurrentRecursionCharge	= this.fltRecursionCharge;
		this.intCurrentTimesToCharge	= this.intTimesToCharge;
	}

	this.SetRecursionChargeTextField = function()
	{
		this.elmRecursionCharge.value	= "$" + (this.fltRecursionCharge).toFixed(2);
		
		// store the current value of the text field
		this.fltCurrentRecursionCharge = this.fltRecursionCharge;
	}
	
	this.SetMinChargeTextField = function()
	{
		this.elmMinCharge.value		= "$" + (this.fltMinCharge).toFixed(2);
		
		// store the current value of the text field
		this.fltCurrentMinCharge = this.fltMinCharge;
	}
	
	this.SetTimesToChargeTextField = function()
	{
		this.elmTimesToCharge.value	= this.intTimesToCharge;
		
		// store the current value of the text field
		this.intCurrentTimesToCharge = this.intTimesToCharge;
	}

}

// instanciate the objects
if (Vixen.RecurringAdjustmentAdd == undefined)
{
	Vixen.RecurringAdjustmentAdd = new VixenRecurringAdjustmentAddClass;
}
