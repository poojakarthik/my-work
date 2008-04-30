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
	this.arrResourceTypes	= null;
	this.arrFileTypes		= null;
	this.intCustomerGroup	= null;
	
	
	this.elmSourceCode		= null;
	this.elmDescription		= null;
	this.objEffectiveOn		= {};

	// Initialises the Document Resource Management table
	this.Initialise = function(arrResourceTypes, arrFileTypes, intCustomerGroup)
	{
		this.arrResourceTypes	= arrResourceTypes;
		this.arrFileTypes		= arrFileTypes;
		this.intCustomerGroup	= intCustomerGroup;
	
	}

	this.InitialiseEmbeddedFrame = function()
	{
		//TODO!  Assign the new elements to their associated private data tributes and Register listeners
		
	}


	// Loads the history of a resource
	this.ShowHistory = function(intResourceType)
	{
		
		// Compile data to be sent to the server
		var objData	=	{
							History	:	{
											ResourceType	: intResourceType,
											CustomerGroup	: this.intCustomerGroup
										}
						};
		
		Vixen.Popup.ShowPageLoadingSplash("Retrieving History");
		Vixen.Ajax.CallAppTemplate("CustomerGroup", "GetDocumentResourceHistory", objData, null, true, true, "Container_ResourceHistory");
	}
	
	this.ValidateForm = function()
	{
		// Validate the EffectiveOn date if one has been specified
		if ((this.objEffectiveOn.elmCombo.value == "date") && (!this.objEffectiveOn.elmTextbox.Validate("ShortDateInFuture")))
		{
			if (!this.objEffectiveOn.elmTextbox.Validate("ShortDate"))
			{
				$Alert("ERROR: Invalid 'Effective On' date.<br />It must be in the format dd/mm/yyyy and in the future");
				return false;
			}
			if (!this.objEffectiveOn.elmTextbox.Validate("ShortDateInFuture"))
			{
				$Alert("ERROR: Invalid 'Effective On' date.  It must be in the future");
				return false;
			}
		}
		return true;
	}

	// Event listener for the EffectiveOnCombo
	this.EffectiveOnComboOnChange = function()
	{
		if (this.objEffectiveOn.elmCombo.value == "date")
		{
			this.objEffectiveOn.elmTextbox.style.visibility	= "visible";
			this.objEffectiveOn.elmTextbox.style.display	= "inline";
		}
		else
		{
			this.objEffectiveOn.elmTextbox.style.visibility	= "hidden";
			this.objEffectiveOn.elmTextbox.style.display	= "none";
		}
	}

}

if (Vixen.DocumentResourceManagement == undefined)
{
	Vixen.DocumentResourceManagement = new VixenDocumentResourceManagementClass;
}
