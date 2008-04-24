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
	this.objTemplate		= null;
	this.objSchema			= null;
	this.elmSourceCode		= null;
	this.elmDescription		= null;

	this.InitialiseAddPage = function(objTemplate, objSchema)
	{
		this.objTemplate	= objTemplate;
		this.objSchema		= objSchema;
		this.elmSourceCode	= $ID("DocumentTemplate.Source");
		this.elmDescription = $ID("DocumentTemplate.Description");
		
		// Null the things that should be null when the template is new
		this.objTemplate.Id				= null;
		this.objTemplate.Schema			= objSchema.Id;
		this.objTemplate.EffectiveOn	= null;
		this.objTemplate.CreatedOn		= null;
		this.objTemplate.Version		= null;
		
		// Register tab handler for the textarea
		Event.startObserving(this.elmSourceCode, "keydown", TextAreaTabListener, true);
	}


	// Saves the template
	this.Save = function(bolConfirmed)
	{
		// Check that changes have been made
		if ((!bolConfirmed) && (this.objTemplate.Source == this.elmSourceCode.value) && (this.objTemplate.Description == this.elmDescription.value))
		{
			$Alert("No changes have actually been made");
			return;
		}
	
		if (!bolConfirmed)
		{
			Vixen.Popup.Confirm("Are you sure you want to save this Template?", function(){Vixen.DocumentTemplate.Save(true)});
			return;
		}
		
		// Compile data to be sent to the server
		var objData			= {}
		objData.Template	= {};
		for (i in this.objTemplate)
		{
			objData.Template[i] = this.objTemplate[i];
		}
		
		objData.Template.Description	= this.elmDescription.value;
		objData.Template.Source			= this.elmSourceCode.value;
		
		Vixen.Ajax.CallAppTemplate("CustomerGroup", "SaveTemplate", objData, null, true, true, this.SaveReturnHandler.bind(this));
	}
	
	this.SaveReturnHandler = function(objXMLHttpRequest)
	{
		var objResponse = JSON.parse(objXMLHttpRequest.responseText);
		
		if (objResponse.Success == true)
		{
			// Load the details of the Template back into this.objTemplate.  The Id should now be set
			this.objTemplate = objResponse.Template;
			$Alert("The Template has been successfully saved");
		}
	}

}

if (Vixen.VixenDocumentTemplateClass == undefined)
{
	Vixen.DocumentTemplate = new VixenDocumentTemplateClass;
}
