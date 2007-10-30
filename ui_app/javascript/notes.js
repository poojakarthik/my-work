//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// notes.js
//----------------------------------------------------------------------------//
/**
 * notes
 *
 * javascript required of the notes functionality
 *
 * javascript required of the notes functionality
 * 
 *
 * @file		notes.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel 'MagnumSwordFortress' Dawkins
 * @version		7.10
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenNoteListClass
//----------------------------------------------------------------------------//
/**
 * VixenNoteListClass
 *
 * Encapsulates all event handling required of the note list Html Templates
 *
 * Encapsulates all event handling required of the note list Html Templates
 * 
 *
 * @package	ui_app
 * @class	VixenNoteListClass
 * 
 */
function VixenNoteListClass()
{
	this.intAccountId = null;
	this.intServiceId = null;
	this.intContactId = null;
	this.intNoteFilter = null;
	
	this.Initialise = function(intAccountId, intServiceId, intContactId, intNoteFilter)
	{
		this.intAccountId		= intAccountId;
		this.intServiceId		= intServiceId;
		this.intContactId		= intContactId;
		this.intNoteFilter		= intNoteFilter;
		
		// Register the listener for the OnNoteAdd event
		Vixen.EventHandler.AddListener("OnNewNote", Vixen.NoteList.OnNewNote);
	}
	
	
	//------------------------------------------------------------------------//
	// ApplyFilter
	//------------------------------------------------------------------------//
	/**
	 * ApplyFilter
	 *
	 * Retrieves the notes based on the current filter
	 *  
	 * Retrieves the notes based on the current filter
	 *
	 * @return	void
	 * @method
	 */
	this.ApplyFilter = function()
	{
		this.ReloadList();
		
	}
	
	this.ReloadList = function()
	{
		// Set up Properties to be sent to AppTemplateNote->ListWithFilter
		var objObjects 			= {};
		objObjects.Account 		= {};
		objObjects.Account.Id 	= this.intAccountId;
		objObjects.Service 		= {};
		objObjects.Service.Id 	= this.intServiceId;
		objObjects.Contact 		= {};
		objObjects.Contact.Id 	= this.intContactId;
		objObjects.NoteDetails 	= {};
		objObjects.NoteDetails.FilterOption = this.intNoteFilter;
		
		Vixen.Ajax.CallAppTemplate("Note", "ListWithFilter", objObjects);
	}

	// Listener for when a note is added
	this.OnNewNote = function(objEvent)
	{
		// Since this is a listener, the "this" pointer may not be pointing to the Vixen.NoteList object
		// So it must always be refered to as Vixen.NoteList, not "this"
		
		// Only bother reloading the note list if it relates to the current Account/Service/Contact
		if	(((objEvent.Data.Account.Id != undefined) && (objEvent.Data.Account.Id == Vixen.NoteList.intAccountId))
			||
			((objEvent.Data.Service.Id != undefined) && (objEvent.Data.Service.Id == Vixen.NoteList.intServiceId))
			||
			((objEvent.Data.Contact.Id != undefined) && (objEvent.Data.Contact.Id == Vixen.NoteList.intContactId)))
		{
			// Reload the note list
			Vixen.NoteList.ReloadList();
			return;
		}
	}
}

// Use this to create the Vixen.NoteList VixenNoteListClass object, if it hasn't
// already been created
function VixenCreateNoteListObject()
{
	if (Vixen.NoteList == undefined)
	{
		Vixen.NoteList = new VixenNoteListClass;
	}
}
