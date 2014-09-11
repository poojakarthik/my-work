<?php
class Invoice_Export {
	// Returns the Invoice Data from the Xth last Invoice
	public static function getOldInvoice($arrInvoice, $intPeriodsAgo) {
		if ((int)$intPeriodsAgo < 0) {
			// Either not an integer, or an invalid number of periods ago
			throw new Exception("\$intPeriodsAgo with value '{$intPeriodsAgo}' is less than the minimum of 0");
		}

		$selOldInvoice = new StatementSelect("Invoice", "*", "Account = <Account> AND CreatedOn <= <CreatedOn>", "CreatedOn DESC", "$intPeriodsAgo, 1");
		if ($selOldInvoice->Execute($arrInvoice) === false) {
			throw new Exception_Database($selOldInvoice->Error());
		}

		// Return data or empty array
		if ($arrOldInvoice = $selOldInvoice->Fetch()) {
			return $arrOldInvoice;
		} else {
			return array();
		}
	}

	public static function getCustomerData($arrInvoice) {
		// Retrieve the Customer Data
		$selCustomerData = self::_preparedStatement('selCustomerData');
		if ($selCustomerData->Execute($arrInvoice) === false) {
			throw new Exception_Database($selCustomerData->Error());
		}

		// Return data or empty array
		if ($arrCustomer = $selCustomerData->Fetch()) {
			return $arrCustomer;
		} else {
			return array();
		}
	}

