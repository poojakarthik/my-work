//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_extra_details_inbound.js
//----------------------------------------------------------------------------//
/**
 * service_extra_details_inbound
 *
 * javascript required of the service_extra_details_inbound (Service, Bulk Add)functionality
 *
 * javascript required of the service_extra_details_inbound (Service, Bulk Add)functionality
 * 
 *
 * @file		service_extra_details_inbound.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceExtraDetailsInboundClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceExtraDetailsInboundClass
 *
 * Encapsulates all event handling required of the Inbound Details popup for the Service Bulk Add webpage
 *
 * Encapsulates all event handling required of the Inbound Details popup for the Service Bulk Add webpage
 * It is assumed that the service_bulk_add.js file has already been executed
 * 
 *
 * @package	ui_app
 * @class	VixenServiceExtraDetailsInboundClass
 * 
 */
function VixenServiceExtraDetailsInboundClass()
{
	this.objInputElements	= {};
	this.objService			= null;
	this.elmPrevious		= null;
	this.elmNext			= null;
	this.elmTitleFnn		= null;
	
	this.Initialise = function()
	{
		// Store a reference to each input element on the form
		var elmForm = document.getElementById("VixenForm_Inbound");

		var strElementId = null;
		for (var intKey=0; intKey < elmForm.elements.length; intKey++)
		{
			strElementId = elmForm.elements[intKey].id;

			if (strElementId == undefined)
			{
				continue;
			}
			if (strElementId.substr(0, 21) == "ServiceInboundDetail.")
			{
				var strElement = strElementId.substr(21);
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
		Vixen.ServiceBulkAdd.CloseAllPopups(Vixen.Constants.SERVICE_TYPE_INBOUND);
		this.objInputElements.AnswerPoint.elmControl.focus();
	}
	
	// Used to load the details of a new service, into the popup
	// objService is of class ServiceInputComponent
	this.LoadService = function(objService)
	{
		this.objService = objService;
		var strPopupId = Vixen.ServiceBulkAdd.objServiceTypeDetails[Vixen.Constants.SERVICE_TYPE_INBOUND].strPopupId;
		var strTitle = "Extra Details - Inbound 1300/1800 - " + objService.elmFnn.value;
	
		// Check if the inbound popup is already on the screen
		if (Vixen.Popup.Exists(strPopupId))
		{
			// The popup is already on the screen
			// Display the details of the new service
			if (objService.objExtraDetails == null)
			{
				// This service does not currently have details defined for it
				// clear the details of the previous service displayed as there
				// are no common details that can be carried accross
				this.objInputElements.AnswerPoint.elmControl.value		= "";
				this.objInputElements.Configuration.elmControl.value	= "";
				this.objInputElements.AnswerPoint.elmControl.focus();
			}
			else
			{
				// This service already has details defined for it
				this.objInputElements.AnswerPoint.elmControl.value		= objService.objExtraDetails.strAnswerPoint;
				this.objInputElements.Configuration.elmControl.value	= objService.objExtraDetails.strConfiguration;
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
			// The inbound popup is not currently on the screen
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
		this.objService.objExtraDetails.strAnswerPoint = this.objInputElements.AnswerPoint.elmControl.value;
		this.objService.objExtraDetails.strConfiguration = this.objInputElements.Configuration.elmControl.value;
	}
	
	this.Previous = function()
	{
		Vixen.ServiceBulkAdd.GetExtraDetailsForNextService(this.objService.intArrayIndex, true);
	}
	
	// Include highlighting of invalid fields, and reporting to the user
	this.ValidateForm = function()
	{
		// There isn't any validation required of the extra details for services of type Inbound
		return true;
	}
	
}


if (Vixen.ServiceBulkAdd.Inbound == undefined)
{
	Vixen.ServiceBulkAdd.Inbound = new VixenServiceExtraDetailsInboundClass;
}
