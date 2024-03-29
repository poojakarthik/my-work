var FlexEmployeeMessage = {
	POPUP_ID		: "EmployeeMessage",

	elmMessage		: null,
	strMessageTypeConstant	: 'EMPLOYEE_MESSAGE_TYPE_GENERAL',

	// Loads the popup, for the customer declared by intContact and intAccount
	newMessage : function()
	{
		jsonFunc = jQuery.json.jsonFunction(this.newMessageReturnHandler.bind(this), null, "Employee_Message", "buildPopup");
		Vixen.Popup.ShowPageLoadingSplash("Loading", null, null, null, 1500);
		jsonFunc(null);
	},
	
	newMessageReturnHandler : function(oResponse)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (oResponse.Success)
		{
			// Display the popup
			Vixen.Popup.Create(this.POPUP_ID, oResponse.PopupContent, "ExtraLarge", "centre", "modal", "Employee Message");
			
			// Initialise the popup
			this.initialiseNewMessagePopup();
		}
		else
		{
			jQuery.json.errorPopup(oResponse, "Loading the Employee Message Popup failed");
		}
	},
	
	initialiseNewMessagePopup : function()
	{
		// Grab references to all important items on the form
		this.elmMessage		= $ID('EmployeeMessagePopup_Message');
		this.elmMessage.focus();
	},
	
	saveNewMessage : function(bolConfirmed)
	{
		if (!bolConfirmed)
		{
			// Validate the form
			if (!this.validateMessage())
			{
				// The message is not valid
				return;
			}
			
			// Prompt the user for confirmation
			Vixen.Popup.Confirm("Are you sure you want to save this message?", function(){FlexEmployeeMessage.saveNewMessage(true)});
			return;
		}
		else
		{
			// The message has already been locally validated
			this.saveMessage(null, this.elmMessage.value, null);
		}
	},
	
	saveMessage : function(intId, strMessage, strEffectiveOn)
	{
		jsonFunc = jQuery.json.jsonFunction(this.saveMessageReturnHandler.bind(this), null, "Employee_Message", "save");
		Vixen.Popup.ShowPageLoadingSplash("Saving", null, null, null, 1500);
		jsonFunc(intId, strMessage, strEffectiveOn, this.strMessageTypeConstant);
	},

	saveMessageReturnHandler : function(oResponse)
	{
		Vixen.Popup.ClosePageLoadingSplash();

		if (oResponse.Success)
		{
			$Alert("The message has been successfully saved");
			Vixen.Popup.Close(this.POPUP_ID);
			window.location = window.location;
			//TODO! either reload the page, or fire an event listener so that the page knows to update itself
		}
		else
		{
			jQuery.json.errorPopup(oResponse, "Saving the message failed");
		}
	},
	
	// notifies the user as to what is wrong with the message, if there is something wrong
	// returns true if the message form data is valid, else false
	validateMessage : function()
	{
		var strMessage = this.elmMessage.value.replace(new RegExp("^([\\s]+)|([\\s]+)$", "gm"), "");
		
		if (strMessage == '')
		{
			$Alert("Please enter a message");
			return false;
		}
		
		return true;
	}
	
};
