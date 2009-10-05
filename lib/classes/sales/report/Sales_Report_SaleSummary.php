<?php
//----------------------------------------------------------------------------//
// Sales_Report_SaleSummary
//----------------------------------------------------------------------------//
/**
 * Sales_Report_SaleSummary
 *
 * Encapsulates the Sales Report, "Sales Summary" functionality - RR010
 *
 * Encapsulates the Sales Report, "Sales Summary" functionality - RR010
 *
 * @class	Sales_Report_SaleSummary
 * @extends	Sales_Report
 */
class Sales_Report_SaleSummary extends Sales_Report
{
	protected $_strDescription = "This report gives a summary of the sales submitted by the dealers considered during the timeframe in question";

	private $_strEarliestTime;
	private $_strLatestTime;
	private $_arrDealers;
	
	// This will store the tallies for each dealer / Vendor / sale state combination (theoretically a dealer can make sales for multiple vendors)
	// $_arrTotals[dealerId][vendorId][TotalVerified] = 5 etc
	private $_arrTotals;
	
	// This array defines the columns that will be included in the report
	protected $_arrColumns = array(
							"DealerUsername"						=> "Dealer",
							"DealerCarrier"							=> "Group",
							"VendorName"							=> "Vendor",
							"TotalSubmitted"						=> "Total Submitted",
							"TotalVerified"							=> "Total Verified",
							"TotalCurrentlyCompleted"				=> "Total Sales with a Current Status of Completed",
							"TotalCurrentlyCancelled"				=> "Total Sales with a Current Status of Cancelled",
							"TotalCurrentlyDispatched"				=> "Total Sales with a Current Status of Dispatched",
							"TotalCurrentlyManualIntervention"		=> "Total Sales with a Current Status of Manual Intervention",
							"TotalCurrentlyAwaitingDispatch"		=> "Total Sales with a Current Status of Awaiting Dispatch",
							"TotalCurrentlyRejected"				=> "Total Sales with a Current Status of Rejected",
							"TotalCurrentlySubmitted"				=> "Total Sales with a Current Status of Submitted",
							"TotalCurrentlyReSubmitted"				=> "Total Sales with a Current Status of Re-Submitted",
							"TotalCurrentlyVerified"				=> "Total Sales with a Current Status of Verified"
							);
	
