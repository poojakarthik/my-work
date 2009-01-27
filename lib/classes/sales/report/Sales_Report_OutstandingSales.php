<?php
//----------------------------------------------------------------------------//
// Sales_Report_OutstandingSales
//----------------------------------------------------------------------------//
/**
 * Sales_Report_OutstandingSales
 *
 * Encapsulates the Sales Report, "Outstanding Sales" functionality
 *
 * Encapsulates the Sales Report, "Outstanding Sales" functionality
 *
 * @class	Sales_Report_OutstandingSales
 * @extends	Sales_Report
 */
class Sales_Report_OutstandingSales extends Sales_Report
{
	protected $_strDescription = "This report lists all outstanding sales.  An outstanding sale is one that has not been set to completed, cancelled or rejected";
	private $_arrStatuses;
	
	// This array defines the columns that will be included in the report
	protected $_arrColumns = array(
							"VendorName"				=> "Vendor",
							"SaleId"					=> "Sale Id",
							"SaleTypeName"				=> "Sale Type",
							"CreatedBy"					=> "Created By",
							"DealerCarrier"				=> "Group",
							"AccountName"				=> "Account",
							"AccountId"					=> "Account Id",
							"VerifiedOn"				=> "Verified On",
							"VerifiedBy"				=> "Verified By",
							"CurrentStatusName"			=> "Current Status",
							"CurrentStatusTimestamp"	=> "Last Actioned",
							"CurrentStatusSetBy"		=> "Last Actioner",
							"CurrentStatusDescription"	=> "Status Description"
							);
	
	protected $_reportType				= Sales_Report::REPORT_TYPE_OUTSTANDING_SALES;
	protected $_arrAllowableRenderModes	= array(self::RENDER_MODE_EXCEL, self::RENDER_MODE_CSV);
	
	// Sets the constraints for the report (and validates them)
	// TODO! define preconditions, such as the timestamps being in their correct format or NULL
	// At least one dealer must be specified
	// Will throw an exception if a problem is encountered
	public function setConstraints($objConstraints)
	{
		$arrAllowableStatuses = array(
										DO_Sales_SaleStatus::SUBMITTED,
										DO_Sales_SaleStatus::VERIFIED,
										DO_Sales_SaleStatus::AWAITING_DISPATCH,
										DO_Sales_SaleStatus::MANUAL_INTERVENTION,
										DO_Sales_SaleStatus::DISPATCHED
									);
		
		// Statuses
		$this->_arrStatuses = array();
		if (isset($objConstraints->statusIds) && is_array($objConstraints->statusIds) && count($objConstraints->statusIds))
		{
			// Statuses have been specified
			foreach ($objConstraints->statusIds as $intSaleStatusId)
			{
				if (!in_array($intSaleStatusId, $arrAllowableStatuses))
				{
					throw new Exception("Invalid Sale Status Id: $intSaleStatusId");
				}
				
				$this->_arrStatuses[] = $intSaleStatusId;
			}
		}
		else
		{
			throw new Exception("No statuses have been specified");
		}
		
		// Everything must have worked A Okay
	}
	
