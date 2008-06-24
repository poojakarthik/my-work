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
 * 
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
