<?php


//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// MenuItems.php
//----------------------------------------------------------------------------//
/**
 * MenuItems
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
class MenuItems {
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

	const OLD_FRAMEWORK = "../management/";
	const NEW_FRAMEWORK = "../admin/";

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
	function GetLabel() {
		if (isset ($this->strLabel)) {
			return $this->strLabel;
		}
		return NULL;
	}

	//------------------------------------------------------------------------//
	// EmployeeMessageManagement
	//------------------------------------------------------------------------//
	/**
	//------------------------------------------------------------------------//
	// EmployeeMessageManagement
	//------------------------------------------------------------------------//
	/**
	 * EmployeeMessageManagement()
	 *
	 * Compiles the Href to be executed when the EmployeeMessageManagement functionality is requested
	 *
	 * Compiles the Href to be executed when the EmployeeMessageManagement functionality is requested
	 *
	 * @return	string				Href
	 *
	 * @method
	 */
	function EmployeeMessageManagement()
	{
		$this->strContextMenuLabel = "Daily Message Management";
		$this->strLabel = "Daily Message Management";
		return self :: NEW_FRAMEWORK . "reflex.php/Employee/ManageDailyMessages";
	}

	//------------------------------------------------------------------------//
	// TicketingAdmin
	//------------------------------------------------------------------------//
	/**
	 * TicketingAdmin()
	 *
	 * Compiles the Href to be executed when the TicketingConsole menu item is clicked
	 *
	 * Compiles the Href to be executed when the TicketingConsole menu item is clicked
	 *
	 * @return	string				Href to be executed when the TicketingConsole menu item is clicked
	 *
	 * @method
	 */
	function TicketingAdmin()
	{
		$this->strContextMenuLabel = "Ticketing Administration";
		$this->strLabel = "Ticketing Administration";
		return self :: NEW_FRAMEWORK . "reflex.php/Ticketing/Admin";
	}

	//------------------------------------------------------------------------//
	// TicketingAttachmentTypes
	//------------------------------------------------------------------------//
	/**
	//------------------------------------------------------------------------//
	// TicketingAttachmentTypes
	//------------------------------------------------------------------------//
	/**
	 * TicketingAttachmentTypes()
	 *
	 * Compiles the Href to be executed when the TicketingAttachmentTypes menu item is clicked
	 *
	 * Compiles the Href to be executed when the TicketingAttachmentTypes menu item is clicked
	 *
	 * @return	string				Href to be executed when the TicketingAttachmentTypes menu item is clicked
	 *
	 * @method
	 */
	function TicketingAttachmentTypes()
	{
		$this->strContextMenuLabel = "Ticketing Attachment Types";
		$this->strLabel = "Ticketing Attachment Types";
		return self :: NEW_FRAMEWORK . "reflex.php/Ticketing/AttachmentTypes";
	}

	/**
	 * TicketingConsole()
	 *
	 * Compiles the Href to be executed when the TicketingConsole menu item is clicked
	 *
	 * Compiles the Href to be executed when the TicketingConsole menu item is clicked
	 *
	 * @return	string				Href to be executed when the TicketingConsole menu item is clicked
	 *
	 * @method
	 */
	function TicketingConsole($lastQuery = FALSE)
	{
		$this->strContextMenuLabel = "View All Tickets";
		$this->strLabel = "Tickets";
		$last = $lastQuery ? '/Last' : ($lastQuery === FALSE ? '/All/' : '/');
		return self::NEW_FRAMEWORK . "reflex.php/Ticketing/Tickets{$last}";
	}

	/**
	 * ViewTicketsForAccount()
	 *
	 * Compiles the Href to be executed when the ViewTicketsForAccount functionality is triggered
	 *
	 * Compiles the Href to be executed when the ViewTicketsForAccount functionality is triggered
	 * This is currently implemented in a hack way, by doing a normal ticket quickSearch using the account id as the search string
	 * It can return tickets not associated with the account
	 *
	 * @return	string				Href to be executed when the TicketingConsole menu item is clicked
	 *
	 * @method
	 */
	function ViewTicketsForAccount($intAccountId, $bolLastQuery=FALSE)
	{
		$this->strContextMenuLabel = "View All";
		$this->strLabel = "Tickets";
		$strLast = ($bolLastQuery)? "/Last" : "";

		return self::NEW_FRAMEWORK ."reflex.php/Ticketing/Tickets{$strLast}/?Account=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// TicketingTicket
	//------------------------------------------------------------------------//
	/**
	 * TicketingTicket()
	 *
	 * Compiles the Href to be executed when the TicketingTicket menu item is clicked
	 *
	 * Compiles the Href to be executed when the TicketingTicket menu item is clicked
	 *
	 * @param	int		$ticketId		id of the ticket to view
	 * @param	int		$intAccountId	(Optional, defaults to NULL, meaning no account context), if you want to view the ticket, in the context of a particular account, (should be the account that the ticket belongs to)
	 *
	 * @return	string				Href to be executed when the TicketingTicket menu item is clicked
	 *
	 * @method
	 */
	function TicketingTicket($ticketId, $intAccountId=NULL)
	{
		$this->strContextMenuLabel = "View Ticket $ticketId";
		$this->strLabel = "Ticket " . $ticketId;

		return self :: NEW_FRAMEWORK . "reflex.php/Ticketing/Ticket/$ticketId/View/?Account=$intAccountId";
	}

	//------------------------------------------------------------------------//
	// ViewUserTickets
	//------------------------------------------------------------------------//
	/**
	 * ViewUserTickets()
	 *
	 * Compiles the Href to be executed when the "View User's Tickets" menu item is clicked
	 *
	 * Compiles the Href to be executed when the "View User's Tickets" menu item is clicked
	 *
	 * @return	string				Href to be executed
	 *
	 * @method
	 */
	function ViewUserTickets()
	{
		$this->strContextMenuLabel = "View My Tickets";
		$this->strLabel = "My Tickets";
		return self :: NEW_FRAMEWORK . "reflex.php/Ticketing/Tickets/Mine/";
	}

	//------------------------------------------------------------------------//
	// AddTicket
	//------------------------------------------------------------------------//
	/**
	 * AddTicket()
	 *
	 * Compiles the Href to be executed when the "Add Ticket" functionality is triggered
	 *
	 * Compiles the Href to be executed when the "Add Ticket" functionality is triggered
	 *
	 * @param	int			$intAccountId	OPTIONAL(defaults to NULL) - id of the account to associate the ticket with
	 * @param	int			$intServiceId	OPTIONAL(defaults to NULL) - id of the service to associate the ticket with
	 * @return	string						Href to be executed
	 *
	 * @method
	 */
	function AddTicket($intAccountId=NULL, $intServiceId=NULL)
	{
		$strAccountId = ($intAccountId)? "":"";
		$arrGetVars = array();
		if ($intAccountId)
		{
			$arrGetVars[] = "accountId={$intAccountId}";
			$arrGetVars[] = "Account={$intAccountId}";
		}
		if ($intServiceId)
		{
			$arrGetVars[] = "serviceId[]={$intServiceId}";
		}

		$strGetVars = count($arrGetVars)? "?". implode('&', $arrGetVars) : "";

		$this->strContextMenuLabel = "Add New Ticket";
		$this->strLabel = "New Ticket";
		return self :: NEW_FRAMEWORK . "reflex.php/Ticketing/Ticket/Create/{$strGetVars}";
	}

	//------------------------------------------------------------------------//
	// TicketingSummaryReport
	//------------------------------------------------------------------------//
	/**
	 * TicketingSummaryReport()
	 *
	 * Compiles the Href to be executed when the TicketingSummaryReport menu item is clicked
	 *
	 * Compiles the Href to be executed when the TicketingSummaryReport menu item is clicked
	 *
	 * @param	bool	$bolRetrieveCachedReport	optional, defaults to false. Set to true to retrieve the last report generated, which is stored in the user's session object
	 * @return	string								Href to be executed when the TicketingSummaryReport menu item is clicked
	 *
	 * @method
	 */
	function TicketingSummaryReport($bolRetrieveCachedReport = FALSE)
	{
		$this->strContextMenuLabel = "Summary";
		$this->strLabel = "Summary";
		return self :: NEW_FRAMEWORK . "reflex.php/Ticketing/SummaryReport" . (($bolRetrieveCachedReport) ? "/GetReport" : "");
	}

	//------------------------------------------------------------------------//
	// ManageCustomerStatuses
	//------------------------------------------------------------------------//
	/**
	 * ManageCustomerStatuses()
	 *
	 * Compiles the Href to be executed when the ManageCustomerStatuses menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageCustomerStatuses menu item is triggered
	 *
	 * @return	string			Href
	 *
	 * @method
	 */
	function ManageCustomerStatuses()
	{
		$this->strContextMenuLabel = "Customer Statuses";
		$this->strLabel = "Customer Statuses";
		return self :: NEW_FRAMEWORK . "reflex.php/CustomerStatus/ViewAll";
	}

	//------------------------------------------------------------------------//
	// ViewCustomerStatus
	//------------------------------------------------------------------------//
	/**
	 * ViewCustomerStatus()
	 *
	 * Compiles the Href to be executed when the ViewCustomerStatus menu item is triggered
	 *
	 * Compiles the Href to be executed when the ViewCustomerStatus menu item is triggered
	 *
	 * @param	integer			id of the customer status to view
	 * @return	string			Href
	 *
	 * @method
	 */
	function ViewCustomerStatus($intId)
	{
		$strName = Customer_Status :: getForId($intId)->name;
		$this->strContextMenuLabel = "View Customer Status $strName";
		$this->strLabel = $strName;
		return self :: NEW_FRAMEWORK . "reflex.php/CustomerStatus/View/$intId";
	}

	//------------------------------------------------------------------------//
	// EditCustomerStatus
	//------------------------------------------------------------------------//
	/**
	 * EditCustomerStatus()
	 *
	 * Compiles the Href to be executed when the EditCustomerStatus menu item is triggered
	 *
	 * Compiles the Href to be executed when the EditCustomerStatus menu item is triggered
	 *
	 * @param	integer			id of the customer status to edit
	 * @return	string			Href
	 *
	 * @method
	 */
	function EditCustomerStatus($intId)
	{
		$strName = Customer_Status :: getForId($intId)->name;
		$this->strContextMenuLabel = "Edit Customer Status $strName";
		$this->strLabel = $strName;
		return self :: NEW_FRAMEWORK . "reflex.php/CustomerStatus/Edit/$intId";
	}

	//------------------------------------------------------------------------//
	// CustomerStatusSummaryReport
	//------------------------------------------------------------------------//
	/**
	 * CustomerStatusSummaryReport()
	 *
	 * Compiles the Href to be executed when the CustomerStatusSummaryReport menu item is triggered
	 *
	 * Compiles the Href to be executed when the CustomerStatusSummaryReport menu item is triggered
	 *
	 * @return	string			Href
	 *
	 * @method
	 */
	function CustomerStatusSummaryReport($bolRetrieveCachedReport = FALSE)
	{
		$this->strContextMenuLabel = "Summary Report";
		$this->strLabel = "Summary Report";
		return self :: NEW_FRAMEWORK . "reflex.php/CustomerStatus/SummaryReport" . (($bolRetrieveCachedReport) ? "/GetReport" : "");
	}

	//------------------------------------------------------------------------//
	// CustomerStatusAccountReport
	//------------------------------------------------------------------------//
	/**
	 * CustomerStatusAccountReport()
	 *
	 * Compiles the Href to be executed when the CustomerStatusAccountReport menu item is triggered
	 *
	 * Compiles the Href to be executed when the CustomerStatusAccountReport menu item is triggered
	 *
	 * @return	string			Href
	 *
	 * @method
	 */
	function CustomerStatusAccountReport($bolRetrieveCachedReport = FALSE)
	{
		$this->strContextMenuLabel = "Account Report";
		$this->strLabel = "Account Report";
		return self :: NEW_FRAMEWORK . "reflex.php/CustomerStatus/AccountReport" . (($bolRetrieveCachedReport) ? "/GetReport" : "");
	}

	//------------------------------------------------------------------------//
	// GenerateCustomerStatusAccountReport
	//------------------------------------------------------------------------//
	/**
	 * GenerateCustomerStatusAccountReport()
	 *
	 * Compiles the Href to be executed when the GenerateCustomerStatusAccountReport functionality is triggered
	 *
	 * Compiles the Href to be executed when the GenerateCustomerStatusAccountReport functionality is triggered
	 *
	 * @return	string			Href
	 *
	 * @method
	 */
	function GenerateCustomerStatusAccountReport($intInvoiceRun, $arrCustomerGroups = NULL, $arrCustomerStatuses = NULL)
	{
		$this->strContextMenuLabel = "";
		$this->strLabel = "";
		$strGetVars = "?InvoiceRun=$intInvoiceRun";
		if (is_array($arrCustomerGroups)) {
			$strGetVars .= implode("&CustomerGroup[]=", $arrCustomerGroups);
		}
		if (is_array($arrCustomerStatuses)) {
			$strGetVars .= implode("&CustomerStatus[]=", $arrCustomerStatuses);
		}

		return self :: NEW_FRAMEWORK . "reflex.php/CustomerStatus/AccountReport/GenerateReport/$strGetVars";
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
		return self :: NEW_FRAMEWORK . "flex.php/Service/ViewPlan/?Service.Id=$intId";
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
		$this->strLabel = "Template History";
		$this->strContextMenuLabel = "Template History";
		return self :: NEW_FRAMEWORK . "flex.php/CustomerGroup/ViewDocumentTemplateHistory/?CustomerGroup.Id=$intCustomerGroup&DocumentTemplateType.Id=$intTemplateType";
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
	 * @param	int		$intId					id of the CustomerGroup
	 * @param	string	$strBreadCrumbLabel		optional, breadcrumb label, preferably the name of the customer group
	 *
	 * @return	string				Href to be executed when the ViewCustomerGroup menu item is clicked
	 *
	 * @method
	 */
	function ViewCustomerGroup($intId, $strBreadCrumbLabel = NULL)
	{
		$this->strLabel = "Customer Group";
		if ($strBreadCrumbLabel !== NULL) {
			$this->strLabel = $strBreadCrumbLabel;
			if (strlen($strBreadCrumbLabel) > 15) {
				$this->strLabel = "<span title='$strBreadCrumbLabel'>" . substr($strBreadCrumbLabel, 0, 12) . "...</span>";
			}
		}

		$this->strContextMenuLabel = "";
		return self :: NEW_FRAMEWORK . "flex.php/CustomerGroup/View/?CustomerGroup.Id=$intId";
	}

	//------------------------------------------------------------------------//
	// ViewCustomerGroupCreditCardConfig
	//------------------------------------------------------------------------//
	/**
	 * ViewCustomerGroupCreditCardConfig()
	 *
	 * Compiles the Href to be executed when the ViewCustomerGroupCreditCardConfig menu item is clicked
	 *
	 * Compiles the Href to be executed when the ViewCustomerGroupCreditCardConfig menu item is clicked
	 *
	 * @param	int		$intId					id of the CustomerGroup
	 *
	 * @return	string				Href to be executed when the ViewCustomerGroup menu item is clicked
	 *
	 * @method
	 */
	function ViewCustomerGroupCreditCardConfig($intId, $strAction = 'View')
	{
		$this->strLabel = "Credit Card Configuration";
		$this->strContextMenuLabel = "";
		return self :: NEW_FRAMEWORK . "reflex.php/CustomerGroup/CreditCardConfig/$intId/$strAction";
	}

	//------------------------------------------------------------------------//
	// AddDocumentResource
	//------------------------------------------------------------------------//
	/**
	 * AddDocumentResource()
	 *
	 * Compiles the Href to be executed when the AddDocumentResource menu item is triggered
	 *
	 * Compiles the Href to be executed when the AddDocumentResource menu item is triggered
	 *
	 * @param	int		$intCustomerGroup		id of the CustomerGroup
	 * @param	int		$intResourceType		id of the DocumentResourceType to add a new resource to
	 * @param	int		$strResourceTypeName	Name of the resource type (gets displayed in the popup's title bar)
	 *
	 * @return	string							Href to trigger the functionality
	 * @method
	 */
	function AddDocumentResource($intCustomerGroup, $intResourceType, $strResourceTypeName)
	{
		$this->strContextMenuLabel = "";
		$this->strLabel = "";
		// Setup data to send
		$arrData['CustomerGroup']['Id'] = $intCustomerGroup;
		$arrData['DocumentResourceType']['Id'] = $intResourceType;

		$strJsonCode = Json()->encode($arrData);
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddDocumentResourcePopup\", \"large\", \"New Resource - $strResourceTypeName\", \"CustomerGroup\", \"AddDocumentResource\", $strJsonCode, \"modal\")";
	}

	//------------------------------------------------------------------------//
	// ViewDocumentTemplateSamplePDF
	//------------------------------------------------------------------------//
	/**
	 * ViewDocumentTemplateSamplePDF()
	 *
	 * Compiles the Href to be executed when the View Document Template Sample PDF menu item is triggered
	 *
	 * Compiles the Href to be executed when the View Document Template Sample PDF menu item is triggered
	 *
	 * @param	int		$intCustomerGroup		optional, defaults to NULL, id of the CustomerGroup
	 * @param	int		$intTemplateType		optional, defaults to NULL, id of the DocumentTemplateType to build a pdf of
	 *
	 * @return	string							Href to trigger the functionality
	 * @method
	 */
	function ViewDocumentTemplateSamplePDF($intCustomerGroup = NULL, $intTemplateType = NULL)
	{
		$this->strContextMenuLabel = "";
		$this->strLabel = "";
		// Setup data to send
		$arrData['CustomerGroup']['Id'] = $intCustomerGroup;
		$arrData['DocumentTemplateType']['Id'] = $intTemplateType;

		$strJsonCode = Json()->encode($arrData);
		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewDocumentTemplateSamplePDFPopup\", \"medium\", \"Sample PDF\", \"CustomerGroup\", \"ViewSamplePDF\", $strJsonCode, \"modal\")";
	}

	//------------------------------------------------------------------------//
	// ViewDocumentResource
	//------------------------------------------------------------------------//
	/**
	 * ViewDocumentResource()
	 *
	 * Compiles the Href to be executed when the ViewDocumentResource menu item is triggered
	 *
	 * Compiles the Href to be executed when the ViewDocumentResource menu item is triggered
	 *
	 * @param	int		$intResourceId		id of the DocumentResource to view
	 * @param	bool	$bolDownloadFile	optional, defaults to FALSE, if set to TRUE
	 *										then the user will be prompted to save the file
	 *										or choose a program to open it with
	 *										If set to false, the resource will be sent to the
	 *										browser with its MIME type declared and the browser
	 *										will display it however it does for files of this
	 *										MIME type
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ViewDocumentResource($intResourceId, $bolDownloadFile = FALSE)
	{
		$this->strContextMenuLabel = "";
		$this->strLabel = "";
		$strDownload = "";
		if ($bolDownloadFile) {
			$strDownload = "&DocumentResource.DownloadFile=TRUE";
		}
		return self :: NEW_FRAMEWORK . "flex.php/CustomerGroup/ViewDocumentResource/?DocumentResource.Id=$intResourceId{$strDownload}";
	}

	//------------------------------------------------------------------------//
	// ViewDocumentResources
	//------------------------------------------------------------------------//
	/**
	 * ViewDocumentResources()
	 *
	 * Compiles the Href to be executed when the ViewDocumentResources menu item is triggered
	 *
	 * Compiles the Href to be executed when the ViewDocumentResources menu item is triggered
	 *
	 * @param	int		$intCustomerGroup	id of the CustomerGroup to view the resources of
	 *
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function ViewDocumentResources($intCustomerGroup)
	{
		$this->strContextMenuLabel = "";
		$this->strLabel = "";
		return self :: NEW_FRAMEWORK . "flex.php/CustomerGroup/ViewDocumentResources/?CustomerGroup.Id=$intCustomerGroup";
	}

	//------------------------------------------------------------------------//
	// ManageSales
	//------------------------------------------------------------------------//
	/**
	 * ManageSales()
	 *
	 * Compiles the Href to be executed when the ManageSales functionality is requested
	 *
	 * Compiles the Href to be executed when the ManageSales functionality is requested
	 *
	 * @param	bool	$bolLast			optional, defaults to FALSE. If true, then the Manage Sales functionality will load, reflecting the last
	 * 										set of boundary conditions
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function ManageSales($bolLast=FALSE)
	{
		$this->strLabel = "Sales";
		$this->strContextMenuLabel = "Manage Sales";

		return self :: NEW_FRAMEWORK . "reflex.php/Sales/ListSales/". ($bolLast ? "Last/" : "");
	}

	//------------------------------------------------------------------------//
	// VerifySales
	//------------------------------------------------------------------------//
	/**
	 * VerifySales()
	 *
	 * Compiles the Href to be executed when the VerifySales functionality is requested
	 *
	 * Compiles the Href to be executed when the VerifySales functionality is requested
	 * This is currently exactly the same as the ManageSales menu item, just named differently
	 *
	 * @param	bool	$bolLast			optional, defaults to FALSE. If true, then the Verify Sales functionality will load, reflecting the last
	 * 										set of boundary conditions
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function VerifySales($bolLast=FALSE)
	{
		$this->strLabel = "Sales";
		$this->strContextMenuLabel = "Verify Sales";

		return self :: NEW_FRAMEWORK . "reflex.php/Sales/ListSales/". ($bolLast ? "Last/" : "");
	}

	//------------------------------------------------------------------------//
	// ViewSale
	//------------------------------------------------------------------------//
	/**
	 * ViewSale()
	 *
	 * Compiles the Href to be executed when the ViewSale functionality is requested
	 *
	 * Compiles the Href to be executed when the ViewSale functionality is requested
	 *
	 * @param	int			$intSaleId		id of the sale to view
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function ViewSale($intSaleId)
	{
		$this->strLabel = "Sale";
		$this->strContextMenuLabel = "View Sale";

		return self :: NEW_FRAMEWORK . "reflex.php/Sales/ViewSale/$intSaleId/";
	}

	//------------------------------------------------------------------------//
	// SalesReport
	//------------------------------------------------------------------------//
	/**
	 * SalesReport()
	 *
	 * Compiles the Href to be executed when the Sales Reporting functionality is requested
	 *
	 * Compiles the Href to be executed when the Sales Reporting functionality is requested
	 *
	 * @param	string	$strReportType		The type of report to generate
	 *
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function SalesReport($strReportType, $bolRetrieveReport=FALSE)
	{
		$arrReportTypes = Sales_Report::getReportTypes();
		if (!array_key_exists($strReportType, $arrReportTypes))
		{
			throw new Exception(__METHOD__ ." - Unknown sales report type: $strReportType");
		}

		$this->strLabel				= $arrReportTypes[$strReportType]['Name'];
		$this->strContextMenuLabel	= $this->strLabel;
		$strRetrieveReport			= $bolRetrieveReport ? "GetReport/" : "";

		return self::NEW_FRAMEWORK . "reflex.php/Sales/Report/{$strReportType}/$strRetrieveReport";
	}

	//------------------------------------------------------------------------//
	// ViewSalesForAccount
	//------------------------------------------------------------------------//
	/**
	 * ViewSalesForAccount()
	 *
	 * Compiles the Href to be executed when the ViewSalesForAccount functionality is requested
	 *
	 * Compiles the Href to be executed when the ViewSalesForAccount functionality is requested
	 *
	 * @param	int			$intAccountId		id of the sale to view
	 * @return	string							Href to trigger the functionality
	 * @method
	 */
	function ViewSalesForAccount($intAccountId)
	{
		$this->strLabel = "View Sales";
		$this->strContextMenuLabel = "View Sales";

		return "javascript:JsAutoLoader.loadScript('javascript/account_sales.js', function(){AccountSales.showSales($intAccountId);});";
	}

	//------------------------------------------------------------------------//
	// ManageDealers
	//------------------------------------------------------------------------//
	/**
	 * ManageDealers()
	 *
	 * Compiles the Href to be executed when the ManageDealers functionality is requested
	 *
	 * Compiles the Href to be executed when the ManageDealers functionality is requested
	 *
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function ManageDealers() {
		$this->strLabel = "Dealers";
		$this->strContextMenuLabel = "Manage Dealers";

		return self :: NEW_FRAMEWORK . "reflex.php/Dealer/ListDealers/";
	}

	//------------------------------------------------------------------------//
	// ViewDealer
	//------------------------------------------------------------------------//
	/**
	 * ViewDealer()
	 *
	 * Compiles the Href to be executed when the ViewDealer functionality is requested
	 *
	 * Compiles the Href to be executed when the ViewDealer functionality is requested
	 *
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function ViewDealer($intDealerId) {
		$this->strLabel = "View Dealer";
		$this->strContextMenuLabel = "View Dealer";

		return "javascript:Dealer.viewDealer($intDealerId)";
	}

	//------------------------------------------------------------------------//
	// EditDealer
	//------------------------------------------------------------------------//
	/**
	 * EditDealer()
	 *
	 * Compiles the Href to be executed when the EditDealer functionality is requested
	 *
	 * Compiles the Href to be executed when the EditDealer functionality is requested
	 *
	 * @return	string						Href to trigger the functionality
	 * @method
	 */
	function EditDealer($intDealerId) {
		$this->strLabel = "Edit Dealer";
		$this->strContextMenuLabel = "Edit Dealer";

		return "javascript:Dealer.editDealer($intDealerId)";
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
	function ViewAllCustomerGroups() {
		$this->strLabel = "Customer Groups";
		$this->strContextMenuLabel = "View Customer Groups";
		return self :: NEW_FRAMEWORK . "flex.php/CustomerGroup/ViewAll/";
	}

	//------------------------------------------------------------------------//
	// ManagePaymentTerms
	//------------------------------------------------------------------------//
	/**
	 * ManagePaymentTerms()
	 *
	 * Compiles the Href to be executed when the ManagePaymentTerms menu item is clicked
	 *
	 * Compiles the Href to be executed when the ManagePaymentTerms menu item is clicked
	 *
	 * @return	string				Href to be executed when the ManagePaymentTerms menu item is clicked
	 *
	 * @method
	 */
	function ManagePaymentTerms($customerGroupId) {
		$this->strLabel = "Payment Process";
		$this->strContextMenuLabel = "Manage Payment Process";
		return self :: NEW_FRAMEWORK . "flex.php/PaymentTerms/Manage/?CustomerGroup.Id=$customerGroupId";
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
	function AddCustomerGroup() {
		$this->strLabel = "Add Customer Group";
		$this->strContextMenuLabel = "";
		return self :: NEW_FRAMEWORK . "flex.php/CustomerGroup/Add/";
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
	function EmployeeConsole() {
		$this->strLabel = "Console";
		$this->strContextMenuLabel = "Console";
		return self :: NEW_FRAMEWORK . "reflex.php/Console/View/";
	}

	//------------------------------------------------------------------------//
	// EmployeeList
	//------------------------------------------------------------------------//
	/**
	 * EmployeeList()
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
	function EmployeeList() {
		self::deprecatedMenuItem('EmployeeList', self::NEW_FRAMEWORK."flex.php/Employee/EmployeeList/");
		/*$this->strLabel = "List Employees";
		$this->strContextMenuLabel = "";
		return self :: NEW_FRAMEWORK . "flex.php/Employee/EmployeeList/";*/
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
	function AddCustomer() {
		$this->strLabel = "Add Customer";
		$this->strContextMenuLabel = "";
		return self :: NEW_FRAMEWORK . "reflex.php/Account/Create";
	}

	//------------------------------------------------------------------------//
	// FindCustomerOld
	//------------------------------------------------------------------------//
	/**
	 * FindCustomerOld()
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
	function FindCustomerOld() {
		self::deprecatedMenuItem('FindCustomerOld', self::OLD_FRAMEWORK."contact_verify.php");
		/*$this->strLabel = "Find Customer";
		$this->strContextMenuLabel = "Find Customer (Old)";
		return self :: OLD_FRAMEWORK . "contact_verify.php";*/
	}

	//------------------------------------------------------------------------//
	// CustomerSearch
	//------------------------------------------------------------------------//
	/**
	 * CustomerSearch()
	 *
	 * Compiles the Href to be executed when the CustomerSearch functionality is triggered
	 *
	 * Compiles the Href to be executed when the CustomerSearch functionality is triggered
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @return	string				Href
	 *
	 * @method
	 */
	function CustomerSearch() {
		$this->strLabel = "Find Customer";
		$this->strContextMenuLabel = "Find Customer";
		return "javascript:FlexSearch.displayPopup()";
	}

	//------------------------------------------------------------------------//
	// CustomerOverdueList
	//------------------------------------------------------------------------//
	/**
	 * CustomerOverdueList()
	 *
	 * Compiles the Href to be executed when the CustomerOverdueList functionality is triggered
	 *
	 * Compiles the Href to be executed when the CustomerOverdueList functionality is triggered
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @return	string				Href
	 *
	 * @method
	 */
	function CustomerOverdueList() {
		$this->strLabel = "Overdue Customers";
		$this->strContextMenuLabel = "Overdue Customers";

		//return "javascript:FlexCustomerOverdueList.displayPopup()";
		return "javascript:JsAutoLoader.loadScript('javascript/customer_overdue_list.js', function(){FlexCustomerOverdueList.displayPopup();});";
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
	function AddServices($intAccountId) {
		$this->strLabel = "Add Services";
		$this->strContextMenuLabel = "Add Services";
		return self :: NEW_FRAMEWORK . "flex.php/Service/BulkAdd/?Account.Id=$intAccountId";
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
	function EditContact($intId) {
		self::deprecatedMenuItem('EditContact', self::OLD_FRAMEWORK."contact_edit.php?Id=$intId");
		/*$this->strLabel = "contact: $intId";
		$this->strContextMenuLabel = "";
		return self :: OLD_FRAMEWORK . "contact_edit.php?Id=$intId";*/
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
	function ExportInvoiceAsCSV($intInvoiceId) {
		$this->strLabel = "";
		$this->strContextMenuLabel = "";
		return self :: NEW_FRAMEWORK . "flex.php/Invoice/ExportAsCSV/?Invoice.Id=$intInvoiceId";
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
	function ViewAllConstants() {
		$this->strLabel = "Constants Management";
		$this->strContextMenuLabel = "Manage Constants";
		return self :: NEW_FRAMEWORK . "flex.php/Config/ManageConstants/";
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
	 * @return	string						Href to be executed when the SystemSettingsMenu menu item is triggered
	 *
	 * @method
	 */
	function SystemSettingsMenu() {
		$this->strLabel = "System Settings";
		$this->strContextMenuLabel = "";
		return self :: NEW_FRAMEWORK . "flex.php/Config/SystemSettingsMenu/";
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
	function AddContact($iAccountId) {
		$this->strLabel = "Add Contact";
		$this->strContextMenuLabel = "";
		//return self :: OLD_FRAMEWORK . "contact_add.php?Account=$iAccountId";
		return "javascript: JsAutoLoader.loadScript('popup_contact_edit.js', function(){new Popup_Contact_Edit(null, {$iAccountId}, Popup_Contact_Edit._goToPage);}, true);";
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
	function ChangePaymentMethod($iAccountId) {
		$this->strLabel = "Change Payment Method";
		$this->strContextMenuLabel = "";
		// return self :: OLD_FRAMEWORK . "account_payment.php?Id=$intAccountId";
		return "javascript:
					Flex.Constant.loadConstantGroup(
						['payment_method', 'rebill_type', 'direct_debit_type'],
						function()
						{
							JsAutoLoader.loadScript(
								[
									'javascript/popup_account_change_payment_method.js',
									'../ui/javascript/reflex_validation.js',
									'../ui/javascript/control_field.js',
									'../ui/javascript/control_field_text.js',
									'../ui/javascript/control_field_checkbox.js',
									'../ui/javascript/control_field_select.js',
									'../ui/javascript/component_date_picker.js',
									'../ui/javascript/control_field_date_picker.js',
									'../ui/javascript/control_field_combo_date.js'
								],
								function()
								{
									new Popup_Account_Change_Payment_Method({$iAccountId})
								},
								false,
								true
							);
						}
					);";
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
	function ViewCostCentres($intAccountId) {
		$this->strLabel = "Cost Centres";
		$this->strContextMenuLabel = "";
		//return self :: OLD_FRAMEWORK . "costcentre_list.php?Account=$intAccountId";
		return "javascript:JsAutoLoader.loadScript('javascript/popup_cost_centres.js', function(){new Popup_Cost_Centres($intAccountId);});";
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
	function ViewRecentCustomers() {
		$this->strContextMenuLabel = "View Recent Customers";

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ViewRecentCustomersId\", \"extralarge\", \"Recent Customers\", \"Employee\", \"ViewRecentCustomers\")";
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
	function ViewRate($intRateId, $bolModal = TRUE) {
		$this->strContextMenuLabel = "";

		// Setup data to send
		$arrData['Rate']['Id'] = $intRateId;

		$strWindowType = ($bolModal) ? "modal" : "nonmodal";

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
	function ViewProvisioningHistory($intServiceId = NULL, $intAccountId = NULL) {
		$this->strContextMenuLabel = "View History";

		if ($intServiceId == NULL && $intAccountId == NULL) {
			throw new Exception("Must specify an AccountId or ServiceId");
		}

		if ($intServiceId) {
			$strPopupId = "ProvisioningHistoryPopup{$intServiceId}";
		} else {
			$strPopupId = "AccountProvisioningHistoryPopupId";
		}

		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;
		$arrData['Account']['Id'] = $intAccountId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"$strPopupId\", \"ExtraLarge\", \"History\", \"Provisioning\", \"ViewHistory\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// ViewServiceHistory
	//------------------------------------------------------------------------//
	/**
	 * ViewServiceHistory()
	 *
	 * Compiles the Href to be executed when the ViewServiceHistory functionality is triggered
	 *
	 * Compiles the Href to be executed when the ViewServiceHistory functionality is triggered
	 *
	 * @param	int		$intServiceId	Id of the Service
	 *
	 * @return	string
	 *
	 * @method
	 */
	function ViewServiceHistory($intServiceId) {
		$this->strContextMenuLabel = "View History";

		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ServiceHistory\", \"Large\", \"Service History\", \"Service\", \"ViewHistory\", $strJsonCode)";
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
	function OverrideRateGroup($intServiceId, $intRecordTypeId) {
		$this->strContextMenuLabel = "";

		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;
		$arrData['RecordType']['Id'] = $intRecordTypeId;

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
	function ViewRateGroup($intRateGroupId, $bolModal = TRUE) {
		$this->strContextMenuLabel = "";

		// Setup data to send
		$arrData['RateGroup']['Id'] = $intRateGroupId;

		$strWindowType = ($bolModal) ? "modal" : "nonmodal";

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
	function AddConfigConstant($intConstantGroupId) {
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
	function EditConfigConstant($intConstantId) {
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
	function EditService($intId) {
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
	function AddService($intId) {
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
	function ImportRateGroup($intRecordTypeId, $bolIsFleet) {
		$this->strContextMenuLabel = "";

		// Setup data to send
		$arrData['RecordType']['Id'] = $intRecordTypeId;
		$arrData['RateGroup']['Fleet'] = $bolIsFleet;

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
	function ChangePlan($intId) {
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
	function ViewContact($intContactId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "Contact: $intContactId";

		return self::NEW_FRAMEWORK."reflex.php/Contact/View/{$intContactId}/";
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
	 * @param	bool	$bolShowAccountName	[optional], defaults to false.  This should only be set to TRUE when being used for the breadcrumb menu.
	 * 										It will use the account name instead of "Account" in the breadcrumb menu
	 *
	 * @return	string						Href to be executed when the AccountOverview menu item is clicked
	 *
	 * @method
	 */
	function AccountOverview($intAccountId, $bolShowAccountName = FALSE) {
		$this->strContextMenuLabel = "Overview";

		$strLabel = "Account";
		if ($bolShowAccountName)
		{
			$objAccount = Account::getForId($intAccountId);
			if ($objAccount !== NULL) {
				$strLabel = htmlspecialchars(trim($objAccount->getName()));
				$strLabel = ($strLabel == "") ? "Account" : $strLabel;
			}
		}

		$this->strLabel = $strLabel;

		return self :: NEW_FRAMEWORK . "flex.php/Account/Overview/?Account.Id={$intAccountId}";
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
	function ListContacts($intAccountId) {
		$this->strContextMenuLabel = "";

		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AccountContactsPopupId\", \"extralarge\", null, \"Account\", \"ViewContacts\", $strJsonCode)";
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
	 * @param	bool	$bolShowFNN		[optional] If TRUE, then the FNN will be shown in the breadcrumb menu,
	 * 									if false then "Service" will be shown.  Defaults to FALSE
	 * 									This should really only be set to TRUE if the menu item is being used in the breadcrumb menu
	 *
	 * @return	string				Href to be executed when the ViewService menu item is clicked
	 *
	 * @method
	 */
	function ViewService($intId, $bolShowFNN = FALSE) {
		$this->strContextMenuLabel = "Service Details";

		$strLabel = "Service";
		if ($bolShowFNN) {
			$objService = new Service(array (
				"id" => $intId
			), TRUE);

			if ($objService->FNN !== NULL) {
				$strLabel = htmlspecialchars($objService->FNN);
			}
		}

		$this->strLabel = $strLabel;
		return self :: NEW_FRAMEWORK . "flex.php/Service/View/?Service.Id=$intId";
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
	function ViewServiceAddress($intServiceId) {
		$this->strContextMenuLabel = "Physical Address";

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
	function EditServiceAddress($intServiceId) {
		$this->strContextMenuLabel = "Edit Address Details";

		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"ServiceAddressPopupId\", \"ExtraLarge\", \"Address Details\", \"Service\", \"EditAddress\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// ViewInvoicedCDR
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoicedCDR()
	 *
	 * Compiles the Href to be executed when the View Invoiced CDR menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoiced CDR menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intInvoiceRunId		id of the invoice_run to view
	 * @param	int		$intCdrId				id of the CDR to view
	 *
	 * @return	string				Href to be executed when the View Invoiced CDR menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoicedCDR($intServiceTotalId, $intInvoiceRunId, $intCdrId) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Record";

		return self :: NEW_FRAMEWORK . "reflex.php/Invoice/CDR/$intServiceTotalId/$intInvoiceRunId/$intCdrId";
	}

	//------------------------------------------------------------------------//
	// RecordCustomerInHistory
	//------------------------------------------------------------------------//
	/**
	 * RecordCustomerInHistory()
	 *
	 * Adds a customer to the employee_account_log table
	 *
	 * Adds a customer to the employee_account_log table
	 *
	 * @param	bool	$bolSupressErrors
	 * @param	string	$strNextPage
	 * @param	int		$intAccountId
	 * @param	int		$intContactId
	 *
	 * @return	string				Href
	 *
	 * @method
	 */
	function RecordCustomerInHistory($bolSupressErrors, $strNextPage, $intAccountId, $intContactId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "";
		$arrGetVars = array ();
		$strSupressErrors = ($bolSupressErrors) ? Application_Handler_CustomerHistory :: SUPRESS_ERRORS_FLAG . "/" : "";

		$arrGetVars[] = "NextPage=$strNextPage";
		if ($intAccountId !== NULL) {
			$arrGetVars[] = "AccountId=$intAccountId";
		}

		if ($intContactId !== NULL) {
			$arrGetVars[] = "ContactId=$intContactId";
		}
		$strGetVars = implode("&", $arrGetVars);

		return self :: NEW_FRAMEWORK . "reflex.php/CustomerHistory/Record/{$strSupressErrors}?{$strGetVars}";
	}

	//------------------------------------------------------------------------//
	// ViewCDR
	//------------------------------------------------------------------------//
	/**
	 * ViewCDR()
	 *
	 * Compiles the Href to be executed when the View CDR menu item is clicked
	 *
	 * Compiles the Href to be executed when the View CDR menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the account to view
	 *
	 * @return	string				Href to be executed when the View CDR menu item is clicked
	 *
	 * @method
	 */
	function ViewCDR($intId) {
		self::deprecatedMenuItem('ViewCDR', self::OLD_FRAMEWORK."cdr_view.php?Id=$intId");
		/*$this->strContextMenuLabel = "";
		$this->strLabel = "Record";
		return self :: OLD_FRAMEWORK . "cdr_view.php?Id=$intId";*/
	}

	//------------------------------------------------------------------------//
	// ViewCDR
	//------------------------------------------------------------------------//
	/**
	 * ViewCDR()
	 *
	 * Compiles the Href to be executed when viewing cdr details
	 *
	 * Compiles the Href to be executed when viewing cdr details
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the cdr to view
	 *
	 * @return	string				Href to be executed when viewing cdr details
	 *
	 * @method
	 */
	function ViewCDRDetails($iServiceId, $iCdrId)
	{
		$this->strContextMenuLabel = "";
		$this->strLabel = "Record";
		return self :: NEW_FRAMEWORK . "reflex.php/Service/CDR/$iServiceId/$iCdrId";
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
	function InvoicesAndPayments($intId) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Invoices and Payments";

		return self :: NEW_FRAMEWORK . "flex.php/Account/InvoicesAndPayments/?Account.Id=$intId";
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
	function EditEmployee($iId=false, $sUserName=false, $sJSCallback=false, $bEditingSelf=false)
	{
		$this->strContextMenuLabel	= "";

		// Setup data to send
		if ($bEditingSelf)
		{
			$this->strContextMenuLabel 	= "View Employee details";
			$this->strLabel 			= "";
			$sJsonCode					= 'null';
			return "javascript:Vixen.Popup.ShowAjaxPopup(\"EmployeeEditPopup\", \"medium\", \"Employee\", \"Employee\", \"EmployeeDetails\", null)";
		}
		else if ($iId)
		{
			$this->strLabel				= "emp: $sUserName";
			$aData['Employee']['Id']	= $iId;
			$sJsonCode					= Json()->encode($aData);
			return "javascript:Vixen.Popup.ShowAjaxPopup(\"EmployeeEditPopup\", \"medium\", \"Employee\", \"Employee\", \"Edit\", $sJsonCode)";
		}
		else
		{
			$this->strLabel				= "emp: new";
			$aData['Employee']['Id']	= -1;
			$sJsonCode					= Json()->encode($aData);
			return "javascript:Vixen.Popup.ShowAjaxPopup(\"Employee{}AddPopup\", \"medium\", \"Employee\", \"Employee\", \"Create\", $sJsonCode)";
		}

		// Removed until permissions are released. rmctainsh 20100429
		/*$this->strContextMenuLabel	= "";

		$this->strLabel	= "Emp: ".($iId ? $sUserName : 'new');

		$aJSFiles	= 	array(
							"dataset_ajax",
							"reflex_validation",
							"reflex_style",
							"reflex_fx_reveal",
							"reflex_control",
							"reflex_control_tree",
							"reflex_control_tree_node",
							"reflex_control_tree_node_root",
							"reflex_control_tree_node_checkable",
							"date_time_picker_dynamic",
							"control_field",
							"control_field_text",
							"control_field_password",
							"control_field_checkbox",
							"control_field_date_picker",
							"control_field_select",
							"operation_tree",
							"operation",
							"status",
							"operation_profile",
							"user_role",
							"ticketing_user_permission",
							"employee",
							"popup_employee_details",
							"popup_employee_password_change",
							"popup_employee_details_permissions",
							"popup_operation_profile_edit"
						);

		$sSelf		= ($bEditingSelf ? 'true' : 'false');
		$sId		= ($iId ? $iId : 'null');
		$sRender	= ($iId ? 'Control_Field.RENDER_MODE_VIEW' : 'Control_Field.RENDER_MODE_EDIT');
		$sCallback	= ($sJSCallback ? $sJSCallback : 'null');
		$sPopup		= "var oPopup	= new Popup_Employee_Details({$sRender}, {$sId}, {$sSelf}, {$sCallback});";
		$sLoad		= "JsAutoLoader.loadScript(['".implode(".js','", $aJSFiles).".js'], function() {{$sPopup}}, true);";
		return "$sLoad";
		*/
	}

	/**
	 * ManageEmployeePermissions()
	 *
	 * Compiles the Href to be executed when the ManageEmployeePermissions menu item is clicked
	 *
	 * Compiles the Href to be executed when the ManageEmployeePermissions menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intId		id of the Employee to whose permissions to manage
	 *
	 * @return	string				Href to be executed when the ManageEmployeePermissions menu item is clicked
	 *
	 * @method
	 */
	function ManageEmployeePermissions($iId)
	{
		$this->strContextMenuLabel	= "";

		$this->strLabel	= "emp permissions: $iId";

		$aJSFiles	= 	array(
							"dataset_ajax",
							"reflex_validation",
							"reflex_style",
							"reflex_fx_reveal",
							"reflex_control",
							"reflex_control_tree",
							"reflex_control_tree_node",
							"reflex_control_tree_node_root",
							"reflex_control_tree_node_checkable",
							"control_field",
							"control_field_text",
							"control_field_select",
							"operation_tree",
							"status",
							"operation",
							"operation_profile",
							"user_role",
							"ticketing_user_permission",
							"employee",
							"popup_employee_details_permissions",
							"popup_operation_profile_edit"
						);

		$sPopup		= "var oPopup	= new Popup_Employee_Details_Permissions(Control_Field.RENDER_MODE_VIEW, {$iId});";
		$sLoad		= "JsAutoLoader.loadScript(['".implode(".js','", $aJSFiles).".js'], function() {{$sPopup}}, true);";
		return "$sLoad";
	}

	function ManagePermissionProfiles()
	{
		$this->strContextMenuLabel	= "Manage Permission Profiles";
		$this->strLabel	= "Manage Permission Profiles";
		return self::NEW_FRAMEWORK."reflex.php/Permission/ProfileList/";
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
	function AddRatePlan($intBasePlanId = NULL, $strCallingPageHref = NULL) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Add Plan";

		// Setup data to send as GET variables
		$strBasePlan = ($intBasePlanId !== NULL) ? "BaseRatePlan.Id=$intBasePlanId" : "";
		$strCallingPage = ($strCallingPageHref !== NULL) ? "CallingPage.Href=$strCallingPageHref" : "";

		if ($intBasePlanId && $strCallingPageHref) {
			// Both parameters are set
			$strGetVariables = "?$strBasePlan&$strCallingPage";
		}
		elseif ($intBasePlanId || $strCallingPageHref) {
			// Only one of the parameters is specified
			$strGetVariables = "?" . $strBasePlan . $strCallingPage;
		} else {
			// No parameters have been specified
			$strGetVariables = "";
		}

		return self :: NEW_FRAMEWORK . "flex.php/Plan/Add/$strGetVariables";
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
	function EditRatePlan($intPlanId, $strCallingPageHref = NULL) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Edit Plan";

		// Setup data to send as GET variables
		$strGetVariables = "RatePlan.Id=$intPlanId";
		if ($strCallingPageHref !== NULL) {
			$strGetVariables .= "&CallingPage.Href=$strCallingPageHref";
		}

		return self :: NEW_FRAMEWORK . "flex.php/Plan/Add/?$strGetVariables";
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
	 * @param	int		$intCustomerGroupId	optional, Will filter one customerGroup, defaults to NULL.  This will override the
	 * @param	bool	$bolGetLast			optional, If set to TRUE, then the cached filter options will be used
	 * @return	string						Href to be executed when the AvailablePlans menu item is clicked
	 *
	 * @method
	 */
	function AvailablePlans($bolGetLast=FALSE)
	{
		$strFilter = ($bolGetLast)? "?RatePlan.GetLast=1" : "";

		$this->strContextMenuLabel	= "Plans";
		$this->strLabel				= "Available Plans";
		return self::NEW_FRAMEWORK . "flex.php/Plan/AvailablePlans/$strFilter";
	}

	function ListPlans($intCustomerGroupId=NULL)
	{
		if ($intCustomerGroupId == NULL)
		{
			$strFilter					= "?RatePlan.CustomerGroup=0&RatePlan.ServiceType=0&RatePlan.Status=0";
			$this->strContextMenuLabel	= "All Plans";
			$this->strLabel				= "Available Plans";
		}
		else
		{
			$objCustomerGroup			= Customer_Group::getForId($intCustomerGroupId);
			$strFilter					= "?RatePlan.CustomerGroup=". $objCustomerGroup->id ."&RatePlan.ServiceType=0&RatePlan.Status=0";
			$this->strContextMenuLabel	= htmlspecialchars($objCustomerGroup->internalName) ." Plans";
			$this->strLabel				= $this->strContextMenuLabel;
		}

		return self::NEW_FRAMEWORK . "flex.php/Plan/AvailablePlans/$strFilter";
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
	function ViewPlan($intRatePlanId) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Plan Details";

		return self :: NEW_FRAMEWORK . "flex.php/Plan/View/?RatePlan.Id=$intRatePlanId";
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
	function AddAssociatedAccount($intAccountId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "";

		return self :: NEW_FRAMEWORK . "reflex.php/Account/Create?Associated=$intAccountId";
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
	function ViewInvoicePDF($iAccount, $iYear, $iMonth, $iInvoiceId, $iInvoiceRun = "")
	{
		$this->strContextMenuLabel = "";

		$this->strLabel = "pdf acct: $iAccount, $iInvoiceId/$iInvoiceRun";
		//return self :: OLD_FRAMEWORK . "invoice_pdf.php?Account=$intAccount&Invoice=$intInvoiceId&invoice_run_id=$intInvoiceRun&Year=$intYear&Month=$intMonth";
		return self :: NEW_FRAMEWORK . "reflex.php/Invoice/PDF/{$iInvoiceId}/?Account=$iAccount&Invoice_Run_Id=$iInvoiceRun&Year=$iYear&Month=$iMonth";
	}

	//------------------------------------------------------------------------//
	// ViewInvoiceService
	//------------------------------------------------------------------------//
	/**
	 * ViewInvoiceService()
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 *
	 * Compiles the Href to be executed when the View Invoice menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intServiceTotal		service total number for the service
	 * @param	str		$strFNN		FNN of the service
	 *
	 * @return	string					Href to be executed when the View Invoice menu item is clicked
	 *
	 * @method
	 */
	function ViewInvoiceService($intServiceTotal, $strFNN) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Service: $strFNN";
		return self :: NEW_FRAMEWORK . "reflex.php/Invoice/Service/$intServiceTotal";
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
	function ViewInvoice($iInvoice, $iAccountId) {
		// The function now returns the url of the invoices and payments page
		// because the invoice view page is now a popup.
		$this->strContextMenuLabel 	= "";
		$this->strLabel 			= "Invoice: $iInvoice";
		//return self :: OLD_FRAMEWORK . "invoice_view.php?Invoice=$iInvoice";
		return self :: NEW_FRAMEWORK . "flex.php/Account/InvoicesAndPayments/?Account.Id={$iAccountId}#Invoice/{$iInvoice}/View/";
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
	function ViewAccountNotes($intAccountId) {
		$this->strContextMenuLabel = "View Notes";

		$this->strLabel = "view account notes";

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
	function ViewServiceDetails($intId) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "acc: $intId";
		return self :: NEW_FRAMEWORK . "flex.php/Service/View/?Service.Id=$intId";
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
	function ViewUnbilledCharges($iServiceId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "Unbilled Charges";
		//return self :: OLD_FRAMEWORK . "service_unbilled.php?Id=$iId";
		return self::NEW_FRAMEWORK."reflex.php/Service/Unbilled/{$iServiceId}";
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
	function ChangeOfLessee($intId) {
		self::deprecatedMenuItem('ChangeOfLessee', self::OLD_FRAMEWORK."service_lessee.php?Service=$intId");
		/*$this->strContextMenuLabel = "Change Lessee";
		$this->strLabel = "change of lessee";
		return self :: OLD_FRAMEWORK . "service_lessee.php?Service=$intId";*/
	}

	//------------------------------------------------------------------------//
	// MoveService
	//------------------------------------------------------------------------//
	/**
	 * MoveService()
	 *
	 * Compiles the javascript to be executed when the MoveService menu item is clicked
	 *
	 * Compiles the javascript to be executed when the MoveService menu item is clicked
	 *
	 * @param	int		$intServiceId	id of the service to move
	 *
	 * @return	string					action to be executed when the MoveService menu item is clicked
	 *
	 * @method
	 */
	function MoveService($intServiceId) {
		$this->strContextMenuLabel = "Move Service";
		$this->strLabel = "";

		// Setup data to send
		$arrData['Service']['Id'] = $intServiceId;
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"MoveServicePopup\", \"mediumlarge\", \"Service Movement\", \"ServiceMovement\", \"DisplayServiceMovementPopup\", $strJsonCode)";
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
	function ViewServiceNotes($intId, $strNoteType = NULL) {
		$this->strContextMenuLabel = "View Notes";

		$this->strLabel = "view service notes";

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
	function ViewContactNotes($intId) {
		$this->strContextMenuLabel = "View Notes";

		$this->strLabel = "view contact notes";

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
	function AddContactNote($intId) {
		$this->strContextMenuLabel = "Add Note";

		$this->strLabel = "Add Contact Note";

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
	function AddAccountNote($intId) {
		$this->strContextMenuLabel = "Add Note";

		$this->strLabel = "Add Account Note";

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
	function AddServiceNote($intId) {
		$this->strContextMenuLabel = "Add Note";

		$this->strLabel = "Add Service Note";

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
	function EmailPDFInvoice($intId, $intYear, $intMonth, $intInvoiceId, $intInvoiceRun) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "email pdf invoice";

		// Setup data to send
		$arrData['Account']['Id'] = $intId;
		$arrData['Invoice']['Id'] = $intInvoiceId;
		$arrData['Invoice']['invoice_run_id'] = $intInvoiceRun;
		$arrData['Invoice']['Year'] = $intYear;
		$arrData['Invoice']['Month'] = $intMonth;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"EmailPDFInvoicePopupId\", \"medium\", \"Email Invoice PDF\", \"Invoice\", \"EmailPDFInvoice\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddCharge
	//------------------------------------------------------------------------//
	/**
	 * AddCharge()
	 *
	 * Compiles the javascript to be executed when the AddCharge menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddCharge menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intAccountId		id of the account that the Charge will be added to
	 * @param	int		$intServiceId		[optional] id of the service that the charge is associated with
	 *
	 * @return	string				action to be executed when the AddCharge menu item is clicked
	 *
	 * @method
	 */
	function AddCharge($intAccountId, $intServiceId = NULL) {
		$this->strContextMenuLabel = "Request Charge";

		$this->strLabel = "Request Charge";

		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;
		$arrData['Service']['Id'] = $intServiceId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddChargePopupId\", \"medium\", \"Request Charge\", \"Charge\", \"Add\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// AddRecurringCharge
	//------------------------------------------------------------------------//
	/**
	 * AddRecurringCharge()
	 *
	 * Compiles the javascript to be executed when the AddRecurringCharge menu item is clicked
	 *
	 * Compiles the javascript to be executed when the AddRecurringCharge menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intAccountId		id of the account that the Charge will be added to
	 * @param	int		$intServiceId		[optional] id of the service that the charge is associated with
	 *
	 * @return	string						action to be executed when the AddRecurringCharge menu item is clicked
	 *
	 * @method
	 */
	function AddRecurringCharge($intAccountId, $intServiceId = NULL) {
		$this->strContextMenuLabel = "Request Recurring Charge";

		$this->strLabel = "Request Recurring Charge";

		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;
		$arrData['Service']['Id'] = $intServiceId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddRecurringChargePopupId\", \"medium\", \"Request Recurring Charge\", \"Charge\", \"AddRecurring\", $strJsonCode)";
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
	 * @param	int		$intServiceId		[optional] id of the service that the Adjustment is associated with
	 *
	 * @return	string				action to be executed when the AddAdjustment menu item is clicked
	 *
	 * @method
	 */
	function AddAdjustment($intAccountId, $intServiceId = NULL) {
		$this->strContextMenuLabel = "Request Adjustment";

		$this->strLabel = "Request Adjustment";

		// Setup data to send
		$arrData['Account']['Id'] = $intAccountId;
		$arrData['Service']['Id'] = $intServiceId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AddAdjustmentPopupId\", \"medium\", \"Request Adjustment\", \"Adjustment\", \"Add\", $strJsonCode)";
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
	function MakePayment($intId) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "make payment";

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
	function EditAccount($intId) {
		$this->strContextMenuLabel = "Edit";

		$this->strLabel = "edit account";

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
	function ListServices($intId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "Services";

		/*  The Old Way of open up the list of services in a popup.  Retain this as it will be eventually used again
		// Setup data to send
		$arrData['Account']['Id'] = $intId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"AccountServicesPopupId\", \"ExtraLarge\", null, \"Account\", \"ViewServices\", $strJsonCode)";
		*/

		// View the list of Services as a page
		return self :: NEW_FRAMEWORK . "flex.php/Account/ViewServices/?Account.Id=$intId";
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
	function DeletePayment($intPaymentId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "delete payment: $intPaymentId";

		// Setup data to send
		$arrData['DeleteRecord']['RecordType'] = "Payment";
		$arrData['Payment']['Id'] = $intPaymentId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeletePaymentPopupId\", \"medium\", \"Reverse Payment\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// DeleteCharge
	//------------------------------------------------------------------------//
	/**
	 * DeleteCharge()
	 *
	 * Compiles the javascript to be executed when the DeleteCharge menu item is clicked
	 *
	 * Compiles the javascript to be executed when the DeleteCharge menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intChargeId		id of the charge to delete
	 *
	 * @return	string							action to be executed when the DeleteCharge menu item is clicked
	 *
	 * @method
	 */
	function DeleteCharge($intChargeId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "delete charge: $intChargeId";

		// Setup data to send
		$arrData['DeleteRecord']['RecordType'] = "Charge";
		$arrData['Charge']['Id'] = $intChargeId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteChargePopupId\", \"medium\", \"Charge\", \"Account\", \"DeleteRecord\", $strJsonCode)";
	}

	//------------------------------------------------------------------------//
	// CancelRecurringCharge
	//------------------------------------------------------------------------//
	/**
	 * CancelRecurringCharge()
	 *
	 * Compiles the javascript to be executed when the CancelRecurringCharge menu item is clicked
	 *
	 * Compiles the javascript to be executed when the CancelRecurringCharge menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int		$intRecurringChargeId		id of the recurring charge to Cancel
	 *
	 * @return	string									action to be executed when the CancelRecurringCharge menu item is clicked
	 *
	 * @method
	 */
	function CancelRecurringCharge($intRecurringChargeId) {
		$this->strContextMenuLabel = "";
		$this->strLabel = "cancel recurring charge: $intRecurringChargeId";

		// Setup data to send
		$arrData['DeleteRecord']['RecordType'] = "RecurringCharge";
		$arrData['RecurringCharge']['Id'] = $intRecurringChargeId;

		// Convert to JSON notation
		$strJsonCode = Json()->encode($arrData);

		return "javascript:Vixen.Popup.ShowAjaxPopup(\"DeleteRecurringChargePopupId\", \"medium\", \"Recurring Charge\", \"Account\", \"DeleteRecord\", $strJsonCode)";
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
	function Provisioning($intServiceId = NULL, $intAccountId = NULL) {
		$this->strContextMenuLabel = "Make Request";

		if ($intServiceId == NULL && $intAccountId == NULL) {
			throw new Exception("Must specify an AccountId or ServiceId");
		}

		if ($intServiceId) {
			$strParameter = "Service.Id=$intServiceId";
		} else {
			$strParameter = "Account.Id=$intAccountId";
		}

		$this->strLabel = "Provisioning";
		return self :: NEW_FRAMEWORK . "flex.php/Provisioning/BulkProvisioningRequest/?$strParameter";
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
	function KnowledgeBase() {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Knowledge Base";
		return self :: NEW_FRAMEWORK . "flex.php/KnowledgeBase/ListArticles/";
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
	function ViewKnowledgeBaseArticle($intId) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Article";
		return self :: NEW_FRAMEWORK . "flex.php/KnowledgeBase/ViewArticle/?KnowledgeBase.Id=$intId";
	}

	//------------------------------------------------------------------------//
	// EditInvoiceRunEvents
	//------------------------------------------------------------------------//
	/**
	 * EditInvoiceRunEvents()
	 *
	 * Compiles the Href to be executed when the EditInvoiceRunEvents menu item is clicked
	 *
	 * Compiles the Href to be executed when the EditInvoiceRunEvents menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @param	int			$intId		id of the invoice run to view events for
	 *
	 * @return	string					Href to be executed when the EditInvoiceRunEvents menu item is clicked
	 *
	 * @method
	 */
	function EditInvoiceRunEvents($intId) {
		$this->strContextMenuLabel = "";

		$this->strLabel = "Events";
		return "javascript:Vixen.InvoiceRunEvents.RenderDetailsForViewing($intId);";
	}

	//------------------------------------------------------------------------//
	// ManageInvoiceRunEvents
	//------------------------------------------------------------------------//
	/**
	 * ManageInvoiceRunEvents()
	 *
	 * Compiles the Href to be executed when the ManageInvoiceRunEvents menu item is clicked
	 *
	 * Compiles the Href to be executed when the ManageInvoiceRunEvents menu item is clicked
	 * Also compiles the label to use if it is being used as a BreadCrumb.
	 *
	 * @return	string					Href to be executed when the ManageInvoiceRunEvents menu item is clicked
	 *
	 * @method
	 */
	function ManageInvoiceRunEvents() {
		$this->strContextMenuLabel = "Manage Invoice Run Events";

		$this->strLabel = "Invoice Run Events";
		return self :: NEW_FRAMEWORK . "flex.php/InvoiceRunEvents/Manage";
	}

	//------------------------------------------------------------------------//
	// AdvancedAccountSearch
	//------------------------------------------------------------------------//
	/**
	 * AdvancedAccountSearch()
	 *
	 * Compiles the Href to be executed when the AdvancedAccountSearch menu item is triggered
	 *
	 * Compiles the Href to be executed when the AdvancedAccountSearch menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function AdvancedAccountSearch() {
		self::deprecatedMenuItem('AdvancedAccountSearch', self::OLD_FRAMEWORK."account_list.php");
		/*$this->strContextMenuLabel = "Advanced Account Search";
		$this->strLabel = "Advanced Account Search";
		return self :: OLD_FRAMEWORK . "account_list.php";*/
	}

	//------------------------------------------------------------------------//
	// AdvancedContactSearch
	//------------------------------------------------------------------------//
	/**
	 * AdvancedContactSearch()
	 *
	 * Compiles the Href to be executed when the AdvancedContactSearch menu item is triggered
	 *
	 * Compiles the Href to be executed when the AdvancedContactSearch menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function AdvancedContactSearch() {
		self::deprecatedMenuItem('AdvancedContactSearch', self::OLD_FRAMEWORK."contact_list.php");
		/*$this->strContextMenuLabel = "Advanced Contact Search";
		$this->strLabel = "Advanced Contact Search";
		return self :: OLD_FRAMEWORK . "contact_list.php";*/
	}

	//------------------------------------------------------------------------//
	// AdvancedServiceSearch
	//------------------------------------------------------------------------//
	/**
	 * AdvancedServiceSearch()
	 *
	 * Compiles the Href to be executed when the AdvancedServiceSearch menu item is triggered
	 *
	 * Compiles the Href to be executed when the AdvancedServiceSearch menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function AdvancedServiceSearch() {
		self::deprecatedMenuItem('AdvancedServiceSearch', self::OLD_FRAMEWORK."service_list.php");
		/*$this->strContextMenuLabel = "Advanced Service Search";
		$this->strLabel = "Advanced Service Search";
		return self :: OLD_FRAMEWORK . "service_list.php";*/
	}

	//------------------------------------------------------------------------//
	// ManageCharges
	//------------------------------------------------------------------------//
	/**
	 * ManageCharges()
	 *
	 * Compiles the Href to be executed when the ManageCharges menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageCharges menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageChargesOld() {
		self::deprecatedMenuItem('ManageChargesOld', self::OLD_FRAMEWORK."charges_approve.php");
		/*$this->strContextMenuLabel = "OLD - Approve and Decline Charges - OLD";
		$this->strLabel = "Manage Charges";
		return self :: OLD_FRAMEWORK . "charges_approve.php";*/
	}

	/**
	 * ManageChargeRequests()
	 *
	 * Compiles the Href to be executed when the ManageChargeRequests menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageChargeRequests menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageChargeRequests() {
		$this->strContextMenuLabel = "Manage Charge Requests";
		$this->strLabel = "Manage Charge Requests";
		return self::NEW_FRAMEWORK . "reflex.php/Charge/ManageChargeRequests/";
	}


	/**
	 * ManageRecurringChargeRequests()
	 *
	 * Compiles the Href to be executed when the ManageRecurringChargeRequests menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageRecurringChargeRequests menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageRecurringChargeRequests() {
		$this->strContextMenuLabel = "Manage Recurring Charge Requests";
		$this->strLabel = "Manage Recurring Charge Requests";
		return self::NEW_FRAMEWORK . "reflex.php/RecurringCharge/ManageRecurringChargeRequests/";
	}


	//------------------------------------------------------------------------//
	// ManageSingleChargeTypes
	//------------------------------------------------------------------------//
	/**
	 * ManageSingleChargeTypes()
	 *
	 * Compiles the Href to be executed when the ManageSingleChargeTypes menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageSingleChargeTypes menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageSingleChargeTypes() {
		$this->strContextMenuLabel = "Manage Single Charge Types";
		$this->strLabel = "Manage Single Charge Types";
		return self::NEW_FRAMEWORK . "reflex.php/ChargeType/Manage/";
	}

	//------------------------------------------------------------------------//
	// ManageRecurringChargeTypes
	//------------------------------------------------------------------------//
	/**
	 * ManageRecurringChargeTypes()
	 *
	 * Compiles the Href to be executed when the ManageRecurringChargeTypes menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageRecurringChargeTypes menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageRecurringChargeTypes() {
		$this->strContextMenuLabel = "Manage Recurring Charge Types";
		$this->strLabel = "Manage Recurring Charge Types";
		return self::NEW_FRAMEWORK . "reflex.php/RecurringChargeType/Manage/";
	}

	//------------------------------------------------------------------------//
	// ManageAdjustmentRequests
	//------------------------------------------------------------------------//
	/**
	 * ManageAdjustmentRequests()
	 *
	 * Compiles the Href to be executed when the ManageAdjustmentRequests menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageAdjustmentRequests menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageAdjustmentRequests() {
		$this->strContextMenuLabel = "Manage Adjustment Requests";
		$this->strLabel = "Manage Adjustment Requests";
		return self::NEW_FRAMEWORK . "reflex.php/Adjustment/ManageRequests/";
	}

	//------------------------------------------------------------------------//
	// ManageAdjustmentTypes
	//------------------------------------------------------------------------//
	/**
	 * ManageAdjustmentTypes()
	 *
	 * Compiles the Href to be executed when the ManageAdjustmentTypes menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageAdjustmentTypes menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageAdjustmentTypes() {
		$this->strContextMenuLabel = "Manage Adjustment Types";
		$this->strLabel = "Manage Adjustment Types";
		return self::NEW_FRAMEWORK . "reflex.php/Adjustment/ManageTypes/";
	}

	//------------------------------------------------------------------------//
	// PaymentDownload
	//------------------------------------------------------------------------//
	/**
	 * PaymentDownload()
	 *
	 * Compiles the Href to be executed when the PaymentDownload menu item is triggered
	 *
	 * Compiles the Href to be executed when the PaymentDownload menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function PaymentDownload() {
		self::deprecatedMenuItem('PaymentDownload', self::OLD_FRAMEWORK."payment_download.php");
		/*$this->strContextMenuLabel = "Payment Download";
		$this->strLabel = "Payment Download";
		return self :: OLD_FRAMEWORK . "payment_download.php";*/
	}

	//------------------------------------------------------------------------//
	// MoveDelinquentCDRs
	//------------------------------------------------------------------------//
	/**
	 * MoveDelinquentCDRs()
	 *
	 * Compiles the Href to be executed when the MoveDelinquentCDRs menu item is triggered
	 *
	 * Compiles the Href to be executed when the MoveDelinquentCDRs menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function MoveDelinquentCDRs() {
		$this->strContextMenuLabel = "Delinquent CDRs";
		$this->strLabel = "Delinquent CDRs";
		return self :: NEW_FRAMEWORK . "flex.php/Misc/MoveDelinquentCDRs/";
	}

	//------------------------------------------------------------------------//
	// DataReports
	//------------------------------------------------------------------------//
	/**
	 * DataReports()
	 *
	 * Compiles the Href to be executed when the DataReports menu item is triggered
	 *
	 * Compiles the Href to be executed when the DataReports menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function DataReports() {
		$this->strContextMenuLabel = "Data Reports";
		$this->strLabel = "Data Reports";
		return self::NEW_FRAMEWORK . "reflex.php/DataReport/ListAll/";
	}

	//------------------------------------------------------------------------//
	// ManageEmployees
	//------------------------------------------------------------------------//
	/**
	 * ManageEmployees()
	 *
	 * Compiles the Href to be executed when the ManageEmployees menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageEmployees menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageEmployees() {
		$this->strContextMenuLabel = "Manage Employees";
		$this->strLabel = "Employees";
		return self :: NEW_FRAMEWORK . "flex.php/Employee/EmployeeList/";
		// Removed until permissions release. rmctainsh 20100429
		//return self :: NEW_FRAMEWORK . "reflex.php/Employee/EmployeeList/";
	}

	//------------------------------------------------------------------------//
	// ManageBreachedContracts
	//------------------------------------------------------------------------//
	/**
	 * ManageBreachedContracts()
	 *
	 * Compiles the Href to be executed when the ManageBreachedContracts menu item is triggered
	 *
	 * Compiles the Href to be executed when the ManageBreachedContracts menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageBreachedContracts() {
		$this->strContextMenuLabel = "Manage Breached Contracts";
		$this->strLabel = "Manage Breached Contracts";
		return self :: NEW_FRAMEWORK . "reflex.php/Contract/ManageBreached/";
	}

	//------------------------------------------------------------------------//
	// TelemarketUploadProposed
	//------------------------------------------------------------------------//
	/**
	 * TelemarketUploadProposed()
	 *
	 * Compiles the Href to be executed when the TelemarketUploadProposed menu item is triggered
	 *
	 * Compiles the Href to be executed when the TelemarketUploadProposed menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function TelemarketUploadProposed() {
		$this->strContextMenuLabel = "Upload Proposed Dialling List";
		$this->strLabel = "Upload Proposed Dialling List";

		return "javascript:JsAutoLoader.loadScript('javascript/telemarketing.js', function(){JsAutoLoader.loadScript('javascript/telemarketing_proposedupload.js', function(){Flex.Telemarketing.ProposedUpload.displayPopupUpload();})});";
	}

	//------------------------------------------------------------------------//
	// TelemarketDownloadDNCR
	//------------------------------------------------------------------------//
	/**
	 * TelemarketDownloadDNCR()
	 *
	 * Compiles the Href to be executed when the TelemarketDownloadDNCR menu item is triggered
	 *
	 * Compiles the Href to be executed when the TelemarketDownloadDNCR menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function TelemarketDownloadDNCR() {
		$this->strContextMenuLabel = "Download DNCR Wash List";
		$this->strLabel = "Download DNCR Wash List";

		return "javascript:JsAutoLoader.loadScript('javascript/telemarketing.js', function(){JsAutoLoader.loadScript('javascript/telemarketing_dncrdownload.js', function(){Flex.Telemarketing.DNCRDownload.displayPopupDownload();})});";
	}

	//------------------------------------------------------------------------//
	// TelemarketUploadDNCR
	//------------------------------------------------------------------------//
	/**
	 * TelemarketUploadDNCR()
	 *
	 * Compiles the Href to be executed when the TelemarketUploadDNCR menu item is triggered
	 *
	 * Compiles the Href to be executed when the TelemarketUploadDNCR menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function TelemarketUploadDNCR() {
		$this->strContextMenuLabel = "Upload Washed DNCR List";
		$this->strLabel = "Upload Washed DNCR List";

		return "javascript:JsAutoLoader.loadScript('javascript/telemarketing.js', function(){JsAutoLoader.loadScript('javascript/telemarketing_dncrupload.js', function(){Flex.Telemarketing.DNCRUpload.displayPopupUpload();})});";
	}

	//------------------------------------------------------------------------//
	// TelemarketDownloadPermitted
	//------------------------------------------------------------------------//
	/**
	 * TelemarketDownloadPermitted()
	 *
	 * Compiles the Href to be executed when the TelemarketDownloadPermitted menu item is triggered
	 *
	 * Compiles the Href to be executed when the TelemarketDownloadPermitted menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function TelemarketDownloadPermitted() {
		$this->strContextMenuLabel = "Download Permitted Dialling List";
		$this->strLabel = "Download Permitted Dialling List";

		return "javascript:JsAutoLoader.loadScript('javascript/telemarketing.js', function(){JsAutoLoader.loadScript('javascript/telemarketing_permitteddownload.js', function(){Flex.Telemarketing.PermittedDownload.displayPopupDownload();})});";
	}

	//------------------------------------------------------------------------//
	// TelemarketUploadDiallerReport
	//------------------------------------------------------------------------//
	/**
	 * TelemarketUploadDiallerReport()
	 *
	 * Compiles the Href to be executed when the TelemarketUploadDiallerReport menu item is triggered
	 *
	 * Compiles the Href to be executed when the TelemarketUploadDiallerReport menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function TelemarketUploadDiallerReport() {
		$this->strContextMenuLabel = "Upload Dialler Report";
		$this->strLabel = "Upload Dialler Report";

		return "javascript:JsAutoLoader.loadScript('javascript/telemarketing.js', function(){JsAutoLoader.loadScript('javascript/telemarketing_diallerreportupload.js', function(){Flex.Telemarketing.DiallerReportUpload.displayPopupUpload();})});";
	}

	//------------------------------------------------------------------------//
	// TelemarketDownloadReconciliationReport
	//------------------------------------------------------------------------//
	/**
	 * TelemarketDownloadReconciliationReport()
	 *
	 * Compiles the Href to be executed when the TelemarketDownloadReconciliationReport menu item is triggered
	 *
	 * Compiles the Href to be executed when the TelemarketDownloadReconciliationReport menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function TelemarketDownloadReconciliationReport() {
		$this->strContextMenuLabel = "Download Reconciliation Report";
		$this->strLabel = "Download Reconciliation Report";

		return "javascript:JsAutoLoader.loadScript('javascript/telemarketing.js', function(){JsAutoLoader.loadScript('javascript/telemarketing_reconciliationdownload.js', function(){Flex.Telemarketing.ReconciliationDownload.displayPopupDownload();})});";
	}

	/**
	 * GenerateInterimInvoice()
	 *
	 * Compiles the Href to be executed when the GenerateInterimInvoice menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function GenerateInterimInvoice($intAccount, $intInvoiceRunType)
	{
		$this->strContextMenuLabel = "Generate Final/Interim Invoice";
		$this->strLabel = "Generate Final/Interim Invoice";

		return "JsAutoLoader.loadScript(\"javascript/invoice.js\", function(){Flex.Invoice.getPreGenerateValues({$intAccount});});";
	}

	/**
	 * CommitInterimInvoice()
	 *
	 * Compiles the Href to be executed when the CommitInterimInvoice menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function CommitInterimInvoice($intInvoice)
	{
		$this->strContextMenuLabel = "Commit Final/Interim Invoice";
		$this->strLabel = "Commit Final/Interim Invoice";

		return "JsAutoLoader.loadScript(\"javascript/invoice.js\", function(){Flex.Invoice.commitInterimInvoiceConfirm({$intInvoice});});";
	}

	/**
	 * RevokeInterimInvoice()
	 *
	 * Compiles the Href to be executed when the CommitInterimInvoice menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function RevokeInterimInvoice($intInvoice)
	{
		$this->strContextMenuLabel = "Revoke Final/Interim Invoice";
		$this->strLabel = "Revoke Final/Interim Invoice";

		return "JsAutoLoader.loadScript(\"javascript/invoice.js\", function(){Flex.Invoice.revokeInterimInvoiceConfirm({$intInvoice});});";
	}

	/**
	 * ViewContactList()
	 *
	 * Compiles the Href to be executed when the ViewContactList menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ViewInternalContactList()
	{
		$this->strContextMenuLabel = "Contact List";
		$this->strLabel = "Revoke Final/Interim Invoice";

		return "javascript:JsAutoLoader.loadScript('javascript/internal_contact_list.js', function(){Flex.InternalContactList.renderViewPopup();});";
	}

	/**
	 * TelemarketingBlacklistAddFNN()
	 *
	 * Compiles the Href to be executed when the TelemarketingBlacklistAddFNN menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function TelemarketingBlacklistAddFNN()
	{
		$this->strContextMenuLabel = "Add FNN to Blacklist";
		$this->strLabel = "Add FNN to Blacklist";

		return "javascript:JsAutoLoader.loadScript('javascript/telemarketing.js', function(){Flex.Telemarketing.addFNNToBlacklist();});";
	}

	/**
	 * ShowDocumentExplorer()
	 *
	 * Compiles the Href to be executed when the ShowDocumentExplorer menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ShowDocumentExplorer()
	{
		$this->strContextMenuLabel = "Documents";
		$this->strLabel = "Document Explorer";

		return "javascript:JsAutoLoader.loadScript('javascript/document.js', function(){JsAutoLoader.loadScript('javascript/document_explorer.js', function(){Flex.Document.Explorer.update(null);})});";
	}

	/**
	 * DownloadInterimEligibilityReport()
	 *
	 * Compiles the Href to be executed when the SubmitInterimInterimEligibilityReport menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function DownloadInterimEligibilityReport()
	{
		$this->strContextMenuLabel = "Download Interim Invoice Eligibility Report";
		$this->strLabel = "Download Interim Invoice Eligibility Report";

		return self :: NEW_FRAMEWORK . "reflex.php/Invoice/InterimEligibilityReport/";
	}

	/**
	 * SubmitInterimInterimEligibilityReport()
	 *
	 * Compiles the Href to be executed when the SubmitInterimInterimEligibilityReport menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function SubmitInterimInterimEligibilityReport()
	{
		$this->strContextMenuLabel = "Submit Interim Invoice Eligibility Report";
		$this->strLabel = "Submit Interim Invoice Eligibility Report";

		return "javascript:JsAutoLoader.loadScript('javascript/invoice.js', function(){Flex.Invoice.buildPopupSubmitInterimInvoiceReport()});";
	}

	/**
	 * ManageActionTypes()
	 *
	 * Compiles the Href to be executed when the ManageActionTypes menu item is triggered
	 *
	 * @return	string					Href to trigger the functionality
	 * @method
	 */
	function ManageActionTypes()
	{
		$this->strContextMenuLabel = "Manage Action Types";
		$this->strLabel = "Manage Action Types";

		return self::NEW_FRAMEWORK . "reflex.php/ActionType/Manage/";
	}

	function ManageAllFollowUps()
	{
		$this->strContextMenuLabel 	= "Manage Follow Ups";
		$this->strLabel 			= "Manage Follow Ups";
		return self::NEW_FRAMEWORK . "reflex.php/FollowUp/Manage/All/";
	}

	function ManageAllRecurringFollowUps()
	{
		$this->strContextMenuLabel 	= "Manage Recurring Follow Ups";
		$this->strLabel 			= "Manage Recurring Follow Ups";
		return self::NEW_FRAMEWORK . "reflex.php/FollowUp/ManageRecurring/All/";
	}

	function ConfigureFollowUps()
	{
		$this->strContextMenuLabel 	= "Configure";
		$this->strLabel 			= "Configure";
		return self::NEW_FRAMEWORK . "reflex.php/FollowUp/Configure/";
	}

	function MyFollowUps()
	{
		$this->strContextMenuLabel 	= "My Follow Ups";
		$this->strLabel 			= "My Follow Ups";
		return self::NEW_FRAMEWORK . "reflex.php/FollowUp/Manage/";
	}

	function MyRecurringFollowUps()
	{
		$this->strContextMenuLabel 	= "My Recurring Follow Ups";
		$this->strLabel 			= "My Recurring Follow Ups";
		return self::NEW_FRAMEWORK . "reflex.php/FollowUp/ManageRecurring/";
	}

	//------------------------------------------------------------------------//
	// ActionsAndNotesCreatorPopup
	//------------------------------------------------------------------------//
	/**
	 * ActionsAndNotesCreatorPopup()
	 *
	 * Compiles the Href (javascript) to execute this functionality
	 *
	 * Compiles the Href (javascript) to execute this functionality
	 *
	 * @param	int			$intAccountId		id of the account to associate the notes and actions with
	 * @param	int			$intServiceId		id of the service to associate the notes and actions with
	 * @param	int			$intContactId		id of the contact to associate the notes and actions with
	 * @param	string		$strTitle			Title for the popup (usually the Account Name/number, or the service FNN)
	 *
	 * @return	string							Href(script) to trigger the functionality
	 * @method
	 */
	function ActionsAndNotesCreatorPopup($intAccountId, $intServiceId, $intContactId, $strTitle=null)
	{
		$jsonAccountId	= JSON_Services::encode($intAccountId);
		$jsonServiceId	= JSON_Services::encode($intServiceId);
		$jsonContactId	= JSON_Services::encode($intContactId);
		$jsonTitle		= JSON_Services::encode($strTitle);

		$this->strContextMenuLabel = "Create Actions / Notes";
		return "javascript:
if (window.ActionsAndNotes)
{
	Flex.ActionsAndNotesCreatorPopup = ActionsAndNotes.Creator.createPopup($jsonTitle, $jsonAccountId, $jsonServiceId, $jsonContactId);
	Flex.ActionsAndNotesCreatorPopup.display();
}
else
{
	JsAutoLoader.loadScript('javascript/actions_and_notes.js', 	function()
																{
																	ActionsAndNotes.load(	function()
																							{
																								Flex.ActionsAndNotesCreatorPopup = ActionsAndNotes.Creator.createPopup($jsonTitle, $jsonAccountId, $jsonServiceId, $jsonContactId);
																								Flex.ActionsAndNotesCreatorPopup.display();
																							});
																});
}
";
	}

	//------------------------------------------------------------------------//
	// ActionsAndNotesListPopup
	//------------------------------------------------------------------------//
	/**
	 * ActionsAndNotesListPopup()
	 *
	 * Compiles the Href (javascript) to execute this functionality
	 *
	 * Compiles the Href (javascript) to execute this functionality
	 *
	 * @param	int			$intAATContextId					ActionAssocationType representing the context in which the list of Actions And Notes will refer to
	 * 															For example, if this is set to ACTION_ASSOCIATION_TYPE_SERVICE, then the list will be refering
	 * 															to the Actions And Notes of a single service with its id declared as $intAATContextReferenceId
	 * @param	int			$intAATContextReferenceId			account id / contact id / service id
	 * @param	int			$bolIncludeAllRelatableAATTypes		If TRUE, then the list will contain all actions and notes relating to $intAATContextReferenceId
	 * 															as well as all actions and notes relating to other entities that are relatable to $intAATContextReferenceId
	 *															For example, if $intAATContextId was ACTION_ASSOCIATION_TYPE_ACCOUNT, and $bolIncludeAllRelatableAATTypes == TRUE
	 *															Then the list will show all Actions and Notes relating to the account, as well as all Actions and Notes relating to
	 *															all servies of the account, and all contacts associated with the account
	 * @param	int			$intMaxRecordsPerPage				Max number of records to show per page, when paginating the list of actions and notes
	 * @param	string		$strTitle							Title for the popup (usually the Account Name/number, or the service FNN)
	 *
	 * @return	string							Href(script) to trigger the functionality
	 * @method
	 */
	function ActionsAndNotesListPopup($intAATContextId, $intAATContextReferenceId, $bolIncludeAllRelatableAATTypes, $intMaxRecordsPerPage, $strTitle)
	{
		$jsonBolIncludeAllRelatableAATTypes = JSON_Services::encode($bolIncludeAllRelatableAATTypes);
		$jsonStrTitle = JSON_Services::encode($strTitle);

		$this->strContextMenuLabel = "View Actions / Notes";
		return "javascript:
if (window.ActionsAndNotes)
{
	ActionsAndNotes.load(	function()
							{
								Flex.ActionsAndNotesListPopup = ActionsAndNotes.List.createPopup($jsonStrTitle, $intAATContextId, $intAATContextReferenceId, $jsonBolIncludeAllRelatableAATTypes, $intMaxRecordsPerPage);
								Flex.ActionsAndNotesListPopup.display();
							});
}
else
{
	JsAutoLoader.loadScript(\"javascript/actions_and_notes.js\",function()
																{
																	ActionsAndNotes.load(	function()
																							{
																								Flex.ActionsAndNotesListPopup = ActionsAndNotes.List.createPopup($jsonStrTitle, $intAATContextId, $intAATContextReferenceId, $jsonBolIncludeAllRelatableAATTypes, $intMaxRecordsPerPage);
																								Flex.ActionsAndNotesListPopup.display();
																							});
																});
}
";
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
	function BreadCrumb($strName, $arrParams) {
		$this->strLabel = NULL;
		$arrReturn = Array ();
		$strName = str_replace('_', '', $strName);

		// call the menu item method specific to $strName
		$arrReturn['Href'] = call_user_func_array(array (
			$this,
			$strName
		), $arrParams);

		if (!$this->strLabel) {
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
	function ContextMenuItemLabel($strName, $arrParams) {
		$this->strContextMenuLabel = "";

		// call the menu item method specific to $strName
		call_user_func_array(array (
			$this,
			$strName
		), $arrParams);

		if ($this->strContextMenuLabel == "") {
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
	function __call($strName, $arrParams) {
		switch ($strName) {
			case "Logout" :
				return self :: NEW_FRAMEWORK . "flex.php/Employee/Logout/";
				break;
			case "AdminConsole" :
				self::deprecatedMenuItem('__call:AdminConsole', self::OLD_FRAMEWORK."console_admin.php");
				/*$this->strLabel = "Admin Console";
				return self :: OLD_FRAMEWORK . "console_admin.php";*/
				break;
			default;
				return "[insert generic HREF here]";

				break;
		}
	}

	private function deprecatedMenuItem($sFunctionName, $sURL)
	{
		try
		{
			// Create an assertion
			Flex::assert(
				false,
				"In MenuItems.php, the function {$sFunctionName} was called, which returns {$sURL}.",
				null,
				"Deprecated Page (Framework 1) Accessed: MenuItems.php - {$sFunctionName}"
			);
		}
		catch (Exception_Assertion $e)
		{
			// Do nothing, the assert function has sent the email already
		}
	}
}
?>
