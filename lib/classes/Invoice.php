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
	private	$_arrTidyNames	= array();
	private	$_arrProperties	= array();
	
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
			$this->_arrProperties[$strName]					= NULL;
			$this->_arrTidyNames[self::tidyName($strName)]	= $strName;
		}
		$this->_arrProperties[$arrTableDefine['Id']]				= NULL;
		$this->_arrTidyNames[self::tidyName($arrTableDefine['Id'])]	= $arrTableDefine['Id'];
		
		// Automatically load the Invoice using the passed Id
		$intId	= ($arrProperties['Id']) ? $arrProperties['Id'] : ($arrProperties['id']) ? $arrProperties['id'] : NULL;
		if ($bolLoadById && $intId)
		{
			$selById	= $this->_preparedStatement('selById');
			if ($selById->Execute(Array('Id' => $intId)))
			{
				$arrProperties	= $selById->Fetch();
			}
			elseif ($selById->Error())
			{
				throw new Exception("DB ERROR: ".$selById->Error());
			}
			else
			{
				// Do we want to Debug something?
			}
		}
		
		// Set Properties
		if (is_array($arrProperties))
		{
			foreach ($arrProperties as $strName=>$mixValue)
			{
				// Load from the Database
				$this->{$strName}	= $mixValue;
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
		Cli_App_Billing::debug("\t* Revoking any existing Invoices for Account with Id {$objAccount->Id}...");
		self::revokeByAccount($objAccount);
		
		//----------------- INVOICEABLE SERVICE PREPROCESSING ----------------//
		$this->invoice_run_id	= $objInvoiceRun->Id;
		$this->Total			= 0.0;
		$this->Debits			= 0.0;
		$this->Credits			= 0.0;
		$this->Tax				= 0.0;
		
		// Retrieve a list of Invoiceable FNNs for this Account
		Cli_App_Billing::debug("\t * Getting list of Invoiceable FNNs...");
		$selInvoiceableFNNs	= $this->_preparedStatement('selInvoiceableFNNs');
		if ($selInvoiceableFNNs->Execute($objAccount->toArray()) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selInvoiceableFNNs->Error());
		}
		
		// Process each Invoiceable FNN
		$arrServices	= Array();
		while ($arrFNN = $selInvoiceableFNNs->Fetch())
		{
			Cli_App_Billing::debug("\t\t * Getting details for FNN {$arrFNN['FNN']}...");
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
			Cli_App_Billing::debug("\t + Generating Service Total Data for Service with Id {$intServiceId}...");
			$arrServiceDetails['ServiceTotal']	= $this->_generateService($arrServiceDetails, $objAccount, $objInvoiceRun);
			$this->Debits						+= $arrServiceDetails['ServiceTotal']['TotalCharge'];
			$this->Tax							+= $arrServiceDetails['ServiceTotal']['Tax'];
			
			// Is this a Shared Plan?
			if ($arrServiceDetails['ServiceTotal']['Shared'])
			{
				Cli_App_Billing::debug("\t\t ! Service is on Shared Plan {$arrServiceDetails['ServiceTotal']['RatePlan']}...");
				$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['Services'][$intServiceId]		= &$arrServiceDetails;
				$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxExemptCappedCharge']		+= $arrServiceDetails['ServiceTotal']['fltTaxExemptCappedCharge'];
				$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxableCappedCharge']		+= $arrServiceDetails['ServiceTotal']['fltTaxableCappedCharge'];
			}
		}
		
		// Calculate and Add in Shared Plan Charges and Credits as Account Charges
		Cli_App_Billing::debug("\t * Generating Shared Plan Charges...");
		foreach ($arrSharedPlans as $intRatePlan=>$arrDetails)
		{
			// Get Shared Plan Details
			Cli_App_Billing::debug("\t\t + Rate Plan {$intRatePlan}...");
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
			
			$arrServiceIds		= array_keys($arrDetails['Services']);			
			
			// Add Plan Charges
			$arrUsageDetails	= $this->_addPlanCharges($arrPlanDetails, $arrServiceIds, TRUE);
			
			$fltMinimumCharge	= $arrUsageDetails['MinMonthly'];
			$fltUsageStart		= $arrUsageDetails['ChargeCap'];
			$fltUsageLimit		= $arrUsageDetails['UsageCap'];
			
			$fltCDRCappedTotal		= $arrDetails['fltTaxExemptCappedCharge'] + $arrDetails['fltTaxableCappedCharge'];
			$fltTaxableCappedCharge	= $arrDetails['fltTaxableCappedCharge'];
			
			// Determine and add in Plan Credit
			$fltPlanCredit	= min(0, $fltUsageStart - min($fltCDRCappedTotal, $fltUsageLimit));
			$intPeriodStart	= $objInvoiceRun->intLastInvoiceDatetime;
			$intPeriodEnd	= strtotime("-1 day", $objInvoiceRun->intInvoiceDatetime);
			$this->_addPlanCharge('PCR', $fltPlanCredit, $arrPlanDetails['Name'], $intPeriodStart, $intPeriodEnd, $objAccount->AccountGroup, $objAccount->Id);
			
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
			$this->Debits	+= $fltSharedTotal;
			$this->Tax		+= self::calculateGlobalTaxComponent($fltTaxableOverusage);
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
		if ($selAccountChargeTotals->Execute($arrData, $arrWhere) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selAccountChargeTotals->Error());
		}
		$arrAccountChargeTotals	= Array();
		while ($arrAccountChargeTotal = $selAccountChargeTotals->Fetch())
		{
			$arrAccountChargeTotals[$arrAccountChargeTotal['Nature']]	= $arrAccountChargeTotal['Total'];
			
			$this->Tax	+= ($arrAccountChargeTotal['global_tax_exempt']) ? 0.0 : self::calculateGlobalTaxComponent($arrAccountChargeTotal['Total'], $objInvoiceRun->intInvoiceDatetime);
		}
		$this->Debits	+= $arrAccountChargeTotals['DR'];
		$this->Credits	+= $arrAccountChargeTotals['CR'];
		
		// Calculate Preliminary Invoice Values
		$this->AccountBalance	= $this->Framework->GetAccountBalance($objAccount->Id);
		if ($this->AccountBalance === FALSE)
		{
			throw new Exception("Unable to calculate Account Balance for {$objAccount->Id}");
		}
		$this->Total			= ceil(($this->Debits - $this->Credits) * 100) / 100;
		$this->Balance			= $this->Total + $this->Tax;
		$this->TotalOwing		= $this->Balance + $this->AccountBalance;
		$this->AccountGroup		= $objAccount->AccountGroup;
		$this->Account			= $objAccount->Id;
		$this->CreatedOn		= $objInvoiceRun->BillingDate;
		$this->DueOn			= date("Y-m-d", strtotime("+ {$objAccount->PaymentTerms} days", strtotime($objInvoiceRun->BillingDate)));
		$this->Disputed			= 0.0;
		$this->Status			= INVOICE_TEMP;
		
		// Generate Account Billing Time Charges
		$arrModules	= Billing_Charge::getModules();
		foreach ($arrModules[$objAccount->CustomerGroup]['Billing_Charge_Account'] as $chgModule)
		{
			// Generate charge
			$mixResult = $chgModule->Generate($this, $objAccount);
		}
		
		// Revert to pre-Preliminary Totals
		$this->Debits	= $fltPreChargeDebitTotal;
		$this->Credits	= $fltPreChargeCreditTotal;
		$this->Tax		= $fltPreChargeTaxTotal;
		
		// Get Final Charge Totals
		$selAccountChargeTotals	= self::_preparedStatement('selAccountChargeTotals');
		if ($selAccountChargeTotals->Execute($arrData, $arrWhere) === FALSE)
		{
			// Database Error -- throw Exception
			throw new Exception("DB ERROR: ".$selAccountChargeTotals->Error());
		}
		$arrAccountChargeTotals	= Array();
		while ($arrAccountChargeTotal = $selAccountChargeTotals->Fetch())
		{
			$arrAccountChargeTotals[$arrAccountChargeTotal['Nature']]	= $arrAccountChargeTotal['Total'];
			
			$this->Tax	+= ($arrAccountChargeTotal['global_tax_exempt']) ? 0.0 : self::calculateGlobalTaxComponent($arrAccountChargeTotal['Total'], $objInvoiceRun->intInvoiceDatetime);
		}
		$this->Debits	+= $arrAccountChargeTotals['DR'];
		$this->Credits	+= $arrAccountChargeTotals['CR'];
		
		// Recalculate Final Invoice Values
		$this->Total			= ceil(($this->Debits - $this->Credits) * 100) / 100;
		$this->Balance			= $this->Total + $this->Tax;
		$this->TotalOwing		= $this->Balance + $this->AccountBalance;
		
		// Determine Delivery Method
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
		
		$arrServiceTotal	= Array();
		$intServiceId		= $arrServiceDetails['Id'];
		
		//--------------------------- PLAN CHARGES ---------------------------//
		// Retrieve Plan Details for the current Service
		$selPlanDetails	= $this->_preparedStatement('selPlanDetails');
		if ($selPlanDetails->Execute(Array('Service' => $intServiceId, 'EffectiveDate' => $objInvoiceRun->intInvoiceDatetime)) === FALSE)
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
			$arrUsageDetails	= $this->_addPlanCharges($arrPlanDetails, Array($intServiceId), FALSE);
			$fltMinimumCharge	= $arrUsageDetails['MinMonthly'];
			$fltUsageStart		= $arrUsageDetails['ChargeCap'];
			$fltUsageLimit		= $arrUsageDetails['UsageCap'];
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
		$strExtensionsQuery  = "INSERT INTO ServiceTypeTotal (FNN, AccountGroup, Account, Service, RecordType, Charge, Units, Records, RateGroup, Cost, invoice_run_id)";
		$strExtensionsQuery .= " SELECT CDR.FNN, CDR.AccountGroup, CDR.Account, CDR.Service,";
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
			$intPeriodStart	= $objInvoiceRun->intLastInvoiceDatetime;
			$intPeriodEnd	= strtotime("-1 day", $objInvoiceRun->intInvoiceDatetime);
			$this->_addPlanCharge('PCR', $fltPlanCredit, $arrPlanDetails['Name'], $intPeriodStart, $intPeriodEnd, $objAccount->AccountGroup, $objAccount->Id, $intServiceId);			
			
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
			
			$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($fltTaxableOverusage);
		}
		
		// Add in Uncapped Charges & Credits
		$fltTotalCharge			+= $fltCDRUncappedTotal;
		$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($fltCDRUncappedTotal);
		
		// Mark all Service Charges as TEMPORARY_INVOICE
		if ($qryQuery->Execute("UPDATE Charge SET Status = ".CHARGE_TEMP_INVOICE.", invoice_run_id = {$objInvoiceRun->Id} WHERE Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND Service IN (".implode(', ', $arrServiceDetails['Ids']).")") === FALSE)
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
			
			$fltTotalCharge	+= ($arrChargeTotal['Nature'] === 'DR') ? $arrChargeTotal['Total'] : -$arrChargeTotal['Total'];
		}
		$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($arrChargeTotals['DR']['IncTax']);
		$arrServiceTotal['Tax']	-= self::calculateGlobalTaxComponent($arrChargeTotals['CR']['IncTax']);
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
		if ($insServiceTotal->Execute($arrServiceTotal) === FALSE)
		{
			throw new Exception("DB ERROR: ".$insServiceTotal->Error());
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
		if ($qryQuery->Execute("DELETE FROM Charge WHERE ChargeType IN ('PCAD', 'PCAR', 'PCR') AND invoice_run_id = {$this->invoice_run_id} AND Account = {$this->Account}") === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		
		// Change Charge Statuses back to CHARGE_APPROVED
		$updChargeRevoke	= self::_preparedStatement('updChargeRevoke');
		if ($updChargeRevoke->Execute(Array('Status' => CHARGE_APPROVED, 'invoice_run_id' => NULL), $this->toArray()) === FALSE)
		{
			throw new Exception("DB ERROR: ".$updChargeRevoke->Error());
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
		// Ensure that this is a Temporary Invoice
		if ($this->invoice_run_status_id !== INVOICE_RUN_STATUS_TEMPORARY)
		{
			throw new Exception("Cannot commit Invoice #{$this->Id} because it is not Temporary!");
		}
		
		
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
	public static function prorate($fltAmount, $intChargeDate, $intPeriodStartDate, $intPeriodEndDate, $strSmallestDenomination=DATE_TRUNCATE_DAY, $bolAllowOverflow=TRUE)
	{
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
			$resResult	= $qryQuery->Execute("SELECT * FROM tax_type WHERE global = 1 AND <EffectiveDate> BETWEEN start_datetime AND end_datetime"); 
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
	private function _addPlanCharges($arrPlanDetails, $arrServiceIds, $bolShared)
	{
		static	$qryQuery;
		$qryQuery		= (isset($qryQuery)) ? $qryQuery : new Query();
		$strServiceIds	= implode(', ', $arrServiceIds);
		
		// Get Earliest CDR Details
		$resResult	= $qryQuery->Execute("SELECT MIN(EarliestCDR) AS EarliestCDR FROM Service WHERE Id IN ({$strServiceIds})"); 
		if ($resResult === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		$arrMinEarliestCDR	= $resResult->fetch_assoc();
		$strEarliestCDR		= $arrMinEarliestCDR['EarliestCDR'];
		
		// Is the Service tolling?
		if ($strEarliestCDR)
		{
			$fltMinimumCharge	= (float)$arrPlanDetails['MinMonthly'];
			$fltUsageStart		= (float)$arrPlanDetails['ChargeCap'];
			$fltUsageLimit		= (float)$arrPlanDetails['UsageCap'];
			
			// Yes -- Does this Service have any Invoiced CDRs?
			$resResult	= $qryQuery->Execute("SELECT Id FROM ServiceTypeTotal WHERE Service IN ({$strServiceIds}) AND Records > 0"); 
			if ($resResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$qryQuery->Error());
			}
			elseif (!$resResult)
			{
				// No -- Is this on a Charge-in-Advance Plan?
				if ($arrPlanDetails['InAdvance'])
				{
					$resResult	= $qryQuery->Execute("SELECT COUNT(CASE WHEN RatePlan = {$arrPlanDetails['Id']} THEN Id ELSE NULL END) AS SamePlan FROM ServiceTotal WHERE Service IN ({$strServiceIds}) GROUP BY invoice_run_id ORDER BY invoice_run_id DESC LIMIT 1"); 
					if ($resResult !== FALSE)
					{
						$arrPlanInvoicedBefore	= $resResult->fetch_assoc();
						if ($arrPlanInvoicedBefore['SamePlan'] === 0)
						{
							// The this Plan has not been invoiced before, so generate a Charge in Advance
							$intAdvancePeriodStart	= $objInvoiceRun->intInvoiceDatetime;
							$intAdvancePeriodEnd	= strtotime("-1 day", strtotime("+1 month", $objInvoiceRun->intInvoiceDatetime));
							$this->_addPlanCharge('PCAD', $fltMinimumCharge, $arrPlanDetails['Name'], $intAdvancePeriodStart, $intAdvancePeriodEnd, $objAccount->AccountGroup, $objAccount->Id, ($bolShared) ? NULL : $arrServiceIds[0]);
						}
					}
					else
					{
						throw new Exception("DB ERROR: ".$selLastPlanInvoiced->Error());
					}
				}
				
				// Prorate the Charges and Usage details in Arrears
				$fltMinimumCharge	= Invoice::prorate($fltMinimumCharge	, strtotime($strEarliestCDR), $objInvoiceRun->intLastInvoiceDatetime, $objInvoiceRun->intInvoiceDatetime);
				$fltUsageStart		= Invoice::prorate($fltUsageStart		, strtotime($strEarliestCDR), $objInvoiceRun->intLastInvoiceDatetime, $objInvoiceRun->intInvoiceDatetime);
				$fltUsageLimit		= Invoice::prorate($fltUsageLimit		, strtotime($strEarliestCDR), $objInvoiceRun->intLastInvoiceDatetime, $objInvoiceRun->intInvoiceDatetime);
				
				$strChargeType	= 'PCAR';
				$intPeriodStart	= strtotime($strEarliestCDR);
				$intPeriodEnd	= strtotime("-1 day", $objInvoiceRun->intInvoiceDatetime);
				$this->_addPlanCharge('PCAR', $fltMinimumCharge, $arrPlanDetails['Name'], $objInvoiceRun->intLastInvoiceDatetime, strtotime("-1 day", $objInvoiceRun->intLastInvoiceDatetime), $objAccount->AccountGroup, $objAccount->Id, ($bolShared) ? NULL : $arrServiceIds[0]);
			}
			else
			{
				// Charge the Standard Plan Charge
				if ($arrPlanDetails['InAdvance'])
				{
					$strChargeType	= 'PCAD';
					$intPeriodStart	= $objInvoiceRun->intInvoiceDatetime;
					$intPeriodEnd	= strtotime("-1 day", strtotime("+1 month", $objInvoiceRun->intInvoiceDatetime));
				}
				else
				{
					$strChargeType	= 'PCAR';
					$intPeriodStart	= $objInvoiceRun->intLastInvoiceDatetime;
					$intPeriodEnd	= strtotime("-1 day", $objInvoiceRun->intInvoiceDatetime);
				}
				$this->_addPlanCharge($strChargeType, $fltMinimumCharge, $arrPlanDetails['Name'], $intPeriodStart, $intPeriodEnd, $objAccount->AccountGroup, $objAccount->Id, ($bolShared) ? NULL : $arrServiceIds[0]);
			}
		}
		else
		{
			// No -- ignore all Plan Charges, because we haven't tolled yet
			$fltMinimumCharge	= 0.0;
			$fltUsageStart		= 0.0;
			$fltUsageLimit		= 0.0;
		}
		
		// Return usage data
		return Array(
						'MinMonthly'			=> $fltMinimumCharge,
						'ChargeCap'				=> $fltUsageStart,
						'UsageCap'				=> $fltUsageLimit,
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
		$arrPlanCharge['Nature']			= $arrChargeTypes[$strChargeType]['Nature'];
		$arrPlanCharge['ChargeType']		= $strChargeType;
		$arrPlanCharge['charge_type_id']	= $arrChargeTypes[$strChargeType]['Id'];
		$arrPlanCharge['global_tax_exempt']	= 0;
		$arrPlanCharge['Description']		= "{$strPlanName} ".$arrChargeTypes[$strChargeType]['Description']." from ".date("d/m/Y", $intPeriodStartDate)." to ".date("d/m/Y", $intPeriodEndDate);
		$arrPlanCharge['ChargedOn']			= date("Y-m-d");
		$arrPlanCharge['Amount']			= abs($fltAmount);
		if (!$this->Framework->AddCharge($arrPlanCharge))
		{
			throw new Exception("Unable to create '{$arrPlanCharge['Description']}' for {$intAccount}::{$intService}!");
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// save
	//------------------------------------------------------------------------//
	/**
	 * save()
	 *
	 * Inserts or Updates the Invoice Record for this instance
	 *
	 * Inserts or Updates the Invoice Record for this instance
	 * 
	 * @return	boolean							Pass/Fail
	 *
	 * @method
	 */
	public function save()
	{
		// Do we have an Id for this instance?
		if ($this->Id !== NULL)
		{
			// Update
			$ubiSelf	= self::_preparedStatement("ubiSelf");
			if ($ubiSelf->Execute($this->toArray()) === FALSE)
			{
				throw new Exception("DB ERROR: ".$ubiSelf->Error());
			}
			return TRUE;
		}
		else
		{
			// Insert
			$insSelf	= self::_preparedStatement("insSelf");
			$mixResult	= $insSelf->Execute($this->toArray());
			if ($mixResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$insSelf->Error());
			}
			if (is_int($mixResult))
			{
				$this->Id	= $mixResult;
				return TRUE;
			}
			else
			{
				return $mixResult;
			}
		}
	}

	public function __get($strName)
	{
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		return (array_key_exists($strName, $this->_arrProperties)) ? $this->_arrProperties[$strName] : NULL;
	}

	protected function __set($strName, $mxdValue)
	{
		if ($strName[0] === '_') return; // It is read only!
		
		$strName	= array_key_exists($strName, $this->_arrTidyNames) ? $this->_arrTidyNames[$strName] : $strName;
		
		if (array_key_exists($strName, $this->_arrProperties))
		{
			$this->_arrProperties[$strName]	= $mxdValue;
			
			if ($this->{$strName} !== $mxdValue)
			{
				$this->_saved = FALSE;
			}
		}
		else
		{
			$this->{$strName} = $mxdValue;
		}
	}
	
	//------------------------------------------------------------------------//
	// tidyName
	//------------------------------------------------------------------------//
	/**
	 * tidyName()
	 *
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * 
	 * Converts a string from xxx_yyy_zzz to xxxYyyZzz
	 * If the string is already in the xxxYxxZzz format, then it will not be changed
	 *
	 * @param	string	$strName
	 * @return	string
	 * @method
	 */
	private function tidyName($name)
	{
		$tidy = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
		$tidy[0] = strtolower($tidy[0]);
		return $tidy;
	}
	
	//------------------------------------------------------------------------//
	// toArray()
	//------------------------------------------------------------------------//
	/**
	 * toArray()
	 *
	 * Returns an associative array modelling the Database Record
	 *
	 * Returns an associative array modelling the Database Record
	 * 
	 * @return	array										DB Record
	 *
	 * @method
	 */
	public function toArray()
	{
		return $this->_arrProperties;
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
				case 'selById':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Invoice", "*", "Id = <Id>", NULL, 1);
					break;
				case 'selInvoiceableFNNs':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Service JOIN service_status ON Service.Status = service_status.id",
																					"Service.Id, FNN, Indial100",
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
																					"*",
																					"(FNN = <FNN> OR (FNN LIKE <IndialRange> AND Indial100 = 1)) AND (<DateTime> BETWEEN Service.CreatedOn AND Service.ClosedOn OR (Service.ClosedOn IS NULL AND Service.CreatedOn <= <DateTime>)) AND Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.")",
																					"Id DESC",
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
				
				// INSERTS
				case 'insServiceTotal':
					$arrPreparedStatements[$strStatement]	= new StatementInsert("ServiceTotal");
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
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Charge", "Account = <Account> AND Service IS NULL AND Status = ".CHARGE_APPROVED, Array('Status'=>NULL, 'invoice_run_id'=>NULL));
					break;
				case 'updCDRRevoke':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("CDR", "invoice_run_id = <invoice_run_id>", Array('invoice_run_id'=>NULL, 'Status'=>CDR_RATED));
					break;
				case 'updChargeRevoke':
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Charge", "invoice_run_id = <invoice_run_id>", Array('invoice_run_id'=>NULL, 'Status'=>CHARGE_APPROVED));
					break;
				
				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
?>