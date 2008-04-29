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
	this.objEffectiveOn		= {};

	// Handles intialisation processes that should be carried out regardless of it if is for adding a new one, or editing an existing one, or view one
	this.Initialise = function(objTemplate, objSchema)
	{
		this.objTemplate				= objTemplate;
		this.objSchema					= objSchema;
		this.elmSourceCode				= $ID("DocumentTemplate.Source");
		this.elmDescription				= $ID("DocumentTemplate.Description");
		this.objEffectiveOn.elmCombo	= $ID("EffectiveOnCombo");
		this.objEffectiveOn.elmTextbox	= $ID("DocumentTemplate.EffectiveOn");
		
		// Register tab handler for the textarea
		Event.startObserving(this.elmSourceCode, "keydown", TextAreaTabListener, true);
		
		// Set up the EffectiveOn controls to reflect the value of objTemplate.EffectiveOn
		this.objEffectiveOn.elmCombo.value = (objTemplate.EffectiveOn == null)? "undeclared" : "date";
		this.EffectiveOnComboOnChange();

		Event.startObserving(this.objEffectiveOn.elmCombo, "keypress", this.EffectiveOnComboOnChange.bind(this), true);
		Event.startObserving(this.objEffectiveOn.elmCombo, "click", this.EffectiveOnComboOnChange.bind(this), true);
		
		this.elmSourceCode.selectionStart = this.elmSourceCode.selectionEnd = 0;
	}

	this.InitialiseAddPage = function(objTemplate, objSchema)
	{
		this.Initialise(objTemplate, objSchema);
		
		// Null the things that should be null when the template is new
		this.objTemplate.Id				= null;
		this.objTemplate.Schema			= objSchema.Id;
		this.objTemplate.EffectiveOn	= null;
		this.objTemplate.CreatedOn		= null;
		this.objTemplate.Version		= null;
		
	}
	
	this.InitialiseEditPage = function(objTemplate, objSchema)
	{
		this.Initialise(objTemplate, objSchema);
		
		// Check if there has been a newer version of the document template schema since this doc template was last saved
		if (objSchema.Id != objTemplate.TemplateSchema)
		{
			$Alert("Note that a newer version of the Template Schema is now available and must be used.  Template Schemas should be backwards compatible but this is not guaranteed.  Please make sure you test pdf generation of this template before saving it.");
		}
	}
	
	this.InitialiseViewPage = function(objTemplate, objSchema)
	{
		this.objTemplate	= objTemplate;
		this.objSchema		= objSchema;
	}
	


	// Saves the template
	this.Save = function(bolConfirmed)
	{
		// Validate the form
		if (!this.ValidateForm())
		{
			// Something on the form was invalid
			return false;
		}
		
		if (!bolConfirmed)
		{
			//TODO! Notify the user that if they have specified an EffectiveOn Date, they can't then remove it
			var strEffectiveOnClause = "";
			if (this.objTemplate.EffectiveOn == null && this.objEffectiveOn.elmCombo.value == "date")
			{
				// An EffectiveOn date has been set for the first time
				strEffectiveOnClause = "<br /><br />WARNING: Templates can be modified up until their 'Effective On' date.  The 'Effective On' date can be modified, but cannot be reset to 'undeclared'.";
			}
			else if (this.objEffectiveOn.elmCombo.value == "immediately")
			{
				strEffectiveOnClause = "<br /><br />WARNING: This template will come into effect immediately and will not be able to be modified further";
			}
			
			Vixen.Popup.Confirm("Are you sure you want to save this Template?" + strEffectiveOnClause, function(){Vixen.DocumentTemplate.Save(true)});
			return;
		}
		
		// Compile data to be sent to the server
		var objData			= {}
		objData.Template	= {};
		for (i in this.objTemplate)
		{
			objData.Template[i] = this.objTemplate[i];
		}
		
		objData.Template.Description		= this.elmDescription.value;
		objData.Template.Source				= this.elmSourceCode.value;
		objData.Template.EffectiveOnType	= this.objEffectiveOn.elmCombo.value;
		objData.Template.EffectiveOn		= this.objEffectiveOn.elmTextbox.value;
		
		Vixen.Ajax.CallAppTemplate("CustomerGroup", "SaveTemplate", objData, null, true, true, this.SaveReturnHandler.bind(this));
	}
	
	// Return handler for the "Save" Ajax request
	this.SaveReturnHandler = function(objXMLHttpRequest)
	{
		var objResponse = JSON.parse(objXMLHttpRequest.responseText);
		
		if (objResponse.Success == true)
		{
			// Load the details of the Template back into this.objTemplate.  The Id should now be set
			this.objTemplate = objResponse.Template;
			
			// The description could have been updated
			this.elmDescription.value = this.objTemplate.Description;
			
			$Alert("The Template has been successfully saved");
		}
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

if (Vixen.VixenDocumentTemplateClass == undefined)
{
	Vixen.DocumentTemplate = new VixenDocumentTemplateClass;
}
