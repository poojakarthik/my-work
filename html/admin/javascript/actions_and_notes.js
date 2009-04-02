var ACTIVE_STATUS_ACTIVE = 2;

/* This class is used to create Actions and Notes */
var ActionsAndNotes = Class.create();

/* Set static properties of the class */
Object.extend(ActionsAndNotes, 
{
	noteTypes : null,
	actionTypes : null,
	
	actionTypeDetailRequirement : { NONE		: 1,
									OPTIONAL	: 2,
									REQUIRED	: 3
									},
	
	actionAssociationType : {   ACCOUNT		: 1,
								CONTACT		: 2,
								REQUIRED	: 3
							},
	
	setNoteTypes : function(objNoteTypes)
	{
		ActionsAndNotes.noteTypes = objNoteTypes;
	},
	
	setActionTypes : function(objActionTypes)
	{
		ActionsAndNotes.actionTypes = objActionTypes;
	},
	
	loadActionAndNoteTypes : function(funcOnLoaded)
	{
		// Load the NoteTypes and ActionTypes from the server, and then run funcOnLoad
		var jsonFunc = jQuery.json.jsonFunction(ActionsAndNotes._loadActionAndNoteTypesResponse.bind(ActionsAndNotes, funcOnLoaded), null, "ActionsAndNotes", "getActionAndNoteTypes");		
		jsonFunc();
	},
	
	_loadActionAndNoteTypesResponse : function(funcOnLoaded, response)
	{
		if (response.success && response.success == true)
		{
			ActionsAndNotes.setNoteTypes(response.noteTypes);
			ActionsAndNotes.setActionTypes(response.actionTypes);
		}
		else
		{
			$Alert("Loading the ActionTypes and NoteTypes failed" + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));
			return;
		}

		if (funcOnLoaded)
		{
			funcOnLoaded();
		}
	},
	
	/* Factory method for creating the ActionsAndNotesCreator popup */
	createActionsAndNotesCreatorPopup : function(strPopupTitle, intAccountId, intServiceId, intContactId)
	{
		return new ActionsAndNotesCreatorPopup(strPopupTitle, intAccountId, intServiceId, intContactId);
	},
	
	/* Factory method for creating the ActionsAndNotesCreator embedded component */
	createActionsAndNotesCreatorEmbeddedComponent : function(elmContainer, intAccountId, intServiceId, intContactId)
	{
		return new ActionsAndNotesCreatorEmbeddedComponent(elmContainer, intAccountId, intServiceId, intContactId);
	},
	
	fireOnNewNoteEvent : function(intNoteTypeId, intAccountId, intServiceId, intContactId)
	{
		// This event handler framework should probably be updated, so that it is simpler
		if (Vixen && Vixen.EventHandler)
		{
			Vixen.EventHandler.FireEvent("OnNewNote", { Account : { Id : intAccountId},
														Service : { Id : intServiceId},
														Contact : { Id : intContactId},
														Note : {NoteType : intNoteTypeId}
														}
										);
		}
	},

	fireOnNewActionEvent : function()
	{
		//TODO! Should probably pass the details of the action
		alert("run all the OnNewAction listeners here");
	},
	
	/*  The ActionsAndNotes.Note class
	 *	This does not extend the ActionsAndNotes class.  It is defined here to protect its scope
	 */
	Note : Class.create(),
	
	/*  The ActionsAndNotes.Action class
	 *	This does not extend the ActionsAndNotes class.  It is defined here to protect its scope
	 */
	Action : Class.create(),
	
	/*  The ActionsAndNotes.Creator class 
	 *	This does not extend the ActionsAndNotes class.  It is defined here to protect its scope
	 *  This is an abstract class, which is extended by the ActionsAndNotes.Creator.EmbeddedComponent and ActionsAndNotes.Creator.Popup classes
	 */
//	Creator : Class.create()
	
});

/* The ActionsAndNotesCreator class
 * This is an abstract class, and is extended by the classes ActionsAndNotesCreatorPopup and ActionsAndNotesCreatorEmbeddedComponent
 */
var ActionsAndNotesCreator = Class.create();

