<?php

//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// menu_items.php
//----------------------------------------------------------------------------//
/**
 * menu_items
 *
 * Defines the MenuItems class, which stores all menu items that can be used in the application
 *
 * Defines the MenuItems class, which stores all menu items that can be used in the application
 *
 * @file		menu_items.php
 * @language	PHP
 * @package		ui_app
 * @author		Jared
 * @version		7.07
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// MenuItems
//----------------------------------------------------------------------------//
/**
 * MenuItems
 *
 * Defines the resultant Href for each paricular item that can be included in a menu
 *
 * Defines the resultant Href for each paricular item that can be included in a menu.
 * Each type of menu item (a command in the context menu) should have a method
 * defined here which returns the Href that should be used when the menu item is 
 * clicked.  Alternatively the menu item can be handled by the __call function.
 * You will notice that the menu item "ViewAccount" has been handled both ways as
 * an example of how they work.
 * These menu items can also be expressed as BreadCrumbMenu items, so long as they 
 * set $strLabel to the label that will be displayed for the BreadCrumb.
 *
 * @prefix	mit
 *
 * @package	ui_app
 * @class	MenuItems
 */
class MenuItems
{
	//------------------------------------------------------------------------//
	// strLabel
	//------------------------------------------------------------------------//
	/**
	 * strLabel
	 *
	 * Stores the accompanying label if the last menu item processed can be used as a breadcrumb
	 *
	 * Stores the accompanying label if the last menu item processed can be used as a breadcrumb
	 *
	 * @type		string
	 *
	 * @property
	 */
	public $strLabel;

	//------------------------------------------------------------------------//
	// ViewServiceRatePlan
	//------------------------------------------------------------------------//
	/**
	 * ViewServiceRatePlans()
	 *
	 * Compiles the Href to be executed when the ViewServiceRatePlan menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewServiceRatePlan menu item is clicked
	 * 
	 * @param	int		$intId		id of the service, to view the RatePlan of
	 *
	 * @return	string				Href to be executed when the ViewServiceRatePlan menu item is clicked
	 *
	 * @method
	 */
	function ViewServiceRatePlan($intId)
	{
		return "flex.php/Service/ViewPlan/?Service.Id=$intId";
	}

