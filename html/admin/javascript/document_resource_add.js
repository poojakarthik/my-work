//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// document_resource_add.js
//----------------------------------------------------------------------------//
/**
 * document_resource_add
 *
 * javascript required of the Add New Document Resource popup
 *
 * javascript required of the Add New Document Resource popup
 * 
 *
 * @file		document_resource_add.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenDocumentResourceAddClass
//----------------------------------------------------------------------------//
/**
 * VixenDocumentResourceAddClass
 *
 * Encapsulates all event handling required of the Add New Document Resource popup
 *
 * Encapsulates all event handling required of the Add New Document Resource popup
 *
 *
 * @package	ui_app
 * @class	VixenDocumentResourceAddClass
 * 
 */
function VixenDocumentResourceAddClass()
{
	this.intCustomerGroup	= null;
	this.intResourceType	= null;
	
	this.objStart			= {};
	this.objEnd				= {};
	
	this.arrFileTypes		= null;
	this.elmFile			= null;
	this.elmFrame			= null;

	
	// Initialises the Document Resource Management table
	this.Initialise = function(intCustomerGroup, intResourceType)
	{
		this.intCustomerGroup		= intCustomerGroup;
		this.intResourceType		= intResourceType;
		
		this.objStart.elmCombo		= $ID("StartCombo");
		this.objStart.elmTextbox	= $ID("DocumentResource.Start");
		this.objEnd.elmCombo		= $ID("EndCombo");
		this.objEnd.elmTextbox		= $ID("DocumentResource.End");
		this.elmFrame				= $ID("FrameUploadResource");
		
		// Register listeners for the Time Range combo boxes
		Event.startObserving(this.objStart.elmCombo, "keypress", this.DateComboOnChange.bind(this.objStart), true);
		Event.startObserving(this.objStart.elmCombo, "click", this.DateComboOnChange.bind(this.objStart), true);
		Event.startObserving(this.objEnd.elmCombo, "keypress", this.DateComboOnChange.bind(this.objEnd), true);
		Event.startObserving(this.objEnd.elmCombo, "click", this.DateComboOnChange.bind(this.objEnd), true);
		
		RegisterAllInputMasks();
	}

	this.InitialiseFrame = function(arrFileTypes)
	{
		this.arrFileTypes	= arrFileTypes;
		this.elmFile		= this.elmFrame.contentDocument.getElementById("ResourceFile");
		
		// The splash might be displayed so close it if it is
		Vixen.Popup.ClosePageLoadingSplash();
	}
	
	// The this pointer should point to either this.objStart or this.objEnd
	this.DateComboOnChange = function()
	{
		this.elmTextbox.style.display = (this.elmCombo.value == "date") ? "inline" : "none";
	}
	
	// This will trigger the form submittion on the iFrame, to upload the resource file
	this.Upload = function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			// Validate the form
			if (!this.ValidateForm())
			{
				// Something on the form was invalid
				return false;
			}
			
			if (this.objStart.elmCombo.value == 'immediate')
			{
				var strImmediateClause = "<br /><br />Because this resource will come into effect immediately, it cannot be deleted once it has been uploaded.";
			}
			
			// Prompt the user
			Vixen.Popup.Confirm("Are you sure you want to upload this file?", function(){this.Upload(true);}.bind(this));
			return;
		}
		
		// Prepare the data to be sent with the form submittion
		var elmStart	= this.elmFrame.contentDocument.getElementById("DocumentResource.Start");
		var elmEnd		= this.elmFrame.contentDocument.getElementById("DocumentResource.End");
		elmStart.value	= (this.objStart.elmCombo.value == "date")? this.objStart.elmTextbox.value : 0;
		elmEnd.value	= (this.objEnd.elmCombo.value == "date")? this.objEnd.elmTextbox.value : 0;
		
		// Show the splash
		Vixen.Popup.ShowPageLoadingSplash("Uploading file", null, null, null, 1);
		
		// Submit the form
		this.elmFrame.contentDocument.forms[0].submit();
	}
	
	// This is triggered by the rendering of the Upload component (within the IFrame), after the form has
	// been submitted.  It is used to report on the outcome of the upload
	this.ImportReturnHandler = function(bolSuccess, strErrorMsg)
	{
		if (bolSuccess)
		{
			$Alert("The upload was successful");
			
			// Trigger an update of anything interested in the fact that a new resource has been added
			// (this has to be run in a separate thread otherwise the ajax request 
			// made to update the Resource History table throws an error)
			// If the popup isn't closed, then the error doesn't occur, which is odd
			var objData = 	{	CustomerGroup	: this.intCustomerGroup,
								ResourceType	: this.intResourceType
							};
			setTimeout(function(){Vixen.EventHandler.FireEvent("OnNewDocumentResource", objData)}, 1);
			
			// Close the popup 
			Vixen.Popup.Close("AddDocumentResourcePopup");
		}
		else
		{
			$Alert(strErrorMsg);
		}
	}
	
	// Validates the form data (includes displaying of error messages)
	this.ValidateForm = function()
	{
		// Validate the Start date if it has been specified
		if ((this.objStart.elmCombo.value == "date") && (!this.objStart.elmTextbox.Validate("ShortDateInFuture")))
		{
			if (!this.objStart.elmTextbox.Validate("ShortDate"))
			{
				$Alert("ERROR: Invalid 'Starting' date.<br />It must be in the format dd/mm/yyyy and in the future");
				return false;
			}
			if (!this.objStart.elmTextbox.Validate("ShortDateInFuture"))
			{
				$Alert("ERROR: Invalid 'Starting' date.  It must be in the future");
				return false;
			}
		}
		
		// Validate the End date if it has been specified
		if (this.objEnd.elmCombo.value == "date")
		{
			if (!this.objEnd.elmTextbox.Validate("ShortDate"))
			{
				$Alert("ERROR: Invalid 'Ending' date.<br />It must be in the format dd/mm/yyyy");
				return false;
			}
			
			// Check that the Ending date >= Starting date
			if (this.objStart.elmCombo.value == "date")
			{
				// A starting date has been defined.  Create a Date object out of it
				var strStartDate	= this.objStart.elmTextbox.value;
				var dateStart		= new Date(strStartDate.substr(3, 2) + "/" + strStartDate.substr(0, 2) + "/" + strStartDate.substr(6, 4));
			}
			else
			{
				// Starting date is immediate.
				var dateStart = new Date();
			}
			
			var strEndDate		= this.objEnd.elmTextbox.value;
			var dateEnd			= new Date(strEndDate.substr(3, 2) + "/" + strEndDate.substr(0, 2) + "/" + strEndDate.substr(6, 4));
			dateEnd.setHours(23);
			dateEnd.setMinutes(59);
			dateEnd.setSeconds(59);
			
			if (dateEnd < dateStart)
			{
				$Alert("ERROR: Invalid 'Ending' date.<br />It must be greater than or equal to the starting date");
				return false;
			}
		}
		
		// Check that the file's file type is acceptable for this DocumentResourceType
		var strFile = this.elmFile.value.toLowerCase();
		if (strFile.length == 0)
		{
			$Alert("ERROR: File has not been specified");
			return false;
		}
		
		var intExtensionStart = strFile.lastIndexOf(".");
		if (intExtensionStart == -1)
		{
			$Alert("ERROR: The file must have a valid extension");
			return false;
		}
		
		var strExtension		= strFile.substr(intExtensionStart + 1);
		var bolFoundFileType	= false;
		for (var i=0; i < this.arrFileTypes.length; i++)
		{
			if (strExtension == this.arrFileTypes[i].Extension.toLowerCase())
			{
				bolFoundFileType = true;
				break;
			}
		}
		if (!bolFoundFileType)
		{
			// The file is not of an appropriate file type
			$Alert("ERROR: The file is not of an appropriate file type");
			return false;
		}
		
		// Check that the filename is no longer than 255 chars including extension
		var intFileStart = strFile.lastIndexOf("/");
		if (intFileStart == -1)
		{
			intFileStart = strFile.lastIndexOf("\\");
		}
		if (intFileStart != -1)
		{
			strFile = strFile.substr(intFileStart + 1);
		}
		if (strFile.length > 255)
		{
			$Alert("ERROR: The file name cannot be any longer than 255 characters including the extension");
			return false;
		}
		
		return true;
	}

}

if (Vixen.DocumentResourceAdd == undefined)
{
	Vixen.DocumentResourceAdd = new VixenDocumentResourceAddClass;
}
