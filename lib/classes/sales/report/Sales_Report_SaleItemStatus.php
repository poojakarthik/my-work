<?php
//----------------------------------------------------------------------------//
// Sales_Report_SaleItemStatus
//----------------------------------------------------------------------------//
/**
 * Sales_Report_SaleItemStatus
 *
 * Encapsulates the Sales Report, "Sale Item Status" functionality
 *
 * Encapsulates the Sales Report, "Sale Item Status" functionality
 *
 * @class	Sales_Report_SaleItemStatus
 * @extends	Sales_Report
 */
class Sales_Report_SaleItemStatus extends Sales_Report
{
	
	private $_strEarliestTime;
	private $_strLatestTime;
	private $_arrStatuses;
	
	// This array defines the columns that will be included in the report
	protected $_arrColumns = array(
							"SaleTypeName"				=> "Sale Type",
							"SaleId"					=> "Sale",
							"AccountName"				=> "Account",
							"AccountId"					=> "Account Id",
							"ProductName"				=> "Product",
							"ProductTypeName"			=> "Product Type",
							"ProductDetails"			=> "Details",
							"VerifiedOn"				=> "Verified On",
							"VerifiedBy"				=> "Verified By",
							"EOTStatusName"				=> "EOT Status",		// End Of Timeframe Status Name
							"EOTStatusTimestamp"		=> "Actioned",
							"EOTStatusSetBy"			=> "Actioner",
							"EOTStatusDescription"		=> "Description",
							"CurrentStatusName"			=> "Current Status",
							"CurrentStatusTimestamp"	=> "Actioned",
							"CurrentStatusSetBy"		=> "Actioner",
							"CurrentStatusDescription"	=> "Description"
							);
	
