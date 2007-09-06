//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// rate_plan_add.js
//----------------------------------------------------------------------------//
/**
 * rate_plan_add
 *
 * javascript required of the "Add Rate Plan" webpage
 *
 * javascript required of the "Add Rate Plan" webpage
 * 
 *
 * @file		rate_plan_add.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRatePlanAddClass
//----------------------------------------------------------------------------//
/**
 * VixenRatePlanAddClass
 *
 * Encapsulates all event handling required of the "Add Rate Plan" webpage
 *
 * Encapsulates all event handling required of the "Add Rate Plan" webpage
 * 
 *
 * @package	ui_app
 * @class	VixenRatePlanAddClass
 * 
 */
function VixenRatePlanAddClass()
{
	
	//------------------------------------------------------------------------//
	// ChangeServiceType
	//------------------------------------------------------------------------//
	/**
	 * ChangeServiceType
	 *
	 * Event handler for when the Service Type is chosen from the Service Type Combobox
	 *  
	 * Event handler for when the Service Type is chosen from the Service Type Combobox
	 *
	 * @param	int		intServiceType		Id of the ServiceType selected
	 *
	 * @return	void
	 * @method
	 */
	this.ChangeServiceType = function(intServiceType)
	{
		var elmRatePlanId = document.getElementById("RatePlan.Id");
		var elmBaseRatePlanId = document.getElementById("BaseRatePlan.Id");
		
		var objObjects = {};
		objObjects.RatePlan = {};
		objObjects.RatePlan.ServiceType = intServiceType;
		
		if (elmRatePlanId.value > 0)
		{
			// We are displaying a draft rate group, and want to show all its associated Rate Groups
			objObjects.RatePlan.Id = elmRatePlanId.value;
		}
		if (elmBaseRatePlanId != null)
		{
			// pass the BaseRatePlan.Id as well (Note you will only ever have elmRatePlanId.value > 0 or elmBaseRatePlanId.value > 0, never both)
			objObjects.BaseRatePlan = {};
			objObjects.BaseRatePlan.Id = elmBaseRatePlanId.value;
		}
		
		Vixen.Ajax.CallAppTemplate('Plan', 'GetRateGroupsForm', objObjects);
	}
	
	//------------------------------------------------------------------------//
	// AddRateGroupPopupOnClose
	//------------------------------------------------------------------------//
	/**
	 * AddRateGroupPopupOnClose
	 *
	 * This is executed when the "Add Rate Group" popup is closed and it needs to update the "Add Rate Plan" page
	 *  
	 * This is executed when the "Add Rate Group" popup is closed and it needs to update the "Add Rate Plan" page
	 * This is called by the "Add Rate Group" popup when a rate group has been added and the popup closes
	 *
	 * @param	object	objRateGroup	Defines a new Rate Group.  It contains the properties:
	 *									Id, Name, RecordType, Fleet, Draft
	 *
	 * @return	void
	 * @method
	 */
	this.AddRateGroupPopupOnClose = function(objRateGroup)
	{
		var strComboId;
		
		//alert("RateGroupId = " + objRateGroup.Id + " Name = " + objRateGroup.Name + " RecordType = " + objRateGroup.RecordType + " Fleet = " + objRateGroup.Fleet);
		
		if (objRateGroup.Fleet)
		{
			// The rate group is a fleet rate group
			strComboId = "RateGroup" + objRateGroup.RecordType + ".FleetRateGroupId";
		}
		else
		{
			// The rate group is not a fleet rate group
			strComboId = "RateGroup" + objRateGroup.RecordType + ".RateGroupId";
		}
		
		// Get the combo box associated with this particular record type
		var elmRateGroupCombo = document.getElementById(strComboId);
		if (elmRateGroupCombo == undefined)
		{
			// The new rate group does not belong to any record types associated with this service type
			return;
		}
		
		// create a new option element
		var elmNewRateGroupOption		= document.createElement('option');
		elmNewRateGroupOption.value		= objRateGroup.Id;
		elmNewRateGroupOption.text		= objRateGroup.Name;
		elmNewRateGroupOption.selected	= TRUE;
		
		if (objRateGroup.Draft)
		{
			elmNewRateGroupOption.text = "DRAFT: " + elmNewRateGroupOption.text;
			elmNewRateGroupOption.setAttribute('draft', 'draft');
		}

		// Remove the old option from the combo box, if it exists
		for (var i=0; i < elmRateGroupCombo.options.length; i++)
		{
			if (elmRateGroupCombo.options[i].value == elmNewRateGroupOption.value)
			{
				// Destroy the old one
				elmRateGroupCombo.removeChild(elmRateGroupCombo.options[i]);
				break;
			}
		}
		
		// Stick it in the combo so that the alphabetical order of the options is preserved
		// i starts at 1 because we don't want to do a comparision between the new option, and the blank option
		for (var i=1; i < elmRateGroupCombo.options.length; i++)
		{
			if (elmNewRateGroupOption.text < elmRateGroupCombo.options[i].text)
			{
				// insert the new option just before the current one
				elmRateGroupCombo.insertBefore(elmNewRateGroupOption, elmRateGroupCombo.options[i]);
				elmRateGroupCombo.selectedIndex = elmNewRateGroupOption.index;
				elmRateGroupCombo.focus();
				return;
			}
		}
		
		// The option should either be the last in the list, or there are no other options in the list.  Append the option to the end of the list
		elmRateGroupCombo.appendChild(elmNewRateGroupOption);
		elmRateGroupCombo.selectedIndex = elmNewRateGroupOption.index;
		elmRateGroupCombo.focus();
	}
	
	//------------------------------------------------------------------------//
	// EditRateGroup
	//------------------------------------------------------------------------//
	/**
	 * EditRateGroup
	 *
	 * This is executed when the user wants to edit a draft Rate Group
	 *  
	 * This is executed when the user wants to edit a draft Rate Group
	 * Only draft RateGroups can be edited
	 *
	 * @param	int		intRecordType		RecordType Id of the rate group
	 * @param	bool	bolFleet			TRUE if the Rate Group to edit is is a fleet Rate Group
	 *
	 * @return	void
	 * @method
	 */
	this.EditRateGroup = function(intRecordType, bolFleet)
	{
		var elmRateGroupCombo;
		var strRateGroupCombo;
		// Find the Rate Group that the user wants to edit
		// Only draft Rate Groups can be editted
		if (bolFleet)
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".FleetRateGroupId";
		}
		else
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".RateGroupId";
		}

		elmRateGroupCombo = document.getElementById(strRateGroupCombo);
		
		if (elmRateGroupCombo.value == 0)
		{
			// A Rate Group has not been selected
			Vixen.Popup.Alert("No Rate Group has been selected");
			return;
		}
		
		var elmRateGroupOption = elmRateGroupCombo.options[elmRateGroupCombo.selectedIndex];
		
		// Only allow the user to edit the Rate Group if it is a draft rate group
		if (elmRateGroupOption.getAttribute('draft'))
		{
			// The Rate Group is a draft. Open the Edit Rate Group popup
			var objObjects = {};
			objObjects.Objects = {};
			objObjects.Objects.RateGroup = {};
			objObjects.Objects.RateGroup.Id = elmRateGroupOption.value;
			objObjects.Objects.CallingPage = {};
			objObjects.Objects.CallingPage.AddRatePlan = true;
			
			Vixen.Popup.ShowAjaxPopup("AddRateGroupPopup", "large", "RateGroup", "Add", objObjects);
		}
		else
		{
			Vixen.Popup.Alert("The currently selected Rate Group is not a draft");
		}
		
	}
	
	//------------------------------------------------------------------------//
	// AddRateGroup
	//------------------------------------------------------------------------//
	/**
	 * AddRateGroup
	 *
	 * This is executed when the user wants to add a Rate Group
	 *  
	 * This is executed when the user wants to add a Rate Group
	 *
	 * @param	int		intRecordType		RecordType Id that the new Rate Group will be associated with
	 * @param	bool	bolFleet			[optional, defaults to FALSE] TRUE if the new Rate Group is to be a fleet Rate Group
	 *
	 * @return	void
	 * @method
	 */
	this.AddRateGroup = function(intRecordType, bolFleet)
	{
		var objObjects = {};
		objObjects.Objects = {};
		var elmRateGroupCombo;
		var strRateGroupCombo;
		
		// Set the default value for bolFleet, if it has not been specified
		bolFleet = (bolFleet == null) ? FALSE : bolFleet;

		// If a RateGroup is currently selected, then have the "Add Rate Group" page load it, so that the
		// new RateGroup will be based on the old one

		// Find if a Rate Group has been selected to base the new one on
		if (bolFleet)
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".FleetRateGroupId";
		}
		else
		{
			strRateGroupCombo = "RateGroup" + intRecordType + ".RateGroupId";
		}

		elmRateGroupCombo = document.getElementById(strRateGroupCombo);
		
		if (elmRateGroupCombo.value != 0)
		{
			// A RateGroup has been selected, so set up the objects to send to the AppTemplate
			objObjects.Objects.BaseRateGroup = {};
			objObjects.Objects.BaseRateGroup.Id = elmRateGroupCombo.value;
		}
		else
		{
			// A RateGroup has not been selected.  The "Add Rate Group" page will require the RecordType and Fleet flag of the new RateGroup
			objObjects.Objects.RecordType = {};
			objObjects.Objects.RecordType.Id = intRecordType;
			objObjects.Objects.RateGroup = {};
			objObjects.Objects.RateGroup.Fleet = bolFleet;
		}
		
		// Set up remaining data that needs to be sent to the "Add Rate Group" page
		objObjects.Objects.CallingPage = {};
		objObjects.Objects.CallingPage.AddRatePlan = true;
		
		// Call the "Add Rate Group" page
		Vixen.Popup.ShowAjaxPopup("AddRateGroupPopup", "large", "RateGroup", "Add", objObjects);
	}
	
	
	//------------------------------------------------------------------------//
	// ReturnToCallingPage
	//------------------------------------------------------------------------//
	/**
	 * ReturnToCallingPage
	 *
	 * This is executed when the Cancel button is clicked and the user confirms the action
	 *  
	 * This is executed when the Cancel button is clicked and the user confirms the action
	 * If the hidden element CallingPage.Href stores am href then the browser is relocated to it.
	 * Else the browser steps back one page through its history
	 *
	 * @return	void
	 * @method
	 */
	this.ReturnToCallingPage = function()
	{
		// There should be a hidden input value called CallingPage.Href storing the href of the page that called this one
		var elmCallingPage = document.getElementById("CallingPage.Href");
		
		if (elmCallingPage.value == '')
		{
			// No calling page has been specified. Use the history object to go back one page
			window.history.go(-1);
		}
		else
		{
			// A calling page has been specified
			location.href = elmCallingPage.value;
		}
	}
	
	//------------------------------------------------------------------------//
	// SaveAsDraft
	//------------------------------------------------------------------------//
	/**
	 * SaveAsDraft
	 *
	 * This is executed when the SaveAsDraft button is clicked and the user confirms the action
	 *  
	 * This is executed when the SaveAsDraft button is clicked and the user confirms the action
	 * It posts the form's input elements via ajax, effectively saving the RatePlan as a Draft
	 *
	 *
	 * @return	void
	 * @method
	 */
	this.SaveAsDraft = function()
	{
		// Execute AppTemplatePlan->Add() and make sure all the input elements of the form are sent
		Vixen.Ajax.SendForm("VixenForm_AddPlan", "Save as Draft", "Plan", "Add");
	}
	
	//------------------------------------------------------------------------//
	// Commit
	//------------------------------------------------------------------------//
	/**
	 * Commit
	 *
	 * This is executed when the Commit button is clicked and the user confirms the action
	 *  
	 * This is executed when the Commit button is clicked and the user confirms the action
	 * It posts the form's input elements via ajax, effectively saving the RatePlan as a permanent, usable Rate Plan
	 *
	 *
	 * @return	void
	 * @method
	 */
	this.Commit = function()
	{
		// Execute AppTemplatePlan->Add() and make sure all the input elements of the form are sent
		Vixen.Ajax.SendForm("VixenForm_AddPlan", "Commit", "Plan", "Add");
	}
}

// instanciate the objects
Vixen.RatePlanAdd = new VixenRatePlanAddClass;