	//------------------------------------------------------------------------//
	// ViewCustomerGroup
	//------------------------------------------------------------------------//
	/**
	 * ViewCustomerGroup()
	 *
	 * Compiles the Href to be executed when the ViewCustomerGroup menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewCustomerGroup menu item is clicked
	 * 
	 * @param	int		$intId		id of the CustomerGroup
	 *
	 * @return	string				Href to be executed when the ViewCustomerGroup menu item is clicked
	 *
	 * @method
	 */
	function ViewCustomerGroup($intId)
	{
		$this->strLabel	= "Customer Group";
		return "flex.php/CustomerGroup/View/?CustomerGroup.Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// ViewAllCustomerGroups
	//------------------------------------------------------------------------//
	/**
	 * ViewAllCustomerGroups()
	 *
	 * Compiles the Href to be executed when the ViewAllCustomerGroups menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewAllCustomerGroups menu item is clicked
	 * 
	 * @return	string				Href to be executed when the ViewAllCustomerGroups menu item is clicked
	 *
	 * @method
	 */
	function ViewAllCustomerGroups()
	{
		$this->strLabel	= "Customer Groups";
		return "flex.php/CustomerGroup/ViewAll/";
	}

	//------------------------------------------------------------------------//
	// AddCustomerGroup
	//------------------------------------------------------------------------//
	/**
	 * AddCustomerGroup()
	 *
	 * Compiles the Href to be executed when the AddCustomerGroup menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddCustomerGroup menu item is clicked
	 * 
	 * @return	string				Href to be executed when the AddCustomerGroup menu item is clicked
	 *
	 * @method
	 */
	function AddCustomerGroup()
	{
		$this->strLabel	= "Add Customer Group";
		return "flex.php/CustomerGroup/Add/";
	}

	
	//------------------------------------------------------------------------//
	// EmployeeConsole
	//------------------------------------------------------------------------//
	/**
	 * EmployeeConsole()
	 *
	 * Compiles the Href to be executed when the EmployeeConsole menu item is clicked
	 *
	 * Compiles the Href to be executed when the EmployeeConsolet menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string				Href to be executed when the EmployeeConsole menu item is clicked
	 *
	 * @method
	 */
	function EmployeeConsole()
	{
		$this->strLabel	= "Console";
		return "console.php";
	}
	
	//------------------------------------------------------------------------//
	// AddCustomer
	//------------------------------------------------------------------------//
	/**
	 * AddCustomer()
	 *
	 * Compiles the Href to be executed when the AddCustomer menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddCustomer menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string				Href to be executed when the AddCustomer menu item is clicked
	 *
	 * @method
	 */
	function AddCustomer()
	{
		$this->strLabel	= "Add Customer";
		return "account_add.php";
	}
	
	//------------------------------------------------------------------------//
	// FindCustomer
	//------------------------------------------------------------------------//
	/**
	 * FindCustomer()
	 *
	 * Compiles the Href to be executed when the FindCustomer menu item is clicked
	 *
	 * Compiles the Href to be executed when the FindCustomer menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string				Href to be executed when the FindCustomer menu item is clicked
	 *
	 * @method
	 */
	function FindCustomer()
	{
		$this->strLabel	= "Find Customer";
		return "contact_verify.php";
	}
	
	//------------------------------------------------------------------------//
	// AddServices
	//------------------------------------------------------------------------//
	/**
	 * AddServices()
	 *
	 * Compiles the Href to be executed when the AddServices (service add bulk) menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddServices (service add bulk) menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param 	integer		$intAccountId	Id of the account that the services will be added to
	 *
	 * @return	string						Href to be executed when the AddServices menu item is clicked
	 *
	 * @method
	 */
	function AddServices($intAccountId)
	{
		$this->strLabel	= "Add Services";
		return "service_addbulk.php?Account=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// EditContact
	//------------------------------------------------------------------------//
	/**
	 * EditContact()
	 *
	 * Compiles the Href to be executed when the EditContact menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditContact menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the contact to edit
	 *
	 * @return	string				Href to be executed when the EditContact menu item is clicked
	 *
	 * @method
	 */
	function EditContact($intId)
	{
		$this->strLabel	= "contact: $intId";
		return "contact_edit.php?Id=$intId";
	}

	//------------------------------------------------------------------------//
	// ExportInvoiceAsCSV
	//------------------------------------------------------------------------//
	/**
	 * ExportInvoiceAsCSV()
	 *
	 * Compiles the Href to be executed when the ExportInvoiceAsCSV menu item is triggered
	 *
	 * Compiles the Href to be executed when the ExportInvoiceAsCSV menu item is triggered
	 * 
	 * @param	int		$intInvoiceId		id of the Invoice to download as a CSV file
	 *
	 * @return	string						Href to be executed when the ExportInvoiceAsCSV menu item is triggered
	 *
	 * @method
	 */
	function ExportInvoiceAsCSV($intInvoiceId)
	{
		$this->strLabel	= "";
		return "flex.php/Invoice/ExportAsCSV/?Invoice.Id=$intInvoiceId";
	}
	
	//------------------------------------------------------------------------//
	// ViewAllConstants
	//------------------------------------------------------------------------//
	/**
	 * ViewAllConstants()
	 *
	 * Compiles the Href to be executed when the ViewAllConstants menu item is triggered
	 *
	 * Compiles the Href to be executed when the ViewAllConstants menu item is triggered
	 * 
	 * @return	string			Href to be executed when the ViewAllConstants menu item is triggered
	 *
	 * @method
	 */
	function ViewAllConstants()
	{
		$this->strLabel	= "Constants Management";
		return "flex.php/Config/ManageConstants/";
	}
	
	//------------------------------------------------------------------------//
	// SystemSettingsMenu
	//------------------------------------------------------------------------//
	/**
	 * SystemSettingsMenu()
	 *
	 * Compiles the Href to be executed when the SystemSettingsMenu menu item is triggered
	 *
	 * Compiles the Href to be executed when the SystemSettingsMenu menu item is triggered
	 * 
	 * @return	string						Href to be executed when the ExportInvoiceAsCSV menu item is triggered
	 *
	 * @method
	 */
	function SystemSettingsMenu()
	{
		$this->strLabel	= "System Settings";
		return "flex.php/Config/SystemSettingsMenu/";
	}


	//------------------------------------------------------------------------//
	// AddContact
	//------------------------------------------------------------------------//
	/**
	 * AddContact()
	 *
	 * Compiles the Href to be executed when the AddContact menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddContact menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		id of the Account to add the contact to
	 *
	 * @return	string						Href to be executed when the AddContact menu item is clicked
	 *
	 * @method
	 */
	function AddContact($intAccountId)
	{
		$this->strLabel	= "Add Contact";
		return "contact_add.php?Account=$intAccountId";
	}
	
	//------------------------------------------------------------------------//
	// ChangePaymentMethod
	//------------------------------------------------------------------------//
	/**
	 * ChangePaymentMethod()
	 *
	 * Compiles the Href to be executed when the ChangePaymentMethod for Account menu item is clicked
	 *
	 * Compiles the Href to be executed when the ChangePaymentMethod for Account menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		id of the Account associated with this action
	 *
	 * @return	string						Href to be executed when the ChangePaymentMethod for Account menu item is clicked
	 *
	 * @method
	 */
	function ChangePaymentMethod($intAccountId)
	{
		$this->strLabel	= "Change Payment Method";
		return "account_payment.php?Id=$intAccountId";
	}
	
	//------------------------------------------------------------------------//
	// ViewCostCentres
	//------------------------------------------------------------------------//
	/**
	 * ViewCostCentres()
	 *
	 * Compiles the Href to be executed when the ViewCostCentres for Account menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewCostCentres for Account menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		id of the Account associated with this action
	 *
	 * @return	string						Href to be executed when the ViewCostCentres for Account menu item is clicked
	 *
	 * @method
	 */
	function ViewCostCentres($intAccountId)
	{
		$this->strLabel	= "Cost Centres";
		return "costcentre_list.php?Account=$intAccountId";
	}
	
	//------------------------------------------------------------------------//
	// ViewRates
	//------------------------------------------------------------------------//
	/**
	 * ViewRates()
	 *
	 * Compiles the Href to be executed when the ViewRates menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewRates menu item is clicked
	 *
	 * @param	int		
	 *
	 * @return	string						
	 *
	 * @method
	 */
	function ViewRate($intId)
	{
		// Setup data to send
		$arrData['Objects']['Rate']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewRatePopupId\", \"medium\", null, \"Rate\", \"View\", $strJsonCode)";	
	}	

	//------------------------------------------------------------------------//
	// OverrideRateGroup
	//------------------------------------------------------------------------//
	/**
	 * OverrideRateGroup()
	 *
	 * Compiles the Href to be executed when the OverrideRateGroup menu item is triggered
	 *
	 * Compiles the Href to be executed when the OverrideRateGroup menu item is triggered
	 *
	 * @param	int		$intServiceId		Id of the Service of which you want to override one of the rate groups
	 * @param 	int		$intRecordTypeId	Id of the RecordType which will be overridden
	 *
	 * @return	string						
	 *
	 * @method
	 */
	function OverrideRateGroup($intServiceId, $intRecordTypeId)
	{
		// Setup data to send
		$arrData['Objects']['Service']['Id']	= $intServiceId;
		$arrData['Objects']['RecordType']['Id']	= $intRecordTypeId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RateGroupOverridePopupId\", \"medium\", \"Override Rate Group\", \"RateGroup\", \"Override\", $strJsonCode)";	
	}


	//------------------------------------------------------------------------//
	// AddConfigConstant
	//------------------------------------------------------------------------//
	/**
	 * AddConfigConstant()
	 *
	 * Compiles the Href to be executed when the AddConfigConstant menu item is triggered
	 *
	 * Compiles the Href to be executed when the AddConfigConstant menu item is triggered
	 *
	 * @param	int		$intConstantGroupId		id of the ConstantGroup that the 
	 *											new constant will belong to.
	 * @return	string						
	 *
	 * @method
	 */
	function AddConfigConstant($intConstantGroupId)
	{
		// Setup data to send
		$arrData['Objects']['ConfigConstantGroup']['Id'] = $intConstantGroupId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddConfigConstantPopupId\", \"medium\", \"Add Constant\", \"Config\", \"EditConstant\", $strJsonCode)";	
	}

	//------------------------------------------------------------------------//
	// EditConfigConstant
	//------------------------------------------------------------------------//
	/**
	 * EditConfigConstant()
	 *
	 * Compiles the Href to be executed when the EditConfigConstant menu item is triggered
	 *
	 * Compiles the Href to be executed when the EditConfigConstant menu item is triggered
	 *
	 * @param	int		$intConstantId		id of the constant to edit
	 *
	 * @return	string						
	 *
	 * @method
	 */
	function EditConfigConstant($intConstantId)
	{
		// Setup data to send
		$arrData['Objects']['ConfigConstant']['Id'] = $intConstantId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"EditConfigConstantPopupId\", \"medium\", \"Edit Constant\", \"Config\", \"EditConstant\", $strJsonCode)";	
	}


	//------------------------------------------------------------------------//
	// EditService
	//------------------------------------------------------------------------//
	/**
	 * EditService()
	 *
	 * Compiles the Href to be executed when the EditService menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditService menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId						id of the service to view
	 *
	 * @return	string								Href to be executed when the EditService menu item is clicked
	 *
	 * @method
	 */
	function EditService($intId)
	{
		// Setup data to send
		$arrData['Objects']['Service']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"EditServicePopupId\", \"medium\", null, \"Service\", \"Edit\", $strJsonCode)";	
	}	

	//------------------------------------------------------------------------//
	// AddService
	//------------------------------------------------------------------------//
	/**
	 * AddService()
	 *
	 * Compiles the Href to be executed when the AddService menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddService menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId						id of the Account, that the service will be associated with
	 *
	 * @return	string								Href to be executed when the AddService menu item is clicked
	 *
	 * @method
	 */
	function AddService($intId)
	{
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddServicePopupId\", \"medium\", null, \"Service\", \"Add\", $strJsonCode)";	
	}
	
	//------------------------------------------------------------------------//
	// ImportRateGroup
	//------------------------------------------------------------------------//
	/**
	 * ImportRateGroup()
	 *
	 * Compiles the Href to be executed when the ImportRateGroup menu item is clicked
	 *
	 * Compiles the Href to be executed when the ImportRateGroup menu item is clicked
	 *
	 * @param	int		$intRecordTypeId	id of the RecordType, of which you want to import a RateGroup of
	 * @param	boolean	$bolIsFleet			TRUE if you want to import the RateGroup as a fleet RateGroup, else FALSE for normal RateGroup importing		
	 *
	 * @return	string						Href to be executed when the ImportRateGroup menu item is clicked
	 *
	 * @method
	 */
	function ImportRateGroup($intRecordTypeId, $bolIsFleet)
	{
		// Setup data to send
		$arrData['Objects']['RecordType']['Id']		= $intRecordTypeId;
		$arrData['Objects']['RateGroup']['Fleet']	= $bolIsFleet;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ImportRateGroupPopupId\", \"large\", null, \"RateGroup\", \"Import\", $strJsonCode)";	
		//return "javascript:Vixen.Popup.Alert(\"RateGroup import functionality has not been implemented yet\")";
	}
	
	//------------------------------------------------------------------------//
	// ChangePlan
	//------------------------------------------------------------------------//
	/**
	 * ChangePlan()
	 *
	 * Compiles the Href to be executed when the ChangePlan menu item is clicked
	 *
	 * Compiles the Href to be executed when the ChangePlan menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the service to change the plan on
	 *
	 * @return	string				Href to be executed when the ChangePlan menu item is clicked
	 *
	 * @method
	 */
	function ChangePlan($intId)
	{
		//$this->strLabel	= "rates list";
		
		// Setup data to send
		$arrData['Objects']['Service']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ChangePlanPopupId\", \"medium\", null, \"Service\", \"ChangePlan\", $strJsonCode)";	
	}	

	//------------------------------------------------------------------------//
	// ViewContact
	//------------------------------------------------------------------------//
	/**
	 * ViewContact()
	 *
	 * Compiles the Href to be executed when the ViewContact menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewContact menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intContactId		id of the contact to view
	 *
	 * @return	string						Href to be executed when the ViewContact menu item is clicked
	 *
	 * @method
	 */
	function ViewContact($intContactId)
	{
		$this->strLabel	= "Contact: $intContactId";
		return "contact_view.php?Id=$intContactId";
	}
	
	//------------------------------------------------------------------------//
	// ListContacts
	//------------------------------------------------------------------------//
	/**
	 * ListContacts()
	 *
	 * Compiles the Href to be executed when the ListContacts menu item is clicked
	 *
	 * Compiles the Href to be executed when the ListContacts menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		id of the Account to view the Contacts of
	 *
	 * @return	string						Href to be executed when the ListContacts menu item is clicked
	 *
	 * @method
	 */
	function ListContacts($intAccountId)
	{
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intAccountId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AccountContactsPopupId\", \"large\", null, \"Account\", \"ViewContacts\", $strJsonCode)";
	}
	
	
	
	//------------------------------------------------------------------------//
	// ViewService
	//------------------------------------------------------------------------//
	/**
	 * ViewService()
	 *
	 * Compiles the Href to be executed when the ViewService menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewService menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the service to view
	 * @param	int		$strFNN		[optional] FNN of the service to view
	 *
	 * @return	string				Href to be executed when the ViewService menu item is clicked
	 *
	 * @method
	 */
	function ViewService($intId, $strFNN=NULL)
	{
		$this->strLabel	= "Service";//: $strFNN";
		return "flex.php/Service/View/?Service.Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// InvoicesAndPayments
	//------------------------------------------------------------------------//
	/**
	 * InvoicesAndPayments()
	 *
	 * Compiles the Href to be executed when the InvoicesAndPayments menu item is clicked
	 *
	 * Compiles the Href to be executed when the InvoicesAndPayments menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account to view
	 *
	 * @return	string				Href to be executed when the InvoicesAndPayments menu item is clicked
	 *
	 * @method
	 */
	function InvoicesAndPayments($intId)
	{
		$this->strLabel	= "Invoices and Payments";

		return "flex.php/Account/InvoicesAndPayments/?Account.Id=$intId";
	}
	
	//------------------------------------------------------------------------//
	// AccountOverview
	//------------------------------------------------------------------------//
	/**
	 * AccountOverview()
	 *
	 * Compiles the Href to be executed when the AccountOverview menu item is clicked
	 *
	 * Compiles the Href to be executed when the AccountOverview menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		id of the account to view
	 *
	 * @return	string						Href to be executed when the AccountOverview menu item is clicked
	 *
	 * @method
	 */
	function AccountOverview($intAccountId)
	{
		$this->strLabel	= "Account";

		return "flex.php/Account/Overview/?Account.Id=$intAccountId";
	}
	
	//------------------------------------------------------------------------//
	// EditEmployee
	//------------------------------------------------------------------------//
	/**
	 * EditEmployee()
	 *
	 * Compiles the Href to be executed when the EditEmployee menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditEmployee menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the Employee to view
	 *
	 * @return	string				Href to be executed when the EditEmployee menu item is clicked
	 *
	 * @method
	 */
	function EditEmployee($intId)
	{
		
		$this->strLabel	= "edit emp: $intId";
		
		// Setup data to send

		//$arrData['HtmlMode'] = TRUE;
		//$arrData['Application'] = "Employee.Edit";
		$arrData['Objects']['Employee']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"Employee{$intId}EditPopup\", \"medium\", \"Edit Employee\", \"Employee\", \"Edit\", $strJsonCode)";

	}
	
	//------------------------------------------------------------------------//
	// AddRateGroupToRatePlan - DEPRECIATED (currently this functionality is explicitly called from javascript)
	//------------------------------------------------------------------------//
	/**
	 * AddRateGroupToRatePlan()
	 *
	 * Compiles the Href to be executed when the AddRateGroupToRatePlan menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddRateGroupToRatePlan menu item is clicked
	 * This will not compile a bread crumb label because the AddRateGroup functionality is in a popup
	 * 
	 * @param	int		$intRecordType		record type that the Rate Group will belong to
	 * @param	bool	$bolFleet			[optional] TRUE if the new Rate Group will be a fleet rate group
	 *
	 * @return	string						Href to be executed when the AddRateGroupToRatePlan menu item is clicked
	 *
	 * @method
	 */
	function AddRateGroupToRatePlan($intRecordType, $bolFleet=FALSE)
	{
		// Setup data to send
		$arrData['Objects']['RecordType']['Id']		= $intRecordType;
		$arrData['Objects']['RateGroup']['Fleet']	= $bolFleet;

		// This is used to flag that the Rate Group will be added to a Rate Plan
		$arrData['Objects']['CallingPage']['AddRatePlan'] = TRUE;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RateGroupPopup\", \"large\", \"Add New Rate Group\", \"RateGroup\", \"Add\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// AddRateGroup
	//------------------------------------------------------------------------//
	/**
	 * AddRateGroup()
	 *
	 * Compiles the Href to be executed when the AddRateGroup menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddRateGroup menu item is clicked
	 * This will not compile a bread crumb label because the AddRateGroup functionality is in a popup
	 * 
	 * @return	string				Href to be executed when the AddRateGroup menu item is clicked
	 *
	 * @method
	 */
	function AddRateGroup()
	{
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RateGroupPopup\", \"large\", \"Add New Rate Group\", \"RateGroup\", \"Add\", \"\", \"modeless\")";
	}

	//------------------------------------------------------------------------//
	// AddRatePlan
	//------------------------------------------------------------------------//
	/**
	 * AddRatePlan()
	 *
	 * Compiles the Href to be executed when the AddRatePlan menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddRatePlan menu item is clicked
	 * 
	 * @param	int		$intBasePlanId			optional, Id of the RatePlan which the new one will be based on
	 * @param	string	$strCallingPageHref		optional, href of the page that calls the AddRatePlan page.
	 *											exiting the AddRatePlan page will relocate the user to this page
	 *
	 * @return	string				Href to be executed when the AddRatePlan menu item is clicked
	 *
	 * @method
	 */
	function AddRatePlan($intBasePlanId = NULL, $strCallingPageHref = NULL)
	{
		$this->strLabel = "Add Plan";
	
		// Setup data to send as GET variables
		$strBasePlan 	= ($intBasePlanId !== NULL) ? "BaseRatePlan.Id=$intBasePlanId" : "";
		$strCallingPage = ($strCallingPageHref !== NULL) ? "CallingPage.Href=$strCallingPageHref" : "";

		if ($intBasePlanId && $strCallingPageHref)
		{
			// Both parameters are set
			$strGetVariables = "?$strBasePlan&$strCallingPage";
		}
		elseif ($intBasePlanId || $strCallingPageHref)
		{
			// Only one of the parameters is specified
			$strGetVariables = "?" . $strBasePlan . $strCallingPage;
		}
		else
		{
			// No parameters have been specified
			$strGetVariables = "";
		}

		return "flex.php/Plan/Add/$strGetVariables";
	}
	
	//------------------------------------------------------------------------//
	// EditRatePlan
	//------------------------------------------------------------------------//
	/**
	 * EditRatePlan()
	 *
	 * Compiles the Href to be executed when the EditRatePlan menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditRatePlan menu item is clicked
	 * 
	 * @param	int		$intPlanId				Id of the RatePlan to edit
	 * @param	string	$strCallingPageHref		optional, href of the page that calls the EditRatePlan page.
	 *											exiting the EditRatePlan page will relocate the user to this page
	 *
	 * @return	string				Href to be executed when the AddRatePlan menu item is clicked
	 *
	 * @method
	 */
	function EditRatePlan($intPlanId, $strCallingPageHref = NULL)
	{
		$this->strLabel = "Edit Plan";
	
		// Setup data to send as GET variables
		$strGetVariables = "RatePlan.Id=$intPlanId";
		if ($strCallingPageHref !== NULL)
		{
			$strGetVariables .= "&CallingPage.Href=$strCallingPageHref";
		}

		return "flex.php/Plan/Add/?$strGetVariables";
	}
	
	//------------------------------------------------------------------------//
	// AvailablePlans
	//------------------------------------------------------------------------//
	/**
	 * AvailablePlans()
	 *
	 * Compiles the Href to be executed when the AvailablePlans menu item is clicked
	 *
	 * Compiles the Href to be executed when the AvailablePlans menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intServiceType	optional, ServiceType used to filter the list of Rate Plans
	 * @return	string					Href to be executed when the AvailablePlans menu item is clicked
	 *
	 * @method
	 */
	function AvailablePlans($intServiceType = 0)
	{
		$this->strLabel = "Available Plans";
		
		// Set up the filter if intServiceType was passed
		$strFilter = ($intServiceType) ? "?RatePlan.ServiceType=$intServiceType" : "";
		
		return "flex.php/Plan/AvailablePlans/$strFilter";
	}
	
	//------------------------------------------------------------------------//
	// ViewPlan
	//------------------------------------------------------------------------//
	/**
	 * ViewPlan()
	 *
	 * Compiles the Href to be executed when the ViewPlan menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewPlan menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intRatePlanId	id of the Rate Plan that you want to view
	 * @return	string					Href to be executed when the ViewPlan menu item is clicked
	 *
	 * @method
	 */
	function ViewPlan($intRatePlanId)
	{
		$this->strLabel = "Plan Details";
		
		return "rates_plan_summary.php?Id=$intRatePlanId";
	}

	//------------------------------------------------------------------------//
	// AddAssociatedAccount
	//------------------------------------------------------------------------//
	/**
	 * AddAssociatedAccount()
	 *
	 * Compiles the Href to be executed when the AddAssociatedAccount menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddAssociatedAccount menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		account number of the calling account
	 *
	 * @return	string					Href to be executed when the AddAssociatedAccount menu item is clicked
	 *
	 * @method
	 */
	function AddAssociatedAccount($intAccountId)
	{
		return "account_add.php?Associated=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// ViewInvoicePdf
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoicePdf()
	 *
	 * Compiles the Href to be executed when the View Invoice Pdf menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoice Pdf menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccount		Account Id of the invoice to view
	 * @param	int		$intYear		year the invoice relates to
	 * @param	int		$intMonth		month the invoice relates to
	 *
	 * @return	string					Href to be executed when the View Invoice Pdf menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoicePDF($intAccount, $intYear, $intMonth)
	{
		$this->strLabel = "pdf acct: $intAccount, $intMonth/$intYear";
		return "invoice_pdf.php?Account=$intAccount&Year=$intYear&Month=$intMonth";
	}
	
	//------------------------------------------------------------------------//
	// ViewInvoice
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoice()
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intInvoice		invoice number of the invoice to view
	 *
	 * @return	string					Href to be executed when the View Invoice menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoice($intInvoice)
	{
		$this->strLabel = "inv: $intInvoice";
		return "invoice_view.php?Invoice=$intInvoice";
	}
	
	//------------------------------------------------------------------------//
	// ViewAccountNotes
	//------------------------------------------------------------------------//
	/**
	 * ViewAccountNotes()
	 *
	 * Compiles the javascript to be executed when the ViewAccountNotes menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ViewAccountNotes menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId	id of the account associated with the notes to view
	 *
	 * @return	string					action to be executed when the ViewAccountNotes menu item is clicked
	 *
	 * @method
	 */
	function ViewAccountNotes($intAccountId)
	{
		$this->strLabel	= "view account notes";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intAccountId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewNotesPopupId\", \"large\", \"Account Notes\", \"Note\", \"View\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// InvoiceAndPayments
	//------------------------------------------------------------------------//
	/**
	 * InvoiceAndPayments()
	 *
	 * Compiles the Href to be executed when the InvoiceAndPayments menu item is clicked
	 *
	 * Compiles the Href to be executed when the InvoiceAndPayments menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account to view
	 							
	 *
	 * @return	string				Href to be executed when the InvoiceAndPayments menu item is clicked
	 *
	 * @method
	 */
	//function InvoiceAndPayments($intId)
	//{
	//	//$this->strLabel	= "acc: $intId";
	//	return "flex.php/Account/InvoicesAndPayments/?Account.Id=$intId";
	//}

	//------------------------------------------------------------------------//
	// ViewServiceDetails
	//------------------------------------------------------------------------//
	/**
	 * ViewServiceDetails()
	 *
	 * Compiles the Href to be executed when the ViewServiceDetails menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewServiceDetails menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the service to view
	 								
	 *
	 * @return	string				Href to be executed when the ViewServiceDetails menu item is clicked
	 *
	 * @method
	 */
	function ViewServiceDetails($intId)
	{
		$this->strLabel	= "acc: $intId";
		return "flex.php/Service/View/?Service.Id=$intId";
	}

	//------------------------------------------------------------------------//
	// ViewUnbilledCharges
	//------------------------------------------------------------------------//
	/**
	 * ViewUnbilledCharges()
	 *
	 * Compiles the javascript to be executed when the ViewUnbilledCharges menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ViewUnbilledCharges menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the service associated with the unbilled charges
	 *
	 * @return	string				action to be executed when the ViewUnbilledCharges menu item is clicked
	 *
	 * Example URL
	 * http://localhost/ross/vixen/intranet_app/service_unbilled.php?Id=1
	 *
	 * @method
	 */
	function ViewUnbilledCharges($intId)
	{
		$this->strLabel	= "view unbilled charges";
		return "service_unbilled.php?Id=$intId";
	}

	//------------------------------------------------------------------------//
	// ChangeOfLessee
	//------------------------------------------------------------------------//
	/**
	 * ChangeOfLessee()
	 *
	 * Compiles the javascript to be executed when the ChangeOfLessee menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ChangeOfLessee menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the service associated
	 *
	 * @return	string				action to be executed when the ChangeOfLessee menu item is clicked
	 *
	 * Example URL
	 * http://localhost/ross/vixen/intranet_app/service_lessee.php?Service=1
	 *
	 * @method
	 */
	function ChangeOfLessee($intId)
	{
		$this->strLabel	= "change of lessee";
		return "service_lessee.php?Service=$intId";
	}

	//------------------------------------------------------------------------//
	// ViewServiceNotes
	//------------------------------------------------------------------------//
	/**
	 * ViewServiceNotes()
	 *
	 * Compiles the javascript to be executed when the ViewServiceNotes menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ViewServiceNotes menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the service associated with the notes to view
	 *
	 * @return	string				action to be executed when the ViewServiceNotes menu item is clicked
	 *
	 * @method
	 */
	function ViewServiceNotes($intId, $strNoteType = NULL)
	{
		$this->strLabel	= "view service notes";
		
		// Setup data to send
		$arrData['Objects']['Service']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewNotesPopupId\", \"large\", \"Service Notes\", \"Note\", \"View\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// ViewContactNotes
	//------------------------------------------------------------------------//
	/**
	 * ViewContactNotes()
	 *
	 * Compiles the javascript to be executed when the ViewContactNotes menu item is clicked
	 *
	 * Compiles the javascript to be executed when the ViewContactNotes menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the contact associated with the notes to view
	 *
	 * @return	string				action to be executed when the ViewContactNotes menu item is clicked
	 *
	 * @method
	 */
	function ViewContactNotes($intId)
	{
		$this->strLabel	= "view contact notes";
		
		// Setup data to send
		$arrData['Objects']['Contact']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewNotesPopupId\", \"large\", \"Contact Notes\", \"Note\", \"View\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddContactNote
	//------------------------------------------------------------------------//
	/**
	 * AddContactNote()
	 *
	 * Compiles the javascript to be executed when the AddContactNote menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddContactNote menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the contact associated with the note to add
	 *
	 * @return	string				action to be executed when the AddContactNotes menu item is clicked
	 *
	 * @method
	 */
	function AddContactNote($intId)
	{
		$this->strLabel	= "Add Contact Note";
		
		// Setup data to send
		$arrData['Objects']['Contact']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddNotePopupId\", \"medium\", \"Add Contact Note\", \"Note\", \"Add\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddAccountNote
	//------------------------------------------------------------------------//
	/**
	 * AddAccountNote()
	 *
	 * Compiles the javascript to be executed when the AddAccountNote menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddAccountNote menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account associated with the note to add
	 *
	 * @return	string				action to be executed when the AddAccountNotes menu item is clicked
	 *
	 * @method
	 */
	function AddAccountNote($intId)
	{
		$this->strLabel	= "Add Account Note";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddNotePopupId\", \"medium\", \"Add Account Note\", \"Note\", \"Add\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddServiceNote
	//------------------------------------------------------------------------//
	/**
	 * AddServiceNote()
	 *
	 * Compiles the javascript to be executed when the AddServiceNote menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddServiceNote menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the service associated with the note to add
	 *
	 * @return	string				action to be executed when the AddServiceNotes menu item is clicked
	 *
	 * @method
	 */
	function AddServiceNote($intId)
	{
		$this->strLabel	= "Add Service Note";
		
		// Setup data to send
		$arrData['Objects']['Service']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddServicePopupId\", \"medium\", \"Add Service Note\", \"Note\", \"Add\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// EmailPDFInvoice
	//------------------------------------------------------------------------//
	/**
	 * EmailPDFInvoice()
	 *
	 * Compiles the javascript to be executed when the EmailPDFInvoice menu item is clicked
	 *
	 * Compiles the javascript to be executed when the EmailPDFInvoice menu item is clicked
	 * 
	 * @param	int		$intId		id of the account associated with the invoice to email
	 * @param	int		$intYear	year part of the date of the invoice to email
	 * @param	int		$intMonth	month part of the date of the invoice to email
	 *
	 * @return	string				action to be executed when the EmailPDFInvoice menu item is clicked
	 *
	 * @method
	 */
	function EmailPDFInvoice($intId, $intYear, $intMonth)
	{
		$this->strLabel	= "email pdf invoice";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		$arrData['Objects']['Invoice']['Year'] = $intYear;
		$arrData['Objects']['Invoice']['Month'] = $intMonth;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"EmailPDFInvoicePopupId\", \"medium\", \"Email PDF Invoice\", \"Invoice\", \"EmailPDFInvoice\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// RatesList
	//------------------------------------------------------------------------//
	/**
	 * RatesList()
	 *
	 * Compiles the javascript to be executed when the RatesList menu item is clicked
	 *
	 * Compiles the javascript to be executed when the RatesList menu item is clicked
	 * 
	 * @param	int		$intId		id of the RatePlan
	 *
	 * @return	string				action to be executed when the RatesList menu item is clicked
	 *
	 * @method
	 */
	function RatesList($intId)
	{
		$this->strLabel	= "rates list";
		
		// Setup data to send
		$arrData['Objects']['RatePlan']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RatesListPopupId\", \"large\", null, \"Plan\", \"RateList\", $strJsonCode)";
	}
	

	//------------------------------------------------------------------------//
	// AddAdjustment
	//------------------------------------------------------------------------//
	/**
	 * AddAdjustment()
	 *
	 * Compiles the javascript to be executed when the AddAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		id of the account that the Adjustment will be added to
	 * @param	int		$intServiceId		[optional] id of the service that the adjustment is associated with
	 *
	 * @return	string				action to be executed when the AddAdjustment menu item is clicked
	 *
	 * @method
	 */
	function AddAdjustment($intAccountId, $intServiceId=NULL)
	{
		$this->strLabel	= "add adjustment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intAccountId;
		$arrData['Objects']['Service']['Id'] = $intServiceId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddAdjustmentPopupId\", \"medium\", \"Adjustment\", \"Adjustment\", \"Add\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// AddRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * AddRecurringAdjustment()
	 *
	 * Compiles the javascript to be executed when the AddRecurringAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddRecurringAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAccountId		id of the account that the Adjustment will be added to
	 * @param	int		$intServiceId		[optional] id of the service that the adjustment is associated with
	 *
	 * @return	string						action to be executed when the AddRecurringAdjustment menu item is clicked
	 *
	 * @method
	 */
	function AddRecurringAdjustment($intAccountId, $intServiceId=NULL)
	{
		$this->strLabel	= "add recurring adjustment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intAccountId;
		$arrData['Objects']['Service']['Id'] = $intServiceId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddRecurringAdjustmentPopupId\", \"medium\", \"Recurring Adjustment\", \"Adjustment\", \"AddRecurring\", $strJsonCode)";
	}
	
	
	//------------------------------------------------------------------------//
	// MakePayment
	//------------------------------------------------------------------------//
	/**
	 * MakePayment()
	 *
	 * Compiles the javascript to be executed when the MakePayment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the MakePayment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account that is currently being viewed
	 *
	 * @return	string				action to be executed when the MakePayment menu item is clicked
	 *
	 * @method
	 */
	function MakePayment($intId)
	{
		$this->strLabel	= "make payment";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"MakePaymentPopupId\", \"mediumlarge\", \"Payment\", \"Payment\", \"Add\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// EditAccount
	//------------------------------------------------------------------------//
	/**
	 * EditAccount()
	 *
	 * Compiles the javascript to be executed when the Edit Account menu item is clicked
	 *
	 * Compiles the javascript to be executed when the Edit Account menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the account to edit
	 *
	 * @return	string	action to be executed when the Edit Account menu item is clicked
	 *
	 * @method
	 */
	function EditAccount($intId)
	{
		$this->strLabel	= "edit account";
		
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewAccountPopupId\", \"large\", null, \"Account\", \"ViewDetails\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// ListServices
	//------------------------------------------------------------------------//
	/**
	 * ListServices()
	 *
	 * Compiles the javascript to be executed when the List Service menu item is clicked
	 *
	 * Compiles the javascript to be executed when the List Service menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intId		id of the Account to view services of
	 *
	 * @return	string	action to be executed when the List Service menu item is clicked
	 *
	 * @method
	 */
	function ListServices($intId)
	{	
		/*  The Old Way of open up the list of services in a popup.  Retain this as it will be eventually used again
		// Setup data to send
		$arrData['Objects']['Account']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AccountServicesPopupId\", \"ExtraLarge\", null, \"Account\", \"ViewServices\", $strJsonCode)";
		*/
		
		// View the list of Services as a page
		return "flex.php/Account/ViewServices/?Account.Id=$intId";
	}

	//------------------------------------------------------------------------//
	// DeletePayment
	//------------------------------------------------------------------------//
	/**
	 * DeletePayment()
	 *
	 * Compiles the javascript to be executed when the DeletePayment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeletePayment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intPaymentId		id of the payment to delete
	 *
	 * @return	string						action to be executed when the DeletePayment menu item is clicked
	 *
	 * @method
	 */
	function DeletePayment($intPaymentId)
	{
		$this->strLabel	= "delete payment: $intPaymentId";
		
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "Payment";
		$arrData['Objects']['Payment']['Id'] = $intPaymentId;
		
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeletePaymentPopupId\", \"medium\", \"Reverse Payment\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeleteAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteAdjustment()
	 *
	 * Compiles the javascript to be executed when the DeleteAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeleteAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intAdjustmentId		id of the adjustment to delete
	 *
	 * @return	string							action to be executed when the DeleteAdjustment menu item is clicked
	 *
	 * @method
	 */
	function DeleteAdjustment($intAdjustmentId)
	{
		$this->strLabel	= "delete adjustment: $intAdjustmentId";
				
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "Adjustment";
		$arrData['Objects']['Charge']['Id'] = $intAdjustmentId;
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteAdjustmentPopupId\", \"medium\", \"Delete Adjustment\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// DeleteRecurringAdjustment
	//------------------------------------------------------------------------//
	/**
	 * DeleteRecurringAdjustment()
	 *
	 * Compiles the javascript to be executed when the DeleteRecurringAdjustment menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeleteRecurringAdjustment menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @param	int		$intRecurringAdjustmentId		id of the recurring adjustment to delete
	 *
	 * @return	string									action to be executed when the DeleteRecurringAdjustment menu item is clicked
	 *
	 * @method
	 */
	function DeleteRecurringAdjustment($intRecurringAdjustmentId)
	{
		$this->strLabel	= "delete recurring adjustment: $intRecurringAdjustmentId";
		
		// Setup data to send
		$arrData['Objects']['DeleteRecord']['RecordType'] = "RecurringAdjustment";
		$arrData['Objects']['RecurringCharge']['Id'] = $intRecurringAdjustmentId;
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteRecurringAdjustmentPopupId\", \"medium\", \"Cancel Recurring Adjustment\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddProvisioning
	//------------------------------------------------------------------------//
	/**
	 * AddProvisioning()
	 *
	 * Compiles the Href to be executed when the AddProvisioning menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddProvisioning menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @return	string				Href to be executed when the AddProvisioning menu item is clicked
	 *
	 * @method
	 */
	function Provisioning($intServiceId)
	{
		$this->strLabel = "AddProvisioning";
		return "service_address.php?Service=$intServiceId";
	}	

	//------------------------------------------------------------------------//
	// KnowledgeBase
	//------------------------------------------------------------------------//
	/**
	 * KnowledgeBase()
	 *
	 * Compiles the Href to be executed when the KnowledgeBase menu item is clicked
	 *
	 * Compiles the Href to be executed when the KnowledgeBase menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @return	string				Href to be executed when the KnowledgeBase menu item is clicked
	 *
	 * @method
	 */
	function KnowledgeBase()
	{
		$this->strLabel = "Knowledge Base";
		return "flex.php/KnowledgeBase/ListArticles/";
	}	

	//------------------------------------------------------------------------//
	// ViewKnowledgeBaseArticle
	//------------------------------------------------------------------------//
	/**
	 * ViewKnowledgeBaseArticle()
	 *
	 * Compiles the Href to be executed when the ViewKnowledgeBaseArticle menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewKnowledgeBaseArticle menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int			$intId		id of the knowledge base article to view
	 *
	 * @return	string					Href to be executed when the ViewKnowledgeBaseArticle menu item is clicked
	 *
	 * @method
	 */
	function ViewKnowledgeBaseArticle($intId)
	{
		$this->strLabel = "Article";
		return "flex.php/KnowledgeBase/ViewArticle/?KnowledgeBase.Id=$intId";
	}


	//------------------------------------------------------------------------//
	// BreadCrumb
	//------------------------------------------------------------------------//
	/**
	 * BreadCrumb()
	 *
	 * Compiles the passed menu item as a breadcrumb to be used in the breadcrumb menu
	 *
	 * Compiles the passed menu item as a breadcrumb to be used in the breadcrumb menu
	 * Any menu item can be used as a breadcrumb so long as it defines a value for 
	 * the public data attribute $strLabel
	 *
	 * @param	string	$strName	Name of the menu item to be used as a breadcrumb
	 *								ie "ViewAccount" or "View_Account"
	 * @param	array	$arrParams	Parameters to be passed to the MenuItem method associated
	 *								with $strName
	 *
	 * @return	array				['Href'] 	= Href to be executed when the breadcrumb is clicked
	 *								['Label'] 	= breadcrumb's label
	 *
	 * @method
	 */
	function BreadCrumb($strName, $arrParams)
	{
		$this->strLabel = NULL;
		$arrReturn = Array();
		$strName = str_replace('_', '', $strName);
		
		// call the menu item method specific to $strName
		$arrReturn['Href'] = call_user_func_array(array($this, $strName), $arrParams);
		
		if (!$this->strLabel)
		{
			// the menu item cannot be used as a breadcrumb
			return FALSE;
		}
		$arrReturn['Label'] = $this->strLabel;
		
		return $arrReturn;
	}
	
	//------------------------------------------------------------------------//
	// __call
	//------------------------------------------------------------------------//
	/**
	 * __call()
	 *
	 * Handles all menu items that have not had a specific method defined in this class
	 *
	 * Handles all menu items that have not had a specific method defined in this class
	 * 
	 * @param	string		$strName		name of the menu item
	 * @param	array		$arrParams		any parameters defined for the menu item
	 *
	 * @return	string						the Href to be executed when menu item is clicked
	 *
	 * @method
	 */
	function __call($strName, $arrParams)
	{
		switch ($strName)
		{
			case "Logout":
				return "logout.php";
				break;
			case "AdminConsole":
				$this->strLabel = "Admin Console";
				return "console_admin.php";
				break;
			default;
				return "[insert generic HREF here]";
				
				break;
		}
	}
}

?>
