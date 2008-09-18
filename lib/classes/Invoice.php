<?php
//----------------------------------------------------------------------------//
// Invoice
//----------------------------------------------------------------------------//
/**
 * Invoice
 *
 * Models a record of the Invoice table
 *
 * Models a record of the Invoice table
 *
 * @class	Invoice
 */
class Invoice
{
	// Local copy of the current Database Properties
	private	$_arrProperties;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 *
	 * @param	array	$arrProperties 		[optional]	Associative array defining an invoice run with keys for each field of the InvoiceRun table
	 * @param	boolean	$bolLoadById		[optional]	Automatically load the Invoice with the passed Id
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($arrProperties=Array(), $bolLoadById=FALSE)
	{
		// Get list of columns from Data Model
		$arrTableDefine	= DataAccess::getDataAccess()->FetchTableDefine('Invoice');
		foreach ($arrTableDefine['Column'] as $strName=>$arrColumn)
		{
			$arrProperties[$strName]	= NULL;
		}
		
		// Set Parameters
		if (is_array($arrProperties))
		{
			// Load from the Database
			$this->_arrProperties	= array_merge($this->_arrProperties, $arrProperties);
		}
		
		// Automatically load the Invoice using the passed Id
		if ($bolLoadById && is_int($arrProperties[$arrTableDefine['Id']]))
		{
			$selInvoiceById	= $this->_preparedStatement('selInvoiceById');
			if ($selInvoiceById->Execute($arrProperties))
			{
				$arrProperties	= $selInvoiceById->Fetch();
			}
			elseif ($selInvoiceById->Error())
			{
				throw new Exception("DB ERROR: ".$selInvoiceById->Error());
			}
			else
			{
				// Do we want to Debug something?
			}
		}
	}
	
	//------------------------------------------------------------------------//
	// generate
	//------------------------------------------------------------------------//
	/**
	 * generate()
	 *
	 * Generates an Invoice and returns the instance
	 * 
	 * Generates an Invoice and returns the instance
	 *
	 * @param		array	$arrAccount							The Account to generate the Invoice for
	 * @return		void
	 * 
	 * @constructor
	 */
	public function generate($arrAccount, &$objInvoiceRun)
	{
		static	$selInvoiceableFNNs;
		static	$selPlanDetails;
		static	$selCurrentService;
		static	$selCDRTotals;
		static	$selEarliestCDR;
		static	$selHasInvoicedCDRs;
		static	$selLastPlanInvoiced;
		
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		// Init the Account Object
		$objAccount	= Account::getForId($arrAccount);
		
		// Is there already an Invoice for this Account?  If so, revoke it
		$objAccount->revokeInvoice();
		
		//----------------- INVOICEABLE SERVICE PREPROCESSING ----------------//
		// Retrieve a list of Invoiceable FNNs for this Account
		$selInvoiceableFNNs	= $this->_preparedStatement('selInvoiceableFNNs');
		if ($selInvoiceableFNNs->Execute($arrAccount) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selInvoiceableFNNs->Error());
		}
		
		// Process each Invoiceable FNN
		$arrServices	= Array();
		while ($arrFNN = $selInvoiceableFNNs->Fetch())
		{
			// Get the Service Details for the current owner of this FNN (or indial range), on this Account
			$selCurrentService	= $this->_preparedStatement('selCurrentService');
			if ($selCurrentService->Execute($arrFNN) === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selCurrentService->Error());
			}
			if (!($arrCurrentService = $selCurrentService->Fetch()))
			{
				// Error
				throw new Exception("WTF -- No Invoiceable Service Id for apparently Invoiceable FNN '{$arrFNN['FNN']}'!");
			}
			
