//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// account_details.js
//----------------------------------------------------------------------------//
/**
 * account_details
 *
 * javascript required of the "Account details" HtmlTemplate (handles both viewing and editing)
 *
 * javascript required of the "Account details" HtmlTemplate (handles both viewing and editing)
 * 
 *
 * @file		account_details.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenAccountDetailsClass
//----------------------------------------------------------------------------//
/**
 * VixenAccountDetailsClass
 *
 * Encapsulates all event handling required of the "Account Details" HtmlTemplate
 *
 * Encapsulates all event handling required of the "Account Details" HtmlTemplate
 * The "account_status" ConstantGroup needs to be loaded into the Vixen.Constants object
 * for the "edit account" functionality to work properly
 *
 * @package	ui_app
 * @class	VixenAccountDetailsClass
 * 
 */
function VixenAccountDetailsClass()
{
	this.intAccountId				= null;
	this.strContainerDivId			= null;
	this.bolInvoicesAndPaymentsPage	= null;
	
	// Stores the current details of the account
	this.objAccount					= null;
	
	//------------------------------------------------------------------------//
	// InitialiseView
	//------------------------------------------------------------------------//
	/**
	 * InitialiseView
	 *
	 * Initialises the object for when the AccountDetails HtmlTemplate is rendered with VIEW context
	 *  
	 * Initialises the object for when the AccountDetails HtmlTemplate is rendered with VIEW context
	 *
	 * @param	int		intAccountId				Id of the account
	 * @param 	string	strTableContainerDivId		Id of the div that stores the table which lists all the services
	 * @param	bool	bolInvoicesAndPaymentsPage	TRUE, if this is being rendered for the invoices and payments page, ELSE FALSE
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseView = function(intAccountId, strContainerDivId, bolInvoicesAndPaymentsPage)
	{
		// Save the parameters
		this.intAccountId				= intAccountId;
		this.strContainerDivId			= strContainerDivId;
		this.bolInvoicesAndPaymentsPage = bolInvoicesAndPaymentsPage;
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnAccountDetailsUpdate", this.OnUpdate, this);
	}
	
	//------------------------------------------------------------------------//
	// InitialiseEdit
	//------------------------------------------------------------------------//
	/**
	 * InitialiseEdit
	 *
	 * Initialises the object for when the AccountDetails HtmlTemplate is rendered with Edit context
	 *  
	 * Initialises the object for when the AccountDetails HtmlTemplate is rendered with Edit context
	 *
	 * @param	object	objAccount					account record as taken from the database
	 * @param 	string	strTableContainerDivId		Id of the div that stores the table which lists all the services
	 * @param	bool	bolInvoicesAndPaymentsPage	TRUE, if this is being rendered for the invoices and payments page, ELSE FALSE
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseEdit = function(objAccount, strContainerDivId, bolInvoicesAndPaymentsPage)
	{
		// Save the parameters
		this.intAccountId				= objAccount.Id;
		this.strContainerDivId			= strContainerDivId;
		this.bolInvoicesAndPaymentsPage = bolInvoicesAndPaymentsPage;
		this.objAccount					= objAccount;
		
		// Register the EventListener for the WithTIO checkbox
		var elmWithTIOCheckbox = $ID("Account.WithTIO");
		Event.startObserving(elmWithTIOCheckbox, "click", this.WithTIOCheckboxOnClick.bind(this), true);
		this.WithTIOCheckboxOnClick();
	}

	// Toggles the displaying of the TIO Reference Number textbox
	this.WithTIOCheckboxOnClick = function()
	{
		var elmWithTIOCheckbox	= $ID("Account.WithTIO");
		var elmTIORefNum		= $ID("Account.tio_reference_number");
		var elmLabel			= $ID("TIOLabel");
		
		if (elmWithTIOCheckbox.checked)
		{
			elmTIORefNum.style.visibility	= "visible";
			elmTIORefNum.style.display		= "inline";
			elmLabel.innerHTML				= "T.I.O Reference Number";
		}
		else
		{
			elmTIORefNum.style.visibility	= "hidden";
			elmTIORefNum.style.display		= "none";
			elmLabel.innerHTML				= "With T.I.O.";
		}
	}

	// Prompts the user and then save the changes
	this.CommitChanges = function(bolConfirm)
	{
		if (!bolConfirm)
		{
			// Prompt the user, to confirm their action
			var intNewStatus	= parseInt($ID("AccountStatusCombo").value);
			
			// Check that the status is valid
			if (isNaN(intNewStatus) || !$Const.ConstantGroupHasConstant(intNewStatus, "account_status"))
			{
				$Alert("ERROR: Invalid account status");
				return;
			}
			
			var strStatusChangeMsg = "";
			var intCurrentStatus = this.objAccount.Archived;
			if (intNewStatus != intCurrentStatus)
			{
				// The user has changed the status
				var strNewStatus		= $Const.GetDescription(intNewStatus, "account_status");
				var strCurrentStatus	= $Const.GetDescription(intCurrentStatus, "account_status");
				
				// Check if it is being activated for the first time
				if (intCurrentStatus == $Const.ACCOUNT_STATUS_PENDING_ACTIVATION &&	intNewStatus == $Const.ACCOUNT_STATUS_ACTIVE)
				{
					// The account is being activated for the first time
					strStatusChangeMsg = "<br />You have chosen to <strong>activate</strong> the account for the first time." + 
										"<br /><span style='color:#FF0000'>WARNING: This will automatically activate and provision all services that are currently pending activation</span>";
				}
				else
				{
					strStatusChangeMsg = "<br />You have chosen to set the status of the account to <strong>"+ strNewStatus +"</strong>.";
					// It is not being activated for the first time
					// Work out what is happening and warn the user of their actions
					switch (intCurrentStatus)
					{
						case $Const.ACCOUNT_STATUS_ACTIVE:
							// The account is currently active
							switch (intNewStatus)
							{
								case $Const.ACCOUNT_STATUS_CLOSED:
								case $Const.ACCOUNT_STATUS_SUSPENDED:
								case $Const.ACCOUNT_STATUS_DEBT_COLLECTION:
									// All Active Services will be set to Disconnected
									strStatusChangeMsg += "  All active services will be set to disconnected";
									break;
								
								case $Const.ACCOUNT_STATUS_ARCHIVED:
									// All Active and Disconnected Services will be set to Archived
									strStatusChangeMsg += "  All active and disconnected services will be set to archived";
									break;
							}
							break;
							
						case $Const.ACCOUNT_STATUS_CLOSED:
						case $Const.ACCOUNT_STATUS_SUSPENDED:
						case $Const.ACCOUNT_STATUS_DEBT_COLLECTION:
							// The account is currently Closed, Suspended or set to Debt Collection
							switch (intNewStatus)
							{
								case $Const.ACCOUNT_STATUS_CLOSED:
								case $Const.ACCOUNT_STATUS_SUSPENDED:
								case $Const.ACCOUNT_STATUS_DEBT_COLLECTION:
									// All Active Services will be set to disconnected
									strStatusChangeMsg += "  All active services will be set to disconnected";
									break;
								
								case $Const.ACCOUNT_STATUS_ARCHIVED:
									// All Active and Disconnected Services will be set to Archived
									strStatusChangeMsg += "  All active and disconnected services will be set to archived";
									break;
									
								case $Const.ACCOUNT_STATUS_ACTIVE:
									// Nothing will happen to the services
									strStatusChangeMsg += "  This will not affect the status of any services";
									break;
							}
							break;
						case $Const.ACCOUNT_STATUS_ARCHIVED:
							// The account is currently Archived
							switch (intNewStatus)
							{
								case $Const.ACCOUNT_STATUS_ACTIVE:
									// Nothing will happen to the services
									strStatusChangeMsg += "  This will not affect the status of any services";
									break;

								case $Const.ACCOUNT_STATUS_CLOSED:
								case $Const.ACCOUNT_STATUS_SUSPENDED:
								case $Const.ACCOUNT_STATUS_DEBT_COLLECTION:
									// All Active Services will be set to disconnected
									strStatusChangeMsg += "  All active services will be set to disconnected";
									break;
							}
							break;
					}
				}
			}
			
			var strMsg = "Are you sure you want to commit these changes?" + strStatusChangeMsg;
			
			Vixen.Popup.Confirm(strMsg, function(){Vixen.AccountDetails.CommitChanges(true)});
			return;
			
		}
		
		// Submit the form
		var elmSubmitButton = $ID("AccountEditSubmitButton");
		elmSubmitButton.click();
	}

	//------------------------------------------------------------------------//
	// RenderAccountDetailsForEditing
	//------------------------------------------------------------------------//
	/**
	 * RenderAccountDetailsForEditing
	 *
	 * Makes an Ajax request to the server to render the AccountDetails HtmlTemplate with EDIT context
	 *  
	 * Makes an Ajax request to the server to render the AccountDetails HtmlTemplate with EDIT context
	 *
	 * @return	void
	 * @method
	 */
	this.RenderAccountDetailsForEditing = function()
	{
		// Organise the data to send
		var objObjects 								= {};
		objObjects.Account 							= {};
		objObjects.Account.Id						= this.intAccountId;
		objObjects.Account.InvoicesAndPaymentsPage	= this.bolInvoicesAndPaymentsPage;
		objObjects.Container						= {};
		objObjects.Container.Id						= this.strContainerDivId;

		// Call the AppTemplate method which renders the AccountDetails HtmlTemplate for editing
		Vixen.Ajax.CallAppTemplate("Account", "RenderAccountDetailsForEditing", objObjects, null, true);
	}
	
	//------------------------------------------------------------------------//
	// CancelEdit
	//------------------------------------------------------------------------//
	/**
	 * CancelEdit
	 *
	 * Makes an Ajax request to the server to render the AccountDetails HtmlTemplate with VIEW context
	 *  
	 * Makes an Ajax request to the server to render the AccountDetails HtmlTemplate with VIEW context
	 *
	 * @return	void
	 * @method
	 */
	this.CancelEdit = function()
	{
		// Organise the data to send
		var objObjects 								= {};
		objObjects.Account 							= {};
		objObjects.Account.Id						= this.intAccountId;
		objObjects.Account.InvoicesAndPaymentsPage	= this.bolInvoicesAndPaymentsPage;
		objObjects.Container						= {};
		objObjects.Container.Id						= this.strContainerDivId;

		// Call the AppTemplate method which renders the AccountDetails HtmlTemplate for editing
		Vixen.Ajax.CallAppTemplate("Account", "RenderAccountDetailsForViewing", objObjects, null, true);
	}

	//------------------------------------------------------------------------//
	// OnUpdate
	//------------------------------------------------------------------------//
	/**
	 * OnUpdate
	 *
	 * Event handler for when the Account has details updated which would necessitate the AccountDetails HtmlTemplate being redrawn
	 *  
	 * Event handler for when the Account has details updated which would necessitate the AccountDetails HtmlTemplate being redrawn
	 *
	 * @param	object	objEvent		objEvent.Data.Account.Id should be set.
	 *
	 * @return	void
	 * @method
	 */
	this.OnUpdate = function(objEvent, objThis)
	{
		// The "this" pointer does not point to this object, when it is called.
		// It points to the Window object
		var strContainerDivId			= objThis.strContainerDivId;
		var intAccountId				= objThis.intAccountId;
		var bolInvoicesAndPaymentsPage	= objThis.bolInvoicesAndPaymentsPage;
		
		if (intAccountId != objEvent.Data.Account.Id)
		{
			// This account is not the one that was updated
			return;
		}
		
		// Organise the data to send
		var objObjects								= {};
		objObjects.Account							= {};
		objObjects.Account.Id						= intAccountId;
		objObjects.Account.InvoicesAndPaymentsPage 	= bolInvoicesAndPaymentsPage;
		objObjects.Container						= {};
		objObjects.Container.Id						= strContainerDivId;

		// Call the AppTemplate method which renders just the AccountServices table
		Vixen.Ajax.CallAppTemplate("Account", "RenderAccountDetailsForViewing", objObjects);
	}
}

// instanciate the object
if (Vixen.AccountDetails == undefined)
{
	Vixen.AccountDetails = new VixenAccountDetailsClass;
}
