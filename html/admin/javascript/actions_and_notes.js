/* This class is used to create Actions and Notes */
/* NOTE: This package needs to be 'loaded' before it can work properly.
 *  Just run ActionsAndNotes.load(funcOnLoad);
 *  You don't have to check if it has loaded, and you can que up more than one thing by doing:
 *  ActionsAndNotes.load(funcOnLoad1);
 *  ActionsAndNotes.load(funcOnLoad2);
 *  both funcOnLoad1 and funcOnLoad2 will run once the package has loaded (or if it has already loaded), although I don't think you can guarantee the order in which they are run
 */
var ActionsAndNotes = Class.create();

/* Set static properties of the class */
Object.extend(ActionsAndNotes, 
{
	noteTypes : null,
	actionTypes : null,

	TYPE_NOTE : 'NOTE',
	TYPE_ACTION	: 'ACTION',

	// These member variables are used by the load method
	bolIsLoading : false,
	bolHasLoaded : false,
	intLoadingStepsCompleted : 0,
	intTotalLoadingSteps : 2,
	arrFuncsOnLoaded : null,

	setNoteTypes : function(objNoteTypes)
	{
		ActionsAndNotes.noteTypes = objNoteTypes;
	},
	
	setActionTypes : function(objActionTypes)
	{
		ActionsAndNotes.actionTypes = objActionTypes;
	},
	
	isLoaded : function()
	{
		return ActionsAndNotes.bolHasLoaded;
	},

	loadActionAndNoteTypes : function(funcOnLoaded, bolForceReload)
	{
		if (ActionsAndNotes.noteTypes == null || ActionsAndNotes.actionTypes == null || bolForceReload)
		{
			// Load the NoteTypes and ActionTypes from the server, and then run funcOnLoad
			var jsonFunc = jQuery.json.jsonFunction(ActionsAndNotes._loadActionAndNoteTypesResponse.bind(ActionsAndNotes, funcOnLoaded), null, "ActionsAndNotes", "getActionAndNoteTypes");		
			jsonFunc();
			return;
		}
		
		// They've already been loaded
		if (funcOnLoaded)
		{
			funcOnLoaded();
		}
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
	
	load : function(funcOnLoaded)
	{
		// Do all the things you need to do before the ActionsAndNotes package can be used, then run the funcOnLoaded

		if (this.bolHasLoaded)
		{
			// The package has already loaded
			funcOnLoaded();
			return;
		}

		if (this.bolIsLoading)
		{
			// The package is currently loading
			// Append funcOnLoaded to the array of all functions to execute, once the package has loaded
			this.arrFuncsOnLoaded.push(funcOnLoaded);
			return;
		}
		
		// We need to load the ActionTypes And NoteTypes, and load the constant groups: active_status, action_type_detail_requirement & action_association_type
		this.arrFuncsOnLoaded = new Array(funcOnLoaded);
		this.bolIsLoading = true;
		this.bolHasLoaded = false;
		this.intLoadingStepsCompleted = 0;
		
		// Step 1: Load ActionTypes and NoteTypes
		this.loadActionAndNoteTypes(this._onLoaded.bind(this, "Loading Actions And Note Types"));
		
		// Step 2: Load constant groups required of this package
		Flex.Constant.loadConstantGroup(new Array('action_association_type', 'action_type_detail_requirement', 'active_status'), this._onLoaded.bind(this, "Loading Constant Groups"));
	},
	
	// The strCaller parameter is for debugging purposes, so you can check which step has loaded
	_onLoaded : function(strCaller)
	{
		this.intLoadingStepsCompleted++;

		if (this.intLoadingStepsCompleted == this.intTotalLoadingSteps)
		{
			// All steps have completed
			this.bolHasLoaded = true;
			this.bolIsLoading = false;
			
			// Run each function in the list of funcs to run when the package has loaded
			for (var i=0, j=this.arrFuncsOnLoaded.length; i<j; i++)
			{
				this.arrFuncsOnLoaded[i]();
			}
		}
	},
	
	// This will store all event listeners pertaining to Action and Note specific events
	arrEventListeners : {
							'newnote' : new Array(),
							'newaction' : new Array()
						},
	
	// I think I might need to pass parameters to this, maybe defining the details of the new Note or Action
	fireEvent : function(strEventType)
	{
		var eventType = new String(strEventType);
		eventType = eventType.toLowerCase();
	
		if (!ActionsAndNotes.arrEventListeners[eventType])
		{
			alert("ActionsAndNotes.fireEvent() - Unknown event type '"+ strEventType +"'");
		}
	
		var arrListeners = ActionsAndNotes.arrEventListeners[eventType];
		
		var arrCalledListeners = new Array();
		
		var i, j, k, bolAlreadyCalled;
		var funcListener;
		var mixListenerReturnValue;
		
		for (i=0; i < arrListeners.length; i++)
		{
			funcListener = arrListeners[i];
			bolAlreadyCalled = false;
			
			// Check if the listener isn't already in the list of called listeners
			for (j=0, k=arrCalledListeners.length; j<k; j++)
			{
				if (funcListener == arrCalledListners[j])
				{
					// This listener has already been called.  Move onto the next
					bolAlreadyCalled = true;
					break;
				}
			}
			
			if (bolAlreadyCalled)
			{
				// Move on to the next listener
				continue;
			}
			
			// If it got this far, then the listener has not already been called
			
			// Call it
			mixListenerReturnValue = funcListener();
			
			if (mixListenerReturnValue === false)
			{
				// The listener returned false, which I have chosen to assume means we should not trigger any of the remaining listeners
				break;
			}
			
			// Add it to the list of called listeners
			arrCalledListeners.push(funcListener);
			
			// Reset i, as the list of listners might have been modified by the called listener
			i = 0;
		}
	},
	
	// Whatever calls this needs to keep a reference to the anonymous function (the listener), so that it can be removed
	addEventListener : function(strEventType, funcListener)
	{
		var eventType = new String(strEventType);
		eventType = eventType.toLowerCase();
	
		if (!ActionsAndNotes.arrEventListeners[eventType])
		{
			alert("ActionsAndNotes.addEventListener() - Unknown event type '"+ strEventType +"'");
		}

		var arrListeners = ActionsAndNotes.arrEventListeners[eventType];
		
		// Check if the listener has already been registered
		for (var i=0, j=arrListeners.length; i<j; i++)
		{
			if (arrListeners[i] == funcListener)
			{
				// This listener is already in the list
				return;
			}
		}
		
		// The listener is not in the list
		// Add it now
		arrListeners.push(funcListener);
	},
	
	// Note the this must point to the exact same function that was added
	// So if you added an anonymous function, you better have kept a reference to it
	removeEventListener : function(strEventType, funcListener)
	{
		// It is assumed funcListener is only in the list once
		
		var eventType = new String(strEventType);
		eventType = eventType.toLowerCase();
	
		if (!ActionsAndNotes.arrEventListeners[eventType])
		{
			alert("ActionsAndNotes.removeEventListener() - Unknown event type '"+ strEventType +"'");
		}

		var arrListeners = ActionsAndNotes.arrEventListeners[eventType];
		
		for (var i=0, j=arrListeners.length; i<j; i++)
		{
			if (arrListeners[i] == funcListener)
			{
				// Found it in the list
				// Remove it
				arrListeners.splice(i, 1);
				return;
			}
		}
	
		// If it got this far then it didn't find it in the list
	},
	

	fireOnNewActionEvent : function(intActionTypeId, intAccountId, intServiceId, intContactId)
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
	 *  This is an abstract class, which is extended by the ActionsAndNotes.Creator.Embedded and ActionsAndNotes.Creator.Popup classes
	 */
	Creator : Class.create(),
	
	/*  The ActionsAndNotes.List class 
	 *	This does not extend the ActionsAndNotes class.  It is defined here to protect its scope
	 *  This is an abstract class, which is extended by the ActionsAndNotes.List.Embedded and ActionsAndNotes.List.Popup classes
	 */
	List : Class.create()
});

/* Static properties of the ActionsAndNotes.Creator class */
Object.extend(ActionsAndNotes.Creator, 
{
	// Insert static member variables and functions here
	
	/* The ActionsAndNotes.Creator.Embedded class
	 * This extends the ActionsAndNotes.Creator class, for use when embedding in a page
	 */
	Embedded : Class.create(),
	
	/* The ActionsAndNotes.Creator.Popup class
	 * This extends the ActionsAndNotes.Creator class, for use as a popup
	 */
	Popup : Class.create(),
	
	/* Factory method for creating the ActionsAndNotes.Creator.Popup objects */
	createPopup : function(strPopupTitle, intAccountId, intServiceId, intContactId)
	{
		return new ActionsAndNotes.Creator.Popup(strPopupTitle, intAccountId, intServiceId, intContactId);
	},
	
	/* Factory method for creating the ActionsAndNotes.Creator.Embedded objects */
	createEmbeddedComponent : function(elmContainer, intAccountId, intServiceId, intContactId)
	{
		return new ActionsAndNotes.Creator.Embedded(elmContainer, intAccountId, intServiceId, intContactId);
	}
});

/* Non-static properties of the ActionsAndNotes.Creator class */
Object.extend(ActionsAndNotes.Creator.prototype, 
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
		if (intAccountId == null && intServiceId == null && intContactId == null)
		{
			alert("ActionsAndNotes.Creator Error : No AccountId, ServiceId or ContactId has been specified");
			return;
		}
	
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
		
		this.elmSubmitButton = document.createElement('button');
		this.elmSubmitButton.appendChild(document.createTextNode('Submit'));
		
		// Register the event listeners on these elements
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
				
				if (this.elmSelectedTypeOption.actionType.actionTypeDetailRequirementId == $CONSTANT.ACTION_TYPE_DETAIL_REQUIREMENT_OPTIONAL)
				{
					strPrompt += "<br /><br />Specifying extra details is optional for this type of action.";
				}
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
			
			//TODO! We can't use this bolSubmitting flag until we make an ajax login popup that, on success, continues the original request, otherwise we can get deadlocks
			//this.bolSubmitting = true;
			
			var jsonFunc = jQuery.json.jsonFunction(this.submitNoteResponse.bind(this, note), null, "ActionsAndNotes", "createNote");
			jsonFunc(note.noteType.id, note.content, note.accountId, note.serviceId, note.contactId);
			Vixen.Popup.ShowPageLoadingSplash("Saving", null, null, null, 1000);
		}
		else if (this.elmSelectedTypeOption.isActionType)
		{
			var strExtraDetails = (this.elmDetailsTextarea.disabled)? null : this.getDetailsString();
			var action = new ActionsAndNotes.Action(this.elmSelectedTypeOption.actionType, this.accountId, this.serviceId, this.contactId, strExtraDetails);

			//TODO! We can't use this bolSubmitting flag until we make an ajax login popup that, on success, continues the original request, otherwise we can get deadlocks
			//this.bolSubmitting = true;
			
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
			ActionsAndNotes.fireEvent("NewNote");
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
			ActionsAndNotes.fireEvent("NewAction");
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
				if  (((this.accountId && actionType.allowableActionAssociationTypes[$CONSTANT.ACTION_ASSOCIATION_TYPE_ACCOUNT]) || 
					(this.serviceId && actionType.allowableActionAssociationTypes[$CONSTANT.ACTION_ASSOCIATION_TYPE_SERVICE]) ||
					(this.contactId && actionType.allowableActionAssociationTypes[$CONSTANT.ACTION_ASSOCIATION_TYPE_CONTACT])) &&
					!actionType.isAutomaticOnly && actionType.activeStatusId == $CONSTANT.ACTIVE_STATUS_ACTIVE)
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
			this.elmDetailsTextarea.disabled = (elmOption.actionType.actionTypeDetailRequirementId == $CONSTANT.ACTION_TYPE_DETAIL_REQUIREMENT_NONE)? true : false;
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
					(this.elmSelectedTypeOption.isActionType && this.elmSelectedTypeOption.actionType.actionTypeDetailRequirementId == $CONSTANT.ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED)
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

/* Set static properties of the class */
Object.extend(ActionsAndNotes.Creator.Embedded, 
{
	// Insert static member variables and functions here
});

// Inherit from ActionsAndNotes.Creator
Object.extend(ActionsAndNotes.Creator.Embedded.prototype, ActionsAndNotes.Creator.prototype);

/* Set non static properties of the class */
Object.extend(ActionsAndNotes.Creator.Embedded.prototype, 
{
	// Reference to the parents intialise function
	ActionsAndNotes_Creator_initialize: ActionsAndNotes.Creator.prototype.initialize,
		
	// The gui element that is the container for this embedded component
	elmContainer : null,
	
	initialize : function(elmContainer, intAccountId, intServiceId, intContactId)
	{
		// Call the parent initialise
		this.ActionsAndNotes_Creator_initialize(intAccountId, intServiceId, intContactId);
		
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

/* Set static properties of the class */
Object.extend(ActionsAndNotes.Creator.Popup, 
{
	// Insert static member variables and functions here
});

// Inherit from ActionsAndNotes.Creator
Object.extend(ActionsAndNotes.Creator.Popup.prototype, ActionsAndNotes.Creator.prototype);

/* Set non static properties of the class */
Object.extend(ActionsAndNotes.Creator.Popup.prototype, 
{
	// Reference to the parent's intialise function
	ActionsAndNotes_Creator_initialize: ActionsAndNotes.Creator.prototype.initialize,
		
	// The Reflex_Popup object making this all possible
	popup : null,
	title : null,
	
	initialize : function(title, intAccountId, intServiceId, intContactId)
	{
		// Call the parent initialise
		this.ActionsAndNotes_Creator_initialize(intAccountId, intServiceId, intContactId);
		
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
		
		var elmCloseButton = document.createElement('button');
		elmCloseButton.appendChild(document.createTextNode('Close'));
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

/* Static properties of the ActionsAndNotes.List class */
Object.extend(ActionsAndNotes.List, 
{
	// Insert static member variables and functions here
	
	/* The ActionsAndNotes.List.Embedded class
	 * This extends the ActionsAndNotes.List class, for use when embedding in a page
	 */
	Embedded : Class.create(),
	
	/* The ActionsAndNotes.List.Popup class
	 * This extends the ActionsAndNotes.List class, for use as a popup
	 */
	Popup : Class.create(),
	
	/* Factory method for creating the ActionsAndNotes.List.Popup objects */
	createPopup : function(strPopupTitle, intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage)
	{
		return new ActionsAndNotes.List.Popup(strPopupTitle, intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage);
	},
	
	/* Factory method for creating the ActionsAndNotes.List.Embedded objects */
	createEmbeddedComponent : function(elmContainer, intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage)
	{
		return new ActionsAndNotes.List.Embedded(elmContainer, intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage);
	},
	
	TYPE_CONSTRAINT_ALL : 'ALL',
	TYPE_CONSTRAINT_NOTES_ONLY : 'NOTES_ONLY',
	TYPE_CONSTRAINT_ACTIONS_ONLY : 'ACTIONS_ONLY',
	
	LOGGED_BY_CONSTRAINT_ANYONE : 'ANYONE',
	LOGGED_BY_CONSTRAINT_MANUAL_ONLY : 'MANUAL_ONLY',
	LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY : 'AUTOMATIC_ONLY',
	
	
});

/* Non-static properties of the ActionsAndNotes.List class */
Object.extend(ActionsAndNotes.List.prototype, 
{
	/* The ActionAssociationType context in which this component is being used in
	 * For example if this is set to ActionsAndNotes.ACTION_ASSOCIATION_TYPE.ACCOUNT,
	 * it will list actions and notes related to the account referenced by aatContextReferenceId
	 * as well as all services and contacts associated with the account.
	 * If it is set to ActionsAndNotes.ACTION_ASSOCIATION_TYPE.SERVICE, it will only retrieve actions and notes belonging to the service defined by aatContextReferenceId
	 * If in the context of a single contact, it will also retrieve all account notes associated with that contact
	 */
	intAATContextId : null,
	intAATContextReferenceId : null,
	bolIncludeAllRelatableAATTypes : false,

	elmControlsContainerDiv : null,
	elmItemsContainerDiv : null,
	elmFilterControlsContainerDiv : null,
	elmPaginationContainerDiv : null,
	
	elmFirstButton : null,
	elmPreviousButton : null,
	elmNextButton : null,
	elmLastButton : null,
	elmPageSummarySpan : null,
	
	elmTypeCombobox : null,
	elmLoggedByCombobox : null,
	elmSearchButton : null,
	
	intMaxRecordsPerPage : null,
	
	jsonFuncSearch : null,
	
	lastSearch : null,
	
	bolSubmitting : false,
	
	funcNewNoteEventListener : null,
	funcNewActionEventListener : null,
	
	initialize : function(intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage)
	{
		this.intAATContextId = intAATContextId;
		this.intAATContextReferenceId = intAATContextReferenceId;
		this.bolIncludeAllRelatableAATTypes = bolIncludeAllRelatableAATTypes;
		this.intMaxRecordsPerPage = intMaxRecordsPerPage;
		
		// Initialise the gui components
		
		// Action And Note Type Combobox
		this.elmTypeConstraintCombobox = document.createElement('select');
		this.elmTypeConstraintCombobox.style.maxWidth = "40%";
		this.elmTypeConstraintCombobox.style.marginLeft = '3px';
		
		// The LoggedBy Combobox
		this.elmLoggedByConstraintCombobox = document.createElement('select');
		this.elmLoggedByConstraintCombobox.appendChild(new Option('Logged By Anyone', ActionsAndNotes.List.LOGGED_BY_CONSTRAINT_ANYONE, false, false));
		this.elmLoggedByConstraintCombobox.appendChild(new Option('Logged Manually', ActionsAndNotes.List.LOGGED_BY_CONSTRAINT_MANUAL_ONLY, false, false));
		this.elmLoggedByConstraintCombobox.appendChild(new Option('Logged Automatically', ActionsAndNotes.List.LOGGED_BY_CONSTRAINT_AUTOMATIC_ONLY, false, false));
		this.elmLoggedByConstraintCombobox.selectedIndex = 0;
		this.elmLoggedByConstraintCombobox.style.maxWidth = "40%";
		this.elmLoggedByConstraintCombobox.style.marginLeft = '3px';
		
				
		// Search Button
		this.elmSearchButton = document.createElement('button');
		//this.elmSearchButton.type = 'button';
		//this.elmSearchButton.value = "Go";
		this.elmSearchButton.appendChild(document.createTextNode('Go'));
		this.elmSearchButton.style.marginLeft = '3px';
		
		// The FilterControlsContainer Div
		this.elmFilterControlsContainerDiv = document.createElement('div');
		this.elmFilterControlsContainerDiv.appendChild(this.elmTypeConstraintCombobox);
		this.elmFilterControlsContainerDiv.appendChild(this.elmLoggedByConstraintCombobox);
		this.elmFilterControlsContainerDiv.appendChild(this.elmSearchButton);
		
		// The pagination buttons
		this.elmFirstButton = document.createElement('button');
		//this.elmFirstButton.type = "button";
		//this.elmFirstButton.value = "<<";
		this.elmFirstButton.appendChild(document.createTextNode('<<'));
		this.elmPreviousButton = document.createElement('button');
		//this.elmPreviousButton.type = "button";
		//this.elmPreviousButton.value = "<";
		this.elmPreviousButton.appendChild(document.createTextNode('<'));
		this.elmNextButton = document.createElement('button');
		//this.elmNextButton.type = "button";
		//this.elmNextButton.value = ">";
		this.elmNextButton.appendChild(document.createTextNode('>'));
		this.elmLastButton = document.createElement('button');
		//this.elmLastButton.type = "button";
		//this.elmLastButton.value = ">>";
		this.elmLastButton.appendChild(document.createTextNode('>>'));

		// Pagination Details
		this.elmPageSummarySpan = document.createElement('span');
		this.elmPageSummarySpan.appendChild(document.createTextNode("Viewing X to Y of Z Records"));
		
		// Create table to space the pagination controls (left, centre, right)
		var elmTable = document.createElement('table');
		elmTable.style.width = '100%';
		var elmTableBody = document.createElement('tbody');
		var elmRow = document.createElement('tr');
		
		var elmLeftCell = document.createElement('td');
		elmLeftCell.style.textAlign = 'left';
		elmLeftCell.appendChild(this.elmFirstButton);
		elmLeftCell.appendChild(this.elmPreviousButton);
		
		var elmMiddleCell = document.createElement('td');
		elmMiddleCell.style.textAlign = 'center';
		elmMiddleCell.appendChild(this.elmPageSummarySpan);
		
		var elmRightCell = document.createElement('td');
		elmRightCell.style.textAlign = 'right';
		elmRightCell.appendChild(this.elmNextButton);
		elmRightCell.appendChild(this.elmLastButton);
		
		elmRow.appendChild(elmLeftCell);
		elmRow.appendChild(elmMiddleCell);
		elmRow.appendChild(elmRightCell);
		
		elmTableBody.appendChild(elmRow);
		
		elmTable.appendChild(elmTableBody);
		
		// The PaginationContainer Div
		this.elmPaginationContainerDiv = document.createElement('div');
		this.elmPaginationContainerDiv.appendChild(elmTable);
		
		// The ControlsContainer Div
		this.elmControlsContainerDiv = document.createElement('div');
		this.elmControlsContainerDiv.appendChild(this.elmFilterControlsContainerDiv);
		this.elmControlsContainerDiv.appendChild(this.elmPaginationContainerDiv);

		// The ItemsContainer Div
		this.elmItemsContainerDiv = document.createElement('div');
		this.elmItemsContainerDiv.className = 'action-list';
		
		// Register the event listeners on these elements
		Event.startObserving(this.elmSearchButton, "click", this.search.bind(this, false), true);

		Event.startObserving(this.elmFirstButton, "click", this._firstPage.bind(this), true);
		Event.startObserving(this.elmPreviousButton, "click", this._previousPage.bind(this), true);
		Event.startObserving(this.elmNextButton, "click", this._nextPage.bind(this), true);
		Event.startObserving(this.elmLastButton, "click", this._lastPage.bind(this), true);

		// Set up the ajax requests
		this.jsonFuncSearch = jQuery.json.jsonFunction(this._searchResponse.bind(this), null, "ActionsAndNotes", "search");

		this.populateTypeConstraintCombobox();
		this.setLastSearchDetails();
	},
	
	display : function()
	{
		// This must be overridden 
		alert("ActionsAndNotesCreator.display() has been called in the base class, but must be overridden by the extended class")
	},
	
	// Registers the ActionsAndNotes specific EventListeners (OnNewNote & OnNewEvent)
	registerEventListeners : function()
	{
		var funcListener = this.search.bind(this, true);

		if (this.funcNewNoteEventListener === null)
		{
			ActionsAndNotes.addEventListener("NewNote", funcListener);
			this.funcNewNoteEventListener = funcListener;
		}
		
		if (this.funcNewActionEventListener === null)
		{
			ActionsAndNotes.addEventListener("NewAction", funcListener);
			this.funcNewActionEventListener = funcListener;
		}
	},
	
	unregisterEventListeners : function()
	{
		if (this.funcNewNoteEventListener !== null)
		{
			ActionsAndNotes.removeEventListener("NewNote", this.funcNewNoteEventListener);
			this.funcNewNoteEventListener = null;
		}

		if (this.funcNewActionEventListener !== null)
		{
			ActionsAndNotes.removeEventListener("NewAction", this.funcNewActionEventListener);
			this.funcNewActionEventListener = null;
		}
	},
	
	_firstPage : function()
	{
		this.search(true, this.lastSearch.firstPageOffset);
	},
	_previousPage : function()
	{
		this.search(true, this.lastSearch.previousPageOffset);
	},
	_nextPage : function()
	{
		this.search(true, this.lastSearch.nextPageOffset);
	},
	_lastPage : function()
	{
		this.search(true, this.lastSearch.lastPageOffset);
	},
	
	search : function(bolUseLast, intPageOffset)
	{
		var typeConstraint;
		var loggedByConstraint;
		
		if (bolUseLast && this.lastSearch != null)
		{
			// Perform the same search as the last one, just use a different offset
			typeConstraint = this.lastSearch.typeConstraint;
			loggedByConstraint = this.lastSearch.loggedByConstraint;
			intPageOffset = (intPageOffset == undefined)? this.lastSearch.currentPageOffset : intPageOffset;
		}
		else
		{
			// New search
			typeConstraint = this.elmTypeConstraintCombobox.value;
			loggedByConstraint = this.elmLoggedByConstraintCombobox.value;
			intPageOffset = 0;
		}

		if (!isNaN(typeConstraint))
		{
			// The typeConstraint is an action_type_id
			typeConstraint = parseInt(typeConstraint);
		}

		this.lockButtons(new Array(this.elmFirstButton, this.elmPreviousButton, this.elmNextButton, this.elmLastButton));
		this.jsonFuncSearch(this.intAATContextId, this.intAATContextReferenceId, this.bolIncludeAllRelatableAATTypes, typeConstraint, loggedByConstraint, this.intMaxRecordsPerPage, intPageOffset);
	},
	
	_searchResponse : function(response)
	{
		if (response.success && response.success == true)
		{
			// Success

			this.displayResults(response.search, response.items);
		}
		else
		{
			// Failure
			$Alert("Retrieving Actions and Notes failed" + ((response.errorMessage != undefined)? "<br />" + response.errorMessage : ""));

			this.displayResults();
		}

	},
	
	lockButtons : function(mixButtons, bolLock)
	{
		var i, j;
		if (bolLock == undefined)
		{
			bolLock = true;
		}
		
		for (var i=0, j=mixButtons.length; i<j; i++)
		{
			mixButtons[i].disabled = bolLock;
		}
	},
	
	unlockAllButtons : function()
	{
		var arrButtons = new Array(this.elmSearchButton,
									this.elmFirstButton,
									this.elmPreviousButton,
									this.elmNextButton,
									this.elmLastButton
								);
		this.lockButtons(arrButtons, false);
	},
	
	lockAllButtons : function()
	{
		var arrButtons = new Array(this.elmSearchButton,
									this.elmFirstButton,
									this.elmPreviousButton,
									this.elmNextButton,
									this.elmLastButton
								);
		this.lockButtons(arrButtons, true);
	},

	displayResults : function(objSearchDetails, arrItems)
	{
		this.lastSearch = (objSearchDetails == undefined)? this.lastSearch : objSearchDetails;
		
		if (arrItems != undefined)
		{
			// There is a result set to show

			// Remove all old items
			while (this.elmItemsContainerDiv.firstChild)
			{
				this.elmItemsContainerDiv.removeChild(this.elmItemsContainerDiv.firstChild);
			}
			
			// Display the new ones
			for (var i=0,j=arrItems.length; i<j; i++)
			{
				this.elmItemsContainerDiv.appendChild(this.renderItem(arrItems[i]));
			}

			// Add a spacer to the bottom, because there are issues with the scrollable container div truncating the border of the last item contained within it, and this fixes the issue
			var elmSpacer = document.createElement('div');
			elmSpacer.style.height = '1px';
			
			this.elmItemsContainerDiv.appendChild(elmSpacer);
			
			// Reset the horizontal scroll
			this.elmItemsContainerDiv.scrollTop = 0;
		}
		
		this.setLastSearchDetails();
	},

	populateTypeConstraintCombobox : function()
	{
		var i, j, elmOption, actionType;
		
		// Empty the control
		while (this.elmTypeConstraintCombobox.firstChild)
		{
			this.elmTypeConstraintCombobox.removeChild(this.elmTypeConstraintCombobox.firstChild);
		}
				
		// Build the special options
		this.elmTypeConstraintCombobox.appendChild(new Option('All Actions & Notes', ActionsAndNotes.List.TYPE_CONSTRAINT_ALL, false, false));
		this.elmTypeConstraintCombobox.appendChild(new Option('Actions Only', ActionsAndNotes.List.TYPE_CONSTRAINT_ACTIONS_ONLY, false, false));
		this.elmTypeConstraintCombobox.appendChild(new Option('Notes Only', ActionsAndNotes.List.TYPE_CONSTRAINT_NOTES_ONLY, false, false));
		
		// Build the Actions option group
		if (ActionsAndNotes.actionTypes != null)
		{
			var elmActionsOptionGroup = document.createElement('optgroup');
			elmActionsOptionGroup.label = 'Action Types';
			
			for (i in ActionsAndNotes.actionTypes)
			{
				actionType = ActionsAndNotes.actionTypes[i];
				
				// Check that have something that the action can be associated with intAATContextId
				if  (actionType.allowableActionAssociationTypes[this.intAATContextId])
				{
					// This ActionType is ok to use in the context of the page
					elmOption = document.createElement('option');
					elmOption.value = actionType.id;
					elmOption.appendChild(document.createTextNode(actionType.name));
				
					elmOption.isActionType = true;
					elmOption.isNoteType = false;

					// Store a reference to the actionType it represents
					elmOption.actionType = ActionsAndNotes.actionTypes[i];

					elmActionsOptionGroup.appendChild(elmOption);
				}
			}
			this.elmTypeConstraintCombobox.appendChild(elmActionsOptionGroup);
		}

		this.elmTypeConstraintCombobox.selectedIndex = 0;
	},
	
	// This will also lock/unlock the buttons
	setLastSearchDetails : function()
	{
		var strPageDescription = "";
		if (this.lastSearch != null && this.lastSearch.pageRecordCount > 0)
		{
			this.elmTypeConstraintCombobox.value = this.lastSearch.typeConstraint;
			this.elmLoggedByConstraintCombobox.value = this.lastSearch.loggedByConstraint;

			strPageDescription = this.lastSearch.firstRecordInPage +" to "+ this.lastSearch.lastRecordInPage +" of "+ this.lastSearch.totalRecordCount;
			this.unlockAllButtons();
			var arrButtonsToLock = new Array();
			if (this.lastSearch.firstPageOffset == this.lastSearch.currentPageOffset)
			{
				// We are on the first page
				arrButtonsToLock.push(this.elmFirstButton);
				arrButtonsToLock.push(this.elmPreviousButton);
			}
			if (this.lastSearch.lastPageOffset == this.lastSearch.currentPageOffset)
			{
				// We are on the last page
				arrButtonsToLock.push(this.elmNextButton);
				arrButtonsToLock.push(this.elmLastButton);
			}
			
			this.lockButtons(arrButtonsToLock, true);
		}
		else
		{
			// No records found
			strPageDescription = "No Records Found";
			
			// Disable all pagination buttons
			this.lockAllButtons();
			
			// Unlock the search button
			this.lockButtons(new Array(this.elmSearchButton), false);
		}
		
		this.elmPageSummarySpan.removeChild(this.elmPageSummarySpan.firstChild);
		this.elmPageSummarySpan.appendChild(document.createTextNode(strPageDescription));
	},
	
	renderItem : function(objItem)
	{
		var i, j;
		var elmLink;
		var elmItemDiv = document.createElement('div');
		elmItemDiv.className = 'item';
		
		var elmHeaderDiv = document.createElement('div');
		elmHeaderDiv.className = "header";
		
		elmItemDiv.appendChild(elmHeaderDiv);
		
		var elmTimestampContainer = document.createElement('div');
		elmTimestampContainer.className = 'timestamp';
		elmTimestampContainer.appendChild(document.createTextNode(objItem.createdOnFormatted));
		
		var strType = "";
		if (objItem.recordType == ActionsAndNotes.TYPE_NOTE)
		{
			// The item is a note
			elmItemDiv.className += ' note';
			var noteType = ActionsAndNotes.noteTypes[objItem.typeId];
			strType = "Note - "+ noteType.typeLabel;
			
			// Set the note specific styling for the item
			elmItemDiv.style.borderColor = "#"+ noteType.borderColor;
			elmItemDiv.style.backgroundColor = "#"+ noteType.backgroundColor;
			elmItemDiv.style.color = "#"+ noteType.textColor;
		}
		else
		{
			// Assume the item is an action
			elmItemDiv.className += ' action';
			var actionType = ActionsAndNotes.actionTypes[objItem.typeId];
			strType = actionType.name;
		}

		var elmTypeContainer = document.createElement('div');
		elmTypeContainer.className = 'type';
		elmTypeContainer.appendChild(document.createTextNode(strType));
		
		var strByLine = "";
		if (objItem.createdBy == "Automatic System")
		{
			strByLine = "Performed by " + objItem.performedBy;
		}
		else if (objItem.createdBy == objItem.performedBy)
		{
			if (objItem.recordType == ActionsAndNotes.TYPE_NOTE)
			{
				// Notes are "created" by the user
				strByLine = "Created by " + objItem.createdBy;
			}
			else
			{
				// Actions are 'logged and performed' by the user (if not automatically logged)
				strByLine = "Logged & Performed by "+ objItem.createdBy;
			}
		}
		else
		{
			strByLine = "Logged by "+ objItem.createdBy +", Performed by "+ objItem.performedBy;
		}

		var elmByLineContainer = document.createElement('div');
		elmByLineContainer.className = 'by-line';
		elmByLineContainer.appendChild(document.createTextNode(strByLine));

		elmHeaderDiv.appendChild(elmTimestampContainer);
		elmHeaderDiv.appendChild(elmTypeContainer);
		elmHeaderDiv.appendChild(elmByLineContainer);

		// Handle any associated Accounts
		if (objItem.associatedAccounts && (j=objItem.associatedAccounts.length) > 0)
		{
			var strAccounts = (j)? "Accounts" : "Account";

			elmHeaderDiv.appendChild(document.createTextNode(strAccounts + " : "));
			
			// Make first one
			elmLink = document.createElement('a');
			elmLink.href = objItem.associatedAccounts[0].link;
			elmLink.appendChild(document.createTextNode(objItem.associatedAccounts[0].name));
			elmHeaderDiv.appendChild(elmLink);
			
			// Now do the rest (comma separated)
			for (i=1; i<j; i++)
			{
				elmLink = document.createElement('a');
				elmLink.href = objItem.associatedAccounts[i].link;
				elmLink.appendChild(document.createTextNode(objItem.associatedAccounts[i].name));

				elmHeaderDiv.appendChild(document.createTextNode(", "));
				elmHeaderDiv.appendChild(elmLink);
			}
		}

		// Handle any associated Services
		if (objItem.associatedServices && (j=objItem.associatedServices.length) > 0)
		{
			var strServices = (j)? "Services" : "Service";

			elmHeaderDiv.appendChild(document.createTextNode(strServices + " : "));
			
			// Make first one
			elmLink = document.createElement('a');
			elmLink.href = objItem.associatedServices[0].link;
			elmLink.appendChild(document.createTextNode(objItem.associatedServices[0].name));
			elmHeaderDiv.appendChild(elmLink);
			
			// Now do the rest (comma separated)
			for (i=1; i<j; i++)
			{
				elmLink = document.createElement('a');
				elmLink.href = objItem.associatedServices[i].link;
				elmLink.appendChild(document.createTextNode(objItem.associatedServices[i].name));

				elmHeaderDiv.appendChild(document.createTextNode(", "));
				elmHeaderDiv.appendChild(elmLink);
			}
		}

		// Handle any associated Contacts
		if (objItem.associatedContacts && (j=objItem.associatedContacts.length) > 0)
		{
			var strContact = (j)? "Contacts" : "Contact";

			elmHeaderDiv.appendChild(document.createTextNode(strContact + " : "));
			
			// Make first one
			elmLink = document.createElement('a');
			elmLink.href = objItem.associatedContacts[0].link;
			elmLink.appendChild(document.createTextNode(objItem.associatedContacts[0].name));
			elmHeaderDiv.appendChild(elmLink);
			
			// Now do the rest (comma separated)
			for (i=1; i<j; i++)
			{
				elmLink = document.createElement('a');
				elmLink.href = objItem.associatedContacts[i].link;
				elmLink.appendChild(document.createTextNode(objItem.associatedContacts[i].name));

				elmHeaderDiv.appendChild(document.createTextNode(", "));
				elmHeaderDiv.appendChild(elmLink);
			}
		}

		if (objItem.details !== null)
		{
			// There are details
			var elmDetailsDiv = document.createElement('div');
			elmDetailsDiv.className = 'details';
			elmItemDiv.appendChild(elmDetailsDiv);
			elmDetailsDiv.appendChild(document.createTextNode(objItem.details));
		}
		
		return elmItemDiv;
	}
	
});


/* The ActionsAndNotes.List.Popup class
 * This extends the ActionsAndNotes.List class
 */
/* Set static properties of the class */
Object.extend(ActionsAndNotes.List.Popup, 
{
	// Insert static member variables and functions here
});

// Inherit from ActionsAndNotes.List
Object.extend(ActionsAndNotes.List.Popup.prototype, ActionsAndNotes.List.prototype);

/* Set non static properties of the class */
Object.extend(ActionsAndNotes.List.Popup.prototype, 
{
	// Reference to the parent's intialise function
	ActionsAndNotes_List_initialize: ActionsAndNotes.List.prototype.initialize,
		
	// The Reflex_Popup object making this all possible
	popup : null,
	title : null,
	
	initialize : function(strPopupTitle, intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage)
	{
		// Call the parent initialise
		this.ActionsAndNotes_List_initialize(intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage);
		
		this.title = "Actions / Notes - " + strPopupTitle;
	},
	
	display : function()
	{
		// Build the popup and display it
		this.popup = new Reflex_Popup(60);
		this.popup.setTitle(this.title);
		this.popup.addCloseButton(this.close.bind(this));
		this.popup.setIcon("../admin/img/template/actions_and_notes.png");
		
		var elmComponent = document.createElement('div');
		elmComponent.className = 'GroupedContent';
		
		elmComponent.appendChild(this.elmControlsContainerDiv);
		
		// Make the items container scrollable to 400px
		this.elmItemsContainerDiv.style.height = '400px';
		elmComponent.appendChild(this.elmItemsContainerDiv);
		
		var elmCloseButton = document.createElement('input');
		elmCloseButton.type = "button";
		elmCloseButton.value = "Close";
		Event.startObserving(elmCloseButton, "click", this.close.bind(this), true);
		
		this.popup.setContent(elmComponent);
		//this.popup.setFooterButtons(new Array(elmCloseButton), false);

		// Register the event listeners
		this.registerEventListeners();
		
		this.popup.display();
		this.search();
	},
	
	close : function()
	{
		if (this.bolSubmitting)
		{
			// You might have an issue with the splash being in front of this popup
			alert("An action or note has been submitted and has not yet finished processing");
			return;
		}
		
		//TODO! Possibly destroy the object (but how would I go about doing that?  Can I grab a reference to the parent object? probably not)
		// Also destroy any EventListeners

		// Unregister the event listeners
		this.unregisterEventListeners();
		
		this.popup.hide();
	}
});

/* The ActionsAndNotes.List.Embedded class
 * This extends the ActionsAndNotes.List class
 */
/* Set static properties of the class */
Object.extend(ActionsAndNotes.List.Embedded, 
{
	// Insert static member variables and functions here
});

// Inherit from ActionsAndNotes.List
Object.extend(ActionsAndNotes.List.Embedded.prototype, ActionsAndNotes.List.prototype);

/* Set non static properties of the class */
Object.extend(ActionsAndNotes.List.Embedded.prototype, 
{
	// Reference to the parent's intialise function
	ActionsAndNotes_List_initialize: ActionsAndNotes.List.prototype.initialize,
		
	// The gui element that is the container for this embedded component
	elmContainer : null,
	
	initialize : function(elmContainer, intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage)
	{
		// Call the parent initialise
		this.ActionsAndNotes_List_initialize(intAATContextId, intAATContextReferenceId, bolIncludeAllRelatableAATTypes, intMaxRecordsPerPage);
		
		this.elmContainer = elmContainer;
	},
	
	display : function()
	{
		// Build and initialise the embedded component
		
		this.elmControlsContainerDiv.className += ' GroupedContent';
		this.elmItemsContainerDiv.className += ' embedded';
		
		this.elmContainer.appendChild(this.elmControlsContainerDiv);
		this.elmContainer.appendChild(this.elmItemsContainerDiv);
		
		// Register the event listeners
		this.registerEventListeners();
		
		this.search();
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
	},
	
	// Builds a div displaying the details of the action
	render : function(bolLinkToAccount, bolLinkToService, bolLinkToContact)
	{
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
