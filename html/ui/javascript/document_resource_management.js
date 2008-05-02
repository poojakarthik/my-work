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
		
		// TODO! Register the listener for the NewResourceHistory event
	}

	// Loads the history of a resource
	this.ShowHistory = function(intResourceType, bolShowSplash)
	{
		bolShowSplash = (bolShowSplash == undefined)? true : bolShowSplash;
	
		if (this.intDisplayedResourceType == intResourceType)
		{
			// We are already displaying this ResourceType
			return;
		}
		
		// Compile data to be sent to the server
		var objData	=	{
							History	:	{
											ResourceType	: intResourceType,
											CustomerGroup	: this.intCustomerGroup
										}
						};
		
		if (bolShowSplash)
		{
			Vixen.Popup.ShowPageLoadingSplash("Retrieving History");
		}
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
		//TODO! Just have it download the resource
		$Alert("TODO! Just have it download the resource");
	}
}

if (Vixen.DocumentResourceManagement == undefined)
{
	Vixen.DocumentResourceManagement = new VixenDocumentResourceManagementClass;
}