/* Set static properties of the class */
Object.extend(ActionsAndNotesCreator, 
{
	// Insert static member variables and functions here
});

/* Set non static properties of the class */
Object.extend(ActionsAndNotesCreator.prototype, 
{
	accountId : null,
	serviceId : null,
	contactId : null,
	elmDetailsTextarea : null,
	elmTypeCombobox : null,
	elmSubmitButton : null,
	bolSubmitting : false,
	elmSelectedTypeOption : null,
	
	initialize : function(intAccountId, intServiceId, intContactId)
	{
		this.accountId = intAccountId;
		this.serviceId = intServiceId;
		this.contactId = intContactId;
		
		// Initialise the gui components
		this.elmDetailsTextarea = document.createElement('textarea');
		this.elmDetailsTextarea.rows = 6;
		this.elmDetailsTextarea.style.width = "100%";
		
		this.elmTypeCombobox = document.createElement('select');
		this.elmTypeCombobox.style.maxWidth = "100%";
		this.elmTypeCombobox.style.minWidth = "50%";
		
		// If the NoteTypes and ActionTypes have already been established, load them now
		if (ActionsAndNotes.noteTypes == null || ActionsAndNotes.actionTypes == null)
		{
			// The NoteTypes and ActionTypes have not yet been loaded
			ActionsAndNotes.loadActionAndNoteTypes(this.populateTypeCombobox.bind(this));
		}
		this.populateTypeCombobox();
		
		this.elmSubmitButton = document.createElement('input');
		this.elmSubmitButton.type = 'button';
		this.elmSubmitButton.value = "Submit";
		
		//TODD! register the event listeners on these elements
		// I don't know if I can set up event listeners for these elements until they have been appended to the document somewhere
		//But try it now anyway
		this.registerEventListeners();
	},
	
	display : function()
	{
		// This must be overridden 
		alert("ActionsAndNotesCreator.display() has been called in the base class, but must be overridden by the extended class")
	},

	buildForm : function(elmContainerDiv)
	{
		// Build the generic ActionsAndNotes form which will be present in both the popup and the embedded versions
		elmContainerDiv.appendChild(this.elmTypeCombobox);
		elmContainerDiv.appendChild(document.createElement('br'));
		elmContainerDiv.appendChild(this.elmDetailsTextarea);
		elmContainerDiv.appendChild(document.createElement('br'));
	},
	
	// Registers all event listeners required of the creator
	registerEventListeners : function()
	{
		Event.startObserving(this.elmTypeCombobox, "change", this.onTypeComboboxChange.bind(this), true);
		Event.startObserving(this.elmDetailsTextarea, "keyup", this.onDetailsTextareaChange.bind(this), true);
		Event.startObserving(this.elmDetailsTextarea, "blur", this.onDetailsTextareaChange.bind(this), true);
		Event.startObserving(this.elmSubmitButton, "click", this.submit.bind(this, false), true);
	},
	
	lockComponent : function()
	{
		//TODO! lock the form while a submittion is processing
		//This might have to be overridden by the extended classes, because if a submittion is processing, you don't want them to close the popup
		alert("TODO! lock the form while a submittion is processing");
	},
	
	unlockComponent : function()
	{
		//TODO! reverse of lockComponent
		alert("TODO! reverse of lockComponent");
	},
	
	clearForm : function()
	{
		// Clear the data entered into the form
		this.elmDetailsTextarea.value = "";
		this.elmTypeCombobox.selectedIndex = 0;
		this.onTypeComboboxChange();
	},
	
	submit : function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			if (this.bolSubmitting)
			{
				$Alert("Cannot submit as previous submission has not finished processing yet");
				return;
			}

			this.validateForm();
		
			if (!this.isValid())
			{
				$Alert("Please set all manditory fields");
				return;
			}
			
			// Prepare the confirmation prompt
			var strPrompt = "";
			if (this.elmSelectedTypeOption.isNoteType)
			{
				strPrompt = "Are you sure you want to submit this '"+ this.elmSelectedTypeOption.noteType.typeLabel +"' note?";
			}
			else if (this.elmSelectedTypeOption.isActionType)
			{
				strPrompt = "Are you sure you want to submit this '"+ this.elmSelectedTypeOption.actionType.name +"' action?";
			}
			else
			{
				alert("Neither an ActionType nor a NoteType has been selected");
				return;
			}
						
			Vixen.Popup.Confirm(strPrompt, this.submit.bind(this, true));
			return;
		}
		
		// Prepare the details to send
		if (this.elmSelectedTypeOption.isNoteType)
		{
			var strContent = this.getDetailsString();
			var note = new ActionsAndNotes.Note(this.elmSelectedTypeOption.noteType, this.accountId, this.serviceId, this.contactId, strContent);
			this.bolSubmitting = true;
			
			var jsonFunc = jQuery.json.jsonFunction(this.submitNoteResponse.bind(this, note), null, "ActionsAndNotes", "createNote");
			jsonFunc(note.noteType.id, note.content, note.accountId, note.serviceId, note.contactId);
			Vixen.Popup.ShowPageLoadingSplash("Saving", null, null, null, 1000);
		}
		else if (this.elmSelectedTypeOption.isActionType)
		{
			var strExtraDetails = (this.elmDetailsTextarea.disabled)? null : this.getDetailsString();
			var action = new ActionsAndNotes.Action(this.elmSelectedTypeOption.actionType, this.accountId, this.serviceId, this.contactId, strExtraDetails);
			this.bolSubmitting = true;
			
			var jsonFunc = jQuery.json.jsonFunction(this.submitActionResponse.bind(this, action), null, "ActionsAndNotes", "createAction");
			jsonFunc(action.actionType.id, action.details, action.accountId, action.serviceId, action.contactId);
			Vixen.Popup.ShowPageLoadingSplash("Saving", null, null, null, 1000);
		}
		else
		{
			alert("Neither an ActionType nor a NoteType has been selected");
			return;
		}
	},
	
	submitNoteResponse : function(note, response)
	{
		this.bolSubmitting = false;
		Vixen.Popup.ClosePageLoadingSplash();
		
		if (response.success && response.success == true)
		{
			$Alert("Successfully saved the '"+ note.noteType.typeLabel +"' note");
			
			this.clearForm();
			
			// Fire an OnNewNote Event
			ActionsAndNotes.fireOnNewNoteEvent(note.noteType.id, note.accountId, note.serviceId, note.contactId);
		}
		else
		{
			$Alert("Saving the '"+ note.noteType.typeLabel +"' failed" + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));
		}
	},

	submitActionResponse : function(action, response)
	{
		this.bolSubmitting = false;
		Vixen.Popup.ClosePageLoadingSplash();
		
		if (response.success && response.success == true)
		{
			$Alert("Successfully saved the '"+ action.actionType.name +"' action");
			
			this.clearForm();
			
			// Fire an Action Event
			ActionsAndNotes.fireOnNewActionEvent(action.actionType.id, action.accountId, action.serviceId, action.contactId);
		}
		else
		{
			$Alert("Saving the '"+ action.actionType.name +"' action failed" + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));
		}
	},
	
	populateTypeCombobox : function()
	{
		var i, j, elmOption, actionType;

		// It is assumed that all ActionTypes and NoteTypes have been retrieved from the server
		// Works out which actions and notes are applicable based on which out of accountId, serviceId, and contactId are set, and populates the combobox accordingly
		
		// Empty the control
		while (this.elmTypeCombobox.firstChild)
		{
			this.elmTypeCombobox.removeChild(this.elmTypeCombobox.firstChild);
		}
		
		// Build the default, null option
		elmOption = document.createElement('option');
		elmOption.appendChild(document.createTextNode('( ActionType / NoteType )'));
		elmOption.value = "";
		elmOption.selected = true;
		elmOption.isNoteType = false;
		elmOption.isActionType = false;
		this.elmTypeCombobox.appendChild(elmOption);
			
		// Build the Notes option group
		if (ActionsAndNotes.noteTypes != null)
		{
			var elmNotesOptionGroup = document.createElement('optgroup');
			elmNotesOptionGroup.label = 'Note Types';
			
			for (i in ActionsAndNotes.noteTypes)
			{
				elmOption = document.createElement('option');
				elmOption.value = ActionsAndNotes.noteTypes[i].id;
				elmOption.appendChild(document.createTextNode(ActionsAndNotes.noteTypes[i].typeLabel));
				elmOption.style.borderWidth = "1px";
				elmOption.style.borderStyle = "solid";
				elmOption.style.borderColor = "#"+ ActionsAndNotes.noteTypes[i].borderColor;
				elmOption.style.backgroundColor = "#"+ ActionsAndNotes.noteTypes[i].backgroundColor;
				elmOption.style.color = "#"+ ActionsAndNotes.noteTypes[i].textColor;
				
				elmOption.isNoteType = true;
				elmOption.isActionType = false;
				
				// Store a reference to the noteType it represents
				elmOption.noteType = ActionsAndNotes.noteTypes[i];
				
				elmNotesOptionGroup.appendChild(elmOption);
			}
			this.elmTypeCombobox.appendChild(elmNotesOptionGroup);

		}

		// Build the Actions option group
		if (ActionsAndNotes.actionTypes != null)
		{
			var elmActionsOptionGroup = document.createElement('optgroup');
			elmActionsOptionGroup.label = 'Action Types';
			
			for (i in ActionsAndNotes.actionTypes)
			{
				actionType = ActionsAndNotes.actionTypes[i];
				
				// Check that have something that the action can be associated with (accountId/serviceId/contactId) and it's not an 'automatic only' action type and it's active
				if  (((this.accountId && actionType.allowableActionAssociationTypes[ActionsAndNotes.actionAssociationType.ACCOUNT]) || 
					(this.serviceId && actionType.allowableActionAssociationTypes[ActionsAndNotes.actionAssociationType.SERVICE]) ||
					(this.contactId && actionType.allowableActionAssociationTypes[ActionsAndNotes.actionAssociationType.CONTACT])) &&
					!actionType.isAutomaticOnly && actionType.activeStatusId == ACTIVE_STATUS_ACTIVE)
				{
					// This ActionType is ok to use in the context of the page
					elmOption = document.createElement('option');
					elmOption.value = actionType.id;
					elmOption.appendChild(document.createTextNode(actionType.name));
				
					elmOption.isActionType = true;
					elmOption.isNoteType = false;

					// Store a reference to the noteType it represents
					elmOption.actionType = ActionsAndNotes.actionTypes[i];

					elmActionsOptionGroup.appendChild(elmOption);
				}
			}
			this.elmTypeCombobox.appendChild(elmActionsOptionGroup);
		}

		this.elmTypeCombobox.selectedIndex = 0;
		this.onTypeComboboxChange();
	},
	
	onTypeComboboxChange : function()
	{
		var elmOption = this.elmSelectedTypeOption = this.elmTypeCombobox.options[this.elmTypeCombobox.selectedIndex];
		if (!elmOption.isNoteType && !elmOption.isActionType)
		{
			// No type has been selected
			this.elmDetailsTextarea.className = "";
			this.elmDetailsTextarea.disabled = false;
		}
		else if (elmOption.isNoteType)
		{
			// A Note Type has been selected
			this.elmDetailsTextarea.className = "";
			this.elmDetailsTextarea.disabled = false;
		}
		else if (elmOption.isActionType)
		{
			// An ActionType has been selected
			this.elmDetailsTextarea.className = "";
			this.elmDetailsTextarea.disabled = (elmOption.actionType.actionTypeDetailRequirementId == ActionsAndNotes.actionTypeDetailRequirement.NONE)? true : false;
		}
		
		this.isValidType();
		this.onDetailsTextareaChange();
	},
	
	onDetailsTextareaChange : function()
	{
		if (this.elmDetailsTextarea.disabled || (this.elmTypeCombobox.selectedIndex == 0))
		{
			// The textarea is disabled or an Action / Note type has not been selected
			this.elmDetailsTextarea.className = "";
		}
		else if (this.isValidDetails())
		{
			// Valid (don't flag as valid)
			this.elmDetailsTextarea.className = "";
		}
		else
		{
			// Invalid
			this.elmDetailsTextarea.className = "invalid";
		}
	},
	
	
	// Validates the form
	isValid : function()
	{
		var bolIsValid = this.isValidType();
		bolIsValid = bolIsValid && this.isValidDetails();
		
		return bolIsValid;
	},
	
	// Returns true if the Type combobox is valid, else false
	isValidType : function()
	{
		return (this.elmSelectedTypeOption.isNoteType || this.elmSelectedTypeOption.isActionType)? true : false;
	},
	
	// returns true if details are required, else false
	// assumes this.elmSelectedTypeOption is set
	detailsRequired : function()
	{
		return  (	(this.elmSelectedTypeOption.isNoteType) || 
					(this.elmSelectedTypeOption.isActionType && this.elmSelectedTypeOption.actionType.actionTypeDetailRequirementId == ActionsAndNotes.actionTypeDetailRequirement.REQUIRED)
				);
	},
	
	// Returns true if the Details textarea is valid, else false
	isValidDetails : function()
	{
		var bolHasDetails = (this.getDetailsString() != "")? true : false;
		if (this.detailsRequired())
		{
			// Details are required
			return bolHasDetails;
		}
		else
		{
			// Details are not required
			return true;
		}
	},
	
	validateForm : function()
	{
		this.onTypeComboboxChange();
	},
	
	// If the details textarea is disabled, then this will return an empty string
	getDetailsString : function()
	{
		var strDetails = new String(this.elmDetailsTextarea.value);
		return strDetails.replace(/^\s*/, "").replace(/\s*$/, "");
	}
});


