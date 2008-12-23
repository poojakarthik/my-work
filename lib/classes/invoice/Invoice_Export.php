<?php
//----------------------------------------------------------------------------//
// Invoice_Export
//----------------------------------------------------------------------------//
/**
 * Invoice_Export
 *
 * Handles preparation for Invoice Export
 * 
 * Handles preparation for Invoice Export
 *
 * @class	Invoice_Export
 */
class Invoice_Export
{ 	
  	//------------------------------------------------------------------------//
	// getOldInvoice()
	//------------------------------------------------------------------------//
	/**
	 * getOldInvoice()
	 *
	 * Returns the Invoice Data from the Xth last Invoice
	 *
	 * Returns the Invoice Data from the Xth last Invoice
	 * 
	 * @param	array	$arrInvoice					The current Invoice to work from
	 * @param	integer	$intPeriodsAgo				The number of billing periods ago to check (eg. 1 will return the last Invoice)
	 *
	 * @return	array								Old Invoice Data
	 *
	 * @method
	 */
	public static function getOldInvoice($arrInvoice, $intPeriodsAgo)
	{
		if ((int)$intPeriodsAgo < 1)
		{
			// Either not an integer, or an invalid number of periods ago
			throw new Exception("\$intPeriodsAgo with value '{$intPeriodsAgo}' is less than the minimum of 1");
		}
		
		$intPeriodsAgo--;
		$selOldInvoice	= new StatementSelect("Invoice", "*", "Account = <Account> AND CreatedOn < <CreatedOn>", "CreatedOn DESC", "$intPeriodsAgo, 1");
		if ($selOldInvoice->Execute($arrInvoice) === FALSE)
		{
			throw new Exception($selOldInvoice->Error());
		}
		
		// Return data or empty array
		if ($arrOldInvoice = $selOldInvoice->Fetch())
		{
			return $arrOldInvoice;
		}
		else
		{
			return Array();
		}
	}
 	
  	//------------------------------------------------------------------------//
	// getCustomerData()
	//------------------------------------------------------------------------//
	/**
	 * getCustomerData()
	 *
	 * Returns the Account's Customer Data
	 *
	 * Returns the Account's Customer Data
	 * 
	 * @param	array	$arrInvoice					Invoice Details
	 *
	 * @return	array								Customer Data Array
	 *
	 * @method
	 */
	public static function getCustomerData($arrInvoice)
	{		
		// Retrieve the Customer Data
		$selCustomerData	= self::_preparedStatement('selCustomerData');
		if ($selCustomerData->Execute($arrInvoice) === FALSE)
		{
			throw new Exception($selCustomerData->Error());
		}
		
		// Return data or empty array
		if ($arrCustomer = $selCustomerData->Fetch())
		{
			return $arrCustomer;
		}
		else
		{
			return Array();
		}
	}
 	
