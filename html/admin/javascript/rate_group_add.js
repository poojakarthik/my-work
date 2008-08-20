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
 * @language	Javascript
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
	// _arrRecordTypes
	//------------------------------------------------------------------------//
	/**
	 * _arrRecordTypes
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

	// Stores references to all the controls on the form
	this.objInputControls = {};
	
	this.strPopupId	= null;

	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
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
	this.Initialise = function(arrRecordTypes, bolDisableElements, strPopupId)
	{
		this._arrRecordTypes = arrRecordTypes;
		this.strPopupId = strPopupId;
		
		this.objInputControls.Name			= $ID("RateGroup.Name");
		this.objInputControls.Description	= $ID("RateGroup.Description");
		this.objInputControls.HasCapLimit	= $ID("CapLimitCheckbox");
		this.objInputControls.CapLimit		= $ID("RateGroup.CapLimit");
		
		this.objInputControls.Fleet			= $ID("RateGroup.Fleet");
		this.objInputControls.ServiceType	= $ID("ServiceTypeCombo");
		this.objInputControls.RecordType	= $ID("RecordTypeCombo");
		
		
		
		this.objInputControls.HasCapLimit.addEventListener("change", this.CapLimitCheckboxOnChange.bind(this), true);
		this.CapLimitCheckboxOnChange();
		
		// Some of the controls can be disabled if they have already been set, and shouldn't change
		if (bolDisableElements)
		{
			this.objInputControls.Fleet.disabled			= true;
			this.objInputControls.ServiceType.disabled		= true;
			this.objInputControls.RecordType.disabled		= true;
			
			this.objInputControls.Fleet.style.color			= "#000000";
			this.objInputControls.ServiceType.style.color	= "#000000";
			this.objInputControls.RecordType.style.color	= "#000000";
		}
		
		// Set the focus to the Name text field
		// This line has been causing an Exception to be thrown.  The exception is a well documented bug in Firefox which can be 
		// protected against by setting the "autocomplete" attribute of all text input elements to "off"
		//Exception... "'Permission denied to set property XULElement.selectedIndex' when calling method: [nsIAutoCompletePopup::selectedIndex]"
		//document.getElementById("RateGroup.Name").focus();
	}
	
	// This is run when the RateSelector section of the popup is drawn
	this.InitialiseRateSelector = function()
	{
		this.objInputControls.AvailableRates	= $ID("AvailableRatesCombo");
		this.objInputControls.SelectedRates		= $ID("SelectedRatesCombo");
	}
	
	// Event Handler for when the CapLimit checkbox is changed
	this.CapLimitCheckboxOnChange = function(objEvent)
	{
		this.objInputControls.CapLimit.style.visibility = (this.objInputControls.HasCapLimit.checked) ? "visible" : "hidden";
	}
	
	//------------------------------------------------------------------------//
	// SelectedRatesComboOnClick
	//------------------------------------------------------------------------//
	/**
	 * SelectedRatesComboOnClick
	 *
	 * Event handler for when the SelectedRateCombo is clicked on, or a key is pressed within it
	 *  
	 * Event handler for when the SelectedRateCombo is clicked on, or a key is pressed within it
	 * All selected Rates in the AvailableRatesCombo are unselected
	 *
	 * @return	void
	 * @method
	 */
	this.SelectedRatesComboOnClick = function()
	{
		document.getElementById("AvailableRatesCombo").selectedIndex = -1;
	}
	
	//------------------------------------------------------------------------//
	// AvailableRatesComboOnClick
	//------------------------------------------------------------------------//
	/**
	 * AvailableRatesComboOnClick
	 *
	 * Event handler for when the AvailableRateCombo is clicked on, or a key is pressed within it
	 *  
	 * Event handler for when the AvailableRateCombo is clicked on, or a key is pressed within it
	 * All selected Rates in the SelectedRatesCombo are unselected
	 *
	 * @return	void
	 * @method
	 */
	this.AvailableRatesComboOnClick = function()
	{
		document.getElementById("SelectedRatesCombo").selectedIndex = -1;
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
		var elmFleet = document.getElementById("RateGroup.Fleet");
		
		// check if we are showing this for a draft RateGroup
		var elmRateGroupId = document.getElementById("RateGroup.Id");
		var elmBaseRateGroupId = document.getElementById("BaseRateGroup.Id");
		
		// Set up the Rate selector control
		var objObjects = {};
		objObjects.RecordType = {};
		objObjects.RecordType.Id = intRecordType;
		objObjects.RecordType.IsFleet = elmFleet.checked;
		
		// If the RateGroup already has an Id then it is a draft
		if (elmRateGroupId.value > 0)
		{
			// pass the RateGroup.Id as well
			objObjects.RateGroup = {};
			objObjects.RateGroup.Id = elmRateGroupId.value;
		}
		if (elmBaseRateGroupId != null)
		{
			// pass the BaseRateGroup.Id as well (Note you will only ever have elmRateGroupId.value > 0 or elmBaseRateGroupId.value > 0, never both)
			objObjects.BaseRateGroup = {};
			objObjects.BaseRateGroup.Id = elmBaseRateGroupId.value;
		}
		
		Vixen.Ajax.CallAppTemplate("RateGroup", "SetRateSelectorControl", objObjects);
	}

	//------------------------------------------------------------------------//
	/**
	 * AddNewRate
	 *
	 * Opens the Add New Rate popup window
	 *  
	 * Opens the Add New Rate popup window
	 * If a single Rate has been selected in either of the Rate comboboxes then
	 * the new rate will be based on this one
	 *
	 * @return	void
	 * @method
	 */
	this.AddNewRate = function()
	{
		var objObjects = {};
		
		//Note: There is now a proper Custom-Event model to handle Popups updating other popups
		// but it has not been implemented in this case, so the old way is being used
		objObjects.CallingPage = {};
		objObjects.CallingPage.AddRateGroup = true;
	
		var elmAvailableRatesCombo	= document.getElementById("AvailableRatesCombo");
		var elmSelectedRatesCombo	= document.getElementById("SelectedRatesCombo");
		var elmCombo = null;
		var intSelectedIndex = null;
		
		// Check if there are any highlighted Rates in either of the Combos.  Only one combo can have selected Rates at any one time
		if (elmAvailableRatesCombo.selectedIndex >= 0)
		{
			// At least 1 item is selected in the AvailableRatesCombo
			elmCombo = elmAvailableRatesCombo;
		}
		else if (elmSelectedRatesCombo.selectedIndex >= 0)
		{
			// At least 1 item is selected in the SelectedRatesCombo
			elmCombo = elmSelectedRatesCombo;
		}
		
		if (elmCombo != null)
		{
			// At least one Rate is selected in the combo referenced by elmCombo.
			// If only one rate is selected, then we want to create a new Rate based on this one.
			// If more than one rate is selected, then create a new rate using a blank "Add Rate" popup
			for (var i=0; i < elmCombo.options.length; i++)
			{
				if (elmCombo.options[i].selected)
				{
					// The current option is selected, check if a previous option has been selected
					if (intSelectedIndex != null)
					{
						// Multiple options have been selected.  Create a new Rate using a blank "Add Rate" popup
						intSelectedIndex = null;
						break;
					}
					else
					{
						// This is the first Rate selected in the combo
						intSelectedIndex = i;
					}				
				}
			}
		}
		
		if (intSelectedIndex != null)
		{
			// Only 1 rate has been selected.  Create a new Rate based on this one
			var intRateId = elmCombo.options[intSelectedIndex].value;
			objObjects.Rate = {};
			objObjects.Rate.Id = intRateId;
			objObjects.Action = {};
			objObjects.Action.CreateNewBasedOnOld = true;
		}
		else
		{
			// Nothing has been selected.  Create a new rate using a blank "Add Rate" popup
			
			// Get the currently selected RecordType and Fleet value
			var intRecordType	= document.getElementById("RecordTypeCombo").value;
			var bolIsFleet		= document.getElementById("RateGroup.Fleet").checked;
	
			objObjects.RecordType = {};
			objObjects.RecordType.Id = intRecordType;
			objObjects.Rate = {};
			objObjects.Rate.Fleet = bolIsFleet;
		}
		
		Vixen.Popup.ShowAjaxPopup("AddRatePopup", "large", "Add New Rate", "Rate", "Add", objObjects);
	}
	
	//------------------------------------------------------------------------//
	// EditRate
	//------------------------------------------------------------------------//
	/**
	 * EditRate
	 *
	 * Opens the Add New Rate popup window for editting the currently selected rate
	 *  
	 * Opens the Add New Rate popup window for editting the currently selected rate
	 * It will only let you edit a Rate if only one is selected and it's a draft
	 *
	 * @return	void
	 * @method
	 */
	this.EditRate = function()
	{
		var elmAvailableRatesCombo	= document.getElementById("AvailableRatesCombo");
		var elmSelectedRatesCombo	= document.getElementById("SelectedRatesCombo");
		var elmCombo;
		
		// Work out which combobox has the item in it.  Items cannot be selected in both comboboxes at the same time
		if (elmAvailableRatesCombo.selectedIndex >= 0)
		{
			// At least 1 item is selected in the AvailableRatesCombo
			elmCombo = elmAvailableRatesCombo;
		}
		else if (elmSelectedRatesCombo.selectedIndex >= 0)
		{
			// At least 1 item is selected in the SelectedRatesCombo
			elmCombo = elmSelectedRatesCombo;
		}
		else
		{
			// Nothing has been selected
			Vixen.Popup.Alert("Please select a Draft Rate for editing");
			return;
		}
		
		// Make sure only 1 rate is selected
		var intSelectedIndex = -1;
		for (var i=0; i < elmCombo.options.length; i++)
		{
			if (elmCombo.options[i].selected)
			{
				// The current option is selected, check if a previous option has been selected
				if (intSelectedIndex != -1)
				{
					// Multiple options have been selected
					Vixen.Popup.Alert("Only one rate can be selected");
					return;
				}
				else
				{
					// This is the first of the selected rates in the combo
					intSelectedIndex = i;
				}
			}
		}
		
		// Check if the selected rate is a draft
		if (!elmCombo.options[intSelectedIndex].getAttribute('draft'))
		{
			// The rate is not a draft
			Vixen.Popup.Alert("Only draft rates can be editted");
			return;
		}
		
		var elmRateOption = elmCombo.options[intSelectedIndex];
		
		var objObjects						= {};
		objObjects.Rate						= {};
		objObjects.Rate.Id					= elmRateOption.value;
		objObjects.CallingPage				= {};
		objObjects.CallingPage.AddRateGroup	= true;
		
		Vixen.Popup.ShowAjaxPopup("AddRatePopup", "large", "Edit Rate", "Rate", "Add", objObjects);
	}


	//------------------------------------------------------------------------//
	// PreviewRateSummary
	//------------------------------------------------------------------------//
	/**
	 * PreviewRateSummary
	 *
	 * Opens the Preview Rate Summary popup window
	 *  
	 * Opens the Preview Rate Summary popup window
	 *
	 * @return	void
	 * @method
	 */
	this.PreviewRateSummary = function()
	{
		// Retrieve the list of rates currently selected for this Rate Group
		var arrSelectedRates = new Array();
		
		var elmSelectedRatesCombo = document.getElementById("SelectedRatesCombo");
		
		var bolHasSelectedRates = FALSE;
		for (var i=0; i < elmSelectedRatesCombo.options.length; i++)
		{
			arrSelectedRates.push(elmSelectedRatesCombo.options[i].value);
			bolHasSelectedRates = TRUE;
		}
		
		// If no rates are selected then don't do anything
		if (!bolHasSelectedRates)
		{
			Vixen.Popup.Alert("Please select some rates");
			return;
		}
		
		// Retrieve the RecordType currently selected
		var intRecordType = document.getElementById("RecordTypeCombo").value;
		
		// Retrieve the Fleet checkbox value
		var bolIsFleet = document.getElementById("RateGroup.Fleet").checked;
		
		// Stick this array in DBO()->SelectedRates->ArrId
		var objObjects = {};
		objObjects.SelectedRates = {};
		objObjects.SelectedRates.ArrId = arrSelectedRates;
		objObjects.RecordType = {};
		objObjects.RecordType.Id = intRecordType;
		objObjects.RateGroup = {};
		objObjects.RateGroup.Fleet = bolIsFleet;
		
		// Execute that application template that creates the popup
		Vixen.Popup.ShowAjaxPopup("PreviewRateSummaryPopup", "large", "Rate Summary", "RateGroup", "PreviewRateSummary", objObjects);
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
		
		// unselect all selected items in the DestinationCombo
		elmDestinationCombo.selectedIndex = -1;
		
		while (elmSourceCombo.options[i] != null)
		{
			if (elmSourceCombo.options[i].selected)
			{
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
	
	//------------------------------------------------------------------------//
	// AddRatePopupOnClose
	//------------------------------------------------------------------------//
	/**
	 * AddRatePopupOnClose
	 *
	 * This is executed when the "Add Rate" popup is closed and it needs to update the "Add Rate Group" page
	 *  
	 * This is executed when the "Add Rate" popup is closed and it needs to update the "Add Rate Group" page
	 * This is called by the "Add Rate" popup when a rate has been added and the popup closes.
	 * It updates the Rate Selector control
	 *
	 * @param	object	objRate			Defines a new Rate.  It contains the properties:
	 *									Id, Name, Description, RecordType, Fleet, Draft
	 *
	 * @return	void
	 * @method
	 */
	this.AddRatePopupOnClose = function(objRate)
	{
		//alert("RateGroupId = " + objRate.Id + " Description = " + objRate.Description + " RecordType = " + objRate.RecordType + " Fleet = " + objRate.Fleet);
		
		// if objRate.RecordType is not the same as the one currently selected then don't do anything
		if (objRate.RecordType != document.getElementById('RecordTypeCombo').value)
		{
			return;
		}

		var elmSelectedRatesCombo	= document.getElementById("SelectedRatesCombo");
		var elmAvailableRatesCombo	= document.getElementById("AvailableRatesCombo");
		// Unselect all selected items from these 2 comboboxes
		elmSelectedRatesCombo.selectedIndex		= -1;
		elmAvailableRatesCombo.selectedIndex	= -1;

		// create a new option element
		var elmNewOption		= document.createElement('option');
		elmNewOption.value		= objRate.Id;
		elmNewOption.text		= objRate.Name;
		elmNewOption.title		= objRate.Description;
		elmNewOption.selected	= TRUE;
		
		// If the Rate is a draft then flag it as such
		if (objRate.Draft)
		{
			//FIXIT! currently this will screw with the alphabetical ordering of the options
			// If we try to maintain the alphabetical order
			elmNewOption.text = "DRAFT: " + elmNewOption.text;
			elmNewOption.setAttribute('draft', 'draft');
		}

		// If a Draft Rate has been editted then we want to put it back in the Combobox it was currently in.
		// If it's a new Rate then we want to put it in the AvailableRates combo.
		// Check if a Rate with the same Id is already present in one of the combo's
		
		// elmCombo will be set to the combobox which the Rate will be inserted into
		var elmCombo = null;
				
		// If it was already in the list of Selected Rates then remove the old option 
		// element and mark this combo as the one to add the new rate to
		for (var i=0; i < elmSelectedRatesCombo.options.length; i++)
		{
			if (elmNewOption.value == elmSelectedRatesCombo.options[i].value)
			{
				// The Rate was found in the list of Selected Rates
				elmSelectedRatesCombo.removeChild(elmSelectedRatesCombo.options[i]);
				elmCombo = elmSelectedRatesCombo;
				break;
			}
		}
		
		if (elmCombo == null)
		{
			// The rate was not in the list of Selected Rates.  Search for it in the list of Available Rates
			elmCombo = elmAvailableRatesCombo;
			for (var i=0; i < elmCombo.options.length; i++)
			{
				if (elmNewOption.value == elmCombo.options[i].value)
				{
					// The Rate was found in the list of Available Rates
					elmCombo.removeChild(elmCombo.options[i]);
					break;
				}
			}
		}
		
		// elmCombo is now the correct combobox to add the rate to
		
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
		elmCombo.selectedIndex = elmNewOption.index;
		
		elmCombo.focus();
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
	this.SaveAsDraft = function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			var strMsg = "Are you sure you want to save this Rate Group as a Draft?";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.RateGroupAdd.SaveAsDraft(true)});
			return;
		}
		
		// Execute AppTemplateRateGroup->Add() and make sure all the input elements of the form are sent
		Vixen.Ajax.SendForm("VixenForm_RateGroup", "Save as Draft", "RateGroup", "Add", "", this.strPopupId);
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
	this.Commit = function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			var strMsg = "Are you sure you want to commit this Rate Group?<br />The Rate Group cannot be edited once it is committed";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.RateGroupAdd.Commit(true)});
			return;
		}
		
		// Execute AppTemplateRateGroup->Add() and make sure all the input elements of the form are sent
		//TODO! I shouldn't have to hardcode the id of the form.  This isn't very elegant because the form's id and the 
		//AppTemplate and method are defined in more than one place
		Vixen.Ajax.SendForm("VixenForm_RateGroup", "Commit", "RateGroup", "Add", "", this.strPopupId);
		
		//TODO! Rewrite this so that it uses Vixen.Ajax.CallAppTemplate instead of Vixen.Ajax.SendForm so
		//that you have more control over the structure of the data that you send to the server
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
		Vixen.Popup.Close(this.strPopupId);
	}
}

// instanciate the objects
if (Vixen.RateGroupAdd == undefined)
{
	Vixen.RateGroupAdd = new VixenRateGroupAddClass;
}