/* The ActionsAndNotesCreatorEmbeddedComponent class (extends the ActionsAndNotesCreator class) */
var ActionsAndNotesCreatorEmbeddedComponent = Class.create();

/* Set static properties of the class */
Object.extend(ActionsAndNotesCreatorEmbeddedComponent, 
{
	// Insert static member variables and functions here
});

// Inherit from ActionsAndNotesCreator (i think this is how you do it)
Object.extend(ActionsAndNotesCreatorEmbeddedComponent.prototype, ActionsAndNotesCreator.prototype);

/* Set non static properties of the class */
Object.extend(ActionsAndNotesCreatorEmbeddedComponent.prototype, 
{
	// Reference to the parents intialise function
	ActionsAndNotesCreator_initialize: ActionsAndNotesCreator.prototype.initialize,
		
	// The gui element that is the container for this embedded component
	elmContainer : null,
	
	initialize : function(elmContainer, intAccountId, intServiceId, intContactId)
	{
		// Call the parent initialise
		this.ActionsAndNotesCreator_initialize(intAccountId, intServiceId, intContactId);
		
		// Do stuff specific to the ActionsAndNotesCreator being an embedded component
		this.elmContainer = elmContainer;
	},
	
	display : function()
	{
		// Build and initialise the embedded component
		var elmComponent = document.createElement('div');
		elmComponent.className = 'GroupedContent';
		
		this.buildForm(elmComponent);
		
		// Stick the submit button on the outside of the component's frame
		var elmButtonContainerContainer = document.createElement('div');
		elmButtonContainerContainer.style.paddingTop = '3px';
		elmButtonContainerContainer.style.height = "auto";
		elmButtonContainerContainer.style.width = "100%";
		
		var elmButtonContainer = document.createElement('div');
		elmButtonContainer.style.cssFloat = 'right';
		elmButtonContainerContainer.appendChild(elmButtonContainer);
		
		var elmSpacer = document.createElement('div');
		elmSpacer.style.clear = "both";
		elmSpacer.style.cssFloat = "none";

		elmButtonContainer.appendChild(this.elmSubmitButton);
		elmButtonContainerContainer.appendChild(elmSpacer);
		
		this.elmContainer.appendChild(elmComponent);
		this.elmContainer.appendChild(elmButtonContainerContainer);
		
		this.clearForm();
	}

});


