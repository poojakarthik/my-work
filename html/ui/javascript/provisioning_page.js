//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// provisioning_page.js
//----------------------------------------------------------------------------//
/**
 * provisioning_page
 *
 * javascript required of the Provisioning webpage (facilitates multiple HtmlTemplates)
 *
 * javascript required of the Provisioning webpage (facilitates multiple HtmlTemplates)
 * 
 *
 * @file		provisioning_page.js
 * @language	Javascript
 * @package		ui_app
 * @author		Joel "MagnumSwordFortress" Dawkins
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// VixenProvisioningPageClass
//----------------------------------------------------------------------------//
/**
 * VixenProvisioningPageClass
 *
 * Encapsulates all event handling required of the Provisioning page
 *
 * Encapsulates all event handling required of the Provisioning page
 * 
 *
 * @package	ui_app
 * @class	VixenProvisioningPageClass
 * 
 */
function VixenProvisioningPageClass()
{
	this.strServicesContainerDivId	= null;
	this.elmMasterServiceCheckbox	= null;
	this.arrServiceCheckboxElements = null;
	
	this.intAccountId = null;
	
	//------------------------------------------------------------------------//
	// InitialiseServicesList
	//------------------------------------------------------------------------//
	/**
	 * InitialiseServicesList
	 *
	 * Sets up the object for dealing with the list of services
	 *  
	 * Sets up the object for dealing with the list of services
	 *
	 * @param 	string	strServicesContainerDivId	Id of the div that stores the ProvisioningServiceList HtmlTemplate
	 * @param	int		intAccountId				Id of the Account
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseServiceList = function(strServicesContainerDivId, intAccountId)
	{
		// Save the parameters
		this.strServicesContainerDivId	= strServicesContainerDivId;
		this.intAccountId				= intAccountId;
		
		// Save a reference to the "SelectAllServices" checkbox
		this.elmMasterServiceCheckbox = document.getElementById("SelectAllServicesCheckbox");
		
		// Save a reference to each Service checkbox element
		this.arrServiceCheckboxElements = document.getElementsByName('ServiceCheckbox');
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnServiceDetailsUpdate", this.OnServiceDetailsUpdate);
	}
	
	this.SelectAllServices(bolChecked)
	{
		// Go through the list and check/uncheck them
		for 
	}
	
	this.InitialiseEdit = function(intAccountId, strContainerDivId, bolInvoicesAndPaymentsPage)
	{
		// Save the parameters
		this.intAccountId				= intAccountId;
		this.strContainerDivId			= strContainerDivId;
		this.bolInvoicesAndPaymentsPage = bolInvoicesAndPaymentsPage;
	}

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

	// Resizes the Edit Controls when the Account Details are rendered in edit mode
	this.ResizeEditControls = function(intWidth, bolIncludeAddressDetails)
	{
		elmCustomerGroup = document.getElementById('Account.CustomerGroup');
		if (elmCustomerGroup != null)
		{
			elmCustomerGroup.style.width = intWidth;
		}
		
		document.getElementById('Account.Archived').style.width				= intWidth;
		document.getElementById('Account.BusinessName').style.width			= intWidth;
		document.getElementById('Account.TradingName').style.width			= intWidth;
		document.getElementById('Account.ABN').style.width					= intWidth;
		document.getElementById('Account.ACN').style.width					= intWidth;
		
		if (bolIncludeAddressDetails)
		{
			document.getElementById('Account.Address1').style.width			= intWidth;
			document.getElementById('Account.Address2').style.width			= intWidth;
			document.getElementById('Account.Suburb').style.width			= intWidth;
			document.getElementById('Account.Postcode').style.width			= intWidth;
			document.getElementById('Account.State').style.width			= intWidth;
			document.getElementById('Account.BillingMethod').style.width	= intWidth;
		}
		
		document.getElementById('Account.Sample').style.width				= intWidth;
		document.getElementById('Account.LatePaymentAmnesty').style.width	= intWidth;
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
	this.OnServiceDetailsUpdate = function(objEvent)
	{
		// The "this" pointer does not point to this object, when it is called.
		// It points to the Window object
		var strContainerDivId			= Vixen.AccountDetails.strContainerDivId;
		var intAccountId				= Vixen.AccountDetails.intAccountId;
		var bolInvoicesAndPaymentsPage	= Vixen.AccountDetails.bolInvoicesAndPaymentsPage;
		
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
	
	// This will be used to initialise the View/Edit Account functionality, when it is displayed in a popup
	//TODO! Sometime
	this.InitialisePopup = function()
	{
	}	
}

// instanciate the object
if (Vixen.ProvisioningPage == undefined)
{
	Vixen.ProvisioningPage = new VixenProvisioningPageClass;
}