  	//------------------------------------------------------------------------//
	// getServices()
	//------------------------------------------------------------------------//
	/**
	 * getServices()
	 *
	 * Gets a list of Services that have been Invoiced this run
	 *
	 * Gets a list of Services that have been Invoiced this run
	 * 
	 * @param	array	$arrInvoice						Invoice Details
	 * 
	 * @return	array									Account Summary Array
	 *
	 * @method
	 */
	public static function getServices($arrInvoice)
	{
		// Get (Primary) FNNs for this Account
		$selAccountFNNs	= self::_preparedStatement('selAccountFNNs');
		if ($selAccountFNNs->Execute($arrInvoice) === FALSE)
		{
			throw new Exception($selAccountFNNs->Error());
		}
		$arrAccountFNNs	= $selAccountFNNs->FetchAll();
		
		// Get List of Service IDs for each FNN
		$arrServices			= Array();
		$selServiceDetails		= self::_preparedStatement('selServiceDetails');
		$selServiceInstances	= self::_preparedStatement('selServiceInstances');
		foreach ($arrAccountFNNs as $intKey=>$arrService)
		{
			//Cli_App_Billing::debug($arrService);
			
			// Get details from the Current Service
			$arrService['invoice_run_id']	= $arrInvoice['invoice_run_id'];
			if ($selServiceDetails->Execute($arrService) === FALSE)
			{
				throw new Exception($selServiceDetails->Error());
			}
			else
			{
				$arrServiceDetails	= $selServiceDetails->Fetch();
				$arrService			= array_merge($arrService, $arrServiceDetails);
				
				//Cli_App_Billing::debug($arrService);
				
				// Is this the Primary FNN?
				$arrService['Primary']		= ($arrService['FNN'] >= $arrService['RangeStart'] && $arrService['FNN'] <= $arrService['RangeEnd']) ? TRUE : FALSE;
				
				// Get all Service Ids that are associated with this FNN
				$arrWhere = Array();
				if ($selServiceInstances->Execute($arrService) === FALSE)
				{
					throw new Exception($selServiceInstances->Error());
				}
				else
				{
					$arrService['Id']	= Array();
					while ($arrId = $selServiceInstances->Fetch())
					{
						$arrService['Id'][] = $arrId['service_id'];
					}
				}
				
				$arrServices[] = $arrService;
				//Cli_App_Billing::debug($arrService);
			}
		}
		
		foreach ($arrServices as &$arrService)
		{
			$arrCategories	= Array();
			$fltRatedTotal	= 0.0;
			
			// Get Record Types
			$arrWhere	= Array();
			$arrWhere['invoice_run_id']	= $arrInvoice['invoice_run_id'];
			$arrWhere['RangeStart']		= $arrService['RangeStart'];
			$arrWhere['RangeEnd']		= $arrService['RangeEnd'];
			$arrRecordTypes				= self::_preparedStatementMultiService('selRecordTypes', $arrService, $arrWhere);
			foreach ($arrRecordTypes as $arrRecordType)
			{
				// Get Call Itemisation
				$arrWhere['RecordGroup']		= $arrRecordType['GroupId'];
				$arrRecordType['Itemisation']	= self::_preparedStatementMultiService('selItemisedCalls', $arrService, $arrWhere);
				
				// Add Record Type to Service Array
				$arrCategories[$arrRecordType['RecordGroup']]	= $arrRecordType;
				
				// Calculate Rated Total
				$fltCDRTotal	= 0.0;
				foreach ($arrRecordType['Itemisation'] as $intIndex=>$arrCDR)
				{
					$fltRatedTotal	+= $arrCDR['Charge'];
					$fltCDRTotal	+= $arrCDR['Charge'];
					
					// Should we hide this CDR?
					if ($arrCDR['allow_cdr_hiding'] && $arrCDR['Charge'] === 0.0 && $arrService['allow_cdr_hiding'])
					{
						// Yes -- hide it/remove it from itemisation
						unset($arrRecordType['Itemisation'][$intIndex]);
					}
				}
				//Cli_App_Billing::debug("CDR Total for {$arrService['FNN']}: \${$fltCDRTotal}");
			}
			
			// Handle ServiceTotals for non-Indials
			if (!$arrService['Indial100'])
			{
				// Get the ServiceTotal
				$arrServiceTotals			= self::_preparedStatementMultiService('selServiceTotal', $arrService, $arrInvoice);
				$arrService['ServiceTotal']	= 0.0;
				foreach ($arrServiceTotals as $arrServiceTotal)
				{
					$arrService['ServiceTotal']	+= $arrServiceTotal['TotalCharge'];
				}
			}
			
			// Only if this is a non-Indial or is the Primary FNN
			if ($arrService['Primary'])
			{
				// Get Adjustments
				$arrItemised	= self::_preparedStatementMultiService('selItemisedCharges', $arrService, $arrInvoice);
				if (count($arrItemised))
				{
					$fltAdjustmentsTotal	= 0.0;
					
					// Convert each Adjustment to a CDR
					foreach ($arrItemised as $arrCharge)
					{
						$arrCDR	= Array();
						$arrCDR['Charge']		= ($arrCharge['Nature'] == NATURE_CR) ? 0 - $arrCharge['Charge'] : $arrCharge['Charge'];
						$fltAdjustmentsTotal	+= $arrCDR['Charge'];
						
						$arrCDR['Units']		= 1;
						$arrCDR['Description']	= ($arrCharge['ChargeType']) ? ($arrCharge['ChargeType']." - ".$arrCharge['Description']) : $arrCharge['Description'];
						$arrCDR['TaxExempt']	= $arrCharge['TaxExempt'];
						
						$arrCategories['Service Charges & Credits']['Itemisation'][]	= $arrCDR;
					}
					
					$arrCategories['Service Charges & Credits']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
					$arrCategories['Service Charges & Credits']['TotalCharge']	= $fltAdjustmentsTotal;
					$arrCategories['Service Charges & Credits']['Records']		= count($arrItemised);
					
					$fltRatedTotal	+= $fltAdjustmentsTotal;
				}
				
				// Get Plan Charges
				$fltPlanChargeTotal			= 0.0;
				$arrPlanChargeAdjustments	= self::_preparedStatementMultiService('selPlanChargeAdjustments', $arrService, $arrInvoice);
				$arrPlanChargeItemisation	= Array();
				foreach ($arrPlanChargeAdjustments as $arrAdjustment)
				{
					// Format Plan Adjustment as CDR
					$arrCDR	= Array();
					$arrCDR['Charge']			= ($arrAdjustment['Nature'] == 'CR') ? 0 - $arrAdjustment['Charge'] : $arrAdjustment['Charge'];
					$arrCDR['Units']			= 1;
					$arrCDR['Description']		= ($arrAdjustment['ChargeType']) ? ($arrAdjustment['ChargeType']." - ".$arrAdjustment['Description']) : $arrAdjustment['Description'];
					$arrCDR['TaxExempt']		= $arrAdjustment['TaxExempt'];
					$arrPlanChargeItemisation[]	= $arrCDR;
					
					$fltPlanChargeTotal			+= $arrCDR['Charge'];
				}
				
				// Add to Service Array
				if (count($arrPlanChargeItemisation))
				{
					$arrCategories['Plan Charges']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
					$arrCategories['Plan Charges']['TotalCharge']	= $fltPlanChargeTotal;
					$arrCategories['Plan Charges']['Records']		= count($arrPlanChargeItemisation);
					$arrCategories['Plan Charges']['Itemisation']	= $arrPlanChargeItemisation;
					
					$fltRatedTotal							+= $fltPlanChargeTotal;
				}
				
				// Get Plan Usage/Credits
				$fltPlanCreditTotal			= 0.0;
				$arrPlanUsageAdjustments	= self::_preparedStatementMultiService('selPlanUsageAdjustments', $arrService, $arrInvoice);
				$arrPlanCreditItemisation	= Array();
				foreach ($arrPlanUsageAdjustments as $arrAdjustment)
				{
					// Format Plan Adjustment as CDR
					$arrCDR	= Array();
					$arrCDR['Charge']			= ($arrAdjustment['Nature'] == 'CR') ? 0 - $arrAdjustment['Charge'] : $arrAdjustment['Charge'];
					$arrCDR['Units']			= 1;
					$arrCDR['Description']		= ($arrAdjustment['ChargeType']) ? ($arrAdjustment['ChargeType']." - ".$arrAdjustment['Description']) : $arrAdjustment['Description'];
					$arrCDR['TaxExempt']		= $arrAdjustment['TaxExempt'];
					$arrPlanCreditItemisation[]	= $arrCDR;
					
					$fltPlanCreditTotal			+= $arrCDR['Charge'];
				}
				
				// Add to Service Array
				if (count($arrPlanCreditItemisation))
				{
					$arrCategories['Plan Usage']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
					$arrCategories['Plan Usage']['TotalCharge']	= $fltPlanCreditTotal;
					$arrCategories['Plan Usage']['Records']		= count($arrPlanCreditItemisation);
					$arrCategories['Plan Usage']['Itemisation']	= $arrPlanCreditItemisation;
					
					$fltRatedTotal							+= $fltPlanCreditTotal;
				}
			}
			
			// Handle ServiceTotals for Indials
			if ($arrService['Indial100'] || $arrService['SharedPlan'])
			{
				// Indial 100s & Shared Plans should only have Rated Totals
				$arrService['ServiceTotal']	= $fltRatedTotal;
			}
			
			$arrService['RecordTypes']	= $arrCategories;
			$arrService['IsRendered']	= ($arrService['ForceRender'] || count($arrCategories)) ? TRUE : FALSE;
		}
		
		return $arrServices;
	}
 	
