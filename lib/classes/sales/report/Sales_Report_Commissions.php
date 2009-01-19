<?php
//----------------------------------------------------------------------------//
// Sales_Report_Commissions
//----------------------------------------------------------------------------//
/**
 * Sales_Report_Commissions
 *
 * Encapsulates the Sales Report, "Commissions" functionality
 *
 * Encapsulates the Sales Report, "Commissions" functionality
 *
 * @class	Sales_Report_Commissions
 * @extends	Sales_Report
 */
class Sales_Report_Commissions extends Sales_Report
{
	
	private $_strEarliestTime;
	private $_strLatestTime;
	private $_arrDealers;
	
	private $_arrReportData;
	
	// This array defines the columns that will be included in the report
	private $_arrColumns = array(
							"DealerUsername"			=> "Dealer",
							"DealerCarrier"				=> "Group",
							"SaleId"					=> "Sale",
							"VendorName"				=> "Vendor",
							"SubordinateUsername"		=> "Subordinate",
							"ProductName"				=> "Product",
							"ProductTypeName"			=> "Product Type",
							"ProductDetails"			=> "Details",
							"AccountName"				=> "Account",
							"AccountId"					=> "Account Id",
							"VerifiedOn"				=> "Verified On",
							"VerifiedBy"				=> "Verified By",
							"CurrentStatusName"			=> "Current Status",
							"CurrentStatusTimestamp"	=> "Last Actioned",
							"CurrentStatusChangedBy"	=> "Last Actioner",
							"IsCommissionPayable"		=> "Is Commission Payable",
							"CommissionReason"			=> "Reason",
							"SaleItemId"				=> "Sale Item Id",
							"CurrentStatusDescription"	=> "Current Status Description"
							);
	
	
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
				$this->_strEarliestTime = $objConstraints->earliestTime;
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
				$this->_strLatestTime = $objConstraints->latestTime;
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
			throw new Exception(__METHOD__ ." - No dealers have been specified");
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
		
		$strEarliestVerificationTime	= $this->_strEarliestTime;
		$strLatestVerificationTime		= $this->_strLatestTime;
		$intSaleItemStatusIdVerified	= DO_Sales_SaleItemStatus::VERIFIED;
		
