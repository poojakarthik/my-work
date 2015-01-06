<?php
class Invoice extends ORM_Cached {
	protected $_strTableName = "Invoice";
	protected static $_strStaticTableName = "Invoice";

	const MAXIMUM_LOCK_RETRIES = 100;

	public function __construct($arrProperties=array(), $bolLoadById=false) {
		// Parent constructor
		parent::__construct($arrProperties, $bolLoadById);
	}

	protected static function getCacheName() {
		// It's safest to keep the cache name the same as the class name, to ensure uniqueness
		static $strCacheName;
		if (!isset($strCacheName)) {
			$strCacheName = __CLASS__;
		}
		return $strCacheName;
	}

	protected static function getMaxCacheSize() {
		return 100;
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	// START - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - START
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function clearCache() {
		parent::clearCache(__CLASS__);
	}

	protected static function getCachedObjects() {
		return parent::getCachedObjects(__CLASS__);
	}

	protected static function addToCache($mObjects) {
		parent::addToCache($mObjects, __CLASS__);
	}

	public static function getForId($iId, $bSilentFail=false) {
		return parent::getForId($iId, $bSilentFail, __CLASS__);
	}

	public static function getAll($bForceReload=false) {
		return parent::getAll($bForceReload, __CLASS__);
	}

	public static function importResult($aResultSet) {
		return parent::importResult($aResultSet, __CLASS__);
	}

	//---------------------------------------------------------------------------------------------------------------------------------//
	// END - FUNCTIONS REQUIRED WHEN INHERITING FROM ORM_Cached UNTIL WE START USING PHP 5.3 - END
	//---------------------------------------------------------------------------------------------------------------------------------//

	public static function getForInvoiceRunAndAccount($mInvoiceRun, $mAccount) {
		$iInvoiceRunId = ORM::extractId($mInvoiceRun);
		$iAccountId = ORM::extractId($mAccount);

		$oGetForInvoiceRunAndAccount = self::_preparedStatement('selForInvoiceRunAndAccount');
		if (false === $oGetForInvoiceRunAndAccount->Execute(array('invoice_run_id'=>$iInvoiceRunId,'account_id'=>$iAccountId))) {
			throw new Exception_Database($oGetForInvoiceRunAndAccount->Error());
		}
		if ($aResult = $oGetForInvoiceRunAndAccount->Fetch()) {
			return new self($aResult);
		} else {
			throw new Exception("No Invoice for Account #{$iAccountId} and Invoice Run #{$iInvoiceRunId}");
		}
	}

	public static function generateForInvoiceRunAndAccount($oInvoiceRun, $oAccount) {
		$iLocksEncountered = 0;
		while (!isset($oInvoice)) {
			if ($iLocksEncountered <= Invoice::MAXIMUM_LOCK_RETRIES) {
				try {
					$oInvoice = new Invoice();
					$oInvoice->generate($oAccount, $oInvoiceRun);

					return $oInvoice;
				} catch (Exception $oException) {
					if ($oException instanceof Exception_Database_Deadlock || $oException instanceof Exception_Database_LockTimeout) {
						unset($oInvoice);
						$iLocksEncountered++;
					} else {
						throw $oException;
					}
				}
			} else {
				throw new Exception("Too many database locks encountered (Maximum: ".Invoice::MAXIMUM_LOCK_RETRIES.")");
			}
		}
	}

	public static function getForInvoiceRunId($iInvoiceRunId) {
		$oStmt = self::_preparedStatement('selByInvoiceRunId');
		if ($oStmt->Execute(array('invoice_run_id' => $iInvoiceRunId)) === false) {
			// Database Error -- throw Exception
			throw new Exception_Database("Failed to get invoices for invoice run id {$iInvoiceRunId}. ".$oStmt->Error());
		} else {
			// Return array of Invoice objects
			$aInvoices = array();
			while ($aInvoice = $oStmt->Fetch()) {
				$aInvoices[$aInvoice['Id']] = new Invoice($aInvoice);
			}

			return $aInvoices;
		}
	}

	public static function getSampleDataForCustomerGroupId($iCustomerGroup) {
		$sSql = "
			SELECT i.Id as 'invoice_id', c.Id as 'contact_id', a.Id as 'account_id'
			FROM Invoice i
			JOIN Account a ON (
						i.Account = a.Id
						AND i.Id = (
							SELECT max(i2.Id)
							FROM Invoice i2
							JOIN Account a2 ON (
								i2.Account = a2.Id
								AND a2.CustomerGroup = {$iCustomerGroup}
							)
						)
					)
			JOIN Contact c ON (a.PrimaryContact = c.Id)
		";
		$oQuery = new Query();
		$mResult = $oQuery->Execute($sSql);
		if ($mResult === false) {
			return array();
		}
		return $mResult->fetch_assoc();

	}

	// Generates an Invoice and returns the instance
	public function generate($objAccount, $objInvoiceRun, $oInvoiceSource=null) {
		// Make sure the invoice source is set, defaults to uninvoiced
		if ($oInvoiceSource === null) {
			$oInvoiceSource = new Invoice_Source_Uninvoiced();
		}

		static $dbaDB;
		static $qryQuery;
		$qryQuery = (isset($qryQuery)) ? $qryQuery : new Query();
		$dbaDB = (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();

		$fStopwatchStart = microtime(true);
		Log::getLog()->log("\t* Invoice processing for Account {$objAccount->Id} started at ".date("Y-m-d H:i:s", (int)$fStopwatchStart));

		// Is there already an Invoice for this Account?  If so, revoke it
		Log::getLog()->log("\t* Revoking any existing Invoices for Account with Id {$objAccount->Id}...");
		self::revokeByAccount($objAccount);

		$fStopwatchRevoke = microtime(true);
		Log::getLog()->log("\t* Revoking took ".($fStopwatchRevoke - $fStopwatchStart)."s...");

		// Generate Invoice
		try {
			// Start Transaction
			$dbaDB->TransactionStart();

			$this->invoice_run_id = $objInvoiceRun->Id;
			$this->_objInvoiceRun = $objInvoiceRun;
			$this->_objAccount = $objAccount;
			$this->AccountGroup = $objAccount->AccountGroup;
			$this->Account = $objAccount->Id;
			$this->CreatedOn = $objInvoiceRun->BillingDate;
			$this->Total = 0.0;
			$this->Debits = 0.0;
			$this->Credits = 0.0;
			$this->Tax = 0.0;

			// Calculate Billing Period
			$this->intInvoiceDatetime = $objInvoiceRun->intInvoiceDatetime;
			$this->strLastInvoiceDatetime = $objAccount->getBillingPeriodStart($objInvoiceRun->BillingDate);
			$this->intLastInvoiceDatetime = strtotime($this->strLastInvoiceDatetime);
			$this->strInvoiceDatetime = date("Y-m-d H:i:s", $this->intInvoiceDatetime);
			$this->intNextInvoiceDatetime = strtotime(Invoice_Run::predictNextInvoiceDate($objAccount->CustomerGroup, $objInvoiceRun->strInvoiceDatetime));
			$this->intLastProductionInvoiceDatetime = strtotime($objAccount->getBillingPeriodStart($objInvoiceRun->BillingDate, true));

			$iCurrentDay = (int)date('d', $objInvoiceRun->intInvoiceDatetime);
			$oPaymentTerms = Payment_Terms::getCurrentForCustomerGroup($objAccount->CustomerGroup);
			if ($iCurrentDay <= $oPaymentTerms->invoice_day) {
				$this->intProratePeriodStart = strtotime("-1 month", strtotime(date("Y-m-".str_pad($oPaymentTerms->invoice_day, 2, '0', STR_PAD_LEFT), $this->intInvoiceDatetime)));
			} else {
				$this->intProratePeriodStart = strtotime(date("Y-m-".str_pad($oPaymentTerms->invoice_day, 2, '0', STR_PAD_LEFT), $this->intInvoiceDatetime));
			}
			$this->intProratePeriodEnd = strtotime("-1 second", strtotime("+1 month", $this->intProratePeriodStart));

			Log::getLog()->log("\t* {$objAccount->Id} Billing Period Start: {$this->strLastInvoiceDatetime} ($this->intLastInvoiceDatetime)");
			Log::getLog()->log("\t* {$objAccount->Id} Billing Period End: {$objInvoiceRun->billing_period_end_datetime}");
			Log::getLog()->log("\t* {$objAccount->Id} Last Invoice Date: ".date("Y-m-d H:i:s", $this->intLastInvoiceDatetime));
			Log::getLog()->log("\t* {$objAccount->Id} Next Invoice Date: ".date("Y-m-d H:i:s", $this->intNextInvoiceDatetime));
			Log::getLog()->log("\t* {$objAccount->Id} Last Production Invoice Date: ".date("Y-m-d H:i:s", $this->intLastProductionInvoiceDatetime));
			Log::getLog()->log("\t* {$objAccount->Id} Prorate Period Start Date: ".date("Y-m-d H:i:s", $this->intProratePeriodStart));
			Log::getLog()->log("\t* {$objAccount->Id} Prorate Period End Date: ".date("Y-m-d H:i:s", $this->intProratePeriodEnd));

			$this->billing_period_start_datetime = $this->strLastInvoiceDatetime;
			$this->billing_period_end_datetime = $objInvoiceRun->billing_period_end_datetime;

			//----------------- INVOICEABLE SERVICE PREPROCESSING ----------------//
			// Retrieve a list of Invoiceable FNNs for this Account
			Log::getLog()->log("\t * Getting list of Invoiceable FNNs...");
			$selInvoiceableFNNs = $this->_preparedStatement('selInvoiceableFNNs');
			if ($selInvoiceableFNNs->Execute(array('InvoiceDatetime'=>$this->strInvoiceDatetime, 'Account'=>$objAccount->Id)) === false) {
				// Database Error -- throw Exception
				throw new Exception_Database("DB ERROR: ".$selInvoiceableFNNs->Error());
			}

			// Process each Invoiceable FNN
			$arrServices = array();
			while ($arrFNN = $selInvoiceableFNNs->Fetch()) {
				Log::getLog()->log("\t\t * Getting details for FNN {$arrFNN['FNN']}...");

				// Get the Service Details for the current owner of this FNN (or indial range), on this Account
				$arrWhere = array();
				$arrWhere['FNN'] = $arrFNN['FNN'];
				$arrWhere['IndialRange'] = substr($arrFNN['FNN'], 0, -2).'__';
				$arrWhere['DateTime'] = $this->strInvoiceDatetime;
				$arrWhere['Account'] = $objAccount->Id;
				$selCurrentService = $this->_preparedStatement('selCurrentService');
				if ($selCurrentService->Execute($arrWhere) === false) {
					// Database Error -- throw Exception
					throw new Exception_Database("DB ERROR: ".$selCurrentService->Error());
				}
				if (!($arrCurrentService = $selCurrentService->Fetch())) {
					// Error
					throw new Exception("WTF -- No Invoiceable Service Id for apparently Invoiceable FNN '{$arrFNN['FNN']}'!");
				}

				// Add this Service to our Invoicing Array
				if (!isset($arrServices[$arrCurrentService['Id']])) {
					$arrServices[$arrCurrentService['Id']] = $arrCurrentService;
				}
				$arrServices[$arrCurrentService['Id']]['Ids'][] = $arrFNN['Id'];
			}
			//--------------------------------------------------------------------//

			//----------------------- GENERATE SERVICE DATA ----------------------//
			$arrSharedPlans = array();
			foreach ($arrServices as $intServiceId=>&$arrServiceDetails) {
				Log::getLog()->log("\n\t + Generating Service Total Data for Service with Id {$intServiceId}...", false);

				// Generate Service Total Data
				$mixServiceTotal = $this->_generateService($arrServiceDetails, $objAccount, $objInvoiceRun, $oInvoiceSource);
				if ($mixServiceTotal !== false) {
					$arrServiceDetails['ServiceTotal'] = $mixServiceTotal;
					$this->Debits += $arrServiceDetails['ServiceTotal']['TotalCharge'] + $arrServiceDetails['ServiceTotal']['Debit'];
					$this->Credits += $arrServiceDetails['ServiceTotal']['Credit'];
					$this->Tax += $arrServiceDetails['ServiceTotal']['Tax'];
					$fltServiceGrandTotal = $arrServiceDetails['ServiceTotal']['TotalCharge'] + $arrServiceDetails['ServiceTotal']['Debit'] - $arrServiceDetails['ServiceTotal']['Credit'];
					Log::getLog()->log("\t Total: \${$fltServiceGrandTotal}; Tax: \${$arrServiceDetails['ServiceTotal']['Tax']}");

					// Is this a Shared Plan?
					if ($arrServiceDetails['ServiceTotal']['Shared']) {
						Log::getLog()->log("\t\t ! Service is on Shared Plan {$arrServiceDetails['ServiceTotal']['RatePlan']}...");
						$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['Services'][$intServiceId] = &$arrServiceDetails;
						$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxExemptCappedCharge'] += $arrServiceDetails['ServiceTotal']['fltTaxExemptCappedCharge'];
						$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['fltTaxableCappedCharge'] += $arrServiceDetails['ServiceTotal']['fltTaxableCappedCharge'];
						$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['bolDisconnectedAndNoCDRs'] = ($arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['bolDisconnectedAndNoCDRs'] === false) ? false : ($arrServiceDetails['ServiceTotal']['bolDisconnectedAndNoCDRs']);

						$arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['strEarliestPlanStartDatetime'] = ($arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['strEarliestPlanStartDatetime']) ? min($arrSharedPlans[$arrServiceDetails['ServiceTotal']['RatePlan']]['strEarliestPlanStartDatetime'], $arrServiceDetails['ServiceTotal']['PlanStartDatetime']) : $arrServiceDetails['ServiceTotal']['PlanStartDatetime'];
					}
				} else {
					// Unable to create Service Total Data (for a sane reason, eg. No Rate Plan)
					Log::getLog()->log("\t Service is not invoicable");
				}
			}

			// Calculate and Add in Shared Plan Charges and Credits as Account Charges
			Log::getLog()->log("\t * Generating Shared Plan Charges...");
			foreach ($arrSharedPlans as $intRatePlan=>$arrDetails) {
				// Get Shared Plan Details
				Log::getLog()->log("\t\t + Rate Plan {$intRatePlan}...");
				$selPlanDetailsById = self::_preparedStatement('selPlanDetailsById');
				if ($selPlanDetailsById->Execute(array('RatePlan' => $intRatePlan)) === false) {
					// Database Error -- throw Exception
					throw new Exception_Database("DB ERROR: ".$selPlanDetailsById->Error());
				} elseif (!($arrPlanDetails = $selPlanDetailsById->Fetch())) {
					throw new Exception("Unable to retrieve details for RatePlan with Id '{$intRatePlan}'!");
				}
				$arrPlanDetails['EarliestStartDatetime'] = $arrDetails['strEarliestPlanStartDatetime'];

				if (!$arrDetails['bolDisconnectedAndNoCDRs']) {
					// Add Plan Charges
					$arrUsageDetails = $this->_addPlanCharges($arrPlanDetails, $arrDetails['Services'], null, $oInvoiceSource);

					// Determine and add in Shared Plan Usage Discounts
					$intArrearsPeriodStart = $arrUsageDetails['ArrearsPeriodStart'];
					$intArrearsPeriodEnd = $arrUsageDetails['ArrearsPeriodEnd'];

					// Get a list of Discounts associated with this Plan
					$aDiscounts = Rate_Plan::getForId($arrPlanDetails['Id'])->getDiscounts();
					foreach ($aDiscounts as $iDiscountId=>$oDiscount) {
						// Calculate this Discount
						$aDiscountTotals = $this->_calculateDiscount($oDiscount, $arrPlanDetails, $arrDetails['Services'], $intArrearsPeriodStart, $intArrearsPeriodEnd);

						// Offset the Global Tax that will be added for discounted usage
						$this->Tax += $aDiscountTotals['fDiscountTaxOffset'];
					}
				}
			}
			//--------------------------------------------------------------------//

			//----------------------- GENERATE INVOICE DATA ----------------------//
			$fltPreChargeDebitTotal = $this->Debits;
			$fltPreChargeCreditTotal = $this->Credits;
			$fltPreChargeTaxTotal = $this->Tax;

			// Mark Account Charges
			//----------------------------------------------------------------//
			$arrWhere = array('Account' => $objAccount->Id, 'BillingPeriodEnd'=>$this->billing_period_end_datetime);
			$arrData = array('Status' => CHARGE_TEMP_INVOICE, 'invoice_run_id' => $this->invoice_run_id);
			$updMarkAccountCharges = self::_preparedStatement('updMarkAccountCharges');
			if ($updMarkAccountCharges->Execute($arrData, $arrWhere) === false) {
				// Database Error -- throw Exception
				throw new Exception_Database("DB ERROR: ".$updMarkAccountCharges->Error());
			}

			// Mark Adjustments (Payment-like)
			//----------------------------------------------------------------//
			$mResult = Query::run("
				UPDATE adjustment
				SET invoice_run_id = {$this->invoice_run_id}
				WHERE account_id = {$this->Account}
				AND invoice_run_id IS NULL
				AND effective_date <= '{$this->billing_period_end_datetime}'
				AND reviewed_datetime <= '{$this->billing_period_end_datetime}'
				AND adjustment_status_id = ".ADJUSTMENT_STATUS_APPROVED
			);
			Log::getLog()->log("Updated {$mResult} Adjustments");

			// Get Preliminary Charge Totals
			//----------------------------------------------------------------//
			$selAccountChargeTotals = self::_preparedStatement('selAccountChargeTotals');
			if ($selAccountChargeTotals->Execute(array('Account' => $objAccount->Id, 'invoice_run_id' => $this->invoice_run_id)) === false) {
				// Database Error -- throw Exception
				throw new Exception_Database("DB ERROR: ".$selAccountChargeTotals->Error());
			}
			$arrAccountChargeTotals = array();
			while ($arrAccountChargeTotal = $selAccountChargeTotals->Fetch()) {
				$arrAccountChargeTotals[$arrAccountChargeTotal['Nature']][$arrAccountChargeTotal['global_tax_exempt']] = $arrAccountChargeTotal['Total'];
				Log::getLog()->log($arrAccountChargeTotal);
			}
			Log::getLog()->log("Preliminary Account Charges START");
			$this->Debits += $arrAccountChargeTotals['DR'][0] + $arrAccountChargeTotals['DR'][1];
			$this->Credits += $arrAccountChargeTotals['CR'][0] + $arrAccountChargeTotals['CR'][1];
			$this->Tax += self::calculateGlobalTaxComponent($arrAccountChargeTotals['DR'][0], $this->intInvoiceDatetime) - self::calculateGlobalTaxComponent($arrAccountChargeTotals['CR'][0], $this->intInvoiceDatetime);
			Log::getLog()->log("Preliminary Account Charges END");
			Log::getLog()->log($arrAccountChargeTotals);

			// Calculate Preliminary Invoice Values
			//----------------------------------------------------------------//
			$this->AccountBalance = $oInvoiceSource->getAccountBalance($this->_objAccount);
			$this->Total = ceil(($this->Debits - $this->Credits) * 100) / 100;
			$this->Balance = $this->Total + $this->Tax;
			$this->TotalOwing = $this->Balance + $this->AccountBalance;
			$this->DueOn = date("Y-m-d", strtotime("+ {$objAccount->PaymentTerms} days", $this->intInvoiceDatetime));
			$this->Disputed = 0.0;
			$this->Status = INVOICE_TEMP;

			// Generate Account Billing Time Charges
			//----------------------------------------------------------------//
			$arrModules = Billing_Charge::getModules();
			foreach ($arrModules[$objAccount->CustomerGroup]['Billing_Charge_Account'] as $chgModule) {
				Log::getLog()->log("Generating Billing Charge: ".get_class($chgModule));

				// Generate charge
				$chgModule->Generate($this, $objAccount);
			}

			// Revert to pre-Preliminary Totals
			//----------------------------------------------------------------//
			$this->Debits = $fltPreChargeDebitTotal;
			$this->Credits = $fltPreChargeCreditTotal;
			$this->Tax = $fltPreChargeTaxTotal;

			// Get Final Charge Totals
			//----------------------------------------------------------------//
			$selAccountChargeTotals = self::_preparedStatement('selAccountChargeTotals');
			if ($selAccountChargeTotals->Execute(array('Account' => $objAccount->Id, 'invoice_run_id' => $this->invoice_run_id)) === false) {
				// Database Error -- throw Exception
				throw new Exception_Database("DB ERROR: ".$selAccountChargeTotals->Error());
			}
			$arrAccountChargeTotals = array();
			while ($arrAccountChargeTotal = $selAccountChargeTotals->Fetch()) {
				$arrAccountChargeTotals[$arrAccountChargeTotal['Nature']][$arrAccountChargeTotal['global_tax_exempt']] = $arrAccountChargeTotal['Total'];
				Log::getLog()->log($arrAccountChargeTotal);
			}
			Log::getLog()->log("Final Account Charges START");
			$this->Debits += $arrAccountChargeTotals['DR'][0] + $arrAccountChargeTotals['DR'][1];
			$this->Credits += $arrAccountChargeTotals['CR'][0] + $arrAccountChargeTotals['CR'][1];
			$this->Tax += self::calculateGlobalTaxComponent($arrAccountChargeTotals['DR'][0], $this->intInvoiceDatetime) - self::calculateGlobalTaxComponent($arrAccountChargeTotals['CR'][0], $this->intInvoiceDatetime);
			Log::getLog()->log("Final Account Charges END");

			// Recalculate Final Invoice Values
			//----------------------------------------------------------------//
			//$this->Total = ceil(($this->Debits - $this->Credits) * 100) / 100;
			//$this->Total = ceil(($this->Debits - $this->Credits) * 100) / 100;
			//$this->Tax = ceil($this->Tax * 100) / 100;
			$this->Total = Invoice::roundOut($this->Debits - $this->Credits, 2);
			$this->Tax = Invoice::roundOut($this->Tax, 2);
			$this->Balance = $this->Total + $this->Tax;
			$this->TotalOwing = $this->Balance + $this->AccountBalance;

			// Get Adjustment Totals (Charge-based)
			//----------------------------------------------------------------//
			// NOTE: This has been deprecated.  It only exists to support re-rating old Invoices
			// adjustment_total and adjustment_tax should both be 0 on all new Invoices
			$selAdjustmentTotals = self::_preparedStatement('selAdjustmentTotals');
			if ($selAdjustmentTotals->Execute(array('account_id' => $objAccount->Id, 'invoice_run_id' => $this->invoice_run_id)) === false) {
				// Database Error -- throw Exception
				throw new Exception_Database("DB ERROR: ".$selAdjustmentTotals->Error());
			}
			$aAdjustmentTotals = array();
			if ($aAdjustmentTotals = $selAdjustmentTotals->Fetch()) {
				// Tax is calculated by the query for us
				$this->adjustment_total = Invoice::roundOut((float)$aAdjustmentTotals['adjustment_total'], 2);
				$this->adjustment_tax = Invoice::roundOut((float)$aAdjustmentTotals['adjustment_tax'], 2);
				//$this->adjustment_total = ceil((float)$aAdjustmentTotals['adjustment_total'] * 100) / 100;
				//$this->adjustment_tax = ceil((float)$aAdjustmentTotals['adjustment_tax'] * 100) / 100;
			} else {
				$this->adjustment_total = 0.0;
				$this->adjustment_tax = 0.0;
			}

			// HACKHACKHACK : Calculate the Charge Total/Tax by subtracting the Adjustment Totals from the overall Totals
			// Technically correct, but just... dirty...
			$this->charge_total = $this->Total - $this->adjustment_total;
			$this->charge_tax = $this->Tax - $this->adjustment_tax;

			// Determine Delivery Method
			//----------------------------------------------------------------//
			$objAccountStatus = Account_Status::getForId($objAccount->Archived);
			$objDeliveryMethod = Delivery_Method::getForId($objAccount->BillingMethod);
			$objCustomerGroup = Customer_Group::getForId($objAccount->CustomerGroup);
			if ($objAccountStatus->deliver_invoice === 0) {
				// Obey Account Status restrictions
				$this->DeliveryMethod = DELIVERY_METHOD_DO_NOT_SEND;
			} elseif (in_array($objInvoiceRun->invoice_run_type_id, array(INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM_FIRST)) && $objCustomerGroup && $objCustomerGroup->interimInvoiceDeliveryMethodId) {
				// Interim/Final Invoices use the Customer Group's setting (if one is configured)
				$this->DeliveryMethod = $objCustomerGroup->interimInvoiceDeliveryMethodId;
			} else {
				// Have we met the requirements for this Delivery Method?
				$objCustomerGroupSettings = $objDeliveryMethod->getCustomerGroupSettings($objAccount->CustomerGroup);

				$this->DeliveryMethod = ($objCustomerGroupSettings->minimum_invoice_value <= $this->TotalOwing) ? $objAccount->BillingMethod : DELIVERY_METHOD_DO_NOT_SEND;
			}

			Log::getLog()->log("Account Status: ".$objAccountStatus->name." ({$objAccount->Archived})");
			Log::getLog()->log("Account Delivery Method: ".$objDeliveryMethod->name." ({$objAccount->BillingMethod})");
			Log::getLog()->log("Invoice Delivery Method: ".Delivery_Method::getForId($this->DeliveryMethod)->name." ({$this->DeliveryMethod})");

			// Insert the Invoice Data
			//----------------------------------------------------------------//
			$this->save();

			// Commit the Transaction
			//----------------------------------------------------------------//
			$dbaDB->TransactionCommit();
		} catch (Exception $eException) {
			$dbaDB->TransactionRollback();
			throw $eException;
		}

		$fStopwatchGeneration = microtime(true);
		Log::getLog()->log("\t* Generation took ".($fStopwatchGeneration - $fStopwatchRevoke)."s...");

		// Export the Invoice
		$this->export();

		$fStopwatchExport = microtime(true);
		Log::getLog()->log("\t* Export to XML took ".($fStopwatchExport - $fStopwatchGeneration)."s...");
		Log::getLog()->log("\t* Total processing time: ".($fStopwatchExport - $fStopwatchStart)."s...");
		//--------------------------------------------------------------------//
	}

	// Generates a Service on an Invoice for a given Account and InvoiceRun
	private function _generateService($arrServiceDetails, $objAccount, $objInvoiceRun, $oInvoiceSource) {
		static $qryQuery;
		$qryQuery = (isset($qryQuery)) ? $qryQuery : new Query();

		$arrServiceTotal = array();
		$arrServiceTotal['TotalCharge'] = 0.0;
		$arrServiceTotal['Tax'] = 0.0;
		$intServiceId = $arrServiceDetails['Id'];

		// Mark all CDRs for this Service as TEMPORARY_INVOICE
		$oInvoiceSource->markAllCDRsAsTemporaryInvoice($this->invoice_run_id, $objInvoiceRun->customer_group_id, $arrServiceDetails['Ids'], $this->billing_period_end_datetime);

		Log::getLog()->log("Inserting ServiceTypeTotals");

		// Generate ServiceTypeTotals
		$iPrecisionMultiplier = pow(10, Rate::RATING_PRECISION);
		$strExtensionsQuery = "
			INSERT INTO ServiceTypeTotal
				(FNN, AccountGroup, Account, Service, RecordType, Charge, Units, Records, RateGroup, Cost, invoice_run_id)
			SELECT		CDR.FNN,
						CDR.AccountGroup,
						CDR.Account,
						{$intServiceId} AS Service,
						CDR.RecordType,
						(
							(CEILING(ABS(SUM(IF(CDR.Credit = 1, 0 - CDR.Charge, CDR.Charge))) * {$iPrecisionMultiplier}) / {$iPrecisionMultiplier})
							*
							SIGN(SUM(IF(CDR.Credit = 1, 0 - CDR.Charge, CDR.Charge)))
						) AS Charge,
						SUM(
							CASE WHEN CDR.Credit = 1 THEN
								0 - CAST(CDR.Units AS SIGNED)
							ELSE
								CAST(CDR.Units AS SIGNED)
							END
						) AS Units,
						COUNT(CDR.Charge) AS Records,
						ServiceRateGroup.RateGroup AS RateGroup,
						SUM(
							CASE WHEN CDR.Credit = 1 THEN
								0 - CDR.Cost
							ELSE
								CDR.Cost
							END
						) AS Cost,
						{$this->invoice_run_id} AS invoice_run_id
			FROM		CDR
						JOIN Service ON (Service.Id = CDR.Service),
						ServiceRateGroup
			WHERE		CDR.FNN IS NOT NULL
						AND CDR.RecordType IS NOT NULL
						AND CDR.invoice_run_id = {$this->invoice_run_id}
						AND CDR.Service IN (".implode(', ', $arrServiceDetails['Ids']).")
						AND ServiceRateGroup.Id = (
							SELECT		SRG.Id
							FROM		ServiceRateGroup SRG
							WHERE		'{$this->billing_period_end_datetime}' BETWEEN SRG.StartDatetime AND SRG.EndDatetime
										AND SRG.Service = CDR.Service
							ORDER BY	CreatedOn DESC
							LIMIT		1
						)
			GROUP BY	CDR.FNN,
						CDR.RecordType
		";
		if ($qryQuery->Execute($strExtensionsQuery) === false) {
			throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
		}

		// Get CDR Total Details
		$strSQL = "
			SELECT		SUM(CDR.Cost) AS TotalCost,
						SUM(CDR.Charge) AS TotalCharge,
						Rate.Uncapped,
						CDR.Credit,
						RecordType.global_tax_exempt,
						COUNT(CDR.Id) AS CDRCount
			FROM		CDR
						JOIN Rate ON (CDR.Rate = Rate.Id)
						JOIN RecordType ON (RecordType.Id = CDR.RecordType)
			WHERE		CDR.Service IN (".implode(', ', $arrServiceDetails['Ids']).")
						AND CDR.invoice_run_id = {$this->invoice_run_id}
			GROUP BY	Rate.Uncapped,
						CDR.Credit,
						RecordType.global_tax_exempt
		";
		$resCDRTotals = $qryQuery->Execute($strSQL);
		if ($resCDRTotals === false) {
			throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
		}
		$intDebitCDRCount = 0;
		$arrCDRTotals = array();
		while ($arrCDRTotal = $resCDRTotals->fetch_assoc()) {
			$strCapped = ($arrCDRTotal['Uncapped'])  	? 'Uncapped' : 'Capped';
			$strCredit = ($arrCDRTotal['Credit']) ? 'Credit' : 'Debit';
			$strTax = ($arrCDRTotal['global_tax_exempt']) ? 'ExTax' : 'IncTax';
			$arrCDRTotals['Charge'][$strCapped][$strCredit][$strTax] = $arrCDRTotal['TotalCharge'];
			$arrCDRTotals['Cost'][$strCapped][$strCredit][$strTax] = $arrCDRTotal['TotalCost'];

			$intDebitCDRCount += (!$arrCDRTotal['Credit']) ? $arrCDRTotal['CDRCount'] : 0;
		}

		// CDR Count
		$arrServiceTotal['bolDisconnectedAndNoCDRs'] = (!$intDebitCDRCount && $arrServiceDetails['Status'] === SERVICE_DISCONNECTED);

		//--------------------------- PLAN CHARGES ---------------------------//
		// Retrieve Plan Details for the current Service
		$selPlanDetails = $this->_preparedStatement('selPlanDetails');
		if ($selPlanDetails->Execute(array('Service' => $intServiceId, 'EffectiveDate' => $this->billing_period_end_datetime)) === false) {
			throw new Exception_Database("DB ERROR: ".$selPlanDetails->Error());
		}
		$arrPlanDetails = $selPlanDetails->Fetch();

		//Log::getLog()->log("PLAN DETAILS: ".print_r($arrPlanDetails, true));

		$arrServiceTotal['PlanStartDatetime'] = $arrPlanDetails['EarliestStartDatetime'];

		if (!$arrPlanDetails) {
			// There is no Plan for this Service, we can't Invoice it
			Log::getLog()->log("No Plan for this Service!");
			return false;
		}

		// Determine & Add in Plan Charge & Usage Limit Details
		$arrServiceTotal['Shared'] = (bool)$arrPlanDetails['Shared'];
		if ($arrServiceTotal['Shared'] || $arrServiceTotal['bolDisconnectedAndNoCDRs']) {
			// This is either a Shared Plan or is Disconnected and has no CDRs -- don't charge any Plan Charges
			$fltMinimumCharge = 0.0;

			if ($arrServiceTotal['bolDisconnectedAndNoCDRs']) {
				Log::getLog()->log("Waiving Plan Charges");
			}
		} else {
			$arrUsageDetails = $this->_addPlanCharges($arrPlanDetails, array($arrServiceDetails), $intServiceId, $oInvoiceSource);
			$fltMinimumCharge = $arrUsageDetails['MinMonthly'];
		}

		//--------------------------------------------------------------------//

		//--------------------------- SERVICE TOTALS -------------------------//

		$fltTaxExemptCappedCharge = $arrCDRTotals['Charge']['Capped']['Debit']['ExTax'] - $arrCDRTotals['Charge']['Capped']['Credit']['ExTax'];
		$fltTaxableCappedCharge = $arrCDRTotals['Charge']['Capped']['Debit']['IncTax'] - $arrCDRTotals['Charge']['Capped']['Credit']['IncTax'];
		$fltCDRCappedTotal = $fltTaxableCappedCharge + $fltTaxExemptCappedCharge;

		$fltTaxExemptUncappedCharge = $arrCDRTotals['Charge']['Uncapped']['Debit']['ExTax'] - $arrCDRTotals['Charge']['Uncapped']['Credit']['ExTax'];
		$fltTaxableUncappedCharge = $arrCDRTotals['Charge']['Uncapped']['Debit']['IncTax'] - $arrCDRTotals['Charge']['Uncapped']['Credit']['IncTax'];
		$fltCDRUncappedTotal = $fltTaxableUncappedCharge + $fltTaxExemptUncappedCharge;

		$fltTaxExemptCappedCost = $arrCDRTotals['Cost']['Capped']['Debit']['ExTax'] - $arrCDRTotals['Cost']['Capped']['Credit']['ExTax'];
		$fltTaxableCappedCost = $arrCDRTotals['Cost']['Capped']['Debit']['IncTax'] - $arrCDRTotals['Cost']['Capped']['Credit']['IncTax'];
		$fltCDRCappedCost = $fltTaxableCappedCost + $fltTaxExemptCappedCost;

		$fltTaxExemptUncappedCost = $arrCDRTotals['Cost']['Uncapped']['Debit']['ExTax'] - $arrCDRTotals['Cost']['Uncapped']['Credit']['ExTax'];
		$fltTaxableUncappedCost = $arrCDRTotals['Cost']['Uncapped']['Debit']['IncTax'] - $arrCDRTotals['Cost']['Uncapped']['Credit']['IncTax'];
		$fltCDRUncappedCost = $fltTaxableUncappedCost + $fltTaxExemptUncappedCost;

		$arrServiceTotal['fltTaxExemptCappedCharge'] = $fltTaxExemptCappedCharge;
		$arrServiceTotal['fltTaxableCappedCharge'] = $fltTaxableCappedCharge;

		$fltTaxExemptCost = $fltTaxExemptCappedCost + $fltTaxExemptUncappedCost;
		$fltTaxExemptCharge = $fltTaxExemptCappedCharge + $fltTaxExemptUncappedCharge;
		if ($fltTaxExemptCost || $fltTaxExemptCharge) {
			Log::getLog()->log("TAX EXEMPT CHARGES!");
			Log::getLog()->log($arrCDRTotals);
		}

		// Calculate Service Plan Usage Discounts for non-Shared Services
		$fltTotalCharge = 0.0;
		if (!$arrPlanDetails['Shared'] && !$arrServiceTotal['bolDisconnectedAndNoCDRs']) {
			$intArrearsPeriodStart = $arrUsageDetails['ArrearsPeriodStart'];
			$intArrearsPeriodEnd = $arrUsageDetails['ArrearsPeriodEnd'];

			// Get a list of Discounts associated with this Plan
			$aDiscounts = Rate_Plan::getForId($arrPlanDetails['Id'])->getDiscounts();
			foreach ($aDiscounts as $iDiscountId=>$oDiscount) {
				// Calculate this Discount
				$aDiscountTotals = $this->_calculateDiscount($oDiscount, $arrPlanDetails, array($arrServiceDetails), $intArrearsPeriodStart, $intArrearsPeriodEnd, $intServiceId);

				// Offset the Global Tax that will be added for discounted usage
				$arrServiceTotal['Tax'] += $aDiscountTotals['fDiscountTaxOffset'];
			}
		}

		// Add in the Capped Usage
		$fltTotalCharge += $fltCDRCappedTotal;
		$arrServiceTotal['Tax'] += self::calculateGlobalTaxComponent($fltTaxableCappedCharge, $this->intInvoiceDatetime);

		// Add in Uncapped Usage
		$fltTotalCharge += $fltCDRUncappedTotal;
		$arrServiceTotal['Tax'] += self::calculateGlobalTaxComponent($fltTaxableUncappedCharge, $this->intInvoiceDatetime);

		// Mark all Service Charges as TEMPORARY_INVOICE
		if ($qryQuery->Execute("
			UPDATE	Charge
			SET		Status = ".CHARGE_TEMP_INVOICE.",
					invoice_run_id = {$this->invoice_run_id}
			WHERE	Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.")
					AND Service IN (".implode(', ', $arrServiceDetails['Ids']).")
					AND ChargedOn <= '{$this->billing_period_end_datetime}'
		") === false) {
			throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
		}

		// Add in Service Billing-time Charges
		$arrModules = Billing_Charge::getModules();
		foreach ($arrModules[$objAccount->CustomerGroup]['Billing_Charge_Service'] as $chgModule) {
			// Generate charge
			$mixResult = $chgModule->Generate($this, new Service($arrServiceDetails, true));
		}

		// Retrieve Charge Totals
		$strServiceChargeTotalSQL = "
			SELECT		Charge.Nature,
						Charge.global_tax_exempt,
						SUM(Charge.Amount) AS Total
			FROM		Charge
			WHERE		Charge.Service IN (".implode(', ', $arrServiceDetails['Ids']).")
						AND Charge.invoice_run_id = {$this->invoice_run_id}
			GROUP BY	Charge.Nature,
						Charge.global_tax_exempt
		";
		$resResult = $qryQuery->Execute($strServiceChargeTotalSQL);
		if ($resResult === false) {
			throw new Exception_Database("DB ERROR: ".$resResult->Error());
		}
		$arrChargeTotals = array();
		while ($arrChargeTotal = $resResult->fetch_assoc()) {
			$arrChargeTotals[$arrChargeTotal['Nature']][($arrChargeTotal['global_tax_exempt']) ? 'ExTax' : 'IncTax'] = $arrChargeTotal['Total'];

			//$fltTotalCharge += ($arrChargeTotal['Nature'] === 'DR') ? $arrChargeTotal['Total'] : -$arrChargeTotal['Total'];
		}

		$arrServiceTotal['Tax'] += self::calculateGlobalTaxComponent($arrChargeTotals['DR']['IncTax'], $this->intInvoiceDatetime);
		//Log::getLog()->log("Service Tax: \${$arrServiceTotal['Tax']} @ Line ".__LINE__);
		$arrServiceTotal['Tax'] -= self::calculateGlobalTaxComponent($arrChargeTotals['CR']['IncTax'], $this->intInvoiceDatetime);
		//Log::getLog()->log("Service Tax: \${$arrServiceTotal['Tax']} @ Line ".__LINE__);

		$fltServiceCredits = $arrChargeTotals['CR']['IncTax'] + $arrChargeTotals['CR']['ExTax'];
		$fltServiceDebits = $arrChargeTotals['DR']['IncTax'] + $arrChargeTotals['DR']['ExTax'];

		// Finalise and Insert Service Total
		$arrServiceTotal['FNN'] = $arrServiceDetails['FNN'];
		$arrServiceTotal['AccountGroup'] = $objAccount->AccountGroup;
		$arrServiceTotal['Account'] = $objAccount->Id;
		$arrServiceTotal['Service'] = $arrServiceDetails['Id'];
		$arrServiceTotal['invoice_run_id'] = $this->invoice_run_id;
		$arrServiceTotal['CappedCharge'] = $fltCDRCappedTotal;
		$arrServiceTotal['UncappedCharge'] = $fltCDRUncappedTotal;
		$arrServiceTotal['TotalCharge'] = $fltTotalCharge;
		$arrServiceTotal['Credit'] = $fltServiceCredits;
		$arrServiceTotal['Debit'] = $fltServiceDebits;
		$arrServiceTotal['RatePlan'] = $arrPlanDetails['Id'];
		$arrServiceTotal['service_rate_plan'] = $arrPlanDetails['ServiceRatePlan'];
		$arrServiceTotal['CappedCost'] = $fltCDRCappedCost;
		$arrServiceTotal['UncappedCost'] = $fltCDRUncappedCost;
		$arrServiceTotal['PlanCharge'] = 0.0; // Deprecated

		Log::getLog()->log("Inserting ServiceTotal: \n".print_r($arrServiceTotal, true));

		$insServiceTotal = self::_preparedStatement('insServiceTotal');
		if (($arrServiceTotal['Id'] = $insServiceTotal->Execute($arrServiceTotal)) === false) {
			throw new Exception_Database("DB ERROR: ".$insServiceTotal->Error());
		}

		// Link each Service to the ServiceTotal
		$insServiceTotalService = self::_preparedStatement('insServiceTotalService');
		foreach ($arrServiceDetails['Ids'] as $intServiceId) {
			$arrData = array(
				'service_id' => $intServiceId,
				'service_total_id' => $arrServiceTotal['Id']
			);
			if ($insServiceTotalService->Execute($arrData) === false) {
				throw new Exception_Database("DB ERROR: ".$insServiceTotalService->Error());
			}
		}

		// Return the Service Total details
		return $arrServiceTotal;
		//--------------------------------------------------------------------//
	}

	// Revokes all Temporary Invoices for a given Account
	public static function revokeByAccount($objAccount) {
		$selTemporaryInvoicesByAccount = self::_preparedStatement('selTemporaryInvoicesByAccount');
		if ($selTemporaryInvoicesByAccount->Execute(array('Account' => $objAccount->Id)) === false) {
			throw new Exception_Database("DB ERROR: ".$selTemporaryInvoicesByAccount->Error());
		}

		while ($arrInvoice = $selTemporaryInvoicesByAccount->Fetch()) {
			$objInvoice = new Invoice($arrInvoice);
			$objInvoice->revoke();
		}
	}

	// Revokes a Temporary Invoice
	public function revoke() {
		// Is this Invoice Temporary?
		if ($this->Status !== INVOICE_TEMP) {
			// No, throw an Exception
			throw new Exception("Invoice '{$this->Id}' is not a Temporary Invoice!");
		}

		// Check if this invoice is final, interim or interim first.
		$oInvoiceRun = Invoice_Run::getForId($this->invoice_run_id);
		$aInvoiceRunTypes = array(INVOICE_RUN_TYPE_FINAL, INVOICE_RUN_TYPE_INTERIM, INVOICE_RUN_TYPE_INTERIM_FIRST);
		if (in_array($oInvoiceRun->invoice_run_type_id, $aInvoiceRunTypes)) {
			// If this is the only invoice left in it's run, revoke the run instead
			$oStmt = self::_preparedStatement('selByInvoiceRunId');
			if ($oStmt->Execute(array('invoice_run_id' => $this->invoice_run_id)) === false) {
				// Database Error -- throw Exception
				throw new Exception_Database("DB ERROR: ".$oStmt->Error());
			} else if ($oStmt->Count() == 1) {
				// Only one left, exit and revoke the run instead
				Log::getLog()->log("Invoice {$this->Id} is last in it's invoice run, revoke the run instead...");
				$oInvoiceRun->revoke();
				return;
			}
		}

		Log::getLog()->log("(Revoking {$this->Id})");

		static $dbaDB;
		static $qryQuery;
		$qryQuery = (isset($qryQuery)) ? $qryQuery : new Query();
		$dbaDB = (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();

		// Revoke Invoice
		try {
			// Start Transaction
			$dbaDB->TransactionStart();

			// Change CDR Statuses back to CDR_RATED
			$updCDRRevoke = self::_preparedStatement('updCDRRevoke');
			if ($updCDRRevoke->Execute(array('invoice_run_id'=>null, 'Status'=>CDR_RATED), $this->toArray()) === false) {
				throw new Exception_Database("DB ERROR: ".$updCDRRevoke->Error());
			}

			// Remove Billing-Time Charges
			$objAccount = new Account(array('Id' => $this->Account), true);
			$arrModules = Billing_Charge::getModules();
			foreach ($arrModules as $intCustomerGroup=>$arrModuleTypes) {
				foreach ($arrModuleTypes as $strModuleType=>$arrModules) {
					foreach ($arrModules as $chgModule) {
						// Revoke charge
						$mixResult = $chgModule->Revoke($this, $objAccount);
					}
				}
			}

			// Remove Plan Charges
			if ($qryQuery->Execute("
				DELETE
				FROM	Charge
				WHERE	ChargeType IN ('PCAD', 'PCAR', 'PCR', 'PDCR')
						AND CreatedBy IS NULL
						AND invoice_run_id = {$this->invoice_run_id}
						AND Account = {$this->Account}
			") === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			}

			// Change Charge Statuses back to CHARGE_APPROVED
			$updChargeRevoke = self::_preparedStatement('updChargeRevoke');
			if ($updChargeRevoke->Execute(array('Status' => CHARGE_APPROVED, 'invoice_run_id' => null), $this->toArray()) === false) {
				throw new Exception_Database("DB ERROR: ".$updChargeRevoke->Error());
			}

			// Remove Invoice Run reference for Adjustments
			$mResult = Query::run("
				UPDATE	adjustment
				SET		invoice_run_id = NULL
				WHERE	account_id = {$this->Account}
						AND invoice_run_id = {$this->invoice_run_id}
			");
			Log::getLog()->log("Un-marked {$mResult} Adjustments");

			// Remove service_total_service Records
			if ($qryQuery->Execute("
				DELETE
				FROM	service_total_service
				WHERE	service_total_id = (
							SELECT	Id
							FROM	ServiceTotal
							WHERE	invoice_run_id = {$this->invoice_run_id}
									AND Account = {$this->Account}
									AND Id = service_total_id
						)
			") === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			}

			// Remove ServiceTotal Records
			if ($qryQuery->Execute("
				DELETE
				FROM	ServiceTotal
				WHERE	invoice_run_id = {$this->invoice_run_id}
						AND Account = {$this->Account}
			") === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			}

			// Remove ServiceTypeTotal Records
			if ($qryQuery->Execute("
				DELETE
				FROM	ServiceTypeTotal
				WHERE	invoice_run_id = {$this->invoice_run_id}
						AND Account = {$this->Account}
			") === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			}

			// Remove Invoice Record
			if ($qryQuery->Execute("
				DELETE
				FROM	Invoice
				WHERE	Id = {$this->Id}
			") === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			}

			// Update the Totals on the InvoiceRun record
			$objInvoiceRun = Invoice_Run::getForId($this->invoice_run_id);
			$objInvoiceRun->calculateTotals();

			// Commit the Transaction
			$dbaDB->TransactionCommit();
		} catch (Exception $eException) {
			$dbaDB->TransactionRollback();
			throw $eException;
		}
	}

	// Commits a Temporary Invoice
	public function commit() {
		static $dbaDB;
		static $qryQuery;
		$qryQuery = (isset($qryQuery)) ? $qryQuery : new Query();
		$dbaDB = (isset($dbaDB)) ? $dbaDB : DataAccess::getDataAccess();

		// Ensure that this is a Temporary Invoice
		if ($this->Status !== INVOICE_TEMP) {
			throw new Exception("Cannot commit Invoice #{$this->Id} because it is not Temporary!");
		}

		try {
			// Start Transaction
			$dbaDB->TransactionStart();

			// Commit the CDRs
			$updCDRsByAccount = self::_preparedStatement('updCDRCommit');
			if ($updCDRsByAccount->Execute(array('Status'=>CDR_INVOICED), array('Account'=>$this->Account, 'invoice_run_id'=>$this->invoice_run_id)) === false) {
				throw new Exception_Database($updCDRsByAccount->Error());
			}

			// Commit the Charges
			$updChargesByAccount = self::_preparedStatement('updChargeCommit');
			if ($updChargesByAccount->Execute(array('Status'=>CHARGE_INVOICED), array('Account'=>$this->Account, 'invoice_run_id'=>$this->invoice_run_id)) === false) {
				throw new Exception_Database($updChargesByAccount->Error());
			}

			//------------------------------ ACCOUNT -----------------------------//
			$objAccount = new Account(array('Id'=>$this->Account), false, true);

			// Update Account.LastBilled to InvoiceRun.BillingDate
			$objAccount->LastBilled = $this->CreatedOn;

			// Update Account.Sample
			$objAccount->Sample = ($objAccount->Sample < 0) ? $objAccount->Sample++ : $objAccount->Sample;
			$objAccount->save();

			//------------------------------ SERVICE -----------------------------//
			// Update Service.discount_start_datetime to NULL
			$strSQL = "
				UPDATE	ServiceTotal
						JOIN service_total_service ON (ServiceTotal.Service = service_total_service.service_total_id)
						JOIN Service ON (Service.Id = service_total_service.service_id)
				SET		Service.discount_start_datetime = NULL,
						cdr_count = NULL,
						cdr_amount = NULL
				WHERE	ServiceTotal.Account = {$this->Account}
						AND invoice_run_id = {$this->invoice_run_id}
			";
			if ($qryQuery->Execute($strSQL) === false) {
				throw new Exception_Database($qryQuery->Error());
			}

			//------------------------------ INVOICE -----------------------------//
			// Determine Invoice Status
			$this->Status = ($this->Balance > 0) ? INVOICE_COMMITTED : INVOICE_SETTLED;

			// Create the Collectable
			$oCollectable = new Collectable();
			$oCollectable->account_id = $this->Account;
			$oCollectable->amount = $this->Total + $this->Tax;
			$oCollectable->balance = $this->amount;
			$oCollectable->created_datetime = Data_Source_Time::currentTimestamp();
			$oCollectable->due_date = $this->DueOn;
			$oCollectable->invoice_id = $this->Id;
			$oCollectable->save();

			// Link the Collectable
			$this->collectable_id = $oCollectable->id;

			// Save
			$this->save();

			// Commit the Transaction
			$dbaDB->TransactionCommit();
		} catch (Exception $eException) {
			$dbaDB->TransactionRollback();
			throw $eException;
		}

	}


	// Exports an Invoice to XML
	public function export() {
		$oInvoiceRun = new Invoice_Run(array('Id'=>$this->invoice_run_id), true);
		$oInvoiceRun->export(array($this->Account));
	}

	public function getCollectableAmount() {
		$aCollectables = Collectable::getForInvoice($this->Id);
		$fAmount = 0;
		foreach ($aCollectables as $oCollectable) {
			$fAmount += $oCollectable->amount;
		}
		return $fAmount;
	}

	public function getCollectableBalance() {
		$aCollectables = Collectable::getForInvoice($this->Id);
		$fBalance = 0;
		foreach ($aCollectables as $oCollectable) {
			$fBalance += $oCollectable->balance;
		}
		return $fBalance;
	}

	//Rounds a number away from zero (positives round up, negatives round down), aka "round half away from zero"
	public static function roundOut($mValue, $iPrecision=0) {
		$fRoundOut = round(abs($mValue), $iPrecision);
		return ($mValue < 0.0) ? 0.0 - $fRoundOut : $fRoundOut;
	}

	public static function getDatasetForAccount($bCountOnly, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null) {
		$aAliases = array(
			'id' => "i.Id",
			'created_on' => "i.CreatedOn",
			'due_on' => "i.DueOn",
			//'new_charges' => "(i.charge_total + i.charge_tax)",
			'new_charges' => "(i.Total + i.Tax)",
			'amount_owing' => "COALESCE(SUM(c.balance), 0)",
			'invoice_run_type_id' => "ir.invoice_run_type_id",
			'invoice_status' => "i.Status",
			'account_id' => "i.Account",
			'invoice_run_id' => "i.invoice_run_id",
			'has_unarchived_cdrs' => "COALESCE((SELECT Id FROM CDR WHERE invoice_run_id = ir.Id AND Account = i.Account LIMIT 1), 0)",
			'invoice_run_status_id' => "ir.invoice_run_status_id"
		);

		if ($bCountOnly) {
			$sSelect = "COUNT(i.Id) AS count";
			$sFrom = " Invoice i
							JOIN InvoiceRun ir ON (ir.Id = i.invoice_run_id)";
			$sOrderBy = "";
			$sLimit = "";
		} else {
			$sFrom = " Invoice i
						JOIN InvoiceRun ir ON (ir.Id = i.invoice_run_id)
						LEFT JOIN collectable c ON (c.invoice_id = i.Id)";
			$aSelectLines = array();
			foreach ($aAliases as $sAlias => $sClause) {
				$aSelectLines[] = "{$sClause} AS {$sAlias}";
			}
			$sSelect = implode(', ', $aSelectLines);
			$sOrderBy = Statement::generateOrderBy($aAliases, get_object_vars($oSort));
			$sLimit = Statement::generateLimit($iLimit, $iOffset);
		}

		$aWhere = Statement::generateWhere($aAliases, get_object_vars($oFilter));
		$sWhere = $aWhere['sClause'];
		$sWhere .= ($sWhere != '' ? '' : '1');

		if (!$bCountOnly) {
			$sWhere .= " GROUP BY i.Id";
		}

		$oSelect = new StatementSelect($sFrom, $sSelect, $sWhere, $sOrderBy, $sLimit);
		if ($oSelect->Execute($aWhere['aValues']) === false) {
			throw new Exception_Database("Failed to get invoice dataset results. ".$oSelect->Error());
		}

		if ($bCountOnly) {
			$aRow = $oSelect->Fetch();
			return $aRow['count'];
		}

		return $oSelect->FetchAll();
	}

	public function hasUnarchivedCDRs() {
		return Invoice_Run::getForId($this->invoice_run_id)->hasUnarchivedCDRsForAccount($this->Account);
	}

	// Prorates a given Charge for a defined period.
	public static function prorate($fltAmount, $intChargeStartDate, $intChargeEndDate, $intPeriodStartDate, $intPeriodEndDate, $strSmallestDenomination=DATE_TRUNCATE_DAY, $bolAllowOverflow=true, $intDecimalPlaces=2) {
		$intProratePeriod = Flex_Date::periodLength($intChargeStartDate, $intChargeEndDate, $strSmallestDenomination);
		$intBillingPeriod = Flex_Date::periodLength($intPeriodStartDate, $intPeriodEndDate, $strSmallestDenomination);

		$iProratePeriodDays = floor($intProratePeriod / Flex_Date::SECONDS_IN_DAY);
		$iBillingPeriodDays = floor($intBillingPeriod / Flex_Date::SECONDS_IN_DAY);
		/*
		Log::getLog()->log("Prorating Charge Start Date: ".date("Y-m-d H:i:s", $intChargeStartDate));
		Log::getLog()->log("Prorating Charge End Date: ".date("Y-m-d H:i:s", $intChargeEndDate));
		Log::getLog()->log("Prorating Period Start Date: ".date("Y-m-d H:i:s", $intPeriodStartDate));
		Log::getLog()->log("Prorating Period End Date: ".date("Y-m-d H:i:s", $intPeriodEndDate));
		Log::getLog()->log("Prorating Period Length (days): {$iProratePeriodDays}");
		Log::getLog()->log("Billing Period Length (days): {$iBillingPeriodDays}");
		*/
		$aArguments = func_get_args();
		Flex::assert(($iBillingPeriodDays > 0), "Invoice Billing Period length in days is not greater than 0", print_r(array('charge-date-start'=>date("Y-m-d H:i:s", $intChargeStartDate), 'charge-date-end'=>date("Y-m-d H:i:s", $intChargeEndDate), 'period-date-start'=>date("Y-m-d H:i:s", $intPeriodStartDate), 'period-date-end'=>date("Y-m-d H:i:s", $intPeriodEndDate), 'prorate-period-days'=>$iProratePeriodDays, 'billing-period-days'=>$iBillingPeriodDays, 'arguments' => $aArguments), true), "Invoice Prorating: Invalid Billing Period");
		if ($iProratePeriodDays) {
			$fltProratedAmount = ($fltAmount / $iBillingPeriodDays) * $iProratePeriodDays;
			//Log::getLog()->log("{$fltProratedAmount}\t= ({$fltAmount} / {$iBillingPeriodDays}) * {$iProratePeriodDays}");

			$fltProratedAmount = ($intDecimalPlaces === null) ? $fltProratedAmount : round($fltProratedAmount, $intDecimalPlaces);
			return $fltProratedAmount;
		} else {
			return 0.0;
		}
	}

	// Calculates the Global Tax Component for a given amount, and returns it
	public static function calculateGlobalTaxComponent($fltAmount, $strEffectiveDate) {
		static $arrGlobalTax;
		if (!isset($arrGlobalTax)) {
			$qryQuery = new Query();
			$resResult = $qryQuery->Execute("
				SELECT	*
				FROM	tax_type
				WHERE	global = 1
						AND '{$strEffectiveDate}' BETWEEN start_datetime AND end_datetime
			");
			if ($resResult->num_rows) {
				$arrGlobalTax = $resResult->fetch_assoc();
			} elseif ($qryQuery->Error()) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			} else {
				$arrGlobalTax = array('rate_percentage' => 0.0);
			}
		}

		return $fltAmount * $arrGlobalTax['rate_percentage'];
	}

	// Adds Plan Charges for a given Service, or group of Shared Services
	private function _addPlanCharges($arrPlanDetails, $arrServices, $intPrimaryService=null, $oInvoiceSource) {
		static $qryQuery;
		$qryQuery = (isset($qryQuery)) ? $qryQuery : new Query();
		$strServiceIds = implode(', ', self::_extractServiceIds($arrServices));

		// If this Plan requires CDRs, get the Earliest CDR Details (ensuring that the earliest date is not after the Invoice date)
		$strEarliestCDR = null;
		if ($arrPlanDetails['cdr_required']) {
			$resResult = $qryQuery->Execute("
				SELECT	MIN(EarliestCDR) AS EarliestCDR
				FROM	Service
				WHERE	Id IN ({$strServiceIds})
			");
			if ($resResult === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			}
			$arrMinEarliestCDR = $resResult->fetch_assoc();
			$strEarliestCDR = ($arrMinEarliestCDR['EarliestCDR'] !== null && strtotime($arrMinEarliestCDR['EarliestCDR']) < $this->intInvoiceDatetime) ? $arrMinEarliestCDR['EarliestCDR'] : null;
		} else {
			Log::getLog()->log("Non-CDR Service");

			// Otherwise use the Service Creation Date
			$resResult = $qryQuery->Execute("
				SELECT	MIN(CreatedOn) AS EarliestCreatedOn
				FROM	Service
				WHERE	Id IN ({$strServiceIds})
			");
			if ($resResult === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			}
			$arrMinCreatedOn = $resResult->fetch_assoc();

			$strPlanStartDatetime = $arrPlanDetails['EarliestStartDatetime'];
			$strServiceCreatedOn = $arrMinCreatedOn['EarliestCreatedOn'];

			$strEarliestCDR = date("Y-m-d", strtotime(max($strPlanStartDatetime, $strServiceCreatedOn)));

			if ($strPlanStartDatetime > $strServiceCreatedOn) {
				Log::getLog()->log("Earliest CDR comes from Plan Date");
			} elseif ($strPlanStartDatetime < $strServiceCreatedOn) {
				Log::getLog()->log("Earliest CDR comes from Creation Date");
			} else {
				Log::getLog()->log("Earliest CDR comes from shared Plan Date/Creation Date");
			}
		}
		$strEarliestCDR = ($strEarliestCDR !== null && strtotime($strEarliestCDR) < $this->intInvoiceDatetime) ? $strEarliestCDR : null;

		// Default Arrears Period
		$intArrearsPeriodStart = $this->intLastInvoiceDatetime;
		$intArrearsPeriodEnd = strtotime("-1 day", $this->intInvoiceDatetime);

		// Is the Service tolling?
		$intLevel = 0;
		Log::getLog()->log("Earliest CDR: {$strEarliestCDR}");
		if ($strEarliestCDR) {
			$fltMinimumCharge = (float)coalesce($arrPlanDetails['min_monthly_override'], $arrPlanDetails['MinMonthly']);

			// Scalable Plans
			if ($arrPlanDetails['Shared']) {
				$intScalable = (int)$arrPlanDetails['scalable'];
				$intMinServices = (int)$arrPlanDetails['minimum_services'];
				$intMaxServices = (int)$arrPlanDetails['maximum_services'];
				if ($intScalable && $intMinServices > 0 && $intMaxServices >= $intMinServices) {
					Log::getLog()->log("Scaling Plan Charges & Usage: {$strEarliestCDR}");

					Log::getLog()->log("Native Plan Charge: {$fltMinimumCharge}");
					$fltMinimumCharge = ($fltMinimumCharge / $intMaxServices) * max($intMaxServices, count($arrServices));
					Log::getLog()->log("Scaled Plan Charge: {$fltMinimumCharge}");
				}
			}

			$arrPlanChargeSteps = array();

			// Yes -- Does this Service have any Invoiced CDRs (or Plan Charges for non-CDR Plans)?
			if ($arrPlanDetails['cdr_required']) {
				$strSQL = "
					SELECT	1
					FROM	ServiceTypeTotal
					WHERE	Service IN ({$strServiceIds})
							AND Records > 0
							AND invoice_run_id != {$this->invoice_run_id}
					";
			} else {
				$strSQL = "
					(
						SELECT	'Plan Charge' AS Matches
						FROM	Charge
						WHERE	Account = {$this->Account}
								AND (
									Service IN ({$strServiceIds})
									OR (
										{$arrPlanDetails['Shared']} = 1
										AND Service IS NULL
									)
								)
								AND ChargeType IN ('PCAR', 'PCAD', 'PCR', 'PDCR')
								AND Status = ".CHARGE_INVOICED."
						LIMIT	1
					) UNION (
						SELECT	'CDR Data' AS Matches
						FROM	ServiceTotal st
								JOIN ServiceTypeTotal stt ON (
									stt.Service = st.Service
									AND stt.invoice_run_id = st.invoice_run_id
								)
								JOIN InvoiceRun ir ON (ir.Id = st.invoice_run_id)
								JOIN service_total_service sts ON (sts.service_total_id = st.Id)
						WHERE	(
									(
										UncappedCost > 0
										OR CappedCost > 0
									)
									OR stt.Id IS NOT NULL
								)
								AND sts.service_id IN ({$strServiceIds})
								AND ir.BillingDate < '{$this->_objInvoiceRun->BillingDate}'
						LIMIT	1
					)
				";
			}
			$resResult = $qryQuery->Execute($strSQL);
			if ($resResult === false) {
				throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
			} elseif (!$resResult->num_rows) {
				$bolFirstInvoice = true;
				$arrPlanChargeSteps[] = 'FIRST INVOICE';

				// Alternate Arrears Period
				$intArrearsPeriodStart = strtotime($strEarliestCDR);
			} else {
				$bolFirstInvoice = false;
			}

			// If the Plan is charged in Advance, then check if we have charged a PCAD yet
			$bHasChargedInAdvance = false;
			$bHasInvoicedOnAnotherPlan = false;
			if ($arrPlanDetails['InAdvance']) {
				$rResult = $qryQuery->Execute("
					SELECT	*
					FROM	Charge
					WHERE	ChargeType = 'PCAD'
							AND Account = {$this->Account}
							AND (
								Service IN ({$strServiceIds})
								OR (
									{$arrPlanDetails['Shared']} = 1
									AND Service IS NULL
								)
							)
							AND Status = ".CHARGE_INVOICED."
							AND invoice_run_id != {$this->_objInvoiceRun->Id}
							AND ChargedOn < '{$this->_objInvoiceRun->BillingDate}'
					LIMIT	1
				");
				if ($rResult === false) {
					throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
				} elseif ($rResult->num_rows) {
					$bHasChargedInAdvance = true;
				} else {
					// Check if the Service(s) have been invoiced on another Plan before
					$oResult = $qryQuery->Execute("
						SELECT	st.RatePlan
						FROM	ServiceTotal st
								JOIN InvoiceRun ir ON (ir.Id = st.invoice_run_id)
						WHERE	st.Service IN ({$strServiceIds})
								AND ir.BillingDate < '{$this->_objInvoiceRun->BillingDate}'
								AND st.RatePlan != {$arrPlanDetails['Id']}
						LIMIT	1
					");
					if ($oResult === false) {
						throw new Exception_Database("DB ERROR: ".$qryQuery->Error());
					} elseif ($oResult->num_rows) {
						$bHasInvoicedOnAnotherPlan = true;
					}
				}
				Log::getLog()->log("Have ".(($bHasChargedInAdvance) ? '' : 'not ')."charged in advanced before");
				Log::getLog()->log("Have ".(($bHasInvoicedOnAnotherPlan) ? '' : 'not ')."invoiced on another Plan before");
			}

			Log::getLog()->log("Arrears period start: ".date("Y-m-d H:i:s", $intArrearsPeriodStart));

			// Charge In Advance (only if this is not an interim Invoice Run)
			if ($arrPlanDetails['InAdvance'] && !$oInvoiceSource->isAnInterimInvoiceRun($this->_objInvoiceRun)) {
				$arrPlanChargeSteps[] = ($bolFirstInvoice) ? 'FIRST_ADVANCE' : 'NORMAL_ADVANCE';

				$strChargeType = 'PCAD';
				$intPeriodStart = $this->intInvoiceDatetime;
				$intPeriodEnd = strtotime("-1 day", strtotime("+1 month", $this->intInvoiceDatetime));
				$this->_addPlanCharge($strChargeType, $fltMinimumCharge, $arrPlanDetails, $intPeriodStart, $intPeriodEnd, $this->_objAccount->AccountGroup, $this->_objAccount->Id, $intPrimaryService);
			}

			// Charge in Arrears
			if (!$arrPlanDetails['InAdvance'] || (!$bHasChargedInAdvance && !$bHasInvoicedOnAnotherPlan)) {
				$arrPlanChargeSteps[] = ($bolFirstInvoice) ? 'FIRST_ARREARS' : 'NORMAL_ARREARS';

				// Prorate the Charges and Usage details in Arrears
				$fltMinimumCharge = Invoice::prorate($fltMinimumCharge , $intArrearsPeriodStart , $intArrearsPeriodEnd , $this->intProratePeriodStart, $this->intProratePeriodEnd);

				$strChargeType = 'PCAR';
				$intPeriodStart = $intArrearsPeriodStart;
				$intPeriodEnd = $intArrearsPeriodEnd;
				$this->_addPlanCharge($strChargeType, $fltMinimumCharge, $arrPlanDetails, $intPeriodStart, $intPeriodEnd, $this->_objAccount->AccountGroup, $this->_objAccount->Id, $intPrimaryService);
			}
		} else {
			$arrPlanChargeSteps[] = 'NEVER_TOLLED';

			// No -- ignore all Plan Charges, because we haven't tolled yet
			$fltMinimumCharge = 0.0;
		}
		// DEBUG
		Log::getLog()->log(print_r($arrPlanChargeSteps, true));

		// Return usage data
		return array(
			'MinMonthly' => $fltMinimumCharge,

			'ArrearsPeriodStart' => $intArrearsPeriodStart,
			'ArrearsPeriodEnd' => $intArrearsPeriodEnd
		);
	}

	// Adds a Plan Charge to a given Account or Service
	private function _addPlanCharge($strChargeType, $fltAmount, $arrPlanDetails, $intPeriodStartDate, $intPeriodEndDate, $intAccountGroup, $intAccount, $intService=null) {
		static $selChargeType;
		static $arrChargeTypes;

		// If the Plan Charge is $0, then don't insert it
		if (round($fltAmount, 2) === 0.0) {
			//CliEcho("Ignoring {$strPlanName} {$strChargeType}");
			return true;
		}
		//CliEcho("Adding {$strPlanName} {$strChargeType} worth \${$fltAmount}");

		// Cache ChargeType details
		if (!isset($arrChargeTypes[$strChargeType])) {
			$selChargeType = ($selChargeType) ? $selChargeType : new StatementSelect("ChargeType", "*", "ChargeType = <ChargeType>");
			if ($selChargeType->Execute(array('ChargeType' => $strChargeType))) {
				$arrChargeTypes[$strChargeType] = $selChargeType->Fetch();
			} elseif ($selChargeType->Error()) {
				throw new Exception_Database("DB ERROR: ".$selChargeType->Error());
			} else {
				throw new Exception("Unable to retrieve details for ChargeType '{$strChargeType}'!");
			}
		}

		// Charge Description
		$strDescription = $arrChargeTypes[$strChargeType]['Description'];

		// Generate Charge
		$arrPlanCharge = array();
		$arrPlanCharge['AccountGroup'] = $intAccountGroup;
		$arrPlanCharge['Account'] = $intAccount;
		$arrPlanCharge['Service'] = $intService;
		$arrPlanCharge['Nature'] = $arrChargeTypes[$strChargeType]['Nature'];
		$arrPlanCharge['ChargeType'] = $strChargeType;
		$arrPlanCharge['charge_type_id'] = $arrChargeTypes[$strChargeType]['Id'];
		$arrPlanCharge['global_tax_exempt'] = 0;
		$arrPlanCharge['Description'] = self::buildPlanChargeDescription($arrPlanDetails['Name'], $strDescription, $intPeriodStartDate, $intPeriodEndDate);
		$arrPlanCharge['ChargedOn'] = date("Y-m-d");
		$arrPlanCharge['Amount'] = abs($fltAmount);
		$arrPlanCharge['invoice_run_id'] = $this->invoice_run_id;
		$arrPlanCharge['Status'] = CHARGE_TEMP_INVOICE;
		if (!$GLOBALS['fwkFramework']->AddCharge($arrPlanCharge)) {
			throw new Exception("Unable to create '{$arrPlanCharge['Description']}' for {$intAccount}::{$intService}!");
		}

		Log::getLog()->log("Added Charge: \n".print_r($arrPlanCharge, true));

		return true;
	}

	public static function buildPlanChargeDescription($sPlanName, $sChargeDescription, $iStartDatetime, $iEndDatetime) {
		return "{$sPlanName} {$sChargeDescription} from ".date("d/m/Y", $iStartDatetime)." to ".date("d/m/Y", $iEndDatetime);
	}

	private function _calculateDiscount($oDiscount, $aPlanDetails, $aServices, $iArrearsPeriodStart, $iArrearsPeriodEnd, $iPrimaryServiceId=null) {
		static $oQuery, $oChargeTypePCR;
		$oQuery = ($oQuery) ? $oQuery : new Query();
		$oChargeTypePCR = ($oChargeTypePCR) ? $oChargeTypePCR : Charge_Type::getByCode('PCR');

		$sServices = implode(', ', self::_extractServiceIds($aServices));

		Log::getLog()->log("\nDiscount: ".$oDiscount->name);

		$fChargeLimit = max($oDiscount->charge_limit, 0);
		$iUnitLimit = max($oDiscount->unit_limit, 0);

		if ($iUnitLimit) {
			$sDiscountType = Discount::DISCOUNT_TYPE_UNITS;
			$mDiscountLimit = $iUnitLimit;
		} else {
			$sDiscountType = Discount::DISCOUNT_TYPE_CHARGE;
			$mDiscountLimit = $fChargeLimit;
		}

		// Scalable Shared Discounts
		$mScaledDiscountLimit = $mDiscountLimit;
		if ($aPlanDetails['Shared']) {
			$iScalable = (int)$aPlanDetails['scalable'];
			$iMinServices = (int)$aPlanDetails['minimum_services'];
			$iMaxServices = (int)$aPlanDetails['maximum_services'];
			if ($iScalable && $iMinServices > 0 && $iMaxServices >= $iMinServices) {
				$mScaledDiscountLimit = ($mDiscountLimit / $iMaxServices) * max($iMaxServices, count($aServices));
				Log::getLog()->log("Scaled! (Full Discount: {$mDiscountLimit}; Scaled Discount: {$mScaledDiscountLimit})");
			}
		}

		$mProratedDiscountLimit = self::prorate($mScaledDiscountLimit, $iArrearsPeriodStart, $iArrearsPeriodEnd, $this->intProratePeriodStart, $this->intProratePeriodEnd, DATE_TRUNCATE_DAY, true);
		$mProratedDiscountLimit = ($sDiscountType === Discount::DISCOUNT_TYPE_UNITS) ? round($mProratedDiscountLimit) : $mProratedDiscountLimit;

		Log::getLog()->log("Prorated Discount Limit: {$mProratedDiscountLimit}");
		Log::getLog()->log("Discount Limit: {$mDiscountLimit}");
		Log::getLog()->log("Discount Limit Type: ".(($iUnitLimit) ? 'UNITS' : 'CHARGE'));

		// If there is a valid Discount to apply...
		$fDiscountApplied = 0.0;
		$fDiscountTaxOffset = 0.0;
		if ($mDiscountLimit) {
			// Get the RecordTypes associated with this Discount
			$aRecordTypes = $oDiscount->getRecordTypes();
			$sRecordTypes = implode(', ', array_keys($aRecordTypes));

			if ($sRecordTypes) {
				// Get all CDRs for the eligible RecordTypes which are not excluded from the Cap
				$sIncludedUsage = "
					SELECT		cdr.Units,
								cdr.Charge,
								cdr.Credit,
								rt.global_tax_exempt
					FROM		CDR cdr
								JOIN Rate r ON (r.Id = cdr.Rate)
								JOIN RecordType rt ON (rt.Id = cdr.RecordType)
					WHERE		cdr.Service IN ({$sServices})
								AND cdr.invoice_run_id = {$this->invoice_run_id}
								AND r.Uncapped = 0
								AND cdr.RecordType IN ($sRecordTypes)
					ORDER BY	cdr.StartDatetime,
								cdr.Id
				";
				Log::getLog()->log("Included Usage Query: $sIncludedUsage");

				$oResult = $oQuery->Execute($sIncludedUsage);
				if ($oResult === false) {
					throw new Exception_Database("DB ERROR: ".$oQuery->Error());
				}

				// If there are any CDRs
				if ($oResult->num_rows) {
					// Get ServiceTypeTotals
					$oServiceTypeTotalsResult = DataAccess::get()->query('
						SELECT stt.Service AS service_id,
							stt.FNN AS service_fnn,
							stt.RecordType AS record_type_id,
							stt.Charge AS total_charge_rounded,
							SUM(
								CASE WHEN c.Credit = 1 THEN
									0 - c.Charge
								ELSE
									c.Charge
								END
							) AS total_charge_unrounded,
							SUM(IF(r.Uncapped = 1,
								CASE WHEN c.Credit = 1 THEN
									0 - c.Charge
								ELSE
									c.Charge
								END,
								0
							)) AS total_undiscounted
						FROM ServiceTypeTotal stt
							JOIN CDR c ON (
								c.RecordType = stt.RecordType
								AND c.Service = stt.Service
								AND c.FNN = stt.FNN
							)
							JOIN Rate r ON (r.Id = c.Rate)
						WHERE stt.invoice_run_id = <invoice_run_id>
							AND stt.Service IN (<service_ids>)
							AND stt.RecordType IN (<record_type_ids>)
					', array(
						'rounding_precision_multiplier' => pow(10, Rate::RATING_PRECISION),
						'invoice_run_id' => $this->invoice_run_id,
						'service_ids' => self::_extractServiceIds($aServices),
						'record_type_ids' => array_keys($aRecordTypes)
					));
					$aServiceTypeTotals = array();
					while ($aServiceTypeTotal = $oServiceTypeTotalsResult->fetch_assoc()) {
						if (!isset($aServiceTypeTotals[$aServiceTypeTotal['Service']])) {
							$aServiceTypeTotals[$aServiceTypeTotal['Service']] = array();
						}
						if (!isset($aServiceTypeTotals[$aServiceTypeTotal['Service']][$aServiceTypeTotal['FNN']])) {
							$aServiceTypeTotals[$aServiceTypeTotal['Service']][$aServiceTypeTotal['FNN']] = array();
						}
						$aServiceTypeTotals[$aServiceTypeTotal['Service']][$aServiceTypeTotal['FNN']][$aServiceTypeTotal['RecordType']] = $aServiceTypeTotal;
						$aServiceTypeTotals[$aServiceTypeTotal['Service']][$aServiceTypeTotal['FNN']][$aServiceTypeTotal['RecordType']]['totalDiscount'] = 0.0;
						$aServiceTypeTotals[$aServiceTypeTotal['Service']][$aServiceTypeTotal['FNN']][$aServiceTypeTotal['RecordType']]['totalDiscountTaxOffset'] = 0.0;
					}

					$iTotalUnits = 0;
					$fTotalCharge = 0.0;
					$fTaxOffsetUnrounded = 0.0;
					$fTotalCreditUnrounded = 0.0;

					$mRemainingDiscount = $mProratedDiscountLimit;
					while ($aDataCDR = $oResult->fetch_assoc()) {
						$iUnits = ($aDataCDR['Credit']) ? 0 - $aDataCDR['Units'] : $aDataCDR['Units'];
						$fCharge = ($aDataCDR['Credit']) ? 0 - $aDataCDR['Charge'] : $aDataCDR['Charge'];

						$iTotalUnits += $iUnits;
						$fTotalCharge += $fCharge;

						// If we haven't gone over our limit yet
						if ($mRemainingDiscount > 0.0) {
							switch ($sDiscountType) {
								case Discount::DISCOUNT_TYPE_UNITS:
									$mRemainingDiscount -= $iUnits;
									if ($mRemainingDiscount < 0) {
										// Prorate the last CDR (assumes a consistent rate per unit)
										$fRatePerUnit = ($iUnits) ? ($fCharge / $iUnits) : 0;
										$fCharge -= (abs($mRemainingDiscount) * $fRatePerUnit);
									}
									break;

								case Discount::DISCOUNT_TYPE_CHARGE:
									$mRemainingDiscount -= $fCharge;
									if ($mRemainingDiscount < 0) {
										// Prorate the last CDR
										$fCharge -= abs($mRemainingDiscount);
									}
									break;
							}

							// Add a global tax offset against the credited CDR
							if (!$aDataCDR['global_tax_exempt']) {
								$fCDRTaxOffset = self::calculateGlobalTaxComponent($fCharge, $this->intInvoiceDatetime);
								$aServiceTypeTotals[$aDataCDR['Service']][$aDataCDR['FNN']][$aDataCDR['RecordType']]['totalDiscountTaxOffset'] -= $fCDRTaxOffset;
								$fTaxOffsetUnrounded -= $fCDRTaxOffset;
							}

							$aServiceTypeTotals[$aDataCDR['Service']][$aDataCDR['FNN']][$aDataCDR['RecordType']]['totalDiscount'] += $fCharge;
							$fTotalCreditUnrounded += $fCharge;
						}
					}
					$mTotalUsage = ($iUnitLimit) ? $iTotalUnits : $fTotalCharge;

					// Sum the rounded discount subtotals for each ServiceTypeTotal, ensuring it doesn't exceed the maximum discount allowance
					$fSummedTotalCredit = 0.0;
					$fSummedTaxOffset = 0.0;
					foreach ($aServiceTypeTotals as $aServiceTypeTotalFNNs) {
						foreach ($aServiceTypeTotalFNNs as $aServiceTypeTotalFNNRecordTypes) {
							foreach ($aServiceTypeTotalFNNRecordTypes as $aServiceTypeTotalFNNRecordType) {
								$fSummedTotalCredit += Rate::roundToRatingStandard($aServiceTypeTotalFNNRecordType['totalDiscount']);
								$fSummedTaxOffset += Rate::roundToRatingStandard($aServiceTypeTotalFNNRecordType['totalDiscountTaxOffset']);
							}
						}
					}
					if ($fSummedTotalCredit > 0) {
						$fTotalCredit = min($fSummedTotalCredit, $mProratedDiscountLimit); // Total Credit can't exceed the maximum allowable discount
						$fTaxOffset = Rate::roundToRatingStandard(($fTotalCredit / $fSummedTotalCredit) * $fSummedTaxOffset); // Adjust the tax offset relative to the total credit
					} else {
						$fTotalCredit = 0;
						$fTaxOffset = 0;
					}

					Log::getLog()->log("Total Usage Units: {$iTotalUnits}");
					Log::getLog()->log("Total Usage Charge: \${$fTotalCharge}");
					switch ($sDiscountType) {
						case Discount::DISCOUNT_TYPE_UNITS:
							Log::getLog()->log("Prorated Discount Limit: {$mProratedDiscountLimit} Units");
							Log::getLog()->log("Usage Included in Discount: ".($mProratedDiscountLimit - max(0, $mRemainingDiscount))." Units");
							Log::getLog()->log("Overusage: ".(max(0, $mTotalUsage - $mProratedDiscountLimit - max(0, $mRemainingDiscount)))." Units");
							break;

						case Discount::DISCOUNT_TYPE_CHARGE:
							Log::getLog()->log("Prorated Discount Limit: \${$mProratedDiscountLimit}");
							Log::getLog()->log("Usage Included in Discount: \$".($mProratedDiscountLimit - max(0, $mRemainingDiscount)));
							Log::getLog()->log("Overusage: \$".(max(0, $mTotalUsage - $mProratedDiscountLimit - max(0, $mRemainingDiscount))));
							break;
					}

					Log::getLog()->log("Creditback (unrounded): \${$fTotalCreditUnrounded}");
					Log::getLog()->log("Tax Offset (unrounded): \${$fTaxOffsetUnrounded}");
					Log::getLog()->log("Overusage Charge (unrounded): \$".($fTotalCharge - $fTotalCreditUnrounded));
					Log::getLog()->log("Creditback: \${$fTotalCredit}");
					Log::getLog()->log("Tax Offset: \${$fTaxOffset}");
					Log::getLog()->log("Overusage Charge: \$".($fTotalCharge - $fTotalCreditUnrounded));

					if ($mTotalUsage > 0) {
						// Add the Credit
						$oCharge = new Charge();
						$oCharge->AccountGroup = $this->_objAccount->AccountGroup;
						$oCharge->Account = $this->Account;
						$oCharge->Service = $iPrimaryServiceId;
						$oCharge->Nature = 'CR';
						$oCharge->ChargeType = $oChargeTypePCR->ChargeType;
						$oCharge->charge_type_id = $oChargeTypePCR->Id;
						$oCharge->global_tax_exempt = 1;
						$oCharge->Description = self::buildPlanChargeDescription($aPlanDetails['Name'], "{$oDiscount->name} Discount", $iArrearsPeriodStart, $iArrearsPeriodEnd);
						$oCharge->CreatedOn = date("Y-m-d");
						$oCharge->ChargedOn = date("Y-m-d");
						$oCharge->Amount = abs($fTotalCredit);
						$oCharge->Notes = '';
						$oCharge->invoice_run_id = $this->invoice_run_id;
						$oCharge->Status = CHARGE_TEMP_INVOICE;
						$oCharge->save();

						$fDiscountApplied = $fTotalCredit;
						$fDiscountTaxOffset = $fTaxOffset;
					} else {
						// Net Credit (or $0.00) -- no Discounting!
						$fDiscountApplied = 0.0;
						$fDiscountTaxOffset = 0.0;
					}
				} else {
					Log::getLog()->log("No CDRs - Skipping Discounting!");
				}
			}
		}

		return array(
			'fDiscountApplied' => $fDiscountApplied,
			'fDiscountTaxOffset' => $fDiscountTaxOffset
		);
	}

	private static function _extractServiceIds($arrServices) {
		$arrServiceIds = array();
		foreach ($arrServices as $arrService) {
			foreach ($arrService['Ids'] as $intServiceId) {
				$arrServiceIds[] = $intServiceId;
			}
		}
		return $arrServiceIds;
	}

	// Resolve a Dispute on the invoice
	public function resolve($iResolveMethod, $fAmount) {
		$iUserId = Flex::getUserId();

		// Check that the invoice is currently in dispute
		if ($this->Status <> INVOICE_DISPUTED) {
			throw new Exception('Invoice Not Disputed');
		}

		// The status that will be added to the Note
		$sStatus = "Resolution for Dispute on Invoice #".$this->Id."\n";

		// Used to save a new credit, if necessary
		$oCharge = false;

		switch ($iResolveMethod) {
			case DISPUTE_RESOLVE_FULL_PAYMENT:
				// If the full amount is required to be paid (for example, Dispute was Denied)
				$sStatus .= "No Credit was applied to this Dispute. ";
				$sStatus .= "The Customer is required to pay the full Amount.";
				break;

			case DISPUTE_RESOLVE_PARTIAL_PAYMENT:
				// If a payment is required for a particular amount of a Dispute
				// Generate a credit for Invoice.Disputed - $fltAmount
				$oCharge = new Charge();
				$oCharge->AccountGroup = $this->AccountGroup;
				$oCharge->Account = $this->Account;
				$oCharge->Service = null;
				$oCharge->invoice_run_id = null;
				$oCharge->CreatedBy = $iUserId;
				$oCharge->CreatedOn = date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
				$oCharge->ApprovedBy = $iUserId;
				$oCharge->ChargeType = '';
				$oCharge->Description = 'Invoice Dispute (Invoice: #'.$this->Id.')';
				$oCharge->ChargedOn = date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
				$oCharge->Nature = NATURE_CR;
				$oCharge->Amount = $this->Disputed - $fAmount;
				$oCharge->Status = CHARGE_APPROVED;

				$sStatus .= "This dispute was resolved by partial payment. ";
				$sStatus .= "The Customer is required to pay the amount of $".sprintf("%01.4f", $fAmount).". ";
				$sStatus .= "The Original disputed amount was: $".sprintf("%01.4f", $this->Disputed).". ";
				$sStatus .= "The remaining amount of $".sprintf("%01.4f", $this->Disputed - $fAmount)." ";
				$sStatus .= "was Credited towards this Account.";
				break;

			case DISPUTE_RESOLVE_NO_PAYMENT:
				// Generate a credit for Invoice.Disputed
				$oCharge = new Charge();
				$oCharge->AccountGroup = $this->AccountGroup;
				$oCharge->Account = $this->Account;
				$oCharge->Service = null;
				$oCharge->invoice_run_id = null;
				$oCharge->CreatedBy = $iUserId;
				$oCharge->CreatedOn = date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
				$oCharge->ApprovedBy = $iUserId;
				$oCharge->ChargeType = '';
				$oCharge->Description = 'Invoice Dispute (Invoice: #'.$this->Id.')';
				$oCharge->ChargedOn = date('Y-m-d', DataAccess::getDataAccess()->getNow(true));
				$oCharge->Nature = NATURE_CR;
				$oCharge->Amount = $this->Disputed;
				$oCharge->Status = CHARGE_WAITING;

				$sStatus .= "The full amount of the the dispute ($".sprintf("%01.4f", $this->Disputed).") ";
				$sStatus .= "was Credited towards this Account.";
				break;

			default:
				throw new Exception('Invalid Resolution');
		}

		if ($oCharge) {
			// Not given, default value applied
			$oCharge->Notes = '';
			$oCharge->global_tax_exempt = 0;

			// Save the new charge
			$oCharge->save();
		}

		// Invoice.Disputed = 0
		// if Balance > 0 Status = INVOICE_COMMITTED
		// else Status = INVOICE_SETTLED
		$this->Disputed = 0;
		$this->Status = ($this->Balance > 0) ? INVOICE_COMMITTED : INVOICE_SETTLED;
		$this->save();

		// Add a note
		Note::createNote(SYSTEM_NOTE_TYPE, $sStatus, $iUserId, $this->Account);
	}

	public static function regenerate($oOriginalInvoice, $oFinishedCallback=null) {
		$oDA = DataAccess::getDataAccess();
		$oDA->TransactionStart();
		try {
			Log::getLog()->log("START: Regenerating Invoice - {$oOriginalInvoice->Id}");

			Log::getLog()->log("Fetching Invoice Run - {$oOriginalInvoice->invoice_run_id}");

			// Retrieve the invoice run the original invoice was a part of, used to help create the new invoice run
			$oOriginalInvoiceRun = Invoice_Run::getForId($oOriginalInvoice->invoice_run_id);

			Log::getLog()->log("Creating new invoice run (copy of {$oOriginalInvoice->invoice_run_id})");

			// Create a new invoice run to encapsulate the regeneration (notice the empty accounts array so that the run is created with no invoices)
			$iBillingDate = strtotime($oOriginalInvoiceRun->BillingDate);
			$oInvoiceRun = new Invoice_Run();
			$oInvoiceRun->calculateBillingPeriodDates(date("Y-m-d", $iBillingDate), $oOriginalInvoice->Account);
			$oInvoiceRun->generate($oOriginalInvoice->customer_group_id, INVOICE_RUN_TYPE_RERATE, $iBillingDate, array());

			Log::getLog()->log("Setting status of new invoice run to INVOICE_RUN_STATUS_GENERATING");

			$oInvoiceRun->invoice_run_status_id = INVOICE_RUN_STATUS_GENERATING;
			$oInvoiceRun->save();

			$oQuery = new Query();

			Log::getLog()->log("Copying Charge records from the original invoice, setting status to CHARGE_APPROVED (".CHARGE_APPROVED.")");

			// Copy all (NON Billing time charges/discounts) charges from the original invoice and changing the invoice_run_id
			// Selecting these charges is done by checking that ChargedOn = the billing date of the original invoice run
			$mChargeResult = $oQuery->Execute("
				INSERT INTO Charge
					(AccountGroup, Account, Service, invoice_run_id, CreatedBy, CreatedOn, ApprovedBy, ChargeType, charge_type_id, Description, ChargedOn, Nature, Amount, Invoice, Notes, LinkType, LinkId, Status, global_tax_exempt, charge_model_id)
				SELECT	AccountGroup,
						Account,
						Service,
						{$oInvoiceRun->Id},
						CreatedBy,
						CreatedOn,
						ApprovedBy,
						ChargeType,
						charge_type_id,
						Description,
						ChargedOn,
						Nature,
						Amount,
						Invoice,
						Notes,
						LinkType,
						LinkId,
						".CHARGE_APPROVED.",
						global_tax_exempt,
						charge_model_id
				FROM	Charge
				WHERE	Account = {$oOriginalInvoice->Account}
						AND invoice_run_id = {$oOriginalInvoiceRun->Id}
						AND ChargedOn < '{$oOriginalInvoiceRun->BillingDate}'
						AND Status = ".CHARGE_INVOICED."
			");
			if ($mChargeResult === false) {
				throw new Exception_Database("Failed to copy Charge records. ".$oQuery->Error());
			}

			Log::getLog()->log("... ".$oQuery->AffectedRows()." records copied");
			Log::getLog()->log("Copying adjustment records from the original invoice, ");

			// Copy all adjustments from the invoice_run for the account
			$iAdjustmentRows = Query::run("
				UPDATE	adjustment
				SET		invoice_run_id = NULL
				WHERE	account_id = {$oOriginalInvoice->Account}
						AND invoice_run_id = {$oOriginalInvoiceRun->Id}
						AND effective_date <= '{$oOriginalInvoiceRun->BillingDate}'
						AND reviewed_datetime <= '{$oOriginalInvoiceRun->BillingDate}'
			");

			Log::getLog()->log("... {$iAdjustmentRows} records copied");
			Log::getLog()->log("Copying CDR records from the original invoice, setting status to CDR_RATED (".CDR_RATED.") and changing the invoice_run_id");

			// Copy all CDRs from the original invoice
			$mCDRResult = $oQuery->Execute("
				INSERT INTO CDR
					(FNN, File, Carrier, CarrierRef, Source, Destination, StartDatetime, EndDatetime, Units, AccountGroup, Account, Service, Cost, Status, CDR, Description, DestinationCode, RecordType, ServiceType, Charge, Rate, NormalisedOn, RatedOn, invoice_run_id, SequenceNo, Credit)
				SELECT	FNN,
						File,
						Carrier,
						CarrierRef,
						Source,
						Destination,
						StartDatetime,
						EndDatetime,
						Units,
						AccountGroup,
						Account,
						Service,
						Cost,
						".CDR_RATED.",
						CDR,
						Description,
						DestinationCode,
						RecordType,
						ServiceType,
						Charge,
						Rate,
						NormalisedOn,
						RatedOn,
						{$oInvoiceRun->Id},
						SequenceNo,
						Credit
				FROM	CDR
				WHERE	Account = {$oOriginalInvoice->Account}
						AND invoice_run_id = {$oOriginalInvoiceRun->Id}
						AND Status = ".CDR_INVOICED."
			");
			if ($mCDRResult === false) {
				throw new Exception_Database("Failed to copy CDR records. ".$oQuery->Error());
			}

			Log::getLog()->log("... ".$oQuery->AffectedRows()." records copied");

			// Update service fields to prepare for rerating
			Log::getLog()->log("Updating services to prepare for rerating");

			$aServiceTotals = Service_Total::getForInvoiceRunAndAccount($oOriginalInvoiceRun->Id, $oOriginalInvoice->Account);
			foreach ($aServiceTotals as $oServiceTotal) {
				// Get service ids for the service total
				$aServices = $oServiceTotal->getServices();
				foreach ($aServices as $oService) {
					Log::getLog()->log("\t...Updating service {$oService->Id} ({$oService->FNN})");
					$oService->discount_start_datetime = null;
					$oService->UncappedCharge = 0.00;
					$oService->CappedCharge = 0.00;
					$oService->save();
				}
			}

			// Rerate CDRs
			Log::getLog()->log("Selecting the copied CDRs for rerating");

			$oStmt = new StatementSelect('CDR', 'Id', "Account = <Account> AND invoice_run_id = <invoice_run_id> AND Status = <Status>", "StartDatetime ASC");
			$iRows = $oStmt->Execute(array('Account' => $oOriginalInvoice->Account, 'invoice_run_id' => $oInvoiceRun->Id, 'Status' => CDR_RATED));
			if ($iRows === false) {
				throw new Exception_Database("Failed to retrieve CDR records after copying them. ".$oStmt->Error());
			}

			Log::getLog()->log("Rerate CDRs");

			while ($aRow = $oStmt->Fetch()) {
				$oCDR = CDR::getForId($aRow['Id']);
				$fInitialCharge = $oCDR->Charge;
				$iInitialRate = $oCDR->Rate;

				Log::getLog()->log("");
				Log::getLog()->log("Before rate: ");
				Log::getLog()->log("\tCharge: {$fInitialCharge}");
				Log::getLog()->log("\tRate : {$iInitialRate}");

				$oCDR->rate(true);

				Log::getLog()->log("After rate: ");
				Log::getLog()->log("\tCharge: {$oCDR->Charge}". (($oCDR->Charge != $fInitialCharge) ? ", CHARGE DIFFERENCE: ".($oCDR->Charge - $fInitialCharge) : ''));
				Log::getLog()->log("\tRate: {$oCDR->Rate}". (($oCDR->Rate != $iInitialRate) ? ", RATE DIFFERENCE" : ''));

				Log::getLog()->log("... Complete");
			}

			Log::getLog()->log("Generating the NEW invoice");

			// Generate new invoice
			$oAccount = Account::getForId($oOriginalInvoice->Account);
			$oInvoice = new Invoice();
			$oInvoice->generate($oAccount, $oInvoiceRun, new Invoice_Source_Invoiced($oOriginalInvoice));

			Log::getLog()->log("... Complete!");

			Log::getLog()->log("Saving the NEW invoice run with status of INVOICE_RUN_STATUS_TEMPORARY");

			// Update status of the invoice run
			$oInvoiceRun->invoice_run_status_id = INVOICE_RUN_STATUS_TEMPORARY;
			$oInvoiceRun->save();

			Log::getLog()->log("Invoking callback");

			if ($oFinishedCallback !== null) {
				$oFinishedCallback->invoke($oInvoice);
			}

			Log::getLog()->log("Rolling back transaction");

			//
			//
			// ALWAYS ROLLBACK THIS PROCESS
			//
			// NEVER COMMIT A REGENERATED INVOICE
			//
			//
			$oDA->TransactionRollback();

			Log::getLog()->log("END: Regenerating Invoice - {$oOriginalInvoice->Id}");

			return $oInvoice;
		} catch (Exception $oException) {
			$oDA->TransactionRollback();
			throw $oException;
		}
	}

	public static function redistributeBalances() {
		// DEPRECATED
		throw new Exception_Assertion(
			"Invoice::redistributeBalances() is deprecated in favour of Logic_Account::batchRedistributeBalances()",
			null,
			"Invoice::redistributeBalances() is deprecated"
		);

		$oQuery = new Query();

		// We want to redistribute Invoice balances so that payments and adjustments affect oldest invoices first
		$sAccounts = "
			SELECT		ir.*,
						COUNT(IF(ROUND(i.Balance, 2) != 0, i.Id, NULL)) AS total_imbalanced,
						COUNT(DISTINCT i.Id) AS total_affected,
						MIN(i.Id) AS earliest_affected,
						MAX(i.Id) AS latest_affected

			FROM		Invoice i
						JOIN (
							SELECT		Account AS account_id,
										MIN(IF(Status != 106 AND Balance >= 0.1, Id, NULL)) AS earliest_outstanding,
										MAX(IF(Status != 106 AND Balance >= 0.1, Id, NULL)) AS latest_outstanding,
										MIN(IF(Status != 106 AND (adjustment_total < 0 OR charge_total < 0 OR Balance < 0 OR Balance < (charge_total + charge_tax)), Id, NULL)) AS earliest_redistributable,
										MAX(IF(Status != 106 AND (adjustment_total < 0 OR charge_total < 0 OR Balance < 0 OR Balance < (charge_total + charge_tax)), Id, NULL)) AS latest_redistributable,
										MAX(IF(Status = 106, Id, NULL)) AS latest_written_off
							FROM		Invoice
							WHERE		Status NOT IN (100)
							GROUP BY	Account
							HAVING		!ISNULL(earliest_outstanding)
										AND !ISNULL(earliest_redistributable)
						) /* invoice_redistributable */ ir ON (i.Account = ir.account_id)
						JOIN Account a ON (a.Id = i.Account AND a.Archived != 1)

			WHERE		i.Status NOT IN (100, 106)
						AND (
							(i.Id >= earliest_outstanding OR i.Id >= ir.earliest_redistributable)
							AND (i.Id <= latest_outstanding OR i.Id <= ir.latest_redistributable)
							AND (i.Id > COALESCE(ir.latest_written_off, 0))
						)

			GROUP BY	i.Account

			HAVING		total_imbalanced > 0
						AND total_affected > 1;
		";

		if (($oAccountsResult = $oQuery->Execute($sAccounts)) === false) {
			throw new Exception_Database($oQuery->Error());
		}

		Log::getLog()->log(" * Redistributing {$oAccountsResult->num_rows} Accounts...");

		while ($aAccount = $oAccountsResult->fetch_assoc()) {
			Log::getLog()->log("\t + Redistributing Account {$aAccount['account_id']}...");

			if (DataAccess::getDataAccess()->TransactionStart()) {
				try {
					// Get affected Invoices
					$sAffectedInvoices = "
						SELECT		i.*,
									SUM(ip.Amount) AS invoice_payment_total

						FROM		Invoice i
									LEFT JOIN InvoicePayment ip ON (i.Account = ip.Account AND ip.invoice_run_id = i.invoice_run_id)

						WHERE		i.Status NOT IN (100, 106)
									AND i.Id >= {$aAccount['earliest_affected']}
									AND i.Account = {$aAccount['account_id']}

						GROUP BY	i.Id

						ORDER BY	i.CreatedOn ASC,
									i.Id ASC;
					";

					if (($oInvoicesResult = $oQuery->Execute($sAffectedInvoices)) === false) {
						throw new Exception_Database($oQuery->Error());
					}

					$aInvoicesAssoc = array();
					while ($aInvoice = $oInvoicesResult->fetch_assoc()) {
						$aInvoicesAssoc[$aInvoice['Id']] = $aInvoice;
					}
					$aInvoices = Invoice::importResult($aInvoicesAssoc);

					// Total Balances for redistribution
					$fInvoicesGrandTotal = 0.0;
					$fBalanceGrandTotal = 0.0;
					$fChargesGrandTotal = 0.0;
					$fTotalReducable = 0.0;
					foreach ($aInvoices as $oInvoice) {
						$fReductions = Invoice::roundOut(max(0.0, $oInvoice->charge_total + $oInvoice->charge_tax) - $oInvoice->Balance, 4);
						$fTotalReducable += $fReductions;
						Log::getLog()->log("\t\t + Invoice {$oInvoice->Id} has \${$fReductions} of reductions");

						$fInvoicesGrandTotal += $oInvoice->Total + $oInvoice->Tax;
						$fChargesGrandTotal += $oInvoice->charge_total + $oInvoice->charge_tax;
						$fBalanceGrandTotal += $oInvoice->Balance;
					}

					Log::getLog()->log("\t\t * Invoices Grand Total: \${$fInvoicesGrandTotal}");
					Log::getLog()->log("\t\t * Charges Grand Total: \${$fChargesGrandTotal}");
					Log::getLog()->log("\t\t * Balance Grand Total: \${$fBalanceGrandTotal}");
					Log::getLog()->log("\t\t * Reductions Total: \${$fTotalReducable}");

					// Redistribute Balances
					$fRedistributedBalanceGrandTotal = 0.0;
					foreach ($aInvoices as $oInvoice) {
						// Pay out Invoice as much as possible
						//$oInvoice->Balance = max(0.0, $oInvoice->Total + $oInvoice->Tax);

						// This isn't how Billing calculates it, but it should be equivalent
						$oInvoice->Balance = max(0.0, $oInvoice->charge_total + $oInvoice->charge_tax);
						$fSubsidy = min($oInvoice->Balance, $fTotalReducable);

						$oInvoice->Balance -= $fSubsidy;
						$fTotalReducable -= $fSubsidy;

						// Save
						$oInvoice->Balance = Invoice::roundOut($oInvoice->Balance, 4);
						$oInvoice->save();

						$fRedistributedBalanceGrandTotal += $oInvoice->Balance;

						Log::getLog()->log("\t\t - Invoice {$oInvoice->Id} of \$".($oInvoice->charge_total + $oInvoice->charge_tax)." reduced by \${$fSubsidy} to \${$oInvoice->Balance} (\${$fTotalReducable} remaining to distribute)");
					}

					// Apply any remaining credits to the most recent Invoice
					Log::getLog()->log("\t\t ! \${$fTotalReducable} left to distribute overall.");
					if (Invoice::roundOut($fTotalReducable, 4) != 0) {
						$oInvoice = end($aInvoices);

						$oInvoice->Balance -= $fTotalReducable;

						$fRedistributedBalanceGrandTotal -= $fTotalReducable;

						// Save
						$oInvoice->save();

						Log::getLog()->log("\t\t - Invoice {$oInvoice->Id} of \$".($oInvoice->Total + $oInvoice->Tax)." reduced to \${$oInvoice->Balance} with the excess \${$fTotalReducable}");

						$fTotalReducable = 0.0;

						Log::getLog()->log("\t\t ! \${$fTotalReducable} left to distribute overall.");
					}

					Log::getLog()->log("\t\t ! \${$fRedistributedBalanceGrandTotal} Balance remaining overall.");

					// Ensure that the pre- and post-redistribution Balance grand totals are equal
					if (Invoice::roundOut($fBalanceGrandTotal, 4) != Invoice::roundOut($fRedistributedBalanceGrandTotal, 4)) {
						throw new Exception("Pre and Post Redistribution Balance Mismatch! (pre: \${$fBalanceGrandTotal}; post: \${$fRedistributedBalanceGrandTotal})");
					}

					//throw new Exception("Debugging!");

					// Commit
					DataAccess::getDataAccess()->TransactionCommit();
				} catch (Exception $oException) {
					Log::getLog()->log("Exception: ".$oException->getMessage()."; Rolling back changes");
					DataAccess::getDataAccess()->TransactionRollback();
				}
			} else {
				throw new Exception("Unable to start a Transaction");
			}
		}
	}

	public function __set($strName, $mxdValue) {
		parent::__set($strName, $mxdValue);

		if (array_key_exists($strName, $this->_arrProperties)) {
			// DEBUG
			switch ($strName) {
				case 'Tax':
				case 'Total':
				case 'Debits':
				case 'Credits':
					break;
					// DEBUG
					Log::getLog()->log("*** {$strName} updated to \${$mxdValue}");
					$arrBacktrace = debug_backtrace();
					Log::getLog()->log("*** Total: {$this->Total}; Tax: {$this->Tax}; Debits: {$this->Debits}; Credits: {$this->Credits};\t{$strName} @ Line {$arrBacktrace[0]['line']}");

					// Is Tax proportionate to Total?
					$fltCalculatedTax = $this->Total / 10;
					$fltDifference = $this->Tax - $fltCalculatedTax;
					if ($fltCalculatedTax == $this->Tax) {
						Log::getLog()->log("*** Tax (\${$this->Tax}) is extactly Total/10");
					} elseif ($fltDifference > -0.01 && $fltDifference < 0.01) {
						Log::getLog()->log("*** Tax (\${$this->Tax}) is nearly Total/10 ({$fltCalculatedTax})");
					} else {
						Log::getLog()->log("*** Tax (\${$this->Tax}) is significantly different to Total/10 ({$fltCalculatedTax})");
					}
					break;
			}
		}
	}

	public static function getForAccount($mAccount, $bCommittedOnly=true) {
		$oSelectForAccount = self::_preparedStatement('selForAccount');
		if ($oSelectForAccount->Execute(array('account_id'=>ORM::extractId($mAccount),'committed_only'=>(int)$bCommittedOnly)) === false) {
			throw new Exception_Database($oSelectForAccount->Error());
		}
		return self::importResult($oSelectForAccount->FetchAll());
	}

	public function getPrimaryCollectable() {
		if (isset($this->collectable_id)) {
			return Collectable::getForId($this->collectable_id);
		} else {
			throw new Exception("Invoice {$this->Id} does not have a Collectable.  Only committed Invoices have Collectables.");
		}
	}

	public function getCollectables($bIncludePromised=false) {
		return Collectable::getForInvoice($this, $bIncludePromised);
	}

	// getPaymentTotal: Returns the total amount for all payments within the invoices bill period. Can optionally return a
	// 'revised' total which excludes reversals that affect payments before the bill period.
	// Simply put the balance needs to exclude 'new' reversals of 'old' payments (if revised).
	public function getPaymentTotal($bRevised=false) {
		// By default, allows only payments created during the invoices bill period
		$sExtraWhereClause = "AND p.created_datetime BETWEEN <billing_period_start_datetime> AND <billing_period_end_datetime>";
		if ($bRevised) {
			// Allow payments created within to the Billing Period and that aren't reversals,
			// if they are reversed they must be reversing another payment within the bill period
			$sExtraWhereClause = " AND (
										(
											p.created_datetime BETWEEN <billing_period_start_datetime> AND <billing_period_end_datetime>
											AND p_reversed.id IS NULL
										) OR (
											p_reversed.id IS NOT NULL
											AND p_reversed.created_datetime BETWEEN <billing_period_start_datetime> AND <billing_period_end_datetime>
										)
									)";
		}

		$aTotal = Query::run("
			SELECT	COALESCE(SUM(p.amount * pn.value_multiplier), 0) AS payment_total
			FROM	payment p
					JOIN payment_nature pn ON (pn.id = p.payment_nature_id)
					LEFT JOIN payment p_reversed ON (p_reversed.id = p.reversed_payment_id)
			WHERE	p.account_id = <account_id>
					{$sExtraWhereClause}
		", array(
			'account_id' => $this->Account,
			'billing_period_start_datetime' => $this->billing_period_start_datetime,
			'billing_period_end_datetime' => $this->billing_period_end_datetime
		))->fetch_assoc();
		return $aTotal['payment_total'];
	}

	public function getAdjustmentTotal() {
		$aTotal = Query::run("
			SELECT (
				SELECT	COALESCE(SUM(adj.amount * adjn.value_multiplier * tn.value_multiplier), 0)
				FROM	adjustment adj
						JOIN adjustment_type adjt ON (adjt.id = adj.adjustment_type_id)
						JOIN adjustment_type_invoice_visibility adjtiv ON (
							adjtiv.id = adjt.adjustment_type_invoice_visibility_id
							AND adjtiv.system_name = 'VISIBLE'
						)
						JOIN adjustment_nature adjn ON (adjn.id = adj.adjustment_nature_id)
						JOIN transaction_nature tn ON (tn.id = adjt.transaction_nature_id)
						JOIN adjustment_status adjs ON (adjs.id = adj.adjustment_status_id)
						LEFT JOIN adjustment adj_reversed ON (adj_reversed.id = adj.reversed_adjustment_id)
						LEFT JOIN adjustment_status adjs_reversed ON (adjs_reversed.id = adj_reversed.adjustment_status_id)
				WHERE	adjs.system_name = 'APPROVED'
						AND adj.account_id = <account_id>
						AND adj.invoice_run_id = <invoice_run_id>
			)
			+
			(
				SELECT COALESCE(
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
									1,
									(
										SELECT	COALESCE(EXP(SUM(LN(1 + tt.rate_percentage))), 1)
										FROM	tax_type tt
										WHERE	c.ChargedOn BETWEEN tt.start_datetime AND tt.end_datetime
												AND tt.global = 1
									)
								)
							), 0
						)
				FROM Charge c
				WHERE c.Account = <account_id>
						AND c.invoice_run_id = <invoice_run_id>
						AND c.charge_model_id = ".CHARGE_MODEL_ADJUSTMENT."
			) AS adjustment_total
		", array(
				'account_id' => $this->Account,
				'invoice_run_id' => $this->invoice_run_id
			)
		)->fetch_assoc();
		return $aTotal['adjustment_total'];
	}

	public function getOpeningBalance($bRevised=false) {
		// Returns historical outstanding balance for the invoices account, as of the start of the billing period minus 1 second
		$sEffectiveDatetime = date('Y-m-d H:i:s', strtotime($this->billing_period_start_datetime) - 1);
		return Account::getForId($this->Account)->getHistoricalBalance($sEffectiveDatetime, false, $bRevised);
	}

	public function getTotalOverdue($bRevised=false) {
		// Returns historical overdue balance for the invoices account, as of the end of the billing period
		return Account::getForId($this->Account)->getHistoricalBalance($this->billing_period_end_datetime, true, $bRevised);
	}

	protected static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		} else {
			switch ($strStatement) {
				// SELECTS
				case 'selById':
					$arrPreparedStatements[$strStatement] = new StatementSelect("Invoice", "*", "Id = <Id>", null, 1);
					break;

				case 'selInvoiceableFNNs':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Service JOIN service_status ON Service.Status = service_status.id",
						"Service.Id, FNN, Indial100",
						"Account = <Account> AND CreatedOn < <InvoiceDatetime> AND service_status.can_invoice = 1"
					);
					break;

				case 'selPlanDetails':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"ServiceRatePlan JOIN RatePlan ON RatePlan.Id = ServiceRatePlan.RatePlan",
						"RatePlan.*, ServiceRatePlan.Id AS ServiceRatePlan, ServiceRatePlan.StartDatetime AS EarliestStartDatetime, ServiceRatePlan.min_monthly AS min_monthly_override",
						"ServiceRatePlan.Service = <Service> AND <EffectiveDate> BETWEEN StartDatetime AND EndDatetime",
						"CreatedOn DESC",
						"1"
					);
					break;

				case 'selPlanDetailsById':
					$arrPreparedStatements[$strStatement] = new StatementSelect("RatePlan", "*", "Id = <RatePlan>");
					break;

				case 'selCurrentService':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Service JOIN service_status ON Service.Status = service_status.id",
						"Service.*",
						"Service.Account = <Account> AND (Service.FNN = <FNN> OR (Service.FNN LIKE <IndialRange> AND Service.Indial100 = 1)) AND Service.CreatedOn <= <DateTime> AND service_status.can_invoice = 1",
						"Service.Id DESC",
						"1"
					);
					break;

				case 'selCDRTotals':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"(CDR JOIN Rate ON CDR.Rate = Rate.Id) JOIN RecordType ON RecordType.Id = CDR.RecordType",
						"SUM(CDR.Cost), SUM(CDR.Charge), Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt",
						"CDR.Service = <Service> AND CDR.Status = ".CDR_TEMP_INVOICE,
						null,
						null,
						"Rate.Uncapped, CDR.Credit, RecordType.global_tax_exempt"
					);
					break;

				case 'selEarliestCDR':
					$arrPreparedStatements[$strStatement] = new StatementSelect("Service", "EarliestCDR AS MinEarliestCDR", "Id = <Service>");
					break;

				case 'selHasInvoicedCDRs':
					$arrPreparedStatements[$strStatement] = new StatementSelect("ServiceTypeTotal", "SUM(Records)", "Service = <Service>");
					break;

				case 'selLastPlanInvoiced':
					$arrPreparedStatements[$strStatement] = new StatementSelect("ServiceTotal", "Id", "Service = <Service> AND RatePlan = <RatePlan>", "Id DESC", "1");
					break;

				case 'selTemporaryInvoicesByAccount':
					$arrPreparedStatements[$strStatement] = new StatementSelect("Invoice JOIN InvoiceRun ON InvoiceRun.Id = Invoice.invoice_run_id", "Invoice.*", "Account = <Account> AND invoice_run_status_id IN (".INVOICE_RUN_STATUS_TEMPORARY.", ".INVOICE_RUN_STATUS_GENERATING.")");
					break;

				case 'selAccountChargeTotals':
					$arrPreparedStatements[$strStatement] = new StatementSelect("Charge", "Nature, global_tax_exempt, SUM(Amount) AS Total", "Service IS NULL AND Account = <Account> AND Status = ".CHARGE_TEMP_INVOICE, null, null, "Nature, global_tax_exempt");
					break;

				case 'selLastInvoiceDatetime':
					$arrPreparedStatements[$strStatement] = new StatementSelect("Invoice JOIN InvoiceRun ON Invoice.invoice_run_id = InvoiceRun.Id", "InvoiceRun.BillingDate", "Invoice.Account = <Account>", "InvoiceRun.BillingDate DESC, InvoiceRun.Id DESC", 1);
					break;

				case 'selAdjustmentTotals':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Charge c", /* FINISH ME!!! */
						" COALESCE(
								SUM(
									COALESCE(
										IF(
											c.Nature = 'CR',
											0 - c.Amount,
											c.Amount
										), 0
									)
								), 0
							) AS adjustment_total,
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
											SELECT COALESCE(EXP(SUM(LN(tt.rate_percentage))), 1)
											FROM tax_type tt
											WHERE c.ChargedOn BETWEEN tt.start_datetime AND tt.end_datetime
														AND tt.global = 1
										)
									)
								), 0
							) AS adjustment_tax",
						"c.Account = <account_id> AND c.Status = ".CHARGE_TEMP_INVOICE." AND c.charge_model_id = ".CHARGE_MODEL_ADJUSTMENT." AND c.invoice_run_id = <invoice_run_id>");
					break;

				case 'selByInvoiceRunId':
					$arrPreparedStatements[$strStatement] = new StatementSelect( "Invoice", "*", "invoice_run_id = <invoice_run_id>");
					break;

				case 'selForInvoiceRunAndAccount':
					$arrPreparedStatements[$strStatement] = new StatementSelect( "Invoice", "*", "invoice_run_id = <invoice_run_id> AND Account = <account_id>");
					break;

				case 'selForAccount':
					$arrPreparedStatements[$strStatement] = new StatementSelect( "Invoice", "*", "Account = <account_id> AND (<committed_only> = 0 OR Invoice.Status != ".INVOICE_TEMP.")", "CreatedOn ASC, Id ASC");
					break;


				// INSERTS
				case 'insServiceTotal':
					$arrPreparedStatements[$strStatement] = new StatementInsert("ServiceTotal");
					break;

				case 'insServiceTotalService':
					$arrPreparedStatements[$strStatement] = new StatementInsert("service_total_service");
					break;

				case 'insSelf':
					$arrPreparedStatements[$strStatement] = new StatementInsert("Invoice");
					break;

				// UPDATE BY IDS
				case 'ubiSelf':
					$arrPreparedStatements[$strStatement] = new StatementUpdateById("Invoice");
					break;

				// UPDATES
				case 'updMarkAccountCharges':
					$arrPreparedStatements[$strStatement] = new StatementUpdate("Charge", "Account = <Account> AND Service IS NULL AND Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.") AND ChargedOn <= <BillingPeriodEnd>", array('Status'=>null, 'invoice_run_id'=>null));
					break;

				case 'updCDRRevoke':
					$arrPreparedStatements[$strStatement] = new StatementUpdate("CDR", "Account = <Account> AND (invoice_run_id = <invoice_run_id> OR Status = ".CDR_TEMP_INVOICE.")", array('invoice_run_id'=>null, 'Status'=>CDR_RATED));
					break;

				case 'updChargeRevoke':
					$arrPreparedStatements[$strStatement] = new StatementUpdate("Charge", "Account = <Account> AND (invoice_run_id = <invoice_run_id> OR Status = ".CHARGE_TEMP_INVOICE.")", array('invoice_run_id'=>null, 'Status'=>CHARGE_APPROVED));
					break;

				case 'updInvoiceStatus':
					$arrPreparedStatements[$strStatement] = new StatementUpdate("Invoice", "Account = <Account> AND invoice_run_id = <invoice_run_id>", array('Status'=>null, 'SettledOn'=>null));
					break;

				case 'updCDRCommit':
					$arrPreparedStatements[$strStatement] = new StatementUpdate("CDR", "Account = <Account> AND invoice_run_id = <invoice_run_id>", array('Status'=>CDR_INVOICED));
					break;

				case 'updChargeCommit':
					$arrPreparedStatements[$strStatement] = new StatementUpdate("Charge", "Account = <Account> AND invoice_run_id = <invoice_run_id>", array('Status'=>CHARGE_INVOICED));
					break;

				case 'updMarkAdjustments':
					$arrPreparedStatements[$strStatement] = new StatementUpdate(
						"adjustment",
						"account_id = <account_id> AND invoice_run_id IS NULL AND effective_date <= <billing_period_end_datetime>",
						array('invoice_run_id'=>null)
					);
					break;

				case 'updRevokeAdjustments':
					$arrPreparedStatements[$strStatement] = new StatementUpdate(
						'adjustment',
						'account_id = <account_id> AND invoice_run_id = <invoice_run_id>',
						array('invoice_run_id'=>null)
					);
					break;

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}
}
