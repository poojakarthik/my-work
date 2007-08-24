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
	 *															.Name
	 * @return	void
	 * @method
	 */
	this.InitialiseForm = function(arrRecordTypes)
	{
		this._arrRecordTypes = arrRecordTypes;
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
		
		// Empty its current contents (there is probably a more elegant way of doing this)
		var arrOldOptions = elmRecordTypeCombo.getElementsByTagName("option");
		while (arrOldOptions.length > 0)
		{
			arrOldOptions[0].parentNode.removeChild(arrOldOptions[0]);
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
				elmNewOption.innerHTML = this._arrRecordTypes[i].Name;
				elmRecordTypeCombo.appendChild(elmNewOption);
			}
		}

		// Remove the contents of the Rate Selector Control
		var elmAvailableRatesCombo = document.getElementById("AvailableRatesCombo");
		
		// Empty its current contents (there is probably a more elegant way of doing this)
		while (elmAvailableRatesCombo.childNodes.length > 0)
		{
			elmAvailableRatesCombo.removeChild(elmAvailableRatesCombo.childNodes[0]);
		}

		var elmSelectedRatesCombo = document.getElementById("SelectedRatesCombo");
		
		// Empty its current contents (there is probably a more elegant way of doing this)
		//var arrOldOptions = elmRecordTypeCombo.getElementsByTagName("option");
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
		// currently selected ones. Therefore we have to do it manually.  There is probably a better way of doing this.
		var elmRecordTypeCombo = document.getElementById("RecordTypeCombo");
		elmRecordTypeCombo.value = intRecordType;
		
		// Set up the Rate selector control
		var objObjects = {};
		objObjects.RecordType = {};
		objObjects.RecordType.Id = intRecordType;
		
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
		
		//Vixen.Ajax.CallAppTemplate("Rate", "Add", objObjects);
		Vixen.Popup.ShowAjaxPopup("AddRatePopup", "large", "Rate", "Add", objObjects);
	}
}

// instanciate the objects
Vixen.RateGroupAdd = new VixenRateGroupAddClass;
