//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_group_add.js
//----------------------------------------------------------------------------//
/**
 * rate_group_add
 *
 * javascript required of the "Add Rate Group" popup webpage
 *
 * javascript required of the "Add Rate Group" popup webpage
 * 
 *
 * @file		rate_add.js
 * @language	Javascript
 * @package		ui_app
 * @author		Ross Mullen
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRateAddClass
//----------------------------------------------------------------------------//
/**
 * VixenRateAddClass
 *
 * Encapsulates all event handling required of the "Add Rate" popup webpage
 *
 * Encapsulates all event handling required of the "Add Rate" popup webpage
 * 
 *
 * @package	ui_app
 * @class	VixenRateAddClass
 * 
 */
function VixenRateAddClass()
{
	const RATE_CAP_NO_CAP = 100;
	const RATE_CAP_CAP_UNITS = 101;
	const RATE_CAP_CAP_COST = 102;
	const RATE_CAP_NO_CAP_LIMITS = 103;
	const RATE_CAP_CAP_LIMIT = 104;
	const RATE_CAP_CAP_USAGE = 105;
	
	const RATE_CHARGES_SHOW = 112;
	const RATE_CHARGES_HIDE = 113;
	//------------------------------------------------------------------------//
	// InitialiseForm
	//------------------------------------------------------------------------//
	/**
	 * InitialiseForm
	 *
	 * Prepares the form when initially loaded
	 *
	 * Prepares the form when initially loaded
	 * Responsible for registering event listeners for the various controls in the popup, that need them
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseForm = function()
	{
		document.getElementById("Rate.StartTime").disabled="true";
		document.getElementById("Rate.EndTime").disabled="true";
		document.getElementById("Rate.Duration").disabled="true";
		
		document.getElementById("Rate.StartTime").style.color = "#000000";
		document.getElementById("Rate.EndTime").style.color = "#000000";
		document.getElementById("Rate.Duration").style.color = "#000000";

		var elmPassThroughCheckbox = document.getElementById("Rate.PassThrough");

		elmPassThroughCheckbox.addEventListener("change", PassThroughOnChange, false);
		
		//  Initialise what is visible on the form
		PassThroughOnChange();
	}
	
	// This should only be visible from within this class, so we don't have to worry about its name conflicting with any
	// other javascript functions that are loaded in memory.  I would have made it a method of the class, but then
	// it wouldn't work properly as an event listener
	function PassThroughOnChange()
	{
		var elmPassThroughCheckbox = document.getElementById("Rate.PassThrough");
		
		if (elmPassThroughCheckbox.checked)
		{
			Vixen.RateAdd.RateCapOnChange(RATE_CHARGES_HIDE);
		}
		else
		{
			Vixen.RateAdd.RateCapOnChange(RATE_CHARGES_SHOW);
		}
		// problem when the form is initialised its resetting the value of showing the hidden DIVs
		// check that the input boxs have no values in them if they do do not hide them
	}
	
	
	this.RateCapOnChange = function(intRateCap)
	{
		intRateCap = parseInt(intRateCap);
		switch (intRateCap)
		{
			case RATE_CAP_NO_CAP:
				// hide any details not required for a no cap
				document.getElementById('CapDetailDiv').style.display='none';
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;
			case RATE_CAP_CAP_UNITS:
				// show any details required for a cap
				document.getElementById('CapDetailDiv').style.display='inline';
				break;
			case RATE_CAP_CAP_COST:
				// show the cap details required for a cap
				document.getElementById('CapDetailDiv').style.display='inline';
				break;
			case RATE_CAP_NO_CAP_LIMITS:
				// hide any details not required for a no cap
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;	
			case RATE_CAP_CAP_LIMIT:
				// hide any details not required for a no cap
				document.getElementById('ExcessDetailDiv').style.display='none';
				break;							
			case RATE_CAP_CAP_USAGE:
				// show the excess details required for a cap
				document.getElementById('ExcessDetailDiv').style.display='inline';
				break;
			case RATE_CHARGES_SHOW:
				document.getElementById('RateDetailDiv').style.display = 'inline';
				document.getElementById('CapMainDetailDiv').style.display = 'inline';
				
				var ArrFormElements = [];
				ArrFormElements[1] = "Rate.CapLimit";
				ArrFormElements[2] = "Rate.CapUsage";
				
				ArrFormElements[3] = "Rate.ExsUnits";
				ArrFormElements[4] = "Rate.ExsRatePerUnit";
				ArrFormElements[5] = "Rate.ExsMarkup";
				ArrFormElements[6] = "Rate.ExsPercentage";
				ArrFormElements[7] = "Rate.ExsFlagfall";
							
				for (var intCounter = 1; intCounter <= ArrFormElements.length; intCounter++)
				{
					if (document.getElementById(ArrFormElements[intCounter]).value != "" && document.getElementById(ArrFormElements[intCounter]).value.indexOf("0.0") == -1)
					{
						document.getElementById('CapDetailDiv').style.display='inline';
						document.getElementById('ExcessDetailDiv').style.display='inline'
					}
				}
				
				break;
			case RATE_CHARGES_HIDE:
				document.getElementById('RateDetailDiv').style.display = 'none';
				document.getElementById('CapMainDetailDiv').style.display = 'none';
				break;
		}
	}
	
	//------------------------------------------------------------------------//
	// SaveAsDraft
	//------------------------------------------------------------------------//
	/**
	 * SaveAsDraft
	 *
	 * This is executed when the SaveAsDraft button is clicked and then the user confirms that they want to go through with it
	 *  
	 * This is executed when the SaveAsDraft button is clicked and then the user confirms that they want to go through with it
	 * It submits the form
	 *
	 * @return	void
	 * @method
	 */
	this.SaveAsDraft = function()
	{
		// Execute AppTemplateRateGroup->Add() and make sure all the input elements of the form are sent
		var strFormId = "VixenForm_" + document.getElementById("VixenFormId").value;
		Vixen.Ajax.SendForm(strFormId, "Save as Draft", "Rate", "Add", "", document.getElementById("AddRatePopupId").value);
	}
	
	//------------------------------------------------------------------------//
	// Commit
	//------------------------------------------------------------------------//
	/**
	 * Commit
	 *
	 * This is executed when the Commit button is clicked and then the user confirms that they want to go through with it
	 *  
	 * This is executed when the Commit button is clicked and then the user confirms that they want to go through with it
	 * It submits the form
	 *
	 * @return	void
	 * @method
	 */
	this.Commit = function()
	{
		// Execute AppTemplateRateGroup->Add() and make sure all the input elements of the form are sent
		//TODO! I shouldn't have to hardcode the id of the form.  This isn't very elegant because the form's id and the 
		//AppTemplate and method are defined in more than one place
		var strFormId = "VixenForm_" + document.getElementById("VixenFormId").value;
		Vixen.Ajax.SendForm(strFormId, "Commit", "Rate", "Add", "", document.getElementById("AddRatePopupId").value);
	}
	
	//------------------------------------------------------------------------//
	// Close
	//------------------------------------------------------------------------//
	/**
	 * Close
	 *
	 * This is executed when the Cancel button is clicked and then the user confirms that they want to go through with it
	 *  
	 * This is executed when the Cancel button is clicked and then the user confirms that they want to go through with it
	 *
	 * @return	void
	 * @method
	 */
	this.Close = function()
	{
		// The PopupId, containing this form, has been rendered as a hidden
		Vixen.Popup.Close(document.getElementById("AddRatePopupId").value);
	}
	
	
}


// instantiate the object
Vixen.RateAdd = new VixenRateAddClass;