			// Add this Service to our Invoicing Array
			if (!isset($arrServices[$arrCurrentService['Id']]))
			{
				$arrServices[$arrCurrentService['Id']]	= $arrCurrentService;
			}
			$arrServices[$arrCurrentService['Id']]['Ids'][]	= $arrFNN['Id'];
		}
		//--------------------------------------------------------------------//
		
		//----------------------- GENERATE SERVICE DATA ----------------------//
		// Generate Service Data
		$arrSharedPlans	= Array();
		foreach ($arrServices as $intServiceId=>&$arrServiceDetails)
		{
			$arrServiceDetails['ServiceTotal']	= $this->_generateService($arrServiceDetails, &$objAccount, &$objInvoiceRun);
			
			// Is this a Shared Plan?
			if ($arrServiceDetails['ServiceTotal']['Shared'])
			{
				$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['Services'][]					= &$arrServiceDetails;
				$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxExemptCappedCharge']		+= $arrServiceDetails['ServiceTotal']['fltTaxExemptCappedCharge'];
				$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxableCappedCharge']		+= $arrServiceDetails['ServiceTotal']['fltTaxableCappedCharge'];
			}
		}
		
		// Calculate and Add in Shared Plan Charges and Credits as Account
		foreach ($arrSharedPlans as $intRatePlan=>$arrDetails)
		{
			// Get Shared Plan Details
			$selPlanDetailsById	= self::_preparedStatement('selPlanDetailsById');
			if ($selPlanDetailsById->Execute(Array('RatePlan' => $intRatePlan)) === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selPlanDetailsById->Error());
			}
			elseif ($arrPlanDetails = $selPlanDetailsById->Fetch())
			{
				throw new Exception("Unable to retrieve details for RatePlan with Id '{$intRatePlan}'!");
			}
			
			$fltMinimumCharge	= (float)$arrPlanDetails['MinMonthly'];
			$fltUsageStart		= (float)$arrPlanDetails['ChargeCap'];
			$fltUsageLimit		= (float)$arrPlanDetails['UsageCap'];
			
			$fltCDRCappedTotal		= $arrDetails['fltTaxExemptCappedCharge'] + $arrDetails['fltTaxableCappedCharge'];
			$fltTaxableCappedCharge	= $arrDetails['fltTaxableCappedCharge'];
			
			// Determine and add in Plan Credit
			$fltPlanCredit	= min(0, $fltUsageStart - min($fltCDRCappedTotal, $fltUsageLimit));
			
			// Determine Usage
			$fltTotalCharge	= min($fltCDRCappedTotal, $fltUsageStart);
			
			// Apply the Minimum Monthly
			$fltTotalCharge	= ($fltMinimumCharge > 0.0) ? max($fltMinimumCharge, $fltTotalCharge) : $fltTotalCharge;
			
			// Add in Taxable over-usage
			$fltTaxableOverusage	= max(0, $fltTaxableCappedCharge - $fltUsageLimit);
			$fltTotalCharge			+= $fltTaxableOverusage;
			
			// Add in Tax exempt over-usage
			$fltTaxExemptOverusage	= max(0, $fltCDRCappedTotal - $fltUsageLimit) - $fltTaxableOverusage;
			$fltTotalCharge			+= $fltTaxExemptOverusage;
			
			$arrServiceTotal['Tax']	+= Invoice::calculateGlobalTaxComponent($fltTaxableOverusage);
		}
		//--------------------------------------------------------------------//
		
		//----------------------- GENERATE INVOICE DATA ----------------------//
		// Mark Account Adjustments
		// TODO
		
		// Calculate Tax Total
		// TODO
		//--------------------------------------------------------------------//
	}
	
	//------------------------------------------------------------------------//
	// _generateService
	//------------------------------------------------------------------------//
	/**
	 * _generateService()
	 *
	 * Generates a Service on an Invoice for a given Account and InvoiceRun
	 *
	 * Generates a Service on an Invoice for a given Account and InvoiceRun
	 * 
	 * @param	array	$arrService					Details for the Service we're Invoicing
	 * @param	array	$arrAccount					Details for the Account we're Invoicing
	 * @param	array	$arrInvoiceRun				Details for the Invoice Run we're generating
	 *
	 * @return	array								ServiceTotal data for this Service
	 *
	 * @method
	 */
	private function _generateService($arrServiceDetails, $arrAccount, $arrInvoiceRun)
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		
		$arrServiceTotal	= Array();
		$intServiceId		= $arrServiceDetails['Id'];
		
		//--------------------------- PLAN CHARGES ---------------------------//
		// Retrieve Plan Details for the current Service
		$selPlanDetails	= $this->_preparedStatement('selPlanDetails');
		if ($selPlanDetails->Execute(Array('Service' => $intServiceId, 'EffectiveDate' => $arrInvoiceRun['intInvoiceDatetime'])) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selPlanDetails->Error());
		}
		$arrPlanDetails	= $selPlanDetails->Fetch();
		
		// Determine & Add in Plan Charge & Usage Limit Details
		if ($arrPlanDetails['Shared'])
		{
			// This is a Shared Plan --  skip the Plan Charge and Usage Stage
			$arrServiceTotal['Shared']	= TRUE;
			$fltMinimumCharge	= 0.0;
			$fltUsageStart		= 0.0;
			$fltUsageLimit		= 0.0;
		}
		else
		{
			$selEarliestCDR	= $this->_preparedStatement('selEarliestCDR');
			if ($selEarliestCDR->Execute(Array('Service' => $intServiceId)))
			{
				$arrEarliestCDR	= $selEarliestCDR->Fetch();
			}
			elseif ($selEarliestCDR->Error())
			{
				throw new Exception("DB ERROR: ".$selEarliestCDR->Error());
			}
			else
			{
				throw new Exception("Unable to get EarliestCDR details for {$arrAccount['Id']}{$arrServiceDetails['FNN']}!");
			}
			
			// Is the Service tolling?
			if ($arrEarliestCDR['MinEarliestCDR'])
			{
				$fltMinimumCharge	= (float)$arrPlanDetails['MinMonthly'];
				$fltUsageStart		= (float)$arrPlanDetails['ChargeCap'];
				$fltUsageLimit		= (float)$arrPlanDetails['UsageCap'];
				
				// Yes -- Does this Service have any Invoiced CDRs?
				$selHasInvoicedCDRs	= $this->_preparedStatement('selHasInvoicedCDRs');
				$mixResult			= $selHasInvoicedCDRs->Execute(Array('Service' => $intServiceId));
				if ($mixResult === FALSE)
				{
					throw new Exception("DB ERROR: ".$selHasInvoicedCDRs->Error());
				}
				elseif (!$mixResult)
				{
					// No -- Is this on a Charge-in-Advance Plan?
					if ($arrPlanDetails['InAdvance'])
					{
						$selLastPlanInvoiced	= $this->_preparedStatement('selLastPlanInvoiced');
						if ($selLastPlanInvoiced->Execute(Array('Service' => $intServiceId)) !== FALSE)
						{
							$arrLastPlanInvoiced	= $selLastPlanInvoiced->Fetch();
							if ($arrLastPlanInvoiced === FALSE || $arrLastPlanInvoiced['RatePlan'] !== $arrPlanDetails['Id'])
							{
								// The this Plan has not been invoiced before, so generate a Charge in Advance
								$intPeriodStart	= $arrInvoiceRun['intInvoiceDatetime'];
								$intPeriodEnd	= strtotime("-1 day", strtotime("+1 month", $arrInvoiceRun['intInvoiceDatetime']));
								$this->_addPlanCharge('PCAD', $fltMinimumCharge, $arrPlanDetails['Name'], $intPeriodStart, $intPeriodEnd, $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
							}
						}
						else
						{
							throw new Exception("DB ERROR: ".$selLastPlanInvoiced->Error());
						}
					}
					
					// Prorate the Charges and Usage details in Arrears
					$fltMinimumCharge	= Invoice::prorate($fltMinimumCharge	, strtotime($arrEarliestCDR['MinEarliestCDR']), $arrInvoiceRun['intLastInvoiceDatetime'], $arrInvoiceRun['intInvoiceDatetime']);
					$fltUsageStart		= Invoice::prorate($fltUsageStart		, strtotime($arrEarliestCDR['MinEarliestCDR']), $arrInvoiceRun['intLastInvoiceDatetime'], $arrInvoiceRun['intInvoiceDatetime']);
					$fltUsageLimit		= Invoice::prorate($fltUsageLimit		, strtotime($arrEarliestCDR['MinEarliestCDR']), $arrInvoiceRun['intLastInvoiceDatetime'], $arrInvoiceRun['intInvoiceDatetime']);
					
					$strChargeType	= 'PCAR';
					$intPeriodStart	= strtotime($arrEarliestCDR['MinEarliestCDR']);
					$intPeriodEnd	= strtotime("-1 day", $arrInvoiceRun['intInvoiceDatetime']);
					$this->_addPlanCharge('PCAR', $fltMinimumCharge, $arrPlanDetails['Name'], $arrInvoiceRun['intLastInvoiceDatetime'], strtotime("-1 day", $arrInvoiceRun['intLastInvoiceDatetime']), $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
				}
				else
				{
					// Charge the Standard Plan Charge
					if ($arrPlanDetails['InAdvance'])
					{
						$strChargeType	= 'PCAD';
						$intPeriodStart	= $arrInvoiceRun['intInvoiceDatetime'];
						$intPeriodEnd	= strtotime("-1 day", strtotime("+1 month", $arrInvoiceRun['intInvoiceDatetime']));
					}
					else
					{
						$strChargeType	= 'PCAR';
						$intPeriodStart	= $arrInvoiceRun['intLastInvoiceDatetime'];
						$intPeriodEnd	= strtotime("-1 day", $arrInvoiceRun['intInvoiceDatetime']);
					}
					$this->_addPlanCharge($strChargeType, $fltMinimumCharge, $arrPlanDetails['Name'], $intPeriodStart, $intPeriodEnd, $arrAccount['AccountGroup'], $arrAccount['Id'], $intServiceId);
				}
			}
			else
			{
				// No -- ignore all Plan Charges, because we haven't tolled yet
				$fltMinimumCharge	= 0.0;
				$fltUsageStart		= 0.0;
				$fltUsageLimit		= 0.0;
			}
		}
		//--------------------------------------------------------------------//
		
		//--------------------------- SERVICE TOTALS -------------------------//
		// Mark all CDRs for this Service as TEMPORARY_INVOICE
		$strSQL		= "UPDATE CDR SET Status = ".CDR_TEMP_INVOICE." WHERE Status = ".CDR_RATED." AND Service IN (".implode(', ', $arrServiceDetails['Ids']).")";
		$resResult	= $qryQuery->Execute($strSQL);
		if ($resResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		
		// Generate ServiceTypeTotals
		$strExtensionsQuery  = "INSERT INTO ServiceTypeTotal (FNN, AccountGroup, Account, Service, InvoiceRun, RecordType, Charge, Units, Records, RateGroup, Cost, invoice_run_id)";
		$strExtensionsQuery .= " SELECT CDR.FNN, CDR.AccountGroup, CDR.Account, CDR.Service, '{$this->InvoiceRun}' AS InvoiceRun,";
		$strExtensionsQuery .= " CDR.RecordType, SUM(CASE WHEN CDR.Credit = 1 THEN 0-CDR.Charge ELSE CDR.Charge END) AS Charge, SUM(CASE WHEN CDR.Credit = 1 THEN 0-CDR.Units ELSE CDR.Units END) AS Units, COUNT(CDR.Charge) AS Records, ServiceRateGroup.RateGroup AS RateGroup, SUM(CASE WHEN CDR.Credit = 1 THEN 0-CDR.Cost ELSE CDR.Cost END) AS Cost, {$this->invoice_run_id} AS invoice_run_id";
		$strExtensionsQuery .= " FROM CDR USE INDEX (Account_2) JOIN Service ON Service.Id = CDR.Service, ServiceRateGroup";
		$strExtensionsQuery .= " WHERE CDR.FNN IS NOT NULL AND CDR.RecordType IS NOT NULL";
		$strExtensionsQuery .= " AND CDR.Status = ".CDR_TEMP_INVOICE;
		$strExtensionsQuery .= " AND CDR.Service IN (".implode(', ', $arrServiceDetails['Ids']).")";
		$strExtensionsQuery .= " AND ServiceRateGroup.Id = (SELECT SRG.Id FROM ServiceRateGroup SRG WHERE NOW() BETWEEN SRG.StartDatetime AND SRG.EndDatetime AND SRG.Service = CDR.Service ORDER BY CreatedOn DESC LIMIT 1) ";
		$strExtensionsQuery .= " GROUP BY CDR.Service, CDR.FNN, CDR.RecordType";
		if ($qryQuery->Execute($strExtensionsQuery) === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		
		// Get CDR Total Details
		$strSQL			= "	SELECT SUM(CDR.Cost) AS TotalCost, SUM(CDR.Charge) AS TotalCharge, Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt
							FROM (CDR JOIN Rate ON CDR.Rate = Rate.Id) JOIN RecordType ON RecordType.Id = CDR.RecordType
							WHERE CDR.Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND CDR.Status = ".CDR_TEMP_INVOICE."
							GROUP BY Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt";
		$resCDRTotals	= $qryQuery->Execute($strSQL);
		if ($resCDRTotals === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		$arrCDRTotals	= Array();
		while ($arrCDRTotal = $resCDRTotals->fetch_assoc())
		{
			$strCapped	= ($arrCDRTotal['Uncapped'])		 	? 'Uncapped'	: 'Capped';
			$strCredit	= ($arrCDRTotal['Credit'])				? 'Credit'		: 'Debit';
			$strTax		= ($arrCDRTotal['global_tax_exempt'])	? 'ExTax'		: 'IncTax';
			$arrCDRTotals['Charge']	[$strCapped][$strCredit][$strTax]	= $arrCDRTotal['TotalCharge'];
			$arrCDRTotals['Cost']	[$strCapped][$strCredit][$strTax]	= $arrCDRTotal['TotalCost'];
		}
		
		$fltTaxExemptCappedCharge	= $arrCDRTotals['Charge']['Capped']['Debit']['ExTax'] - $arrCDRTotals['Charge']['Capped']['Credit']['ExTax'];
		$fltTaxableCappedCharge		= $arrCDRTotals['Charge']['Capped']['Debit']['IncTax'] - $arrCDRTotals['Charge']['Capped']['Credit']['IncTax']; 
		$fltCDRCappedTotal			= $fltTaxableCappedCharge + $fltTaxExemptCappedCharge;
		
		$fltTaxExemptUncappedCharge	= $arrCDRTotals['Charge']['Uncapped']['Debit']['ExTax'] - $arrCDRTotals['Charge']['Uncapped']['Credit']['ExTax'];
		$fltTaxableUncappedCharge	= $arrCDRTotals['Charge']['Uncapped']['Debit']['IncTax'] - $arrCDRTotals['Charge']['Uncapped']['Credit']['IncTax'];
		$fltCDRUncappedTotal		= $fltTaxableUncappedCharge + $fltTaxExemptUncappedCharge;
		
		$fltTaxExemptCappedCost		= $arrCDRTotals['Cost']['Capped']['Debit']['ExTax'] - $arrCDRTotals['Cost']['Capped']['Credit']['ExTax'];
		$fltTaxableCappedCost		= $arrCDRTotals['Cost']['Capped']['Debit']['IncTax'] - $arrCDRTotals['Cost']['Capped']['Credit']['IncTax']; 
		$fltCDRCappedCost			= $fltTaxableCappedCharge + $fltTaxExemptCappedCharge;
		
		$fltTaxExemptUncappedCost	= $arrCDRTotals['Cost']['Uncapped']['Debit']['ExTax'] - $arrCDRTotals['Cost']['Uncapped']['Credit']['ExTax'];
		$fltTaxableUncappedCost		= $arrCDRTotals['Cost']['Uncapped']['Debit']['IncTax'] - $arrCDRTotals['Cost']['Uncapped']['Credit']['IncTax'];
		$fltCDRUncappedCost			= $fltTaxableUncappedCharge + $fltTaxExemptUncappedCharge;
		
		$arrServiceTotal['fltTaxExemptCappedCharge']	= $fltTaxExemptCappedCharge;
		$arrServiceTotal['fltTaxableCappedCharge']		= $fltTaxableCappedCharge;
		
		// Calculate Service Plan Usage for non-Shared Services
		$fltTotalCharge	= 0.0;
		if (!$arrPlanDetails['Shared'])
		{
			// Determine and add in Plan Credit
			$fltPlanCredit	= min(0, $fltUsageStart - min($fltCDRCappedTotal, $fltUsageLimit));
			
			// Determine Usage
			$fltTotalCharge	= min($fltCDRCappedTotal, $fltUsageStart);
			
			// Apply the Minimum Monthly
			$fltTotalCharge	= ($fltMinimumCharge > 0.0) ? max($fltMinimumCharge, $fltTotalCharge) : $fltTotalCharge;
			
			// Add in Taxable over-usage
			$fltTaxableOverusage	= max(0, $fltTaxableCappedCharge - $fltUsageLimit);
			$fltTotalCharge			+= $fltTaxableOverusage;
			
			// Add in Tax exempt over-usage
			$fltTaxExemptOverusage	= max(0, $fltCDRCappedTotal - $fltUsageLimit) - $fltTaxableOverusage;
			$fltTotalCharge			+= $fltTaxExemptOverusage;
			
			$arrServiceTotal['Tax']	+= Invoice::calculateGlobalTaxComponent($fltTaxableOverusage);
		}
		
		// Add in Uncapped Charges
		$fltTotalCharge	+= $fltCDRUncappedTotal;
		$arrServiceTotal['Tax']	+= Invoice::calculateGlobalTaxComponent($fltTaxableUncappedCharge);
		
		// Mark all Service Charges as TEMPORARY_INVOICE
		if ($qryQuery->Execute("UPDATE Charge SET Status = ".CHARGE_TEMP_INVOICE.", invoice_run_id = {$arrInvoiceRun['Id']}, InvoiceRun = '{$arrInvoiceRun['InvoiceRun']}' WHERE Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND Service IN (".implode(', ', $arrServiceDetails['Ids']).")") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		
		// Add in Service Billing-time Charges
		$arrModules	= Billing_Charge::getModules();
		foreach ($this->_arrBillingChargeModules[$arrAccount['CustomerGroup']]['Billing_Charge_Service'] as $chgModule)
		{
			// Generate charge
			$mixResult = $chgModule->Generate($arrInvoiceRun, $arrServiceDetails);
		}
		
		// Retrieve Charge Totals
		$resResult	= $qryQuery->Execute(	"SELECT Charge.Nature, Charge.global_tax_exempt, SUM(Charge.Amount) AS Total " .
											"FROM Charge " .
											"WHERE Charge.Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND Charge.Status = ".CHARGE_TEMP_INVOICE.
											" GROUP BY Charge.Nature, Charge.global_tax_exempt");
		if ($resResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$resResult->Error());
		}
		$arrChargeTotals	= Array();
		while ($arrChargeTotal = $resResult->fetch_assoc())
		{
			$arrChargeTotals[$arrChargeTotal['Nature']][($arrChargeTotal['global_tax_exempt'])	? 'ExTax' : 'IncTax']	= $arrChargeTotal['Total'];
			
			$fltTotalCharge	+= ($arrChargeTotal['Nature'] === 'DR') ? $arrChargeTotal['Amount'] : -$arrChargeTotal['Amount'];
		}
		$arrServiceTotal['Tax']	+= Invoice::calculateGlobalTaxComponent($arrChargeTotals['DR']['IncTax']);
		$arrServiceTotal['Tax']	-= Invoice::calculateGlobalTaxComponent($arrChargeTotals['CR']['IncTax']);
		$fltServiceCredits		= $arrChargeTotals['CR']['IncTax'] + $arrChargeTotals['CR']['ExTax'];
		$fltServiceDebits		= $arrChargeTotals['DR']['IncTax'] + $arrChargeTotals['DR']['ExTax'];
		
		// Finalise and Insert Service Total
		$arrServiceTotal['FNN']					= $arrServiceDetails['FNN'];
		$arrServiceTotal['AccountGroup']		= $arrAccount['AccountGroup'];
		$arrServiceTotal['Account']				= $arrAccount['Id'];
		$arrServiceTotal['Service']				= $arrServiceDetails['Id'];
		$arrServiceTotal['InvoiceRun']			= $this->InvoiceRun;			// FIXME: Remove when we no longer support this field
		$arrServiceTotal['invoice_run_id']		= $this->invoice_run_id;
		$arrServiceTotal['CappedCharge']		= $fltCDRCappedTotal;
		$arrServiceTotal['UncappedCharge']		= $fltCDRUncappedTotal;
		$arrServiceTotal['TotalCharge']			= $fltTotalCharge;
		$arrServiceTotal['Credit']				= $fltServiceCredits;
		$arrServiceTotal['Debit']				= $fltServiceDebits;
		$arrServiceTotal['RatePlan']			= $arrPlanDetails['Id'];
		$arrServiceTotal['service_rate_plan']	= $arrPlanDetails['ServiceRatePlan'];
		$arrServiceTotal['CappedCost']			= $fltCDRCappedCost;
		$arrServiceTotal['UncappedCost']		= $fltCDRUncappedCost;
		$arrServiceTotal['PlanCharge']			= 0.0;							// Deprecated
		$insServiceTotal	= self::_preparedStatement('insServiceTotal');
		if ($insServiceTotal->Execute($arrServiceTotal) === FALSE)
		{
			throw new Exception("DB ERROR: ".$insServiceTotal->Error());
		}
		
		// Return the Service Total details
		return $arrServiceTotal;
		//--------------------------------------------------------------------//
	}
	
	//------------------------------------------------------------------------//
	// prorate
	//------------------------------------------------------------------------//
	/**
	 * prorate()
	 *
	 * Prorates a given Charge for a defined period
	 *
	 * Prorates a given Charge for a defined period.
	 * 
	 * @param	float	$fltAmount								Full value of the Charge
	 * @param	integer	$intChargeStartDate						The date on which the Charge Period Starts
	 * @param	integer	$intPeriodStartDate						The date on which the Billing Period Starts
	 * @param	integer	$intPeriodEndDate						The date on which the Billing Period Ends
	 * @param	string	$strSmallestDenomination	[optional]	The smallest Datetime denomination that will be used. (defaults to DATE_TRUNCATE_DAY, where all dates are rounded down to the nearest Day)
	 * @param	boolean	$bolAllowOverflow			[optional]	TRUE	: The prorated amount will be greater than $fltAmount if $intChargeStartDate is before $intPeriodStartDate
	 * 															FALSE	: The prorated amount will be capped at $fltAmount if $intChargeStartDate is before $intPeriodStartDate
	 *
	 * @return													Prorated Amount (rounded to 2 decimal places)
	 *
	 * @method
	 */
	public static function prorate($fltAmount, $intChargeDate, $intPeriodStartDate, $intPeriodEndDate, $strSmallestDenomination=DATE_TRUNCATE_DAY, $bolAllowOverflow=TRUE)
	{
		$strSmallestDenomination	= ($strSmallestDenomination) ? $strSmallestDenomination : DATE_TRUNCATE_DAY;
		$intProratePeriod			= TruncateTime($intPeriodEndDate, $strSmallestDenomination, 'floor') - TruncateTime($intChargeDate, $strSmallestDenomination, 'floor');
		$intBillingPeriod			= TruncateTime($intPeriodEndDate, $strSmallestDenomination, 'floor') - TruncateTime($intPeriodStartDate, $strSmallestDenomination, 'floor');
		$fltProratedAmount			= ($fltAmount / $intBillingPeriod) * $intProratePeriod;
		$fltProratedAmount			= round($fltProratedAmount, 2);
		return $fltProratedAmount;
	}
	
	//------------------------------------------------------------------------//
	// calulateGlobalTaxComponent
	//------------------------------------------------------------------------//
	/**
	 * calulateGlobalTaxComponent()
	 *
	 * Calculates the Global Tax Component for a given amount, and returns it
	 *
	 * Calculates the Global Tax Component for a given amount, and returns it
	 * 
	 * @param	float	$fltAmount								Amount to be taxed
	 * @param	string	$strEffectiveDate						The date to apply the tax on
	 *
	 * @return													Tax Component (rounded to 2 decimal places)
	 *
	 * @method
	 */
	public static function calulateGlobalTaxComponent($fltAmount, $strEffectiveDate)
	{
		static	$arrGlobalTax;
		if (!isset($arrGlobalTax))
		{
			$qryQuery	= new Query();
			$resResult	= $qryQuery->Execute("SELECT * FROM tax_type WHERE is_global = 1 AND (<EffectiveDate> BETWEEN StartDatetime AND EndDatetime OR (<EffectiveDate> > StartDatetime AND EndDatetime IS NULL))"); 
			if ($resResult)
			{
				$arrGlobalTax	= $resResult->fetch_assoc();
			}
			elseif ($qryQuery->Error())
			{
				throw new Exception("DB ERROR: ".$qryQuery->Error());
			}
			else
			{
				$arrGlobalTax	= Array('tax_rate_percentage' => 0.0);
			}
		}
		
		return $fltAmount * $arrGlobalTax['tax_rate_percentage'];
	}
	
	
	
	//------------------------------------------------------------------------//
	// _addPlanCharge
	//------------------------------------------------------------------------//
	/**
	 * _addPlanCharge()
	 *
	 * Adds a Plan Charge to a given Account or Service
	 *
	 * Adds a Plan Charge to a given Account or Service
	 * 
	 * @param	string	$strChargeType							The ChargeType to Generate
	 * @param	float	$fltAmount								The value of the Plan Charge
	 * @param	string	$strPlanName							The Name of the Plan
	 * @param	integer	$intPeriodStartDate						The start of the Billing Period
	 * @param	integer	$intPeriodEndDate						The end of the Billing Period
	 * @param	integer	$intAccountGroup						Account Group Id
	 * @param	integer	$intAccount								Account Id
	 * @param	integer	$intService					[optional]	Service Id or NULL for Account Charge
	 * 
	 * @return	boolean
	 *
	 * @method
	 */
	private function _addPlanCharge($strChargeType, $fltAmount, $strPlanName, $intPeriodStartDate, $intPeriodEndDate, $intAccountGroup, $intAccount, $intService=NULL)
	{
		static	$selChargeType;
		static	$arrChargeTypes;
		
		// Cache ChargeType details
		if (!isset($arrChargeTypes[$strChargeType]))
		{
			$selChargeType	= ($selChargeType) ? $selChargeType : new StatementSelect("ChargeType", "*", "ChargeType = <ChargeType>");
			if ($selChargeType->Execute(Array('ChargeType' => $strChargeType)))
			{
				$arrChargeTypes[$strChargeType]	= $selChargeType->Fetch();
			}
			elseif ($selChargeType->Error())
			{
				throw new Exception("DB ERROR: ".$selChargeType->Fetch());
			}
			else
			{
				throw new Exception("Unable to retrieve details for ChargeType '{$strChargeType}'!");
			}
		}
		
		// Generate Charge
		$arrPlanCharge						= Array();
		$arrPlanCharge['AccountGroup']		= $intAccountGroup;
		$arrPlanCharge['Account']			= $intAccount;
		$arrPlanCharge['Service']			= $intService;
		$arrPlanCharge['tax_type']			= $arrChargeTypes[$strChargeType]['tax_type'];
		$arrPlanCharge['Nature']			= $arrChargeTypes[$strChargeType]['Nature'];
		$arrPlanCharge['ChargeType']		= $arrChargeTypes[$strChargeType]['ChargeType'];
		$arrPlanCharge['charge_type_id']	= $arrChargeTypes[$strChargeType]['Id'];
		$arrPlanCharge['global_tax_exempt']	= FALSE;
		$arrPlanCharge['Description']		= "{$strPlanName} ".$arrChargeTypes[$strChargeType]['Description']." from ".date("d/m/Y", $intPeriodStartDate)." to ".date("d/m/Y", $intPeriodEndDate);
		$arrPlanCharge['ChargedOn']			= date("Y-m-d");
		$arrPlanCharge['Amount']			= $fltAmount;
		if (!$this->Framework->AddCharge($arrPlanCharge))
		{
			throw new Exception("Unable to create '{$arrPlanCharge['Description']}' for {$intAccount}::{$intService}!");
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Retrieves a Property for this Object
	 *
	 * Retrieves a Property for this Object
	 * 
	 * @param	string	$strProperty						The property to retrieve
	 * 
	 * @return	mixed										The value of the Property
	 *
	 * @method
	 */
	public function __get($strProperty)
	{
		return (isset($this->_arrProperties[$strProperty])) ? $this->_arrProperties[$strProperty] : NULL;
	}
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Sets a Property for this Object
	 *
	 * Sets a Property for this Object
	 * 
	 * @param	string	$strProperty						The property to set
	 * @param	mixed	$mixValue							The value to set
	 *
	 * @method
	 */
	public function __set($strProperty, $mixValue)
	{
		$this->_arrProperties[$strProperty]	= $mixValue;
	}
	
	//------------------------------------------------------------------------//
	// _preparedStatement
	//------------------------------------------------------------------------//
	/**
	 * _preparedStatement()
	 *
	 * Access a Static Cache of Prepared Statements used by Invoice
	 *
	 * Access a Static Cache of Prepared Statements used by Invoice
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
				case 'selInvoiceById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Account", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selInvoiceableFNNs':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Service JOIN service_status ON Service.Status = service_status.id",
																					"Id, FNN, Indial100",
																					"Account = <Account> AND CreatedOn < <InvoiceDatetime> AND (ClosedOn > <InvoiceDatetime> OR service_status.can_invoice = 1)");
					break;
				case 'selPlanDetails':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"ServiceRatePlan JOIN RatePlan ON RatePlan.Id = ServiceRatePlan.RatePlan", 
																					"RatePlan.*, ServiceRatePlan.Id AS ServiceRatePlan",
																					"ServiceRatePlan.Service = <Service> AND <EffectiveDate> >= StartDatetime AND <EffectiveDate> < EndDatetime",
																					"CreatedOn DESC",
																					"1");
					break;
				case 'selPlanDetailsById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"RatePlan", 
																					"*",
																					"Id = <RatePlan>");
					break;
				case 'selCurrentService':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Service",
																					"",
																					"");
					break;
				case 'selCDRTotals':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"(CDR JOIN Rate ON CDR.Rate = Rate.Id) JOIN RecordType ON RecordType.Id = CDR.RecordType",
																					"SUM(CDR.Cost), SUM(CDR.Charge), Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt",
																					"CDR.Service = <Service> AND CDR.Status = ".CDR_TEMP_INVOICE,
																					NULL,
																					NULL,
																					"Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt");
					break;
				case 'selEarliestCDR':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Service", "EarliestCDR AS MinEarliestCDR", "Id = <Service>");
					break;
				case 'selHasInvoicedCDRs':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("ServiceTypeTotal", "SUM(Records)", "Service = <Service>");
					break;
				case 'selLastPlanInvoiced':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("ServiceTotal", "Id", "Service = <Service> AND RatePlan = <RatePlan>", "Id DESC", "1");
					break;
				
				// INSERTS
				case 'insServiceTotal':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("ServiceTotal");
					break;
				
				// UPDATE BY IDS
				
				// UPDATES
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>