	// Generates the report
	// Will throw an exception on error or if a problem is encountered
	// This will return the number of records in the report
	public function buildReport()
	{
		$this->_arrReportData = array();
		
		$intSaleStatusIdVerified	= DO_Sales_SaleStatus::VERIFIED;
		
		$strSaleStatusesToConsider = implode(", ", $this->_arrStatuses);
		
		$strQuery = "
SELECT 	s.id AS sale_id,
		s.sale_type_id AS sale_type_id,
		s.created_by AS created_by,
		verified_details.changed_on AS verified_on,
		verified_details.changed_by AS verified_by,

		current_details.sale_status_id AS current_status_id,
		current_details.changed_on AS current_status_changed_on,
		current_details.changed_by AS current_status_changed_by,
		current_details.description AS current_status_description,

		sa.business_name AS business_name,
		sa.external_reference AS account_external_reference,
		sa.vendor_id AS vendor_id
		
FROM 	sale AS s
		LEFT JOIN sale_status_history AS verified_details /* The sale doesn't necesserily have to have been verified */
			ON 	s.id = verified_details.sale_id 
				AND verified_details.sale_status_id = $intSaleStatusIdVerified
		INNER JOIN sale_account AS sa
			ON sa.sale_id = s.id
		INNER JOIN 
			(
				/* This table defines details of the current state of the sale */
				SELECT	sale_status_history.sale_status_id AS sale_status_id, 
						sale_status_history.changed_on AS changed_on,
						sale_status_history.changed_by AS changed_by,
						sale_status_history.description AS description,
						sale_status_history.sale_id AS sale_id
				FROM	sale_status_history
						INNER JOIN 
							(
								/* This finds the id of the most recent sale_status_history record, for each sale_id */
								SELECT sale_id, MAX(id) AS id 
								FROM sale_status_history
								GROUP BY sale_id
							) AS newest_sale_status_record
								ON sale_status_history.id = newest_sale_status_record.id
				WHERE sale_status_history.sale_status_id IN ($strSaleStatusesToConsider) /* This constraint is far more efficient when placed here instead of in the outer most WHERE clause */
			) AS current_details
				ON s.id = current_details.sale_id

ORDER BY vendor_id ASC, sale_id ASC
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
		
		$arrSaleStatuses	= DO_Sales_SaleStatus::getAll();
		$arrSaleTypes		= DO_Sales_SaleType::getAll();
		$arrDealers			= array();
		
		// Execute the query
		if (PEAR::isError($objResults = $dsSales->query($strQuery)))
		{
			throw new Exception("Failed to execute OutstandingSales Report Query, using query: $strQuery - ". $objResults->getMessage());
		}
	
		while ($arrRecord = $objResults->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
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
			
			// Find out who created the sale
			if (!array_key_exists($arrRecord['created_by'], $arrDealers))
			{
				$arrDealers[$arrRecord['created_by']] = Dealer::getForId($arrRecord['created_by'], TRUE);
			}
			$objCreatedByDealer = $arrDealers[$arrRecord['created_by']];
			
			// Find out who verified the sale, if it has been verified
			if ($arrRecord['verified_by'] !== NULL)
			{
				// The sale has been verified
				if (!array_key_exists($arrRecord['verified_by'], $arrDealers))
				{
					$arrDealers[$arrRecord['verified_by']] = Dealer::getForId($arrRecord['verified_by'], TRUE);
				}
				$objVerificationDealer = $arrDealers[$arrRecord['verified_by']];
			}
			else
			{
				// The sale has not been verified
				$objVerificationDealer = NULL;
			}

			// Find out who performed the most recent status change on the sale_item
			if (!array_key_exists($arrRecord['current_status_changed_by'], $arrDealers))
			{
				$arrDealers[$arrRecord['current_status_changed_by']] = Dealer::getForId($arrRecord['current_status_changed_by'], TRUE);
			}
			$objLatestChangeDealer = $arrDealers[$arrRecord['current_status_changed_by']];

			// Add all the information to the record
			$arrDetails['VendorName']				= $arrVendors[$arrRecord['vendor_id']]->name;
			$arrDetails['SaleId']					= $arrRecord['sale_id'];
			$arrDetails['SaleTypeName']				= $arrSaleTypes[$arrRecord['sale_type_id']]->name;
			$arrDetails['CreatedBy']				= $objCreatedByDealer->username;
			$arrDetails['DealerCarrier']			= $arrDealerCarriers[$objCreatedByDealer->carrierId]->name;
			$arrDetails['AccountName']				= $arrRecord['business_name'];
			$arrDetails['AccountId']				= $intAccountId;
			$arrDetails['VerifiedOn']				= $arrRecord['verified_on'];
			$arrDetails['VerifiedBy']				= ($objVerificationDealer !== NULL)? $objVerificationDealer->username : NULL;

			$arrDetails['CurrentStatusName']		= $arrSaleStatuses[$arrRecord['current_status_id']]->name;
			$arrDetails['CurrentStatusTimestamp']	= $arrRecord['current_status_changed_on'];
			$arrDetails['CurrentStatusSetBy']		= $objLatestChangeDealer->username;
			$arrDetails['CurrentStatusDescription']	= $arrRecord['current_status_description'];

			$this->_arrReportData[] = $arrDetails;
		}
		return count($this->_arrReportData);
	}
	
	// Returns detailed report name, possibly based on the constraints of the report
	public function getDetailedReportName()
	{
		return "Outstanding Sales";
	}
	
}

?>