/* The ActionsAndNotesCreatorEmbeddedComponent class (extends the ActionsAndNotesCreator class) */
var ActionsAndNotesCreatorPopup = Class.create();

/* Set static properties of the class */
Object.extend(ActionsAndNotesCreatorPopup, 
{
	// Insert static member variables and functions here
});

// Inherit from ActionsAndNotesCreator (i think this is how you do it)
Object.extend(ActionsAndNotesCreatorPopup.prototype, ActionsAndNotesCreator.prototype);

/* Set non static properties of the class */
Object.extend(ActionsAndNotesCreatorPopup.prototype, 
{
	// Reference to the parent's intialise function
	ActionsAndNotesCreator_initialize: ActionsAndNotesCreator.prototype.initialize,
		
	// The Reflex_Popup object making this all possible
	popup : null,
	title : null,
	
	initialize : function(title, intAccountId, intServiceId, intContactId)
	{
		// Call the parent initialise
		this.ActionsAndNotesCreator_initialize(intAccountId, intServiceId, intContactId);
		
		this.title = "Actions / Notes - " + title;
	},
	
	display : function()
	{
		// Build the popup and display it
		this.popup = new Reflex_Popup(40);
		this.popup.setTitle(this.title);
		this.popup.addCloseButton(this.close.bind(this));
		this.popup.setIcon("../admin/img/template/actions_and_notes.png");
		
		var elmComponent = document.createElement('div');
		elmComponent.className = 'GroupedContent';
		
		this.buildForm(elmComponent);
		
		// Stick the submit button on the outside of the component's frame
		var elmButtonContainerContainer = document.createElement('div');
		elmButtonContainerContainer.style.paddingTop = '3px';
		elmButtonContainerContainer.style.height = "auto";
		elmButtonContainerContainer.style.width = "100%";
		
		var elmButtonContainer = document.createElement('div');
		elmButtonContainer.style.cssFloat = 'right';
		elmButtonContainerContainer.appendChild(elmButtonContainer);
		
		var elmSpacer = document.createElement('div');
		elmSpacer.style.clear = "both";
		elmSpacer.style.cssFloat = "none";

		elmButtonContainer.appendChild(this.elmSubmitButton);
		elmButtonContainerContainer.appendChild(elmSpacer);
		
		var elmCloseButton = document.createElement('input');
		elmCloseButton.type = "button";
		elmCloseButton.value = "close";
		Event.startObserving(elmCloseButton, "click", this.close.bind(this), true);
		
		
		this.popup.setContent(elmComponent);
		this.popup.setFooterButtons(new Array(this.elmSubmitButton, elmCloseButton), false);
		
		this.clearForm();
		
		this.popup.display();

	},
	
	close : function()
	{
		if (this.bolSubmitting)
		{
			// You might have a issue with the splash being in front of this popup
			alert("An action or note has been submitted and has not yet finished processing");
			return;
		}
		
		//TODO! Possibly destroy the object (but how would I go about doing that?  Can I grab a reference to the parent object? probably not)
		// Also destroy any EventListeners
		
		this.popup.hide();
	}

});


/* The ActionsAndNotes.Action class */
/* Set static properties of the class */
Object.extend(ActionsAndNotes.Action, 
{
	// Insert static member variables and functions here
});

/* Set non static properties of the class */
Object.extend(ActionsAndNotes.Action.prototype, 
{
	accountId : null,
	serviceId : null,
	contactId : null,
	actionType : null,
	details : null,

	initialize : function(actionType, intAccountId, intServiceId, intContactId, strDetails)
	{
		this.actionType = actionType;
		this.accountId = intAccountId;
		this.serviceId = intServiceId;
		this.contactId = intContactId;
		this.details = strDetails;
	}
});


/* The ActionsAndNotes.Note class */
/* Set static properties of the class */
Object.extend(ActionsAndNotes.Note, 
{
	// Insert static member variables and functions here
});

/* Set non static properties of the class */
Object.extend(ActionsAndNotes.Note.prototype, 
{
	accountId : null,
	serviceId : null,
	contactId : null,
	noteType : null,
	content : null,

	initialize : function(noteType, intAccountId, intServiceId, intContactId, strContent)
	{
		this.noteType = noteType;
		this.accountId = intAccountId;
		this.serviceId = intServiceId;
		this.contactId = intContactId;
		this.content = strContent;
	}
});