	protected $_reportType				= Sales_Report::REPORT_TYPE_SALE_SUMMARY;
	protected $_arrAllowableRenderModes	= array(self::RENDER_MODE_EXCEL, self::RENDER_MODE_CSV);
	
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
			throw new Exception("Earliest Submission Time has not been specified");
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
			throw new Exception("Latest Submission Time has not been specified");
		}
		
		if ($this->_strLatestTime < $this->_strEarliestTime)
		{
			throw new Exception("Earliest Submission Time ({$this->_strEarliestTime}) is greater than the Latest Submission Time ({$this->_strLatestTime})");
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
		
		$strEarliestSubmissionTime	= $this->_strEarliestTime;
		$strLatestSubmissionTime	= $this->_strLatestTime;
		$intSaleStatusIdVerified	= DO_Sales_SaleStatus::VERIFIED;
		
		$strQueryTemplate = "
SELECT	sale.id										AS sale_id, 
		sale.created_by								AS created_by, 
		sale.created_on								AS created_on, 
		sale_account.vendor_id						AS vendor_id, 
		current_status_details.sale_status_id		AS current_sale_status_id, 
		current_status_details.changed_on			AS current_sale_status_changed_on, 
		verification_details.changed_on				AS verified_on

FROM sale
	INNER JOIN sale_account
		ON sale.id = sale_account.sale_id
	INNER JOIN
		(
			/* This table defines details regarding the current state of the sale */
			SELECT	sale_status_history.sale_id			AS sale_id,
					sale_status_history.changed_on		AS changed_on,
					sale_status_history.changed_by		AS changed_by,
					sale_status_history.sale_status_id	AS sale_status_id
			FROM sale_status_history
				INNER JOIN
					(
						/* Find the id of the most recent record in the sale_status_history table relating to each sale (there is always at least one record in this table for each sale) */
						SELECT sale_id, MAX(id) AS id 
						FROM sale_status_history 
						GROUP BY sale_id

					) AS most_recent_status_change
						ON sale_status_history.id = most_recent_status_change.id

		) AS current_status_details
			ON sale.id = current_status_details.sale_id
	LEFT JOIN sale_status_history AS verification_details
		ON sale.id = verification_details.sale_id AND verification_details.sale_status_id = {$intSaleStatusIdVerified}

WHERE	sale.created_by IN (<DealerIds>)
		AND sale.created_on BETWEEN '{$strEarliestSubmissionTime}' AND '{$strLatestSubmissionTime}'
ORDER BY sale_account.vendor_id ASC
";

		// Convert new line chars to spaces, and remove all tabs
		$strQueryTemplate = str_replace("\n", " ", $strQueryTemplate);
		$strQueryTemplate = str_replace("\t", " ", $strQueryTemplate);
		
		$dsSales = DO_Sales_Sale::getDataSource();

		$strNowTimestamp = Data_Source_Time::currentTimestamp($dsSales);

		// Cache data that will be used repeatedly
		$arrDealerCarriers	= Carrier::listForCarrierTypeId(CARRIER_TYPE_SALES_CALL_CENTRE);
		$arrVendors			= DO_Sales_Vendor::getAll();
		
		$arrDealers = array();
		
		$arrTotalsTemplate = array(	"TotalSubmitted"						=> 0,
									"TotalVerified"							=> 0,
									"TotalCurrentlyCompleted"				=> 0,
									"TotalCurrentlyCancelled"				=> 0,
									"TotalCurrentlyDispatched"				=> 0,
									"TotalCurrentlyManualIntervention"		=> 0,
									"TotalCurrentlyAwaitingDispatch"		=> 0,
									"TotalCurrentlyRejected"				=> 0,
									"TotalCurrentlySubmitted"				=> 0,
									"TotalCurrentlyReSubmitted"				=> 0,
									"TotalCurrentlyVerified"				=> 0
									);

		
		foreach ($this->_arrDealers as $intDealerId=>$objDealerDetails)
		{
			$this->_arrTotals[$intDealerId] = array();
			
			// This will store the totals, grouped by vendor, for the current dealer (and possibly their subordinates)
			// The key will be the vendor id
			$arrVendorTotals = array();
			
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
				throw new Exception("Failed to execute SaleSummary Report Query for dealer {$objDealerDetails->dealer->username}, using query: $strQuery - ". $objResults->getMessage());
			}
		
			while ($arrRecord = $objResults->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				$intVendorId = intval($arrRecord['vendor_id']);
				
				if (!array_key_exists($intVendorId, $this->_arrTotals[$intDealerId]))
				{
					// This is the first sale for this dealer/vendor combination
					// Initialise the totals
					$this->_arrTotals[$intDealerId][$intVendorId] = $arrTotalsTemplate;
				}
				

				
				
				// Update the tallies appropriately
				$this->_arrTotals[$intDealerId][$intVendorId]['TotalSubmitted'] += 1;
				
				if ($arrRecord['verified_on'] !== null)
				{
					// The sale has been verified
					$this->_arrTotals[$intDealerId][$intVendorId]['TotalVerified'] += 1;
				}
				
				$intCurrentStatusId			= intval($arrRecord['current_sale_status_id']);
				$strCurrentStatusTimestamp	= $arrRecord['current_sale_status_changed_on'];
				$strCreatedOnTimestamp		= $arrRecord['created_on'];
				
				
				
				switch ($intCurrentStatusId)
				{
					case DO_Sales_SaleStatus::CANCELLED:
						// Sale has been cancelled
						$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyCancelled'] += 1;
						break;
					
					case DO_Sales_SaleStatus::COMPLETED:
						// Sale has been completed
						$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyCompleted'] += 1;
						break;
						
					case DO_Sales_SaleStatus::DISPATCHED:
						$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyDispatched'] += 1;
						break;
					
					case DO_Sales_SaleStatus::MANUAL_INTERVENTION:
						$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyManualIntervention'] += 1;
						break;
					
					case DO_Sales_SaleStatus::AWAITING_DISPATCH:
						$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyAwaitingDispatch'] += 1;
						break;
					
					case DO_Sales_SaleStatus::VERIFIED:
						$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyVerified'] += 1;
						break;
						
					case DO_Sales_SaleStatus::REJECTED:
						$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyRejected'] += 1;
						break;

					case DO_Sales_SaleStatus::SUBMITTED:
						if ($strCurrentStatusTimestamp == $strCreatedOnTimestamp)
						{
							$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlySubmitted'] += 1;
						}
						else
						{
							// Must relate to a resubmission of the sale because the timestamp doesn't match the sale's created_on timestamp
							$this->_arrTotals[$intDealerId][$intVendorId]['TotalCurrentlyReSubmitted'] += 1;
						}
						break;
						
					default:
						// Whatever the status is, it shouldn't be set to it
						$doSaleStatus = DO_Sales_SaleStatus::getForId($intCurrentStatusId);
						throw new Exception("Sale (id {$arrRecord['sale_id']}) has an unaccounted for status of: {$doSaleItemStatus->name} ($intCurrentStatusId)");
						break;
				}
			}
		}
		
		// Translate the totals into the report data
		// In the report, each dealer/vendor combination is represented by a single row
		foreach ($this->_arrTotals as $intDealerId=>$arrDealerVendorTotals)
		{
			$arrRecord = array(	"DealerId"			=> $intDealerId,
								"DealerUsername"	=> $this->_arrDealers[$intDealerId]->dealer->username,
								"DealerCarrier"		=> $arrDealerCarriers[$this->_arrDealers[$intDealerId]->dealer->carrierId]->name
								);
			foreach ($arrDealerVendorTotals as $intVendorId=>$arrVendorTotals)
			{
				$arrRecord['VendorName']						= $arrVendors[$intVendorId]->name;
				$arrRecord['TotalSubmitted']					= $arrVendorTotals['TotalSubmitted'];
				$arrRecord['TotalVerified']						= $arrVendorTotals['TotalVerified'];
				$arrRecord['TotalCurrentlyCompleted']			= $arrVendorTotals['TotalCurrentlyCompleted'];
				$arrRecord['TotalCurrentlyCancelled']			= $arrVendorTotals['TotalCurrentlyCancelled'];
				$arrRecord['TotalCurrentlyDispatched']			= $arrVendorTotals['TotalCurrentlyDispatched'];
				$arrRecord['TotalCurrentlyManualIntervention']	= $arrVendorTotals['TotalCurrentlyManualIntervention'];
				$arrRecord['TotalCurrentlyAwaitingDispatch']	= $arrVendorTotals['TotalCurrentlyAwaitingDispatch'];
				$arrRecord['TotalCurrentlyRejected']			= $arrVendorTotals['TotalCurrentlyRejected'];
				$arrRecord['TotalCurrentlySubmitted']			= $arrVendorTotals['TotalCurrentlySubmitted'];
				$arrRecord['TotalCurrentlyReSubmitted']			= $arrVendorTotals['TotalCurrentlyReSubmitted'];
				$arrRecord['TotalCurrentlyVerified']			= $arrVendorTotals['TotalCurrentlyVerified'];
				
				$this->_arrReportData[] = $arrRecord;
			}
		}
		
		return count($this->_arrReportData);
	}
	
	// Returns detailed report name, possibly based on the constraints of the report
	public function getDetailedReportName()
	{
		$strEarliestSubmissionTime	= date("Y-m-d", strtotime($this->_strEarliestTime));
		$strLatestSubmissionTime	= date("Y-m-d", strtotime($this->_strLatestTime));
		
		return "Sale Summary {$strEarliestSubmissionTime} to {$strLatestSubmissionTime}";
	}
}

?>
