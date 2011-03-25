<?php

/**
 * Cli_App_Payments
 *
 * @parent	Cli
 */
class Cli_App_Payments extends Cli
{
	const	SWITCH_TEST_RUN				= 't';
	const	SWITCH_MODE					= 'm';
	const	SWITCH_PAYMENT_ID			= 'p';
	const	SWITCH_FILE_IMPORT_ID		= 'f';
	const	SWITCH_FILE_IMPORT_DATA_ID	= 'd';
	const	SWITCH_LIMIT				= 'x';

	const	MODE_PREPROCESS		= 'PREPROCESS';
	const	MODE_PROCESS		= 'PROCESS';
	const	MODE_DISTRIBUTE		= 'DISTRIBUTE';
	const	MODE_EXPORT			= 'EXPORT';
	const	MODE_DIRECT_DEBIT	= 'DIRECTDEBIT';
	const	MODE_DEBUG			= 'DEBUG';
	const	MODE_REVERSE		= 'REVERSE';
	
	const 	DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT		= 'Invalid Bank Account reference';
	const 	DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD			= 'Invalid Credit Card reference';
	const 	DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY	= 'Credit card has expired';
	const 	DIRECT_DEBIT_INELIGIBLE_AMOUNT				= 'Overdue balance is too small';
	const 	DIRECT_DEBIT_INELIGIBLE_RETRY				= 'Invoice Run has already been Direct Debited';
	
	function run()
	{
		try
		{
			// The arguments are present and in a valid format if we get past this point.
			$this->_aArgs = $this->getValidatedArguments();
			
			$sMode	= '_'.strtolower($this->_aArgs[self::SWITCH_MODE]);
			if (!method_exists($this, $sMode))
			{
				throw new Exception("Invalid Mode '{$sMode}'");
			}
			else
			{
				$oDataAccess	= DataAccess::getDataAccess();
				
				// TEST MODE: Start Transaction
				if ($this->_aArgs[self::SWITCH_TEST_RUN] && !$oDataAccess->TransactionStart())
				{
					throw new Exception_Database($oDataAccess->Error());
				}
				
				try
				{
					// Call the approrite MODE method
					$this->$sMode();
					
					// TEST MODE: Force Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN])
					{
						throw new Exception("TEST MODE");
					}
				}
				catch (Exception $oException)
				{
					// TEST MODE: Rollback
					if ($this->_aArgs[self::SWITCH_TEST_RUN])
					{
						$oDataAccess->TransactionRollback();
					}
					throw $oException;
				}
				
				// TEST MODE: Commit
				if ($this->_aArgs[self::SWITCH_TEST_RUN])
				{
					$oDataAccess->TransactionCommit();
				}
			}
		}
		catch (Exception $oException)
		{
			echo "\n".$oException."\n";
			return 1;
		}
	}
	
	protected function _preprocess()
	{
		// Ensure that Payment Import isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_PREPROCESS)->lock();
		
		// Optional FileImport.Id parameter
		$iFileImportId	= $this->_aArgs[self::SWITCH_FILE_IMPORT_ID];
		if ($iFileImportId && ($oFileImport = File_Import::getForId()))
		{
			if ($oFileImport->Status !== FILE_IMPORTED)
			{
				throw new Exception("Only Files with Status FILE_IMPORTED (".FILE_IMPORTED.") can be Processed");
			}
			
			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}
		
		// Optional Limit Parameter
		$iLimit	= (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);
		
		// Process the Files
		try
		{
			Resource_Type_File_Import_Payment::preProcessFiles($iFileImportId, $iLimit);
		}
		catch (Exception $oException)
		{
			// TODO: Transaction if testing
			throw $oException;
		}
	}
	
