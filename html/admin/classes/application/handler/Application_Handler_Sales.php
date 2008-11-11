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
			
			// We need to load all of the sales matching any passed criteria (in $_POST)
	
			$intDefaultLimit = self::MAX_RECORDS_PER_PAGE;
	
			$strPathToken = count($subPath) ? strtolower($subPath[0]) : NULL;
	
			// Default all search settings to be 'blank'
			$intOffset		= 0;
			$intLimit		= $intDefaultLimit;
			$arrSort		= array();
	
			// If this search is based on last search, default all search settings to be those of the last search
			if (($strPathToken == 'last' || array_key_exists('last', $_REQUEST)) && array_key_exists('Sales', $_SESSION) && array_key_exists('LastSalesList', $_SESSION['Sales']))
			{
				$arrLastQuery		= unserialize($_SESSION['Sales']['LastSalesList']);
				$arrSort			= $arrLastQuery['Sort'];
				$arrOldFilter		= $arrLastQuery['Filter'];
				//$intDealerStatusId	= array_key_exists('dealerStatusId', $arrOldFilter) ? $arrOldFilter['dealerStatusId']['Value'] : NULL;
				//$intUpLineId		= array_key_exists('upLineId', $arrOldFilter) ? $arrOldFilter['upLineId']['Value'] : NULL;
				$intOffset			= $arrLastQuery['Offset'];
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
	
			//$intDealerStatusId	= array_key_exists('dealerStatusId', $_REQUEST) ? (strlen($_REQUEST['dealerStatusId']) ? intval($_REQUEST['dealerStatusId']) : NULL) : $intDealerStatusId;
			//$intUpLineId		= array_key_exists('upLineId', $_REQUEST) ? (strlen($_REQUEST['upLineId']) ? intval($_REQUEST['upLineId']) : NULL) : $intUpLineId;
	
			$arrFilter = array();
			// Never include the system dealer in the list of dealers (Note that we will also have to filter on id for other reasons, so this can be overridden by
			// another dealer id, or list of dealer ids)
	
			$detailsToRender = array();
			$detailsToRender['Sort']	= $arrSort;
			$detailsToRender['Filter']	= $arrFilter;
			$detailsToRender['Offset']	= $intOffset;
			$detailsToRender['Limit']	= $intLimit;

			$_SESSION['Sales']['LastSalesList'] = serialize($detailsToRender);

			$detailsToRender['Sales']			= DO_Sales_Sale::searchFor($arrFilter, $arrSort, $intLimit, $intOffset);
			$detailsToRender['Pagination']		= DO_Sales_Sale::getPaginationDetails();

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
