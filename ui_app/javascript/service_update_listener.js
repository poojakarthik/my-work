//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_update_listener.js
//----------------------------------------------------------------------------//
/**
 * service_update_listener
 *
 * javascript required to update the current page, if an "OnServiceUpdate" Event has been fired
 *
 * javascript required to update the current page, if an "OnServiceUpdate" Event has been fired
 * 
 *
 * @file		service_update_listener.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceUpdateListenerClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceUpdateListenerClass
 *
 * javascript required to update the current page, if an "OnServiceUpdate" Event has been fired
 *
 * javascript required to update the current page, if an "OnServiceUpdate" Event has been fired
 * 
 *
 * @package	ui_app
 * @class	VixenServiceUpdateListenerClass
 * 
 */
function VixenServiceUpdateListenerClass()
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
			setTimeout(this.Initialise(), 500);
			return;
		}
		
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
		Vixen.EventHandler.AddListener("OnServiceUpdate", this.OnUpdate);
		Vixen.EventHandler.AddListener("OnNewNote", this.OnUpdate);
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
		Vixen.EventHandler.RemoveListener("OnNewNote", this.OnUpdate);
	}

	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when a service has been updated
	 *  
	 * Event handler for when a service has been updated
	 * This will reload the current page.  If a New Service Id is provided, then it will load the current page, 
	 * with this new Id replacing the old one, for the Service.Id GET variable
	 *
	 * @param	object	objEvent		objEvent.Data.Service.Id should be set.  objEvent.Data.NewService.Id is optional
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent)
	{
		if (objEvent.Data.NewService != undefined)
		{
			// Changes made to the service have resulted in a new service record being created.
			// This should only occur when you activate a service, who's fnn was 
			// used by another service which is not deactivated
			// see KnowledgeBase article KB00005
			var intServiceId = objEvent.Data.NewService.Id;
		}
		else if (objEvent.Data.Service != undefined)
		{
			var intServiceId = objEvent.Data.Service.Id;
		}
		else
		{
			// The event does not concern a service
			// This can happen when a "OnNewNote" event is fired, and the note doesn't relate to a service
			return;
		}
		
		// Stick intServiceId in the url as the value of Service.Id, if this GET variable is already present
		var strUrl = String(window.location);
		var arrUrlHalves = strUrl.split('?');
		

		if (arrUrlHalves[1])
		{
			// There are GET variables.  Find the Service.Id and replace it
			
			// Load all the name/value pairs into an array
			var arrGetVars = arrUrlHalves[1].split('&');
			
			var arrGetVar;
			// Find the Service.Id variable
			for(i=0; i < (arrGetVars.length); i++)
			{
				arrGetVar = arrGetVars[i].split('=');
				
				if (arrGetVar[0] == "Service.Id")
				{
					// The Service.Id get variable has been found. Update the url
					strUrl = strUrl.replace(arrGetVars[i], "Service.Id=" + intServiceId);
					break;
				}
			}
			
			// Load the page, with the potentially modified url
			window.location = strUrl;
		}
		else
		{
			// There are no GET varibales.  Just reload the page
			window.location.reload();
		}
	}
}

// instanciate the object
if (Vixen.ServiceUpdateListener == undefined)
{
	Vixen.ServiceUpdateListener = new VixenServiceUpdateListenerClass;
	Vixen.ServiceUpdateListener.Initialise();
}
