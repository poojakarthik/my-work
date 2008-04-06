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
			// Unhighlight everything
			this.objInputElements[strControl].elmControl.SetHighlight(false);
		
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
	
	//**************************************************************************
	// The following functionality is used specifically by the Bulk Add
	// Service page
	//**************************************************************************
	
	// Returns all the details on the form pertaining to the defined address
	// This should return a clean version of the details.  That being
	// if the user has specified Residential details, but then decided to make
	// it a Business Service, the residential details should be nulled
	// If any of these fields are not used in the database then they are nulled
	this.GetAddressDetails = function()
	{
		var objAddressDetails = {};
		
		for (strProperty in this.objInputElements)
		{
			// If any of the properties are empty strings then convert them to nulls
			objAddressDetails[strProperty] = (this.objInputElements[strProperty].elmControl.value != "")? this.objInputElements[strProperty].elmControl.value : null;
		}
		
		return objAddressDetails;
	}
	
	
	// objAddressDetails must have each member the same name as the member of the this.objInputElements that it relates to
	this.SetAddressDetails = function(objAddressDetails)
	{
		if (objAddressDetails == undefined)
		{
			// This service does not currently have address details defined for it
			// Clear the fields that should not carry over from the previously defined Address
			// (Nothing to do.  All values can carry across)
		}
		else
		{
			// This service has address details defined
			// Set up the form to reflect them
			for (strProperty in objAddressDetails)
			{
				this.objInputElements[strProperty].elmControl.value = objAddressDetails[strProperty];
			}
			
			// Set up the form's controls to reflect the details of the service
			this.SetServiceCategory(this.objInputElements.Residential.elmControl.value);
			this.UpdateServiceAddressControls();
		}
		
		$ID("AddressEdit.AddressCombo").value = 0;
	}
	
	
	// Initialises the ServiceAddress HtmlTemplate for use with the ServiceBulkAdd functionality
	this.InitialiseServiceBulkAdd = function(intAccountId, objPostalAddressTypes, objAccountAddresses, objAddressDetails)
	{
		this.intAccountId			= intAccountId;
		this.objPostalAddressTypes	= objPostalAddressTypes;
		this.objAccountAddresses	= objAccountAddresses;
		
		// Store references to the divs that can be hidden
		this.objContainerDivs.elmResidentialUserDetails	= $ID("Container.ResidentialUserDetails");
		this.objContainerDivs.elmBusinessUserDetails	= $ID("Container.BusinessUserDetails");
		
		// Store a reference to each input element on the form
		var elmForm = $ID("VixenForm_ServiceAddress");
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

		// Load in the currently specified address details
		for (strProperty in objAddressDetails)
		{
			if (this.objInputElements[strProperty] != undefined)
			{
				this.objInputElements[strProperty].elmControl.value = objAddressDetails[strProperty];
			}
		}

		// Load the Address Details of the current Service if they have already been specified
		this.SetServiceCategory(this.objInputElements.Residential.elmControl.value);
		this.UpdateServiceAddressControls();
	}


	this.ValidateForm = function()
	{
		// Load all the values into an object
		var objValues = {};
		for (strProperty in this.objInputElements)
		{
			objValues[strProperty] = this.objInputElements[strProperty].elmControl.value;
			
			// Unhighlight each control, if it is currently highlighted
			this.objInputElements[strProperty].elmControl.SetHighlight(false);
		}
	
		var bolAllValid = this.ValidateAndCleanServiceAddress()
	
		if (!bolAllValid)
		{
			$Alert("ERROR: Invalid fields are highlighted");
		}

		return bolAllValid;
	}

	// Returns TRUE if valid, else false
	// Any problems encountered will be recorded in the $arrProblems array
	// The $dboServiceAddress will also be cleaned of values that shouldn't be set
	this.ValidateAndCleanServiceAddress = function()
	{
		var bolAllValid = true;

		// Load all the values into an object, for easy reference
		var objValues = {};
		for (strProperty in this.objInputElements)
		{
			objValues[strProperty] = this.objInputElements[strProperty].elmControl.value.Trim();
		}
		
		// Convert to upper case, those values that should be in upper case
		objValues.ServiceAddressTypeSuffix	= (objValues.ServiceAddressTypeSuffix != null) ? objValues.ServiceAddressTypeSuffix.toUpperCase() : null;
		objValues.ServiceStreetNumberSuffix	= (objValues.ServiceStreetNumberSuffix != null) ? objValues.ServiceStreetNumberSuffix.toUpperCase() : null;
		
		// Handle the user details
		if (objValues.Residential == "1")
		{
			// It's a residential service
			if (!objValues.EndUserTitle.Validate("NotEmptyString"))
			{
				this.objInputElements.EndUserTitle.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
			if (!objValues.EndUserGivenName.Validate("NotEmptyString"))
			{
				this.objInputElements.EndUserGivenName.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
			if (!objValues.EndUserFamilyName.Validate("NotEmptyString"))
			{
				this.objInputElements.EndUserFamilyName.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
			if (!objValues.DateOfBirth.Validate("ShortDate"))
			{
				this.objInputElements.DateOfBirth.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
			
			// Clear the "business" specific fields
			objValues.ABN					= null;
			objValues.EndUserCompanyName	= null;
			objValues.TradingName			= null;
		}
		else
		{
			// It's a business service
			if (!objValues.ABN.Validate("ABN"))
			{
				this.objInputElements.ABN.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
			if (!objValues.EndUserCompanyName.Validate("NotEmptyString"))
			{
				this.objInputElements.EndUserCompanyName.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
			
			// Clear the "residential" specific fields
			objValues.EndUserTitle		= null;
			objValues.EndUserGivenName	= null;
			objValues.EndUserFamilyName	= null;
			objValues.DateOfBirth		= null;
			objValues.Employer			= null;
			objValues.Occupation		= null;
		}
		
		// Check the Billing Address fields
		if (!objValues.BillName.Validate("NotEmptyString"))
		{
			this.objInputElements.BillName.elmControl.SetHighlight(true);
			bolAllValid = false;
		}
		if (!objValues.BillAddress1.Validate("NotEmptyString"))
		{
			this.objInputElements.BillAddress1.elmControl.SetHighlight(true);
			bolAllValid = false;
		}
		if (!objValues.BillLocality.Validate("NotEmptyString"))
		{
			this.objInputElements.BillLocality.elmControl.SetHighlight(true);
			bolAllValid = false;
		}
		if (!objValues.BillPostcode.Validate("PostCode"))
		{
			this.objInputElements.BillPostcode.elmControl.SetHighlight(true);
			bolAllValid = false;
		}
		
		// Validate the service's physical address
		var bolHasAddressType = objValues.ServiceAddressType.Validate("NotEmptyString");
		if (bolHasAddressType)
		{
			// An Address Type has been specified
			if (!objValues.ServiceAddressTypeNumber.Validate("PositiveIntegerNonZero"))
			{
				this.objInputElements.ServiceAddressTypeNumber.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
			if (!objValues.ServiceAddressTypeSuffix.Validate("LettersOnly", true))
			{
				this.objInputElements.ServiceAddressTypeSuffix.elmControl.SetHighlight(true);
				bolAllValid = false;
			}
		}
		else
		{
			// No address type has been specified
			objValues.ServiceAddressType		= null;
			objValues.ServiceAddressTypeNumber	= null;
			objValues.ServiceAddressTypeSuffix	= null;
		}


		if (bolHasAddressType && this.objPostalAddressTypes[objValues.ServiceAddressType] != undefined)
		{
			// ServiceAddressType is a postal address
			// null the fields that aren't used for postal addresses
			objValues.ServiceStreetNumberStart	= null;
			objValues.ServiceStreetNumberEnd	= null;
			objValues.ServiceStreetNumberSuffix	= null;
			objValues.ServiceStreetName			= null;
			objValues.ServiceStreetType			= "NR";
			objValues.ServiceStreetTypeSuffix	= null;
			objValues.ServicePropertyName		= null;
		}
		else
		{
			// ServiceAddressType is not a postal address type, and can therefore have street details
			if (objValues.ServiceAddressType == "LOT")
			{
				// LOTs do not have Street numbers
				objValues.ServiceStreetNumberStart	= null;
				objValues.ServiceStreetNumberEnd	= null;
				objValues.ServiceStreetNumberSuffix	= null;
			}
			else
			{
				// Validate the StreetNumber details, but only if a StreetName has been specified
				if (objValues.ServiceStreetName == "")
				{
					// StreetName has not been declared
					objValues.ServiceStreetNumberStart	= null;
					objValues.ServiceStreetNumberEnd	= null;
					objValues.ServiceStreetNumberSuffix	= null;
				}
				else
				{
					// StreetName has been specified
					// Validate the Street Number
					if (objValues.ServiceStreetNumberStart == "")
					{
						// Street Number Start has not been specified, but should be
						this.objInputElements.ServiceStreetNumberStart.elmControl.SetHighlight(true);
						bolAllValid = false;
					}
					else
					{
						// StreetNumberStart has been declared
						if (!objValues.ServiceStreetNumberStart.Validate("PositiveIntegerNonZero"))
						{
							this.objInputElements.ServiceStreetNumberStart.elmControl.SetHighlight(true);
							bolAllValid = false;
						}
						
						if (objValues.ServiceStreetNumberEnd != "")
						{
							// An end number has been declared
							if (!objValues.ServiceStreetNumberEnd.Validate("PositiveIntegerNonZero"))
							{
								this.objInputElements.ServiceStreetNumberEnd.elmControl.SetHighlight(true);
								bolAllValid = false;
							}
							else if (parseInt(objValues.ServiceStreetNumberEnd) <= parseInt(objValues.ServiceStreetNumberStart))
							{
								// The end number is less than or equal to the start number
								this.objInputElements.ServiceStreetNumberEnd.elmControl.SetHighlight(true);
								bolAllValid = false;
							}
						}
	
						if (!objValues.ServiceStreetNumberSuffix.Validate("LettersOnly", true))
						{
							// A suffix has been specified but is invalid
							this.objInputElements.ServiceStreetNumberSuffix.elmControl.SetHighlight(true);
							bolAllValid = false;
						}
					}
				}
			}
			
			if (objValues.ServiceStreetName != "")
			{
				// A street name has been declared
				// You don't need to test the ServiceStreetType as it is always valid
				if (objValues.ServiceStreetType == "NR")
				{
					// Suffix is not required
					objValues.ServiceStreetTypeSuffix = null;
				}
			}
			else
			{
				// A street name has not been declared
				objValues.ServiceStreetType			= "NR";
				objValues.ServiceStreetTypeSuffix	= null;
				
				// Check that a Property Name has been declared
				if (objValues.ServicePropertyName == "")
				{
					this.objInputElements.ServiceStreetName.elmControl.SetHighlight(true);
					this.objInputElements.ServicePropertyName.elmControl.SetHighlight(true);
					bolAllValid = false;
				}
			}
		}
		
		if (!objValues.ServiceLocality.Validate("NotEmptyString"))
		{
			this.objInputElements.ServiceLocality.elmControl.SetHighlight(true);
			bolAllValid = false;
		}
		if (!objValues.ServiceState.Validate("NotEmptyString"))
		{
			this.objInputElements.ServiceState.elmControl.SetHighlight(true);
			bolAllValid = false;
		}
		if (!objValues.ServicePostcode.Validate("PostCode"))
		{
			this.objInputElements.ServicePostcode.elmControl.SetHighlight(true);
			bolAllValid = false;
		}
		
		// Save the cleaned fields back into the form, but only if everything was valid
		if (bolAllValid)
		{
			for (strProperty in objValues)
			{
				this.objInputElements[strProperty].elmControl.value = objValues[strProperty];
			}
		}
		
		
		return bolAllValid;
	}
}

if (Vixen.ServiceAddress == undefined)
{
	Vixen.ServiceAddress = new VixenServiceAddressClass;
}