	protected $_reportType = Sales_Report::REPORT_TYPE_SALE_ITEM_STATUS;
	
	
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
			throw new Exception("Earliest Time of Change has not been specified");
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
			throw new Exception("Latest Time of Change has not been specified");
		}
		
		if ($this->_strLatestTime < $this->_strEarliestTime)
		{
			throw new Exception("Earliest Time of Change ({$this->_strEarliestTime}) is greater than the Latest Time of Change ({$this->_strLatestTime})");
		}
		
		// Statuses
		$this->_arrStatuses = array();
		if (isset($objConstraints->statusIds) && is_array($objConstraints->statusIds) && count($objConstraints->statusIds))
		{
			$arrAllStatuses = DO_Sales_SaleItemStatus::getAll();
			
			// Statuses have been specified
			foreach ($objConstraints->statusIds as $intSaleItemStatusId)
			{
				if (!array_key_exists($intSaleItemStatusId, $arrAllStatuses))
				{
					throw new Exception("Invalid Sale Item Status Id: $intSaleItemStatusId");
				}
				
				$this->_arrStatuses[] = $intSaleItemStatusId;
			}
		}
		else
		{
			throw new Exception(__METHOD__ ." - No statuses have been specified");
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
		
		$strSaleItemStatusesToConsider = implode(", ", $this->_arrStatuses);
		
		$strQuery = "
SELECT 	si.id AS sale_item_id, 
		si.sale_id AS sale_id, 
		s.sale_type_id AS sale_type_id,
		si.product_id AS product_id, 
		verified_details.changed_on AS verified_on,
		verified_details.changed_by AS verified_by,

		eot_details.sale_item_status_id AS eot_status_id, /* eot = end of timeframe */
		eot_details.changed_on AS eot_status_changed_on,
		eot_details.changed_by AS eot_status_changed_by,
		eot_details.description AS eot_status_description,

		current_details.sale_item_status_id AS current_status_id,
		current_details.changed_on AS current_status_changed_on,
		current_details.changed_by AS current_status_changed_by,
		current_details.description AS current_status_description,

		sa.business_name AS business_name,
		sa.external_reference AS account_external_reference
		
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

		INNER JOIN 
			(	/* This finds details of a sale_item status change, but only if it is to one of the statuses we are interested in, and only if it was the last status changed to, within the timeframe specified */
				SELECT	sale_item_status_history.sale_item_status_id AS sale_item_status_id,
						sale_item_status_history.changed_on AS changed_on,
						sale_item_status_history.changed_by AS changed_by,
						sale_item_status_history.description AS description,
						sale_item_status_history.sale_item_id AS sale_item_id
				FROM	sale_item_status_history
						INNER JOIN 
							(	/* This finds the id of the most recent sale_item_status_history record created between '$strEarliestVerificationTime' AND '$strLatestVerificationTime', for each sale_item_id */
								SELECT sale_item_id, MAX(id) AS id 
								FROM sale_item_status_history
								WHERE changed_on BETWEEN '$strEarliestVerificationTime' AND '$strLatestVerificationTime'
								GROUP BY sale_item_id
							) AS eot_sale_item_status_record
								ON sale_item_status_history.id = eot_sale_item_status_record.id
				WHERE sale_item_status_history.sale_item_status_id IN ($strSaleItemStatusesToConsider)
			) AS eot_details
				ON si.id = eot_details.sale_item_id

WHERE verified_details.changed_on <= '$strLatestVerificationTime'
ORDER BY sale_type_id ASC, sale_id ASC, sale_item_id ASC, business_name ASC
";

		// Convert new line chars to spaces, and remove all tabs
		$strQuery = str_replace("\n", " ", $strQuery);
		$strQuery = str_replace("\t", " ", $strQuery);
		
		$dsSales = DO_Sales_Sale::getDataSource();

		// Cache data that will be used repeatedly
		$arrDealerCarriers	= Carrier::listForCarrierTypeId(CARRIER_TYPE_SALES_CALL_CENTRE);
		$arrVendors			= DO_Sales_Vendor::getAll();
		foreach ($arrVendors as &$doVendor)
		{
			if ($doVender->coolingOffPeriod === NULL)
			{
				$doVender->coolingOffPeriod = 0;
			}
		}
		
		$arrSaleItemStatuses		= DO_Sales_SaleItemStatus::getAll();
		$arrSaleTypes				= DO_Sales_SaleType::getAll();
		$arrProductTypesNotIndexed	= DO_Sales_ProductType::listAll();
		$arrProductTypes			= array();
		foreach ($arrProductTypesNotIndexed as $doProductType)
		{
			$arrProductTypes[$doProductType->id]					= $doProductType;
			$arrProductTypes[$doProductType->id]->moduleClassName	= Product_Type_Module::getModuleClassNameForProductType($doProductType);
		}
		
		$arrDealers		= array();
		$arrProducts	= array();
		
		// Execute the query
		if (PEAR::isError($objResults = $dsSales->query($strQuery)))
		{
			throw new Exception("Failed to execute Sale Report Query for dealer {$objDealerDetails->dealer->username}, using query: $strQuery - ". $objResults->getMessage());
		}
	
		while ($arrRecord = $objResults->fetchRow(MDB2_FETCHMODE_ASSOC))
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

			// Find out who performed the End Of Timeframe status change on the sale_item
			if (!array_key_exists($arrRecord['eot_status_changed_by'], $arrDealers))
			{
				$arrDealers[$arrRecord['eot_status_changed_by']] = Dealer::getForId($arrRecord['eot_status_changed_by'], TRUE);
			}
			$objEOTChangeDealer = $arrDealers[$arrRecord['eot_status_changed_by']];
			
			// Add all the information to the record
			$arrDetails['SaleTypeName']					= $arrSaleTypes[$arrRecord['sale_type_id']]->name;
			$arrDetails['SaleId']					= $arrRecord['sale_id'];
			$arrDetails['AccountName']				= $arrRecord['business_name'];
			$arrDetails['AccountId']				= $intAccountId;
			$arrDetails['ProductName']				= $doProduct->name;
			$arrDetails['ProductTypeName']			= $arrProductTypes[$doProduct->productTypeId]->name;
			$arrDetails['ProductDetails']			= call_user_func(array($strModuleClassName, "getSaleItemDescription"), $doSaleItem, FALSE, FALSE);
			$arrDetails['VerifiedOn']				= $arrRecord['verified_on'];
			$arrDetails['VerifiedBy']				= $objVerificationDealer->username;

			$arrDetails['EOTStatusName']			= $arrSaleItemStatuses[$arrRecord['eot_status_id']]->name;
			$arrDetails['EOTStatusTimestamp']		= $arrRecord['eot_status_changed_on'];
			$arrDetails['EOTStatusSetBy']			= $objEOTChangeDealer->username;
			$arrDetails['EOTStatusDescription']		= $arrRecord['eot_status_description'];

			$arrDetails['CurrentStatusName']		= $arrSaleItemStatuses[$arrRecord['current_status_id']]->name;
			$arrDetails['CurrentStatusTimestamp']	= $arrRecord['current_status_changed_on'];
			$arrDetails['CurrentStatusSetBy']		= $objLatestChangeDealer->username;
			$arrDetails['CurrentStatusDescription']	= $arrRecord['current_status_description'];

			$this->_arrReportData[] = $arrDetails;
		}
		return count($this->_arrReportData);
	}
	
	// Returns an array defining the allowable RenderModes for the specific report type
	public static function getAllowableRenderModes()
	{
		return array(self::RENDER_MODE_EXCEL);
	}
	
	// Returns detailed report name, possibly based on the constraints of the report
	public function getDetailedReportName()
	{
		$strEarliestVerificationTime	= date("Y-m-d", strtotime($this->_strEarliestTime));
		$strLatestVerificationTime		= date("Y-m-d", strtotime($this->_strLatestTime));
		
		return "SaleItem Status {$strEarliestVerificationTime} to {$strLatestVerificationTime}";
	}
	
}

?>
