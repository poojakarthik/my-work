//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_services.js
//----------------------------------------------------------------------------//
/**
 * account_services
 *
 * javascript required of the "Account Services" popup webpage
 *
 * javascript required of the "Account Services" popup webpage
 * This page doesn't really do much, however it needs Event listeners defined
 * for the various things that can be updated on it
 * 
 *
 * @file		account_services.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenAccountServicesClass
//----------------------------------------------------------------------------//
/**
 * VixenAccountServicesClass
 *
 * Encapsulates all event handling required of the "Account Services" popup webpage
 *
 * Encapsulates all event handling required of the "Account Services" popup webpage
 * 
 *
 * @package	ui_app
 * @class	VixenAccountServicesClass
 * 
 */
function VixenAccountServicesClass()
{
	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the object - registers event listeners
	 *  
	 * Initialises the object - registers event listeners
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function()
	{
		if (Vixen.EventHandler == undefined)
		{
			// The EventHandler hasn't been loaded yet
			// Try again in half a second
			setTimeout(Vixen.AccountServices.Initialise, 500);
			return;
		}
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnServicePlanChange", this.OnUpdate);
		Vixen.EventHandler.AddListener("OnServiceUpdate", this.OnUpdate);
	}

	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when something on the Account Services popup has been updated and the entire list of services should be redrawn
	 *  
	 * Event handler for when something on the Account Services popup has been updated and the entire list of services should be redrawn
	 *
	 * @param	object	objEvent		objEvent.Data.Service.Id should be set.  objEvent.Data.NewService.Id is optional
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent)
	{
		// I should check that the AccountServices popup is actually open because this will stay in 
		// memory after the popup is closed, and if something else then triggers the event, 
		// who knows what would happen
		
		if (objEvent.Data.NewService != undefined)
		{
			// Editing the service required a new service record to be created.
			// This should only occur when you activate a service, who's fnn was used by another service which is not deactivated
			// see KnowledgeBase article KB00005
			var intServiceId = objEvent.Data.NewService.Id;
		}
		else
		{
			var intServiceId = objEvent.Data.Service.Id;
		}
		
		var objObjects 					= {};
		objObjects.Objects 				= {};
		objObjects.Objects.Service 		= {};
		objObjects.Objects.Service.Id 	= intServiceId;
		
		Vixen.Popup.ShowAjaxPopup("AccountServicesPopupId", "large", null, "Account", "ViewServices", objObjects);
		// I had hoped the following method would work, but it doesn't.  Probably because nothing is specified as the popup's Id
		//Vixen.Ajax.CallAppTemplate("Account", "ViewServices", objObjects.Objects, "Popup");
	}
}

// instanciate the object
if (Vixen.AccountServices == undefined)
{
	Vixen.AccountServices = new VixenAccountServicesClass;
	Vixen.AccountServices.Initialise();
}
