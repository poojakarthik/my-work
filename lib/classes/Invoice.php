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
class Invoice extends ORM
{
	protected	$_strTableName	= "Invoice";

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
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
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
	 * @param		Account		$objAccount							The Account to generate the Invoice for
	 * @param		Invoice_Run	$objInvoiceRun						The InvoiceRun we're generating
	 *
	 * @return		void
	 *
	 * @constructor
	 */
	public function generate($objAccount, $objInvoiceRun)
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();

		// Is there already an Invoice for this Account?  If so, revoke it
		Log::getLog()->log("\t* Revoking any existing Invoices for Account with Id {$objAccount->Id}...");
		self::revokeByAccount($objAccount);

		$this->invoice_run_id	= $objInvoiceRun->Id;
		$this->_objInvoiceRun	= $objInvoiceRun;
		$this->_objAccount		= $objAccount;
		$this->AccountGroup		= $objAccount->AccountGroup;
		$this->Account			= $objAccount->Id;
		$this->CreatedOn		= $objInvoiceRun->BillingDate;
		$this->Total			= 0.0;
		$this->Debits			= 0.0;
		$this->Credits			= 0.0;
		$this->Tax				= 0.0;
		
		// Calculate Billing Period
		$selLastInvoiceDatetime	= self::_preparedStatement('selLastInvoiceDatetime');
		if ($selLastInvoiceDatetime->Execute() === false)
		{
			throw new Exception($selLastInvoiceDatetime->Error());
		}
		$arrLastInvoiceDatetime			= $selLastInvoiceDatetime->Fetch();
		$this->intInvoiceDatetime		= $objInvoiceRun->intInvoiceDatetime;
		$this->intLastInvoiceDatetime	= ($arrLastInvoiceDatetime) ? strtotime($arrLastInvoiceDatetime['BillingDate']) : $objInvoiceRun->intLastInvoiceDatetime;
		$this->strInvoiceDatetime		= date("Y-m-d H:i:s", $this->intInvoiceDatetime);

