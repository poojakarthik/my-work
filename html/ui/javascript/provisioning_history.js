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
// VixenProvisioningHistoryListClass
//----------------------------------------------------------------------------//
/**
 * VixenProvisioningHistoryListClass
 *
 * Encapsulates all event handling required of the provisioning history list Html Template
 *
 * Encapsulates all event handling required of the provisioning history list Html Template
 * 
 *
 * @package	ui_app
 * @class	VixenProvisioningHistoryListClass
 * 
 */
function VixenProvisioningHistoryListClass()
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
		
		Vixen.Ajax.CallAppTemplate("Provisioning", "RenderHistoryList", objObjects, null, bolShowSplash);
	}

	// Listener for when the provisioning history has been updated
	this.OnProvisioningHistoryUpdate = function(objEvent, objThis)
	{
		// Check if this list is being displayed in a popup
		if (objThis.strPopupId != null)
		{
			// Check if the popup is open
			if (!Vixen.Popup.Exists(objThis.strPopupId))
			{
				// The popup doesn't exist so don't do anything
				// But first remove the listener as it is no longer required
				Vixen.EventHandler.RemoveListener("OnProvisioningRequestSubmission", objThis.OnProvisioningHistoryUpdate);
				Vixen.EventHandler.RemoveListener("OnProvisioningRequestCancellation", objThis.OnProvisioningHistoryUpdate);
				
				//TODO! And you should probably destroy the object (but I'm currently within the object?)
				return true;
			}
		}
		
		objThis.ReloadList();
	}
}