		$strQueryTemplate = "
SELECT 	si.id AS sale_item_id, 
		si.sale_id AS sale_id, 
		si.created_by AS created_by, 
		si.product_id AS product_id, 
		verified_details.changed_on AS verified_on,
		verified_details.changed_by AS verified_by,
		current_details.sale_item_status_id AS current_status_id,
		current_details.changed_on AS current_status_changed_on,
		current_details.changed_by AS current_status_changed_by,
		current_details.description AS current_status_description,
		sa.business_name AS business_name,
		sa.external_reference AS account_external_reference,
		sa.vendor_id AS vendor_id
		
FROM 	sale_item AS si 
		INNER JOIN sale_item_status_history AS verified_details 
			ON 	si.id = verified_details.sale_item_id 
				AND verified_details.sale_item_status_id = $intSaleItemStatusIdVerified
		INNER JOIN sale AS s
			ON si.sale_id = s.id
		INNER JOIN sale_account AS sa
			ON sa.sale_id = s.id
		INNER JOIN 
			(
				SELECT	sale_item_status_history.sale_item_status_id AS sale_item_status_id, /* This table defines details of the current state of the sale_item */
						sale_item_status_history.changed_on AS changed_on,
						sale_item_status_history.changed_by AS changed_by,
						sale_item_status_history.description AS description,
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
ORDER BY sale_id ASC, sale_item_id ASC
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
		
		$arrSaleItemStatuses		= DO_Sales_SaleItemStatus::getAll();
		$arrProductTypesNotIndexed	= DO_Sales_ProductType::listAll();
		$arrProductTypes			= array();
		foreach ($arrProductTypesNotIndexed as $doProductType)
		{
			$arrProductTypes[$doProductType->id]					= $doProductType;
			$arrProductTypes[$doProductType->id]->moduleClassName	= Product_Type_Module::getModuleClassNameForProductType($doProductType);
		}
		$arrDealers		= array();
		$arrProducts	= array();
		
		foreach ($this->_arrDealers as $intDealerId=>$objDealerDetails)
		{
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
				throw new Exception("Failed to execute Commissions Report Query for dealer {$objDealerDetails->dealer->username}, using query: $strQuery - ". $objResults->getMessage());
			}
		
			$arrRecordSet = $objResults->fetchAll(MDB2_FETCHMODE_ASSOC);
			
			// Prepare the default values for the Record
			$arrDetails = array(	"DealerId"			=> $objDealerDetails->id,
									"DealerUsername"	=> $objDealer->username,
									"DealerCarrier"		=> $arrDealerCarriers[$objDealer->carrierId]->name
								);
						
			foreach ($arrRecordSet as $arrRecord)
			{
				// Get Product details
				if (!array_key_exists($arrRecord['product_id'], $arrProducts))
				{
					// Cache the product
					$arrProducts[$arrRecord['product_id']] = DO_Sales_Product::getForId($arrRecord['product_id']);
				}
				$doProduct			= $arrProducts[$arrRecord['product_id']];
				$strModuleClassName	= $arrProductTypes[$doProduct->productTypeId]->moduleClassName;
				$doSaleItem			= DO_Sales_SaleItem::getForId($arrRecord['sale_item_id']);
				
				// Work out the account Id
				if ($arrRecord['account_external_reference'] !== NULL && preg_match('/^Account.Id=\d+$/', $arrRecord['account_external_reference']))
				{
					// The account is now in flex, retrieve the id
					$intAccountId = intval(str_replace('Account.Id=', '', $arrRecord['account_external_reference']));
				}
				else
				{
					$intAccountId = NULL;
				}
				
				// Find out who verified the sale
				if (!array_key_exists($arrRecord['verified_by'], $arrDealers))
				{
					$arrDealers[$arrRecord['verified_by']] = Dealer::getForId($arrRecord['verified_by'], TRUE);
				}
				$objVerificationDealer = $arrDealers[$arrRecord['verified_by']];

				// Find out who performed the most recent status change on the sale_item
				if (!array_key_exists($arrRecord['current_status_changed_by'], $arrDealers))
				{
					$arrDealers[$arrRecord['current_status_changed_by']] = Dealer::getForId($arrRecord['current_status_changed_by'], TRUE);
				}
				$objLatestChangeDealer = $arrDealers[$arrRecord['current_status_changed_by']];
				
				// Find out if a subordinate made the sale
				if ($arrRecord['created_by'] != $objDealer->id)
				{
					// A subordinate must have made the sale
					if (!array_key_exists($arrRecord['created_by'], $objDealerDetails->subordinates))
					{
						throw new Exception("Sale item {$arrRecord['sale_item_id']} was created by {$arrRecord['created_by']} who is not a subordinate of dealer {$objDealer->id}");
					}
					$objSubordinate = &$objDealerDetails->subordinates[$arrRecord['created_by']];
				}
				else
				{
					// the dealer must have made the sale, not a subordinate of the dealer
					$objSubordinate = NULL;
				}

				// Calculate the timestamp for the end of the clawback period
				$strVerifiedOn						= $arrRecord['verified_on'];
				$strEndOfClawbackPeriodTimestamp	= date("Y-m-d H:i:s", strtotime("+{$objDealer->clawbackPeriod} hour {$strVerifiedOn}"));
				if ($strEndOfClawbackPeriodTimestamp < '2001')
				{
					throw new exception("Could not calculate the 'end of clawback period' timestamp for sale item: {$arrRecord['sale_item_id']} - dealer.clawbackPeriod = {$objDealer->clawbackPeriod} hours, saleItem.verifiedOn = {$strVerifiedOn}, resultant end of clawback period = $strEndOfClawbackPeriodTimestamp");
				}
				
				// Calculate the timestamp for the end of the cooling off period
				$strEndOfCoolingOffTimestamp = date("Y-m-d H:i:s", strtotime("+{$arrVendors[$arrRecord['vendor_id']]->coolingOffPeriod} hour {$strVerifiedOn}"));
				if ($strEndOfCoolingOffTimestamp < '2001')
				{
					throw new exception("Could not calculate the 'end of cooling off' timestamp for sale item: {$arrRecord['sale_item_id']} - vendor->coolingOffPeriod = {$arrVendors[$arrRecord['vendor_id']]->coolingOffPeriod} hours, saleItem.verifiedOn = {$strVerifiedOn}, resultant cooling off end timestamp = $strEndOfCoolingOffTimestamp");
				}
				
				// Work out if commission is payable or not
				$intCurrentStatus			= $arrRecord['current_status_id'];
				$strCurrentStatusTimestamp	= $arrRecord['current_status_changed_on'];
				if ($intCurrentStatus == DO_Sales_SaleItemStatus::CANCELLED)
				{
					// The sale item has been cancelled
					// Check if this happend before or after the Clawback period ends
					if ($strCurrentStatusTimestamp < $strEndOfClawbackPeriodTimestamp)
					{
						// The sale item was cancelled within the clawback period
						$strCommissionPayable	= "No";
						$strCommissionReason	= "Cancelled within clawback period";
					}
					else
					{
						// The sale item was cancelled after the clawback period ended
						$strCommissionPayable	= "Yes";
						$strCommissionReason	= "Cancelled outside of clawback period";
					}
				}
				else
				{
					// The sale item has not been cancelled
					if ($strEndOfCoolingOffTimestamp < $strNowTimestamp)
					{
						// The cooling off period has transpired without the sale item being cancelled (this veto's the dealer's clawback period)
						$strCommissionPayable	= "Yes";
						$strCommissionReason	= "Cooling off period has transpired";
					}
					else
					{
						// The cooling off period has not finished yet
						if ($strEndOfClawbackPeriodTimestamp < $strNowTimestamp)
						{
							// The clawback period has ended without the sale item being cancelled
							$strCommissionPayable	= "Yes";
							$strCommissionReason	= "Clawback period has transpired";
						}
						else
						{
							// The clawback period has not ended yet, and the sale item hasn't yet been cancelled, but it could before the clawback period ends
							$strCommissionPayable	= "Unknown";
							$strCommissionReason	= "Clawback period has not yet transpired";
						}
					}
				}
				
				// Add all the information to the record
				$arrDetails['SaleId']					= $arrRecord['sale_id'];
				$arrDetails['SubordinateId']			= ($objSubordinate !== NULL)? $objSubordinate->id : NULL;
				$arrDetails['SubordinateUsername']		= ($objSubordinate !== NULL)? $objSubordinate->username : NULL;
				$arrDetails['SaleItemId']				= $arrRecord['sale_item_id'];
				$arrDetails['ProductId']				= $doProduct->id;
				$arrDetails['ProductName']				= $doProduct->name;
				$arrDetails['ProductTypeName']			= $arrProductTypes[$doProduct->productTypeId]->name;
				$arrDetails['ProductDetails']			= call_user_func(array($strModuleClassName, "getSaleItemDescription"), $doSaleItem, FALSE, FALSE);
				$arrDetails['AccountName']				= $arrRecord['business_name'];
				$arrDetails['AccountId']				= $intAccountId;
				$arrDetails['VerifiedOn']				= $strVerifiedOn;
				$arrDetails['VerifiedBy']				= $objVerificationDealer->username;
				$arrDetails['CurrentStatusName']		= $arrSaleItemStatuses[$intCurrentStatus]->name;
				$arrDetails['CurrentStatusTimestamp']	= $strCurrentStatusTimestamp;
				$arrDetails['CurrentStatusChangedBy']	= $objLatestChangeDealer->username;
				$arrDetails['CurrentStatusDescription']	= $arrRecord['current_status_description'];
				$arrDetails['IsCommissionPayable']		= $strCommissionPayable;
				$arrDetails['CommissionReason']			= $strCommissionReason;
				$arrDetails['VendorName']				= $arrVendors[$arrRecord['vendor_id']]->name;

				$this->_arrReportData[] = $arrDetails;
			}
		}
		return count($this->_arrReportData);
	}
	
	
	// Retrieves the report, in the RenderMode specified, assuming the report can be rendered in this mode
	public function getReport($strRenderMode)
	{
		switch ($strRenderMode)
		{
			case Sales_Report::RENDER_MODE_EXCEL:
				$strReport = $this->_translateToExcel();
				break;
				
			default:
				throw new Exception("Invalid Render Mode, '$strRenderMode', for Sales Commission Report");
				break;
		}
		
		return $strReport;
	}

	private function _translateToExcel()
	{
		// Build the header row
		$strHeaderRow = "";
		foreach ($this->_arrColumns as $strColumnName)
		{
			$strHeaderRow .= "\t\t\t\t\t<th>$strColumnName</th>\n";
		}
		
		// Build the rows
		$strRows = "";
		foreach ($this->_arrReportData as $arrDetails)
		{
			$strRow = "";
			foreach ($this->_arrColumns as $strPropName=>$strColumnName)
			{
				$strRow .= "\t\t\t\t\t<td>{$arrDetails[$strPropName]}</td>\n";
			}
			
			$strRows .= "\t\t\t\t<tr>\n$strRow\t\t\t\t</tr>\n";
		}
		
		$arrRenderMode	= Sales_Report::getRenderModeDetails(Sales_Report::RENDER_MODE_EXCEL);
		$strMimeType	= $arrRenderMode['MimeType'];

		// Put it all together
		$strReport = "<html>
	<head>
		<meta http-equiv=\"content-type\" content=\"$strMimeType\">
	</head>
	<body>
		<table>
			<thead>
				<tr>
$strHeaderRow
				</tr>
			</thead>
			<tbody>
$strRows
			</tbody>
		</table>
	</body>
</html>";

		return $strReport;
	}
	
	// Returns an array defining the allowable RenderModes for the specific report type
	public function getAllowableRenderModes()
	{
		return array(self::RENDER_MODE_EXCEL);
	}
	
	// Returns detailed report name, possibly based on the constraints of the report
	public function getDetailedReportName()
	{
		$strEarliestVerificationTime	= date("Y-m-d", strtotime($this->_strEarliestTime));
		$strLatestVerificationTime		= date("Y-m-d", strtotime($this->_strLatestTime));
		
		return "Sales Commissions {$strEarliestVerificationTime} to {$strLatestVerificationTime}";
	}
	
}

?>
