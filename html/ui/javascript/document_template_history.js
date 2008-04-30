//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_template_history.js
//----------------------------------------------------------------------------//
/**
 * document_template_history
 *
 * javascript required of the CustomerGroup Document Template History webpage
 *
 * javascript required of the CustomerGroup Document Template History webpage
 * 
 *
 * @file		document_template_history.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDocumentTemplateHistoryClass
//----------------------------------------------------------------------------//
/**
 * VixenDocumentTemplateHistoryClass
 *
 * Encapsulates all event handling required of the CustomerGroup Document Template History webpage
 *
 * Encapsulates all event handling required of the CustomerGroup Document Template History webpage
 * 
 *
 * @package	ui_app
 * @class	VixenDocumentTemplateHistoryClass
 * 
 */
function VixenDocumentTemplateHistoryClass()
{
	this.intDraftVersion	= null;
	this.intCustomerGroup	= null;
	
	// This is called when the DocumentTemplateHistory page is loaded
	this.Initialise = function(intCustomerGroup, intDraftVersion)
	{
		this.intCustomerGroup	= intCustomerGroup;
		this.intDraftVersion	= (intDraftVersion)? intDraftVersion : null;
	}

	// Loads the Edit Template page
	this.Edit = function(intTemplateId)
	{
		window.location = "flex.php/CustomerGroup/EditTemplate/?DocumentTemplate.Id=" + intTemplateId;
	}
	
	// Builds a new template based on the one with id == intTemplateId
	// If intTemplateId is not supplied then it doesn't base the new template on anything
	this.BuildNew = function(intTemplateId, intVersion, bolConfirmed)
	{
		if (!bolConfirmed)
		{
			if (this.intDraftVersion)
			{
				var strPrompt = "There is currently a draft template (Version "+ this.intDraftVersion +") which will be overwritten if you choose to build a new template based on version "+ intVersion +".  Are you sure you want to overwrite the current draft template?<br /><br />Note that the current draft template is not actually overridden until you save the changes you make.";
			}
			else
			{
				var strPrompt = "Are you sure you want to create a new template based on version " + intVersion;
			}
			Vixen.Popup.Confirm(strPrompt, function(){Vixen.DocumentTemplateHistory.BuildNew(intTemplateId, intVersion, true)});
			return;
		}
		
		window.location = "flex.php/CustomerGroup/BuildNewTemplate/?BaseTemplate.Id=" + intTemplateId +"&CustomerGroup.Id=" + this.intCustomerGroup;
	}
	
	// Loads the View template page
	this.View = function(intTemplateId)
	{
		window.location = "flex.php/CustomerGroup/ViewTemplate/?DocumentTemplate.Id=" + intTemplateId;
	}

}

if (Vixen.DocumentTemplateHistory == undefined)
{
	Vixen.DocumentTemplateHistory = new VixenDocumentTemplateHistoryClass;
}
