//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// available_plans_page.js
//----------------------------------------------------------------------------//
/**
 * available_plans_page
 *
 * javascript required of the Available Plans page
 *
 * javascript required of the Available Plans page
 * 
 *
 * @file		available_plans_page.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenAvailablePlansPageClass
//----------------------------------------------------------------------------//
/**
 * VixenAvailablePlansPageClass
 *
 * Encapsulates all event handling required of the Available Plans webpage
 *
 * Encapsulates all event handling required of the Available Plans webpage
 *
 * @package	ui_app
 * @class	VixenAvailablePlansPageClass
 * 
 */
function VixenAvailablePlansPageClass()
{
	var RATE_STATUS_ACTIVE		= 0;
	var RATE_STATUS_ARCHIVED	= 1;
	
	this.objRatePlans = null;
	

	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the Popup
	 *
	 * Initialises the Popup
	 *
	 * @param	int		intCurrentStatus	The current status of the service
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(objRatePlans)
	{
		this.objRatePlans = objRatePlans;
	}
	
	// Triggers Status toggle between Active and Archived
	this.TogglePlanStatus = function(intRatePlan, bolConfirmed)
	{
		var objRatePlan = this.objRatePlans[intRatePlan];
		
		if (objRatePlan == undefined)
		{
			return;
		}
		
		var intNewStatus;
		var strArchiveDescription = "";
		var strAction;
		if (objRatePlan.Status == RATE_STATUS_ACTIVE)
		{
			intNewStatus			= RATE_STATUS_ARCHIVED;
			strAction				= "archive";
			strArchiveDescription	= "<br /><br />Archived Plans cannot be assigned to services that aren't already using them.";
		}
		else if (objRatePlan.Status == RATE_STATUS_ARCHIVED)
		{
			intNewStatus	= RATE_STATUS_ACTIVE;
			strAction		= "activate";
		}
		else
		{
			// Don't do anything
			return FALSE;
		}
		
		if (!bolConfirmed)
		{
			var strMsg = "Are you sure you want to <strong>"+ strAction +"</strong> the "+ objRatePlan.CustomerGroup +", "+ objRatePlan.ServiceType +" plan, '"+ objRatePlan.Name +"'?" + strArchiveDescription;
			
			Vixen.Popup.Confirm(strMsg, function(){Vixen.AvailablePlansPage.TogglePlanStatus(intRatePlan, true)});
			return;
		}
		
		// Make the call to the server, to toggle the status of the RatePlan
		var objData =	{
							RatePlan :	{	
											Id : intRatePlan
										}
						};
		
		Vixen.Ajax.CallAppTemplate("Plan", "TogglePlanStatus", objData, null, true, true);
	}
}

// instanciate the objects
if (Vixen.AvailablePlansPage == undefined)
{
	Vixen.AvailablePlansPage = new VixenAvailablePlansPageClass;
}
