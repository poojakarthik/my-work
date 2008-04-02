//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_address.js
//----------------------------------------------------------------------------//
/**
 * service_address
 *
 * javascript required of the service_address functionality
 *
 * javascript required of the service_address functionality
 * 
 *
 * @file		service_address.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceAddressClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceAddressClass
 *
 * Encapsulates all event handling required of the Service Address Edit Html Template
 *
 * Encapsulates all event handling required of the Service Address Edit Html Template
 * 
 *
 * @package	ui_app
 * @class	VixenServiceAddressClass
 * 
 */
function VixenServiceAddressClass()
{
	this.intAccountId			= null;
	this.intServiceId			= null;
	this.strContainerDivId		= null;
	this.objAccountAddresses	= null;
	this.objPostalAddressTypes	= null;
	this.strPopupId				= null;
	this.objInputElements		= {};
	this.objContainerDivs		= {};

	this.InitialiseEdit = function(intAccountId, intServiceId, strContainerDivId, objPostalAddressTypes, objAccountAddresses, strPopupId)
	{
		this.intAccountId					= intAccountId;
		this.intServiceId					= intServiceId;
		this.strContainerDivId				= strContainerDivId;
		this.objPostalAddressTypes			= objPostalAddressTypes;
		this.objAccountAddresses			= objAccountAddresses;
		this.strPopupId						= strPopupId;
		
		// Store references to the divs that can be hidden
		this.objContainerDivs.elmResidentialUserDetails	= document.getElementById("Container.ResidentialUserDetails");
		this.objContainerDivs.elmBusinessUserDetails	= document.getElementById("Container.BusinessUserDetails");
		
		// Store a reference to each input element on the form
		var elmForm = document.getElementById("VixenForm_ServiceAddress");
		var strElementId;
		for (intKey in elmForm.elements)
		{
			strElementId = elmForm.elements[intKey].id;

			if (strElementId == null)
			{
				continue;
			}
			if (strElementId.substr(0, 15) == "ServiceAddress.")
			{
				strElement = strElementId.substr(15);
				this.objInputElements[strElement] = {};
				this.objInputElements[strElement].elmControl = elmForm.elements[intKey];
				
				if (strElement.length > 7 && strElement.substr(0, 7) == "Service")
				{
					// This is a control relating to the physical service address and has a "Required" flag which can be manipulated
					this.objInputElements[strElement].elmRequired = document.getElementById(strElementId + ".Required");
					this.objInputElements[strElement].bolPartOfServiceAddress = true;
				}
			}
		}
		
		// Load the Address Details of the current Service if one has been specified
		this.LoadAddress(this.intServiceId);
	}
	
	this.SetServiceCategory = function(intCategory)
	{
		if (intCategory == 0)
		{
			// The service is a business service
			this.objContainerDivs.elmResidentialUserDetails.style.display = "none";
			this.objContainerDivs.elmBusinessUserDetails.style.display = "block";
		}
		else
		{
			// The service is a residential
			this.objContainerDivs.elmBusinessUserDetails.style.display = "none";
			this.objContainerDivs.elmResidentialUserDetails.style.display = "block";
		}
	}
	
	this.LoadAddress = function(intServiceId)
	{
		if (intServiceId == 0)
		{
			// The blank option has been chosen
			return;
		}
	
		var objDetails = this.objAccountAddresses[intServiceId];
		
		if (objDetails != undefined)
		{
			// The service address record already exists
			this.SetServiceCategory(objDetails.Residential);
			
			for (strProperty in this.objInputElements)
			{
				if (this.objInputElements[strProperty] != undefined)
				{
					this.objInputElements[strProperty].elmControl.value = objDetails[strProperty];
				}
			}
		}
		else
		{
			// The service address record does not yet exist
			this.SetServiceCategory(0);
		}
		
		this.UpdateServiceAddressControls();
	}

	// Event handler for when the ServiceAddressType control has been edited
	this.UpdateServiceAddressControls = function(bolRetainFocus)
	{
		var strAddressType = this.objInputElements.ServiceAddressType.elmControl.value;
		
		var arrControlsNotAllowed;
		var arrRequiredControls;
		
		if (strAddressType == "")
		{
			// There is no address type
			arrControlsNotAllowed	= new Array("ServiceAddressTypeNumber", "ServiceAddressTypeSuffix");
			arrRequiredControls		= new Array("ServiceStreetNumberStart", "ServiceStreetName", "ServicePropertyName");
		}
		else if (strAddressType == "LOT")
		{
			// The address type is a "LOT"
			arrControlsNotAllowed	= new Array("ServiceStreetNumberStart", "ServiceStreetNumberEnd", "ServiceStreetNumberSuffix");
			arrRequiredControls		= new Array("ServiceAddressTypeNumber", "ServiceStreetName", "ServicePropertyName");
		}
		else if (this.objPostalAddressTypes[strAddressType] != undefined)
		{
			// Postal address type
			arrControlsNotAllowed	= new Array("ServiceStreetNumberStart", "ServiceStreetNumberEnd", "ServiceStreetNumberSuffix", "ServiceStreetName", "ServiceStreetType", "ServiceStreetTypeSuffix", "ServicePropertyName");
			arrRequiredControls		= new Array("ServiceAddressTypeNumber");
		}
		else
		{
			// Normal address type
			arrControlsNotAllowed	= new Array();
			arrRequiredControls		= new Array("ServiceAddressTypeNumber", "ServiceStreetNumberStart", "ServiceStreetName", "ServicePropertyName");
		}
		
		arrRequiredControls.push("ServiceLocality");
		arrRequiredControls.push("ServiceState");
		arrRequiredControls.push("ServicePostcode");
		
		for (strControl in this.objInputElements)
		{
			// Only manipulate the controls that make up the Physical Service Address
			if (this.objInputElements[strControl].bolPartOfServiceAddress)
			{
				this.SetDisabled(strControl, false);
				this.SetRequired(strControl, false);
			}
		}
		for (i in arrControlsNotAllowed)
		{
			this.SetDisabled(arrControlsNotAllowed[i], true);
		}
		for (i in arrRequiredControls)
		{
			this.SetRequired(arrRequiredControls[i], true);
		}
		
		this.UpdateStreetNumberStartControl();
		this.UpdateStreetNameControl();
		this.UpdatePropertyNameControl();
		
		
		if (bolRetainFocus)
		{
			this.objInputElements.ServiceAddressType.elmControl.focus();
		}
	}
	
	// Event handler for where the ServiceStreetNumberStart control has been edited
	this.UpdateStreetNumberStartControl = function()
	{
		if (this.objInputElements.ServiceStreetNumberStart.elmControl.disabled)
		{
			// Don't update anything if the control is disabled
			return;
		}
		
		if (this.objInputElements.ServiceStreetNumberStart.elmControl.value == "")
		{
			// No street number has been specified
			this.SetDisabled("ServiceStreetNumberEnd", true);
			this.SetDisabled("ServiceStreetNumberSuffix", true);
		}
		else
		{
			// A street number has been specified
			this.SetDisabled("ServiceStreetNumberEnd", false);
			this.SetDisabled("ServiceStreetNumberSuffix", false);
		}
	}
	
	// Event handler for where the ServiceStreetName control has been edited
	this.UpdateStreetNameControl = function()
	{
		if (this.objInputElements.ServiceStreetName.elmControl.disabled)
		{
			// Don't update anything if the control is disabled
			return;
		}
		
		if (this.objInputElements.ServiceStreetName.elmControl.value == "")
		{
			// No street name has been specified
			this.SetDisabled("ServiceStreetType", true);
			this.SetDisabled("ServiceStreetTypeSuffix", true);
			this.SetRequired("ServicePropertyName", true);
		}
		else
		{
			// A street name has been specified
			this.SetDisabled("ServiceStreetType", false);
			this.SetDisabled("ServiceStreetTypeSuffix", false);
			this.SetRequired("ServicePropertyName", false);
		}
	}
	
	// Event handler for where the ServicePropertyName control has been edited
	this.UpdatePropertyNameControl = function()
	{
		if (this.objInputElements.ServicePropertyName.elmControl.disabled)
		{
			// Don't update anything if the control is disabled
			return;
		}
		
		if (this.objInputElements.ServicePropertyName.elmControl.value == "")
		{
			this.SetRequired("ServiceStreetName", true);
			if (this.objInputElements.ServiceStreetNumberStart.elmControl.disabled != true)
			{
				this.SetRequired("ServiceStreetNumberStart", true);
			}
		}
		else
		{
			this.SetRequired("ServiceStreetName", false);
			this.SetRequired("ServiceStreetNumberStart", false);
		}
	}
	
	// Used to visibly flag a control as being required or not
	this.SetRequired = function(strControlName, bolRequired)
	{

		if (this.objInputElements[strControlName] != undefined)
		{
			this.objInputElements[strControlName].elmRequired.style.visibility = (bolRequired)? "visible" : "hidden";
		}
	}
	
	// Used to disable/enable a control
	this.SetDisabled = function(strControlName, bolDisable)
	{
		if (this.objInputElements[strControlName] != undefined)
		{
			this.objInputElements[strControlName].elmControl.disabled = bolDisable;
		}
	}
	
	// Saves the Service Address Details via an ajax request
	this.SaveAddress = function()
	{
		// Check that the form has not already been submitted and is waiting for a reply
		if (Vixen.Ajax.strFormCurrentlyProcessing != null)
		{
			Vixen.Popup.Alert("WARNING: A form is currently processing.  Please wait until it has finished before making subsequent submissions", null, "WarningId");
			return;
		}

		var objObjects = {};
		
		objObjects.Service			= {};
		objObjects.Service.Id		= this.intServiceId;
		objObjects.ContainerDiv		= {};
		objObjects.ContainerDiv.Id	= this.strContainerDivId;
		objObjects.Popup			= {};
		objObjects.Popup.Id			= this.strPopupId;
		objObjects.ServiceAddress	= {};
		
		for (strProperty in this.objInputElements)
		{
			objObjects.ServiceAddress[strProperty] = this.objInputElements[strProperty].elmControl.value;
		}
		
		// Call the AppTemplate method that handles saving a ServiceAddress record
		Vixen.Ajax.CallAppTemplate("Service", "SaveAddress", objObjects, null, true, true);		
	}
	
}

if (Vixen.ServiceAddress == undefined)
{
	Vixen.ServiceAddress = new VixenServiceAddressClass;
}
