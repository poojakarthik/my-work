<?php

class Application_Handler_Dealer extends Application_Handler
{
	const MAX_RECORDS_PER_PAGE = 24;
	
	// Handle a request for the home page of the Dealer system
	public function System($subPath)
	{
		return $this->ListDealers($subPath);
	}

	// Handle a request for the home page of the ticketing system
	//TODO! Convert this to ajax so I can reload it in the background without disturbing popups
	public function ListDealers($subPath)
	{
		// Check user permissions
		AuthenticatedUser()->PermissionOrDie(PERMISSION_SALES_ADMIN);
		
		BreadCrumb()->Employee_Console();
		BreadCrumb()->SetCurrentPage("Dealers");
		
		try
		{
			// We need to load all of the dealers matching any passed criteria (in $_POST)
			// Criteria include columns (array(field)), sorting (array(field=>direction)), offset (int, default=0) and limit (int, default=30) as well as:
	
			$intDefaultLimit = self::MAX_RECORDS_PER_PAGE;
	
			$strPathToken = count($subPath) ? strtolower($subPath[0]) : NULL;
	
			// Default all search settings to be 'blank'
			$intOffset			= 0;
			$intLimit			= $intDefaultLimit;
			$arrSort			= array();
			$intDealerStatusId	= Dealer_Status::ACTIVE;
			$intUpLineId		= NULL;
			$intCarrierId		= NULL;
			$strSearchString	= NULL;
	
			// If this search is based on last search, default all search settings to be those of the last search
			if (($strPathToken == 'last' || array_key_exists('last', $_REQUEST)) && array_key_exists('Dealers', $_SESSION) && array_key_exists('LastDealerList', $_SESSION['Dealers']))
			{
				$arrLastQuery		= unserialize($_SESSION['Dealers']['LastDealerList']);
				$arrSort			= $arrLastQuery['Sort'];
				$intOffset			= $arrLastQuery['Offset'];
				$arrOldFilter		= $arrLastQuery['Filter'];

				$intDealerStatusId	= array_key_exists('dealerStatusId', $arrOldFilter) ? $arrOldFilter['dealerStatusId']['Value'] : NULL;
				$intUpLineId		= array_key_exists('upLineId', $arrOldFilter) ? $arrOldFilter['upLineId']['Value'] : NULL;
				$intCarrierId		= array_key_exists('carrierId', $arrOldFilter) ? $arrOldFilter['carrierId']['Value'] : NULL;
				$strSearchString	= array_key_exists('searchString', $arrOldFilter) ? $arrOldFilter['searchString']['Value'] : NULL;
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
	
			$intDealerStatusId	= array_key_exists('dealerStatusId', $_REQUEST) ? (strlen($_REQUEST['dealerStatusId']) ? intval($_REQUEST['dealerStatusId']) : NULL) : $intDealerStatusId;
			$intUpLineId		= array_key_exists('upLineId', $_REQUEST) ? (strlen($_REQUEST['upLineId']) ? intval($_REQUEST['upLineId']) : NULL) : $intUpLineId;
			$intCarrierId		= array_key_exists('carrierId', $_REQUEST) ? (strlen($_REQUEST['carrierId']) ? intval($_REQUEST['carrierId']) : NULL) : $intCarrierId;
			$strSearchString	= array_key_exists('searchString', $_REQUEST) ? (strlen(trim($_REQUEST['searchString'])) ? trim($_REQUEST['searchString']) : NULL) : $strSearchString;
	
			$arrFilter = array();
			
			// Never include the system dealer in the list of dealers (Note that we will also have to filter on id for other reasons, so this can be overridden by
			// another dealer id, or list of dealer ids)
			$arrFilter['systemDealerId'] = array(	"Type"			=> Dealer::SEARCH_CONSTRAINT_DEALER_ID,
													"Value"			=> Dealer::SYSTEM_DEALER_ID,
													"Comparison"	=> '!='
											);

			if ($intDealerStatusId !== NULL)
			{
				$arrFilter['dealerStatusId'] = array(	"Type"			=> Dealer::SEARCH_CONSTRAINT_DEALER_STATUS_ID,
														"Value"			=> $intDealerStatusId,
														"Comparison"	=> '='
												);
			}
			
			if ($intUpLineId !== NULL)
			{
				$arrFilter['upLineId'] = array(	"Type"			=> Dealer::SEARCH_CONSTRAINT_UP_LINE_ID,
												"Value"			=> $intUpLineId,
												"Comparison"	=> '='
												);
			}
			
			if ($intCarrierId !== NULL)
			{
				$arrFilter['carrierId'] = array("Type"			=> Dealer::SEARCH_CONSTRAINT_CARRIER_ID,
												"Value"			=> $intCarrierId,
												"Comparison"	=> '='
												);
			}

			if ($strSearchString !== NULL)
			{
				$arrFilter['searchString'] = array(	"Type"			=> Dealer::SEARCH_CONSTRAINT_SEARCH_STRING,
													"Value"			=> $strSearchString,
													"Comparison"	=> '='
													);
			}
			
	
			$detailsToRender = array();
			$detailsToRender['Sort']	= $arrSort;
			$detailsToRender['Filter']	= $arrFilter;
			$detailsToRender['Offset']	= $intOffset;
			$detailsToRender['Limit']	= $intLimit;

			$_SESSION['Dealers']['LastDealerList'] = serialize($detailsToRender);

			$detailsToRender['Dealers']			= Dealer::getAll($arrFilter, $arrSort, $intLimit, $intOffset);
			$detailsToRender['Pagination']		= Dealer::getPaginationDetails();
			$detailsToRender['DealerStatuses']	= Dealer_Status::getAll();
			$detailsToRender['Managers']		= Dealer::getManagers();

			$this->LoadPage('dealer_list', HTML_CONTEXT_DEFAULT, $detailsToRender);
		}
		catch (Exception $e)
		{
			$arrDetailsToRender['Message'] = "An error occured when trying to view the list of dealers";
			$arrDetailsToRender['ErrorMessage'] = $e->getMessage();
			$this->LoadPage('error_page', HTML_CONTEXT_DEFAULT, $arrDetailsToRender);
		}
	}


}

?>