	protected function _process()
	{
		// Ensure that Payment Normalisation isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_PROCESS)->lock();
		
		// Optional file_import_data.id parameter
		$iFileImportDataId	= $this->_aArgs[self::SWITCH_FILE_IMPORT_DATA_ID];
		if ($iFileImportDataId && ($oFileImportData = File_Import_Data::getForId($iFileImportDataId)))
		{
			if ($oFileImportData->file_import_data_status_id !== FILE_IMPORT_DATA_STATUS_IMPORTED)
			{
				throw new Exception("Only File Data with Status FILE_IMPORT_DATA_STATUS_IMPORTED (".FILE_IMPORT_DATA_STATUS_IMPORTED.") can be Normalised");
			}
			
			// Make sure that we have a Carrier Module defined to process this File
			Carrier_Module::getForDefinition(MODULE_TYPE_NORMALISATION_PAYMENT, $oFileImport->FileType, $oFileImport->Carrier);
		}
		
		// Optional Limit Parameter
		$iLimit	= (isset($this->_aArgs[self::SWITCH_LIMIT]) ? (int)$this->_aArgs[self::SWITCH_LIMIT] : null);
		
		// Process the Records
		try
		{
			Resource_Type_File_Import_Payment::processRecords($iFileImportDataId, $iLimit);
		}
		catch (Exception $oException)
		{
			// TODO: Transaction if testing
			throw $oException;
		}
	}

	protected function _debug() {
		
	}
	
	protected function _distribute()
	{
		// Ensure that Payment Processing isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_DISTRIBUTE)->lock();
		
		// Optional Payment.Id parameter
		$iPaymentId	= $this->_aArgs[self::SWITCH_PAYMENT_ID];
		if ($iPaymentId && ($oPayment = Payment::getForId($iPaymentId)))
		{
			Log::getLog()->log("Applying Payment #{$iPaymentId}");
			Logic_Payment::distributeAll(array((int)$iPaymentId));
			return;
		}
		
		// Apply the Payments
		Logic_Payment::distributeAll();
	}
	
