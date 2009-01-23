<?php
//----------------------------------------------------------------------------//
// Sales_Report_SaleItemSummary
//----------------------------------------------------------------------------//
/**
 * Sales_Report_SaleItemSummary
 *
 * Encapsulates the Sales Report, "SaleItem Summary" functionality
 *
 * Encapsulates the Sales Report, "SaleItem Summary" functionality
 *
 * @class	Sales_Report_SaleItemSummary
 * @extends	Sales_Report
 */
class Sales_Report_SaleItemSummary extends Sales_Report
{
	
	private $_strEarliestTime;
	private $_strLatestTime;
	private $_arrDealers;
	
	// This will store the tallies for each dealer / product combination
	// $_arrTotals[dealerId][productId][TotalVerified] = 5 etc
	private $_arrTotals;
	
	// This array defines the columns that will be included in the report
	protected $_arrColumns = array(
							"DealerUsername"					=> "Dealer",
							"DealerCarrier"						=> "Group",
							"VendorName"						=> "Vendor",
							"ProductTypeName"					=> "Product Type",
							"ProductName"						=> "Product",
							"TotalVerified"						=> "Total Verified",
							"TotalCompletedAndCooledOff"		=> "Completed and Cooled Off",
							"TotalCompletedButNotCooledOff"		=> "Completed but Not Cooled Off",
							"TotalCancelledAndClawedBack"		=> "Cancelled and Clawed Back",
							"TotalCancelledButNotClawedBack"	=> "Cancelled but Not Clawed Back",
							"TotalOutstanding"					=> "Total Outstanding"
							);
	
	protected $_reportType				= Sales_Report::REPORT_TYPE_SALE_ITEM_SUMMARY;
	protected $_arrAllowableRenderModes	= array(self::RENDER_MODE_EXCEL);
	
	// Sets the constraints for the report (and validates them)
	// TODO! define preconditions, such as the timestamps being in their correct format or NULL
	// At least one dealer must be specified
	// Will throw an exception if a problem is encountered
	public function setConstraints($objConstraints)
	{
		// Earliest Time
		if (isset($objConstraints->earliestTime))
		{
			if ($objConstraints->earliestTime === NULL)
			{
				// earliestTime is set to NULL, so use the earliest sale creation date
				$arrSales = DO_Sales_Sale::searchFor(NULL, array(DO_Sales_Sale::ORDER_BY_CREATED_ON=>TRUE), 1);
				if (count($arrSales) == 1)
				{
					$doSale = current($arrSales);
					$this->_strEarliestTime = date("Y-m-d 00:00:00", strtotime($doSale->createdOn));
				}
				else
				{
					// There are no sales in the system.  This is going to be a short report
					$this->_strEarliestTime = date("Y-m-d 00:00:00", strtotime("-3 months"));
				}
			}
			else
			{
				// Make sure it is in a correct format
				$this->_strEarliestTime = date("Y-m-d H:i:s", strtotime($objConstraints->earliestTime));
			}
		}
		else
		{
			throw new Exception("Earliest Verification Time has not been specified");
		}
		
		// Latest Time
		if (isset($objConstraints->latestTime))
		{
			if ($objConstraints->latestTime === NULL)
			{
				$this->_strLatestTime = date("Y-m-d 23:59:59");
			}
			else
			{
				// Make sure it is in a correct format
				$this->_strLatestTime = date("Y-m-d H:i:s", strtotime($objConstraints->latestTime));
			}
		}
		else
		{
			throw new Exception("Latest Verification Time has not been specified");
		}
		
		if ($this->_strLatestTime < $this->_strEarliestTime)
		{
			throw new Exception("Earliest Verification Time ({$this->_strEarliestTime}) is greater than the Latest Verification Time ({$this->_strLatestTime})");
		}
		
		// Dealers
		$this->_arrDealers = array();
		if (isset($objConstraints->dealers) && is_array($objConstraints->dealers) && count($objConstraints->dealers))
		{
			// Dealers have been specified
			foreach ($objConstraints->dealers as $objDealerDetails)
			{
				if (!isset($objDealerDetails->id))
				{
					throw new Exception("Dealer has been defined without an id");
				}
				
				// Check that the dealer isn't already included in the array of dealers for the report
				if (array_key_exists($objDealerDetails->id, $this->_arrDealers))
				{
					throw new Exception("Dealer with id: {$objDealerDetails->id} has been included more than once");
				}
				
				$objDealerDetails->dealer = Dealer::getForId($objDealerDetails->id, TRUE);
				
				if (!isset($objDealerDetails->includeSubordinates))
				{
					// The includeSubordinates flag has not even been specified, so set it to false
					$objDealerDetails->includeSubordinates = FALSE;
				}
				
				if ($objDealerDetails->includeSubordinates)
				{
					// Build an array of ALL Subordinates that the dealer has
					$arrSubordinates = $objDealerDetails->dealer->getSubordinates();
					$objDealerDetails->subordinates = array();
					
					foreach($arrSubordinates as $objSubordinate)
					{
						$objDealerDetails->subordinates[$objSubordinate->id] = $objSubordinate;
					}
				}
				
				$this->_arrDealers[$objDealerDetails->id] = $objDealerDetails;
			}
		}
		else
		{
			throw new Exception("No dealers have been specified");
		}
		
		// Make sure there are no dealers listed in the dealer array, which will also be included as a subordinate of another dealer in the dealer array
		foreach ($this->_arrDealers as $intDealerId=>$objDealerDetails)
		{
			if ($objDealerDetails->includeSubordinates)
			{
				// The dealer will have its subordinates included
				foreach ($objDealerDetails->subordinates as $objSubordinate)
				{
					if (array_key_exists($objSubordinate->id, $this->_arrDealers))
					{
						throw new Exception("Dealer '{$objSubordinate->username}' is a subordinate of dealer '{$objDealerDetails->dealer->username}', and as such cannot be included in this report as their associated sales will be listed under 2 dealers");
					}
				}
			}
		}

		// Everything must have worked A Okay
	}
	
