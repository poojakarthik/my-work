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
		
		// Set the focus to the Name text field
		// This line has been causing an Exception to be thrown.  The exception is a well documented bug in Firefox which can be 
		// protected against by setting the "autocomplete" attribute of all text input elements to "off"
		//Exception... "'Permission denied to set property XULElement.selectedIndex' when calling method: [nsIAutoCompletePopup::selectedIndex]"
		//document.getElementById("RateGroup.Name").focus();
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
		var elmRateGroupId = document.getElementById("RateGroup.Id");
		var elmBaseRateGroupId = document.getElementById("BaseRateGroup.Id");
		
		// Set up the Rate selector control
		var objObjects = {};
		objObjects.RecordType = {};
		objObjects.RecordType.Id = intRecordType;
		
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
	// RateChooser
	//------------------------------------------------------------------------//
	/**
	 * RateChooser
	 *
	 * -
	 *  
	 * -
	 *
	 * @return	void
	 * @method
	 */
	this.RateChooser = function()
	{
		var elmAvailableRatesCombo = document.getElementById("AvailableRatesCombo");
		
		var intSelectedIndex = -1;
		// Make sure only 1 rate is selected and that it is a draft rate
		for (var i=0; i < elmAvailableRatesCombo.options.length; i++)
		{
			if (elmAvailableRatesCombo.options[i].selected)
			{
				// the current option is selected, check if a previous option has been selected
				if (intSelectedIndex != -1)
				{
					// multiple options have been selected
					this.AddNewRate();
					return;
				}
				else
				{
					// this is the first of the selected rates in the AvailableRatesCombo
					intSelectedIndex = i;
					this.AddNewRateExisting(elmAvailableRatesCombo.options[i].value);
					return;
				}				
			}
		}
		
		// Either no rates are selected or just one is selected in the AvailableRatesCombo
		if (intSelectedIndex == -1)
		{
			// No rate is selected
			this.AddNewRate();
			return;
		}
	}

	//------------------------------------------------------------------------//
	// AddNewRateExisting
	//------------------------------------------------------------------------//
	/**
	 * AddNewRateExisting
	 *
	 * Opens the Add New Rate popup window
	 *  
	 * Opens the Add New Rate popup window
	 *
	 * @return	void
	 * @method
	 */
	this.AddNewRateExisting = function(intRateId)
	{
		var objObjects = {};
		objObjects.Objects = {};
		objObjects.Objects.Rate = {};
		objObjects.Objects.Rate.Id = intRateId;
		objObjects.Objects.CallingPage = {};
		objObjects.Objects.CallingPage.AddRateGroup = true;
		
		objObjects.Objects.Action = {};
		objObjects.Objects.Action.CreateNewBasedOnOld = true;
		
		Vixen.Popup.ShowAjaxPopup("AddRatePopup", "large", "Rate", "Add", objObjects);
	}
	
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
		Vixen.Popup.ShowAjaxPopup("AddRatePopup", "large", "Rate", "Add", objObjects);
	}
	
	//------------------------------------------------------------------------//
	// EditRate
	//------------------------------------------------------------------------//
	/**
	 * EditRate
	 *
	 * Opens the Add New Rate popup window for editting the currently selected rate from the Availble Rates combobox
	 *  
	 * Opens the Add New Rate popup window for editting the currently selected rate from the Availble Rates combobox
	 * It will only let you edit a Rate if only one is selected in the combobox, and it's a draft
	 *
	 * @return	void
	 * @method
	 */
	this.EditRate = function()
	{
		var elmAvailableRatesCombo = document.getElementById("AvailableRatesCombo");
		
		var intSelectedIndex = -1;
		// Make sure only 1 rate is selected and that it is a draft rate
		for (var i=0; i < elmAvailableRatesCombo.options.length; i++)
		{
			if (elmAvailableRatesCombo.options[i].selected)
			{
				// the current options is selected, check if a previous option has been selected
				if (intSelectedIndex != -1)
				{
					// multiple options have been selected
					Vixen.Popup.Alert("Only one rate can be selected");
					return;
				}
				else
				{
					// this is the first of the selected rates in the AvailableRatesCombo
					intSelectedIndex = i;
				}
			}
		}
		
		// Either no rates are selected or just one is selected in the AvailableRatesCombo
		if (intSelectedIndex == -1)
		{
			// No rate is selected
			Vixen.Popup.Alert("Please select a draft rate from the list of available rates");
			return;
		}
		
		// Check if the selected rate is a draft
		if (!elmAvailableRatesCombo.options[intSelectedIndex].getAttribute('draft'))
		{
			// The rate is not a draft
			Vixen.Popup.Alert("Only draft rates can be editted");
			return;
		}
		
		var elmRateOption = elmAvailableRatesCombo.options[intSelectedIndex];
		
		var objObjects = {};
		objObjects.Objects = {};
		objObjects.Objects.Rate = {};
		objObjects.Objects.Rate.Id = elmRateOption.value;
		objObjects.Objects.CallingPage = {};
		objObjects.Objects.CallingPage.AddRateGroup = true;
		
		Vixen.Popup.ShowAjaxPopup("AddRatePopup", "large", "Rate", "Add", objObjects);
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
		intRecordType = document.getElementById("RecordTypeCombo").value;
		
		// Stick this array in DBO()->SelectedRates->ArrId
		var objObjects = {};
		objObjects.Objects = {};
		objObjects.Objects.SelectedRates = {};
		objObjects.Objects.SelectedRates.ArrId = arrSelectedRates;
		objObjects.Objects.RecordType = {};
		objObjects.Objects.RecordType.Id = intRecordType;
		objObjects.Objects.CallingPage = {};
		objObjects.Objects.CallingPage.AddRateGroup = true;
		
		// Execute that application template that creates the popup
		Vixen.Popup.ShowAjaxPopup("PreviewRateSummaryPopup", "large", "RateGroup", "PreviewRateSummary", objObjects);
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
		/*
		for (var i=0; i < elmDestinationCombo.options.length; i++)
		{
			if (elmOption.text < elmDestinationCombo.options[i].text)
			{
				elmDestinationCombo.insertBefore(elmOption, elmDestinationCombo.options[i]);
				return;
			}
		}
		*/
		
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

		// Get the AvailableRatesCombo combo box
		var elmCombo = document.getElementById("AvailableRatesCombo");
		
		// create a new option element
		var elmNewOption		= document.createElement('option');
		elmNewOption.value		= objRate.Id;
		elmNewOption.text		= objRate.Name;
		elmNewOption.title		= objRate.Description;
		elmNewOption.selected	= TRUE;
		
		// If the Rate is a fleet rate then mark it as such
		if (objRate.Fleet)
		{
			elmNewOption.text = "Fleet: " + elmNewOption.text;
		}
		
		// If the Rate is a draft then flag it as such
		if (objRate.Draft)
		{
			//FIXIT! currently this will fuck with the alphabetical ordering of the options
			// If we try to maintain the alphabetical order
			elmNewOption.text = "DRAFT: " + elmNewOption.text;
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
		
		/*
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
		*/
		// Append it to the end of the AvailableRates Combo
		elmCombo.appendChild(elmNewOption);
		elmCombo.selectedIndex = elmNewOption.index;
		
		// The option should either be the last in the list, or there are no other options in the list.  Append the option to the end of the list
		//elmCombo.appendChild(elmNewOption);
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
	this.SaveAsDraft = function()
	{
		// Execute AppTemplateRateGroup->Add() and make sure all the input elements of the form are sent
		Vixen.Ajax.SendForm("VixenForm_RateGroup", "Save as Draft", "RateGroup", "Add", "", document.getElementById("AddRateGroupPopupId").value);
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
		Vixen.Ajax.SendForm("VixenForm_RateGroup", "Commit", "RateGroup", "Add", "", document.getElementById("AddRateGroupPopupId").value);
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
		Vixen.Popup.Close(document.getElementById("AddRateGroupPopupId").value);
	}
	
}

// instanciate the objects
Vixen.RateGroupAdd = new VixenRateGroupAddClass;
