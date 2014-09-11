//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// provisioning_history.js
//----------------------------------------------------------------------------//
/**
 * provisioning_history
 *
 * javascript required of the provisioning_history functionality
 *
 * javascript required of the provisioning_history functionality
 * 
 *
 * @file		provisioning_history.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenProvisioningHistoryClass
//----------------------------------------------------------------------------//
/**
 * VixenProvisioningHistoryClass
 *
 * Encapsulates all event handling required of the provisioning history list Html Template
 *
 * Encapsulates all event handling required of the provisioning history list Html Template
 * 
 *
 * @package	ui_app
 * @class	VixenProvisioningHistoryClass
 * 
 */
function VixenProvisioningHistoryClass()
{
	this.intAccountId		= null;
	this.intServiceId		= null;
	this.intCategoryFilter	= null;
	this.intTypeFilter		= null;
	this.intMaxItems		= null;
	this.strContainerDivId	= null;
	this.bolUpdateCookies	= null;
	this.strPopupId			= null;

	this.Initialise = function(intAccountId, intServiceId, intCategoryFilter, intTypeFilter, intMaxItems, strHistoryContainerDivId, bolUpdateCookies, strPopupId)
	{
		this.intAccountId		= intAccountId;
		this.intServiceId		= intServiceId;
		this.intCategoryFilter	= intCategoryFilter;
		this.intTypeFilter		= intTypeFilter;
		this.intMaxItems		= intMaxItems;
		this.strContainerDivId	= strHistoryContainerDivId;
		this.bolUpdateCookies	= bolUpdateCookies;
		
		if (strPopupId != undefined)
		{
			this.strPopupId = strPopupId;
		}

		Vixen.EventHandler.AddListener("OnProvisioningRequestSubmission", this.OnProvisioningHistoryUpdate, this);
		Vixen.EventHandler.AddListener("OnProvisioningRequestCancellation", this.OnProvisioningHistoryUpdate, this);
	}
	
	//------------------------------------------------------------------------//
	// ApplyFilter
	//------------------------------------------------------------------------//
	/**
	 * ApplyFilter
	 *
	 * Retrieves the Provisioning history records based on the current filter options
	 *  
	 * Retrieves the Provisioning history records based on the current filter options
	 * 
	 * @param	bolShowSplash		optional, set to true if you want to show the 
	 *								page loading splash
	 *
	 * @return	void
	 * @method
	 */
	this.ApplyFilter = function(bolShowSplash)
	{
		this.ReloadList(bolShowSplash);
	}
	
	this.ReloadList = function(bolShowSplash)
	{
		// Set up Properties to be sent to AppTemplateProvisioning->RenderHistoryList
		var objObjects 			= {};
		objObjects.Account 		= {};
		objObjects.Account.Id 	= this.intAccountId;
		objObjects.Service 		= {};
		objObjects.Service.Id 	= this.intServiceId;
		objObjects.History 		= {};
		objObjects.History.CategoryFilter	= this.intCategoryFilter;
		objObjects.History.TypeFilter		= this.intTypeFilter;
		objObjects.History.MaxItems			= this.intMaxItems;
		objObjects.History.UpdateCookies	= this.bolUpdateCookies;
		objObjects.History.ContainerDivId	= this.strContainerDivId;
		
		// Work out the name of the current object
		for (var strMember in Vixen)
		{
			if (Vixen[strMember] == this)
			{
				objObjects.History.JsObjectName = strMember;
				break;
			}
		}
		
		
		Vixen.Ajax.CallAppTemplate("Provisioning", "RenderHistoryList", objObjects, null, bolShowSplash);
	}

	//------------------------------------------------------------------------//
	// CancelProvisioningRequest
	//------------------------------------------------------------------------//
	/**
	 * CancelProvisioningRequest()
	 *
	 * Compiles the javascript to be executed when the CancelProvisioningRequest action is triggered
	 *
	 * Compiles the javascript to be executed when the CancelProvisioningRequest action is triggered
	 * 
	 * @param	int		intId			id of the ProvisioningRequest record which will be cancelled
	 * @param	bool	bolConfirmed	optional, if set to true, the user will not be prompted before cancellation.
	 *									if not used, the user will be prompted for confirmation
	 *
	 * @return	void
	 *
	 * @method
	 */
	this.CancelProvisioningRequest = function(intId, bolConfirmed)
	{
		// Check if the action has not been confirmed yet
		if (bolConfirmed == null)
		{
			var strMsg = "Are you sure you want to cancel this provisioning request?";
			var objThis = this;
			Vixen.Popup.Confirm(strMsg, function(){objThis.CancelProvisioningRequest(intId, true);});
			return;
		}

		// Organise the data to send
		var objObjects						= {};
		objObjects.ProvisioningRequest		= {};
		objObjects.ProvisioningRequest.Id	= intId;

		// Call the AppTemplate method which handles canceling requests
		Vixen.Ajax.CallAppTemplate("Provisioning", "CancelRequest", objObjects, null, true, true);		
	}

	// Listener for when the provisioning history has been updated
	this.OnProvisioningHistoryUpdate = function(objEvent, objThis)
	{
		// Check that if the list is being displayed in a popup, the popup is still open
		if (objThis.strPopupId && (!Vixen.Popup.Exists(objThis.strPopupId)))
		{
			// The popup doesn't exist anymore so don't do anything
			// But first remove the listeners as they are no longer required
			Vixen.EventHandler.RemoveListener("OnProvisioningRequestSubmission", objThis.OnProvisioningHistoryUpdate);
			Vixen.EventHandler.RemoveListener("OnProvisioningRequestCancellation", objThis.OnProvisioningHistoryUpdate);

			//TODO! And you should probably destroy the object (but I'm currently within the object?)
			return true;
		}
		
		objThis.ReloadList();
	}
}