	// Generates the report
	// Will throw an exception on error or if a problem is encountered
	// This will return the number of records in the report
	public function buildReport()
	{
		$this->_arrReportData = array();
		$this->_arrTotals = array();
		
		$strEarliestVerificationTime	= $this->_strEarliestTime;
		$strLatestVerificationTime		= $this->_strLatestTime;
		$intSaleItemStatusIdVerified	= DO_Sales_SaleItemStatus::VERIFIED;
		
		$strQueryTemplate = "
SELECT 	si.id AS sale_item_id, 
		si.sale_id AS sale_id, 
		si.created_by AS created_by, 
		si.product_id AS product_id, 
		verified_details.changed_on AS verified_on,
		current_details.sale_item_status_id AS current_status_id,
		current_details.changed_on AS current_status_changed_on
		
FROM 	sale_item AS si 
		INNER JOIN sale_item_status_history AS verified_details 
			ON 	si.id = verified_details.sale_item_id 
				AND verified_details.sale_item_status_id = $intSaleItemStatusIdVerified
		INNER JOIN sale AS s
			ON si.sale_id = s.id
		INNER JOIN product AS p
			ON p.id = si.product_id
		INNER JOIN vendor AS v
			ON v.id = p.vendor_id
		INNER JOIN product_type AS pt
			ON pt.id = p.product_type_id
		INNER JOIN 
			(
				SELECT	sale_item_status_history.sale_item_status_id AS sale_item_status_id, /* This table defines details of the current state of the sale_item */
						sale_item_status_history.changed_on AS changed_on,
						sale_item_status_history.changed_by AS changed_by,
						sale_item_status_history.sale_item_id AS sale_item_id
				FROM	sale_item_status_history
						INNER JOIN 
							(
								SELECT sale_item_id, MAX(id) AS id /* This finds the id of the most recent sale_item_status_history record, for each sale_item_id */
								FROM sale_item_status_history
								GROUP BY sale_item_id
							) AS newest_sale_item_status_record
								ON sale_item_status_history.id = newest_sale_item_status_record.id
			) AS current_details
				ON si.id = current_details.sale_item_id

WHERE	si.created_by IN (<DealerIds>)
		AND verified_details.changed_on BETWEEN '$strEarliestVerificationTime' AND '$strLatestVerificationTime'

ORDER BY v.name ASC, pt.name ASC, p.name ASC
";

		// Convert new line chars to spaces, and remove all tabs
		$strQueryTemplate = str_replace("\n", " ", $strQueryTemplate);
		$strQueryTemplate = str_replace("\t", " ", $strQueryTemplate);
		
		$dsSales = DO_Sales_Sale::getDataSource();

		$strNowTimestamp = Data_Source_Time::currentTimestamp($dsSales);

		// Cache data that will be used repeatedly
		$arrDealerCarriers			= Carrier::listForCarrierTypeId(CARRIER_TYPE_SALES_CALL_CENTRE);
		$arrVendors					= DO_Sales_Vendor::getAll();
		foreach ($arrVendors as &$doVendor)
		{
			if ($doVender->coolingOffPeriod === NULL)
			{
				$doVender->coolingOffPeriod = 0;
			}
		}
		
		$arrProductTypesNotIndexed	= DO_Sales_ProductType::listAll();
		$arrProductTypes			= array();
		foreach ($arrProductTypesNotIndexed as $doProductType)
		{
			$arrProductTypes[$doProductType->id] = $doProductType;
		}
		$arrDealers		= array();
		$arrProducts	= array();
		
		$arrProductTotalsTemplate = array(	"TotalVerified"						=> 0,
											"TotalCompletedAndCooledOff"		=> 0,
											"TotalCompletedButNotCooledOff"		=> 0,
											"TotalCancelledAndClawedBack"		=> 0,
											"TotalCancelledButNotClawedBack"	=> 0,
											"TotalOutstanding"					=> 0
										);

		
		foreach ($this->_arrDealers as $intDealerId=>$objDealerDetails)
		{
			$this->_arrTotals[$intDealerId] = array();
			
			// This will store the Sales totals, grouped by product, for the current dealer (and possibly their subordinates)
			// The key will be the product id
			$arrProductTotals = array();
			
			// Get a reference to the dealer object, (It makes it easier to reference)
			$objDealer = &$objDealerDetails->dealer;
			
			// Work out what dealer ids to use
			$arrDealerIds = ($objDealerDetails->includeSubordinates && count($objDealerDetails->subordinates))? array_merge(array($intDealerId), array_keys($objDealerDetails->subordinates)) : array($intDealerId);
			$strDealerIds = implode(", ", $arrDealerIds);
			
			// Build the query specific to this dealer, and possibly their subordinates
			$strQuery = str_replace("<DealerIds>", $strDealerIds, $strQueryTemplate);
			
			// Execute the query
			if (PEAR::isError($objResults = $dsSales->query($strQuery)))
			{
				throw new Exception("Failed to execute SaleItemSummary Report Query for dealer {$objDealerDetails->dealer->username}, using query: $strQuery - ". $objResults->getMessage());
			}
		
			while ($arrRecord = $objResults->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				// Get Product details
				if (!array_key_exists($arrRecord['product_id'], $arrProducts))
				{
					// Cache the product
					$arrProducts[$arrRecord['product_id']] = DO_Sales_Product::getForId($arrRecord['product_id']);
				}
				
				$intVendorId = $arrProducts[$arrRecord['product_id']]->vendorId;
				
				if (!array_key_exists($arrRecord['product_id'], $this->_arrTotals[$intDealerId]))
				{
					// This is the first sale item of this product for this dealer
					// Initialise the totals
					$this->_arrTotals[$intDealerId][$arrRecord['product_id']] = $arrProductTotalsTemplate;
				}
				
				// Calculate the timestamp for the end of the clawback period
				$strVerifiedOn						= $arrRecord['verified_on'];
				$strEndOfClawbackPeriodTimestamp	= date("Y-m-d H:i:s", strtotime("+{$objDealer->clawbackPeriod} hour {$strVerifiedOn}"));
				if ($strEndOfClawbackPeriodTimestamp < '2001')
				{
					throw new exception("Could not calculate the 'end of clawback period' timestamp for sale item: {$arrRecord['sale_item_id']} - dealer.clawbackPeriod = {$objDealer->clawbackPeriod} hours, saleItem.verifiedOn = {$strVerifiedOn}, resultant end of clawback period = $strEndOfClawbackPeriodTimestamp");
				}
				
				// Calculate the timestamp for the end of the cooling off period
				$strEndOfCoolingOffTimestamp = date("Y-m-d H:i:s", strtotime("+{$arrVendors[$intVendorId]->coolingOffPeriod} hour {$strVerifiedOn}"));
				if ($strEndOfCoolingOffTimestamp < '2001')
				{
					throw new exception("Could not calculate the 'end of cooling off' timestamp for sale item: {$arrRecord['sale_item_id']} - vendor->coolingOffPeriod = {$arrVendors[$arrRecord['vendor_id']]->coolingOffPeriod} hours, saleItem.verifiedOn = {$strVerifiedOn}, resultant cooling off end timestamp = $strEndOfCoolingOffTimestamp");
				}
				
				// Update the tallies appropriately
				$this->_arrTotals[$intDealerId][$arrRecord['product_id']]['TotalVerified'] += 1;
				
				$intCurrentStatus			= $arrRecord['current_status_id'];
				$strCurrentStatusTimestamp	= $arrRecord['current_status_changed_on'];
				
				switch ($intCurrentStatus)
				{
					case DO_Sales_SaleItemStatus::CANCELLED:
						// SaleItem has been cancelled
						// Check if it happened before or after the clawback period ended
						if ($strCurrentStatusTimestamp > $strEndOfClawbackPeriodTimestamp)
						{
							// The SaleItem was cancelled after the end of the clawback period
							$this->_arrTotals[$intDealerId][$arrRecord['product_id']]['TotalCancelledButNotClawedBack'] += 1;
						}
						else
						{
							// The SaleItem was cancelled before the end of the clawback period
							$this->_arrTotals[$intDealerId][$arrRecord['product_id']]['TotalCancelledAndClawedBack'] += 1;
						}
						break;
					
					case DO_Sales_SaleItemStatus::COMPLETED:
						// SaleItem has been completed
						// Check if it has cooled off or not
						if ($strNowTimestamp > $strEndOfCoolingOffTimestamp)
						{
							// The SaleItem has cooled off
							$this->_arrTotals[$intDealerId][$arrRecord['product_id']]['TotalCompletedAndCooledOff'] += 1;
						}
						else
						{
							// The SaleItem has not cooled off yet
							$this->_arrTotals[$intDealerId][$arrRecord['product_id']]['TotalCompletedButNotCooledOff'] += 1;
						}
						break;
						
					default:
						//  The sale item is outstanding
						$this->_arrTotals[$intDealerId][$arrRecord['product_id']]['TotalOutstanding'] += 1;
						break;
				}
			}
		}
		
		// Translate the totals into the report data
		// In the report, each dealer/product combination is represented by a single row
		foreach ($this->_arrTotals as $intDealerId=>$arrDealerProductTotals)
		{
			$arrRecord = array(	"DealerId"			=> $intDealerId,
								"DealerUsername"	=> $this->_arrDealers[$intDealerId]->dealer->username,
								"DealerCarrier"		=> $arrDealerCarriers[$this->_arrDealers[$intDealerId]->dealer->carrierId]->name
								);
			foreach ($arrDealerProductTotals as $intProductId=>$arrProductTotals)
			{
				$arrRecord['VendorName']						= $arrVendors[$arrProducts[$intProductId]->vendorId]->name;
				$arrRecord['ProductTypeName']					= $arrProductTypes[$arrProducts[$intProductId]->productTypeId]->name;
				$arrRecord['ProductName']						= $arrProducts[$intProductId]->name;
				$arrRecord['TotalVerified']						= $arrProductTotals['TotalVerified'];
				$arrRecord['TotalCompletedAndCooledOff']		= $arrProductTotals['TotalCompletedAndCooledOff'];
				$arrRecord['TotalCompletedButNotCooledOff']		= $arrProductTotals['TotalCompletedButNotCooledOff'];
				$arrRecord['TotalCancelledAndClawedBack']		= $arrProductTotals['TotalCancelledAndClawedBack'];
				$arrRecord['TotalCancelledButNotClawedBack']	= $arrProductTotals['TotalCancelledButNotClawedBack'];
				$arrRecord['TotalOutstanding']					= $arrProductTotals['TotalOutstanding'];
				
				$this->_arrReportData[] = $arrRecord;
			}
		}
		
		return count($this->_arrReportData);
	}
	
	// Returns detailed report name, possibly based on the constraints of the report
	public function getDetailedReportName()
	{
		$strEarliestVerificationTime	= date("Y-m-d", strtotime($this->_strEarliestTime));
		$strLatestVerificationTime		= date("Y-m-d", strtotime($this->_strLatestTime));
		
		return "Sale Item Summary {$strEarliestVerificationTime} to {$strLatestVerificationTime}";
	}
	
}

?>
