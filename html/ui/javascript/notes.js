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

// Note related constants (this should be moved to definitions.js (which doesn't exist yet) and only be given scope within the Vixen object)
if (SYSTEM_NOTE_TYPE == undefined)
{
	var SYSTEM_NOTE_TYPE	= 7;
	
	var NOTE_FILTER_ALL		= 1;
	var NOTE_FILTER_USER	= 2;
	var NOTE_FILTER_SYSTEM	= 3;
}

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
	this.intAccountId			= null;
	this.intServiceId			= null;
	this.intContactId			= null;
	this.intNoteFilter			= null;
	this.intMaxNotes			= null;
	this.strNotesContainerDivId	= null;
	this.bolUpdateCookies		= null;
	
	this.Initialise = function(intAccountId, intServiceId, intContactId, intNoteFilter, intMaxNotes, strNotesContainerDivId, bolUpdateCookies)
	{
		this.intAccountId			= intAccountId;
		this.intServiceId			= intServiceId;
		this.intContactId			= intContactId;
		this.intNoteFilter			= intNoteFilter;
		this.intMaxNotes			= intMaxNotes;
		this.strNotesContainerDivId	= strNotesContainerDivId;
		this.bolUpdateCookies		= bolUpdateCookies;
	}
	
	// If the list is rendered as a popup, it should not have a listener registered for the OnNewNote Event
	this.RegisterListeners = function()
	{
		Vixen.EventHandler.AddListener("OnNewNote", this.OnNewNote);
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
	 * @param	bolShowSplash		optional, set to true if you want to show the 
	 *								page loading splash
	 *
	 * @return	void
	 * @method
	 */
	this.ApplyFilter = function(bolShowSplash)
	{
		this.intMaxNotes = parseInt(this.intMaxNotes, 10);
		
		if (isNaN(this.intMaxNotes) || this.intMaxNotes <= 0)
		{
			// intMaxNotes is not a number greater than 0.  Have it default to 10
			this.intMaxNotes = 10;
			document.getElementById("NoteDetails.MaxNotes").value = this.intMaxNotes;
		}
		else if (this.intMaxNotes > 1000)
		{
			// Limit the maximum number of notes to retrive
			this.intMaxNotes = 1000;
			document.getElementById("NoteDetails.MaxNotes").value = this.intMaxNotes;
		}
	
		this.ReloadList(bolShowSplash);
	}
	
	this.ReloadList = function(bolShowSplash)
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
		objObjects.NoteDetails.FilterOption		= this.intNoteFilter;
		objObjects.NoteDetails.MaxNotes			= this.intMaxNotes;
		objObjects.NoteDetails.ContainerDivId	= this.strNotesContainerDivId;
		objObjects.NoteDetails.UpdateCookies	= this.bolUpdateCookies;
		
		Vixen.Ajax.CallAppTemplate("Note", "ListWithFilter", objObjects, null, bolShowSplash);
	}

	// Listener for when a note is added
	this.OnNewNote = function(objEvent)
	{
		// Since this is a listener, the "this" pointer may not be pointing to the Vixen.NoteList object
		// So it must always be refered to as Vixen.NoteList, not "this"
		
		// Check if the new note should be displayed given the current NoteListFilter
		// (I tried implementing this as a switch statement but it didn't work)
		var intNoteType = objEvent.Data.Note.NoteType;
		if (Vixen.NoteList.intNoteFilter == NOTE_FILTER_ALL)
		{
			// Reload the list of note regardless of the new note's NoteType
		}
		else if (Vixen.NoteList.intNoteFilter == NOTE_FILTER_USER)
		{
			// If the new note is a system note, then you don't have to bother reloading the list because it wouldn't be displayed anyway
			if (intNoteType == SYSTEM_NOTE_TYPE)
			{
				// exit gracefully
				return;
			}
		}
		else if (Vixen.NoteList.intNoteFilter == NOTE_FILTER_SYSTEM)
		{
			// If the new note is not a system note, then you don't have to bother reloading the list because it wouldn't be displayed anyway
			if (intNoteType != SYSTEM_NOTE_TYPE)
			{
				// exit gracefully
				return;
			}
		}
		
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

//----------------------------------------------------------------------------//
// VixenNoteAddClass
//----------------------------------------------------------------------------//
/**
 * VixenNoteAddClass
 *
 * Encapsulates all event handling required of the "Add Note" Html Template
 *
 * Encapsulates all event handling required of the "Add Note" Html Template
 * 
 *
 * @package	ui_app
 * @class	VixenNoteAddClass
 * 
 */
function VixenNoteAddClass()
{
	this.strPopupId = null;
	
	// If strPopupId is supplied, then it is assumed that the "Add Note" Html Template is being rendered as a popup
	this.Initialise = function(strPopupId)
	{
		// Check if the "Add Note" Html Template is being rendered as a popup, or in a page
		if (strPopupId != null)
		{
			// The "Add Note" functionality is a popup
			this.strPopupId = strPopupId;
		}
		else
		{
			// The "Add Note" functionality is in a page
			// You don't have to do anything
		}
	
		// Register the listener for the OnNoteAdd event
		Vixen.EventHandler.AddListener("OnNewNote", Vixen.NoteAdd.OnNewNote);
	}

	// Listener for when a note is added
	this.OnNewNote = function(objEvent)
	{
		var strPopupId = Vixen.NoteAdd.strPopupId;
		
		if (strPopupId == null)
		{
			// The "Add Note" Html Template has been rendered in a page
			// Remove the contents of the note's textarea, but only if the new note was not an automatically generated system note
			if (objEvent.Data.Note.NoteType != SYSTEM_NOTE_TYPE)
			{
				document.getElementById('Note.Note').value = "";
			}
		}
		else
		{
			// The "Add Note" Html Template has been rendered as a popup
			// Remove the listener from the registered list of listeners
			Vixen.EventHandler.RemoveListener("OnNewNote", Vixen.NoteAdd.OnNewNote);
			
			// Close the popup, if it hasn't already been closed
			if (Vixen.Popup.Exists(strPopupId))
			{
				Vixen.Popup.Close(strPopupId);
			}
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

// Use this to create the Vixen.NoteAdd VixenNoteAddClass object, if it hasn't
// already been created
function VixenCreateNoteAddObject()
{
	if (Vixen.NoteAdd == undefined)
	{
		Vixen.NoteAdd = new VixenNoteAddClass;
	}
}
