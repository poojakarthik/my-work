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
	this.bolHasFlaggedServices		= null;
	this.elmServiceFilterCombo		= null;
	
	this.elmRequestCombo			= null;
	this.elmCarrierCombo			= null;
	this.elmAuthDateTextBox			= null;
	
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
	this.InitialiseServiceList = function(strServicesContainerDivId, intAccountId, bolHasFlaggedServices)
	{
		// Save the parameters
		this.strServicesContainerDivId	= strServicesContainerDivId;
		this.intAccountId				= intAccountId;
		this.bolHasFlaggedServices		= bolHasFlaggedServices;
		
		// Save a reference to the "SelectAllServices" checkbox
		this.elmMasterServiceCheckbox	= $ID("SelectAllServicesCheckbox");
		this.elmServiceFilterCombo		= $ID("ServicesListFilterCombo");
		
		// Save a reference to each Service checkbox element
		this.arrServiceCheckboxElements = document.getElementsByName('ServiceCheckbox');
		
		// Register Event Listeners
		Vixen.EventHandler.AddListener("OnServiceUpdate", this.OnServiceDetailsUpdate, this);
		
		this.UpdateServiceToggle();
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
		
		if (this.elmMasterServiceCheckbox.checked && (this.bolHasFlaggedServices))
		{
			// Not all of the services could be selected as some of them do not have address details defined or are pending activation
			Vixen.Popup.Alert("WARNING: Flagged services could not be selected");
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
	 * Checks/Unchecks the Select All Services checkbox based on whether or not all the services are currently selected
	 *  
	 * Checks/Unchecks the Select All Services checkbox based on whether or not all the services are currently selected
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
		
		if (this.arrServiceCheckboxElements.length == 0)
		{
			bolAllSelected = false;
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
		
		this.elmRequestCombo	= $ID("RequestCombo");
		this.elmCarrierCombo	= $ID("CarrierCombo");
		this.elmAuthDateTextBox	= $ID("AuthorisationDateTextBox");
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
			if (strErrorMsg)
			{
				Vixen.Popup.Alert(strErrorMsg);
				return;
			}
			
			var strMsg = "Are you sure you want to submit this provisioning request?";
			Vixen.Popup.Confirm(strMsg, function(){Vixen.ProvisioningPage.SubmitRequest(true);});
			return;
		}
		
		// Organise the data to send
		var objObjects							= {};
		objObjects.Account						= {};
		objObjects.Account.Id					= this.intAccountId;
		objObjects.Request						= {};
		objObjects.Request.ServiceIds			= arrServices;
		objObjects.Request.Carrier				= this.elmCarrierCombo.value;
		objObjects.Request.Type					= this.elmRequestCombo.value;	
		objObjects.Request.AuthorisationDate	= this.elmAuthDateTextBox.value;

		// Call the AppTemplate method which handles a provisioning request
		Vixen.Ajax.CallAppTemplate("Provisioning", "SubmitRequest", objObjects, null, true, true);		
	}
	
	// Listener for when one of the services has its address details updated
	this.OnServiceDetailsUpdate = function(objEvent, objThis)
	{
		objThis.ReloadServiceList();
	}
	
	this.ReloadServiceList = function(bolShowSplash)
	{
		bolShowSplash = (bolShowSplash != undefined)? bolShowSplash : false;
	
		// Build a list of all the services that are currently selected
		var arrServices = new Array();
		for (i=0; i < this.arrServiceCheckboxElements.length; i++)
		{
			if (this.arrServiceCheckboxElements[i].checked)
			{
				arrServices.push(parseInt(this.arrServiceCheckboxElements[i].getAttribute('Service')));
			}
		}
		
		// Set up Properties to be sent to AppTemplateProvisioning->RenderServiceList
		var objData = 	{	Account	:	{	
											Id					: this.intAccountId
										},
							List	:	{
											SelectedServices	: arrServices,
											ContainerDivId		: this.strServicesContainerDivId,
											Filter				: this.elmServiceFilterCombo.value
										}
						};
		
		
		Vixen.Ajax.CallAppTemplate("Provisioning", "RenderServiceList", objData, null, bolShowSplash);
	}
}

// Instanciate the object
if (Vixen.ProvisioningPage == undefined)
{
	Vixen.ProvisioningPage = new VixenProvisioningPageClass;
}