	public static function getServices($arrInvoice) {
		// Get (Primary) FNNs for this Account
		$selAccountFNNs = self::_preparedStatement('selAccountFNNs');
		if ($selAccountFNNs->Execute($arrInvoice) === false) {
			throw new Exception_Database($selAccountFNNs->Error());
		}
		$arrAccountFNNs = $selAccountFNNs->FetchAll();

		// Get List of Service IDs for each FNN
		$arrServices = array();
		$selServiceDetails = self::_preparedStatement('selServiceDetails');
		$selServiceInstances = self::_preparedStatement('selServiceInstances');
		foreach ($arrAccountFNNs as $intKey=>$arrService) {
			//Cli_App_Billing::debug($arrService);

			// Get details from the Current Service
			$arrService['invoice_run_id'] = $arrInvoice['invoice_run_id'];
			if ($selServiceDetails->Execute($arrService) === false) {
				throw new Exception_Database($selServiceDetails->Error());
			} else {
				$arrServiceDetails = $selServiceDetails->Fetch();
				$arrService = array_merge($arrService, $arrServiceDetails);

				//Cli_App_Billing::debug($arrService);

				// Is this the Primary FNN?
				$arrService['Primary'] = ($arrService['FNN'] >= $arrService['RangeStart'] && $arrService['FNN'] <= $arrService['RangeEnd']) ? true : false;

				// Get all Service Ids that are associated with this FNN
				$arrWhere = array();
				if ($selServiceInstances->Execute($arrService) === false) {
					throw new Exception_Database($selServiceInstances->Error());
				} else {
					$arrService['Id'] = array();
					while ($arrId = $selServiceInstances->Fetch()) {
						$arrService['Id'][] = $arrId['service_id'];
					}
				}

				$arrServices[] = $arrService;
				//Cli_App_Billing::debug($arrService);
			}
		}

		foreach ($arrServices as &$arrService) {
			$arrCategories = array();
			$fltRatedTotal = 0.0;

			// Get Record Types
			$arrWhere = array();
			$arrWhere['invoice_run_id'] = $arrInvoice['invoice_run_id'];
			$arrWhere['RangeStart'] = $arrService['RangeStart'];
			$arrWhere['RangeEnd'] = $arrService['RangeEnd'];
			$arrRecordTypes = self::_preparedStatementMultiService('selRecordTypes', $arrService, $arrWhere);
			foreach ($arrRecordTypes as $arrRecordType) {
				// Get Call Itemisation
				$arrWhere['RecordGroup'] = $arrRecordType['GroupId'];
				$arrRecordType['Itemisation'] = self::_preparedStatementMultiService('selItemisedCalls', $arrService, $arrWhere);

				// Calculate Rated Total
				$fltCDRTotal = 0.0;
				$fUnitsTotal = 0.0;
				foreach ($arrRecordType['Itemisation'] as $intIndex=>$arrCDR) {
					$fltRatedTotal += $arrCDR['Charge'];
					$fltCDRTotal += $arrCDR['Charge'];
					$fUnitsTotal += ($arrCDR['Credit'] == 1 ? -1 : 1) * $arrCDR['Units'];

					// Should we hide this CDR?
					if ($arrCDR['allow_cdr_hiding'] && $arrCDR['Charge'] === 0.0 && $arrService['allow_cdr_hiding']) {
						// Yes -- hide it/remove it from itemisation
						unset($arrRecordType['Itemisation'][$intIndex]);
					}
				}
				//Cli_App_Billing::debug("CDR Total for {$arrService['FNN']}: \${$fltCDRTotal}");
				$arrRecordType['UnitsTotal'] = round($fUnitsTotal, 2);

				// Add Record Type to Service array
				$arrCategories[$arrRecordType['RecordGroup']] = $arrRecordType;
			}

			// Handle ServiceTotals for non-Indials
			if (!$arrService['Indial100']) {
				// Get the ServiceTotal
				$arrServiceTotals = self::_preparedStatementMultiService('selServiceTotal', $arrService, $arrInvoice);
				$arrService['ServiceTotal'] = 0.0;
				foreach ($arrServiceTotals as $arrServiceTotal) {
					$arrService['ServiceTotal'] += $arrServiceTotal['TotalCharge'];
				}
			}

			// Only if this is a non-Indial or is the Primary FNN
			if ($arrService['Primary']) {
				// Get Charges
				$arrItemised = self::_preparedStatementMultiService('selItemisedCharges', $arrService, $arrInvoice);
				$aChargeItemisation = array();
				if (count($arrItemised)) {
					$fltChargesTotal = 0.0;

					// Convert each Charge to a CDR
					foreach ($arrItemised as $arrCharge) {
						$arrCDR = array();
						$arrCDR['Charge'] = ($arrCharge['Nature'] == NATURE_CR) ? 0 - $arrCharge['Charge'] : $arrCharge['Charge'];
						$fltChargesTotal += $arrCDR['Charge'];

						$arrCDR['Units'] = 1;
						$arrCDR['Description'] = ($arrCharge['ChargeType']) ? ($arrCharge['ChargeType']." - ".$arrCharge['Description']) : $arrCharge['Description'];
						$arrCDR['TaxExempt'] = $arrCharge['TaxExempt'];

						$aChargeItemisation[] = $arrCDR;
					}

					// Perform "Roll-Ups"
					$aChargeItemisation = self::_chargeRollup($aChargeItemisation);

					$arrCategories['Service Charges & Discounts']['Itemisation'] = $aChargeItemisation;
					$arrCategories['Service Charges & Discounts']['DisplayType'] = RECORD_DISPLAY_S_AND_E;
					$arrCategories['Service Charges & Discounts']['TotalCharge'] = $fltChargesTotal;
					$arrCategories['Service Charges & Discounts']['Records'] = count($aChargeItemisation);

					$fltRatedTotal += $fltChargesTotal;
				}

				// Get Plan Charges
				$fltPlanChargeTotal = 0.0;
				$arrPlanChargeCharges = self::_preparedStatementMultiService('selPlanChargeCharges', $arrService, $arrInvoice);
				$arrPlanChargeItemisation = array();
				foreach ($arrPlanChargeCharges as $arrCharge) {
					// Format Plan Charge as CDR
					$arrCDR = array();
					$arrCDR['Charge'] = ($arrCharge['Nature'] == 'CR') ? 0 - $arrCharge['Charge'] : $arrCharge['Charge'];
					$arrCDR['Units'] = 1;
					$arrCDR['Description'] = ($arrCharge['ChargeType']) ? ($arrCharge['ChargeType']." - ".$arrCharge['Description']) : $arrCharge['Description'];
					$arrCDR['TaxExempt'] = $arrCharge['TaxExempt'];

					// HACKHACKHACK: This is the only way to extract the calculated period for Plan Charges to date
					$aDescription = self::_parsePlanChargeDescription($arrCharge['Description']);
					$arrCDR['ShortDescription'] = $aDescription['description'];
					$arrCDR['StartDatetime'] = "{$aDescription['start_year']}-{$aDescription['start_month']}-{$aDescription['start_day']} 00:00:00";
					$arrCDR['EndDatetime'] = "{$aDescription['end_year']}-{$aDescription['end_month']}-{$aDescription['end_day']} 23:59:59";

					$arrPlanChargeItemisation[] = $arrCDR;
					$fltPlanChargeTotal += $arrCDR['Charge'];
				}

				// Perform "Roll-Ups"
				$arrPlanChargeItemisation = self::_chargeRollup($arrPlanChargeItemisation);

				// Add to Service array
				if (count($arrPlanChargeItemisation)) {
					$arrCategories['Plan Charges']['DisplayType'] = RECORD_DISPLAY_S_AND_E;
					$arrCategories['Plan Charges']['TotalCharge'] = $fltPlanChargeTotal;
					$arrCategories['Plan Charges']['Records'] = count($arrPlanChargeItemisation);
					$arrCategories['Plan Charges']['Itemisation'] = $arrPlanChargeItemisation;

					$fltRatedTotal += $fltPlanChargeTotal;
				}

				// Get Plan Usage/Credits
				$fltPlanCreditTotal = 0.0;
				$arrPlanUsageCharges = self::_preparedStatementMultiService('selPlanUsageCharges', $arrService, $arrInvoice);
				$arrPlanCreditItemisation = array();
				foreach ($arrPlanUsageCharges as $arrCharge) {
					// Format Plan Charge as CDR
					$arrCDR = array();
					$arrCDR['Charge'] = ($arrCharge['Nature'] == 'CR') ? 0 - $arrCharge['Charge'] : $arrCharge['Charge'];
					$arrCDR['Units'] = 1;
					$arrCDR['Description'] = ($arrCharge['ChargeType']) ? ($arrCharge['ChargeType']." - ".$arrCharge['Description']) : $arrCharge['Description'];
					$arrCDR['TaxExempt'] = $arrCharge['TaxExempt'];

					// HACKHACKHACK: This is the only way to extract the calculated period for Plan Charges to date
					$aDescription = self::_parsePlanChargeDescription($arrCharge['Description']);
					$arrCDR['ShortDescription'] = $aDescription['description'];
					$arrCDR['StartDatetime'] = "{$aDescription['start_year']}-{$aDescription['start_month']}-{$aDescription['start_day']} 00:00:00";
					$arrCDR['EndDatetime'] = "{$aDescription['end_year']}-{$aDescription['end_month']}-{$aDescription['end_day']} 23:59:59";

					$arrPlanCreditItemisation[] = $arrCDR;
					$fltPlanCreditTotal += $arrCDR['Charge'];
				}

				// Perform "Roll-Ups"
				$arrPlanCreditItemisation = self::_chargeRollup($arrPlanCreditItemisation);

				// Add to Service array
				if (count($arrPlanCreditItemisation)) {
					$arrCategories['Plan Usage']['DisplayType'] = RECORD_DISPLAY_S_AND_E;
					$arrCategories['Plan Usage']['TotalCharge'] = $fltPlanCreditTotal;
					$arrCategories['Plan Usage']['Records'] = count($arrPlanCreditItemisation);
					$arrCategories['Plan Usage']['Itemisation'] = $arrPlanCreditItemisation;

					$fltRatedTotal += $fltPlanCreditTotal;
				}
			}

			// Handle ServiceTotals for Indials
			if ($arrService['Indial100'] || $arrService['SharedPlan']) {
				// Indial 100s & Shared Plans should only have Rated Totals
				$arrService['ServiceTotal'] = $fltRatedTotal;
			}

			$arrService['RecordTypes'] = $arrCategories;
			$arrService['IsRendered'] = ($arrService['ForceRender'] || count($arrCategories)) ? true : false;
		}

		return $arrServices;
	}

