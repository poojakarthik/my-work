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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
		BreadCrumb()->ManageSales(TRUE);
		BreadCrumb()->SetCurrentPage("Sale");
		
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
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SUPER_ADMIN);
		
		BreadCrumb()->Admin_Console();
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
}

?>
