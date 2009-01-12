<?php

class Application_Handler_Sales extends Application_Handler
{
	const MAX_RECORDS_PER_PAGE = 24;
	
	
	// Handle a request for the home page of the Sales (Flex) system
	public function System($subPath)
	{
		return $this->ListSales($subPath);
	}

	public function ViewSale($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SALES);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->ManageSales(TRUE);
		
		try
		{
			if (!Flex_Module::isActive(FLEX_MODULE_SALES_PORTAL))
			{
				throw new Exception("Sales Portal module is not active");
			}
			
			if (count($subPath))
			{
				$intSaleId = intval($subPath[0]);
			}
			else
			{
				throw new Exception("Sale Id has not been specified");
			}
			
			$objSale = DO_Sales_Sale::getForId($intSaleId);
			if ($objSale === NULL)
			{
				throw new Exception("Sale with id: $intSaleId, could not be found");
			}
			
			// Set the final breadcrumb to include the sale id
			BreadCrumb()->SetCurrentPage("Sale ". $objSale->id);
	
			$detailsToRender = array();
			$detailsToRender['Sale']	= $objSale;
			$detailsToRender['ExtraPath'] = $subPath;

			$this->LoadPage('sale_view', HTML_CONTEXT_DEFAULT, $detailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to build the \"View Sale\" page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}

	// Lists sales
	public function ListSales($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SALES);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Sales");
		
		try
		{
			if (!Flex_Module::isActive(FLEX_MODULE_SALES_PORTAL))
			{
				throw new Exception("Sales Portal module is not active");
			}
			
			// We need to load all of the sales matching any passed criteria (in $_REQUEST)
	
			$intDefaultLimit = self::MAX_RECORDS_PER_PAGE;
	
			$strPathToken = count($subPath) ? strtolower($subPath[0]) : NULL;
	
			// Default all search settings to be 'blank'
			$intOffset			= 0;
			$intLimit			= $intDefaultLimit;
			$arrSort			= array();
			$intDealerId		= NULL;
			$intManagerId		= NULL;
			$intSaleStatusId	= NULL;
			$intSaleTypeId		= NULL;
			$intVendorId		= NULL;
	
			// If this search is based on last search, default all search settings to be those of the last search
			if (($strPathToken == 'last' || array_key_exists('last', $_REQUEST)) && array_key_exists('Sales', $_SESSION) && array_key_exists('LastSalesList', $_SESSION['Sales']))
			{
				$arrLastQuery		= unserialize($_SESSION['Sales']['LastSalesList']);
				$arrSort			= $arrLastQuery['Sort'];
				$intOffset			= $arrLastQuery['Offset'];
				$arrOldFilter		= $arrLastQuery['Filter'];
				$intDealerId		= array_key_exists('dealerId', $arrOldFilter) ? $arrOldFilter['dealerId']['Value'] : NULL;
				$intManagerId		= array_key_exists('managerId', $arrOldFilter) ? $arrOldFilter['managerId']['Value'] : NULL;

				$intSaleStatusId	= array_key_exists('saleStatusId', $arrOldFilter) ? $arrOldFilter['saleStatusId']['Value'] : NULL;
				$intSaleTypeId		= array_key_exists('saleTypeId', $arrOldFilter) ? $arrOldFilter['saleTypeId']['Value'] : NULL;
				$intVendorId		= array_key_exists('vendorId', $arrOldFilter) ? $arrOldFilter['vendorId']['Value'] : NULL;
			}
	
			if (array_key_exists('offset', $_REQUEST))
			{
				$intOffset = max(intval($_REQUEST['offset']), 0);
			}
	
			if (array_key_exists('limit', $_REQUEST))
			{
				$intLimit = intval($_REQUEST['limit']); 
			}
	
			if ($intLimit <= 0)
			{
				$intLimit = $intDefaultLimit;
			}
	
			if (array_key_exists('sort', $_REQUEST))
			{
				$arrSort = array();
				foreach ($_REQUEST['sort'] as $strColumn=>$ascDesc)
				{
					if (!$ascDesc)
					{
						continue;
					}
					$strColumn = preg_replace("/[^a-z_|]+/i", '', $strColumn);
					if (!$strColumn)
					{
						continue;
					}
					$arrSort[$strColumn] = ($ascDesc[0] == 'a');
				}
			}
			if (count($arrSort) == 0)
			{
				// Default to sort by sale.id descending
				$arrSort[DO_Sales_Sale::ORDER_BY_SALE_ID] = FALSE;
			}
	
	
			// Filtering by manager overrides filtering by dealer
			$strDealerFilter = (array_key_exists('dealerFilter', $_REQUEST) && strlen($_REQUEST['dealerFilter']))? $_REQUEST['dealerFilter'] : NULL;
			$arrParts = array();
			if ($strDealerFilter !== NULL && preg_match("/^(?<filter>\w+)\|(?<value>\d+)$/", $strDealerFilter, $arrParts))
			{
				if ($arrParts['filter'] == DO_Sales_Sale::SEARCH_CONSTRAINT_MANAGER_ID)
				{
					$intManagerId = intval($arrParts['value']);
				}
				elseif ($arrParts['filter'] == DO_Sales_Sale::SEARCH_CONSTRAINT_DEALER_ID)
				{
					$intDealerId = intval($arrParts['value']);
				}
				else
				{
					throw new Exception("Invalid Sale filter: {$arrParts['filter']}");
				}
			}
			// Specifying a manager will override specifying a dealer
			$intDealerId = ($intManagerId === NULL) ? $intDealerId : NULL;
			
			$intSaleStatusId	= array_key_exists('saleStatusId', $_REQUEST) ? (strlen($_REQUEST['saleStatusId']) ? intval($_REQUEST['saleStatusId']) : NULL) : $intSaleStatusId;
			$intSaleTypeId		= array_key_exists('saleTypeId', $_REQUEST) ? (strlen($_REQUEST['saleTypeId']) ? intval($_REQUEST['saleTypeId']) : NULL) : $intSaleTypeId;
			$intVendorId		= array_key_exists('vendorId', $_REQUEST) ? (strlen($_REQUEST['vendorId']) ? intval($_REQUEST['vendorId']) : NULL) : $intVendorId;
				
			$arrFilter = array();
			// Never include the system dealer in the list of dealers (Note that we will also have to filter on id for other reasons, so this can be overridden by
			// another dealer id, or list of dealer ids)
			
			if ($intManagerId !== NULL)
			{
				$arrFilter['managerId'] = array(	"Type"	=> DO_Sales_Sale::SEARCH_CONSTRAINT_MANAGER_ID,
													"Value"	=> $intManagerId
												);
			}
			if ($intDealerId !== NULL)
			{
				$arrFilter['dealerId'] = array(	"Type"	=> DO_Sales_Sale::SEARCH_CONSTRAINT_DEALER_ID,
												"Value"	=> $intDealerId
												);
			}
			if ($intSaleStatusId !== NULL)
			{
				$arrFilter['saleStatusId'] = array(	"Type"	=> DO_Sales_Sale::SEARCH_CONSTRAINT_SALE_STATUS_ID,
													"Value"	=> $intSaleStatusId
													);
			}
			if ($intSaleTypeId !== NULL)
			{
				$arrFilter['saleTypeId'] = array(	"Type"	=> DO_Sales_Sale::SEARCH_CONSTRAINT_SALE_TYPE_ID,
													"Value"	=> $intSaleTypeId
													);
			}
			if ($intVendorId !== NULL)
			{
				$arrFilter['vendorId'] = array(	"Type"	=> DO_Sales_Sale::SEARCH_CONSTRAINT_VENDOR_ID,
												"Value"	=> $intVendorId
											);
			}
			
			$detailsToRender = array();
			$detailsToRender['Sort']	= $arrSort;
			$detailsToRender['Filter']	= $arrFilter;
			$detailsToRender['Offset']	= $intOffset;
			$detailsToRender['Limit']	= $intLimit;

			$_SESSION['Sales']['LastSalesList'] = serialize($detailsToRender);

			$detailsToRender['Sales']			= DO_Sales_Sale::searchFor($arrFilter, $arrSort, $intLimit, $intOffset);
			$detailsToRender['Pagination']		= DO_Sales_Sale::getPaginationDetails();
			$detailsToRender['Dealers']			= DO_Sales_Dealer::listAll();
			$detailsToRender['Managers']		= DO_Sales_Dealer::listManagers();
			$detailsToRender['SaleTypes']		= DO_Sales_SaleType::getAll();
			$detailsToRender['SaleStatuses']	= DO_Sales_SaleStatus::getAll();
			$detailsToRender['Vendors']			= DO_Sales_Vendor::getAll();
			
			$this->LoadPage('sale_list', HTML_CONTEXT_DEFAULT, $detailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured while trying to build the \"Sales Management\" page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}

	// Manages the Reporting functionality
	public function Report($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SALES_ADMIN);
		
		BreadCrumb()->EmployeeConsole();
		BreadCrumb()->ManageSales(TRUE);
		
		try
		{
			// Work out what report they want
			if (is_array($subPath) && count($subPath) >= 1)
			{
				$strReportType	= array_shift($subPath);
				$arrReportTypes	= Sales_Report::getReportTypes();
				
				if (!array_key_exists($strReportType, $arrReportTypes))
				{
					// Unknown report request
					throw new Exception("Unknown Report Request: {$strReportType}");
				}
			}
			else
			{
				// No specific report has been requested
				throw new Exception("A specific sales report has not been specified");
			}
			
			$strReportName = $arrReportTypes[$strReportType]['Name'];
			BreadCrumb()->SetCurrentPage($strReportName);
			
			if (count($subPath) == 1)
			{
				$strAction = strtolower(array_shift($subPath));
				if ($strAction == "getreport")
				{
					// The user wants to retrieve the cached SummaryReport
					if (	is_array($_SESSION['Sales']) && 
							is_array($_SESSION['Sales']['Report']) && 
							array_key_exists("Content", $_SESSION['Sales']['Report'])
						)
					{
						// A report has been cached. Send it to the user
						header("Content-Type: application/excel");
						header("Content-Disposition: attachment; filename=\"" . strtolower(str_replace(" ", "_", $strReportName)) ."_". date("Y_m_d") . ".xls" . "\"");
						echo $_SESSION['Sales']['Report']['Content'];
						
						// Remove it from the Session
						unset($_SESSION['Sales']['Report']['Content']);
						exit;
					}
				}
			}
		
			// Process Dealers
			$arrObjDealers		= Dealer::getAll("id != ". Dealer::SYSTEM_DEALER_ID, array("username" => TRUE));
			$arrObjManagers		= Dealer::getManagers();
			$arrDealers			= array();
			$arrSortedDealerIds	= array();
			foreach ($arrObjDealers as $intId=>$objDealer)
			{
				$dealer = new stdClass();
				$dealer->id			= $intId;
				$dealer->username	= $objDealer->username;
				$dealer->name		= $objDealer->firstName ." ". $objDealer->lastName;
				$dealer->upLineId	= $objDealer->upLineId;
				$dealer->isManager	= array_key_exists($intId, $arrObjManagers);
				$arrDealers[$intId]	= $dealer;
				
				// This is used by javascript so that the order of the dealers is retained (it's current sorted by username ASC)
				$arrSortedDealerIds[] = $intId;
			}

			// Find out when the first sale was created (although we should really be looking for the first sale that was verified)
			$arrSales = DO_Sales_Sale::searchFor(NULL, array(DO_Sales_Sale::ORDER_BY_CREATED_ON=>TRUE), 1);
			if (count($arrSales) == 1)
			{
				$doSale = current($arrSales);
				$strEarliestTimestamp = date("Y-m-d 00:00:00", strtotime($doSale->createdOn));
			}
			else
			{
				$strEarliestTimestamp = date("Y-m-d 00:00:00", strtotime("-3 months"));
			}
			
			$arrData = array(	"ReportType"		=> $strReportType,
								"ReportName"		=> $strReportName,
								"Dealers"			=> $arrDealers,
								"SortedDealerIds"	=> $arrSortedDealerIds,
								"SaleStatuses"		=> DO_Sales_SaleStatus::getAll(),
								"SaleItemStatuses"	=> DO_Sales_SaleItemStatus::getAll(),
								"EarliestTimestamp"	=> $strEarliestTimestamp
							);

			$this->LoadPage('sale_report', HTML_CONTEXT_DEFAULT, $arrData);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to prepare the Sales Report page";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
		
	}

}

?>
