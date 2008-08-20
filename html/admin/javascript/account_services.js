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
	this.strPopupId				= null;
	this.strTableContainerDivId	= "AccountServicesTableDiv";
	this.intAccountId			= null;
	this.elmFilterCombo			= null;
	
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
	 * @param	int		intAccountId			Id of the account
	 * @param	string	strPopupId				Id of the Account Services popup, which
	 *											this object facilitates.
	 *											Note: This should not include the 
	 *											"VixenPopup__" prefix
	 * @param 	string	strTableContainerDivId	Id of the div that stores the table which lists all the services
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(intAccountId, strPopupId, strTableContainerDivId)
	{
		// Save the parameters
		this.strPopupId = strPopupId;
		if (strTableContainerDivId != null)
		{
			this.strTableContainerDivId = strTableContainerDivId;
		}
		this.intAccountId = intAccountId;
		
		this.elmFilterCombo = $ID("ServicesListFilterCombo");
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnServiceUpdate", this.OnUpdate, this);
		Vixen.EventHandler.AddListener("OnAccountServicesUpdate", this.OnUpdate, this);
	}

	//------------------------------------------------------------------------//
	// RemoveListeners
	//------------------------------------------------------------------------//
	/**
	 * RemoveListeners
	 *
	 * Unregisters the listeners contained within this class
	 *  
	 * Unregisters the listeners contained within this class
	 * Currently this isn't being used.  The listener "this.OnUpdate" checks to make 
	 * sure that the "AccountServices" popup is still present before actually trying
	 * to do anything with it, so the Event listeners are protected against running
	 * when the popup isn't displayed
	 *
	 * @return	void
	 * @method
	 */
	this.RemoveListeners = function()
	{
		Vixen.EventHandler.RemoveListener("OnServiceUpdate", this.OnUpdate);
		Vixen.EventHandler.RemoveListener("OnAccountServicesUpdate", this.OnUpdate);
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
	 *									(although neither of these are used by this particular listener)
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent, objThis)
	{
		var strPopupId = objThis.strPopupId;
		
		// If this is loaded in a popup:
		// Check that the AccountServices popup is actually open because this will stay in 
		// memory after the popup is closed, and if something else then triggers the event, 
		// who knows what would happen
		if (strPopupId && !Vixen.Popup.Exists(strPopupId))
		{
			// The popup isn't open so don't do anything
			return;
		}
		
		objThis.ReloadList();
	}
	
	this.ReloadList = function(bolShowSplash)
	{
		bolShowSplash = (bolShowSplash != undefined)? bolShowSplash : false;
		
		var objData = 	{
							ServiceList	:	{
												Account			: this.intAccountId,
												ContainerDivId	: this.strTableContainerDivId,
												Filter			: this.elmFilterCombo.value
											}
						};

		// Call the AppTemplate method which renders just the AccountServices table
		//Vixen.Ajax.CallAppTemplate("Account", "RenderAccountServicesTable", objData, "Div", bolShowSplash);
		Vixen.Ajax.CallAppTemplate("Account", "RenderAccountServicesTable", objData, null, bolShowSplash);
	}
}

// instanciate the object
if (Vixen.AccountServices == undefined)
{
	Vixen.AccountServices = new VixenAccountServicesClass;
}