  	//------------------------------------------------------------------------//
	// getAccountAdjustments()
	//------------------------------------------------------------------------//
	/**
	 * getAccountAdjustments()
	 *
	 * Returns a CDR array of Account Adjustments
	 *
	 * Returns a CDR array of Account Adjustments
	 * 
	 * @param	array	$arrInvoice						Invoice Details
	 *
	 * @return	array									Account Adjustments Array
	 *
	 * @method
	 */
	public static function getAccountAdjustments($arrInvoice)
	{
		$arrAdjustments			= Array();
		$fltAccountChargeTotal	= 0.0;
		$selAccountAdjustments	= self::_preparedStatement('selAccountAdjustments');
		if ($selAccountAdjustments->Execute($arrInvoice) === FALSE)
		{
			throw new Exception($selAccountAdjustments->Error());
		}
		else
		{
			while ($arrAdjustment = $selAccountAdjustments->Fetch())
			{
				$arrCDR								= Array();
				$arrCDR['Description']				= ($arrAdjustment['ChargeType']) ? ($arrAdjustment['ChargeType']." - ".$arrAdjustment['Description']) : $arrAdjustment['Description'];
				$arrCDR['Units']					= 1;
				$arrCDR['Charge']					= $arrAdjustment['Amount'];
				$arrCDR['TaxExempt']				= $arrAdjustment['TaxExempt'];
				$arrAdjustments['Itemisation'][]	= $arrCDR;
				$fltAccountChargeTotal				+= $arrCDR['Charge'];
			}
		}
		
		$arrAdjustments['DisplayType']	= RECORD_DISPLAY_S_AND_E;
		$arrAdjustments['TotalCharge']	= $fltAccountChargeTotal;
		$arrAdjustments['Records']		= count($arrAdjustments['Itemisation']);
		
		return $arrAdjustments;
	}
 	
