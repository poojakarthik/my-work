//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// config_constants_management.js
//----------------------------------------------------------------------------//
/**
 * config_constants_management
 *
 * javascript required of the "Constants List" HtmlTemplate
 *
 * javascript required of the "Constants List" HtmlTemplate
 * 
 *
 * @file		config_constants_management.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		8.01
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenConfigConstantsManagementClass
//----------------------------------------------------------------------------//
/**
 * VixenConfigConstantsManagementClass
 *
 * Encapsulates all event handling required of the "Constants List" HtmlTemplate
 *
 * Encapsulates all event handling required of the "Constants List" HtmlTemplate
 * 
 *
 * @package	ui_app
 * @class	VixenConfigConstantsManagementClass
 * 
 */
function VixenConfigConstantsManagementClass()
{
	this.strContainerDivPrefix = null;
	
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
	 * @param 	string	strContainerDivPrefix	Prefix for the Id of the divs that wrap each of the ConstantGroups
	 *
	 * @return	void
	 * @method
	 */
	this.Initialise = function(strContainerDivPrefix)
	{
		// Save the parameters
		this.strContainerDivPrefix = strContainerDivPrefix;
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnConfigConstantUpdate", this.OnUpdate);
	}
	
	//------------------------------------------------------------------------//
	// DeleteConstant
	//------------------------------------------------------------------------//
	/**
	 * DeleteConstant
	 *
	 * EventHandler for when the user chooses to delete a constant
	 *  
	 * EventHandler for when the user chooses to delete a constant
	 *
	 * @param 	object		objConstant		Should store all the details about the constant
	 *										It specifically needs to store the Id and name of the constant
	 *										(objConstant.Id & objConstant.Name)
	 * @param	bool		bolConfirm		optional, this is internaly used to check if the user has confirmed
	 *										that they want to delete the constant
	 *
	 * @return	void
	 * @method
	 */
	this.DeleteConstant = function(intId, strName, bolConfirmed)
	{
		if (bolConfirmed == null)
		{
		
			var strMsg = "Are you sure you want to delete the constant "+ strName +"?";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ConfigConstantsManagement.DeleteConstant(intId, strName, true);});
			return;
		}
	
		// Organise the data to send
		var objObjects 					= {};
		objObjects.ConfigConstant		= {};
		objObjects.ConfigConstant.Id	= intId;

		// Display the Pablo Splash
		Vixen.Popup.ShowPageLoadingSplash("Deleting Constant", null, null, null, 1000);
		
		// Call the AppTemplate method which deletes the constant
		Vixen.Ajax.CallAppTemplate("Config", "DeleteConstant", objObjects);
	}
	
	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when one of the ConfigConstants has been updated
	 *  
	 * Event handler for when one of the ConfigConstants has been updated
	 *
	 * @param	object	objEvent		objEvent.Data.ConfigConstant.Id should be set
	 *									objEvent.Data.ConfigConstantGroup.Id should be set (can be set to NULL)
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent)
	{
		// The "this" pointer does not point to this object, when it is called.
		// It points to the Window object
		var strContainerDivPrefix	= Vixen.ConfigConstantsManagement.strContainerDivPrefix;
		var strContainerDivId		= null;
		
		strContainerDivId = strContainerDivPrefix + objEvent.Data.ConfigConstantGroup.Id;
		
		// Organise the data to send
		var objObjects 						= {};
		objObjects.ConfigConstantGroup		= {};
		objObjects.ConfigConstantGroup.Id	= objEvent.Data.ConfigConstantGroup.Id;
		objObjects.Container 				= {};
		objObjects.Container.Id				= strContainerDivId;

		// Call the AppTemplate method which renders just the AccountServices table
		Vixen.Ajax.CallAppTemplate("Config", "RenderSingleConstantGroup", objObjects);
	}
}

// instanciate the object
if (Vixen.ConfigConstantsManagement == undefined)
{
	Vixen.ConfigConstantsManagement = new VixenConfigConstantsManagementClass;
}
