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
class Invoice extends ORM_Cached
{
	protected			$_strTableName			= "Invoice";
	protected static	$_strStaticTableName	= "Invoice";
	
	const	MAXIMUM_LOCK_RETRIES	= 100;
	
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
	
	protected static function getCacheName()
	{
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName))
		{
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}
	
	protected static function getMaxCacheSize()
	{
		return 100;
	}
	
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache()
	{
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects()
	{
		return parent::getCachedObjects(__CLASS__);
	}
	
	protected static function addToCache($mObjects)
	{
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false)
	{
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}
	
	public static function getAll($bForceReload=false)
	{
		return parent::getAll($bForceReload, __CLASS__);
	}
	
	public static function importResult($aResultSet)
	{
		return parent::importResult($aResultSet, __CLASS__);
	}
		
	//---------------------------------------------------------------------------------------------------------------------------------//
	//				END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//
	
	public static function generateForInvoiceRunAndAccount($oInvoiceRun, $oAccount)
	{
		$iLocksEncountered	= 0;
		while (!isset($oInvoice))
		{
			if ($iLocksEncountered <= Invoice::MAXIMUM_LOCK_RETRIES)
			{
				try
				{
					$oInvoice	= new Invoice();
					$oInvoice->generate($oAccount, $oInvoiceRun);
				}
				catch (Exception $oException)
				{
					if ($oException instanceof Exception_Database_Deadlock || $oException instanceof Exception_Database_LockTimeout)
					{
						unset($oInvoice);
						$iLocksEncountered++;
					}
					else
					{
						throw $oException;
					}
				}
			}
			else
			{
				throw new Exception("Too many database locks encountered (Maximum: ".Invoice_Run::MAXIMUM_LOCK_RETRIES.")");
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
		static	$dbaDB;
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		$dbaDB		= (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();
		
		$fStopwatchStart	= microtime(true);
		Log::getLog()->log("\t* Invoice processing for Account {$objAccount->Id} started at ".date("Y-m-d H:i:s", (int)$fStopwatchStart));
		
		// Is there already an Invoice for this Account?  If so, revoke it
		Log::getLog()->log("\t* Revoking any existing Invoices for Account with Id {$objAccount->Id}...");
		self::revokeByAccount($objAccount);
		
		$fStopwatchRevoke	= microtime(true);
		Log::getLog()->log("\t* Revoking took ".($fStopwatchRevoke - $fStopwatchStart)."s...");
		
		// Generate Invoice
		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
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
			$this->intInvoiceDatetime				= $objInvoiceRun->intInvoiceDatetime;
			$this->strLastInvoiceDatetime			= $objAccount->getBillingPeriodStart($objInvoiceRun->BillingDate);
			$this->intLastInvoiceDatetime			= strtotime($this->strLastInvoiceDatetime);
			$this->strInvoiceDatetime				= date("Y-m-d H:i:s", $this->intInvoiceDatetime);
			$this->intNextInvoiceDatetime			= strtotime(Invoice_Run::predictNextInvoiceDate($objAccount->CustomerGroup, $objInvoiceRun->strInvoiceDatetime));
			$this->intLastProductionInvoiceDatetime	= strtotime($objAccount->getBillingPeriodStart($objInvoiceRun->BillingDate, true));
			
			$iCurrentDay	= (int)date('d', $objInvoiceRun->intInvoiceDatetime);
			$oPaymentTerms		= Payment_Terms::getCurrentForCustomerGroup($objAccount->CustomerGroup);
			if ($iCurrentDay <= $oPaymentTerms->invoice_day)
			{
				$this->intProratePeriodStart	= strtotime("-1 month", strtotime(date("Y-m-".str_pad($oPaymentTerms->invoice_day, 2, '0', STR_PAD_LEFT), $this->intInvoiceDatetime)));
			}
			else
			{
				$this->intProratePeriodStart	= strtotime(date("Y-m-".str_pad($oPaymentTerms->invoice_day, 2, '0', STR_PAD_LEFT), $this->intInvoiceDatetime));
			}
			$this->intProratePeriodEnd			= strtotime("-1 second", strtotime("+1 month", $this->intProratePeriodStart));
			
			Log::getLog()->log("\t* {$objAccount->Id} Billing Period Start: {$this->strLastInvoiceDatetime} ($this->intLastInvoiceDatetime)");
			Log::getLog()->log("\t* {$objAccount->Id} Billing Period End: {$objInvoiceRun->billing_period_end_datetime}");
			Log::getLog()->log("\t* {$objAccount->Id} Last Invoice Date: ".date("Y-m-d H:i:s", $this->intLastInvoiceDatetime));
			Log::getLog()->log("\t* {$objAccount->Id} Next Invoice Date: ".date("Y-m-d H:i:s", $this->intNextInvoiceDatetime));
			Log::getLog()->log("\t* {$objAccount->Id} Last Production Invoice Date: ".date("Y-m-d H:i:s", $this->intLastProductionInvoiceDatetime));
			Log::getLog()->log("\t* {$objAccount->Id} Prorate Period Start Date: ".date("Y-m-d H:i:s", $this->intProratePeriodStart));
			Log::getLog()->log("\t* {$objAccount->Id} Prorate Period End Date: ".date("Y-m-d H:i:s", $this->intProratePeriodEnd));
			
			$this->billing_period_start_datetime	= $this->strLastInvoiceDatetime;
			$this->billing_period_end_datetime		= $objInvoiceRun->billing_period_end_datetime;
			
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
				Log::getLog()->log("\n\t + Generating Service Total Data for Service with Id {$intServiceId}...", FALSE);
	
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
						$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['bolDisconnectedAndNoCDRs']		= ($arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['bolDisconnectedAndNoCDRs'] === false) ? false : ($arrServiceDetails['ServiceTotal']['bolDisconnectedAndNoCDRs']);
						
						$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['strEarliestPlanStartDatetime']	= ($arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['strEarliestPlanStartDatetime']) ? min($arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['strEarliestPlanStartDatetime'], $arrServiceDetails['ServiceTotal']['PlanStartDatetime']) : $arrServiceDetails['ServiceTotal']['PlanStartDatetime'];
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
				$arrPlanDetails['EarliestStartDatetime']	= $arrDetails['strEarliestPlanStartDatetime'];
				
				if (!$arrDetails['bolDisconnectedAndNoCDRs'])
				{
					// Add Plan Charges
					$arrUsageDetails	= $this->_addPlanCharges($arrPlanDetails, $arrDetails['Services'], NULL);
					
					// Determine and add in Shared Plan Usage Discounts
					$intArrearsPeriodStart	= $arrUsageDetails['ArrearsPeriodStart'];
					$intArrearsPeriodEnd	= $arrUsageDetails['ArrearsPeriodEnd'];
					
					// Get a list of Discounts associated with this Plan
					$aDiscounts	= Rate_Plan::getForId($arrPlanDetails['Id'])->getDiscounts();
					foreach ($aDiscounts as $iDiscountId=>$oDiscount)
					{
						// Calculate this Discount
						$aDiscountTotals	= $this->_calculateDiscount($oDiscount, $arrPlanDetails, $arrDetails['Services'], $intArrearsPeriodStart, $intArrearsPeriodEnd);
						
						// Offset the Global Tax that will be added for discounted usage
						$this->Tax	+= $aDiscountTotals['fDiscountTaxOffset'];
					}
				}
			}
			//--------------------------------------------------------------------//
			
			//----------------------- GENERATE INVOICE DATA ----------------------//
			$fltPreChargeDebitTotal		= $this->Debits;
			$fltPreChargeCreditTotal	= $this->Credits;
			$fltPreChargeTaxTotal		= $this->Tax;
			
			// Mark Account Charges
			$arrWhere	= Array('Account' => $objAccount->Id, 'BillingPeriodEnd'=>$this->billing_period_end_datetime);
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
				Log::getLog()->log($arrAccountChargeTotal);
			}
			Log::getLog()->log("Preliminary Account Charges START");
			$this->Debits	+= $arrAccountChargeTotals['DR'][0] + $arrAccountChargeTotals['DR'][1];
			$this->Credits	+= $arrAccountChargeTotals['CR'][0] + $arrAccountChargeTotals['CR'][1];
			$this->Tax		+= self::calculateGlobalTaxComponent($arrAccountChargeTotals['DR'][0], $this->intInvoiceDatetime) - self::calculateGlobalTaxComponent($arrAccountChargeTotals['CR'][0], $this->intInvoiceDatetime);
			Log::getLog()->log("Preliminary Account Charges END");
			Log::getLog()->log($arrAccountChargeTotals);
			
			// Calculate Preliminary Invoice Values
			$this->AccountBalance	= $this->_objAccount->getAccountBalance(false, false, true);	// We don't want to include Adjustments as they are handled elsewhere, but we do want outstanding Payments
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
				Log::getLog()->log($arrAccountChargeTotal);
			}
			Log::getLog()->log("Final Account Charges START");
			$this->Debits	+= $arrAccountChargeTotals['DR'][0] + $arrAccountChargeTotals['DR'][1];
			$this->Credits	+= $arrAccountChargeTotals['CR'][0] + $arrAccountChargeTotals['CR'][1];
			$this->Tax		+= self::calculateGlobalTaxComponent($arrAccountChargeTotals['DR'][0], $this->intInvoiceDatetime) - self::calculateGlobalTaxComponent($arrAccountChargeTotals['CR'][0], $this->intInvoiceDatetime);
			Log::getLog()->log("Final Account Charges END");
			
			// Recalculate Final Invoice Values
			//$this->Total			= ceil(($this->Debits - $this->Credits) * 100) / 100;
			//$this->Total			= ceil(($this->Debits - $this->Credits) * 100) / 100;
			//$this->Tax				= ceil($this->Tax * 100) / 100;
			$this->Total			= Invoice::roundOut($this->Debits - $this->Credits, 2);
			$this->Tax				= Invoice::roundOut($this->Tax, 2);
			$this->Balance			= $this->Total + $this->Tax;
			$this->TotalOwing		= $this->Balance + $this->AccountBalance;
			
			// Get Adjustments
			$selAdjustmentTotals	= self::_preparedStatement('selAdjustmentTotals');
			if ($selAdjustmentTotals->Execute(Array('account_id' => $objAccount->Id, 'invoice_run_id' => $this->invoice_run_id)) === FALSE)
			{
				// Database Error -- throw Exception
				throw new Exception("DB ERROR: ".$selAdjustmentTotals->Error());
			}
			$aAdjustmentTotals	= Array();
			if ($aAdjustmentTotals = $selAdjustmentTotals->Fetch())
			{
				// Tax is calculated by the query for us
				$this->adjustment_total	= Invoice::roundOut((float)$aAdjustmentTotals['adjustment_total'], 2);
				$this->adjustment_tax	= Invoice::roundOut((float)$aAdjustmentTotals['adjustment_tax'], 2);
				//$this->adjustment_total	= ceil((float)$aAdjustmentTotals['adjustment_total'] * 100) / 100;
				//$this->adjustment_tax	= ceil((float)$aAdjustmentTotals['adjustment_tax'] * 100) / 100;
			}
			else
			{
				$this->adjustment_total	= 0.0;
				$this->adjustment_tax	= 0.0;
			}
			
			// HACKHACKHACK	: Calculate the Charge Total/Tax by subtracting the Adjustment Totals from the overall Totals
			//				Technically correct, but just... dirty...
			$this->charge_total	= $this->Total - $this->adjustment_total;
			$this->charge_tax	= $this->Tax - $this->adjustment_tax;
			
			// Determine Delivery Method
			$objAccountStatus	= Account_Status::getForId($objAccount->Archived);
			$objDeliveryMethod	= Delivery_Method::getForId($objAccount->BillingMethod);
			$objCustomerGroup	= Customer_Group::getForId($objAccount->CustomerGroup);
			if ($objAccountStatus->deliver_invoice === 0)
			{
				// Obey Account Status restrictions
				$this->DeliveryMethod	= DELIVERY_METHOD_DO_NOT_SEND;
			}
			elseif (in_array($objInvoiceRun->invoice_run_type_id, array(INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL)) && $objCustomerGroup && $objCustomerGroup->interimInvoiceDeliveryMethodId)
			{
				// Interim/Final Invoices use the Customer Group's setting (if one is configured)
				$this->DeliveryMethod	= $objCustomerGroup->interimInvoiceDeliveryMethodId;
			}
			else
			{
				// Have we met the requirements for this Delivery Method?
				$objCustomerGroupSettings	= $objDeliveryMethod->getCustomerGroupSettings($objAccount->CustomerGroup);
				
				$this->DeliveryMethod		= ($objCustomerGroupSettings->minimum_invoice_value <= $this->TotalOwing) ? $objAccount->BillingMethod : DELIVERY_METHOD_DO_NOT_SEND;
			}
			
			Log::getLog()->log("Account Status: ".$objAccountStatus->name." ({$objAccount->Archived})");
			Log::getLog()->log("Account Delivery Method: ".$objDeliveryMethod->name." ({$objAccount->BillingMethod})");
			Log::getLog()->log("Invoice Delivery Method: ".Delivery_Method::getForId($this->DeliveryMethod)->name." ({$this->DeliveryMethod})");
			
			// Insert the Invoice Data
			$this->save();
			
			// Commit the Transaction
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
		}
		
		$fStopwatchGeneration	= microtime(true);
		Log::getLog()->log("\t* Generation took ".($fStopwatchGeneration - $fStopwatchRevoke)."s...");
		
		// Export the Invoice
		$this->export();
		
		$fStopwatchExport	= microtime(true);
		Log::getLog()->log("\t* Export to XML took ".($fStopwatchExport - $fStopwatchGeneration)."s...");
		Log::getLog()->log("\t* Total processing time: ".($fStopwatchExport - $fStopwatchStart)."s...");
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
		
		// Mark all CDRs for this Service as TEMPORARY_INVOICE
		$strSQL		= "UPDATE CDR SET Status = ".CDR_TEMP_INVOICE.", invoice_run_id = {$this->invoice_run_id} WHERE Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") AND Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND StartDatetime <= '{$this->billing_period_end_datetime}'";
		$strSQL		.= (!Customer_Group::getForId($objInvoiceRun->customer_group_id)->invoiceCdrCredits) ? " AND (Credit = 0 OR RecordType = 21)" : '';
		//Log::getLog()->log($strSQL);
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
		$strSQL			= "	SELECT SUM(CDR.Cost) AS TotalCost, SUM(CDR.Charge) AS TotalCharge, Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt, COUNT(CDR.Id) AS CDRCount
							FROM (CDR JOIN Rate ON CDR.Rate = Rate.Id) JOIN RecordType ON RecordType.Id = CDR.RecordType
							WHERE CDR.Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND CDR.invoice_run_id = {$this->invoice_run_id}
							GROUP BY Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt";
		$resCDRTotals	= $qryQuery->Execute($strSQL);
		if ($resCDRTotals === FALSE)
		{
			throw new Exception("DB ERROR: ".$qryQuery->Error());
		}
		$intDebitCDRCount	= 0;
		$arrCDRTotals		= Array();
		while ($arrCDRTotal = $resCDRTotals->fetch_assoc())
		{
			$strCapped	= ($arrCDRTotal['Uncapped'])		 	? 'Uncapped'	: 'Capped';
			$strCredit	= ($arrCDRTotal['Credit'])				? 'Credit'		: 'Debit';
			$strTax		= ($arrCDRTotal['global_tax_exempt'])	? 'ExTax'		: 'IncTax';
			$arrCDRTotals['Charge']	[$strCapped][$strCredit][$strTax]	= $arrCDRTotal['TotalCharge'];
			$arrCDRTotals['Cost']	[$strCapped][$strCredit][$strTax]	= $arrCDRTotal['TotalCost'];
			
			$intDebitCDRCount	+= (!$arrCDRTotal['Credit']) ? $arrCDRTotal['CDRCount'] : 0;
		}
		
		// CDR Count
		$arrServiceTotal['bolDisconnectedAndNoCDRs']	= (!$intDebitCDRCount && $arrServiceDetails['Status'] === SERVICE_DISCONNECTED);

		//--------------------------- PLAN CHARGES ---------------------------//
		// Retrieve Plan Details for the current Service
		$selPlanDetails	= $this->_preparedStatement('selPlanDetails');
		if ($selPlanDetails->Execute(Array('Service' => $intServiceId, 'EffectiveDate' => $this->billing_period_end_datetime)) === FALSE)
		{
			throw new Exception("DB ERROR: ".$selPlanDetails->Error());
		}
		$arrPlanDetails	= $selPlanDetails->Fetch();
		$arrServiceTotal['PlanStartDatetime']	= $arrPlanDetails['EarliestStartDatetime'];

		if (!$arrPlanDetails)
		{
			// There is no Plan for this Service, we can't Invoice it
			Log::getLog()->log("No Plan for this Service!");
			return FALSE;
		}

		// Determine & Add in Plan Charge & Usage Limit Details
		$arrServiceTotal['Shared']	= (bool)$arrPlanDetails['Shared'];
		if ($arrServiceTotal['Shared'] || $arrServiceTotal['bolDisconnectedAndNoCDRs'])
		{
			// This is either a Shared Plan or is Disconnected and has no CDRs -- don't charge any Plan Charges
			$fltMinimumCharge					= 0.0;
			
			if ($arrServiceTotal['bolDisconnectedAndNoCDRs'])
			{
				Log::getLog()->log("Waiving Plan Charges");
			}
		}
		else
		{
			$arrUsageDetails	= $this->_addPlanCharges($arrPlanDetails, array($arrServiceDetails), $intServiceId);
			$fltMinimumCharge	= (float)$arrUsageDetails['MinMonthly'];
		}
		//--------------------------------------------------------------------//
		
		//--------------------------- SERVICE TOTALS -------------------------//
		
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
			Log::getLog()->log("TAX EXEMPT CHARGES!");
			Log::getLog()->log($arrCDRTotals);
		}
		
		// Calculate Service Plan Usage Discounts for non-Shared Services
		$fltTotalCharge	= 0.0;
		if (!$arrPlanDetails['Shared'] && !$arrServiceTotal['bolDisconnectedAndNoCDRs'])
		{
			$intArrearsPeriodStart	= $arrUsageDetails['ArrearsPeriodStart'];
			$intArrearsPeriodEnd	= $arrUsageDetails['ArrearsPeriodEnd'];
			
			// Get a list of Discounts associated with this Plan
			$aDiscounts	= Rate_Plan::getForId($arrPlanDetails['Id'])->getDiscounts();
			foreach ($aDiscounts as $iDiscountId=>$oDiscount)
			{
				// Calculate this Discount
				$aDiscountTotals	= $this->_calculateDiscount($oDiscount, $arrPlanDetails, array($arrServiceDetails), $intArrearsPeriodStart, $intArrearsPeriodEnd, $intServiceId);
				
				// Offset the Global Tax that will be added for discounted usage
				$arrServiceTotal['Tax']	+= $aDiscountTotals['fDiscountTaxOffset'];
			}
		}
		
		// Add in the Capped Usage
		$fltTotalCharge			+= $fltCDRCappedTotal;
		$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($fltTaxableCappedCharge, $this->intInvoiceDatetime);
		
		// Add in Uncapped Usage
		$fltTotalCharge			+= $fltCDRUncappedTotal;
		$arrServiceTotal['Tax']	+= self::calculateGlobalTaxComponent($fltTaxableUncappedCharge, $this->intInvoiceDatetime);
		
		// Mark all Service Charges as TEMPORARY_INVOICE
		if ($qryQuery->Execute("UPDATE Charge SET Status = ".CHARGE_TEMP_INVOICE.", invoice_run_id = {$this->invoice_run_id} WHERE Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND ChargedOn <= '{$this->billing_period_end_datetime}'") === FALSE)
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
		$strServiceChargeTotalSQL	=	"SELECT Charge.Nature, Charge.global_tax_exempt, SUM(Charge.Amount) AS Total " .
										"FROM Charge " .
										"WHERE Charge.Service IN (".implode(', ', $arrServiceDetails['Ids']).") AND Charge.invoice_run_id = {$this->invoice_run_id} " .
										"GROUP BY Charge.Nature, Charge.global_tax_exempt";
		//Log::getLog()->log($strServiceChargeTotalSQL);
		$resResult	= $qryQuery->Execute($strServiceChargeTotalSQL);
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

		static	$dbaDB;
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		$dbaDB		= (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();
		
		// Revoke Invoice
		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
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
			if ($qryQuery->Execute("DELETE FROM Charge WHERE ChargeType IN ('PCAD', 'PCAR', 'PCR', 'PDCR') AND CreatedBy IS NULL AND invoice_run_id = {$this->invoice_run_id} AND Account = {$this->Account}") === FALSE)
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
			if ($qryQuery->Execute("DELETE FROM service_total_service WHERE service_total_id = (SELECT Id FROM ServiceTotal WHERE invoice_run_id = {$this->invoice_run_id} AND Account = {$this->Account} AND Id = service_total_id)") === FALSE)
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
			
			// Update the Totals on the InvoiceRun record
			$objInvoiceRun	= Invoice_Run::getForId($this->invoice_run_id);
			$objInvoiceRun->calculateTotals();
			
			// Commit the Transaction
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
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
		static	$dbaDB;
		static	$qryQuery;
		$qryQuery	= (isset($qryQuery)) ? $qryQuery : new Query();
		$dbaDB		= (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();

		// Ensure that this is a Temporary Invoice
		if ($this->Status !== INVOICE_TEMP)
		{
			throw new Exception("Cannot commit Invoice #{$this->Id} because it is not Temporary!");
		}

		try
		{
			// Start Transaction
			$dbaDB->TransactionStart();
			
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
			
			// Commit the Transaction
			$dbaDB->TransactionCommit();
		}
		catch (Exception $eException)
		{
			$dbaDB->TransactionRollback();
			throw $eException;
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
		$oInvoiceRun	= new Invoice_Run(array('Id'=>$this->invoice_run_id), true);
		$oInvoiceRun->export(array($this->Account));
	}
	
	/**
	 * roundOut()
	 *
	 * Rounds a number away from zero (positives round up, negatives round down), aka "round half away from zero"
	 *
	 * @return		mixed
	 *
	 * @method
	 */
	public static function roundOut($mValue, $iPrecision=0)
	{
		$fRoundOut	= round(abs($mValue), $iPrecision);
		return ($mValue < 0.0) ? 0.0 - $fRoundOut : $fRoundOut;
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
	 * @param	integer	$intChargeEndDate						The date on which the Charge Period Ends
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
	public static function prorate($fltAmount, $intChargeStartDate, $intChargeEndDate, $intPeriodStartDate, $intPeriodEndDate, $strSmallestDenomination=DATE_TRUNCATE_DAY, $bolAllowOverflow=TRUE, $intDecimalPlaces=2)
	{
		$intProratePeriod	= Flex_Date::periodLength($intChargeStartDate, $intChargeEndDate, $strSmallestDenomination);
		$intBillingPeriod	= Flex_Date::periodLength($intPeriodStartDate, $intPeriodEndDate, $strSmallestDenomination);
		
		$iProratePeriodDays	= floor($intProratePeriod / Flex_Date::SECONDS_IN_DAY);
		$iBillingPeriodDays	= floor($intBillingPeriod / Flex_Date::SECONDS_IN_DAY);
		
		Log::getLog()->log("Prorating Charge Start Date\t: ".date("Y-m-d H:i:s", $intChargeStartDate));
		Log::getLog()->log("Prorating Charge End Date\t: ".date("Y-m-d H:i:s", $intChargeEndDate));
		Log::getLog()->log("Prorating Period Start Date\t: ".date("Y-m-d H:i:s", $intPeriodStartDate));
		Log::getLog()->log("Prorating Period End Date\t: ".date("Y-m-d H:i:s", $intPeriodEndDate));
		Log::getLog()->log("Prorating Period Length (days)\t: {$iProratePeriodDays}");
		Log::getLog()->log("Billing Period Length (days)\t: {$iBillingPeriodDays}");
		
		$aArguments	= func_get_args();
		Flex::assert(($iBillingPeriodDays > 0), "Invoice Billing Period length in days is not greater than 0", print_r(array('charge-date-start'=>date("Y-m-d H:i:s", $intChargeStartDate), 'charge-date-end'=>date("Y-m-d H:i:s", $intChargeEndDate), 'period-date-start'=>date("Y-m-d H:i:s", $intPeriodStartDate), 'period-date-end'=>date("Y-m-d H:i:s", $intPeriodEndDate), 'prorate-period-days'=>$iProratePeriodDays, 'billing-period-days'=>$iBillingPeriodDays, 'arguments' => $aArguments), true), "Invoice Prorating: Invalid Billing Period");
		if ($iProratePeriodDays)
		{
			$fltProratedAmount			= ($fltAmount / $iBillingPeriodDays) * $iProratePeriodDays;
			Log::getLog()->log("{$fltProratedAmount}\t= ({$fltAmount} / {$iBillingPeriodDays}) * {$iProratePeriodDays}");
			
			$fltProratedAmount			= round($fltProratedAmount, $intDecimalPlaces);
			return $fltProratedAmount;
		}
		else
		{
			return 0.0;
		}
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
	private function _addPlanCharges($arrPlanDetails, $arrServices, $intPrimaryService=NULL)
	{
		static	$qryQuery;
		$qryQuery		= (isset($qryQuery)) ? $qryQuery : new Query();
		$strServiceIds	= implode(', ', self::_extractServiceIds($arrServices));
		
		// If this Plan requires CDRs, get the Earliest CDR Details (ensuring that the earliest date is not after the Invoice date)
		$strEarliestCDR	= null;
		if ($arrPlanDetails['cdr_required'])
		{
			$resResult	= $qryQuery->Execute("SELECT MIN(EarliestCDR) AS EarliestCDR FROM Service WHERE Id IN ({$strServiceIds})");
			if ($resResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$qryQuery->Error());
			}
			$arrMinEarliestCDR	= $resResult->fetch_assoc();
			$strEarliestCDR		= ($arrMinEarliestCDR['EarliestCDR'] !== NULL && strtotime($arrMinEarliestCDR['EarliestCDR']) < $this->intInvoiceDatetime) ? $arrMinEarliestCDR['EarliestCDR'] : NULL;
		}
		else
		{
			Log::getLog()->log("Non-CDR Service");
			
			// Otherwise use the Service Creation Date
			$resResult	= $qryQuery->Execute("SELECT MIN(CreatedOn) AS EarliestCreatedOn FROM Service WHERE Id IN ({$strServiceIds})");
			if ($resResult === FALSE)
			{
				throw new Exception("DB ERROR: ".$qryQuery->Error());
			}
			$arrMinCreatedOn		= $resResult->fetch_assoc();
			
			$strPlanStartDatetime	= $arrPlanDetails['EarliestStartDatetime'];
			$strServiceCreatedOn	= $arrMinCreatedOn['EarliestCreatedOn'];
			
			$strEarliestCDR			= date("Y-m-d", strtotime(max($strPlanStartDatetime, $strServiceCreatedOn)));
			
			if ($strPlanStartDatetime > $strServiceCreatedOn)
			{
				Log::getLog()->log("Earliest CDR comes from Plan Date");
			}
			elseif ($strPlanStartDatetime < $strServiceCreatedOn)
			{
				Log::getLog()->log("Earliest CDR comes from Creation Date");
			}
			else
			{
				Log::getLog()->log("Earliest CDR comes from shared Plan Date/Creation Date");
			}
		}
		$strEarliestCDR	= ($strEarliestCDR !== null && strtotime($strEarliestCDR) < $this->intInvoiceDatetime) ? $strEarliestCDR : null;

		// Default Arrears Period
		$intArrearsPeriodStart	= $this->intLastInvoiceDatetime;
		$intArrearsPeriodEnd	= strtotime("-1 day", $this->intInvoiceDatetime);

		// Is the Service tolling?
		$intLevel	= 0;
		Log::getLog()->log("Earliest CDR: {$strEarliestCDR}");
		if ($strEarliestCDR)
		{
			$fltMinimumCharge	= (float)$arrPlanDetails['MinMonthly'];
			
			// Scalable Plans
			if ($arrPlanDetails['Shared'])
			{
				$intScalable		= (int)$arrPlanDetails['scalable'];
				$intMinServices		= (int)$arrPlanDetails['minimum_services'];
				$intMaxServices		= (int)$arrPlanDetails['maximum_services'];
				if ($intScalable && $intMinServices > 0 && $intMaxServices >= $intMinServices)
				{
					Log::getLog()->log("Scaling Plan Charges & Usage: {$strEarliestCDR}");
					
					Log::getLog()->log("Native Plan Charge: {$fltMinimumCharge}");
					$fltMinimumCharge	= ($fltMinimumCharge	/ $intMaxServices) * max($intMaxServices, count($arrServices));
					Log::getLog()->log("Scaled Plan Charge: {$fltMinimumCharge}");
				}
			}
			
			$arrPlanChargeSteps	= Array();
			
			// Yes -- Does this Service have any Invoiced CDRs (or Plan Charges for non-CDR Plans)?
			if ($arrPlanDetails['cdr_required'])
			{
				$strSQL	= "SELECT 1 FROM ServiceTypeTotal WHERE Service IN ({$strServiceIds}) AND Records > 0 AND invoice_run_id != {$this->invoice_run_id}";
			}
			else
			{
				$strSQL	=	"SELECT 'Plan Charge' AS Matches FROM Charge WHERE Account = {$this->Account} AND (Service IN ({$strServiceIds}) OR ({$arrPlanDetails['Shared']} = 1 AND Service IS NULL)) AND ChargeType IN ('PCAR', 'PCAD', 'PCR', 'PDCR') AND Status = ".CHARGE_INVOICED." LIMIT 1 \n" .
							"UNION \n" .
							"SELECT 'CDR Data' AS Matches FROM ServiceTotal st JOIN ServiceTypeTotal stt ON (stt.Service = st.Service AND stt.invoice_run_id = st.invoice_run_id) JOIN InvoiceRun ir ON (ir.Id = st.invoice_run_id) JOIN service_total_service sts ON (sts.service_total_id = st.Id) WHERE ((UncappedCost > 0 OR CappedCost > 0) OR stt.Id IS NOT NULL) AND sts.service_id IN ({$strServiceIds}) AND ir.BillingDate < '{$this->_objInvoiceRun->BillingDate}' LIMIT 1";
			}
			$resResult	= $qryQuery->Execute($strSQL);
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
			
			// If the Plan is charged in Advance, then check if we have charged a PCAD yet
			$bHasChargedInAdvance		= false;
			$bHasInvoicedOnAnotherPlan	= false;
			if ($arrPlanDetails['InAdvance'])
			{
				$rResult	= $qryQuery->Execute("SELECT * FROM Charge WHERE ChargeType = 'PCAD' AND Account = {$this->Account} AND (Service IN ({$strServiceIds}) OR ({$arrPlanDetails['Shared']} = 1 AND Service IS NULL)) AND Status = ".CHARGE_INVOICED." AND invoice_run_id != {$this->_objInvoiceRun->Id} LIMIT 1");
				if ($rResult === false)
				{
					throw new Exception("DB ERROR: ".$qryQuery->Error());
				}
				elseif ($rResult->num_rows)
				{
					$bHasChargedInAdvance	= true;
				}
				else
				{
					// Check if the Service(s) have been invoiced on another Plan before
					$oResult	= $qryQuery->Execute("SELECT st.RatePlan FROM ServiceTotal st JOIN InvoiceRun ir ON (ir.Id = st.invoice_run_id) WHERE st.Service IN ({$strServiceIds}) AND ir.BillingDate < '{$this->_objInvoiceRun->BillingDate}' AND st.RatePlan != {$arrPlanDetails['Id']} LIMIT 1");
					if ($oResult === false)
					{
						throw new Exception("DB ERROR: ".$qryQuery->Error());
					}
					elseif ($oResult->num_rows)
					{
						$bHasInvoicedOnAnotherPlan	= true;
					}
				}
				Log::getLog()->log("Have ".(($bHasChargedInAdvance) ? '' : 'not ')."charged in advanced before");
				Log::getLog()->log("Have ".(($bHasInvoicedOnAnotherPlan) ? '' : 'not ')."invoiced on another Plan before");
			}
			
			Log::getLog()->log("Arrears period start: ".date("Y-m-d H:i:s", $intArrearsPeriodStart));

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
			if (!$arrPlanDetails['InAdvance'] || (!$bHasChargedInAdvance && !$bHasInvoicedOnAnotherPlan))
			{
				$arrPlanChargeSteps[]	= ($bolFirstInvoice) ? 'FIRST_ARREARS' : 'NORMAL_ARREARS';
				
				// Prorate the Charges and Usage details in Arrears
				$fltMinimumCharge	= Invoice::prorate($fltMinimumCharge	, $intArrearsPeriodStart	, $intArrearsPeriodEnd	, $this->intProratePeriodStart, $this->intProratePeriodEnd);
				
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
		}
		// DEBUG
		Log::getLog()->log(print_r($arrPlanChargeSteps, true));

		// Return usage data
		return Array(
						'MinMonthly'			=> $fltMinimumCharge,

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

		// Charge Description
		$strDescription	= $arrChargeTypes[$strChargeType]['Description'];
		
		// Generate Charge
		$arrPlanCharge						= Array();
		$arrPlanCharge['AccountGroup']		= $intAccountGroup;
		$arrPlanCharge['Account']			= $intAccount;
		$arrPlanCharge['Service']			= $intService;
		$arrPlanCharge['Nature']			= $arrChargeTypes[$strChargeType]['Nature'];
		$arrPlanCharge['ChargeType']		= $strChargeType;
		$arrPlanCharge['charge_type_id']	= $arrChargeTypes[$strChargeType]['Id'];
		$arrPlanCharge['global_tax_exempt']	= 0;
		$arrPlanCharge['Description']		= self::buildPlanChargeDescription($arrPlanDetails['Name'], $strDescription, $intPeriodStartDate, $intPeriodEndDate);
		$arrPlanCharge['ChargedOn']			= date("Y-m-d");
		$arrPlanCharge['Amount']			= abs($fltAmount);
		$arrPlanCharge['invoice_run_id']	= $this->invoice_run_id;
		$arrPlanCharge['Status']			= CHARGE_TEMP_INVOICE;
		if (!$GLOBALS['fwkFramework']->AddCharge($arrPlanCharge))
		{
			throw new Exception("Unable to create '{$arrPlanCharge['Description']}' for {$intAccount}::{$intService}!");
		}
		return true;
	}
	
	public static function buildPlanChargeDescription($sPlanName, $sChargeDescription, $iStartDatetime, $iEndDatetime)
	{
		return "{$sPlanName} {$sChargeDescription} from ".date("d/m/Y", $iStartDatetime)." to ".date("d/m/Y", $iEndDatetime);
	}
	
	private function _calculateDiscount($oDiscount, $aPlanDetails, $aServices, $iArrearsPeriodStart, $iArrearsPeriodEnd, $iPrimaryServiceId=NULL)
	{
		static	$oQuery, $oChargeTypePCR;
		$oQuery			= ($oQuery) ? $oQuery : new Query();
		$oChargeTypePCR	= ($oChargeTypePCR) ? $oChargeTypePCR : Charge_Type::getByCode('PCR');
		
		$sServices	= implode(', ', self::_extractServiceIds($aServices));
		
		Log::getLog()->log("Discount: ".$oDiscount->name);
		
		$fChargeLimit	= max($oDiscount->charge_limit, 0);
		$iUnitLimit		= max($oDiscount->unit_limit, 0);
		
		if ($iUnitLimit)
		{
			$sDiscountType	= Discount::DISCOUNT_TYPE_UNITS;
			$mDiscountLimit	= $iUnitLimit;
		}
		else
		{
			$sDiscountType	= Discount::DISCOUNT_TYPE_CHARGE;
			$mDiscountLimit	= $fChargeLimit;
		}
		
		// Scalable Shared Discounts
		$mScaledDiscountLimit	= $mDiscountLimit;
		if ($aPlanDetails['Shared'])
		{
			$iScalable		= (int)$aPlanDetails['scalable'];
			$iMinServices	= (int)$aPlanDetails['minimum_services'];
			$iMaxServices	= (int)$aPlanDetails['maximum_services'];
			if ($iScalable && $iMinServices > 0 && $iMaxServices >= $iMinServices)
			{
				$mScaledDiscountLimit	= ($mDiscountLimit	/ $iMaxServices) * max($iMaxServices, count($aServices));
				Log::getLog()->log("Scaled! (Full Discount: {$mDiscountLimit}; Scaled Discount: {$mScaledDiscountLimit})");
			}
		}
		
		$mProratedDiscountLimit	= self::prorate($mScaledDiscountLimit, $iArrearsPeriodStart, $iArrearsPeriodEnd, $this->intProratePeriodStart, $this->intProratePeriodEnd, DATE_TRUNCATE_DAY, true);
		$mProratedDiscountLimit	= ($sDiscountType === Discount::DISCOUNT_TYPE_UNITS) ? round($mProratedDiscountLimit) : $mProratedDiscountLimit;
		
		Log::getLog()->log("Prorated Discount Limit: {$mProratedDiscountLimit}");
		Log::getLog()->log("Discount Limit: {$mDiscountLimit}");
		Log::getLog()->log("Discount Limit Type: ".(($iUnitLimit) ? 'UNITS' : 'CHARGE'));
		
		// If there is a valid Discount to apply...
		$fDiscountApplied	= 0.0;
		$fDiscountTaxOffset	= 0.0;
		if ($mDiscountLimit)
		{
			// Get the RecordTypes associated with this Discount
			$aRecordTypes	= $oDiscount->getRecordTypes();
			$sRecordTypes	= implode(', ', array_keys($aRecordTypes));
			
			if ($sRecordTypes)
			{
				// Get all CDRs for the eligible RecordTypes which are not excluded from the Cap
				$sIncludedUsage	=	"SELECT cdr.Units, cdr.Charge , cdr.Credit, rt.global_tax_exempt " .
									"FROM CDR cdr JOIN Rate r ON r.Id = cdr.Rate JOIN RecordType rt ON (rt.Id = cdr.RecordType) " .
									"WHERE cdr.Service IN ({$sServices}) AND cdr.invoice_run_id = {$this->invoice_run_id} AND r.Uncapped = 0 AND cdr.RecordType IN ($sRecordTypes) " .
									"ORDER BY cdr.StartDatetime ";
				//Log::getLog()->log($sIncludedUsage);
				$oResult	= $oQuery->Execute($sIncludedUsage);
				if ($oResult === FALSE)
				{
					throw new Exception("DB ERROR: ".$oQuery->Error());
				}
				
				// If there are any CDRs
				if ($oResult->num_rows)
				{
					$iTotalUnits		= 0;
					$fTotalCharge		= 0.0;
					$fTaxOffset			= 0.0;
					$fTotalCredit		= 0.0;
					
					$mRemainingDiscount		= $mProratedDiscountLimit;
					while ($aDataCDR = $oResult->fetch_assoc())
					{
						$iUnits		= ($aDataCDR['Credit']) ? 0 - $aDataCDR['Units'] : $aDataCDR['Units'];
						$fCharge	= ($aDataCDR['Credit']) ? 0 - $aDataCDR['Charge'] : $aDataCDR['Charge'];
						
						$iTotalUnits	+= $iUnits;
						$fTotalCharge	+= $fCharge;
						
						// If we haven't gone over our limit yet
						if ($mRemainingDiscount > 0.0)
						{
							switch ($sDiscountType)
							{
								case Discount::DISCOUNT_TYPE_UNITS:
									$mRemainingDiscount	-= $iUnits;
									if ($mRemainingDiscount < 0)
									{
										// Prorate the last CDR (assumes a consistent rate per unit)
										$fRatePerUnit	= ($iUnits) ? ($fCharge / $iUnits) : 0;
										$fCharge		-= (abs($mRemainingDiscount) * $fRatePerUnit);
									}
									break;
									
								case Discount::DISCOUNT_TYPE_CHARGE:
									$mRemainingDiscount	-= $fCharge;
									if ($mRemainingDiscount < 0)
									{
										// Prorate the last CDR
										$fCharge		-= abs($mRemainingDiscount);
									}
									break;
							}
							
							// Add a global tax offset against the credited CDR
							if (!$aDataCDR['global_tax_exempt'])
							{
								$fTaxOffset	-= self::calculateGlobalTaxComponent($fCharge, $this->intInvoiceDatetime);
							}
							
							$fTotalCredit	+= $fCharge;
						}
					}
					$mTotalUsage	= ($iUnitLimit) ? $iTotalUnits : $fTotalCharge;
					
					Log::getLog()->log("Total Usage Units			: {$iTotalUnits}");
					Log::getLog()->log("Total Usage Charge			: \${$fTotalCharge}");
					switch ($sDiscountType)
					{
						case Discount::DISCOUNT_TYPE_UNITS:
							Log::getLog()->log("Prorated Discount Limit		: {$mProratedDiscountLimit} Units");
							Log::getLog()->log("Usage Included in Discount	: ".($mProratedDiscountLimit - max(0, $mRemainingDiscount))." Units");
							Log::getLog()->log("Overusage					: ".(max(0, $mTotalUsage - $mProratedDiscountLimit - max(0, $mRemainingDiscount)))." Units");
							break;
							
						case Discount::DISCOUNT_TYPE_CHARGE:
							Log::getLog()->log("Prorated Discount Limit		: \${$mProratedDiscountLimit}");
							Log::getLog()->log("Usage Included in Discount	: \$".($mProratedDiscountLimit - max(0, $mRemainingDiscount)));
							Log::getLog()->log("Overusage					: \$".(max(0, $mTotalUsage - $mProratedDiscountLimit - max(0, $mRemainingDiscount))));
							break;
					}
					
					Log::getLog()->log("Creditback					: \${$fTotalCredit}");
					Log::getLog()->log("Tax Offset					: \${$fTaxOffset}");
					Log::getLog()->log("Overusage Charge			: \$".($fTotalCharge - $fTotalCredit));
				
					if ($mTotalUsage > 0)
					{
						// Add the Credit
						$oCharge					= new Charge();
						$oCharge->AccountGroup		= $this->_objAccount->AccountGroup;
						$oCharge->Account			= $this->Account;
						$oCharge->Service			= $iPrimaryServiceId;
						$oCharge->Nature			= 'CR';
						$oCharge->ChargeType		= $oChargeTypePCR->ChargeType;
						$oCharge->charge_type_id	= $oChargeTypePCR->Id;
						$oCharge->global_tax_exempt	= 1;
						$oCharge->Description		= self::buildPlanChargeDescription($aPlanDetails['Name'], "{$oDiscount->name} Discount", $iArrearsPeriodStart, $iArrearsPeriodEnd);
						$oCharge->CreatedOn			= date("Y-m-d");
						$oCharge->ChargedOn			= date("Y-m-d");
						$oCharge->Amount			= abs($fTotalCredit);
						$oCharge->Notes				= '';
						$oCharge->invoice_run_id	= $this->invoice_run_id;
						$oCharge->Status			= CHARGE_TEMP_INVOICE;
						$oCharge->save();
						
						$fDiscountApplied	= $fTotalCredit;
						$fDiscountTaxOffset	= $fTaxOffset;
					}
					else
					{
						// Net Credit (or $0.00) -- no Discounting!
						$fDiscountApplied	= 0.0;
						$fDiscountTaxOffset	= 0.0;
					}
				}
				else
				{
					Log::getLog()->log("No CDRs - Skipping Discounting!");
				}
			}
		}
		
		return	array
				(
					'fDiscountApplied'		=> $fDiscountApplied,
					'fDiscountTaxOffset'	=> $fDiscountTaxOffset
				);
	}
	
	private static function _extractServiceIds($arrServices)
	{
		$arrServiceIds	= array();
		foreach ($arrServices as $arrService)
		{
			foreach ($arrService['Ids'] as $intServiceId)
			{
				$arrServiceIds[]	= $intServiceId;
			}
		}
		return $arrServiceIds;
	}
	
	//------------------------------------------------------------------------//
	// Resolve
	//------------------------------------------------------------------------//
	/**
	 * resolve()
	 *
	 * Resolve a Dispute on the invoice
	 *
	 * Resolve a Dispute on the invoice
	 *
	 * @param	Integer		$intResolveMethod	(CONSTANT) The method in which this dispute will be resolved
	 * @param	Float		$fltAmount			The amount which will be changed to the Account, if customer to Pay $X.XX
	 * @return	void
	 *
	 * @method
	 */
	public function resolve($iResolveMethod, $fAmount)
	{
		$iUserId = Flex::getUserId();
		
		// Check that the invoice is currently in dispute
		if ($this->Status <> INVOICE_DISPUTED)
		{
			throw new Exception('Invoice Not Disputed');
		}
		
		// The status that will be added to the Note
		$sStatus 	= "Resolution for Dispute on Invoice #".$this->Id."\n";
		
		// Used to save a new credit, if necessary
		$oCharge	= false;
		
		switch ($iResolveMethod)
		{
			case DISPUTE_RESOLVE_FULL_PAYMENT:
				// If the full amount is required to be paid (for example, Dispute was Denied)
				$sStatus .= "No Credit was applied to this Dispute. ";
				$sStatus .= "The Customer is required to pay the full Amount.";
				break;
				
			case DISPUTE_RESOLVE_PARTIAL_PAYMENT:
				// If a payment is required for a particular amount of a Dispute
				// Generate a credit for Invoice.Disputed - $fltAmount
				$oCharge					= new Charge();
				$oCharge->AccountGroup		= $this->AccountGroup;
				$oCharge->Account			= $this->Account;
				$oCharge->Service			= NULL;
				$oCharge->invoice_run_id	= NULL;
				$oCharge->CreatedBy			= $iUserId;
				$oCharge->CreatedOn			= date('Y-m-d');
				$oCharge->ApprovedBy		= $iUserId;
				$oCharge->ChargeType		= '';
				$oCharge->Description		= 'Invoice Dispute (Invoice: #'.$this->Id.')';
				$oCharge->ChargedOn			= date('Y-m-d');
				$oCharge->Nature			= NATURE_CR;
				$oCharge->Amount			= $this->Disputed - $fAmount;
				$oCharge->Status			= CHARGE_APPROVED;
				
				$sStatus .= "This dispute was resolved by partial payment. ";
				$sStatus .= "The Customer is required to pay the amount of $".sprintf("%01.4f", $fAmount).". ";
				$sStatus .= "The Original disputed amount was: $".sprintf("%01.4f", $this->Disputed).". ";
				$sStatus .= "The remaining amount of $".sprintf("%01.4f", $this->Disputed - $fAmount)." ";
				$sStatus .= "was Credited towards this Account.";
				break;
				
			case DISPUTE_RESOLVE_NO_PAYMENT:
				// Generate a credit for Invoice.Disputed
				$oCharge					= new Charge();
				$oCharge->AccountGroup		= $this->AccountGroup;
				$oCharge->Account			= $this->Account;
				$oCharge->Service			= NULL;
				$oCharge->invoice_run_id	= NULL;
				$oCharge->CreatedBy			= $iUserId;
				$oCharge->CreatedOn			= date('Y-m-d');
				$oCharge->ApprovedBy		= $iUserId;
				$oCharge->ChargeType		= '';
				$oCharge->Description		= 'Invoice Dispute (Invoice: #'.$this->Id.')';
				$oCharge->ChargedOn			= date('Y-m-d');
				$oCharge->Nature			= NATURE_CR;
				$oCharge->Amount			= $this->Disputed;
				$oCharge->Status			= CHARGE_WAITING;
				
				$sStatus	.= "The full amount of the the dispute ($".sprintf("%01.4f", $this->Disputed).") ";
				$sStatus	.= "was Credited towards this Account.";
				break;
				
			default:
				throw new Exception('Invalid Resolution');
		}
		
		if ($oCharge)
		{
			// Not given, default value applied
			$oCharge->Notes				= '';
			$oCharge->global_tax_exempt	= 0;
			
			// Save the new charge
			$oCharge->save();
		}
		
		// Invoice.Disputed = 0
		// if Balance > 0	Status = INVOICE_COMMITTED
		// else				Status = INVOICE_SETTLED
		$this->Disputed = 0;
		$this->Status	= ($this->Balance > 0) ? INVOICE_COMMITTED : INVOICE_SETTLED;
		$this->save();
		
		// Add a note
		Note::createNote(SYSTEM_NOTE_TYPE, $sStatus, $iUserId, $this->Account);
	}
	
	public static function redistributeBalances()
	{
		$oQuery	= new Query();
		
		// We want to redistribute Invoice balances so that payments and adjustments affect oldest invoices first
		$sAccounts	= "	SELECT		i.Account																AS account_id,
									MAX(IF(i.Balance != 0 AND i.Balance != (i.Total + i.Tax), i.Id, NULL))	AS latest_redistributable,
									MIN(IF(i.Balance != 0 AND i.Balance != (i.Total + i.Tax), i.Id, NULL))	AS earliest_redistributable
						
						FROM		Invoice i
						
						WHERE		i.Status NOT IN (100, 106)
						
						GROUP BY	i.Account
						
						HAVING		latest_redistributable != earliest_redistributable;";
		
		if (($oAccountsResult = $oQuery->Execute($sAccounts)) === false)
		{
			throw new Exception($oQuery->Error());
		}
		
		Log::getLog()->log(" * Redistributing {$oAccountsResult->num_rows} Accounts...");
		
		while ($aAccount = $oAccountsResult->fetch_assoc())
		{
			Log::getLog()->log("\t + Redistributing Account {$aAccount['account_id']}...");
			
			if (DataAccess::getDataAccess()->TransactionStart())
			{
				try
				{
					// Get affected Invoices
					$sAffectedInvoices	= "	SELECT		i.*
											
											FROM		Invoice i
											
											WHERE		i.Status NOT IN (100, 106)
														AND i.Id BETWEEN {$aAccount['earliest_redistributable']} AND {$aAccount['latest_redistributable']}
														AND i.Account = {$aAccount['account_id']}
											
											ORDER BY	i.CreatedOn ASC,
														i.Id ASC;";
					
					if (($oInvoicesResult = $oQuery->Execute($sAffectedInvoices)) === false)
					{
						throw new Exception($oQuery->Error());
					}
					
					$aInvoices	= array();
					while ($aInvoice = $oInvoicesResult->fetch_assoc())
					{
						$aInvoices[]	= $aInvoice;
					}
					$aInvoices	= Invoice::importResult($aInvoices);
					
					// Total Balances for redistribution
					$fInvoicesGrandTotal		= 0.0;
					$fTotalPayments				= 0.0;
					$fTotalCreditCharges		= 0.0;
					$fTotalCreditAdjustments	= 0.0;
					$fBalanceGrandTotal			= 0.0;
					foreach ($aInvoices as $oInvoice)
					{
						$fTotalCreditAdjustments	-= max(0.0, $oInvoice->adjustment_total + $oInvoice->adjustment_tax);	// Credit Adjustment Totals
						$fTotalCreditCharges		-= min(0.0, $oInvoice->charges_total + $oInvoice->charges_tax);			// Credit Charge Totals
						$fTotalPayments				+= max(0.0, ($oInvoice->Total + $oInvoice->Tax) - $oInvoice->Balance);	// Payments
						
						$fInvoicesGrandTotal	+= $oInvoice->Total + $oInvoice->Tax;
						$fBalanceGrandTotal		+= $oInvoice->Balance;
					}
					$fTotalReducable	= $fTotalCreditAdjustments + $fTotalCreditCharges + $fTotalPayments;
					
					Log::getLog()->log("\t\t * Invoices Grand Total: \${$fInvoicesGrandTotal}");
					Log::getLog()->log("\t\t * Balance Grand Total: \${$fBalanceGrandTotal}");
					Log::getLog()->log("\t\t * Credit Charge Total: \${$fTotalCreditCharges}");
					Log::getLog()->log("\t\t * Credit Adjustment Total: \${$fTotalCreditAdjustments}");
					Log::getLog()->log("\t\t * Payment Total: \${$fTotalPayments}");
					Log::getLog()->log("\t\t * Total Reducable: \${$fTotalReducable}");
					
					// Redistribute Balances
					$fRedistributedBalanceGrandTotal	= 0.0;
					foreach ($aInvoices as $oInvoice)
					{
						// Pay out Invoice as much as possible
						$oInvoice->Balance	= max(0.0, $oInvoice->Total + $oInvoice->Tax);
						$fSubsidy			= min($oInvoice->Balance, $fTotalReducable);
						
						$oInvoice->Balance	-= $fSubsidy;
						$fTotalReducable	-= $fSubsidy;
						
						// Save
						$oInvoice->save();
						
						$fRedistributedBalanceGrandTotal	+= $oInvoice->Balance;
						
						Log::getLog()->log("\t\t - Invoice {$oInvoice->Id} of \$".($oInvoice->Total + $oInvoice->Tax)." reduced by \${$fSubsidy} to \${$oInvoice->Balance} (\${$fTotalReducable} remaining to distribute)");
					}
					
					// Apply any remaining credits to the most recent Invoice
					Log::getLog()->log("\t\t ! \${$fTotalReducable} left to distribute overall.");
					if (Invoice::roundOut($fTotalReducable, 4) > 0)
					{
						$oInvoice	= end($aInvoices);
						
						$oInvoice->Balance	-= $fTotalReducable;
						
						$fRedistributedBalanceGrandTotal	+= $fTotalReducable;
						
						// Save
						$oInvoice->save();
						
						Log::getLog()->log("\t\t - Invoice {$oInvoice->Id} of \$".($oInvoice->Total + $oInvoice->Tax)." reduced to \${$oInvoice->Balance} with the excess \${$fTotalReducable}");
						
						$fTotalReducable	= 0.0;
					}
					
					// Ensure that the pre- and post-redistribution Balance grand totals are equal
					if (Invoice::roundOut($fBalanceGrandTotal, 4) != Invoice::roundOut($fRedistributedBalanceGrandTotal, 4))
					{
						throw new Exception("Pre and Post Redistribution Balance Mismatch! (pre: \${$fBalanceGrandTotal}; post: \${$fRedistributedBalanceGrandTotal})");
					}
					
					throw new Exception("Debugging!");
					
					// Commit
					DataAccess::getDataAccess()->TransactionCommit();
				}
				catch (Exception $oException)
				{
					Log::getLog()->log("Exception: ".$oException->getMessage()."; Rolling back changes");
					DataAccess::getDataAccess()->TransactionRollback();
				}
			}
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
					Log::getLog()->log("*** {$strName} updated to \${$mxdValue}");
					$arrBacktrace	= debug_backtrace();
					Log::getLog()->log("*** Total: {$this->Total}; Tax: {$this->Tax}; Debits: {$this->Debits}; Credits: {$this->Credits};\t{$strName} @ Line {$arrBacktrace[0]['line']}");

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
																					"RatePlan.*, ServiceRatePlan.Id AS ServiceRatePlan, ServiceRatePlan.StartDatetime AS EarliestStartDatetime",
																					"ServiceRatePlan.Service = <Service> AND <EffectiveDate> BETWEEN StartDatetime AND EndDatetime",
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
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Invoice JOIN InvoiceRun ON InvoiceRun.Id = Invoice.invoice_run_id", "Invoice.*", "Account = <Account> AND invoice_run_status_id IN (".INVOICE_RUN_STATUS_TEMPORARY.", ".INVOICE_RUN_STATUS_GENERATING.")");
					break;
				case 'selAccountChargeTotals':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Charge", "Nature, global_tax_exempt, SUM(Amount) AS Total", "Service IS NULL AND Account = <Account> AND Status = ".CHARGE_TEMP_INVOICE, NULL, NULL, "Nature, global_tax_exempt");
					break;
				case 'selLastInvoiceDatetime':
					$arrPreparedStatements[$strStatement]	= new StatementSelect("Invoice JOIN InvoiceRun ON Invoice.invoice_run_id = InvoiceRun.Id", "InvoiceRun.BillingDate", "Invoice.Account = <Account>", "InvoiceRun.BillingDate DESC, InvoiceRun.Id DESC", 1);
					break;
				case 'selAdjustmentTotals':
					$arrPreparedStatements[$strStatement]	= new StatementSelect(	"Charge c", /* FINISH ME!!! */
																					"	COALESCE(
																							SUM(
																								COALESCE(
																									IF(
																										c.Nature = 'CR',
																										0 - c.Amount,
																										c.Amount
																									), 0
																								)
																							), 0
																						)																						AS adjustment_total,
																						COALESCE(
																							SUM(
																								COALESCE(
																									IF(
																										c.Nature = 'CR',
																										0 - c.Amount,
																										c.Amount
																									), 0
																								)
																								*
																								IF(
																									c.global_tax_exempt = 1,
																									0,
																									(
																										SELECT		COALESCE(EXP(SUM(LN(tt.rate_percentage))), 1)
																										FROM		tax_type tt
																										WHERE		c.ChargedOn BETWEEN tt.start_datetime AND tt.end_datetime
																													AND tt.global = 1
																									)
																								)
																							), 0
																						)																						AS adjustment_tax",
																					"c.Account = <account_id> AND c.Status = ".CHARGE_TEMP_INVOICE." AND c.charge_model_id = ".CHARGE_MODEL_ADJUSTMENT." AND c.invoice_run_id = <invoice_run_id>");
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
					$arrPreparedStatements[$strStatement]	= new StatementUpdate("Charge", "Account = <Account> AND Service IS NULL AND Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND ChargedOn <= <BillingPeriodEnd>", Array('Status'=>NULL, 'invoice_run_id'=>NULL));
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