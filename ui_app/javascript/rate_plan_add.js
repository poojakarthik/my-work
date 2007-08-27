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
 * @language	PHP
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
		var objObjects = {};
		objObjects.RatePlan = {};
		objObjects.RatePlan.ServiceType = intServiceType;
		Vixen.Ajax.CallAppTemplate('Plan', 'GetRateGroupsForm', objObjects);
	}
	
	//------------------------------------------------------------------------//
	// ChooseRateGroup
	//------------------------------------------------------------------------//
	/**
	 * ChooseRateGroup
	 *
	 * This is executed to add a new rate group to its appropriate combo box, and make it the selected rate group for its record type
	 *  
	 * This is executed to add a new rate group to its appropriate combo box, and make it the selected rate group for its record type
	 * This is called by the "Add Rate Group" popup when a rate group has been added and the popup closes
	 *
	 * @param	int		intId			Id of the Rate Group
	 * @param	string	strDescription	Description of the Rate Group (used to identify it in the combo box)
	 * @param	int		intRecordType	Record Type of the Rate Group (used to work out which combo box it belongs to)
	 * @param	bol		bolFleet		flags whether it is a Fleet Rate Group or Regular Rate group (used to work out which combo box it belongs to)
	 *
	 * @return	void
	 * @method
	 */
	// This is called by the "Add Rate Group" popup page, when a rate group has been added and we want to use it in the Rate Plan currently being defined
	this.ChooseRateGroup = function(intId, strDescription, intRecordType, bolFleet)
	{
		var strComboId;
		
		//alert("RateGroupId = " + intId + " Description = " + strDescription + " RecordType = " + intRecordType + " Fleet = " + bolFleet);
		
		if (bolFleet)
		{
			// The rate group is a fleet rate group
			strComboId = "RateGroup" + intRecordType + ".FleetRateGroupId";
		}
		else
		{
			// The rate group is not a fleet rate group
			strComboId = "RateGroup" + intRecordType + ".RateGroupId";
		}
		
		// Get the combo box associated with this particular record type
		var elmRateGroupCombo = document.getElementById(strComboId);
		if (elmRateGroupCombo == undefined)
		{
			// The new rate group does not belong to any record types associated with this service type
			return;
		}
		
		// create a new option element
		var elmNewRateGroupOption = document.createElement('option');
		elmNewRateGroupOption.value = intId;
		elmNewRateGroupOption.text = strDescription;
		elmNewRateGroupOption.selected = TRUE;

		// Stick it in the combo so that the alphabetical order of the options is preserved
		// i starts at 1 because we don't want to do a comparision between the new option, and the blank option
		for (var i=1; i < elmRateGroupCombo.options.length; i++)
		{
			if (elmNewRateGroupOption.text < elmRateGroupCombo.options[i].text)
			{
				// insert the new option just before the current one
				elmRateGroupCombo.insertBefore(elmNewRateGroupOption, elmRateGroupCombo.options[i]);
				elmRateGroupCombo.focus();
				return;
			}
		}
		
		// The option should either be the last in the list, or there are no other options in the list.  Append the option to the end of the list
		elmRateGroupCombo.appendChild(elmNewRateGroupOption);
		elmRateGroupCombo.focus();
	}
	
}

// instanciate the objects
Vixen.RatePlanAdd = new VixenRatePlanAddClass;
