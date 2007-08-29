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
 * @file		rate_group_add.js
 * @language	PHP
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.08
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenRateGroupAddClass
//----------------------------------------------------------------------------//
/**
 * VixenRateGroupAddClass
 *
 * Encapsulates all event handling required of the "Add Rate Group" popup webpage
 *
 * Encapsulates all event handling required of the "Add Rate Group" popup webpage
 * 
 *
 * @package	ui_app
 * @class	VixenRateGroupAddClass
 * 
 */
function VixenRateGroupAddClass()
{
	//------------------------------------------------------------------------//
	// _objRecordTypes
	//------------------------------------------------------------------------//
	/**
	 * _objRecordTypes
	 *
	 * Stores data relating to each RecordType from the database table "RecordType"
	 *
	 * Stores data relating to each RecordType from the database table "RecordType"
	 * 
	 * @type		indexed array of objects
	 *
	 * @property
	 */
	this._arrRecordTypes = {};

	//------------------------------------------------------------------------//
	// InitialiseForm
	//------------------------------------------------------------------------//
	/**
	 * InitialiseForm
	 *
	 * Sets the member variable storing data relating to all RecordTypes
	 *
	 * Sets the member variable storing data relating to all RecordTypes
	 *
	 * @param	array		arrRecordTypes		array storing all RecordType data
	 *											structure:
	 *											arrRecordTypes[].Id
	 *															.ServiceType
	 *															.Description
	 * @return	void
	 * @method
	 */
	this.InitialiseForm = function(arrRecordTypes, bolIsDraft)
	{
		this._arrRecordTypes = arrRecordTypes;
		
		// If the RateGroup is a draft then disable the Fleet Checkbox, Service Type Combobox and the Record Type combobox
		if (bolIsDraft)
		{
			document.getElementById("RateGroup.Fleet").disabled		= true;
			document.getElementById("ServiceTypeCombo").disabled	= true;
			document.getElementById("RecordTypeCombo").disabled		= true;
		}
	}
	
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
		// What to do:
		// Set up the contents of the Service Type combobox
		// Empty the contents of the RateSelectionDiv
		var elmNewOption;
		
		// Set up the contents of the Service Type combobox
		var elmRecordTypeCombo = document.getElementById("RecordTypeCombo");
		
		// Empty its current contents 
		while (elmRecordTypeCombo.childNodes.length > 0)
		{
			elmRecordTypeCombo.removeChild(elmRecordTypeCombo.childNodes[0]);
		}

		// Stick in the empty option
		elmNewOption = document.createElement('option');
		elmNewOption.setAttribute('value', 0);
		elmNewOption.innerHTML = "&nbsp;";
		elmRecordTypeCombo.appendChild(elmNewOption);

		// Stick in each Record Type belonging to the Service Type intServiceType
		for (i=0; i < this._arrRecordTypes.length; i++)
		{
			if (this._arrRecordTypes[i].ServiceType == intServiceType)
			{
				elmNewOption = document.createElement('option');
				elmNewOption.setAttribute('value', this._arrRecordTypes[i].Id);
				elmNewOption.innerHTML = this._arrRecordTypes[i].Description;
				elmRecordTypeCombo.appendChild(elmNewOption);
			}
		}

		// Remove the contents of the Rate Selector Control (AvailableRatesCombo)
		var elmAvailableRatesCombo = document.getElementById("AvailableRatesCombo");
		while (elmAvailableRatesCombo.childNodes.length > 0)
		{
			elmAvailableRatesCombo.removeChild(elmAvailableRatesCombo.childNodes[0]);
		}

		// Remove the contents of the SelectedRatesCombo
		var elmSelectedRatesCombo = document.getElementById("SelectedRatesCombo");
		while (elmSelectedRatesCombo.childNodes.length > 0)
		{
			elmSelectedRatesCombo.removeChild(elmSelectedRatesCombo.childNodes[0]);
		}

		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// ChangeRecordType
	//------------------------------------------------------------------------//
	/**
	 * ChangeRecordType
	 *
	 * Event handler for when the Record Type is chosen from the Record Type Combobox
	 *  
	 * Event handler for when the Record Type is chosen from the Record Type Combobox
	 *
	 * @param	int		intRecordType		Id of the RecordType selected
	 *
	 * @return	void
	 * @method
	 */
	this.ChangeRecordType = function(intRecordType)
	{
		// If this is being explicitly executed from code then the record type details will not actually be the 
		// currently selected ones. Therefore we have to do it manually.
		var elmRecordTypeCombo = document.getElementById("RecordTypeCombo");
		elmRecordTypeCombo.value = intRecordType;
		
		// check if we are showing this for a draft RateGroup
		var intRateGroupId = document.getElementById("RateGroup.Id").value;
		
		// Set up the Rate selector control
		var objObjects = {};
		objObjects.RecordType = {};
		objObjects.RecordType.Id = intRecordType;
		
		// If the RateGroup already has an Id then it is a draft
		if (intRateGroupId > 0)
		{
			// pass the RateGroup.Id as well
			objObjects.RateGroup = {};
			objObjects.RateGroup.Id = intRateGroupId;
		}
		
		Vixen.Ajax.CallAppTemplate("RateGroup", "SetRateSelectorControl", objObjects);
	}

	//------------------------------------------------------------------------//
	// AddNewRate
	//------------------------------------------------------------------------//
	/**
	 * AddNewRate
	 *
	 * Opens the Add New Rate popup window
	 *  
	 * Opens the Add New Rate popup window
	 *
	 * @return	void
	 * @method
	 */
	this.AddNewRate = function()
	{
		// Get the currently selected RecordType and ServiceType
		var intRecordType	= document.getElementById("RecordTypeCombo").value;
		var intServiceType	= document.getElementById("ServiceTypeCombo").value;

		var objObjects = {};
		objObjects.Objects = {};
		objObjects.Objects.RecordType = {};
		objObjects.Objects.RecordType.Id = intRecordType;
		objObjects.Objects.ServiceType = {};
		objObjects.Objects.ServiceType.Id = intServiceType;
		objObjects.Objects.CallingPage = {};
		objObjects.Objects.CallingPage.AddRateGroup = true;
		
		//Vixen.Ajax.CallAppTemplate("Rate", "Add", objObjects);
		Vixen.Popup.ShowAjaxPopup("AddRatePopup", "large", "Rate", "Add", objObjects, "modeless");
	}
	
	//------------------------------------------------------------------------//
	// MoveSelectedOptions
	//------------------------------------------------------------------------//
	/**
	 * MoveSelectedOptions
	 *
	 * Removes the highlighted options from the the source combobox and puts them in the destination combobox
	 *  
	 * Removes the highlighted options from the the source combobox and puts them in the destination combobox
	 *
	 * @return	void
	 * @method
	 */
	this.MoveSelectedOptions = function(strSourceComboId, strDestinationComboId)
	{
		var elmSourceCombo = document.getElementById(strSourceComboId);
		var elmDestinationCombo = document.getElementById(strDestinationComboId);
		var i = 0;
		
		while (elmSourceCombo.options[i] != null)
		{
			if (elmSourceCombo.options[i].selected)
			{
				// unselect the option
				elmSourceCombo.options[i].selected = FALSE;
				
				// remove the option from the source and stick it in the destination combobox
				this.MoveOption(elmSourceCombo.options[i], elmSourceCombo, elmDestinationCombo);
			}
			else
			{
				// move the index along one
				i++;
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// MoveOption
	//------------------------------------------------------------------------//
	/**
	 * MoveOption
	 *
	 * Removes the option from the source combo and sticks it in the destination combo, preserving alphabetical order
	 *  
	 * Removes the option from the source combo and sticks it in the destination combo, preserving alphabetical order
	 *
	 * @param	object	elmOption				option element to be removed from elmSourceCombo and placed in elmDestinationCombo
	 * @param	object	elmSourceCombo			combo box which elmOption currently belongs to.  This isn't actually needed because 
	 *											you could just reference is as elmOption.parent
	 * @param	object	elmDestinationCombo		combo box to add elmOption to
	 *
	 * @return	void
	 * @method
	 */
	this.MoveOption = function(elmOption, elmSourceCombo, elmDestinationCombo)
	{
		// Remove the option from the source combo
		elmSourceCombo.removeChild(elmOption);
		
		// Stick it in the destination combo so that the alphabetical order of the options is preserved
		for (var i=0; i < elmDestinationCombo.options.length; i++)
		{
			if (elmOption.text < elmDestinationCombo.options[i].text)
			{
				elmDestinationCombo.insertBefore(elmOption, elmDestinationCombo.options[i]);
				return;
			}
		}
		
		// If it has gotten this far then add the element to the end of the list of options
		elmDestinationCombo.appendChild(elmOption);
		return;
	}
	
	// Updates the AvailableRatesCombo with this new rate, and selects it
	this.ChooseRate = function(intId, strDescription, strName, intRecordType, bolIsDraft)
	{
		//alert("RateGroupId = " + intId + " Description = " + strDescription + " RecordType = " + intRecordType + " Fleet = " + bolFleet);
		
		// if intRecordType is not the same as the one currently selected then don't do anything
		if (intRecordType != document.getElementById('RecordTypeCombo').value)
		{
			return;
		}

		// Get the AvailableRatesCombo combo box
		var elmCombo = document.getElementById("AvailableRatesCombo");
		
		// create a new option element
		var elmNewOption = document.createElement('option');
		elmNewOption.value = intId;
		elmNewOption.text = strName;
		elmNewOption.title = strDescription;
		elmNewOption.selected = TRUE;
		
		// If the Rate is a draft then flag it as such
		if (bolIsDraft)
		{
			//FIXIT! currently this will fuck with the alphabetical ordering of the options
			elmNewOption.text = "[DRAFT] - " + elmNewOption.text;
			elmNewOption.setAttribute('draft', 'draft');
		}

		// If it was already in the list of Available Rates then remove the old option element
		for (var i=0; i < elmCombo.options.length; i++)
		{
			if (elmNewOption.value == elmCombo.options[i].value)
			{
				elmCombo.removeChild(elmCombo.options[i]);
				break;
			}
		}

		// Stick it in the combo so that the alphabetical order of the options is preserved
		for (var i=0; i < elmCombo.options.length; i++)
		{
			if (elmNewOption.text < elmCombo.options[i].text)
			{
				// insert the new option just before the current one
				elmCombo.insertBefore(elmNewOption, elmCombo.options[i]);
				return;
			}
		}
		
		// The option should either be the last in the list, or there are no other options in the list.  Append the option to the end of the list
		elmCombo.appendChild(elmNewOption);
		elmCombo.focus();
	}
	
}

// instanciate the objects
Vixen.RateGroupAdd = new VixenRateGroupAddClass;
