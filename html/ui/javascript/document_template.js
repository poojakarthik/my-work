//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_template.js
//----------------------------------------------------------------------------//
/**
 * document_template
 *
 * javascript required of the CustomerGroup Document Template webpages
 *
 * javascript required of the CustomerGroup Document Template webpages
 * 
 *
 * @file		document_template.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDocumentTemplateClass
//----------------------------------------------------------------------------//
/**
 * VixenDocumentTemplateClass
 *
 * Encapsulates all event handling required of the CustomerGroup Document Template webpages
 *
 * Encapsulates all event handling required of the CustomerGroup Document Template webpages
 * 
 *
 * @package	ui_app
 * @class	VixenDocumentTemplateClass
 * 
 */
function VixenDocumentTemplateClass()
{
	this.intDraftVersion	= null;
	this.intCustomerGroup	= null;

	// This is called when the DocumentTemplateHistory page is loaded
	this.InitialiseHistoryPage = function(intCustomerGroup, intDraftVersion)
	{
		this.intCustomerGroup	= intCustomerGroup;
		this.intDraftVersion	= (intDraftVersion)? intDraftVersion : null;
	}

	// Edits the template
	this.Edit = function(intTemplateId)
	{
	}
	
	// Builds a new template based on the one with id == intTemplateId
	// If intTemplateId is not supplied then it doesn't base the new template on anything
	this.BuildNew = function(intTemplateId, intVersion, bolConfirmed)
	{
		if (!bolConfirmed)
		{
			if (this.intDraftVersion)
			{
				var strPrompt = "There is currently a draft template (Version "+ this.intDraftVersion +") which will be overwritten if you choose to build a new template based on version "+ intVersion +".  Are you sure you want to overwrite the current draft template?<br />Note that the current draft template is not actually overridden until you save the changes you make.";
			}
			else
			{
				var strPrompt = "Are you sure you want to create a new template based on version " + intVersion;
			}
			Vixen.Popup.Confirm(strPrompt, function(){Vixen.DocumentTemplate.BuildNew(intTemplateId, intVersion, true)});
			//Vixen.Popup.Confirm(strPrompt, function(){alert('hello')});
			return;
		}
		
		window.location = "flex.php/CustomerGroup/BuildNewTemplate/?BaseTemplate.Id=" + intTemplateId +"&CustomerGroup.Id=" + this.intCustomerGroup;
	}
	
	// View a template
	this.View = function(intTemplateId)
	{
	}

}

if (Vixen.VixenDocumentTemplateClass == undefined)
{
	Vixen.DocumentTemplate = new VixenDocumentTemplateClass;
}