		//----------------- INVOICEABLE SERVICE PREPROCESSING ----------------//
		// Retrieve a list of Invoiceable FNNs for this Account
		Log::getLog()->log("\t * Getting list of Invoiceable FNNs...");
		$selInvoiceableFNNs	= $this->_preparedStatement('selInvoiceableFNNs');
		if ($selInvoiceableFNNs->Execute(Array('InvoiceDatetime'=>$this->strInvoiceDatetime, 'Account'=>$objAccount->Id)) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selInvoiceableFNNs->Error());
		}

		// Process each Invoiceable FNN
		$arrServices	= Array();
		while ($arrFNN = $selInvoiceableFNNs->Fetch())
		{
			Log::getLog()->log("\t\t * Getting details for FNN {$arrFNN['FNN']}...");

			// Get the Service Details for the current owner of this FNN (or indial range), on this Account
			$arrWhere	= Array();
			$arrWhere['FNN']			= $arrFNN['FNN'];
			$arrWhere['IndialRange']	= substr($arrFNN['FNN'], 0, -2).'__';
			$arrWhere['DateTime']		= $this->strInvoiceDatetime;
			$arrWhere['Account']		= $objAccount->Id;
			$selCurrentService	= $this->_preparedStatement('selCurrentService');
			if ($selCurrentService->Execute($arrWhere) === FALSE)
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
		$arrSharedPlans	= Array();
		foreach ($arrServices as $intServiceId=>&$arrServiceDetails)
		{
			Log::getLog()->log("\t + Generating Service Total Data for Service with Id {$intServiceId}...", FALSE);

			// Generate Service Total Data
			$mixServiceTotal	= $this->_generateService($arrServiceDetails, $objAccount, $objInvoiceRun);
			if ($mixServiceTotal !== FALSE)
			{
				$arrServiceDetails['ServiceTotal']	= $mixServiceTotal;
				$this->Debits						+= $arrServiceDetails['ServiceTotal']['TotalCharge'] + $arrServiceDetails['ServiceTotal']['Debit'];
				$this->Credits						+= $arrServiceDetails['ServiceTotal']['Credit'];
				$this->Tax							+= $arrServiceDetails['ServiceTotal']['Tax'];
				$fltServiceGrandTotal				= $arrServiceDetails['ServiceTotal']['TotalCharge'] + $arrServiceDetails['ServiceTotal']['Debit'] - $arrServiceDetails['ServiceTotal']['Credit'];
				Log::getLog()->log("\t Total: \${$fltServiceGrandTotal}; Tax: \${$arrServiceDetails['ServiceTotal']['Tax']}");

				// Is this a Shared Plan?
				if ($arrServiceDetails['ServiceTotal']['Shared'])
				{
					Log::getLog()->log("\t\t ! Service is on Shared Plan {$arrServiceDetails['ServiceTotal']['RatePlan']}...");
					$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['Services'][$intServiceId]		= &$arrServiceDetails;
					$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxExemptCappedCharge']		+= $arrServiceDetails['ServiceTotal']['fltTaxExemptCappedCharge'];
					$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxableCappedCharge']		+= $arrServiceDetails['ServiceTotal']['fltTaxableCappedCharge'];
				}
			}
			else
			{
				// Unable to create Service Total Data (for a sane reason, eg. No Rate Plan)
				Log::getLog()->log("\t Service is not invoicable");
			}
		}

		// Calculate and Add in Shared Plan Charges and Credits as Account Charges
		Log::getLog()->log("\t * Generating Shared Plan Charges...");
		foreach ($arrSharedPlans as $intRatePlan=>$arrDetails)
		{
			// Get Shared Plan Details
			Log::getLog()->log("\t\t + Rate Plan {$intRatePlan}...");
			$selPlanDetailsById	= self::_preparedStatement('selPlanDetailsById');
			if ($selPlanDetailsById->Execute(Array('RatePlan' => $intRatePlan)) === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selPlanDetailsById->Error());
			}
			elseif (!($arrPlanDetails = $selPlanDetailsById->Fetch()))
			{
				throw new Exception("Unable to retrieve details for RatePlan with Id '{$intRatePlan}'!");
			}

			$arrServiceIds		= array_keys($arrDetails['Services']);

			// Add Plan Charges
			$arrUsageDetails	= $this->_addPlanCharges($arrPlanDetails, $arrServiceIds, NULL);

			$fltMinimumCharge	= $arrUsageDetails['MinMonthly'];
			$fltUsageStart		= $arrUsageDetails['ChargeCap'];
			$fltUsageLimit		= $arrUsageDetails['UsageCap'];

			$intArrearsPeriodStart	= $arrUsageDetails['ArrearsPeriodStart'];
			$intArrearsPeriodEnd	= $arrUsageDetails['ArrearsPeriodEnd'];

			$fltCDRCappedTotal		= $arrDetails['fltTaxExemptCappedCharge'] + $arrDetails['fltTaxableCappedCharge'];
			$fltTaxableCappedCharge	= $arrDetails['fltTaxableCappedCharge'];

			// Determine and add in Plan Credit
			//Log::getLog()->log("Usage Start: {$fltUsageStart}, Capped Total: {$fltCDRCappedTotal}, Usage Limit: {$fltUsageLimit}");
			$fltPlanCredit	= min(max($fltUsageLimit, $fltMinimumCharge), max(0, $fltCDRCappedTotal)) - (max($fltUsageStart, $fltMinimumCharge) - $fltMinimumCharge);
			$intPeriodStart	= $this->intLastInvoiceDatetime;
			$intPeriodEnd	= strtotime("-1 day", $this->intInvoiceDatetime);
			$this->_addPlanCharge('PCR', $fltPlanCredit, $arrPlanDetails, $intPeriodStart, $intPeriodEnd, $objAccount->AccountGroup, $objAccount->Id);

			// HACKHACKHACK: Add inverse tax value of Plan Credit to the Tax Total, so that everything balances
			$this->Tax		+= self::calculateGlobalTaxComponent(abs($fltPlanCredit), $this->intInvoiceDatetime);

			// Determine Usage
			$fltSharedTotal			= min($fltCDRCappedTotal, $fltUsageStart);

			// Apply the Minimum Monthly
			$fltSharedTotal			= ($fltMinimumCharge > 0.0) ? max($fltMinimumCharge, $fltSharedTotal) : $fltSharedTotal;

			// Add in Taxable over-usage
			$fltTaxableOverusage	= max(0, $fltTaxableCappedCharge - $fltUsageLimit);
			$fltSharedTotal			+= $fltTaxableOverusage;

			// Add in Tax exempt over-usage
			$fltTaxExemptOverusage	= max(0, $fltCDRCappedTotal - $fltUsageLimit) - $fltTaxableOverusage;
			$fltSharedTotal			+= $fltTaxExemptOverusage;

			// Add to Invoice Totals
			//Log::getLog()->log("Shared Plan Total: \$".$fltSharedTotal);
			$this->Debits	+= $fltCDRCappedTotal;
			$this->Tax		+= self::calculateGlobalTaxComponent($fltTaxableOverusage, $this->intInvoiceDatetime);

			//----------------------------------------------------------------//
			// PLAN DATA USAGE
			$this->_addPlanDataCredit($arrPlanDetails, $arrServiceIds, $intArrearsPeriodStart, $intArrearsPeriodEnd);
			//----------------------------------------------------------------//
		}
		//--------------------------------------------------------------------//

		//----------------------- GENERATE INVOICE DATA ----------------------//
		$fltPreChargeDebitTotal		= $this->Debits;
		$fltPreChargeCreditTotal	= $this->Credits;
		$fltPreChargeTaxTotal		= $this->Tax;

		// Mark Account Adjustments
		$arrWhere	= Array('Account' => $objAccount->Id);
		$arrData	= Array('Status' => CHARGE_TEMP_INVOICE, 'invoice_run_id' => $this->invoice_run_id);
		$updMarkAccountCharges	= self::_preparedStatement('updMarkAccountCharges');
		if ($updMarkAccountCharges->Execute($arrData, $arrWhere) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$updMarkAccountCharges->Error());
		}

		// Get Preliminary Charge Totals
		$selAccountChargeTotals	= self::_preparedStatement('selAccountChargeTotals');
		if ($selAccountChargeTotals->Execute(Array('Account' => $objAccount->Id, 'invoice_run_id' => $this->invoice_run_id)) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selAccountChargeTotals->Error());
		}
		$arrAccountChargeTotals	= Array();
		while ($arrAccountChargeTotal = $selAccountChargeTotals->Fetch())
		{
			$arrAccountChargeTotals[$arrAccountChargeTotal['Nature']][$arrAccountChargeTotal['global_tax_exempt']]	= $arrAccountChargeTotal['Total'];
			//Log::getLog()->log($arrAccountChargeTotal);
		}
		//Log::getLog()->log("Preliminary Account Charges START");
		$this->Debits	+= $arrAccountChargeTotals['DR'][0] + $arrAccountChargeTotals['DR'][1];
		$this->Credits	+= $arrAccountChargeTotals['CR'][0] + $arrAccountChargeTotals['CR'][1];
		$this->Tax		+= self::calculateGlobalTaxComponent($arrAccountChargeTotals['DR'][0], $this->intInvoiceDatetime) - self::calculateGlobalTaxComponent($arrAccountChargeTotals['CR'][0], $this->intInvoiceDatetime);
		//Log::getLog()->log("Preliminary Account Charges END");
		//Log::getLog()->log($arrAccountChargeTotals);

		// Calculate Preliminary Invoice Values
		$this->AccountBalance	= $GLOBALS['fwkFramework']->GetAccountBalance($objAccount->Id);
		if ($this->AccountBalance === FALSE)
		{
			throw new Exception("Unable to calculate Account Balance for {$objAccount->Id}");
		}
		$this->Total			= ceil(($this->Debits - $this->Credits) * 100) / 100;
		$this->Balance			= $this->Total + $this->Tax;
		$this->TotalOwing		= $this->Balance + $this->AccountBalance;
		$this->DueOn			= date("Y-m-d", strtotime("+ {$objAccount->PaymentTerms} days", $this->intInvoiceDatetime));
		$this->Disputed			= 0.0;
		$this->Status			= INVOICE_TEMP;

		// Generate Account Billing Time Charges
		$arrModules	= Billing_Charge::getModules();
		foreach ($arrModules[$objAccount->CustomerGroup]['Billing_Charge_Account'] as $chgModule)
		{
			// Generate charge
			$chgModule->Generate($this, $objAccount);
		}

		// Revert to pre-Preliminary Totals
		$this->Debits	= $fltPreChargeDebitTotal;
		$this->Credits	= $fltPreChargeCreditTotal;
		$this->Tax		= $fltPreChargeTaxTotal;

		// Get Final Charge Totals
		$selAccountChargeTotals	= self::_preparedStatement('selAccountChargeTotals');
		if ($selAccountChargeTotals->Execute(Array('Account' => $objAccount->Id, 'invoice_run_id' => $this->invoice_run_id)) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selAccountChargeTotals->Error());
		}
		$arrAccountChargeTotals	= Array();
		while ($arrAccountChargeTotal = $selAccountChargeTotals->Fetch())
		{
			$arrAccountChargeTotals[$arrAccountChargeTotal['Nature']][$arrAccountChargeTotal['global_tax_exempt']]	= $arrAccountChargeTotal['Total'];
			//Log::getLog()->log($arrAccountChargeTotal);
		}
		//Log::getLog()->log("Final Account Charges START");
		$this->Debits	+= $arrAccountChargeTotals['DR'][0] + $arrAccountChargeTotals['DR'][1];
		$this->Credits	+= $arrAccountChargeTotals['CR'][0] + $arrAccountChargeTotals['CR'][1];
		$this->Tax		+= self::calculateGlobalTaxComponent($arrAccountChargeTotals['DR'][0], $this->intInvoiceDatetime) - self::calculateGlobalTaxComponent($arrAccountChargeTotals['CR'][0], $this->intInvoiceDatetime);
		//Log::getLog()->log("Final Account Charges END");

		// Recalculate Final Invoice Values
		$this->Total			= ceil(($this->Debits - $this->Credits) * 100) / 100;
		$this->Balance			= $this->Total + $this->Tax;
		$this->TotalOwing		= $this->Balance + $this->AccountBalance;

		// Determine Delivery Method
		if ($objAccount->deliver_invoice === 0)
		{
			$this->DeliveryMethod	= BILLING_METHOD_DO_NOT_SEND;
		}
		else
		{
			switch($objAccount->BillingMethod)
			{
				case BILLING_METHOD_EMAIL:
					if ($this->Balance != 0 || ($this->TotalOwing != 0 && $objAccount->Status == ACCOUNT_STATUS_ACTIVE))
					{
						$this->DeliveryMethod	= $objAccount->BillingMethod;
					}
					else
					{
						$this->DeliveryMethod	= BILLING_METHOD_DO_NOT_SEND;
					}
					break;

				default:
					if ($this->Balance >= BILLING_MINIMUM_TOTAL || $this->TotalOwing >= BILLING_MINIMUM_TOTAL)
					{
						$this->DeliveryMethod	= $objAccount->BillingMethod;
					}
					else
					{
						$this->DeliveryMethod	= BILLING_METHOD_DO_NOT_SEND;
					}
					break;
			}
		}

		// Insert the Invoice Data
		$this->save();

		// Export the Invoice
		$this->export();
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
	 * @param	array	$objAccount					Details for the Account we're Invoicing
	 * @param	array	$objInvoiceRun				Details for the Invoice Run we're generating
	 *
	 * @return	array								ServiceTotal data for this Service
	 *
	 * @method
	 */
	private function _generateService($arrServiceDetails, $objAccount, $objInvoiceRun)
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();

		$arrServiceTotal				= Array();
		$arrServiceTotal['TotalCharge']	= 0.0;
		$arrServiceTotal['Tax']			= 0.0;
		$intServiceId					= $arrServiceDetails['Id'];

		//--------------------------- PLAN CHARGES ---------------------------//
		// Retrieve Plan Details for the current Service
		$selPlanDetails	= $this->_preparedStatement('selPlanDetails');
		if ($selPlanDetails->Execute(Array('Service' => $intServiceId, 'EffectiveDate' => $this->strInvoiceDatetime)) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selPlanDetails->Error());
		}
		$arrPlanDetails	= $selPlanDetails->Fetch();

		if (!$arrPlanDetails)
		{
			// There is no Plan for this Service, we can't Invoice it
			return FALSE;
		}

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
			$arrUsageDetails	= $this->_addPlanCharges($arrPlanDetails, Array($intServiceId), $intServiceId);
			$fltMinimumCharge	= $arrUsageDetails['MinMonthly'];
			$fltUsageStart		= $arrUsageDetails['ChargeCap'];
			$fltUsageLimit		= $arrUsageDetails['UsageCap'];

			$intArrearsPeriodStart	= $arrUsageDetails['ArrearsPeriodStart'];
			$intArrearsPeriodEnd	= $arrUsageDetails['ArrearsPeriodEnd'];
		}
		//--------------------------------------------------------------------//

		//--------------------------- SERVICE TOTALS -------------------------//
		// Mark all CDRs for this Service as TEMPORARY_INVOICE
		$strSQL		= "UPDATE CDR SET Status = ".CDR_TEMP_INVOICE.", invoice_run_id = {$this->invoice_run_id} WHERE Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") AND Service IN (".implode(', ', $arrServiceDetails['Ids']).")";
		$strSQL		.= (!$this->objInvoiceRun->bolInvoiceCDRCredits) ? " AND Credit = 0" : '';
		$resResult	= $qryQuery->Execute($strSQL);
		if ($resResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Generate ServiceTypeTotals
		$strExtensionsQuery  = "INSERT INTO ServiceTypeTotal (FNN, AccountGroup, Account, Service, RecordType, Charge, Units, Records, RateGroup, Cost, invoice_run_id)";
		$strExtensionsQuery .= " SELECT CDR.FNN, CDR.AccountGroup, CDR.Account, {$intServiceId} AS Service,";
		$strExtensionsQuery .= " CDR.RecordType, SUM(CASE WHEN CDR.Credit = 1 THEN 0-CDR.Charge ELSE CDR.Charge END) AS Charge, SUM(CASE WHEN CDR.Credit = 1 THEN 0-CDR.Units ELSE CDR.Units END) AS Units, COUNT(CDR.Charge) AS Records, ServiceRateGroup.RateGroup AS RateGroup, SUM(CASE WHEN CDR.Credit = 1 THEN 0-CDR.Cost ELSE CDR.Cost END) AS Cost, {$this->invoice_run_id} AS invoice_run_id";
		$strExtensionsQuery .= " FROM CDR JOIN Service ON Service.Id = CDR.Service, ServiceRateGroup";
		$strExtensionsQuery .= " WHERE CDR.FNN IS NOT NULL AND CDR.RecordType IS NOT NULL";
		$strExtensionsQuery .= " AND CDR.invoice_run_id = {$this->invoice_run_id}";
		$strExtensionsQuery .= " AND CDR.Service IN (".implode(', ', $arrServiceDetails['Ids']).")";
		$strExtensionsQuery .= " AND ServiceRateGroup.Id = (SELECT SRG.Id FROM ServiceRateGroup SRG WHERE NOW() BETWEEN SRG.StartDatetime AND SRG.EndDatetime AND SRG.Service = CDR.Service ORDER BY CreatedOn DESC LIMIT 1) ";
		$strExtensionsQuery .= " GROUP BY CDR.FNN, CDR.RecordType";
		if ($qryQuery->Execute($strExtensionsQuery) === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Get CDR Total Details
		$strSQL			= "	SELECT SUM(CDR.Cost) AS TotalCost, SUM(CDR.Charge) AS TotalCharge, Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt
							FROM (CDR JOIN Rate ON CDR.Rate = Rate.Id) JOIN RecordType ON RecordType.Id = CDR.RecordType
							WHERE CDR.Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND CDR.invoice_run_id = {$this->invoice_run_id}
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
		$fltCDRCappedCost			= $fltTaxableCappedCost + $fltTaxExemptCappedCost;

		$fltTaxExemptUncappedCost	= $arrCDRTotals['Cost']['Uncapped']['Debit']['ExTax'] - $arrCDRTotals['Cost']['Uncapped']['Credit']['ExTax'];
		$fltTaxableUncappedCost		= $arrCDRTotals['Cost']['Uncapped']['Debit']['IncTax'] - $arrCDRTotals['Cost']['Uncapped']['Credit']['IncTax'];
		$fltCDRUncappedCost			= $fltTaxableUncappedCost + $fltTaxExemptUncappedCost;

		$arrServiceTotal['fltTaxExemptCappedCharge']	= $fltTaxExemptCappedCharge;
		$arrServiceTotal['fltTaxableCappedCharge']		= $fltTaxableCappedCharge;

		$fltTaxExemptCost	= $fltTaxExemptCappedCost + $fltTaxExemptUncappedCost;
		$fltTaxExemptCharge	= $fltTaxExemptCappedCharge + $fltTaxExemptUncappedCharge;
		if ($fltTaxExemptCost || $fltTaxExemptCharge)
		{
			//Log::getLog()->log("TAX EXEMPT CHARGES!");
			//Log::getLog()->log($arrCDRTotals);
		}

		// Calculate Service Plan Usage for non-Shared Services
		$fltTotalCharge	= 0.0;
		if (!$arrPlanDetails['Shared'])
		{
			// Determine and add in Plan Credit
			$fltPlanCredit			= min(max($fltUsageLimit, $fltMinimumCharge), max(0, $fltCDRCappedTotal)) - (max($fltUsageStart, $fltMinimumCharge) - $fltMinimumCharge);
			//Log::getLog()->log("min(max($fltUsageLimit, $fltMinimumCharge), max(0, $fltCDRCappedTotal)) - (max($fltUsageStart, $fltMinimumCharge) - $fltMinimumCharge)\t = $fltPlanCredit");
			$intPeriodStart			= $intArrearsPeriodStart;
			$intPeriodEnd			= $intArrearsPeriodEnd;
			$this->_addPlanCharge('PCR', $fltPlanCredit, $arrPlanDetails, $intPeriodStart, $intPeriodEnd, $objAccount->AccountGroup, $objAccount->Id, $intServiceId);

			// HACKHACKHACK: Add inverse tax value of Plan Credit to Service Tax Total, so that everything balances
			$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent(abs($fltPlanCredit), $this->intInvoiceDatetime);
			//Log::getLog()->log("Service Tax: \${$arrServiceTotal['Tax']} @ Line ".__LINE__);

			// Determine Usage
			$fltTotalCharge			= min($fltCDRCappedTotal, $fltUsageStart);

			// Apply the Minimum Monthly
			$fltTotalCharge			= ($fltMinimumCharge > 0.0) ? max($fltMinimumCharge, $fltTotalCharge) : $fltTotalCharge;

			// Add in Taxable over-usage
			$fltTaxableOverusage	= max(0, $fltTaxableCappedCharge - $fltUsageLimit);
			$fltTotalCharge			+= $fltTaxableOverusage;

			// Add in Tax exempt over-usage
			$fltTaxExemptOverusage	= max(0, $fltCDRCappedTotal - $fltUsageLimit) - $fltTaxableOverusage;
			$fltTotalCharge			+= $fltTaxExemptOverusage;

			if ($fltTaxExemptOverusage)
			{
				//Log::getLog()->log("Tax Exempt Overusage: \${$fltTaxExemptOverusage}");
			}
			$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($fltTaxableOverusage, $this->intInvoiceDatetime);
			//Log::getLog()->log("Service Tax: \${$arrServiceTotal['Tax']} @ Line ".__LINE__);

			$fltTotalCharge	= $fltCDRCappedTotal;

			//----------------------------------------------------------------//
			// PLAN DATA USAGE
			$this->_addPlanDataCredit($arrPlanDetails, Array($intServiceId), $intArrearsPeriodStart, $intArrearsPeriodEnd, $intServiceId);
			//----------------------------------------------------------------//
		}

		// Add in Uncapped Charges & Credits
		$fltTotalCharge			+= $fltCDRUncappedTotal;
		$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($fltTaxableUncappedCharge, $this->intInvoiceDatetime);
		//Log::getLog()->log("Service Tax: \${$arrServiceTotal['Tax']} @ Line ".__LINE__);

		// Mark all Service Charges as TEMPORARY_INVOICE
		if ($qryQuery->Execute("UPDATE Charge SET Status = ".CHARGE_TEMP_INVOICE.", invoice_run_id = {$this->invoice_run_id} WHERE Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND Service IN (".implode(', ', $arrServiceDetails['Ids']).")") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Add in Service Billing-time Charges
		$arrModules	= Billing_Charge::getModules();
		foreach ($arrModules[$objAccount->CustomerGroup]['Billing_Charge_Service'] as $chgModule)
		{
			// Generate charge
			$mixResult = $chgModule->Generate($this, new Service($arrServiceDetails, TRUE));
		}

		// Retrieve Charge Totals
		$resResult	= $qryQuery->Execute(	"SELECT Charge.Nature, Charge.global_tax_exempt, SUM(Charge.Amount) AS Total " .
											"FROM Charge " .
											"WHERE Charge.Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND Charge.invoice_run_id = {$this->invoice_run_id} " .
											"GROUP BY Charge.Nature, Charge.global_tax_exempt");
		if ($resResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$resResult->Error());
		}
		$arrChargeTotals	= Array();
		while ($arrChargeTotal = $resResult->fetch_assoc())
		{
			$arrChargeTotals[$arrChargeTotal['Nature']][($arrChargeTotal['global_tax_exempt'])	? 'ExTax' : 'IncTax']	= $arrChargeTotal['Total'];

			//$fltTotalCharge	+= ($arrChargeTotal['Nature'] === 'DR') ? $arrChargeTotal['Total'] : -$arrChargeTotal['Total'];
		}
		$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($arrChargeTotals['DR']['IncTax'], $this->intInvoiceDatetime);
		//Log::getLog()->log("Service Tax: \${$arrServiceTotal['Tax']} @ Line ".__LINE__);
		$arrServiceTotal['Tax']	-= self::calculateGlobalTaxComponent($arrChargeTotals['CR']['IncTax'], $this->intInvoiceDatetime);
		//Log::getLog()->log("Service Tax: \${$arrServiceTotal['Tax']} @ Line ".__LINE__);
		$fltServiceCredits		= $arrChargeTotals['CR']['IncTax'] + $arrChargeTotals['CR']['ExTax'];
		$fltServiceDebits		= $arrChargeTotals['DR']['IncTax'] + $arrChargeTotals['DR']['ExTax'];

		// Finalise and Insert Service Total
		$arrServiceTotal['FNN']					= $arrServiceDetails['FNN'];
		$arrServiceTotal['AccountGroup']		= $objAccount->AccountGroup;
		$arrServiceTotal['Account']				= $objAccount->Id;
		$arrServiceTotal['Service']				= $arrServiceDetails['Id'];
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
		if (($arrServiceTotal['Id'] = $insServiceTotal->Execute($arrServiceTotal)) === FALSE)
		{
			throw new Exception("DB ERROR: ".$insServiceTotal->Error());
		}

		// Link each Service to the ServiceTotal
		$insServiceTotalService	= self::_preparedStatement('insServiceTotalService');
		foreach ($arrServiceDetails['Ids'] as $intServiceId)
		{
			$arrData	= Array(
									'service_id'		=> $intServiceId,
									'service_total_id'	=> $arrServiceTotal['Id']
								);
			if ($insServiceTotalService->Execute($arrData) === FALSE)
			{
				throw new Exception("DB ERROR: ".$insServiceTotalService->Error());
			}
		}

		// Return the Service Total details
		return $arrServiceTotal;
		//--------------------------------------------------------------------//
	}

	//------------------------------------------------------------------------//
	// revokeByAccount
	//------------------------------------------------------------------------//
	/**
	 * revokeByAccount()
	 *
	 * Revokes all Temporary Invoices for a given Account
	 *
	 * Revokes all Temporary Invoices for a given Account
	 *
	 * @param		integer	$intAccount							The Account Id to revoke Invoices for
	 *
	 * @return		void
	 *
	 * @constructor
	 */
	public static function revokeByAccount($objAccount)
	{
		$selTemporaryInvoicesByAccount	= self::_preparedStatement('selTemporaryInvoicesByAccount');
		if ($selTemporaryInvoicesByAccount->Execute(Array('Account' => $objAccount->Id)) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selTemporaryInvoicesByAccount->Error());
		}

		while ($arrInvoice = $selTemporaryInvoicesByAccount->Fetch())
		{
			$objInvoice = new Invoice($arrInvoice);
			$objInvoice->revoke();
		}
	}

	//------------------------------------------------------------------------//
	// revoke
	//------------------------------------------------------------------------//
	/**
	 * revoke()
	 *
	 * Revokes a Temporary Invoice
	 *
	 * Revokes a Temporary Invoice
	 *
	 * @param		array	$arrAccount							The Account to generate the Invoice for
	 * @return		void
	 *
	 * @constructor
	 */
	public function revoke()
	{
		// Is this Invoice Temporary?
		if ($this->Status !== INVOICE_TEMP)
		{
			// No, throw an Exception
			throw new Exception("Invoice '{$this->Id}' is not a Temporary Invoice!");
		}
		else
		{
			Log::getLog()->log("(Revoking {$this->Id})");
		}

		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();

		// Change CDR Statuses back to CDR_RATED
		$updCDRRevoke	= self::_preparedStatement('updCDRRevoke');
		if ($updCDRRevoke->Execute(Array('invoice_run_id'=>NULL, 'Status'=>CDR_RATED), $this->toArray()) === FALSE)
		{
			throw new Exception("DB ERROR: ".$updCDRRevoke->Error());
		}

		// Remove Billing-Time Charges
		$objAccount	= new Account(Array('Id' => $this->Account), TRUE);
		$arrModules	= Billing_Charge::getModules();
		foreach ($arrModules as $intCustomerGroup=>$arrModuleTypes)
		{
			foreach ($arrModuleTypes as $strModuleType=>$arrModules)
			{
				foreach ($arrModules as $chgModule)
				{
					// Revoke charge
					$mixResult = $chgModule->Revoke($this, $objAccount);
				}
			}
		}

		// Remove Plan Charges
		if ($qryQuery->Execute("DELETE FROM Charge WHERE ChargeType IN ('PCAD', 'PCAR', 'PCR', 'PDCR') AND invoice_run_id = {$this->invoice_run_id} AND Account = {$this->Account}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Change Charge Statuses back to CHARGE_APPROVED
		$updChargeRevoke	= self::_preparedStatement('updChargeRevoke');
		if ($updChargeRevoke->Execute(Array('Status' => CHARGE_APPROVED, 'invoice_run_id' => NULL), $this->toArray()) === FALSE)
		{
			throw new Exception("DB ERROR: ".$updChargeRevoke->Error());
		}

		// Remove service_total_service Records
		if ($qryQuery->Execute("DELETE FROM service_total_service WHERE service_total_id = (SELECT Id FROM ServiceTotal WHERE invoice_run_id = {$this->invoice_run_id} AND Id = service_total_id)") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Remove ServiceTotal Records
		if ($qryQuery->Execute("DELETE FROM ServiceTotal WHERE invoice_run_id = {$this->invoice_run_id} AND Account = {$this->Account}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Remove ServiceTypeTotal Records
		if ($qryQuery->Execute("DELETE FROM ServiceTypeTotal WHERE invoice_run_id = {$this->invoice_run_id} AND Account = {$this->Account}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}

		// Remove Invoice Record
		if ($qryQuery->Execute("DELETE FROM Invoice WHERE Id = {$this->Id}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
	}

	//------------------------------------------------------------------------//
	// commit
	//------------------------------------------------------------------------//
	/**
	 * commit()
	 *
	 * Commits a Temporary Invoice
	 *
	 * Commits a Temporary Invoice
	 *
	 * @return		void
	 *
	 * @constructor
	 */
	public function commit()
	{
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();

		// Ensure that this is a Temporary Invoice
		if ($this->Status !== INVOICE_TEMP)
		{
			throw new Exception("Cannot commit Invoice #{$this->Id} because it is not Temporary!");
		}

		// Commit the CDRs
		$updCDRsByAccount		= self::_preparedStatement('updCDRCommit');
		if ($updCDRsByAccount->Execute(Array('Status'=>CDR_INVOICED), Array('Account'=>$this->Account, 'invoice_run_id'=>$this->invoice_run_id)) === FALSE)
		{
			throw new Exception($updCDRsByAccount->Error());
		}

		// Commit the Charges
		$updChargesByAccount	= self::_preparedStatement('updChargeCommit');
		if ($updChargesByAccount->Execute(Array('Status'=>CHARGE_INVOICED), Array('Account'=>$this->Account, 'invoice_run_id'=>$this->invoice_run_id)) === FALSE)
		{
			throw new Exception($updChargesByAccount->Error());
		}

		//------------------------------ ACCOUNT -----------------------------//
		$objAccount	= new Account(Array('Id'=>$this->Account), FALSE, TRUE);

		// Update Account.LastBilled to InvoiceRun.BillingDate
		$objAccount->LastBilled	= $this->CreatedOn;

		// Update Account.Sample
		$objAccount->Sample		= ($objAccount->Sample < 0) ? $objAccount->Sample++ : $objAccount->Sample;
		$objAccount->save();

		//------------------------------ SERVICE -----------------------------//
		// Update Service.discount_start_datetime to NULL
		$strSQL	= "UPDATE (ServiceTotal JOIN service_total_service ON ServiceTotal.Service = service_total_service.service_total_id) JOIN Service ON Service.Id = service_total_service.service_id " .
					" SET Service.discount_start_datetime = NULL, cdr_count = NULL, cdr_amount = NULL " .
					" WHERE ServiceTotal.Account = {$this->Account} AND invoice_run_id = {$this->invoice_run_id}";
		if ($qryQuery->Execute($strSQL) === FALSE)
		{
			throw new Exception($qryQuery->Error());
		}

		//------------------------------ INVOICE -----------------------------//
		// Determine Invoice Status
		$this->Status	= ($this->Balance > 0) ? INVOICE_COMMITTED : INVOICE_SETTLED;

		// Save
		$this->save();
	}

	//------------------------------------------------------------------------//
	// export
	//------------------------------------------------------------------------//
	/**
	 * export()
	 *
	 * Exports an Invoice to XML
	 *
	 * Exports an Invoice to XML.  The path used is [FILES_BASE_PATH]/invoices/xml/[Invoice_Run.Id]/[Invoice.Id].xml
	 *
	 * @return		void
	 *
	 * @constructor
	 */
	public function export()
	{
		Invoice_Export_XML::export($this->toArray());
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
	public static function prorate($fltAmount, $intChargeDate, $intPeriodStartDate, $intPeriodEndDate, $strSmallestDenomination=DATE_TRUNCATE_DAY, $bolAllowOverflow=TRUE, $intDecimalPlaces=2)
	{
		$intProratePeriod			= TruncateTime($intPeriodEndDate, $strSmallestDenomination, 'floor') - TruncateTime($intChargeDate, $strSmallestDenomination, 'floor');
		$intBillingPeriod			= TruncateTime($intPeriodEndDate, $strSmallestDenomination, 'floor') - TruncateTime($intPeriodStartDate, $strSmallestDenomination, 'floor');
		$fltProratedAmount			= ($fltAmount / $intBillingPeriod) * $intProratePeriod;
		$fltProratedAmount			= round($fltProratedAmount, $intDecimalPlaces);
		return $fltProratedAmount;
	}

	//------------------------------------------------------------------------//
	// calculateGlobalTaxComponent
	//------------------------------------------------------------------------//
	/**
	 * calculateGlobalTaxComponent()
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
	public static function calculateGlobalTaxComponent($fltAmount, $strEffectiveDate)
	{
		static	$arrGlobalTax;
		if (!isset($arrGlobalTax))
		{
			$qryQuery	= new Query();
			$resResult	= $qryQuery->Execute("SELECT * FROM tax_type WHERE global = 1 AND '{$strEffectiveDate}' BETWEEN start_datetime AND end_datetime");
			if ($resResult->num_rows)
			{
				$arrGlobalTax	= $resResult->fetch_assoc();
			}
			elseif ($qryQuery->Error())
			{
				throw new Exception("DB ERROR: ".$qryQuery->Error());
			}
			else
			{
				$arrGlobalTax	= Array('rate_percentage' => 0.0);
			}
		}

		return $fltAmount * $arrGlobalTax['rate_percentage'];
	}



	//------------------------------------------------------------------------//
	// _addPlanCharges
	//------------------------------------------------------------------------//
	/**
	 * _addPlanCharges()
	 *
	 * Adds Plan Charges for a given Service, or group of Shared Services
	 *
	 * Adds Plan Charges for a given Service, or group of Shared Services
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
	private function _addPlanCharges($arrPlanDetails, $arrServiceIds, $intPrimaryService=NULL)
	{
		static	$qryQuery;
		$qryQuery		= (isset($qryQuery)) ? $qryQuery : new Query();
		$strServiceIds	= implode(', ', $arrServiceIds);

		// Get Earliest CDR Details (ensuring that the earliest date is not after the Invoice date)
		$resResult	= $qryQuery->Execute("SELECT MIN(EarliestCDR) AS EarliestCDR FROM Service WHERE Id IN ({$strServiceIds})");
		if ($resResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		$arrMinEarliestCDR	= $resResult->fetch_assoc();
		$strEarliestCDR		= ($arrMinEarliestCDR['EarliestCDR'] !== NULL && strtotime($arrMinEarliestCDR['EarliestCDR']) < $this->intInvoiceDatetime) ? $arrMinEarliestCDR['EarliestCDR'] : NULL;
		
		// Default Arrears Period
		$intArrearsPeriodStart	= $this->intLastInvoiceDatetime;
		$intArrearsPeriodEnd	= strtotime("-1 day", $this->intInvoiceDatetime);
		
		// Is the Service tolling?
		$intLevel	= 0;
		if ($strEarliestCDR)
		{
			$fltMinimumCharge	= (float)$arrPlanDetails['MinMonthly'];
			$fltUsageStart		= (float)$arrPlanDetails['ChargeCap'];
			$fltUsageLimit		= (float)$arrPlanDetails['UsageCap'];

			$arrPlanChargeSteps	= Array();

			// Yes -- Does this Service have any Invoiced CDRs?
			$resResult	= $qryQuery->Execute("SELECT Id FROM ServiceTypeTotal WHERE Service IN ({$strServiceIds}) AND Records > 0");
			if ($resResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$qryQuery->Error());
			}
			elseif (!$resResult->num_rows)
			{
				$bolFirstInvoice	= true;
				$arrPlanChargeSteps[]	= 'FIRST INVOICE';
				
				// Alternate Arrears Period
				$intArrearsPeriodStart	= strtotime($strEarliestCDR);
			}
			else
			{
				$bolFirstInvoice	= false;
			}

			// Charge In Advance (only if this is not an interim Invoice Run)
			if ($arrPlanDetails['InAdvance'] && !in_array($this->_objInvoiceRun->invoice_run_type_id, array(INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM)))
			{
				$arrPlanChargeSteps[]	= ($bolFirstInvoice) ? 'FIRST_ADVANCE' : 'NORMAL_ADVANCE';

				$strChargeType	= 'PCAD';
				$intPeriodStart	= $this->intInvoiceDatetime;
				$intPeriodEnd	= strtotime("-1 day", strtotime("+1 month", $this->intInvoiceDatetime));
				$this->_addPlanCharge($strChargeType, $fltMinimumCharge, $arrPlanDetails, $intPeriodStart, $intPeriodEnd, $this->_objAccount->AccountGroup, $this->_objAccount->Id, $intPrimaryService);
			}
			
			// Charge in Arrears
			if (!$arrPlanDetails['InAdvance'] || $bolFirstInvoice)
			{
				$arrPlanChargeSteps[]	= ($bolFirstInvoice) ? 'FIRST_ARREARS' : 'NORMAL_ARREARS';

				// Prorate the Charges and Usage details in Arrears
				$fltMinimumCharge	= Invoice::prorate($fltMinimumCharge	, $intArrearsPeriodStart, $this->intLastInvoiceDatetime, $intArrearsPeriodEnd);
				$fltUsageStart		= Invoice::prorate($fltUsageStart		, $intArrearsPeriodStart, $this->intLastInvoiceDatetime, $intArrearsPeriodEnd);
				$fltUsageLimit		= Invoice::prorate($fltUsageLimit		, $intArrearsPeriodStart, $this->intLastInvoiceDatetime, $intArrearsPeriodEnd);
				
				$strChargeType	= 'PCAR';
				$intPeriodStart	= $intArrearsPeriodStart;
				$intPeriodEnd	= $intArrearsPeriodEnd;
				$this->_addPlanCharge($strChargeType, $fltMinimumCharge, $arrPlanDetails, $intPeriodStart, $intPeriodEnd, $this->_objAccount->AccountGroup, $this->_objAccount->Id, $intPrimaryService);
			}
		}
		else
		{
			$arrPlanChargeSteps[]	= 'NEVER_TOLLED';

			// No -- ignore all Plan Charges, because we haven't tolled yet
			$fltMinimumCharge	= 0.0;
			$fltUsageStart		= 0.0;
			$fltUsageLimit		= 0.0;
		}
		// DEBUG
		//Log::getLog()->log($arrPlanChargeSteps);

		// Return usage data
		return Array(
						'MinMonthly'			=> $fltMinimumCharge,
						'ChargeCap'				=> $fltUsageStart,
						'UsageCap'				=> $fltUsageLimit,

						'ArrearsPeriodStart'	=> $intArrearsPeriodStart,
						'ArrearsPeriodEnd'		=> $intArrearsPeriodEnd
					);
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
	 * @param	array	$arrPlanDetails							Plan Details
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
	private function _addPlanCharge($strChargeType, $fltAmount, $arrPlanDetails, $intPeriodStartDate, $intPeriodEndDate, $intAccountGroup, $intAccount, $intService=NULL)
	{
		static	$selChargeType;
		static	$arrChargeTypes;

		// If the Plan Charge is $0, then don't insert it
		if (round($fltAmount, 2) === 0.0)
		{
			//CliEcho("Ignoring {$strPlanName} {$strChargeType}");
			return TRUE;
		}
		//CliEcho("Adding {$strPlanName} {$strChargeType} worth \${$fltAmount}");

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
				throw new Exception("DB ERROR: ".$selChargeType->Error());
			}
			else
			{
				throw new Exception("Unable to retrieve details for ChargeType '{$strChargeType}'!");
			}
		}

		// If this is a Plan Data Credit, include the allowance in the Description
		$strDescription	= $arrChargeTypes[$strChargeType]['Description'];
		if ($arrPlanDetails['included_data'] > 0 && $strChargeType == 'PDCR')
		{
			$intMegaBytes	= $arrPlanDetails['included_data'] / 1024;
			$strDescription	.= " ({$intMegaBytes}MB)";
		}

		// Generate Charge
		$arrPlanCharge						= Array();
		$arrPlanCharge['AccountGroup']		= $intAccountGroup;
		$arrPlanCharge['Account']			= $intAccount;
		$arrPlanCharge['Service']			= $intService;
		$arrPlanCharge['Nature']			= $arrChargeTypes[$strChargeType]['Nature'];
		$arrPlanCharge['ChargeType']		= $strChargeType;
		$arrPlanCharge['charge_type_id']	= $arrChargeTypes[$strChargeType]['Id'];
		$arrPlanCharge['global_tax_exempt']	= 0;
		$arrPlanCharge['Description']		= "{$arrPlanDetails['Name']} ".$strDescription." from ".date("d/m/Y", $intPeriodStartDate)." to ".date("d/m/Y", $intPeriodEndDate);
		$arrPlanCharge['ChargedOn']			= date("Y-m-d");
		$arrPlanCharge['Amount']			= abs($fltAmount);
		if (!$GLOBALS['fwkFramework']->AddCharge($arrPlanCharge))
		{
			throw new Exception("Unable to create '{$arrPlanCharge['Description']}' for {$intAccount}::{$intService}!");
		}
		return TRUE;
	}

	/**
	 * _addPlanDataCredit()
	 *
	 * Adds a Plan Credit for Included Data
	 *
	 * @param	array		$arrPlanDetails
	 * @param	array		$arrServiceIds
	 * @param	integer		$intArrearsPeriodStart
	 * @param	integer		$intArrearsPeriodEnd
	 * @param	integer		$intPrimaryService
	 *
	 * @return	boolean
	 *
	 * @method
	 */
	private function _addPlanDataCredit($arrPlanDetails, $arrServiceIds, $intArrearsPeriodStart, $intArrearsPeriodEnd, $intPrimaryService=NULL)
	{
		static	$qryQuery;
		$qryQuery	= ($qryQuery) ? $qryQuery : new Query();

		$strServices	= implode(', ', $arrServiceIds);

		// If there is Included Data on this Plan...
		$intIncludedData	= $arrPlanDetails['included_data'];
		if ($intIncludedData > 0)
		{
			// Get all CDRs which are on Uncapped Data Rates
			$sqlIncludedData	=	"SELECT CDR.Units, CDR.Charge , CDR.Credit " .
									"FROM CDR JOIN Rate ON Rate.Id = CDR.Rate JOIN RecordType ON RecordType.Id = CDR.RecordType " .
									"WHERE CDR.Service IN ({$strServices}) AND CDR.invoice_run_id = {$this->invoice_run_id} AND Rate.Uncapped = 1 AND RecordType.DisplayType = ".RECORD_DISPLAY_DATA." " .
									"ORDER BY CDR.StartDatetime ";
			$resResult			= $qryQuery->Execute($sqlIncludedData);
			if ($resResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$qryQuery->Error());
			}

			// If there are any CDRs
			if ($resResult->num_rows)
			{
				$intTotalUnits		= 0;
				$fltTotalCredit		= 0.0;
				$intAvailableUnits	= self::prorate($intIncludedData, $intArrearsPeriodStart, $this->intLastInvoiceDatetime, $this->intInvoiceDatetime, DATE_TRUNCATE_DAY, TRUE, 0);
				while (($intAvailableUnits > 0.0) && ($arrDataCDR = $resResult->fetch_assoc()))
				{
					// If we haven't gone over our Data Cap yet
					if ($intAvailableUnits > 0.0)
					{
						$arrDataCDR['Units']	= ($arrDataCDR['Credit']) ? 0-$arrDataCDR['Units'] : $arrDataCDR['Units'];
						$arrDataCDR['Charge']	= ($arrDataCDR['Charge']) ? 0-$arrDataCDR['Charge'] : $arrDataCDR['Charge'];
						
						$intAvailableUnits	-= $arrDataCDR['Units'];
						$fltCharge			= $arrDataCDR['Charge'];
						if ($intAvailableUnits < 0)
						{
							// Prorate the last session (assumes a consistent rate per unit [KB])
							$fltRatePerKB	= ($arrDataCDR['Units']) ? ($fltCharge / $arrDataCDR['Units']) : 0;
							$fltCharge		-= (abs(intAvailableUnits) * $fltRatePerKB);
						}

						$fltTotalCredit	+= $fltCharge;
					}
					$intTotalUnits	+= $arrDataCDR['Units'];
				}
			}

			// Add the Credit
			$this->_addPlanCharge('PDCR', $fltTotalCredit, $arrPlanDetails, $intArrearsPeriodStart, $intArrearsPeriodEnd, $this->AccountGroup, $this->Account, $intPrimaryService);
		}
	}

	protected function __set($strName, $mxdValue)
	{
		parent::__set($strName, $mxdValue);

		if (array_key_exists($strName, $this->_arrProperties))
		{
			// DEBUG
			switch ($strName)
			{
				case 'Tax':
				case 'Total':
				case 'Debits':
				case 'Credits':
					break;
					//Log::getLog()->log("*** {$strName} updated to \${$mxdValue}");
					$arrBacktrace	= debug_backtrace();
					//Log::getLog()->log("*** Total: {$this->Total}; Tax: {$this->Tax}; Debits: {$this->Debits}; Credits: {$this->Credits};\t{$strName} @ Line {$arrBacktrace[0]['line']}");

					// Is Tax proportionate to Total?
					$fltCalculatedTax	= $this->Total / 10;
					$fltDifference		= $this->Tax - $fltCalculatedTax;
					if ($fltCalculatedTax == $this->Tax)
					{
						Log::getLog()->log("*** Tax (\${$this->Tax}) is extactly Total/10");
					}
					elseif ($fltDifference > -0.01 && $fltDifference < 0.01)
					{
						Log::getLog()->log("*** Tax (\${$this->Tax}) is nearly Total/10 ({$fltCalculatedTax})");
					}
					else
					{
						Log::getLog()->log("*** Tax (\${$this->Tax}) is significantly different to Total/10 ({$fltCalculatedTax})");
					}
					break;
			}
		}
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
	protected static function _preparedStatement($strStatement)
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
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Invoice", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selInvoiceableFNNs':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Service JOIN service_status ON Service.Status = service_status.id",
																					"Service.Id, FNN, Indial100",
																					"Account = <Account> AND CreatedOn < <InvoiceDatetime> AND service_status.can_invoice = 1");
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Service JOIN service_status ON Service.Status = service_status.id",
																					"Service.*",
																					"Service.Account = <Account> AND (Service.FNN = <FNN> OR (Service.FNN LIKE <IndialRange> AND Service.Indial100 = 1)) AND Service.CreatedOn <= <DateTime> AND service_status.can_invoice = 1",
																					"Service.Id DESC",
																					"1");
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
				case 'selTemporaryInvoicesByAccount':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Invoice JOIN InvoiceRun ON InvoiceRun.Id = Invoice.invoice_run_id", "Invoice.*", "Account = <Account> AND invoice_run_status_id = ".INVOICE_RUN_STATUS_TEMPORARY);
					break;
				case 'selAccountChargeTotals':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Charge", "Nature, global_tax_exempt, SUM(Amount) AS Total", "Service IS NULL AND Account = <Account> AND Status = ".CHARGE_TEMP_INVOICE, NULL, NULL, "Nature, global_tax_exempt");
					break;
				case 'selLastInvoiceDatetime':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Invoice JOIN InvoiceRun ON Invoice.invoice_run_id = InvoiceRun.Id", "InvoiceRun.BillingDate", "Invoice.Account = <Account>", "InvoiceRun.BillingDate DESC, InvoiceRun.Id DESC", 1);
					break;
				

				// INSERTS
				case 'insServiceTotal':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("ServiceTotal");
					break;
				case 'insServiceTotalService':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("service_total_service");
					break;
				case 'insSelf':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("Invoice");
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement]	= new StatementUpdateById("Invoice");
					break;

				// UPDATES
				case 'updMarkAccountCharges':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Charge", "Account = <Account> AND Service IS NULL AND Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.")", Array('Status'=>NULL, 'invoice_run_id'=>NULL));
					break;
				case 'updCDRRevoke':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("CDR", "Account = <Account> AND (invoice_run_id = <invoice_run_id> OR Status = ".CDR_TEMP_INVOICE.")", Array('invoice_run_id'=>NULL, 'Status'=>CDR_RATED));
					break;
				case 'updChargeRevoke':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Charge", "Account = <Account> AND (invoice_run_id = <invoice_run_id> OR Status = ".CHARGE_TEMP_INVOICE.")", Array('invoice_run_id'=>NULL, 'Status'=>CHARGE_APPROVED));
					break;
				case 'updInvoiceStatus':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Invoice", "Account = <Account> AND invoice_run_id = <invoice_run_id>", Array('Status'=>NULL, 'SettledOn'=>NULL));
					break;
				case 'updCDRCommit':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("CDR", "Account = <Account> AND invoice_run_id = <invoice_run_id>", Array('Status'=>CDR_INVOICED));
					break;
				case 'updChargeCommit':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Charge", "Account = <Account> AND invoice_run_id = <invoice_run_id>", Array('Status'=>CHARGE_INVOICED));
					break;

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>