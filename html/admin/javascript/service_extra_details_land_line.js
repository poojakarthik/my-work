//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_extra_details_land_line.js
//----------------------------------------------------------------------------//
/**
 * service_extra_details_land_line
 *
 * javascript required of the service_extra_details_land_line (Service, Bulk Add)functionality
 *
 * javascript required of the service_extra_details_land_line (Service, Bulk Add)functionality
 * 
 *
 * @file		service_extra_details_land_line.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceExtraDetailsLandLineClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceExtraDetailsLandLineClass
 *
 * Encapsulates all event handling required of the LandLine Details popup for the Service Bulk Add webpage
 *
 * Encapsulates all event handling required of the LandLine Details popup for the Service Bulk Add webpage
 * It is assumed that the service_bulk_add.js file has already been executed
 * 
 *
 * @package	ui_app
 * @class	VixenServiceExtraDetailsLandLineClass
 * 
 */
function VixenServiceExtraDetailsLandLineClass()
{
	this.objInputElements		= {};
	this.objService				= null;
	this.elmELBContainer		= null;
	this.elmTitleFnn			= null;
	
	// This is a reference to the Vixen.ServiceAddress object
	this.objServiceAddress		= null;
	
	this.Initialise = function()
	{
		// Store a reference to each input element on the form
		var elmForm = $ID("VixenForm_LandLine");

		var strElementId = null;
		for (intKey in elmForm.elements)
		{
			strElementId = elmForm.elements[intKey].id;

			if (strElementId == undefined)
			{
				continue;
			}
			if (strElementId.substr(0, 8) == "Service.")
			{
				var strElement = strElementId.substr(8);
				this.objInputElements[strElement] = {};
				this.objInputElements[strElement].elmControl = elmForm.elements[intKey];
			}
		}
		
		// Store a reference to the ELB container div
		this.elmELBContainer = $ID("Container_ELB");
		
		// Store a reference to the place holder in the title, for the FNN
		this.elmTitleFnn = $ID("ExtraDetailTitleFnn");
		
		// Store a reference to the js object that manages the Service Address HtmlTemplate
		this.objServiceAddress = Vixen.ServiceAddress;
		
		// Set up the EventListener for the Indial100 checkbox
		this.objInputElements.Indial100.elmControl.addEventListener('change', this.Indial100OnChange.bind(this), TRUE);
		
		// Close all the other "ExtraDetail"  popups except for this one
		Vixen.ServiceBulkAdd.CloseAllPopups($Const("SERVICE_TYPE_LAND_LINE"));
		this.objInputElements.AuthorisationDate.elmControl.focus();
	}
	
	// Event Listener for when the Indial100 checkbox is changed
	this.Indial100OnChange = function(objEvent)
	{
		if (this.objInputElements.Indial100.elmControl.checked == true)
		{
			this.objInputElements.ELB.elmControl.checked	= false;
			this.elmELBContainer.style.visibility			= "visible";
		}
		else
		{
			this.objInputElements.ELB.elmControl.checked	= false;
			this.elmELBContainer.style.visibility			= "hidden";
		}
	}
	
	// Used to load the details of a new service, into the popup
	// objService is of class ServiceInputComponent
	this.LoadService = function(objService)
	{
		this.objService	= objService;
		var strPopupId	= Vixen.ServiceBulkAdd.objServiceTypeDetails[$Const("SERVICE_TYPE_LAND_LINE")].strPopupId;
		var strTitle	= "Extra Details - Land Line - " + objService.elmFnn.value;
	
		// Check if the landline popup is already on the screen
		if (Vixen.Popup.Exists(strPopupId))
		{
			// The popup is already on the screen
			// Display the details of the new service
			if (objService.objExtraDetails == null)
			{
				// This service does not currently have details defined for it
				// clear details of the previous service that should not be carried across
				this.objInputElements.Indial100.elmControl.checked = false;
				this.Indial100OnChange();
				
				this.objServiceAddress.SetAddressDetails();
				this.objInputElements.AuthorisationDate.elmControl.focus();
			}
			else
			{
				// This service already has details defined for it
				this.objInputElements.AuthorisationDate.elmControl.value	= objService.objExtraDetails.strAuthorisationDate;
				this.objInputElements.Indial100.elmControl.checked			= objService.objExtraDetails.bolIndial100;
				this.objInputElements.ELB.elmControl.checked				= objService.objExtraDetails.bolELB;
				this.Indial100OnChange();
				
				this.objServiceAddress.SetAddressDetails(this.objService.objExtraDetails.objAddressDetails);
			}
			
			// Unhighlight any of the fields that are currently highlighted
			for (strField in this.objInputElements)
			{
				this.objInputElements[strField].elmControl.SetHighlight(false);
			}
			
			// Update the title bar of the popup
			Vixen.Popup.SetTitle(strPopupId, strTitle);
			
			// Update the title
			this.elmTitleFnn.innerHTML = objService.elmFnn.value;
		}
		else
		{
			// The landline popup is not currently on the screen
			// Make a call to the server to render the popup
			var objObjects			= {};
			objObjects.Account		= {};
			objObjects.Account.Id	= Vixen.ServiceBulkAdd.intAccountId;
			
			objObjects.Service		= {};
			
			var objProperties = objService.GetProperties();
			for (strProperty in objProperties)
			{
				objObjects.Service[strProperty.substr(3)] = objProperties[strProperty];
			}
			
			Vixen.Popup.ShowPageLoadingSplash("Please wait");
			Vixen.Popup.ShowAjaxPopup(strPopupId, "ExtraLarge", strTitle, "Service", "LoadExtraDetailsPopup", objObjects, "modal");
		}
	}
	
	// Validates the current data, and if valid, saves the details and moves to the next service
	this.Next = function()
	{
		var bolSuccess = this.ValidateForm();
		
		if (bolSuccess)
		{
			this.SaveDetails();
			Vixen.ServiceBulkAdd.GetExtraDetailsForNextService(this.objService.intArrayIndex);
		}
	}
	
	// Saves the details to the Service object's objExtraDetails object
	this.SaveDetails = function()
	{
		if (this.objService.objExtraDetails == null)
		{
			this.objService.objExtraDetails = {};
		}
		
		this.objService.objExtraDetails.strAuthorisationDate	= this.objInputElements.AuthorisationDate.elmControl.value;
		this.objService.objExtraDetails.bolIndial100			= this.objInputElements.Indial100.elmControl.checked;
		this.objService.objExtraDetails.bolELB					= this.objInputElements.ELB.elmControl.checked;
		this.objService.objExtraDetails.objAddressDetails		= this.objServiceAddress.GetAddressDetails();
	}
	
	this.Previous = function()
	{
		Vixen.ServiceBulkAdd.GetExtraDetailsForNextService(this.objService.intArrayIndex, true);
	}
	
	// Include highlighting of invalid fields, and reporting to the user
	this.ValidateForm = function()
	{
		// Check that AuthorisationDate is valid
		this.objInputElements.AuthorisationDate.elmControl.SetHighlight(false);
		if (!this.objInputElements.AuthorisationDate.elmControl.Validate("ShortDate"))
		{
			// The AuthorisationDate is invalid
			this.objInputElements.AuthorisationDate.elmControl.SetHighlight(true);
			$Alert("ERROR: AuthorisationDate must be in the format dd/mm/yyyy");
			return false;
		}
		
		// Validate the Address Details
		return this.objServiceAddress.ValidateForm();
	}
}

if (Vixen.ServiceBulkAdd.LandLine == undefined)
{
	Vixen.ServiceBulkAdd.LandLine = new VixenServiceExtraDetailsLandLineClass;
}
