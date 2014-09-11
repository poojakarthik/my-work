//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_resource_management.js
//----------------------------------------------------------------------------//
/**
 * document_resource_management
 *
 * javascript required of the CustomerGroup Document Resource webpage
 *
 * javascript required of the CustomerGroup Document Resource webpage
 * 
 *
 * @file		document_resource_management.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDocumentResourceManagementClass
//----------------------------------------------------------------------------//
/**
 * VixenDocumentResourceManagementClass
 *
 * Encapsulates all event handling required of the CustomerGroup Document Resource webpage
 *
 * Encapsulates all event handling required of the CustomerGroup Document Resource webpage
 *
 *
 * @package	ui_app
 * @class	VixenDocumentResourceManagementClass
 * 
 */
function VixenDocumentResourceManagementClass()
{
	this.intCustomerGroup			= null;
	this.elmHistoryContainer		= null;
	this.intDisplayedResourceType	= null;

	
	// Initialises the Document Resource Management table
	this.Initialise = function(intCustomerGroup)
	{
		this.intCustomerGroup		= intCustomerGroup;
		this.elmHistoryContainer	= $ID("Container_ResourceHistory");
		
		// Register the listener for the OnNewDocumentResource event
		Vixen.EventHandler.AddListener("OnNewDocumentResource", this.UpdateResourceHistory.bind(this));
	}

	// Loads the history of a resource
	// If bolIsRefresh is set to true, then the splash isn't shown, and it will reload the history even if it is the one currently displayed
	this.ShowHistory = function(intResourceType, bolIsRefresh)
	{
		bolIsRefresh = (bolIsRefresh == undefined)? true : bolIsRefresh;
	
		if (!bolIsRefresh)
		{
			if (this.intDisplayedResourceType == intResourceType)
			{
				// We are already displaying this ResourceType
				return;
			}
			
			Vixen.Popup.ShowPageLoadingSplash("Retrieving History");
		}
		
		// Compile data to be sent to the server
		var objData	=	{
							History	:	{
											ResourceType	: intResourceType,
											CustomerGroup	: this.intCustomerGroup
										}
						};
		
		Vixen.Ajax.CallAppTemplate("CustomerGroup", "GetDocumentResourceHistory", objData, null, false, true, this.ShowHistoryReturnHandler.bind(this));
	}
	
	// Return handler for the ShowHistory function
	this.ShowHistoryReturnHandler = function(objXMLHttpRequest, objRequestData)
	{
		this.intDisplayedResourceType		= objRequestData.Objects.History.ResourceType;
		var elmNewHistoryContainer			= this.elmHistoryContainer.cloneNode(false);
		elmNewHistoryContainer.innerHTML	= objXMLHttpRequest.responseText;
		this.elmHistoryContainer.parentNode.replaceChild(elmNewHistoryContainer, this.elmHistoryContainer);
		this.elmHistoryContainer = elmNewHistoryContainer;
	}
	
	// This will display the actual resource in some way
	this.ShowResource = function(intResourceId)
	{
		//TODO! Just have it download the resource in a new window/tab
		$Alert("TODO! Just have it download the resource");
	}
	
	// Reloads the Resource History table if it is currently displaying the 
	this.UpdateResourceHistory = function(objEvent)
	{
		if (objEvent.Data.CustomerGroup == this.intCustomerGroup && objEvent.Data.ResourceType == this.intDisplayedResourceType)
		{
			// Reload the History
			this.ShowHistory(objEvent.Data.ResourceType, true);
		}
	}
}

if (Vixen.DocumentResourceManagement == undefined)
{
	Vixen.DocumentResourceManagement = new VixenDocumentResourceManagementClass;
}
