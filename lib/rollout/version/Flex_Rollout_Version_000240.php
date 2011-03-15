<?php

/**
 * Version 240 of database update.
 *
 *	Collections - Data migration for payments and adjustments
 *	
 */

class Flex_Rollout_Version_000240 extends Flex_Rollout_Version
{
	private $rollbackSQL 						= array();
	private $_aChargeTypeIdToAdjustmentTypeId	= array();
	private $_bCancelLogging					= false;
	
	public function rollout()
	{
		$oDB = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		$this->rollbackSQL[] = "TRUNCATE adjustment_type;";
		$this->_copyAdjustmentTypes($oDB);
		
		$this->_bCancelLogging = $this->getUserResponseYesNo('Would you like to disable all extra logging for this version (do so, for example, if you would like it to complete unattended)?');
		
		//
		// Create migration only adjustment types called 'INVOICE_WRITE_OFF' and 'MIGRATION_BALANCE_CORRECTION' (for both DR and CR)
		//
		$this->outputMessage("Create adjustment types 'INVOICE_WRITE_OFF' and 'MIGRATION_BALANCE_CORRECTION' (both DR and CR)...\n");
		$mInsertResult = $oDB->query("	INSERT INTO adjustment_type (code, description, amount, is_amount_fixed, transaction_nature_id, status_id, adjustment_type_invoice_visibility_id)
										VALUES		(
														'INVOICE_WRITE_OFF',
														'Credit Invoice written off, debit adjustment', 
														0, 
														0, 
														(SELECT id FROM transaction_nature WHERE code = 'DR'), 
														".STATUS_INACTIVE.",
														(SELECT id FROM adjustment_type_invoice_visibility WHERE system_name = 'HIDDEN')
													),
													(
														'INVOICE_WRITE_OFF',
														'Debit Invoice written off, credit adjustment', 
														0, 
														0, 
														(SELECT id FROM transaction_nature WHERE code = 'CR'), 
														".STATUS_INACTIVE.",
														(SELECT id FROM adjustment_type_invoice_visibility WHERE system_name = 'HIDDEN')
													),
													(
														'MIGRATION_BALANCE_CORRECTION',
														'Account balance correction (Credit) during data migration for the collections system upgrade', 
														0, 
														0, 
														(SELECT id FROM transaction_nature WHERE code = 'CR'), 
														".STATUS_INACTIVE.",
														(SELECT id FROM adjustment_type_invoice_visibility WHERE system_name = 'HIDDEN')
													),
													(
														'MIGRATION_BALANCE_CORRECTION',
														'Account balance correction (Debit) during data migration for the collections system upgrade', 
														0, 
														0, 
														(SELECT id FROM transaction_nature WHERE code = 'DR'), 
														".STATUS_INACTIVE.",
														(SELECT id FROM adjustment_type_invoice_visibility WHERE system_name = 'HIDDEN')
													);");
		if (PEAR::isError($mInsertResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to create adjustment_type for ChargeType {$aRow['Id']}. ".$mInsertResult->getMessage()." (DB Error: ".$mInsertResult->getUserInfo().")");
		}
		
		// Get the global tax rate_percentage
		$mTaxTypeResult = $oDB->query("	SELECT 	rate_percentage
										FROM	tax_type
										WHERE	global = 1;");
		if (PEAR::isError($mTaxTypeResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to get the global tax rate_percentage. ".$mTaxTypeResult->getMessage()." (DB Error: ".$mTaxTypeResult->getUserInfo().")");
		}
		
		$aGlobalTax 				= $mTaxTypeResult->fetchRow(MDB2_FETCHMODE_ASSOC);
		$fGlobalTaxRatePercentage	= $aGlobalTax['rate_percentage'];
		
		$this->_copyAdjustments($oDB, $fGlobalTaxRatePercentage);
		$this->_createInvoiceWriteOffAdjustments($oDB);
		$this->_copyPayments($oDB);
		$this->_applyBalanceDifferenceAdjustment($oDB, $fGlobalTaxRatePercentage);
	}

	protected function _copyAdjustmentTypes($oDB)
	{
		//
		// Copy adjustment types (from ChargeType)
		//
		$this->outputMessage("Copy adjustment types (from ChargeType)...\n");
		$mResult = $oDB->query("SELECT	*
								FROM	ChargeType
								WHERE	charge_model_id = ".CHARGE_MODEL_ADJUSTMENT.";");
		if (PEAR::isError($mResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to get all adjustment charge types. ".$mResult->getMessage()." (DB Error: ".$mResult->getUserInfo().")");
		}
		
		$this->rollbackSQL[] = "TRUNCATE adjustment_type_system_config;";
		
		// For each ChargeType...
		while ($aRow = $mResult->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// Check that the visibility is not CREDIT_CONTROL (unsupported)
			if ($aRow['charge_type_visibility_id'] == CHARGE_TYPE_VISIBILITY_CREDIT_CONTROL)
			{
				throw new Exception(__CLASS__." Found a charge type with CHARGE_TYPE_VISIBILITY_CREDIT_CONTROL (ChargeType.Id = {$aRow['Id']}), can't migrate it.");
			}
			
			$iStatus 				= ($aRow['Archived'] == 1 ? STATUS_INACTIVE : STATUS_ACTIVE);
			$sVisibilitySystemName 	= (($aRow['charge_type_visibility_id'] == CHARGE_TYPE_VISIBILITY_VISIBLE) ? 'VISIBLE' : 'HIDDEN');
			
			$mInsertResult = $oDB->query("	INSERT INTO adjustment_type (code, description, amount, is_amount_fixed, transaction_nature_id, status_id, adjustment_type_invoice_visibility_id)
											VALUES		('{$aRow['ChargeType']}',
														'{$aRow['Description']}', 
														{$aRow['Amount']}, 
														{$aRow['Fixed']}, 
														(SELECT id FROM transaction_nature WHERE code = '{$aRow['Nature']}'), 
														{$iStatus},
														(SELECT id FROM adjustment_type_invoice_visibility WHERE system_name = '{$sVisibilitySystemName}'));");
			if (PEAR::isError($mInsertResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to create adjustment_type for ChargeType {$aRow['Id']}. ".$mInsertResult->getMessage()." (DB Error: ".$mInsertResult->getUserInfo().")");
			}
			
			// Get the id of the last inserted adjustment_type
			$mLastIdResult = $oDB->lastInsertId('adjustment_type');
			if (PEAR::isError($mLastIdResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to get the last adjustment_type insert id for ChargeType ".$aRow['Id'].". ".$mLastIdResult->getMessage()." (DB Error: ".$mLastIdResult->getUserInfo().")");
			}
			
			// Success, id returned
			$iAdjustmentTypeId = $mLastIdResult;
			
			$this->outputMessage("Created adjustment type {$aRow['ChargeType']} ({$iAdjustmentTypeId})\n");
			
			// Replicate any charge_type_system_config records involving the source ChargeType (using the new adjustment type id)
			$mSystemConfigResult = $oDB->query("SELECT		ctsc.id, ats.id AS adjustment_type_system_id
												FROM		charge_type_system_config ctsc
												JOIN		charge_type_system cts ON (cts.id = ctsc.charge_type_system_id)
												LEFT JOIN 	adjustment_type_system ats ON (ats.system_name = cts.system_name)
												WHERE		ctsc.charge_type_id = {$aRow['Id']}");
			if (PEAR::isError($mSystemConfigResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to get system charge type config for ChargeType {$aRow['Id']}. ".$mSystemConfigResult->getMessage()." (DB Error: ".$mSystemConfigResult->getUserInfo().")");
			}
			
			// For each existing system charge type config row... 
			while ($aSystemConfigRow = $mSystemConfigResult->fetchRow(MDB2_FETCHMODE_ASSOC))
			{
				// Check if a matching adjustment_type_system could be found, if not error
				if ($aSystemConfigRow['adjustment_type_system_id'] == null)
				{
					throw new Exception(__CLASS__." Can't copy charge_type_system_config record (id = {$aSystemConfigRow['id']}) because an adjustment_type_system record to apply to cannot be found.");
				}
				
				// Insert an equivalent adjustment_type_system_config row
				$mSystemConfigInsertResult = $oDB->query("	INSERT INTO adjustment_type_system_config (adjustment_type_system_id, adjustment_type_id)
															VALUES		({$aSystemConfigRow['adjustment_type_system_id']}, {$iAdjustmentTypeId});");
				if (PEAR::isError($mSystemConfigInsertResult))
				{
					// Failed
					throw new Exception(__CLASS__." Failed to get create adjustment_type_system_config row for ChargeType={$aRow['Id']}, charge_type_system_config.id={$aSystemConfigRow['id']}. ".$mSystemConfigInsertResult->getMessage()." (DB Error: ".$mSystemConfigInsertResult->getUserInfo().")");
				}
			}
			
			$this->_aChargeTypeIdToAdjustmentTypeId[$aRow['Id']] = $iAdjustmentTypeId;
		}
	}

	protected function _copyAdjustments($oDB, $fGlobalTaxRatePercentage)
	{
		//
		// Copy adjustment Charge records to adjustment...
		//
		$this->outputMessage("Copy adjustment Charge records to adjustment...\n");
		$mResult = $oDB->query("SELECT 	*
								FROM	Charge
								WHERE	charge_model_id = ".CHARGE_MODEL_ADJUSTMENT."
								AND		Status IN (".CHARGE_WAITING.", ".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.");");
		if (PEAR::isError($mResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to get all adjustment Charges. ".$mResult->getMessage()." (DB Error: ".$mResult->getUserInfo().")");
		}
		
		$this->rollbackSQL[] = "TRUNCATE adjustment";
		
		// For each Charge...
		$aCopiedChargeIds = array();
		while ($aRow = $mResult->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// Determine adjustment_review_outcome
			$sAdjustmentReviewOutcomeSystemName = (($aRow['ApprovedBy'] != null) ? 'APPROVED' : 'DECLINED');
			
			// Determine adjustment_status
			switch ($aRow['Status'])
			{
				case CHARGE_WAITING:
					$sAdjustmentStatusSystemName 		= 'PENDING';
					$sAdjustmentReviewOutcomeSystemName	= null; 
					break;
				case CHARGE_APPROVED:
				case CHARGE_TEMP_INVOICE:
					$sAdjustmentStatusSystemName 		= 'APPROVED';
					$sAdjustmentReviewOutcomeSystemName	= 'APPROVED';
					break;
			}
			
			if (!isset($this->_aChargeTypeIdToAdjustmentTypeId[$aRow['charge_type_id']]))
			{
				throw new Exception(__CLASS__." Trying to add an adjustment with an invalid adjustment type ({$aRow['charge_type_id']}), one that was not copied across.");
			}
			
			$iAdjustmentTypeId 	= $this->_aChargeTypeIdToAdjustmentTypeId[$aRow['charge_type_id']];
			$fAmount			= $aRow['Amount'];
			$fTax 				= (($aRow['global_tax_exempt'] == 1) 				? 0			: ($fAmount * $fGlobalTaxRatePercentage));
			$sApprovedBy		= (($aRow['ApprovedBy'] == null) 					? USER_ID	: $aRow['ApprovedBy']);
			$sReviewOutcome		= (($sAdjustmentReviewOutcomeSystemName == null) 	? 'NULL' 	: "(SELECT id FROM adjustment_review_outcome WHERE system_name = '{$sAdjustmentReviewOutcomeSystemName}')");
			$sService			= (($aRow['Service'] == null) 						? 'NULL' 	: $aRow['Service']);
			$sInvoiceRun		= (($aRow['invoice_run_id'] == null) 				? 'NULL' 	: $aRow['invoice_run_id']);
			$sInvoice			= (($aRow['Invoice'] == null) 						? 'NULL' 	: $aRow['Invoice']);
			$sCreatedBy			= (($aRow['CreatedBy'] == null) 					? USER_ID 	: $aRow['CreatedBy']);
			$sChargedOn			= (($aRow['ChargedOn'] == null) 					? 'NULL' 	: "'{$aRow['ChargedOn']}'");
			
			// Add the tax_component to the amount
			$fAmount += $fTax;
			
			// Insert the adjustment
			$mInsertResult = $oDB->query("	INSERT INTO adjustment (
															adjustment_type_id, 
															amount, 
															tax_component, 
															balance,
															effective_date, 
															created_employee_id, 
															created_datetime,
															reviewed_employee_id, 
															reviewed_datetime, 
															adjustment_nature_id, 
															adjustment_review_outcome_id, 
															adjustment_status_id, 
															account_id, 
															service_id, 
															invoice_id, 
															invoice_run_id
														)
											VALUES		(
															{$iAdjustmentTypeId},
															{$fAmount},
															{$fTax},
															{$fAmount},
															{$sChargedOn},
															{$sCreatedBy},
															{$aRow['CreatedOn']},
															{$sApprovedBy},
															{$aRow['CreatedOn']},
															(SELECT id FROM adjustment_nature WHERE system_name = 'ADJUSTMENT'),
															{$sReviewOutcome},
															(SELECT id FROM adjustment_status WHERE system_name = '{$sAdjustmentStatusSystemName}'),
															{$aRow['Account']},
															{$sService},
															{$sInvoice},
															{$sInvoiceRun}
														);");
			if (PEAR::isError($mInsertResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to create adjustment from Charge.Id={$aRow['Id']}. ".$mInsertResult->getMessage()." (DB Error: ".$mInsertResult->getUserInfo().")");
			}
			
			$aCopiedChargeIds[] = $aRow['Id'];
		}
		
		// Update all copied charges to CHARGE_UNINVOICED_ADJUSTMENT_MIGRATED_(PENDING/APPROVED)
		$mUpdateOldChargeResult = $oDB->query("	UPDATE 	Charge
												SET		Status = IF(
															Status = ".CHARGE_WAITING.",
															".CHARGE_UNINVOICED_PENDING_ADJUSTMENT_MIGRATED.",
															".CHARGE_UNINVOICED_APPROVED_ADJUSTMENT_MIGRATED."
														)
												WHERE	Id IN (".implode(',', $aCopiedChargeIds).");");
		if (PEAR::isError($mUpdateOldChargeResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to update the status of all copied charge (adjustment) records. ".$mUpdateOldChargeResult->getMessage()." (DB Error: ".$mUpdateOldChargeResult->getUserInfo().")");
		}
		
		$this->rollbackSQL[] = "UPDATE 	Charge
								SET		Status = (
											CASE
												WHEN ApprovedBy IS NULL			THEN ".CHARGE_WAITING." /* Not approved - Waiting */
												WHEN invoice_run_id IS NOT NULL	THEN ".CHARGE_TEMP_INVOICE." /* Has invoice_run_id - Temp Invoice */
												ELSE ".CHARGE_APPROVED." /* Approved */
											END
										)
								WHERE	Status IN (".CHARGE_UNINVOICED_PENDING_ADJUSTMENT_MIGRATED.", ".CHARGE_UNINVOICED_APPROVED_ADJUSTMENT_MIGRATED.");";
		$this->outputMessage(count($aCopiedChargeIds)." Adjustments updated...\n");
	}

	protected function _createInvoiceWriteOffAdjustments($oDB)
	{
		//
		// Create an adjustment for all written off invoices 
		//
		$this->outputMessage("Create an adjustment for all written off invoices...\n");
		$mWrittenOffInvoicesResult = $oDB->query("	SELECT	*
													FROM	Invoice
													WHERE	Status = ".INVOICE_WRITTEN_OFF."
													AND		Balance <> 0
													AND		Total <> 0;");
		if (PEAR::isError($mWrittenOffInvoicesResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to get all written off invoices. ".$mWrittenOffInvoicesResult->getMessage()." (DB Error: ".$mWrittenOffInvoicesResult->getUserInfo().")");
		}
		
		// Get the invoice write off adjustment type id
		$mWriteOffAdjustmentTypeResult = $oDB->query("	SELECT	at.id as adjustment_type_id, tn.code, tn.value_multiplier
														FROM	adjustment_type at
														JOIN	transaction_nature tn ON (tn.id = at.transaction_nature_id)
														WHERE	at.code = 'INVOICE_WRITE_OFF';");
		if (PEAR::isError($mWriteOffAdjustmentTypeResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to get the 'invoice write off' adjustment types. ".$mWriteOffAdjustmentTypeResult->getMessage()." (DB Error: ".$mWriteOffAdjustmentTypeResult->getUserInfo().")");
		}
		
		// Hash the adjustment type rows against their code
		$aWriteOffAdjustmentTypes = array();
		while ($aRow = $mWriteOffAdjustmentTypeResult->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$aWriteOffAdjustmentTypes[$aRow['code']] = $aRow;
		}
		
		// For each Invoice...
		while ($aRow = $mWrittenOffInvoicesResult->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// Determine amount & tax_component for the adjustment
			$fAmount = $aRow['Balance'];
			if ($fAmount < 0)
			{
				// Credit Invoice, use Debit adjustment to write it off
				$aAdjustmentType = $aWriteOffAdjustmentTypes['DR'];
			}
			else
			{
				// Debit Invoice, use Credit adjustment to write it off
				$aAdjustmentType = $aWriteOffAdjustmentTypes['CR'];
			}
			
			// Modify the amount using the adjustment types natures value multiplier
			$fAmount 			= $fAmount * $aAdjustmentType['value_multiplier'];
			$fTaxRatePercentage	= round(($aRow['Tax'] / $aRow['Total']), 2);
			$fModifiedTaxRate 	= 1 + $fTaxRatePercentage;
			$fTax 				= $fAmount - ($fAmount / $fModifiedTaxRate);
			
			$fAmount 	= abs($fAmount);
			$fTax 		= abs($fTax);
			
			// Create an adjustment
			$mInsertResult 		= $oDB->query("	INSERT INTO adjustment (
																adjustment_type_id, 
																amount, 
																tax_component, 
																balance,
																effective_date, 
																created_employee_id, 
																created_datetime,
																reviewed_employee_id, 
																reviewed_datetime, 
																adjustment_nature_id, 
																adjustment_review_outcome_id, 
																adjustment_status_id, 
																account_id, 
																invoice_id, 
																invoice_run_id
															)
												VALUES		(
																{$aAdjustmentType['adjustment_type_id']},
																{$fAmount},
																{$fTax},
																{$fAmount},
																{$aRow['DueOn']},
																".USER_ID.",
																NOW(),
																".USER_ID.",
																{$aRow['DueOn']},
																(SELECT id FROM adjustment_nature WHERE system_name = 'ADJUSTMENT'),
																(SELECT id FROM adjustment_review_outcome WHERE system_name = 'APPROVED'),
																(SELECT id FROM adjustment_status WHERE system_name = 'APPROVED'),
																{$aRow['Account']},
																{$aRow['Id']},
																{$aRow['invoice_run_id']}
															);");
			if (PEAR::isError($mInsertResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to create adjustment from Charge.Id={$aRow['Id']}. ".$mInsertResult->getMessage()." (DB Error: ".$mInsertResult->getUserInfo().")");
			}
		}
	}
	
	protected function _copyPayments($oDB)
	{
		//
		// Copy payments...
		//
		// Turn OFF foreign key checks
		$this->outputMessage("Turn OFF foreign key checks...\n");
		$mFKResult = $oDB->query("SET foreign_key_checks = 0;");
		if (PEAR::isError($mFKResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to turn OFF foreign keys. ".$mFKResult->getMessage()." (DB Error: ".$mFKResult->getUserInfo().")");
		}
		
		// Add 'Turn ON foreign keys' to rollback
		$this->rollbackSQL[] = "SET foreign_key_checks = 1;";
		
		// Drop fk_payment_request_payment_id (payment_request table)
		$this->outputMessage("Drop (& re-add pointing at new payment table) fk_payment_request_payment_id (payment_request table)...\n");
		$mFKResult = $oDB->query("	ALTER TABLE payment_request
									DROP FOREIGN KEY fk_payment_request_payment_id,
									ADD CONSTRAINT fk_payment_request_payment_v2_id FOREIGN KEY (payment_id) REFERENCES payment(id) ON UPDATE CASCADE ON DELETE RESTRICT;");
		if (PEAR::isError($mFKResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to Drop Payment.Id in payment_request table. ".$mFKResult->getMessage()." (DB Error: ".$mFKResult->getUserInfo().")");
		}
		
		// Rollback (reverts fk to point at old Payment table)
		$this->rollbackSQL[] = "	ALTER TABLE payment_request
									DROP FOREIGN KEY fk_payment_request_payment_v2_id,
									ADD CONSTRAINT fk_payment_request_payment_id FOREIGN KEY (payment_id) REFERENCES Payment(Id) ON UPDATE CASCADE ON DELETE RESTRICT;";
		
		// Drop fk_payment_response_payment_id (payment_response table)
		$this->outputMessage("Drop (& re-add pointing at new payment table) fk_payment_response_payment_id (payment_response table)...\n");
		$mFKResult = $oDB->query("	ALTER TABLE payment_response
									DROP FOREIGN KEY fk_payment_response_payment_id,
									ADD CONSTRAINT fk_payment_response_payment_v2_id FOREIGN KEY (payment_id) REFERENCES payment(id) ON UPDATE CASCADE ON DELETE RESTRICT;");
		if (PEAR::isError($mFKResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to Drop Payment.Id in payment_response table. ".$mFKResult->getMessage()." (DB Error: ".$mFKResult->getUserInfo().")");
		}
		
		// Rollback (reverts fk to point at old Payment table)
		$this->rollbackSQL[] = "	ALTER TABLE payment_response
									DROP FOREIGN KEY fk_payment_response_payment_v2_id,
									ADD CONSTRAINT fk_payment_response_payment_id FOREIGN KEY (payment_id) REFERENCES Payment(Id) ON UPDATE CASCADE ON DELETE RESTRICT;";
			
		// Define valid copiable statuses
		$this->outputMessage("Copy payments...\n");
		$aStatuses	= array(PAYMENT_IMPORTED, PAYMENT_WAITING, PAYMENT_PAYING, PAYMENT_FINISHED, PAYMENT_REVERSED);
		
		// Get all payment records
		$mResult 	= $oDB->query("	SELECT		p.*, a.Id as account_id
									FROM		Payment p
									LEFT JOIN	Account a ON (a.Id = p.Account);");
		if (PEAR::isError($mResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to get all payments. ".$mResult->getMessage()." (DB Error: ".$mResult->getUserInfo().")");
		}
		
		$this->rollbackSQL[] = "TRUNCATE payment";
		$this->rollbackSQL[] = "SET foreign_key_checks = 0;";
		
		$aInserts 			= array();
		$aReversals 		= array();
		$iCurrentReversals	= null;
		$iCurrentInserts	= null;
		
		$aFKNullifyIds 							= array();
		$aNoPaymentResponseTransactionInserts	= array();
		
		// For each Payment...
		$aFailureCount =	array(
								'status' => 0, 
								'amount' => 0, 
								'account' => 0
							);
		while ($aRow = $mResult->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			// Check status, skip if not one of the valid ones defined above
			$bCopy = true;
			if (!in_array($aRow['Status'], $aStatuses))
			{
				// Ignore the payment, invalid status
				$aFailureCount['status']++;
				$bCopy = false;
			}
			
			// Check amount, skip if null
			if ($aRow['Amount'] === null)
			{
				// Ignore the payment, no amount
				$aFailureCount['amount']++;
				$bCopy = false;
			}
			
			// Skip this payment if the Account field is invalid
			$iValidAccountId = $aRow['Account'];
			if (($aRow['account_id'] === null) && ($aRow['Account'] !== null))
			{
				// Try and retrieve the account id from the InvoicePayment table
				$mInvoicePaymentResult 	= $oDB->query("	SELECT		ip.Account
														FROM		InvoicePayment ip
														WHERE		ip.Payment = {$aRow['Id']};");
				if (PEAR::isError($mInvoicePaymentResult))
				{
					// Failed
					throw new Exception(__CLASS__." Failed to get InvoicePayment records for payment {$aRow['Id']}. ".$mInvoicePaymentResult->getMessage()." (DB Error: ".$mInvoicePaymentResult->getUserInfo().")");
				}
				
				if ($aInvoicePaymentRow = $mInvoicePaymentResult->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					// Use the account from the InvoicePayment
					$iValidAccountId = $aInvoicePaymentRow['Account'];
				}
				else
				{
					// Ignore the payment, invalid account
					$aFailureCount['account']++;
					$bCopy = false;
				}
			}
			
			if ($bCopy)
			{
				if ($aRow['latest_payment_response_id'] === null)
				{
					// Add the transaction data to the list of transaction data for payments without a payment_response
					// ... OriginId
					if ($aRow['OriginId'] !== null)
					{
						$aNoPaymentResponseTransactionInserts[] = "('origin_id', '{$aRow['OriginId']}', ".DATA_TYPE_STRING.", {$aRow['Id']})";
					}
					
					// ... OriginType
					if ($aRow['OriginType'] !== null)
					{
						$aNoPaymentResponseTransactionInserts[] = "('origin_type', '{$aRow['OriginType']}', ".DATA_TYPE_INTEGER.", {$aRow['Id']})";
					}
				}
				
				// Copy the 'Payment' record to the 'payment' table (creating a reversal if necessary)
				$sCarrier 					= (($aRow['Carrier'] == null) 						? 'NULL' 	: $aRow['Carrier']);
				$sLatestPayementResponse	= (($aRow['latest_payment_response_id'] == null) 	? 'NULL' 	: $aRow['latest_payment_response_id']);
				$sSurchargeCharge			= (($aRow['surcharge_charge_id'] == null) 			? 'NULL' 	: $aRow['surcharge_charge_id']);
				$sPaidOn					= (($aRow['PaidOn'] == null) 						? 'NULL' 	: "'{$aRow['PaidOn']}'");
				$sAccount					= (($iValidAccountId == null)						? 'NULL' 	: $iValidAccountId);
				$sEnteredBy					= (($aRow['EnteredBy'] == null)						? USER_ID 	: $aRow['EnteredBy']);
				$sAmount					= (($aRow['Amount'] == null)						? '0' 		: $aRow['Amount']);
				$sTransactionReference		= $oDB->escape($aRow['TXNReference']); 
				
				// Payment type, if NULL or 0 insert NULL
				$mPaymentType 	= $aRow['PaymentType'];
				$sPaymentType	= $mPaymentType;
				if (($mPaymentType === null) || ((int)$mPaymentType === 0))
				{
					$sPaymentType = 'NULL';
				}
				
				if (($iCurrentInserts === null) || (count($aInserts[$iCurrentInserts]) == 1000))
				{
					$iCurrentInserts 			= (($iCurrentInserts === null) ? 0 : ($iCurrentInserts + 1));
					$aInserts[$iCurrentInserts]	= array();
				}
				
				$aInserts[$iCurrentInserts][] = "	(
														{$aRow['Id']},
														{$sAccount},
														{$sCarrier},
														'{$aRow['created_datetime']}',
														{$sEnteredBy},
														{$sPaidOn},
														{$sPaymentType},
														'{$sTransactionReference}',
														(SELECT id FROM payment_nature WHERE system_name = 'PAYMENT'),
														{$sAmount},
														{$sAmount},
														{$sSurchargeCharge},
														{$sLatestPayementResponse}
													)";
				
				if ($aRow['Status'] == PAYMENT_REVERSED)
				{
					// Insert payment to represent the reversal
					if (($iCurrentReversals === null) || (count($aReversals[$iCurrentReversals]) == 1000))
					{
						$iCurrentReversals 				= (($iCurrentReversals === null) ? 0 : ($iCurrentReversals + 1));
						$aReversals[$iCurrentReversals] = array();
					}
				
					$aReversals[$iCurrentReversals][] = "	(
																{$sAccount},
																{$sCarrier},
																'{$aRow['created_datetime']}',
																{$sEnteredBy},
																{$sPaidOn},
																{$sPaymentType},
																'{$sTransactionReference}',
																(SELECT id FROM payment_nature WHERE system_name = 'REVERSAL'),
																{$sAmount},
																{$sAmount},
																{$sSurchargeCharge},
																{$aRow['Id']},
																(SELECT id FROM payment_reversal_reason WHERE system_name = 'AGENT_REVERSAL'),
																(SELECT id FROM payment_reversal_type WHERE system_name = 'AGENT')
															)";
				}
			}
			else
			{
				// Not copied, nullify all references to the payment id (payment_request, payment_response)
				$aFKNullifyIds[] = $aRow['Id'];
			}
		}
		
		$this->outputMessage("Payment Copy Failures: \n  Invalid Status={$aFailureCount['status']}, \n  NULL Amount={$aFailureCount['amount']}, \n  Invalid Account Id={$aFailureCount['account']}\n");
		
		// Inserts
		$this->outputMessage("Insert a new payment record for all Payment records...\n");
		foreach ($aInserts as $i => $aValues)
		{
			$sInsertQuery = "	INSERT INTO payment (
												id,
												account_id,
												carrier_id, 
												created_datetime, 
												created_employee_id, 
												paid_date, 
												payment_type_id,
												transaction_reference, 
												payment_nature_id, 
												amount, 
												balance, 
												surcharge_charge_id,
												latest_payment_response_id
											)
								VALUES	".implode(', ', $aValues).";";
			$mInsertResult = $oDB->query($sInsertQuery);
			if (PEAR::isError($mInsertResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to insert copied payment records. Query number {$i}. ".$mInsertResult->getMessage()." (DB Error: ".$mInsertResult->getUserInfo().")");
			}
		}
		
		// Reversals
		$this->outputMessage("Insert payments of nature 'REVERSAL' for all reversed payments...\n");
		foreach ($aReversals as $i => $aValues)
		{
			$mInsertReversalsResult = $oDB->query("	INSERT INTO payment (
																	account_id,
																	carrier_id, 
																	created_datetime, 
																	created_employee_id, 
																	paid_date, 
																	payment_type_id,
																	transaction_reference, 
																	payment_nature_id, 
																	amount, 
																	balance, 
																	surcharge_charge_id,
																	reversed_payment_id,
																	payment_reversal_type_id,
																	payment_reversal_reason_id
																)
													VALUES	".implode(', ', $aValues).";");	
			if (PEAR::isError($mInsertReversalsResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to insert reversed payment records. Query number {$i}");
			}
		}
		
		// Log payment_id values which will be nullified (from payment_request and payment_response)
		$sForeignKeyLogPath = null;
		if (!$this->_bCancelLogging)
		{
			while (($sForeignKeyLogPath === null) || ($sForeignKeyLogPath != '') && !file_exists($sForeignKeyLogPath))
			{
				$sForeignKeyLogPath = $this->getUserResponse('Specify a file to store payment_id values from payment_request and payment_response which will be nullified. Leave blank to skip:');
			}
			
			if ($sForeignKeyLogPath != '')
			{
				$this->outputMessage("Log file: {$sForeignKeyLogPath}...\n");
				
				// payment_request backup
				$mPaymentRequestFKResult = $oDB->query("SELECT	id, payment_id
														FROM	payment_request
														WHERE	payment_id IN (".implode(',', $aFKNullifyIds).");");
				if (PEAR::isError($mPaymentRequestFKResult))
				{
					// Failed
					throw new Exception(__CLASS__." Failed to get payment_request records that link to non-copied payments. ".$mPaymentRequestFKResult->getMessage()." (DB Error: ".$mPaymentRequestFKResult->getUserInfo().")");
				}
				
				// payment_response backup
				$mPaymentResponseFKResult = $oDB->query("SELECT	id, payment_id
														FROM	payment_response
														WHERE	payment_id IN (".implode(',', $aFKNullifyIds).");");
				if (PEAR::isError($mPaymentResponseFKResult))
				{
					// Failed
					throw new Exception(__CLASS__." Failed to get payment_response records that link to non-copied payments. ".$mPaymentResponseFKResult->getMessage()." (DB Error: ".$mPaymentResponseFKResult->getUserInfo().")");
				}
				
				$aLogLines 		= array();
				$aLogLines[]	= "'payment_request' records (id, payment_id)";
				$aLogLines[]	= "------------------------------------------";
				while ($aRequestRow = $mPaymentRequestFKResult->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					$aLogLines[] = "{$aRequestRow['id']}, {$aRequestRow['payment_id']}";
				}
				
				$aLogLines[]	= "'payment_response' records (id, payment_id)";
				$aLogLines[]	= "------------------------------------------";
				while ($aResponseRow = $mPaymentResponseFKResult->fetchRow(MDB2_FETCHMODE_ASSOC))
				{
					$aLogLines[] = "{$aResponseRow['id']}, {$aResponseRow['payment_id']}";
				}
				
				// Write to the log file
				file_put_contents($sForeignKeyLogPath, implode("\n", $aLogLines));
				$this->outputMessage("Backed up payment_request & payment_response payment_id foreign key data to: {$sForeignKeyLogPath}.\n");
			}
		}
		
		// Nullify non-copied foreign keys in payment_request and payment_response
		$this->outputMessage("Nullify non-copied foreign keys in the payment_request table...\n");
		$mUpdatePaymentRequestResult = $oDB->query("UPDATE	payment_request
													SET		payment_id = NULL
													WHERE	payment_id IN (".implode(',', $aFKNullifyIds).");");
		if (PEAR::isError($mUpdatePaymentRequestResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to update payment request records with a null payment_id (for non-copied payments). (DB Error: ".$mUpdatePaymentRequestResult->getUserInfo().")");
		}
		
		$this->outputMessage("Nullify non-copied foreign keys in the payment_response table...\n");
		$mUpdatePaymentResponseResult = $oDB->query("	UPDATE	payment_response
														SET		payment_id = NULL
														WHERE	payment_id IN (".implode(',', $aFKNullifyIds).");");
		if (PEAR::isError($mUpdatePaymentResponseResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to update payment response records with a null payment_id (for non-copied payments). (DB Error: ".$mUpdatePaymentResponseResult->getUserInfo().")");
		}
		
		// Turn ON foreign keys
		$this->outputMessage("Turn ON foreign key checks...\n");
		$mFKResult = $oDB->query("SET foreign_key_checks = 1;");
		if (PEAR::isError($mFKResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to turn ON foreign keys. ".$mFKResult->getMessage()." (DB Error: ".$mFKResult->getUserInfo().")");
		}
		
		// Insert payment_response transaction_data record
		$this->outputMessage("Insert payment_response payment_transaction_data records...\n");
		$mPaymentResponseTransactionDataResult = $oDB->query("	INSERT INTO payment_transaction_data(name, value, data_type_id, payment_id, payment_response_id)
																(
																	SELECT		'origin_id',
																				CAST(
																					IF(
																                        (p.Id IS NOT NULL) AND (p.OriginId IS NOT NULL),
																                        p.OriginId,
																                        pr.origin_id
																                    )
																					AS CHAR(1024)
																				) AS data_value,
																				1 AS data_type_id, # DATA_TYPE_STRING
																				p.Id AS payment_id,
																				pr.id AS payment_response_id
																	FROM		payment_response pr
																	LEFT JOIN	Payment p ON (p.id = pr.payment_id)
																	HAVING		data_value IS NOT NULL
																)
																UNION
																(
																	SELECT		'origin_type',
																				CAST(p.OriginType AS CHAR(1024)) AS data_value,
																				2 AS data_type_id, # DATA_TYPE_INTEGER
																				p.Id AS payment_id,
																				pr.id AS payment_response_id
																	FROM		payment_response pr
																	LEFT JOIN	Payment p ON (p.Id = pr.payment_id)
																	WHERE		p.Id IS NOT NULL
																	HAVING		data_value IS NOT NULL
																);");
		if (PEAR::isError($mPaymentResponseTransactionDataResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to insert payment_response payment_transaction_data records. ".$mPaymentResponseTransactionDataResult->getMessage()." (DB Error: ".$mPaymentResponseTransactionDataResult->getUserInfo().")");
		}
		
		$this->rollbackSQL[] = "TRUNCATE payment_transaction_data;";
		
		// Insert payment transaction_data records (ones with no payment response
		$this->outputMessage("Insert payment transaction_data records (ones with no payment response...\n");
		$mPaymentTransactionDataResult = $oDB->query("	INSERT INTO payment_transaction_data(name, value, data_type_id, payment_id)
														VALUES		".implode(',', $aNoPaymentResponseTransactionInserts).";");
		if (PEAR::isError($mPaymentTransactionDataResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to insert payment_response payment_transaction_data records. ".$mPaymentTransactionDataResult->getMessage()." (DB Error: ".$mPaymentTransactionDataResult->getUserInfo().")");
		}
		
		// TODO: CR137 - UNCOMMENT THIS FOR RELEASE
		// Drop payment_response.origin_id
		/*$this->outputMessage("Drop payment_response.origin_id...\n");
		$mPaymentResponseDropOriginIdResult = $oDB->query("	ALTER TABLE	payment_response
															DROP COLUMN	origin_id;");
		if (PEAR::isError($mPaymentResponseDropOriginIdResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to drop payment_response.origin_id. ".$mPaymentResponseDropOriginIdResult->getMessage()." (DB Error: ".$mPaymentResponseDropOriginIdResult->getUserInfo().")");
		}*/
		
		// Update all payment_response.payment_reversal_reason_id & payment_response.payment_reversal_type_id
		$this->outputMessage("Update all payment_response.payment_reversal_reason_id & payment_response.payment_reversal_type_id...\n");
		$mPaymentResponseReversalResult = $oDB->query("	UPDATE 	payment_response
														SET		payment_reversal_type_id = (
																	IF(
																		payment_response_type_id = 2, /* Rejection */
																		(SELECT id FROM payment_reversal_type WHERE system_name = 'AGENT'),
																		NULL
																	)
																),
																payment_reversal_reason_id = (
																	IF(
																		payment_response_type_id = 2, /* Rejection */
																		(SELECT id FROM payment_reversal_reason WHERE system_name = 'AGENT_REVERSAL'),
																		NULL
																	)
																);");
		if (PEAR::isError($mPaymentResponseReversalResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to insert payment_response payment_transaction_data records. ".$mPaymentResponseReversalResult->getMessage()." (DB Error: ".$mPaymentResponseReversalResult->getUserInfo().")");
		}
		
		$this->rollbackSQL[] = "UPDATE 	payment_response
								SET		payment_reversal_type_id = NULL,
										payment_reversal_reason_id = NULL
								WHERE	1;";
	}
	
	protected function _applyBalanceDifferenceAdjustment($oDB, $fGlobalTaxRatePercentage)
	{
		$this->outputMessage("Checking for Account balance differences...\n");
		
		// Get the balance discrepancy adjustment type id
		$mAdjustmentTypeResult = $oDB->query("	SELECT	at.id as adjustment_type_id, tn.code, tn.value_multiplier
												FROM	adjustment_type at
												JOIN	transaction_nature tn ON (tn.id = at.transaction_nature_id)
												WHERE	at.code = 'MIGRATION_BALANCE_CORRECTION';");
		if (PEAR::isError($mAdjustmentTypeResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to get the 'account balance difference' adjustment types. ".$mAdjustmentTypeResult->getMessage()." (DB Error: ".$mAdjustmentTypeResult->getUserInfo().")");
		}
		
		// Hash the adjustment type rows against their code
		$aAdjustmentTypes = array();
		while ($aRow = $mAdjustmentTypeResult->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$aAdjustmentTypes[$aRow['code']] = $aRow;
		}
		
		// Get all account ids (and balances) where the new balance (w/out adjustments)
		// is different to the old balance (w/out adjustments)
		$mResult = $oDB->query("SELECT 	a.Id as account_id,
								    	(
								            coalesce(
								                (
								                    SELECT	sum(amount)
								                    FROM	collectable
								                    WHERE	account_id = a.Id
								                )
								            , 0)
								            +
								            coalesce(
								                (
								                    SELECT 	sum(p.amount * pn.value_multiplier)
								                    FROM	payment p
													JOIN	payment_nature pn ON (pn.id = p.payment_nature_id)
								                    WHERE	p.account_id = a.Id
								                )
								            , 0)
								        ) as balance,
								        (
								            coalesce(
								                (
								                    SELECT	sum(Balance)
								                    FROM	Invoice
								                    WHERE	Account = a.Id
								                    AND Status NOT IN (100)
								                )
								            , 0)
								            -
								            coalesce(
								                (
								                    SELECT	SUM(p.Balance)	AS balance
								                    FROM	Payment p
								                    WHERE	p.Status IN (101, 103, 150)
								                    AND p.Account = a.Id
								                )
								            , 0)
								        ) as old_balance
								FROM Account a
								HAVING balance <> old_balance;");
		if (PEAR::isError($mResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed to retrieve accounts with balance difference. ".$mResult->getMessage()." (DB Error: ".$mResult->getUserInfo().")");
		}
		
		// Create an adjustment for each account
		$this->outputMessage("Creating adjustments for each Account with a discrepancy (".$mResult->numRows()." Accounts)...\n");
		$aAdjustmentInsertData = array();
		while ($aRow = $mResult->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$fAmount = $aRow['balance'] - $aRow['old_balance'];
			if ($fAmount < 0)
			{
				// Debit
				$aAdjustmentType = $aAdjustmentTypes['DR'];
			}
			else
			{
				// Credit
				$aAdjustmentType = $aAdjustmentTypes['CR'];
			}
			
			// Modify the amount using the adjustment types natures value multiplier
			$fAmount = $fAmount * $aAdjustmentType['value_multiplier'];
			
			// Calculate the tax component of the amount
			$fModifiedTaxRate	= 1 + $fGlobalTaxRatePercentage;
			$fTax 				= $fAmount - ($fAmount / $fModifiedTaxRate);
			
			$fAmount 	= abs($fAmount);
			$fTax		= abs($fTax);
			
			// Build the insert data
			$aAdjustmentInsertData[] = "(
											{$aAdjustmentType['adjustment_type_id']},
											{$fAmount},
											{$fTax},
											{$fAmount},
											NOW(),
											".USER_ID.",
											NOW(),
											".USER_ID.",
											NOW(),
											(SELECT id FROM adjustment_nature 			WHERE system_name = 'ADJUSTMENT'),
											(SELECT id FROM adjustment_review_outcome	WHERE system_name = 'APPROVED'),
											(SELECT id FROM adjustment_status 			WHERE system_name = 'APPROVED'),
											{$aRow['account_id']}
										)";
		}
		
		// Insert the adjustments
		if (count($aAdjustmentInsertData) > 0)
		{
			$mInsertResult = $oDB->query("	INSERT INTO adjustment (
															adjustment_type_id, 
															amount, 
															tax_component, 
															balance,
															effective_date, 
															created_employee_id, 
															created_datetime,
															reviewed_employee_id, 
															reviewed_datetime, 
															adjustment_nature_id, 
															adjustment_review_outcome_id, 
															adjustment_status_id, 
															account_id
														)
											VALUES		".implode(',', $aAdjustmentInsertData).";");
			if (PEAR::isError($mResult))
			{
				// Failed
				throw new Exception(__CLASS__." Failed to insert the adjustments for accounts with balance difference. ".$mInsertResult->getMessage()." (DB Error: ".$mInsertResult->getUserInfo().")");
			}
		}
		
		$this->outputMessage("Checking balances again, including adjustments...\n");
		
		// Check balances again, this time include adjustments
		$mBalanceCheckResult = $oDB->query("SELECT	a.Id as account_id,
													(
											            coalesce(
											                (
											                    SELECT	sum(amount)
											                    FROM	collectable
											                    WHERE	account_id = a.Id
											                )
											            , 0)
											            +
											            coalesce(
											                (
											                    SELECT 	sum(p.amount * pn.value_multiplier)
											                    FROM	payment p
																JOIN	payment_nature pn ON (pn.id = p.payment_nature_id)
											                    WHERE	p.account_id = a.Id
											                )
											            , 0)
														+
														coalesce(
													        (
																SELECT 	sum(adj.amount * tn.value_multiplier * adjn.value_multiplier)
														        FROM	adjustment adj
														        JOIN	adjustment_type adjt ON (adjt.id = adj.adjustment_type_id)
														        JOIN	transaction_nature tn ON (tn.id = adjt.transaction_nature_id)
														        JOIN  	adjustment_review_outcome aro ON (aro.id = adj.adjustment_review_outcome_id)
														        JOIN  	adjustment_review_outcome_type arot ON (arot.id = aro.adjustment_review_outcome_type_id)
																JOIN	adjustment_nature adjn ON (adjn.id = adj.adjustment_nature_id)
																JOIN	adjustment_status adjs ON (adjs.id = adj.adjustment_status_id)
														        WHERE	adj.account_id = a.Id
														        AND   	arot.system_name = 'APPROVED'
														        AND   	adjs.system_name = 'APPROVED'
															)
													    , 0)
											        ) as balance,
											        (
											            coalesce(
											                (
											                    SELECT	sum(Balance)
											                    FROM	Invoice
											                    WHERE	Account = a.Id
											                    AND 	Status NOT IN (".INVOICE_TEMP.", ".INVOICE_WRITTEN_OFF.") /* Ignore Temp Invoice (100) & Written Off (106) */
											                )
											            , 0)
											            +
											            (
										                	SELECT	COALESCE(
											                            SUM(
											                                ROUND(
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
											                                            SELECT		COALESCE(EXP(SUM(LN(1 + tt.rate_percentage))), 1)
											                                            FROM		tax_type tt
											                                            WHERE		c.ChargedOn BETWEEN tt.start_datetime AND tt.end_datetime
											                                                        AND tt.global = 1
											                                        )
											                                    ), 4
											                                )
											                            ), 0
											                        )
											                FROM	Charge c
											                WHERE	c.Account = a.Id
											                AND 	c.Status IN (".CHARGE_APPROVED.", ".CHARGE_TEMP_INVOICE.", ".CHARGE_UNINVOICED_APPROVED_ADJUSTMENT_MIGRATED.") /* Approved (101), Temp Invoice (102) or Uninvoiced Adjustment Migrated (107) */
											                AND 	c.charge_model_id IN (SELECT id FROM charge_model WHERE system_name = 'ADJUSTMENT')
											                AND 	c.Nature = 'CR'
											            )
											            -
											            coalesce(
											                (
											                    SELECT	SUM(p.Balance)	AS balance
											                    FROM	Payment p
											                    WHERE	p.Status IN (101, 103, 150)
											                    AND 	p.Account = a.Id
											                )
											            , 0)
											        ) as old_balance
											FROM 	Account a
											HAVING 	balance <> old_balance;");
		if (PEAR::isError($mBalanceCheckResult))
		{
			// Failed
			throw new Exception(__CLASS__." Failed the second account balance difference check. ".$mBalanceCheckResult->getMessage()." (DB Error: ".$mBalanceCheckResult->getUserInfo().")");
		}
		
		if ($mBalanceCheckResult->numRows() > 0)
		{
			// There are still differences, log them
			$this->outputMessage("There are still ".$mBalanceCheckResult->numRows()." balance differences.\n");
			
			if (!$this->_bCancelLogging)
			{
				$sLogPath = null;
				while (($sLogPath === null) || ($sLogPath != '') && !file_exists($sLogPath))
				{
					$sLogPath = $this->getUserResponse('Specify a file to log account, balance and expected (pre-migration) balance information (Leave blank to skip logging):');
				}
				
				if ($sLogPath != '')
				{
					// Write all account balances differences to the given log file
					$aLogLines = array("Account\t\tBalance\t\tOld Balance");
					while ($aCheckRow = $mBalanceCheckResult->fetchRow(MDB2_FETCHMODE_ASSOC))
					{
						$aLogLines[] = "{$aCheckRow['account_id']},\t\t{$aCheckRow['balance']},\t\t{$aCheckRow['old_balance']}";
					}
					
					file_put_contents($sLogPath, implode("\n", $aLogLines));
					$this->outputMessage("Wrote all account balances differences to: {$sLogPath}.\n");
				}
			}
			
			throw new Exception(__CLASS__." ".$mBalanceCheckResult->numRows()." Account balances do not match expected values.");
		}
		else
		{
			$this->outputMessage("All account balances differences have been adjusted.\n");
		}
	}
	
	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$this->outputMessage("Rolling back: '".$this->rollbackSQL[$l]."'...\n");
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>