	protected function _export()
	{
		// Ensure that Payment Export isn't already running, then identify that it is now running
		Flex_Process::factory(Flex_Process::PROCESS_PAYMENTS_EXPORT)->lock();
		
		try
		{
			$bTestRun	= $this->_aArgs[self::SWITCH_TEST_RUN];
			if ($bTestRun)
			{
				Log::getLog()->log("** TEST MODE **");
				Log::getLog()->log("The exported files will NOT be delivered, instead will be emailed to ybs-admin@ybs.net.au");
				
				// Enable file delivery testing (this will force emailling of all files to ybs-admin@ybs.net.au)
				Resource_Type_File_Deliver::enableTestMode();
				
				$oDataAccess	= DataAccess::getDataAccess();
				if ($oDataAccess->TransactionStart() === false)
				{
					throw new Exception("Failed to START db transaction");
				}
				Log::getLog()->log("Transaction started");
			}
			
			Resource_Type_File_Export_Payment::exportDirectDebits();
			
			if ($bTestRun)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception("Failed to ROLLBACK db transaction");
				}
				Log::getLog()->log("Transaction rolled back");
			}
		}
		catch (Exception $oException)
		{
			throw new Exception("Failed to export. ".$oException->getMessage());
		}
	}

	protected function _reverse() {
		$oPayment	= Payment::getForId($this->_aArgs[self::SWITCH_PAYMENT_ID]);

		if ($oPayment->getReversal()) {
			throw new Exception("Payment #{$oPayment->id} has already been reversed");
		} else {
			Log::getLog()->log("Reversing Payment #{$oPayment->id}...");
			$oPayment->reverse(Payment_Reversal_Reason::getForSystemName('AGENT_REVERSAL'));
			Log::getLog()->log("Successful!");
		}
	}
	
	protected function _directdebit()
	{
		// The arguments are present and in a valid format if we get past this point.
		$aArgs		= $this->getValidatedArguments();
		$bTestRun	= (bool)$aArgs[self::SWITCH_TEST_RUN];
		
		if ($bTestRun)
		{
			// In test mode, start a db transaction
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception_Database("Failed to start db transaction");
			}
			Log::getLog()->log("Running in Test Mode, transaction started");
		}
		
		try
		{
			// Determine if this should be run
			if (Collections_Schedule::getEligibility(null, true))
			{
				$this->_runBalanceDirectDebits();
				$this->_runPromiseInstalmentDirectDebits();
			}
			else
			{
				Log::getLog()->log("Direct debits cannot be processed today, check collections_schedule for more info.");
			}
			
			if ($bTestRun)
			{
				// In test mode, rollback all changes
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception_Database("Failed to rollback db transaction");
				}
				Log::getLog()->log("Running in Test Mode, Transaction rolled back");
			}
		}
		catch (Exception $oEx)
		{
			if ($bTestRun)
			{
				// In test mode, rollback transaction
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception_Database("Failed to rollback db transaction");
				}
				Log::getLog()->log("Transaction rolled back due to exception (in Test Mode)");
			}
			throw $oEx;
		}
	}
	
	private function _runBalanceDirectDebits()
	{
		Log::getLog()->log("");
		Log::getLog()->log("Overdue Balance Direct Debits");
		Log::getLog()->log("");
		
		// Eligible for direct debits today, list the invoices that are eligible
		$oCollectionsConfig = Collections_Config::get();
		if (!$oCollectionsConfig || $oCollectionsConfig->direct_debit_due_date_offset === null)
		{
			throw new Exception("There is no direct debit due date offset configured in collections_config.");
		}

		$iTimestamp	= DataAccess::getDataAccess()->getNow(true);
		
		// Get Account records
		Log::getLog()->log("Getting accounts that are eligible");
		$mResult	= Query::run("
			SELECT		i.Id						AS invoice_id,
						a.Id						AS account_id,
						pt.direct_debit_minimum		AS direct_debit_minimum,
						bt.description				AS billing_type_description

			FROM		Account a
						JOIN Invoice i ON (
							i.Account = a.Id
							/* Must be the latest non-Temporary Invoice */
							AND i.Id = (
								SELECT		Id
								FROM		Invoice
								WHERE		Account = a.Id
											AND Status != ".INVOICE_TEMP."
											AND <effective_date> >= DueOn + INTERVAL <direct_debit_due_date_offset> DAY
								ORDER BY	Id DESC
								LIMIT		1
							)
							/* Can't have been Direct Debited previously */
							AND ISNULL((
								SELECT		invoice_id
								FROM		payment_request_invoice pri
											JOIN payment_request pr ON (pr.id = pri.payment_request_id)
								WHERE		invoice_id = i.Id
											AND pr.payment_request_status_id != ".PAYMENT_REQUEST_STATUS_CANCELLED."
								LIMIT		1
							))
						)
						JOIN payment_terms pt ON (
							pt.customer_group_id = a.CustomerGroup
							AND pt.id = (
								SELECT		MAX(id)
								FROM		payment_terms
								WHERE		customer_group_id = a.CustomerGroup
							)
						)
						JOIN billing_type bt ON (
							bt.id = a.BillingType
							AND bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.")
						)

			WHERE		a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
		", array(
			'effective_date'				=> date('Y-m-d', $iTimestamp),
			'direct_debit_due_date_offset'	=> (int)$oCollectionsConfig->direct_debit_due_date_offset
		));
		
		Log::getLog()->log("Got accounts");
		
		// Process each account
		$iAppliedCount		= 0;
		$iDoubleUpsCount	= 0;
		$aAccountsApplied	= array();
		$sDatetime			= date('Y-m-d H:i:s', $iTimestamp);
		$sPaidOn			= date('Y-m-d', $iTimestamp);
		
		// Arrays for recording error information
		$aIneligible = 	array(
							self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT 			=> 0, 
							self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD 			=> 0,
							self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY	=> 0, 
							self::DIRECT_DEBIT_INELIGIBLE_AMOUNT 				=> 0,
							self::DIRECT_DEBIT_INELIGIBLE_RETRY					=> 0
						);
		
		while ($aRow = $mResult->fetch_assoc())
		{
			// Check if the account has already been processed, potentially useless 
			// but is here just to be sure that accounts aren't charged more than once
			$iAccountId	= $aRow['account_id'];
			if ($aAccountsApplied[$iAccountId])
			{
				$iDoubleUpsCount++;
				Log::getLog()->log("Already applied to account {$iAccountId}");
				continue;
			}
			$aAccountsApplied[$iAccountId]	= true;
			
			$oAccount	= Account::getForId($aRow['account_id']);

			// Check if this Invoice Run has been Direct Debited previously
			// This prevents us from constantly re-attempting to Direct Debit dishonoured payments
			// This *should* be handled by the SELECT query, but we'll leave it here just in case
			$oInvoice	= Invoice::getForId($aRow['invoice_id']);
			if (Payment_Request::getForInvoice($oInvoice->id)) {
				Log::getLog()->log("ERROR: {$iAccountId} has already been Direct Debited for Invoice Run {$oInvoice->invoice_run_id}");
				$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_RETRY]++;
			}
			
			// Offset Effective Date is today's date MINUS the Direct Debit Offset (as this offset is usually applied to the Due Date)
			// We then need to ADD 1 day, because we want people that are *Due* (but will not be overdue until tomorrow)
			$sOffsetEffectiveDate	= date(
				'Y-m-d',
				strtotime(
					'+1 day',															// We want due, not overdue
					strtotime(
						"-{$oCollectionsConfig->direct_debit_due_date_offset} days",	// Apply offset
						$iTimestamp														// Today
					)
				)
			);

			$fAmount	= Rate::roundToCurrencyStandard($oAccount->getOverdueBalance($sOffsetEffectiveDate), 2);
			if ($fAmount < $aRow['direct_debit_minimum'] || $fAmount <= 0.0) {
				// Not enough of a balance to be eligible
				//Log::getLog()->log("ERROR: {$iAccountId} doesn't owe enough, ineligible amount: {$fAmount} (less than minimum, which is {$aRow['direct_debit_minimum']})");
				$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_AMOUNT]++;
				continue;
			}
			
			// Determine if the direct debit details are valid & the origin id (cc or bank account number) & payment type (for the payment)
			$oPaymentMethodDetail	= $oAccount->getPaymentMethodDetails();
			$bDirectDebitable		= false;
			$iPaymentType			= null;
			$sOriginIdType			= null;
			$mOriginId				= null;
			switch($oAccount->BillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
					$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
					$oDirectDebit	= DirectDebit::getForId($oAccount->DirectDebit);
					if ($oDirectDebit)
					{
						$bDirectDebitable	= true;
						$sOriginIdType		= Payment_Transaction_Data::BANK_ACCOUNT_NUMBER;
						$mOriginId			= $oPaymentMethodDetail->AccountNumber;
					}
					else
					{
						// Ineligible due to invalid bank account
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid bank account, id = {$oAccount->DirectDebit}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT]++;
					}
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
					$oCreditCard	= Credit_Card::getForId($oAccount->CreditCard);
					$sExpiry		= "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01";
					$sCompareExpiry	= "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01 + 1 month";
					$iExpiry		= strtotime($sCompareExpiry);
					$iNow			= time();
					if ($oCreditCard && ($iNow < $iExpiry))
					{
						$bDirectDebitable	= true;
						$sOriginIdType		= Payment_Transaction_Data::CREDIT_CARD_NUMBER;
						$mOriginId			= Credit_Card::getMaskedCardNumber(Decrypt($oPaymentMethodDetail->CardNumber));
					}
					else if ($iNow >= $iExpiry)
					{
						// Ineligible because credit card has expired
						Log::getLog()->log("ERROR: {$iAccountId} has an expired credit card: {$sExpiry} (".date('Y-m-d', strtotime($sCompareExpiry)).")");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY]++;
					}
					else
					{
						// Ineligible due to invalid credit card
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid credit card, id = {$oAccount->CreditCard}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD]++;
					}
					break;
			}
			
			if ($bDirectDebitable)
			{
				// Create Payment (using origin id, payment type, account & amount)
				$oPayment =	Logic_Payment::factory(
								$iAccountId, 
								$iPaymentType, 
								$fAmount, 
								PAYMENT_NATURE_PAYMENT, 
								'', 
								$sPaidOn,
								array(
									'aTransactionData' =>	array(
																$sOriginIdType => $mOriginId
															)
								)
							);
				
				// Create payment_request (linked to the payment & invoice run id)
				$oPaymentRequest	= 	Payment_Request::generatePending(
											$oAccount->Id, 					// Account id
											$iPaymentType,					// Payment type
											$fAmount,						// Amount
											$oInvoice->invoice_run_id,		// Invoice run id
											Employee::SYSTEM_EMPLOYEE_ID,	// Employee id
											$oPayment->id					// Payment id
										);

				// Create payment_request_invoice
				$oPaymentRequestInvoice	= new Payment_Request_Invoice();
				$oPaymentRequestInvoice->payment_request_id		= $oPaymentRequest->id;
				$oPaymentRequestInvoice->invoice_id				= $oInvoice->Id;
				$oPaymentRequestInvoice->save();
				
				// Update the payments transaction reference (this done separately because the transaction reference 
				// is derived from the payment request)
				$oPayment->transaction_reference = $oPaymentRequest->generateTransactionReference();
				$oPayment->save();
				
				// Distribute the payment
				$oPayment->distribute();
				
				Log::getLog()->log("Account: {$oAccount->Id}, Payment: {$oPayment->id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}; Due: {$oInvoice->DueOn}");
				
				$iAppliedCount++;
			}
		}
		
		Log::getLog()->log("APPLIED: {$iAppliedCount}");
		Log::getLog()->log("INELIGIBLE: ".print_r($aIneligible, true));
		Log::getLog()->log("DOUBLE-UPS: {$iDoubleUpsCount} (This should always be zero)");
	}
	
	private function _runPromiseInstalmentDirectDebits()
	{
		Log::getLog()->log("");
		Log::getLog()->log("");
		Log::getLog()->log("Promise Instalment Direct Debits");
		Log::getLog()->log("");

		$iTimestamp	= DataAccess::getDataAccess()->getNow(true);
		
		// Eligible for direct debits today, list the instalments that are eligible
		$oCollectionsConfig = Collections_Config::get();
		if (!$oCollectionsConfig || $oCollectionsConfig->promise_direct_debit_due_date_offset === null)
		{
			throw new Exception("There is no promise direct debit due date offset configured in collections_config.");
		}
		
		$mResult	= Query::run("
			SELECT		cpi.id						AS collection_promise_instalment_id,
						cp.account_id				AS account_id,
						bt.description				AS billing_type_description

			FROM		collection_promise_instalment cpi
						JOIN collection_promise cp ON (
							cp.id = cpi.collection_promise_id
							AND cp.completed_datetime IS NULL
							AND cp.use_direct_debit = 1
						)
						JOIN Account a ON (a.Id = cp.account_id)
						JOIN billing_type bt ON (
							bt.id = a.BillingType
							AND bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.")
						)

			WHERE		a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
						AND <effective_date> >= cpi.due_date + INTERVAL <promise_direct_debit_due_date_offset> DAY
						/* Instalment can't have been Direct Debited previously */
						AND ISNULL((
							SELECT		prcpi.collection_promise_instalment_id
							FROM		payment_request_collection_promise_instalment prcpi
										JOIN payment_request pr ON (pr.id = prcpi.payment_request_id)
							WHERE		prcpi.collection_promise_instalment_id = cpi.id
										AND pr.payment_request_status_id != ".PAYMENT_REQUEST_STATUS_CANCELLED."
							LIMIT		1
						))
		", array(
			'effective_date'						=> date('Y-m-d', $iTimestamp),
			'promise_direct_debit_due_date_offset'	=> (int)$oCollectionsConfig->promise_direct_debit_due_date_offset
		));
		
		// Process each instalment
		$iAppliedCount 	= 0;
		$sDatetime		= date('Y-m-d H:i:s', $iTimestamp);
		$sPaidOn		= date('Y-m-d', $iTimestamp);
		
		// Arrays for recording error information
		$aIneligible = 	array(
							self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT 			=> 0, 
							self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD 			=> 0,
							self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY	=> 0, 
							self::DIRECT_DEBIT_INELIGIBLE_AMOUNT 				=> 0
						);
		
		while ($aRow = $mResult->fetch_assoc())
		{
			$oInstalment	= Collection_Promise_Instalment::getForId($aRow['collection_promise_instalment_id']);
			$oPayable		= new Logic_Collection_Promise_Instalment($oInstalment);
			$fAmount 		= Rate::roundToCurrencyStandard($oPayable->getBalance(), 2);

			// Promise Instalment Direct Debits are not subject to the direct_debit_minimum
			// But they must be > $0.00
			if ($fAmount <= 0.0) {
				// Not enough of a balance to be eligible
				//Log::getLog()->log("ERROR: {$iAccountId} doesn't owe enough, ineligible amount: {$fAmount} (less than minimum, which is {$aRow['direct_debit_minimum']})");
				$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_AMOUNT]++;
				continue;
			}

			// Determine if the direct debit details are valid & the origin id (cc or bank account number) & payment type (for the payment)
			$oAccount				= Account::getForId($aRow['account_id']);
			$oPaymentMethodDetail	= $oAccount->getPaymentMethodDetails();
			$bDirectDebitable		= false;
			$iPaymentType			= null;
			$sOriginIdType			= null;
			$mOriginId				= null;
			switch($oAccount->BillingType)
			{
				case BILLING_TYPE_DIRECT_DEBIT:
					$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
					$oDirectDebit	= DirectDebit::getForId($oAccount->DirectDebit);
					if ($oDirectDebit)
					{
						$bDirectDebitable	= true;
						$sOriginIdType		= Payment_Transaction_Data::BANK_ACCOUNT_NUMBER;
						$mOriginId			= $oPaymentMethodDetail->AccountNumber;
					}
					else
					{
						// Ineligible due to invalid bank account
						Log::getLog()->log("ERROR: {$oAccount->Id} has an invalid bank account, id = {$oAccount->DirectDebit}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_BANK_ACCOUNT]++;
					}
					break;
				case BILLING_TYPE_CREDIT_CARD:
					$iPaymentType	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
					$oCreditCard	= Credit_Card::getForId($oAccount->CreditCard);
					$sExpiry		= "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01";
					$sCompareExpiry	= "{$oCreditCard->ExpYear}-{$oCreditCard->ExpMonth}-01 + 1 month";
					$iExpiry		= strtotime($sCompareExpiry);
					$iNow			= time();
					if ($oCreditCard && ($iNow < $iExpiry))
					{
						$bDirectDebitable	= true;
						$sOriginIdType		= Payment_Transaction_Data::CREDIT_CARD_NUMBER;
						$mOriginId			= Credit_Card::getMaskedCardNumber(Decrypt($oPaymentMethodDetail->CardNumber));
					}
					else if ($iNow >= $iExpiry)
					{
						// Ineligible because credit card has expired
						Log::getLog()->log("ERROR: {$oAccount->Id} has an expired credit card: {$sExpiry} (".date('Y-m-d', strtotime($sCompareExpiry)).")");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD_EXPIRY]++;
					}
					else
					{
						// Ineligible due to invalid credit card
						Log::getLog()->log("ERROR: {$oAccount->Id} has an invalid credit card, id = {$oAccount->CreditCard}");
						$aIneligible[self::DIRECT_DEBIT_INELIGIBLE_CREDIT_CARD]++;
					}
					break;
			}
			
			if ($bDirectDebitable)
			{
				// Create Payment (using origin id, payment type, account & amount)
				$oPayment =	Logic_Payment::factory(
								$oAccount->Id, 
								$iPaymentType, 
								$fAmount, 
								PAYMENT_NATURE_PAYMENT, 
								'', 
								$sPaidOn,
								array(
									'aTransactionData' => 	array(
																$sOriginIdType => $mOriginId
															)
								)
							);
				
				// Create payment_request (linked to the payment & invoice run id)
				$oPaymentRequest	= 	Payment_Request::generatePending(
											$oAccount->Id, 					// Account id
											$iPaymentType,					// Payment type
											$fAmount,						// Amount
											null,							// Invoice run id
											Employee::SYSTEM_EMPLOYEE_ID,	// Employee id
											$oPayment->id					// Payment id
										);

				// Create payment_request_collection_promise_instalment
				$oPaymentRequestCollectionPromiseInstalment	= new Payment_Request_Collection_Promise_Instalment();
				$oPaymentRequestCollectionPromiseInstalment->payment_request_id					= $oPaymentRequest->id;
				$oPaymentRequestCollectionPromiseInstalment->collection_promise_instalment_id	= $oInstalment->id;
				$oPaymentRequestCollectionPromiseInstalment->save();

				// Update the payments transaction reference (this done separately because the transaction reference 
				// is derived from the payment request)
				$oPayment->transaction_reference = $oPaymentRequest->generateTransactionReference();
				$oPayment->save();
				
				// Distribute the payment
				$oPayment->distribute();
				
				Log::getLog()->log("Account: {$oAccount->Id}, Payment: {$oPayment->id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}; Due: {$oInstalment->due_date}");
				
				$iAppliedCount++;
			}
		}
		
		Log::getLog()->log("APPLIED: {$iAppliedCount}");
		Log::getLog()->log("INELIGIBLE: ".print_r($aIneligible, true));
	}
	
	function getCommandLineArguments()
	{
		return array(
			self::SWITCH_TEST_RUN => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_DESCRIPTION	=> "No changes to the database.",
				self::ARG_DEFAULT		=> false,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			),
			
			self::SWITCH_MODE => array(
				self::ARG_LABEL			=> "MODE",
				self::ARG_REQUIRED		=> TRUE,
				self::ARG_DESCRIPTION	=> "Payment operation to perform [".self::MODE_PREPROCESS."|".self::MODE_PROCESS."|".self::MODE_DISTRIBUTE."|".self::MODE_EXPORT."|".self::MODE_DIRECT_DEBIT."|".self::MODE_DEBUG."|".self::MODE_REVERSE."]",
				self::ARG_VALIDATION	=> 'Cli::_validInArray("%1$s", array("'.self::MODE_PREPROCESS.'","'.self::MODE_PROCESS.'","'.self::MODE_DISTRIBUTE.'","'.self::MODE_EXPORT.'","'.self::MODE_DIRECT_DEBIT.'","'.self::MODE_DEBUG.'","'.self::MODE_REVERSE.'"))'
			),
			
			self::SWITCH_PAYMENT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "PAYMENT_ID",
				self::ARG_DESCRIPTION	=> "Payment Id (".self::MODE_DISTRIBUTE.", ".self::MODE_REVERSE." Modes only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_FILE_IMPORT_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "FILE_IMPORT_ID",
				self::ARG_DESCRIPTION	=> "File Import Id (".self::MODE_PREPROCESS.", ".self::MODE_PROCESS.", ".self::MODE_DISTRIBUTE." Modes only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),

			self::SWITCH_FILE_IMPORT_DATA_ID => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "FILE_IMPORT_DATA_ID",
				self::ARG_DESCRIPTION	=> "File Import Data Id (".self::MODE_PROCESS." Mode only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			),
			
			self::SWITCH_LIMIT => array(
				self::ARG_REQUIRED		=> false,
				self::ARG_LABEL			=> "LIMIT",
				self::ARG_DESCRIPTION	=> "Limit/Maximum Items to Process (".self::MODE_PREPROCESS.", ".self::MODE_PROCESS." Modes only)",
				self::ARG_VALIDATION	=> 'Cli::_validInteger("%1$s")'
			)
		);
	}
}

?>