	public static function getAccountAdjustments($aInvoice) {
		// Get Adjustments
		//--------------------------------------------------------------------//
		$aAdjustments = array();
		$fAccountAdjustmentTotal = 0.0;
		$fAccountAdjustmentTax = (float)$aInvoice['adjustment_tax'];

		$mResult = Query::run("
			(
				SELECT		CONCAT(a_t.code, ' - ', a_t.description, IF(an.system_name = 'REVERSAL', ' Reversal', '')) AS Description,
							1 AS Units,
							(a.amount - a.tax_component) * an.value_multiplier * tn.value_multiplier AS Charge,
							IF(a.tax_component > 0.0, 0, 1) AS TaxExempt,
							a.effective_date AS ChargedDate,
							CONCAT('ADJ:', a.id) AS UniqueId,
							CONCAT('ADJ:', a.reversed_adjustment_id) AS ReversedUniqueId,
							a.tax_component * an.value_multiplier * tn.value_multiplier AS TaxComponent
				FROM		adjustment a
							JOIN adjustment_type a_t ON (a_t.id = a.adjustment_type_id)
							JOIN adjustment_nature an ON (an.id = a.adjustment_nature_id)
							JOIN transaction_nature tn ON (tn.id = a_t.transaction_nature_id)
				WHERE		a.account_id = <account_id>
							AND a.invoice_run_id = <invoice_run_id>
							AND a_t.adjustment_type_invoice_visibility_id = ".ADJUSTMENT_TYPE_INVOICE_VISIBILITY_VISIBLE."
			) UNION (
				SELECT		CONCAT(IF(c.ChargeType, CONCAT(c.ChargeType, ' - '), ''), c.Description) AS Description,
							1 AS Units,
							IF(c.Nature = 'DR', c.Amount, c.Amount * -1) AS Charge,
							c.global_tax_exempt AS TaxExempt,
							c.ChargedOn AS ChargedDate,
							CONCAT('CHG:', c.Id) AS UniqueId,
							NULL AS ReversedUniqueId,
							0.0 AS TaxComponent /* Handled by Invoice.adjustment_tax */
				FROM		Charge c
				WHERE		c.Account = <account_id>
							AND invoice_run_id = <invoice_run_id>
							AND c.charge_model_id = ".CHARGE_MODEL_ADJUSTMENT."
			)
			ORDER BY	ChargedDate ASC
		", array(
			'account_id' => (int)$aInvoice['Account'],
			'invoice_run_id' => (int)$aInvoice['invoice_run_id']
		));
		$aAdjustmentItemisation = array();
		while ($aAdjustment = $mResult->fetch_assoc()) {
			$aAdjustmentItemisation[] = $aAdjustment;
			$fAccountAdjustmentTotal += $aAdjustment['Charge'];
			$fAccountAdjustmentTax += $aAdjustment['TaxComponent'];
		}

		// Perform "Roll-Ups"
		$aAdjustmentItemisation = self::_chargeRollup($aAdjustmentItemisation);

		// Totals
		$aAdjustments['Itemisation'] = $aAdjustmentItemisation;
		$aAdjustments['DisplayType'] = RECORD_DISPLAY_S_AND_E;
		$aAdjustments['TotalCharge'] = $fAccountAdjustmentTotal;
		$aAdjustments['TaxComponent'] = $fAccountAdjustmentTax;
		$aAdjustments['Records'] = count($aAdjustments['Itemisation']);

