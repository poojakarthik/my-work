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
}
