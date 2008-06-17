//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_extra_details_mobile.js
//----------------------------------------------------------------------------//
/**
 * service_extra_details_mobile
 *
 * javascript required of the service_extra_details_mobile (Service, Bulk Add)functionality
 *
 * javascript required of the service_extra_details_mobile (Service, Bulk Add)functionality
 * 
 *
 * @file		service_extra_details_mobile.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceExtraDetailsMobileClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceExtraDetailsMobileClass
 *
 * Encapsulates all event handling required of the Mobile Details popup for the Service Bulk Add webpage
 *
 * Encapsulates all event handling required of the Mobile Details popup for the Service Bulk Add webpage
 * It is assumed that the service_bulk_add.js file has already been executed
 * 
 *
 * @package	ui_app
 * @class	VixenServiceExtraDetailsMobileClass
 * 
 */
function VixenServiceExtraDetailsMobileClass()
{
	this.objInputElements	= {};
	this.objService			= null;
	this.elmPrevious		= null;
	this.elmNext			= null;
	this.elmTitleFnn		= null;
	
	this.Initialise = function()
	{
		// Store a reference to each input element on the form
		var elmForm = document.getElementById("VixenForm_Mobile");

		var strElementId = null;
		for (intKey in elmForm.elements)
		{
			strElementId = elmForm.elements[intKey].id;

			if (strElementId == undefined)
			{
				continue;
			}
			if (strElementId.substr(0, 20) == "ServiceMobileDetail.")
			{
				var strElement = strElementId.substr(20);
				this.objInputElements[strElement] = {};
				this.objInputElements[strElement].elmControl = elmForm.elements[intKey];
			}
			else if (strElementId == "VixenButton_Back")
			{
				this.elmPrevious = elmForm.elements[intKey];
			}
			else if (strElementId == "VixenButton_Save")
			{
				this.elmNext = elmForm.elements[intKey];
			}
		}

		// Store a reference to the place holder in the title, for the FNN
		this.elmTitleFnn = $ID("ExtraDetailTitleFnn");
		
		// Close all the other "ExtraDetail"  popups except for this one
		Vixen.ServiceBulkAdd.CloseAllPopups($Const("SERVICE_TYPE_MOBILE"));
		this.objInputElements.SimPUK.elmControl.focus();
	}
	
	// Used to load the details of a new service, into the popup
	// objService is of class ServiceInputComponent
	this.LoadService = function(objService)
	{
		this.objService	= objService;
		var strPopupId	= Vixen.ServiceBulkAdd.objServiceTypeDetails[$Const("SERVICE_TYPE_MOBILE")].strPopupId;
		var strTitle	= "Extra Details - Mobile - " + objService.elmFnn.value;
	
		// Check if the mobile popup is already on the screen
		if (Vixen.Popup.Exists(strPopupId))
		{
			// The popup is already on the screen
			// Display the details of the new service
			if (objService.objExtraDetails == null)
			{
				// This service does not currently have details defined for it
				// clear details of the previous service that should not be carried across
				this.objInputElements.SimPUK.elmControl.value = "";
				this.objInputElements.SimESN.elmControl.value = "";
				
				this.objInputElements.SimPUK.elmControl.focus();
			}
			else
			{
				// This service already has details defined for it
				this.objInputElements.SimPUK.elmControl.value	= objService.objExtraDetails.strSimPUK;
				this.objInputElements.SimESN.elmControl.value	= objService.objExtraDetails.strSimESN;
				this.objInputElements.SimState.elmControl.value	= objService.objExtraDetails.strSimState;
				this.objInputElements.DOB.elmControl.value		= objService.objExtraDetails.strDOB;
				this.objInputElements.Comments.elmControl.value	= objService.objExtraDetails.strComments;
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
			// The mobile popup is not currently on the screen
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
			Vixen.Popup.ShowAjaxPopup(strPopupId, "Medium", strTitle, "Service", "LoadExtraDetailsPopup", objObjects, "modal");
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
		this.objService.objExtraDetails.strSimPUK	= this.objInputElements.SimPUK.elmControl.value;
		this.objService.objExtraDetails.strSimESN	= this.objInputElements.SimESN.elmControl.value;
		this.objService.objExtraDetails.strSimState	= this.objInputElements.SimState.elmControl.value;
		this.objService.objExtraDetails.strDOB		= this.objInputElements.DOB.elmControl.value;
		this.objService.objExtraDetails.strComments	= this.objInputElements.Comments.elmControl.value;
	}
	
	this.Previous = function()
	{
		Vixen.ServiceBulkAdd.GetExtraDetailsForNextService(this.objService.intArrayIndex, true);
	}
	
	// Include highlighting of invalid fields, and reporting to the user
	this.ValidateForm = function()
	{
		// Validate the Date of birth
		if (!this.objInputElements.DOB.elmControl.Validate("ShortDate", true))
		{
			// The Date Of Birth is invalid
			this.objInputElements.DOB.elmControl.SetHighlight(true);
			$Alert("ERROR: DOB must be in the format dd/mm/yyyy");
			return false;
		}
		return true;
	}
}

if (Vixen.ServiceBulkAdd.Mobile == undefined)
{
	Vixen.ServiceBulkAdd.Mobile = new VixenServiceExtraDetailsMobileClass;
}
