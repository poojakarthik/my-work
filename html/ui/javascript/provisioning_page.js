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
	this.arrServiceCheckboxElements	= null;
	this.intServiceCount			= null;
	
	this.elmRequestCombo			= null;
	this.elmCarrierCombo			= null;
	
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
	this.InitialiseServiceList = function(strServicesContainerDivId, intAccountId, intServiceCount)
	{
		// Save the parameters
		this.strServicesContainerDivId	= strServicesContainerDivId;
		this.intAccountId				= intAccountId;
		this.intServiceCount			= intServiceCount;
		
		// Save a reference to the "SelectAllServices" checkbox
		this.elmMasterServiceCheckbox = document.getElementById("SelectAllServicesCheckbox");
		
		// Save a reference to each Service checkbox element
		this.arrServiceCheckboxElements = document.getElementsByName('ServiceCheckbox');
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnServiceDetailsUpdate", this.OnServiceDetailsUpdate);
	}
	
	//------------------------------------------------------------------------//
	// SelectAllServices
	//------------------------------------------------------------------------//
	/**
	 * SelectAllServices
	 *
	 * Checks/Unchecks all the services in the list of services
	 *  
	 * Checks/Unchecks all the services in the list of services
	 *
	 * @return	void
	 * @method
	 */
	this.SelectAllServices = function()
	{
		// Go through the list and check/uncheck them
		for (i=0; i < this.arrServiceCheckboxElements.length; i++)
		{
			this.arrServiceCheckboxElements[i].checked = this.elmMasterServiceCheckbox.checked;
		}
		
		if (this.elmMasterServiceCheckbox.checked && (this.arrServiceCheckboxElements.length != this.intServiceCount))
		{
			// Not all of the services could be selected as some of them do not have address details defined
			Vixen.Popup.Alert("WARNING: Some services could not be selected as they do not have address details defined");
		}
	}
	
	//------------------------------------------------------------------------//
	// ShowHistory
	//------------------------------------------------------------------------//
	/**
	 * ShowHistory
	 *
	 * Loads the ProvisioningHistory popup for the service
	 *  
	 * Loads the ProvisioningHistory popup for the service
	 *
	 * @param	integer	intService		id of the service
	 *
	 * @return	void
	 * @method
	 */
	this.ShowHistory = function(intService)
	{
		objObjects				= {};
		objObjects.Service		= {};
		objObjects.Service.Id	= intService;
		
		Vixen.Popup.ShowAjaxPopup("ProvisioningHistoryPopupId", "ExtraLarge", "Provisioning", "Provisioning", "ViewHistory", objObjects);
	}
	
	//------------------------------------------------------------------------//
	// UpdateServiceToggle
	//------------------------------------------------------------------------//
	/**
	 * UpdateServiceToggle
	 *
	 * Checks/Unchecks all the Select All Services checkbox based on whether or not all the services are currently selected
	 *  
	 * Checks/Unchecks all the Select All Services checkbox based on whether or not all the services are currently selected
	 *
	 * @return	void
	 * @method
	 */
	this.UpdateServiceToggle = function()
	{
		var bolAllSelected = true;
		
		for (i=0; i < this.arrServiceCheckboxElements.length; i++)
		{
			bolAllSelected = (bolAllSelected && this.arrServiceCheckboxElements[i].checked);
		}
	
		this.elmMasterServiceCheckbox.checked = bolAllSelected;
	}

	//------------------------------------------------------------------------//
	// InitialiseRequestForm
	//------------------------------------------------------------------------//
	/**
	 * InitialiseRequestForm
	 *
	 * Sets up the object for dealing with the provisioning request form
	 *  
	 * Sets up the object for dealing with the provisioning request form
	 *
	 * @param	int		intAccountId				Id of the Account
	 *
	 * @return	void
	 * @method
	 */
	this.InitialiseRequestForm = function(intAccountId)
	{
		// Save the parameters
		if (this.intAccountId == null)
		{
			// Only set this if it hasn't already been set
			this.intAccountId = intAccountId;
		}
		
		this.elmRequestCombo = document.getElementById("RequestCombo");
		this.elmCarrierCombo = document.getElementById("CarrierCombo");
	}

	//------------------------------------------------------------------------//
	// SubmitRequest
	//------------------------------------------------------------------------//
	/**
	 * SubmitRequest
	 *
	 * Submits the provisioning request.  It prompts the user first
	 *  
	 * Submits the provisioning request.  It prompts the user first
	 *
	 * @param	bool	bolConfirmed	optional, true when the user has confirmed the action
	 *
	 * @return	void
	 * @method
	 */
	this.SubmitRequest = function(bolConfirmed)
	{
		// Retrieve the services that the provisioning request will be applied to
		var arrServices = new Array();
		for (i=0; i < this.arrServiceCheckboxElements.length; i++)
		{
			if (this.arrServiceCheckboxElements[i].checked)
			{
				arrServices.push(parseInt(this.arrServiceCheckboxElements[i].getAttribute('Service')));
			}
		}
		
		// Check that the form has not already been submitted and is waiting for a reply
		if (Vixen.Ajax.strFormCurrentlyProcessing != null)
		{
			Vixen.Popup.Alert("WARNING: A form is currently processing.  Please wait until it has finished before making subsequent requests", null, "ProvisioningWarningId");
			return;
		}
		
		// Check if the Request has not been confirmed yet
		if (bolConfirmed == null)
		{
			var strErrorMsg;
			// Check that a request has actually been selected
			if (this.elmCarrierCombo.value == 0)
			{
				strErrorMsg = "Please select a carrier from the drop down list";
			}
			else if (this.elmRequestCombo.value == 0)
			{
				strErrorMsg = "Please select a request from the drop down list";
			}
			else if (arrServices.length == 0)
			{
				strErrorMsg = "Please select at least one service";
			}

			if (strErrorMsg != undefined)
			{
				Vixen.Popup.Alert(strErrorMsg);
				return;
			}
		
			var strMsg = "Are you sure you want to submit this provisioning request?";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ProvisioningPage.SubmitRequest(true);});
			return;
		}
		
		// Organise the data to send
		var objObjects					= {};
		objObjects.Account				= {};
		objObjects.Account.Id			= this.intAccountId;
		objObjects.Request				= {};
		objObjects.Request.ServiceIds	= arrServices;
		objObjects.Request.Carrier		= this.elmCarrierCombo.value;
		objObjects.Request.Type			= this.elmRequestCombo.value;	

		// Call the AppTemplate method which handles a provisioning request
		Vixen.Ajax.CallAppTemplate("Provisioning", "SubmitRequest", objObjects, null, true, true);		
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
