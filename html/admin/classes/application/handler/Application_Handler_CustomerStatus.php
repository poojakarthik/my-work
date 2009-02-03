<?php

class Application_Handler_CustomerStatus extends Application_Handler
{

	// View all the Customer Statuses in a tabulated format
	public function ViewAll($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->SetCurrentPage("Customer Statuses");

		ContextMenu()->Customer_Statuses->ManageCustomerStatuses();
		ContextMenu()->Customer_Statuses->CustomerStatusSummaryReport();
		ContextMenu()->Customer_Statuses->CustomerStatusAccountReport();
		
		$arrDetailsToRender = array();
		$arrDetailsToRender = Customer_Status::getAllOrderedByPrecedence();

		$this->LoadPage('customer_status_view_all', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
	}

	// View the details of a single CustomerStatus
	public function View($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->Manage_Customer_Statuses();
		
		ContextMenu()->Customer_Statuses->ManageCustomerStatuses();
		ContextMenu()->Customer_Statuses->CustomerStatusSummaryReport();
		ContextMenu()->Customer_Statuses->CustomerStatusAccountReport();
		
		$intId = array_shift($subPath);
		
		try
		{
			$objStatus = Customer_Status::getForId($intId);
			if ($objStatus === NULL)
			{
				// Customer Status with id == $intId could not be found
				throw new exception("Could not find CustomerStatus with id: $intId");
			}
			
			BreadCrumb()->SetCurrentPage(htmlspecialchars($objStatus->name));
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['CustomerStatus'] = $objStatus;
	
			$this->LoadPage('customer_status_view', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to view CustomerStatus with id: $intId";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	// Edit the details of a single CustomerStatus
	public function Edit($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->Manage_Customer_Statuses();
		
		ContextMenu()->Customer_Statuses->ManageCustomerStatuses();
		ContextMenu()->Customer_Statuses->CustomerStatusSummaryReport();
		ContextMenu()->Customer_Statuses->CustomerStatusAccountReport();
		
		$intId = array_shift($subPath);
		
		try
		{
			$objStatus = Customer_Status::getForId($intId);
			if ($objStatus === NULL)
			{
				// Customer Status with id == $intId could not be found
				throw new exception("Could not find CustomerStatus with id: $intId");
			}
			
			BreadCrumb()->SetCurrentPage(htmlspecialchars($objStatus->name));
			
			$arrDetailsToRender = array();
			$arrDetailsToRender['CustomerStatus'] = $objStatus;
	
			$this->LoadPage('customer_status_edit', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to Edit CustomerStatus with id: $intId";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}
	
	// Manages the Customer Status Summary Report functionality
	public function SummaryReport($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->Manage_Customer_Statuses();
		BreadCrumb()->SetCurrentPage("Summary Report");
		
		ContextMenu()->Customer_Statuses->ManageCustomerStatuses();
		ContextMenu()->Customer_Statuses->CustomerStatusSummaryReport();
		ContextMenu()->Customer_Statuses->CustomerStatusAccountReport();

		if (is_array($subPath) && count($subPath) == 1)
		{
			$strAction = strtolower(array_shift($subPath));
			if ($strAction == "getreport")
			{
				// The user wants to retrieve the cached SummaryReport
				if (	is_array($_SESSION['CustomerStatus']) && 
						is_array($_SESSION['CustomerStatus']['SummaryReport']) && 
						array_key_exists("Content", $_SESSION['CustomerStatus']['SummaryReport'])
					)
				{
					// A report has been cached
					$strReport = $_SESSION['CustomerStatus']['SummaryReport']['Content'];
					
					// Remove it from the Session
					unset($_SESSION['CustomerStatus']['SummaryReport']['Content']);
					
					// Send it to the user
					header("Content-Type: application/excel");
					header("Content-Disposition: attachment; filename=\"" . "customer_status_summary_report_". date("Y_m_d") . ".xls" . "\"");
					echo $strReport;
					
					exit;
				}
			}
		}
		
		try
		{
			// Build CustomerGroup data
			$arrCustomerGroups = array();
			$arrCustomerGroups[] = array(	"Id"	=> "any",
											"Name"	=> "Any Customer Group"
										);
			$arrCustomerGroupObjects = Customer_Group::getAll();
			foreach ($arrCustomerGroupObjects as $objGroup)
			{
				$arrCustomerGroups[] = array(	"Id"	=> $objGroup->id,
												"Name"	=> $objGroup->name
											);
			}
			
			// Build Customer Status combo box data
			// You should probably sort this by name.  By default it will be sorted by precedence
			$arrCustomerStatuses = array();
			$arrCustomerStatusObjects = Customer_Status::getAll();
			foreach ($arrCustomerStatusObjects as $objStatus)
			{
				$arrCustomerStatuses[] = array(	"Id"	=> $objStatus->id,
												"Name"	=> $objStatus->name
											);
			}
			
			// Build InvoiceRun data
			$arrInvoiceRuns = array();
			
			// Retrieve all InvoiceRuns that have records present in the customer_status_history table
			$arrColumns = array(
								"Id"			=> "ir.Id",
								"InvoiceRun"	=> "ir.InvoiceRun",
								"BillingDate"	=> "ir.BillingDate",
								"CustomerGroup"	=> "cg.InternalName"
							);
			$strTables = "InvoiceRun AS ir INNER JOIN (SELECT DISTINCT invoice_run_id FROM customer_status_history) AS csh ON ir.Id = csh.invoice_run_id LEFT JOIN CustomerGroup AS cg ON ir.customer_group_id = cg.Id";
			$strWhere = "ir.invoice_run_type_id = ". INVOICE_RUN_TYPE_LIVE ." AND ir.invoice_run_status_id = ". INVOICE_RUN_STATUS_COMMITTED;
			$selInvoiceRuns = new StatementSelect($strTables, $arrColumns, $strWhere, "BillingDate DESC, Id DESC");
			if (($intRecCount = $selInvoiceRuns->Execute()) === FALSE)
			{
				// Error occurred when trying to retrieve the invoice runs
				throw new Exception("Failed to retrieve data of eligible Invoice Runs: ". $selInvoiceRuns->Error());
			}
			
			if ($intRecCount > 0)
			{
				$arrInvoiceRuns = $selInvoiceRuns->FetchAll();
			}
			
			$arrData = array(
								"CustomerStatuses"	=> $arrCustomerStatuses,
								"InvoiceRuns"		=> $arrInvoiceRuns,
								"CustomerGroups"	=> $arrCustomerGroups
							);

			$this->LoadPage('customer_status_summary_report', HTML_CONTEXT_DEFAULT, $arrData);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to prepare the Customer Status Summary Report page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		
	}
	
	// Manages the Customer Status Account Report functionality
	public function AccountReport($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->System_Settings_Menu();
		BreadCrumb()->Manage_Customer_Statuses();
		BreadCrumb()->SetCurrentPage("Account Report");
		
		ContextMenu()->Customer_Statuses->ManageCustomerStatuses();
		ContextMenu()->Customer_Statuses->CustomerStatusSummaryReport();
		ContextMenu()->Customer_Statuses->CustomerStatusAccountReport();

		if (is_array($subPath) && count($subPath) == 1)
		{
			$strAction = strtolower(array_shift($subPath));
			if ($strAction == "getreport")
			{
				// The user wants to retrieve the cached AccountReport
				if (	is_array($_SESSION['CustomerStatus']) && 
						is_array($_SESSION['CustomerStatus']['AccountReport']) && 
						array_key_exists("Content", $_SESSION['CustomerStatus']['AccountReport'])
					)
				{
					// A report has been cached 
					$strReport = $_SESSION['CustomerStatus']['AccountReport']['Content'];
					
					// Remove it from the Session
					unset($_SESSION['CustomerStatus']['AccountReport']['Content']);
					
					// Send it to the user
					header("Content-Type: application/excel");
					header("Content-Disposition: attachment; filename=\"" . "customer_status_account_report_". date("Y_m_d") . ".xls" . "\"");
					echo $strReport;
					
					exit;
				}
			}
			elseif ($strAction == "generatereport")
			{
				// Generate the report based on the GET parameters passed
				try
				{
					$arrCustomerGroups		= array();
					$arrCustomerStatuses	= array();
					$arrInvoiceRuns			= array();
					
					if (isset($GET['CustomerGroup']))
					{
						if (is_array($GET['CustomerGroup']))
						{
							$arrCustomerGroups = $GET['CustomerGroup'];
						}
						else
						{
							// Single Customer Group
							$arrCustomerGroups = array($GET['CustomerGroup']);
						}
					}
					else
					{
						// No Customer Groups specified, so don't restrict by customer group
						$arrCustomerGroups = NULL;
					}
					
					if (isset($GET['CustomerStatus']))
					{
						if (is_array($GET['CustomerStatus']))
						{
							$arrCustomerStatuses = $GET['CustomerStatus'];
						}
						else
						{
							// Single Customer Status
							$arrCustomerStatuses = array($GET['CustomerStatus']);
						}
					}
					else
					{
						// No Customer Statuses specified, so don't restrict by customer Statuses
						$arrCustomerStatuses = NULL;
					}
					
					if (isset($GET['InvoiceRun']))
					{
						if (is_array($GET['InvoiceRun']))
						{
							if (count($GET['InvoiceRun']) > 0)
							{
								// Restrict the report, to the first invoice run passed
								$arrInvoiceRuns = array($GET['InvoiceRun'][0]);
							}
							else
							{
								// No Invoice Run has been specified
								throw new Exception("Invoice Run has not been specified");
							}
						}
						else
						{
							// Single Invoice Run
							$arrInvoiceRuns = array($GET['InvoiceRun']);
						}
					}
					else
					{
						// No Invoice Run has been specified
						throw new Exception("Invoice Run has not been specified");
					}
					
					$strRenderMode = "excel";
					$objReportBuilder = new Customer_Status_Account_Report();
					$objReportBuilder->SetBoundaryConditions($arrCustomerGroups, $arrCustomerStatuses, $arrInvoiceRuns);
					$objReportBuilder->BuildReport();
			
					$strReport = $objReportBuilder->GetReport($strRenderMode);
					
					header("Content-Type: application/excel");
					header("Content-Disposition: attachment; filename=\"customer_status_account_report_". date("Y_m_d") . ".xls\"");
					echo $strReport;
					exit;
				}
				catch (Exception $e)
				{
					$arrDetailsToRender['Message'] = "An error occured when trying to prepare the Customer Status Account Report";
					$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
					$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
					return;
				}
			}
		}
		
		try
		{
			// Build CustomerGroup data
			$arrCustomerGroups = array();
			$arrCustomerGroupObjects = Customer_Group::getAll();
			foreach ($arrCustomerGroupObjects as $objGroup)
			{
				$arrCustomerGroups[] = array(	"Id"	=> $objGroup->id,
												"Name"	=> $objGroup->name
											);
			}
			
			// Build Customer Status combo box data
			// You should probably sort this by name.  By default it will be sorted by precedence
			$arrCustomerStatuses = array();
			$arrCustomerStatusObjects = Customer_Status::getAll();
			foreach ($arrCustomerStatusObjects as $objStatus)
			{
				$arrCustomerStatuses[] = array(	"Id"	=> $objStatus->id,
												"Name"	=> $objStatus->name
											);
			}
			
			// Build InvoiceRun data
			$arrInvoiceRuns = array();
			
			// Retrieve all InvoiceRuns that have records present in the customer_status_history table
			$arrColumns	= array(
								"Id"			=> "ir.Id",
								"InvoiceRun"	=> "ir.InvoiceRun",
								"BillingDate"	=> "ir.BillingDate",
								"CustomerGroup"	=> "cg.InternalName"
							);
			$strTables	= "InvoiceRun AS ir INNER JOIN (SELECT DISTINCT invoice_run_id FROM customer_status_history) AS csh ON ir.Id = csh.invoice_run_id LEFT JOIN CustomerGroup AS cg ON ir.customer_group_id = cg.Id";
			$strWhere	= "ir.invoice_run_type_id = ". INVOICE_RUN_TYPE_LIVE ." AND ir.invoice_run_status_id = ". INVOICE_RUN_STATUS_COMMITTED;
			$selInvoiceRuns = new StatementSelect($strTables, $arrColumns, $strWhere, "BillingDate DESC, Id DESC");
			if (($intRecCount = $selInvoiceRuns->Execute()) === FALSE)
			{
				// Error occurred when trying to retrieve the invoice runs
				throw new Exception("Failed to retrieve data of eligible Invoice Runs: ". $selInvoiceRuns->Error());
			}
			
			if ($intRecCount > 0)
			{
				$arrInvoiceRuns = $selInvoiceRuns->FetchAll();
			}
			
			$arrData = array(
								"CustomerStatuses"	=> $arrCustomerStatuses,
								"InvoiceRuns"		=> $arrInvoiceRuns,
								"CustomerGroups"	=> $arrCustomerGroups
							);

			$this->LoadPage('customer_status_account_report', HTML_CONTEXT_DEFAULT, $arrData);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to prepare the Customer Status Account Report page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		
	}
	
	// Displays account details for a given CustomerGroup/InvoiceRun/CustomerStatus combination
	// You will have to do pagination for this
	public function ViewAccounts($subPath)
	{
		//TODO!
	}

}

?>