  	//------------------------------------------------------------------------//
	// getAccountCharges()
	//------------------------------------------------------------------------//
	/**
	 * getAccountCharges()
	 *
	 * Returns the Account Summary and Itemisation as an associative array for a given Invoice
	 *
	 * Returns the Account Summary and Itemisation as an associative array for a given Invoice
	 * 
	 * @param	array	$arrInvoice						Invoice Details
	 * @param	boolean	$bolAdjustments		[optional]	TRUE	: Include 'Service Charges & Credits'
	 * 													FALSE	: Do not add Adjustments
	 * @param	boolean	$bolPlanAdjustments	[optional]	TRUE	: Include 'Plan Charges' and 'Plan Credits'
	 * 													FALSE	: Do not add Plan Adjustments
	 * @param	boolean	$bolGST				[optional]	TRUE	: Add GST Total as the final element (default)
	 * 													FALSE	: Do not add GST Total
	 *
	 * @return	array								Account Summary Array
	 *
	 * @method
	 */
	public static function getAccountCharges($arrInvoice, $bolAdjustments = TRUE, $bolPlanAdjustments = TRUE, $bolGST = TRUE)
	{
		$arrAccountSummary	= Array();
		
		// Get Account Summary
		$selAccountSummary	= self::_preparedStatement('selAccountSummary');
		if ($selAccountSummary->Execute($arrInvoice) === FALSE)
		{
			throw new Exception($selAccountSummary->Error());
		}
		else
		{
			while ($arrSummary = $selAccountSummary->Fetch())
			{
				$arrAccountSummary[$arrSummary['Description']]['TotalCharge']	= number_format($arrSummary['Total'], 2, '.', '');
				$arrAccountSummary[$arrSummary['Description']]['DisplayType']	= $arrSummary['DisplayType'];
			}
		}
		
		// Add Other Charges and Credits
		if ($bolAdjustments)
		{
			$selAccountSummaryCharges	= self::_preparedStatement('selAccountSummaryCharges');
			if (($mixResult = $selAccountSummaryCharges->Execute($arrInvoice)) === FALSE)
			{
				throw new Exception($selAccountSummaryCharges->Error());
			}
			elseif ($mixResult)
			{
				while ($arrSummary = $selAccountSummaryCharges->Fetch())
				{
					$arrAccountSummary['Service Charges & Credits']['TotalCharge']	= number_format($arrSummary['Total'], 2, '.', '');
					$arrAccountSummary['Service Charges & Credits']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
				}
			}
		}
		
		// Account Charges and Credits
		$arrAccountSummary['Account Charges & Credits']	= self::getAccountAdjustments($arrInvoice);
		
		if ($bolPlanAdjustments)
		{
			// Plan Charges
			$selPlanChargeSummary	= self::_preparedStatement('selPlanChargeSummary');
			if ($selPlanChargeSummary->Execute($arrInvoice) === FALSE)
			{
				throw new Exception($selPlanChargeSummary->Error());
			}
			while ($arrPlanChargeSummary = $selPlanChargeSummary->Fetch())
			{
				$arrAccountSummary['Plan Charges']['TotalCharge']	+= $arrPlanChargeSummary['Amount'];
				$arrAccountSummary['Plan Charges']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
				
				if ($arrPlanChargeSummary['Service'] === NULL)
				{
					// Shared Plan Charge -- add to Account Itemisation
					$arrCDR															= Array();
					$arrCDR['Description']											= ($arrPlanChargeSummary['ChargeType']) ? ($arrPlanChargeSummary['ChargeType']." - ".$arrPlanChargeSummary['Description']) : $arrPlanChargeSummary['Description'];
					$arrCDR['Units']												= 1;
					$arrCDR['Charge']												= $arrPlanChargeSummary['Amount'];
					$arrCDR['TaxExempt']											= $arrPlanChargeSummary['TaxExempt'];
					$arrAccountSummary['Plan Charges']['Itemisation'][]	= $arrCDR;
					$arrAccountSummary['Plan Charges']['Records']++;
				}
			}
			
			// Plan Usage/Credit
			$selPlanChargeSummary	= self::_preparedStatement('selPlanUsageSummary');
			if ($selPlanChargeSummary->Execute($arrInvoice) === FALSE)
			{
				throw new Exception($selPlanChargeSummary->Error());
			}
			while ($arrPlanChargeSummary = $selPlanChargeSummary->Fetch())
			{
				$arrAccountSummary['Plan Usage']['TotalCharge']	+= $arrPlanChargeSummary['Amount'];
				$arrAccountSummary['Plan Usage']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
				
				if ($arrPlanChargeSummary['Service'] === NULL)
				{
					// Shared Plan Credit -- add to Account Itemisation
					$arrCDR															= Array();
					$arrCDR['Description']											= ($arrPlanChargeSummary['ChargeType']) ? ($arrPlanChargeSummary['ChargeType']." - ".$arrPlanChargeSummary['Description']) : $arrPlanChargeSummary['Description'];
					$arrCDR['Units']												= 1;
					$arrCDR['Charge']												= $arrPlanChargeSummary['Amount'];
					$arrCDR['TaxExempt']											= $arrPlanChargeSummary['TaxExempt'];
					$arrAccountSummary['Plan Usage']['Itemisation'][]	= $arrCDR;
					$arrAccountSummary['Plan Usage']['Records']++;
				}
			}
		}
		
		// Add GST Element
		if ($bolGST)
		{
			$arrAccountSummary['GST Total']['TotalCharge']	= number_format($arrInvoice['Tax'], 2, '.', '');
			$arrAccountSummary['GST Total']['DisplayType']	= RECORD_DISPLAY_S_AND_E;
		}
		
		// Return Array
		return $arrAccountSummary;
	}
	
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements for this Class
	 *
	 * Access a Static Cache of Prepared Statements for this Class
	 * 
	 * @param	string		$strStatement						Name of the statement
	 * 
	 * @return	Statement										The requested Statement
	 *
	 * @method
	 */
	private static function _preparedStatement($strStatement)
	{
		static	$arrPreparedStatements	= Array();
		if (isset($arrPreparedStatements[$strStatement]))
		{
			return $arrPreparedStatements[$strStatement];
		}
		else
		{
			switch ($strStatement)
			{
				// SELECTS
				case 'selAccountFNNs':
					$arrCols						= Array();
					$arrCols['service_total_id']	= "ServiceTotal.Id";
					$arrCols['CurrentId']			= "ServiceTotal.Service";
					$arrCols['FNN']					= "ServiceTotal.FNN";
					$arrCols['Extension']			= "CASE WHEN ServiceExtension.Id IS NOT NULL THEN ServiceExtension.Name ELSE ServiceTotal.FNN END";
					$arrCols['RangeStart']			= "CASE WHEN ServiceExtension.Id IS NOT NULL THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), LPAD(ServiceExtension.RangeStart, 2, '0')) WHEN Service.Indial100 = 1 THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), '00') ELSE ServiceTotal.FNN END";
					$arrCols['RangeEnd']			= "CASE WHEN ServiceExtension.Id IS NOT NULL THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), LPAD(ServiceExtension.RangeEnd, 2, '0')) WHEN Service.Indial100 = 1 THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), '99') ELSE ServiceTotal.FNN END";
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"(ServiceTotal JOIN Service ON Service.Id = ServiceTotal.Service) LEFT JOIN ServiceExtension ON (ServiceExtension.Service = Service.Id AND ServiceExtension.Archived = 0)",
																					$arrCols,
																					"ServiceTotal.Account = <Account> AND ServiceTotal.invoice_run_id = <invoice_run_id>",
																					"Service.ServiceType, Extension",
																					NULL,
																					"Extension");
					break;
				case 'selServiceDetails':
					$arrService						= Array();
					$arrService['CostCentre']		= "(CASE WHEN CostCentreExtension.Id IS NULL THEN CostCentre.Name ELSE CostCentreExtension.Name END)";
					$arrService['Indial100']		= "MAX(Service.Indial100)";
					$arrService['ForceRender']		= "Service.ForceInvoiceRender";
					$arrService['RatePlan']			= "RatePlan.Name";
					$arrService['SharedPlan']		= "RatePlan.Shared";
					//$arrService['allow_cdr_hiding']	= "RatePlan.allow_cdr_hiding";
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"((((Service JOIN ServiceTotal ON ServiceTotal.Service = Service.Id) JOIN RatePlan ON ServiceTotal.RatePlan = RatePlan.Id) LEFT JOIN CostCentre ON CostCentre.Id = Service.CostCentre) LEFT JOIN ServiceExtension ON (ServiceExtension.Service = Service.Id AND ServiceExtension.Archived = 0)) LEFT JOIN CostCentre CostCentreExtension ON ServiceExtension.CostCentre = CostCentreExtension.Id",
																					$arrService,
																					"ServiceTotal.invoice_run_id = <invoice_run_id> AND ServiceTotal.Service = <CurrentId> AND (ServiceExtension.Name IS NULL OR ServiceExtension.Name = <Extension>)",
																					"Service.ServiceType, Service.FNN, ServiceExtension.Name",
																					NULL,
																					"Service.FNN, ServiceExtension.Name");
					break;
				case 'selServiceInstances':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"service_total_service",
																					"service_id",
																					"service_total_id = <service_total_id>");
					break;
				case 'selAccountSummary':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"(ServiceTypeTotal STT JOIN RecordType RT ON STT.RecordType = RT.Id) JOIN RecordType RG ON RT.GroupId = RG.Id",
																					"RG.Description AS Description, SUM(STT.Charge) AS Total, SUM(Records) AS Records, RG.DisplayType AS DisplayType",
																					"Account = <Account> AND invoice_run_id = <invoice_run_id>",
																					"RG.Description",
																					NULL,
																					"RG.Id");
					break;
				case 'selAccountSummaryCharges':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Charge",
																					"SUM(CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Total, COUNT(Id) AS Records",
																					"Account = <Account> AND invoice_run_id = <invoice_run_id> AND ChargeType NOT IN ('PCAD', 'PCAR', 'PCR', 'PDCR') AND Service IS NOT NULL");
					break;
				case 'selCustomerData':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Account LEFT JOIN Invoice ON Account.Id = Invoice.Account",
																					"BusinessName, Address1, Address2, Suburb, Postcode, State, CustomerGroup, COUNT(Invoice.Id) AS InvoiceCount, BillingType",
																					"Account.Id = <Account>",
																					NULL,
																					NULL,
																					"Account.Id");
					break;
				case 'selAccountAdjustments':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Charge",
																					"ChargeType, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description, global_tax_exempt AS TaxExempt",
																					"invoice_run_id = <invoice_run_id> AND Account = <Account> AND Service IS NULL AND ChargeType NOT IN ('PCAD', 'PCAR', 'PCR', 'PDCR')");
					break;
				case 'selPlanChargeSummary':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Charge",
																					"ChargeType, Service, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description, global_tax_exempt AS TaxExempt",
																					"invoice_run_id = <invoice_run_id> AND Account = <Account> AND ChargeType IN ('PCAD', 'PCAR')");
					break;
				case 'selPlanUsageSummary':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Charge",
																					"ChargeType, Service, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description, global_tax_exempt AS TaxExempt",
																					"invoice_run_id = <invoice_run_id> AND Account = <Account> AND ChargeType IN ('PCR', 'PDCR')");
					break;
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
 	
  	//------------------------------------------------------------------------//
	// _preparedStatementMultiService()
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatementMultiService()
	 *
	 * Creates and executes a Bill Printing Query, summing values for all of the services
	 * passed in
	 *
	 * Creates and executes a Bill Printing Query, summing values for all of the services
	 * passed in
	 * 
	 * @param	string	$strStatement	Name of the statement
	 * @param	array	$arrService		MySQL resultset from _selService with additional 'Id' array
	 * @param	array	$arrParams		WHERE parameters
	 *
	 * @return	mixed					string	: invoice data
	 * 									FALSE	: invalid input
	 *
	 * @method
	 */
 	private static function _preparedStatementMultiService($strStatement, $arrService, $arrParams)
 	{
 		static	$arrPreparedStatements	= Array();
 		
		$intCount = count($arrService['Id']);
 		if (!$intCount)
 		{
			Cli_App_Billing::debug("No Service Ids!");
			Cli_App_Billing::debug($arrService);
 		}
 		
 		// Is there a Statement for this many Service Ids and Type?
 		if (!$arrPreparedStatements[$strStatement][$intCount])
 		{
	 		$arrWhere = Array();
	 		foreach ($arrService['Id'] as $intKey=>$intId)
	 		{
	 			$arrWhere[] = "Service = <Service$intKey>";
	 		}
	 		$strWhereService = "(".implode(' OR ', $arrWhere).")";
	 		
	 		switch ($strStatement)
	 		{
	 			case 'selServiceSummary':
	 				$arrColumns = Array();
			 		$arrColumns['RecordType']	= "GroupType.Description";
			 		$arrColumns['Total']		= "SUM(ServiceTypeTotal.Charge)";
			 		$arrColumns['Records']		= "SUM(Records)";
 					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
 						(
							"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, RecordType AS GroupType",
							$arrColumns,
		 					"$strWhereService AND FNN BETWEEN <RangeStart> AND <RangeEnd> AND invoice_run_id = <invoice_run_id> AND GroupType.Id = RecordType.GroupId",
		 					"ServiceTypeTotal.FNN, GroupType.Description",
		 					NULL,
		 					"GroupType.Description DESC"
	 					);
	 				break;
	 				
	 			case 'selItemisedRecordTypes':
	 				$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
						(	
							"CDR USE INDEX (Service_3) JOIN RecordType ON CDR.RecordType = RecordType.Id, RecordType AS RecordGroup",
							"RecordGroup.Id AS RecordType, RecordGroup.Description AS Description, RecordGroup.DisplayType AS DisplayType", 
							"$strWhereService AND " .
							"RecordGroup.Id = RecordType.GroupId AND " .
							"RecordGroup.Itemised = 1 AND " .
							"CDR.invoice_run_id = <invoice_run_id> AND " .
							"FNN BETWEEN <RangeStart> AND <RangeEnd>",
							"RecordGroup.Description",
							NULL,
							"RecordGroup.Id"
	 					);
	 				break;
	 				
	 			case 'selItemisedCalls':
					$arrColumns = Array();
					$arrColumns['Charge']			= "CASE WHEN CDR.Credit = 0 THEN CDR.Charge ELSE 0 - CDR.Charge END";
					$arrColumns['Source']			= "CDR.Source";
					$arrColumns['Destination']		= "CDR.Destination";
					$arrColumns['StartDatetime']	= "CDR.StartDatetime";
					$arrColumns['EndDatetime']		= "CDR.EndDatetime";
					$arrColumns['Units']			= "CDR.Units";
					$arrColumns['Description']		= "CDR.Description";
					$arrColumns['DestinationCode']	= "CDR.DestinationCode";
					$arrColumns['DisplayType']		= "RecordGroup.DisplayType";
					$arrColumns['RecordGroup']		= "RecordGroup.Description";
					$arrColumns['TaxExempt']		= "RecordType.global_tax_exempt";
					//$arrColumns['allow_cdr_hiding']	= "Rate.allow_cdr_hiding";
 					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
 					(	
						"CDR USE INDEX (Service_3) JOIN RecordType ON CDR.RecordType = RecordType.Id JOIN Rate ON Rate.Id = CDR.Rate" .
						", RecordType as RecordGroup",
						$arrColumns,
						"$strWhereService AND " .
						"RecordGroup.Id = RecordType.GroupId AND " .
						"RecordGroup.Id = <RecordGroup> AND " .
						"CDR.invoice_run_id = <invoice_run_id> AND " .
						"FNN BETWEEN <RangeStart> AND <RangeEnd>",
						"CDR.StartDatetime"
 					);
	 				break;
	 				
	 			case 'selItemisedCharges':
	 				$arrColumns['Charge']				= "Amount";
					$arrColumns['Description']			= "Description";
					$arrColumns['ChargeType']			= "ChargeType";
					$arrColumns['Nature']				= "Nature";
					$arrColumns['TaxExempt']			= "global_tax_exempt";
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
					(	
						"Charge",
						$arrColumns,
						"$strWhereService AND invoice_run_id = <invoice_run_id> AND ChargeType NOT IN ('PCAD', 'PCAR', 'PCR', 'PDCR')"
					);
	 				break;
	 				
	 			case 'selServiceTotal':
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
					(
						"ServiceTotal",
						"SUM(TotalCharge + Debit - Credit) AS TotalCharge, PlanCharge",
						"$strWhereService AND invoice_run_id = <invoice_run_id>",
						NULL,
						NULL,
						"Service"
					);
	 				break;
	 				
	 			case 'selServiceChargesTotal':
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
					(
	 					"Charge",
						"SUM(Amount) AS Charge, 'Service Charges & Credits' AS RecordType, COUNT(Id) AS Records, Nature",
						"$strWhereService AND invoice_run_id = <invoice_run_id>",
						"Nature",
						2,
						"Nature"
					);
	 				break;
	 				
	 			case 'selRecordTypes':
					$arrRecordType	= Array();
					$arrRecordType['RecordGroup']	= "RecordGroup.Description";
					$arrRecordType['GroupId']		= "RecordGroup.Id";
					/*$arrRecordType['Itemised']		= "RecordGroup.Itemised";*/
					$arrRecordType['DisplayType']	= "RecordGroup.DisplayType";
					$arrRecordType['TotalCharge']	= "SUM(ServiceTypeTotal.Charge)";
					$arrRecordType['Records']		= "SUM(ServiceTypeTotal.Records)";
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
					(
	 					"(ServiceTypeTotal JOIN RecordType ON RecordType.Id = ServiceTypeTotal.RecordType) JOIN RecordType RecordGroup ON RecordType.GroupId = RecordGroup.Id",
						$arrRecordType,
						"invoice_run_id = <invoice_run_id> AND $strWhereService AND FNN BETWEEN <RangeStart> AND <RangeEnd>",
						"RecordGroup.Description",
						NULL,
						"RecordGroup.Id"
					);
	 				break;
	 				
	 			case 'selPlanChargeAdjustments':
	 				$arrColumns['Charge']				= "Amount";
					$arrColumns['Description']			= "Description";
					$arrColumns['ChargeType']			= "ChargeType";
					$arrColumns['Nature']				= "Nature";
					$arrColumns['TaxExempt']			= "global_tax_exempt";
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
					(	
						"Charge",
						$arrColumns,
						"$strWhereService AND invoice_run_id = <invoice_run_id> AND ChargeType IN ('PCAD', 'PCAR')"
					);
	 				break;
	 				
	 			case 'selPlanUsageAdjustments':
	 				$arrColumns['Charge']				= "Amount";
					$arrColumns['Description']			= "Description";
					$arrColumns['ChargeType']			= "ChargeType";
					$arrColumns['Nature']				= "Nature";
					$arrColumns['TaxExempt']			= "global_tax_exempt";
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect
					(	
						"Charge",
						$arrColumns,
						"$strWhereService AND invoice_run_id = <invoice_run_id> AND ChargeType IN ('PCR', 'PDCR')"
					);
	 				break;
	 			
	 			default:
	 				// No such Type
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
	 		}
 		}
 		
 		// Prepare WHERE parameters
 		foreach ($arrService['Id'] as $intKey=>$intId)
 		{
 			$arrParams["Service$intKey"] = $intId;
 		}
 		
 		// Execute and return data
 		if ($arrPreparedStatements[$strStatement][$intCount]->Execute($arrParams) === FALSE)
 		{
 			throw new Exception($arrPreparedStatements[$strStatement][$intCount]->Error());
 		}
 		else
 		{
 			return $arrPreparedStatements[$strStatement][$intCount]->FetchAll();
 		}
 	}
}

?>
