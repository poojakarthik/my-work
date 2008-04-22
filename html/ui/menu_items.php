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
	// strContextMenuLabel
	//------------------------------------------------------------------------//
	/**
	 * strContextMenuLabel
	 *
	 * Stores the accompanying label for use with the ContextMenu
	 *
	 * Stores the accompanying label for use with the ContextMenu
	 *
	 * @type		string
	 *
	 * @property
	 */
	public $strContextMenuLabel;

	
	//------------------------------------------------------------------------//
	// GetLabel
	//------------------------------------------------------------------------//
	/**
	 * GetLabel()
	 *
	 * Returns the label for this menu item or NULL if not set
	 *
	 * Returns the label for this menu item or NULL if not set
	 * 
	 * @return	string	the label for this menu item or NULL if not set
	 *
	 * @method
	 */
	function GetLabel()
	{
		if (isset($this->strLabel))
		{
			return $this->strLabel;
		}
		return NULL;
	}
	
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
		$this->strContextMenuLabel = "View Plan";
		$this->strLabel = "Plan";
		return "flex.php/Service/ViewPlan/?Service.Id=$intId";
	}

	//------------------------------------------------------------------------//
	// ViewDocumentTemplateHistory
	//------------------------------------------------------------------------//
	/**
	 * ViewDocumentTemplateHistory()
	 *
	 * Compiles the Href for the Document Template History webpage
	 *
	 * Compiles the Href for the Document Template History webpage
	 * 
	 * @param	int		$intCustomerGroup	id of the CustomerGroup
	 * @param	int		$intTemplateType	DocumentTemplateType Id
	 *
	 * @return	string						Href
	 *
	 * @method
	 */
	function ViewDocumentTemplateHistory($intCustomerGroup, $intTemplateType)
	{
		$this->strLabel	= "Template History";
		$this->strContextMenuLabel = "Template History";
		return "flex.php/CustomerGroup/ViewDocumentTemplateHistory/?CustomerGroup.Id=$intCustomerGroup&DocumentTemplateType.Id=$intTemplateType";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
		return "console.php";
	}
	
	//------------------------------------------------------------------------//
	// EmployeeList
	//------------------------------------------------------------------------//
	/**
	 * EmployeeConsole()
	 *
	 * Compiles the Href to be executed when the EmployeeList menu item is clicked
	 *
	 * Compiles the Href to be executed when the EmployeeList menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string				Href to be executed when the EmployeeList menu item is clicked
	 *
	 * @method
	 */
	function EmployeeList()
	{
		$this->strLabel	= "List Employees";
		$this->strContextMenuLabel = "";
		return "flex.php/Employee/EmployeeList/";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "Add Services";
		return "flex.php/Service/BulkAdd/?Account.Id=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// AddServices2
	//------------------------------------------------------------------------//
	/**
	 * AddServices2()
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
	function AddServices2($intAccountId)
	{
		$this->strLabel	= "Old Add Services";
		$this->strContextMenuLabel = "Old Add Services";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
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
		$this->strContextMenuLabel = "";
		return "costcentre_list.php?Account=$intAccountId";
	}
	
	//------------------------------------------------------------------------//
	// ViewRecentCustomers
	//------------------------------------------------------------------------//
	/**
	 * ViewRecentCustomers()
	 *
	 * Compiles the Href to be executed when the ViewRecentCustomers menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewRecentCustomers menu item is clicked
	 *
	 * @return	string						
	 *
	 * @method
	 */
	function ViewRecentCustomers()
	{
		$this->strContextMenuLabel = "View Recent Customers";
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewRecentCustomersId\", \"large\", \"Recent Customers\", \"Employee\", \"ViewRecentCustomers\")";
	}
	
	//------------------------------------------------------------------------//
	// ViewRate
	//------------------------------------------------------------------------//
	/**
	 * ViewRate()
	 *
	 * Compiles the Href to be executed when the ViewRates menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewRates menu item is clicked
	 *
	 * @param	int		$intRateId		Id of the Rate
	 * @param	bool	$bolModal		optional, Set to FALSE for non-modal window
	 *									Defaults to TRUE (modal)
	 *
	 * @return	string						
	 *
	 * @method
	 */
	function ViewRate($intRateId, $bolModal=TRUE)
	{
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['Rate']['Id'] = $intRateId;
		
		$strWindowType = ($bolModal)? "modal" : "nonmodal";
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewRatePopupId_$intRateId\", \"medium\", \"Rate\", \"Rate\", \"View\", $strJsonCode, \"$strWindowType\")";	
	}	

	//------------------------------------------------------------------------//
	// ViewProvisioningHistory
	//------------------------------------------------------------------------//
	/**
	 * ViewProvisioningHistory()
	 *
	 * Compiles the Href to be executed when the ViewProvisioningHistory functionality is triggered
	 *
	 * Compiles the Href to be executed when the ViewProvisioningHistory functionality is triggered
	 * Only one of $intAccountId and $intServiceId should be set.  The other should be NULL
	 *
	 * @param	int		$intServiceId	optional, Id of the Service
	 * @param	int		$intAccountId	optional, Id of the Account
	 *
	 * @return	string						
	 *
	 * @method
	 */
	function ViewProvisioningHistory($intServiceId=NULL, $intAccountId=NULL)
	{
		$this->strContextMenuLabel = "View History";
		
		if ($intServiceId == NULL && $intAccountId == NULL)
		{
			throw new Exception("Must specify an AccountId or ServiceId");
		}
		
		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;
		$arrData['Account']['Id'] = $intAccountId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ProvisioningHistoryPopupId\", \"ExtraLarge\", \"History\", \"Provisioning\", \"ViewHistory\", $strJsonCode)";
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
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['Service']['Id']	= $intServiceId;
		$arrData['RecordType']['Id']	= $intRecordTypeId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RateGroupOverridePopupId\", \"medium\", \"Override Rate Group\", \"RateGroup\", \"Override\", $strJsonCode)";	
	}

	//------------------------------------------------------------------------//
	// ViewRateGroup
	//------------------------------------------------------------------------//
	/**
	 * ViewRateGroup()
	 *
	 * Compiles the Href to be executed when the ViewRateGroup menu item is triggered
	 *
	 * Compiles the Href to be executed when the ViewRateGroup menu item is triggered
	 *
	 * @param	int		$intRateGroupId		Id of the RateGroup
	 * @param	bool	$bolModal		optional, Set to FALSE for non-modal window
	 *									Defaults to TRUE (modal)
	 *
	 * @return	string						
	 *
	 * @method
	 */
	function ViewRateGroup($intRateGroupId, $bolModal=TRUE)
	{
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['RateGroup']['Id'] = $intRateGroupId;
		
		$strWindowType = ($bolModal)? "modal" : "nonmodal";
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"RateGroupViewPopupId_$intRateGroupId\", \"mediumlarge\", \"Rate Group\", \"RateGroup\", \"View\", $strJsonCode, \"$strWindowType\")";
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
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['ConfigConstantGroup']['Id'] = $intConstantGroupId;
		
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
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['ConfigConstant']['Id'] = $intConstantId;
		
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
		$this->strContextMenuLabel = "Edit Service";
		
		// Setup data to send
		$arrData['Service']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['RecordType']['Id']		= $intRecordTypeId;
		$arrData['RateGroup']['Fleet']	= $bolIsFleet;
		
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
		$this->strContextMenuLabel = "";
		
		
		// Setup data to send
		$arrData['Service']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;
		
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
		$this->strContextMenuLabel = "Service Details";
		
		$this->strLabel	= "Service";//: $strFNN";
		return "flex.php/Service/View/?Service.Id=$intId";
	}
	
	
	//------------------------------------------------------------------------//
	// ViewServiceAddress
	//------------------------------------------------------------------------//
	/**
	 * ViewServiceAddress()
	 *
	 * Compiles the Href to be executed when the ViewServiceAddress popup is triggered
	 *
	 * Compiles the Href to be executed when the ViewServiceAddress popup is triggered
	 *
	 * @param	int		$intServiceId		id of the service to view
	 *
	 * @return	string						Href to be executed
	 *
	 * @method
	 */
	function ViewServiceAddress($intServiceId)
	{
		$this->strContextMenuLabel = "View Address Details";
		
		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ServiceAddressPopupId\", \"MediumLarge\", \"Address Details\", \"Service\", \"ViewAddress\", $strJsonCode)";
	}
	
	//------------------------------------------------------------------------//
	// EditServiceAddress
	//------------------------------------------------------------------------//
	/**
	 * EditServiceAddress()
	 *
	 * Compiles the Href to be executed when the EditServiceAddress popup is triggered
	 *
	 * Compiles the Href to be executed when the EditServiceAddress popup is triggered
	 *
	 * @param	int		$intServiceId		id of the service
	 *
	 * @return	string						Href to be executed
	 *
	 * @method
	 */
	function EditServiceAddress($intServiceId)
	{
		$this->strContextMenuLabel = "Edit Address Details";
		
		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ServiceAddressPopupId\", \"ExtraLarge\", \"Address Details\", \"Service\", \"EditAddress\", $strJsonCode)";
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "Overview";
		
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
	 * @param	int		$intId		id of the Employee to edit
	 *
	 * @return	string				Href to be executed when the EditEmployee menu item is clicked
	 *
	 * @method
	 */
	function EditEmployee($intId, $strUserName)
	{
		$this->strContextMenuLabel = "";
		
		$this->strLabel	= "emp: $strUserName";
		
		// Setup data to send

		$arrData['Employee']['Id'] = $intId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"Employee{$intId}EditPopup\", \"medium\", \"Employee\", \"Employee\", \"Edit\", $strJsonCode)";
	}
		
	//------------------------------------------------------------------------//
	// AddEmployee
	//------------------------------------------------------------------------//
	/**
	 * AddEmployee()
	 *
	 * Compiles the Href to be executed when the AddEmployee menu item is clicked
	 *
	 * Compiles the Href to be executed when the AddEmployee menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 * 
	 * @return	string				Href to be executed when the AddEmployee menu item is clicked
	 *
	 * @method
	 */
	function AddEmployee()
	{
		$this->strContextMenuLabel = "";
		
		$this->strLabel	= "emp: new";
		
		// Setup data to send

		$arrData['Employee']['Id'] = -1;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"Employee{$intId}AddPopup\", \"medium\", \"Employee\", \"Employee\", \"Create\", $strJsonCode)";
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "";
		
		$this->strLabel = "Plan Details";
		
		return "flex.php/Plan/View/?RatePlan.Id=$intRatePlanId";
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "View Notes";
		
		$this->strLabel	= "view account notes";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		//return "javascript:ShowAjaxPopup('ViewNotes', medium, Note.View, $strJsonCode)";
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewNotesPopupId\", \"large\", \"Account Notes\", \"Note\", \"View\", $strJsonCode)";
	}


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
		$this->strContextMenuLabel = "";
		
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
	 * @method
	 */
	function ViewUnbilledCharges($intId)
	{
		$this->strContextMenuLabel = "";
		
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
	 * @method
	 */
	function ChangeOfLessee($intId)
	{
		$this->strContextMenuLabel = "Change Lessee";
		
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
		$this->strContextMenuLabel = "View Notes";
		
		$this->strLabel	= "view service notes";
		
		// Setup data to send
		$arrData['Service']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "View Notes";
		
		$this->strLabel	= "view contact notes";
		
		// Setup data to send
		$arrData['Contact']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "Add Note";
		
		$this->strLabel	= "Add Contact Note";
		
		// Setup data to send
		$arrData['Contact']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "Add Note";
		
		$this->strLabel	= "Add Account Note";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "Add Note";
		
		$this->strLabel	= "Add Service Note";
		
		// Setup data to send
		$arrData['Service']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "";
		
		$this->strLabel	= "email pdf invoice";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intId;
		$arrData['Invoice']['Year'] = $intYear;
		$arrData['Invoice']['Month'] = $intMonth;
		
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"EmailPDFInvoicePopupId\", \"medium\", \"Email Invoice PDF\", \"Invoice\", \"EmailPDFInvoice\", $strJsonCode)";
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
		$this->strContextMenuLabel = "";
		
		$this->strLabel	= "add adjustment";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;
		$arrData['Service']['Id'] = $intServiceId;
		
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
		$this->strContextMenuLabel = "";
		
		$this->strLabel	= "add recurring adjustment";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;
		$arrData['Service']['Id'] = $intServiceId;
		
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
		$this->strContextMenuLabel = "";
		
		$this->strLabel	= "make payment";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "Edit";
		
		$this->strLabel	= "edit account";
		
		// Setup data to send
		$arrData['Account']['Id'] = $intId;
		
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
		$this->strContextMenuLabel = "";
		
		/*  The Old Way of open up the list of services in a popup.  Retain this as it will be eventually used again
		// Setup data to send
		$arrData['Account']['Id'] = $intId;
		
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
		$arrData['DeleteRecord']['RecordType'] = "Payment";
		$arrData['Payment']['Id'] = $intPaymentId;
		
				
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
		$arrData['DeleteRecord']['RecordType'] = "Adjustment";
		$arrData['Charge']['Id'] = $intAdjustmentId;
				
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
		$arrData['DeleteRecord']['RecordType'] = "RecurringAdjustment";
		$arrData['RecurringCharge']['Id'] = $intRecurringAdjustmentId;
				
		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);
		
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteRecurringAdjustmentPopupId\", \"medium\", \"Cancel Recurring Adjustment\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// Provisioning
	//------------------------------------------------------------------------//
	/**
	 * Provisioning()
	 *
	 * Compiles the Href to be executed when the Provisioning action is triggered
	 *
	 * Compiles the Href to be executed when the Provisioning action is triggered
	 *
	 * @param	int		$intServiceId	optional, Id of the service which will be provisioned
	 * @param	int		$intAccountId	optional, Id of the account to bulk provision.  If $intServiceId
	 *									is set then it will override this
	 *
	 * @return	string				Href to be executed
	 * @method
	 */
	function Provisioning($intServiceId=NULL, $intAccountId=NULL)
	{
		$this->strContextMenuLabel = "Make Request";
		
		if ($intServiceId == NULL && $intAccountId == NULL)
		{
			throw new Exception("Must specify an AccountId or ServiceId");
		}
		
		if ($intServiceId)
		{
			$strParameter = "Service.Id=$intServiceId";
		}
		else
		{
			$strParameter = "Account.Id=$intAccountId";
		}

		$this->strLabel = "Provisioning";
		return "flex.php/Provisioning/BulkProvisioningRequest/?$strParameter";
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
		$this->strContextMenuLabel = "";
		
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
		$this->strContextMenuLabel = "";
		
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
	// ContextMenuItemLabel
	//------------------------------------------------------------------------//
	/**
	 * ContextMenuItemLabel()
	 *
	 * Retrieves the Label to use for the Menu Item, when used in the context menu
	 *
	 * Retrieves the Label to use for the Menu Item, when used in the context menu
	 *
	 * @param	string	$strName	Name of the menu item
	 *								ie "ViewAccount" or "View_Account"
	 * @param	array	$arrParams	Parameters to be passed to the MenuItem method associated
	 *								with $strName
	 *
	 * @return	string				the Context Menu Item Label
	 * @method
	 */
	function ContextMenuItemLabel($strName, $arrParams)
	{
		$this->strContextMenuLabel = "";

		// call the menu item method specific to $strName
		call_user_func_array(array($this, $strName), $arrParams);
		
		if ($this->strContextMenuLabel == "")
		{
			// The Menu Item function did not specify a Context Menu Label
			return NULL;
		}
		
		return $this->strContextMenuLabel;
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
				return "flex.php/Employee/Logout/";
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
