//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// service_rate_groups.js
//----------------------------------------------------------------------------//
/**
 * service_rate_groups
 *
 * javascript required of the "Service Rate Group List" HtmlTemplate
 *
 * javascript required of the "Service Rate Group List" HtmlTemplate
 * 
 *
 * @file		service_rate_groups.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		8.02
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenServiceRateGroupsClass
//----------------------------------------------------------------------------//
/**
 * VixenServiceRateGroupsClass
 *
 * Encapsulates all event handling required of the "Service Rate Group List" HtmlTemplate
 *
 * Encapsulates all event handling required of the "Service Rate Group List" HtmlTemplate
 * 
 *
 * @package	ui_app
 * @class	VixenServiceRateGroupsClass
 * 
 */
function VixenServiceRateGroupsClass()
{
	this.strContainerDivId = null;
	
	this.intServiceId = null;
	
	//------------------------------------------------------------------------//
	// Initialise
	//------------------------------------------------------------------------//
	/**
	 * Initialise
	 *
	 * Initialises the object 
	 *  
	 * Initialises the object 
	 *
	 * @param	int		intServiceId			Id of the service
	 * @param 	string	strTableContainerDivId	Id of the div that stores the table which lists all the services
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(intServiceId, strContainerDivId)
	{
		// Save the parameters
		this.intServiceId				= intServiceId;
		this.strContainerDivId			= strContainerDivId;
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnServiceRateGroupsUpdate", this.OnUpdate);
	}
	
	//------------------------------------------------------------------------//
	// RemoveRateGroup
	//------------------------------------------------------------------------//
	/**
	 * RemoveRateGroup
	 *
	 * Removes a RateGroup from the service
	 *  
	 * Removes a RateGroup from the service
	 *
	 * @param	int		intServiceRateGroupId	Id of the ServiceRateGroup record to remove
	 * @param	string	strRateGroupName		Name of the RateGroup
	 * @param 	bool	bolConfirmed			optional, set to TRUE to confirm action
	 *
	 * @return	void
	 * @method
	 */
	this.RemoveRateGroup = function(intServiceRateGroupId, strRateGroupName, bolConfirmed)
	{
		//TODO! This functionality should be modified so that the confirmation box lists details
		// about the RateGroup to remove, and even whether or not it can be removed
		
		// Check that the RemoveRateGroup action has been confirmed
		if (bolConfirmed == null)
		{
			// We are not dealing with a Credit Card Payment
			var strMsg = "Are you sure you want to remove this RateGroup?<br />Name: " + strRateGroupName;
			strMsg += "<br /><br />WARNING: This will cause all un-invoiced CDRs to be rerated";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ServiceRateGroups.RemoveRateGroup(intServiceRateGroupId, strRateGroupName, true);});
			return;
		}
		
		// Organise the data to send
		var objObjects					= {};
		objObjects.ServiceRateGroup		= {};
		objObjects.ServiceRateGroup.Id	= intServiceRateGroupId;

		// Call the AppTemplate method which removes the ServiceRateGroup record
		Vixen.Ajax.CallAppTemplate("Service", "RemoveServiceRateGroup", objObjects, null, true);
	}
	

	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when the Service has RateGroups updated which would necessitate the ServiceRateGroupList HtmlTemplate being redrawn
	 *  
	 * Event handler for when the Service has RateGroups updated which would necessitate the ServiceRateGroupList HtmlTemplate being redrawn
	 *
	 * @param	object	objEvent		objEvent.Data.Service.Id should be set.
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent)
	{
		// The "this" pointer does not point to this object, when it is called.
		// It points to the Window object
		var strContainerDivId	= Vixen.ServiceRateGroups.strContainerDivId;
		var intServiceId		= Vixen.ServiceRateGroups.intServiceId;
		
		if (intServiceId != objEvent.Data.Service.Id)
		{
			// This service is not the one that was updated
			return;
		}
		
		// Organise the data to send
		var objObjects				= {};
		objObjects.Service			= {};
		objObjects.Service.Id		= intServiceId;
		objObjects.Container		= {};
		objObjects.Container.Id		= strContainerDivId;

		// Call the AppTemplate method which renders just the ServiceRateGroupList HtmlTemplate
		Vixen.Ajax.CallAppTemplate("Service", "RenderServiceRateGroupList", objObjects);
	}
}

// instanciate the object
if (Vixen.ServiceRateGroups == undefined)
{
	Vixen.ServiceRateGroups = new VixenServiceRateGroupsClass;
}