		return $aAdjustments;
	}

	public static function getAccountCharges($arrInvoice) {
		$arrCharges = array();
		$fltAccountChargeTotal = 0.0;
		$selAccountCharges = self::_preparedStatement('selAccountCharges');
		$aChargeItemisation = array();
		if ($selAccountCharges->Execute($arrInvoice) === false) {
			throw new Exception_Database($selAccountCharges->Error());
		} else {
			while ($arrCharge = $selAccountCharges->Fetch()) {
				$arrCDR = array();
				$arrCDR['Description'] = ($arrCharge['ChargeType']) ? ($arrCharge['ChargeType']." - ".$arrCharge['Description']) : $arrCharge['Description'];
				$arrCDR['Units'] = 1;
				$arrCDR['Charge'] = $arrCharge['Amount'];
				$arrCDR['TaxExempt'] = $arrCharge['TaxExempt'];
				$aChargeItemisation[] = $arrCDR;
				$fltAccountChargeTotal += $arrCDR['Charge'];
			}
		}

		// Perform "Roll-Ups"
		$aChargeItemisation = self::_chargeRollup($aChargeItemisation);

		$arrCharges['Itemisation'] = $aChargeItemisation;
		$arrCharges['DisplayType'] = RECORD_DISPLAY_S_AND_E;
		$arrCharges['TotalCharge'] = $fltAccountChargeTotal;
		$arrCharges['Records'] = count($arrCharges['Itemisation']);

		return $arrCharges;
	}

	// Returns the Account Summary and Itemisation as an associative array for a given Invoice
	public static function getAccountSummary($arrInvoice, $bolCharges=true, $bolPlanCharges=true, $bolGST=true) {
		$arrAccountSummary = array();

		// Get Account Summary
		$selAccountSummary = self::_preparedStatement('selAccountSummary');
		if ($selAccountSummary->Execute($arrInvoice) === false) {
			throw new Exception_Database($selAccountSummary->Error());
		} else {
			while ($arrSummary = $selAccountSummary->Fetch()) {
				$arrAccountSummary[$arrSummary['Description']]['RecordTypeId'] = $arrSummary['RecordTypeId'];
				$arrAccountSummary[$arrSummary['Description']]['TotalCharge'] = number_format($arrSummary['Total'], 2, '.', '');
				$arrAccountSummary[$arrSummary['Description']]['DisplayType'] = $arrSummary['DisplayType'];
			}
		}

		// Add Other Charges and Credits
		if ($bolCharges) {
			$selAccountSummaryCharges = self::_preparedStatement('selAccountSummaryCharges');
			if (($mixResult = $selAccountSummaryCharges->Execute($arrInvoice)) === false) {
				throw new Exception_Database($selAccountSummaryCharges->Error());
			} elseif ($mixResult) {
				while ($arrSummary = $selAccountSummaryCharges->Fetch()) {
					$arrAccountSummary['Service Charges & Discounts']['TotalCharge'] = number_format($arrSummary['Total'], 2, '.', '');
					$arrAccountSummary['Service Charges & Discounts']['DisplayType'] = RECORD_DISPLAY_S_AND_E;
				}
			}
		}

		// Account Charges and Credits
		$arrAccountSummary['Account Charges & Discounts'] = self::getAccountCharges($arrInvoice);

		if ($bolPlanCharges) {
			// Plan Charges
			$selPlanChargeSummary = self::_preparedStatement('selPlanChargeSummary');
			if ($selPlanChargeSummary->Execute($arrInvoice) === false) {
				throw new Exception_Database($selPlanChargeSummary->Error());
			}
			$arrAccountSummary['Plan Charges']['Itemisation'] = array();
			while ($arrPlanChargeSummary = $selPlanChargeSummary->Fetch()) {
				$arrAccountSummary['Plan Charges']['TotalCharge'] += $arrPlanChargeSummary['Amount'];
				$arrAccountSummary['Plan Charges']['DisplayType'] = RECORD_DISPLAY_S_AND_E;

				if ($arrPlanChargeSummary['Service'] === null) {
					// Shared Plan Charge -- add to Account Itemisation
					$arrCDR = array();
					$arrCDR['Description'] = ($arrPlanChargeSummary['ChargeType']) ? ($arrPlanChargeSummary['ChargeType']." - ".$arrPlanChargeSummary['Description']) : $arrPlanChargeSummary['Description'];
					$arrCDR['Units'] = 1;
					$arrCDR['Charge'] = $arrPlanChargeSummary['Amount'];
					$arrCDR['TaxExempt'] = $arrPlanChargeSummary['TaxExempt'];
					$arrCDR['ChargeType'] = $arrPlanChargeSummary['ChargeType'];

					// HACKHACKHACK: This is the only way to extract the calculated period for Plan Charges to date
					$aDescription = self::_parsePlanChargeDescription($arrPlanChargeSummary['Description']);
					$arrCDR['ShortDescription'] = $aDescription['description'];
					$arrCDR['StartDatetime'] = "{$aDescription['start_year']}-{$aDescription['start_month']}-{$aDescription['start_day']} 00:00:00";
					$arrCDR['EndDatetime'] = "{$aDescription['end_year']}-{$aDescription['end_month']}-{$aDescription['end_day']} 23:59:59";

					$arrAccountSummary['Plan Charges']['Itemisation'][] = $arrCDR;
				}
			}

			// Perform "Roll-Ups"
			$arrAccountSummary['Plan Charges']['Itemisation'] = self::_chargeRollup($arrAccountSummary['Plan Charges']['Itemisation']);
			$arrAccountSummary['Plan Charges']['Records'] = count($arrAccountSummary['Plan Charges']['Itemisation']);

			// Plan Usage/Credit
			$selPlanChargeSummary = self::_preparedStatement('selPlanUsageSummary');
			if ($selPlanChargeSummary->Execute($arrInvoice) === false) {
				throw new Exception_Database($selPlanChargeSummary->Error());
			}
			$arrAccountSummary['Plan Usage']['Itemisation'] = array();
			while ($arrPlanChargeSummary = $selPlanChargeSummary->Fetch()) {
				$arrAccountSummary['Plan Usage']['TotalCharge'] += $arrPlanChargeSummary['Amount'];
				$arrAccountSummary['Plan Usage']['DisplayType'] = RECORD_DISPLAY_S_AND_E;

				if ($arrPlanChargeSummary['Service'] === null) {
					// Shared Plan Credit -- add to Account Itemisation
					$arrCDR = array();
					$arrCDR['Description'] = ($arrPlanChargeSummary['ChargeType']) ? ($arrPlanChargeSummary['ChargeType']." - ".$arrPlanChargeSummary['Description']) : $arrPlanChargeSummary['Description'];
					$arrCDR['Units'] = 1;
					$arrCDR['Charge'] = $arrPlanChargeSummary['Amount'];
					$arrCDR['TaxExempt'] = $arrPlanChargeSummary['TaxExempt'];
					$arrCDR['ChargeType'] = $arrPlanChargeSummary['ChargeType'];

					// HACKHACKHACK: This is the only way to extract the calculated period for Plan Charges to date
					$aDescription = self::_parsePlanChargeDescription($arrPlanChargeSummary['Description']);
					$arrCDR['ShortDescription'] = $aDescription['description'];
					$arrCDR['StartDatetime'] = "{$aDescription['start_year']}-{$aDescription['start_month']}-{$aDescription['start_day']} 00:00:00";
					$arrCDR['EndDatetime'] = "{$aDescription['end_year']}-{$aDescription['end_month']}-{$aDescription['end_day']} 23:59:59";

					$arrAccountSummary['Plan Usage']['Itemisation'][] = $arrCDR;
					$arrAccountSummary['Plan Usage']['Records']++;
				}
			}

			// Perform "Roll-Ups"
			$arrAccountSummary['Plan Usage']['Itemisation'] = self::_chargeRollup($arrAccountSummary['Plan Usage']['Itemisation']);
			$arrAccountSummary['Plan Usage']['Records'] = count($arrAccountSummary['Plan Usage']['Itemisation']);
		}

		// Add GST Element
		if ($bolGST) {
			$arrAccountSummary['GST Total']['TotalCharge'] = number_format($arrInvoice['charge_tax'], 2, '.', '');
			$arrAccountSummary['GST Total']['DisplayType'] = RECORD_DISPLAY_S_AND_E;
		}

		// Return array
		return $arrAccountSummary;
	}

	private static function _parsePlanChargeDescription($sDescription) {
		//Log::get()->log('Plan Charge Description: ' . $sDescription);
		$aDescription = array();
		preg_match(
			'/^(?P<description>.*)from ((?P<start_day>\d{2})\/(?P<start_month>\d{2})\/(?P<start_year>\d{4})) to ((?P<end_day>\d{2})\/(?P<end_month>\d{2})\/(?P<end_year>\d{4}))$/i',
			$sDescription,
			$aDescription
		);
		//Log::get()->log('Plan Charge Description: ' . var_export($aDescription, true));
		return $aDescription;
	}

	public static function getRateClasses() {
		return Rate_Class::getAll();
	}

	public static function getPaymentTotal($aInvoice) {
		return Invoice::getForId($aInvoice['Id'])->getPaymentTotal(true);
	}

	public static function getOpeningBalance($aInvoice) {
		return Invoice::getForId($aInvoice['Id'])->getOpeningBalance(true);
	}

	public static function getTotalOverdue($aInvoice) {
		return Invoice::getForId($aInvoice['Id'])->getTotalOverdue(true);
	}

	// Removes Credit/Debit pairs in an array of Charges
	private static function _chargeRollup($aCharges) {
		/*
		static $rLogFile;
		if (!isset($rLogFile)) {
			$rLogFile = @fopen('/tmp/invoice-export-rollup-'.date("YmdHis").'.log', 'w');
		}
		*/

		$aChargeKeys = array_keys($aCharges);
		$aChargePairKeys = array_keys($aCharges);

		$aCleanCharges = array();
		foreach ($aChargeKeys as $mChargeIndex) {
			$aCharge = &$aCharges[$mChargeIndex];

			// Have we already matched against something?
			if (!array_key_exists('Matched', $aCharge)) {
				// Search for a mate
				foreach ($aChargePairKeys as $mPairChargeIndex) {
					// Don't match against myself -- though that should be impossible...
					if ($mChargeIndex === $mPairChargeIndex) {
						continue;
					}

					$aPairCharge = &$aCharges[$mPairChargeIndex];
					/*
					fwrite($rLogFile, "\n'{$aCharge['Description']}' vs '{$aPairCharge['Description']}'\n");
					fwrite($rLogFile, "\n'".(float)$aCharge['Charge']."' + '".(float)$aPairCharge['Charge']."' === '".((float)$aCharge['Charge'] + (float)$aPairCharge['Charge'])."'\n");
					*/
					// Check if Description is the same (which includes ChargeType) && that the Amounts negate eachother
					// Additionally, we cannot have already matched against this Charge
					if (!array_key_exists('Matched', $aPairCharge) &&
						(($aCharge['Description'] === $aPairCharge['Description']) || ($aCharge['UniqueId'] == $aPairCharge['ReversedUniqueId']))&&
						(round((float)$aCharge['Charge'] + (float)$aPairCharge['Charge'], 4) == 0.0)) {
						Log::getLog()->log("Charge being rolled up: {$aCharge['UniqueId']} - {$aCharge['Description']}");

						// Perfect Pair -- Mark as matched
						$aCharge['Matched'] = $mPairChargeIndex;
						$aPairCharge['Matched'] = $mChargeIndex;
						unset($aCharge);
						unset($aPairCharge);
						continue 2;
					}
					unset($aPairCharge);
				}

				// No mate has been found -- Add to the "clean" array
				$aCleanCharges[] = $aCharges[$mChargeIndex];
			}
			unset($aCharge);
		}
		/*
		if (count($aCharges) == 3) {*//*
			//throw new Exception(print_r($aCharges, true));
			if ($rLogFile) {
				fwrite($rLogFile, "\n".print_r($aCharges, true)."\n");
				fwrite($rLogFile, "\n".print_r($aCleanCharges, true)."\n");
			}/*
		}
		*/
		// Return an array of Charges without CR/DR Pairs
		return $aCleanCharges;
	}

	private static function _preparedStatement($strStatement) {
		static $arrPreparedStatements = array();
		if (isset($arrPreparedStatements[$strStatement])) {
			return $arrPreparedStatements[$strStatement];
		} else {
			switch ($strStatement) {
				// SELECTS
				case 'selAccountFNNs':
					$arrCols = array();
					$arrCols['service_total_id'] = "ServiceTotal.Id";
					$arrCols['CurrentId'] = "ServiceTotal.Service";
					$arrCols['FNN'] = "ServiceTotal.FNN";
					$arrCols['Extension'] = "CASE WHEN ServiceExtension.Id IS NOT NULL THEN ServiceExtension.Name ELSE ServiceTotal.FNN END";
					$arrCols['RangeStart'] = "CASE WHEN ServiceExtension.Id IS NOT NULL THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), LPAD(ServiceExtension.RangeStart, 2, '0')) WHEN Service.Indial100 = 1 THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), '00') ELSE ServiceTotal.FNN END";
					$arrCols['RangeEnd'] = "CASE WHEN ServiceExtension.Id IS NOT NULL THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), LPAD(ServiceExtension.RangeEnd, 2, '0')) WHEN Service.Indial100 = 1 THEN CONCAT(SUBSTRING(ServiceTotal.FNN, 1, CHAR_LENGTH(ServiceTotal.FNN)-2), '99') ELSE ServiceTotal.FNN END";
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"(ServiceTotal JOIN Service ON Service.Id = ServiceTotal.Service) LEFT JOIN ServiceExtension ON (ServiceExtension.Service = Service.Id AND ServiceExtension.Archived = 0)",
						$arrCols,
						"ServiceTotal.Account = <Account> AND ServiceTotal.invoice_run_id = <invoice_run_id>",
						"Service.ServiceType, Extension",
						null,
						"Extension"
					);
					break;

				case 'selServiceDetails':
					$arrService = array();
					$arrService['ServiceType'] = "Service.ServiceType";
					$arrService['CostCentre'] = "(CASE WHEN CostCentreExtension.Id IS NULL THEN CostCentre.Name ELSE CostCentreExtension.Name END)";
					$arrService['Indial100'] = "MAX(Service.Indial100)";
					$arrService['ForceRender'] = "Service.ForceInvoiceRender";
					$arrService['RatePlan'] = "RatePlan.Name";
					$arrService['SharedPlan'] = "RatePlan.Shared";
					//$arrService['allow_cdr_hiding'] = "RatePlan.allow_cdr_hiding";
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"((((Service JOIN ServiceTotal ON ServiceTotal.Service = Service.Id) JOIN RatePlan ON ServiceTotal.RatePlan = RatePlan.Id) LEFT JOIN CostCentre ON CostCentre.Id = Service.CostCentre) LEFT JOIN ServiceExtension ON (ServiceExtension.Service = Service.Id AND ServiceExtension.Archived = 0)) LEFT JOIN CostCentre CostCentreExtension ON ServiceExtension.CostCentre = CostCentreExtension.Id",
						$arrService,
						"ServiceTotal.invoice_run_id = <invoice_run_id> AND ServiceTotal.Service = <CurrentId> AND (ServiceExtension.Name IS NULL OR ServiceExtension.Name = <Extension>)",
						"Service.ServiceType, Service.FNN, ServiceExtension.Name",
						null,
						"Service.FNN, ServiceExtension.Name"
					);
					break;

				case 'selServiceInstances':
					$arrPreparedStatements[$strStatement] = new StatementSelect("service_total_service", "service_id", "service_total_id = <service_total_id>");
					break;

				case 'selAccountSummary':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"(ServiceTypeTotal STT JOIN RecordType RT ON STT.RecordType = RT.Id) JOIN RecordType RG ON RT.GroupId = RG.Id",
						"RG.Id AS RecordTypeId, RG.Description AS Description, SUM(STT.Charge) AS Total, SUM(Records) AS Records, RG.DisplayType AS DisplayType",
						"Account = <Account> AND invoice_run_id = <invoice_run_id>",
						"RG.Description",
						null,
						"RG.Id"
					);
					break;

				case 'selAccountSummaryCharges':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Charge",
						"SUM(CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Total, COUNT(Id) AS Records",
						"Account = <Account> AND invoice_run_id = <invoice_run_id> AND ChargeType NOT IN ('PCAD', 'PCAR', 'PCR', 'PDCR') AND Service IS NOT NULL AND charge_model_id = ".CHARGE_MODEL_CHARGE
					);
					break;

				case 'selCustomerData':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Account LEFT JOIN Invoice ON Account.Id = Invoice.Account",
						"BusinessName, Address1, Address2, Suburb, Postcode, State, CustomerGroup, COUNT(CASE WHEN Invoice.Status != ".INVOICE_TEMP." THEN Invoice.Id ELSE NULL END) AS InvoiceCount, BillingType, SUM(IF(Invoice.DueOn < <CreatedOn>, Invoice.Balance, 0)) AS OverdueBalance",
						"Account.Id = <Account>",
						null,
						null,
						"Account.Id"
					);
					break;

				case 'selAccountCharges':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Charge",
						"ChargeType, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description, global_tax_exempt AS TaxExempt",
						"invoice_run_id = <invoice_run_id> AND Account = <Account> AND Service IS NULL AND ChargeType NOT IN ('PCAD', 'PCAR', 'PCR', 'PDCR') AND charge_model_id = ".CHARGE_MODEL_CHARGE
					);
					break;

				case 'selPlanChargeSummary':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Charge",
						"ChargeType, Service, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description, global_tax_exempt AS TaxExempt",
						"invoice_run_id = <invoice_run_id> AND Account = <Account> AND ChargeType IN ('PCAD', 'PCAR') AND charge_model_id = ".CHARGE_MODEL_CHARGE
					);
					break;

				case 'selPlanUsageSummary':
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Charge",
						"ChargeType, Service, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description, global_tax_exempt AS TaxExempt",
						"invoice_run_id = <invoice_run_id> AND Account = <Account> AND ChargeType IN ('PCR', 'PDCR') AND charge_model_id = ".CHARGE_MODEL_CHARGE
					);
					break;

				case 'selAccountAdjustments': // Legacy
					$arrPreparedStatements[$strStatement] = new StatementSelect(
						"Charge",
						"ChargeType, (CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS Amount, Description, global_tax_exempt AS TaxExempt",
						"invoice_run_id = <invoice_run_id> AND Account = <Account> AND charge_model_id = ".CHARGE_MODEL_ADJUSTMENT
					);
					break;

				default:
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
			}
			return $arrPreparedStatements[$strStatement];
		}
	}

 	private static function _preparedStatementMultiService($strStatement, $arrService, $arrParams) {
 		static $arrPreparedStatements = array();

		$intCount = count($arrService['Id']);
 		if (!$intCount) {
			Cli_App_Billing::debug("No Service Ids!");
			Cli_App_Billing::debug($arrService);
 		}

 		// Is there a Statement for this many Service Ids and Type?
 		if (!$arrPreparedStatements[$strStatement][$intCount]) {
	 		$arrWhere = array();
	 		foreach ($arrService['Id'] as $intKey=>$intId) {
	 			$arrWhere[] = "Service = <Service$intKey>";
	 		}
	 		$strWhereService = "(".implode(' OR ', $arrWhere).")";

	 		switch ($strStatement) {
	 			case 'selServiceSummary':
	 				$arrColumns = array();
			 		$arrColumns['RecordType'] = "GroupType.Description";
			 		$arrColumns['Total'] = "SUM(ServiceTypeTotal.Charge)";
			 		$arrColumns['Records'] = "SUM(Records)";
 					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
							"ServiceTypeTotal JOIN RecordType ON ServiceTypeTotal.RecordType = RecordType.Id, RecordType AS GroupType",
							$arrColumns,
		 					"$strWhereService AND FNN BETWEEN <RangeStart> AND <RangeEnd> AND invoice_run_id = <invoice_run_id> AND GroupType.Id = RecordType.GroupId",
		 					"ServiceTypeTotal.FNN, GroupType.Description",
		 					null,
		 					"GroupType.Description DESC"
	 					);
	 				break;

	 			case 'selItemisedRecordTypes':
	 				$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
							"CDR JOIN RecordType ON CDR.RecordType = RecordType.Id, RecordType AS RecordGroup",
							"RecordGroup.Id AS RecordType, RecordGroup.Description AS Description, RecordGroup.DisplayType AS DisplayType",
							"$strWhereService AND " .
							"RecordGroup.Id = RecordType.GroupId AND " .
							"RecordGroup.Itemised = 1 AND " .
							"CDR.invoice_run_id = <invoice_run_id> AND " .
							"FNN BETWEEN <RangeStart> AND <RangeEnd>",
							"RecordGroup.Description",
							null,
							"RecordGroup.Id"
	 					);
	 				break;

	 			case 'selItemisedCalls':
					$arrColumns = array();
					$arrColumns['Charge'] = "CASE WHEN CDR.Credit = 0 THEN CDR.Charge ELSE 0 - CDR.Charge END";
					$arrColumns['Source'] = "CDR.Source";
					$arrColumns['Destination'] = "CDR.Destination";
					$arrColumns['StartDatetime'] = "CDR.StartDatetime";
					$arrColumns['EndDatetime'] = "CDR.EndDatetime";
					$arrColumns['Units'] = "CDR.Units";
					$arrColumns['Description'] = "CDR.Description";
					$arrColumns['DestinationCode'] = "CDR.DestinationCode";
					$arrColumns['DisplayType'] = "RecordGroup.DisplayType";
					$arrColumns['RecordGroup'] = "RecordGroup.Description";
					$arrColumns['TaxExempt'] = "RecordType.global_tax_exempt";
					//$arrColumns['allow_cdr_hiding'] = "Rate.allow_cdr_hiding";
					$arrColumns['Rate'] = "CDR.Rate";
					$arrColumns['RateClass'] = "Rate.rate_class_id";
 					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
						"	CDR
							JOIN RecordType ON (CDR.RecordType = RecordType.Id)
							JOIN Rate ON (Rate.Id = CDR.Rate),
							RecordType as RecordGroup",
						$arrColumns,
						"	{$strWhereService}
							AND RecordGroup.Id = RecordType.GroupId
							AND RecordGroup.Id = <RecordGroup>
							AND CDR.invoice_run_id = <invoice_run_id>
							AND FNN BETWEEN <RangeStart> AND <RangeEnd>",
						"CDR.StartDatetime"
 					);
	 				break;

	 			case 'selItemisedCharges':
	 				$arrColumns['Charge'] = "Amount";
					$arrColumns['Description'] = "Description";
					$arrColumns['ChargeType'] = "ChargeType";
					$arrColumns['Nature'] = "Nature";
					$arrColumns['TaxExempt'] = "global_tax_exempt";
					$arrColumns['ChargedOn'] = 'ChargedOn';
					$arrColumns['StartDatetime'] = 'ChargedOn';
					$arrColumns['EndDatetime'] = 'NULL';
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
						"Charge",
						$arrColumns,
						"$strWhereService AND invoice_run_id = <invoice_run_id> AND ChargeType NOT IN ('PCAD', 'PCAR', 'PCR', 'PDCR') AND charge_model_id = ".CHARGE_MODEL_CHARGE
					);
	 				break;

	 			case 'selServiceTotal':
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
						"ServiceTotal",
						"SUM(TotalCharge + Debit - Credit) AS TotalCharge, PlanCharge",
						"$strWhereService AND invoice_run_id = <invoice_run_id>",
						null,
						null,
						"Service"
					);
	 				break;

	 			case 'selServiceChargesTotal':
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
	 					"Charge",
						"SUM(Amount) AS Charge, 'Service Charges & Discounts' AS RecordType, COUNT(Id) AS Records, Nature",
						"$strWhereService AND invoice_run_id = <invoice_run_id> AND charge_model_id = ".CHARGE_MODEL_CHARGE,
						"Nature",
						2,
						"Nature"
					);
	 				break;

	 			case 'selRecordTypes':
					$arrRecordType = array();
					$arrRecordType['RecordGroup'] = "RecordGroup.Description";
					$arrRecordType['RecordTypeId'] = "RecordType.Id";
					$arrRecordType['GroupId'] = "RecordGroup.Id";
					/*$arrRecordType['Itemised'] = "RecordGroup.Itemised";*/
					$arrRecordType['DisplayType'] = "RecordGroup.DisplayType";
					$arrRecordType['TotalCharge'] = "SUM(ServiceTypeTotal.Charge)";
					$arrRecordType['Records'] = "SUM(ServiceTypeTotal.Records)";
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
	 					"(ServiceTypeTotal JOIN RecordType ON RecordType.Id = ServiceTypeTotal.RecordType) JOIN RecordType RecordGroup ON RecordType.GroupId = RecordGroup.Id",
						$arrRecordType,
						"invoice_run_id = <invoice_run_id> AND $strWhereService AND FNN BETWEEN <RangeStart> AND <RangeEnd>",
						"RecordGroup.Description",
						null,
						"RecordGroup.Id"
					);
	 				break;

	 			case 'selPlanChargeCharges':
	 				$arrColumns['Charge'] = "Amount";
					$arrColumns['Description'] = "Description";
					$arrColumns['ChargeType'] = "ChargeType";
					$arrColumns['Nature'] = "Nature";
					$arrColumns['TaxExempt'] = "global_tax_exempt";
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
						"Charge",
						$arrColumns,
						"$strWhereService AND invoice_run_id = <invoice_run_id> AND ChargeType IN ('PCAD', 'PCAR') AND charge_model_id = ".CHARGE_MODEL_CHARGE
					);
	 				break;

	 			case 'selPlanUsageCharges':
	 				$arrColumns['Charge'] = "Amount";
					$arrColumns['Description'] = "Description";
					$arrColumns['ChargeType'] = "ChargeType";
					$arrColumns['Nature'] = "Nature";
					$arrColumns['TaxExempt'] = "global_tax_exempt";
					$arrColumns['ChargedOn'] = 'ChargedOn';
					$arrColumns['StartDatetime'] = "DATE_FORMAT(STR_TO_DATE(SUBSTRING(Description, -24, 10), '%d/%m/%Y'), '%Y-%m-%d')";
					$arrColumns['EndDatetime'] = "DATE_FORMAT(STR_TO_DATE(SUBSTRING(Description, -10, 10), '%d/%m/%Y'), '%Y-%m-%d')";
					$arrPreparedStatements[$strStatement][$intCount] = new StatementSelect(
						"Charge",
						$arrColumns,
						"$strWhereService AND invoice_run_id = <invoice_run_id> AND ChargeType IN ('PCR', 'PDCR') AND charge_model_id = ".CHARGE_MODEL_CHARGE
					);
	 				break;

	 			default:
	 				// No such Type
					throw new Exception(__CLASS__."::{$strStatement} does not exist!");
	 		}
 		}

 		// Prepare WHERE parameters
 		foreach ($arrService['Id'] as $intKey=>$intId) {
 			$arrParams["Service$intKey"] = $intId;
 		}

 		// Execute and return data
 		if ($arrPreparedStatements[$strStatement][$intCount]->Execute($arrParams) === false) {
 			throw new Exception_Database($arrPreparedStatements[$strStatement][$intCount]->Error());
 		} else {
 			return $arrPreparedStatements[$strStatement][$intCount]->FetchAll();
 		}
 	}
}
