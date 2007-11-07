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
	// strPopupId
	//------------------------------------------------------------------------//
	/**
	 * strPopupId
	 *
	 * Stores the PopupId of the popup associated with this object
	 *
	 * Stores the PopupId of the popup associated with this object
	 * Defaults to "AccountServicesPopupId"
	 * 
	 * @type		string
	 *
	 * @property
	 */
	this.strPopupId = null;
	
	this.strTableContainerDivId = "AccountServicesTableDiv";
	
	this.intAccountId = null;
	
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
		
		// Register Event Listeners
		this.AddListeners();
	}

	//------------------------------------------------------------------------//
	// AddListeners
	//------------------------------------------------------------------------//
	/**
	 * AddListeners
	 *
	 * Registers the listeners contained within this class
	 *  
	 * Registers the listeners contained within this class
	 *
	 * @return	void
	 * @method
	 */
	this.AddListeners = function()
	{
		if (Vixen.EventHandler == undefined)
		{
			// The EventHandler hasn't been loaded yet.  Try again in half a second
			setTimeout(this.AddListeners(), 500);
			return;
		}
	
		Vixen.EventHandler.AddListener("OnServiceUpdate", this.OnUpdate);
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
	this.OnUpdate = function(objEvent)
	{
		// The "this" pointer does not point to this object, when it is called.
		// It points to the Window object
		var strPopupId				= Vixen.AccountServices.strPopupId;
		var strTableContainerDivId	= Vixen.AccountServices.strTableContainerDivId;
		var intAccountId			= Vixen.AccountServices.intAccountId;
		
		/* The old way of handling when the HtmlTemplate is in a page
		// If strPopupId == null then the list of Services is being displayed in a page, not a popup.
		// Just reload the page
		if (strPopupId == null)
		{
			// Since this functoin will preform a page reload, make sure there are no popups open,
			// as reloading the page will destory them.  If there are popups open, wait 0.5 seconds and check again
			if (Vixen.Popup.PopupsExist())
			{	
				// We cant reload yet
				setTimeout(function(){Vixen.AccountServices.OnUpdate(objEvent)}, 500);
				return true;
			}
		
			// There aren't any popups open.  Reload the page
			window.location = window.location;
		}
		*/
		
		// If this is loaded in a popup:
		// Check that the AccountServices popup is actually open because this will stay in 
		// memory after the popup is closed, and if something else then triggers the event, 
		// who knows what would happen
		if (strPopupId && !Vixen.Popup.Exists(strPopupId))
		{
			// The popup isn't open so don't do anything
			return;
		}

		// Organise the data to send
		var objObjects 					= {};
		objObjects.Objects 				= {};
		objObjects.Objects.Account 		= {};
		objObjects.Objects.Account.Id 	= intAccountId;
		// This will be used so that we know where to rerender the list of services
		objObjects.Objects.TableContainer 		= {};
		objObjects.Objects.TableContainer.Id	= strTableContainerDivId;

		// Call the AppTemplate method which renders just the AccountServices table
		Vixen.Ajax.CallAppTemplate("Account", "RenderAccountServicesTable", objObjects.Objects);
	}
}

// instanciate the object
if (Vixen.AccountServices == undefined)
{
	Vixen.AccountServices = new VixenAccountServicesClass;
}
