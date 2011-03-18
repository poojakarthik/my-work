<?php

require_once dirname(__FILE__) . '/' . '../../../' . 'flex.require.php';
require_once dirname(__FILE__) . '/' . '../../pdf/Flex_Pdf.php';

class Cli_App_ApplyDirectDebits extends Cli
{
	const SWITCH_TEST_RUN	= "t";
	
	const ERROR_ACTIONING_EVENT			= 'Action invoice run event';
	const ERROR_ACCOUNT_INVOICE_ACTION	= 'Update account invoice action';
	
	const INELIGIBLE_BANK_ACCOUNT		= 'Invalid Bank Account reference';
	const INELIGIBLE_CREDIT_CARD		= 'Invalid Credit Card reference';
	const INELIGIBLE_CREDIT_CARD_EXPIRY	= 'Credit card has expired';
	const INELIGIBLE_AMOUNT				= 'Overdue balance is too small';
	
	public function run()
	{
		try
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
					//$this->_runBalanceDirectDebits();
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
			
			return 0;
		}
		catch(Exception $oException)
		{
			$this->showUsage($oException->getMessage());
			return 1;
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
		
		// Get Account records
		Log::getLog()->log("Getting accounts that are eligible");
		$mResult = Query::run("	SELECT	i.Id,
								        a.Id 					AS account_id,
								        pt.direct_debit_minimum	AS direct_debit_minimum,
								        bt.description 			AS billing_type_description,
								        i.invoice_run_id 		AS latest_invoice_run_id
								FROM	Account a
								JOIN	Invoice i ON (
								            i.Account = a.Id
								            AND i.CreatedOn = (
								                SELECT  MAX(CreatedOn)
								                FROM    Invoice
								                WHERE   Account = a.Id
								                AND     NOW() > DATE_ADD(DueOn, INTERVAL {$oCollectionsConfig->direct_debit_due_date_offset} DAY)
								            )
								        )
								JOIN 	payment_terms pt ON (pt.customer_group_id = a.CustomerGroup)
								JOIN 	billing_type bt ON (bt.id = a.BillingType)
								WHERE	NOW() > DATE_ADD(DueOn, INTERVAL {$oCollectionsConfig->direct_debit_due_date_offset} DAY)
								AND 	a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
								AND 	pt.id IN (SELECT MAX(id) FROM payment_terms WHERE customer_group_id = a.CustomerGroup)
								AND 	bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.");");
		
		Log::getLog()->log("Got accounts");
		
		// Process each account
		$iAppliedCount		= 0;
		$iDoubleUpsCount	= 0;
		$aAccountsApplied	= array();
		$sDatetime			= date('Y-m-d H:i:s');
		$sPaidOn			= date('Y-m-d');
		
		// Arrays for recording error information
		$aIneligible = 	array(
							self::INELIGIBLE_BANK_ACCOUNT 		=> 0, 
							self::INELIGIBLE_CREDIT_CARD 		=> 0,
							self::INELIGIBLE_CREDIT_CARD_EXPIRY	=> 0, 
							self::INELIGIBLE_AMOUNT 			=> 0
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
						$sOriginIdType		= 'bank_account_number';
						$mOriginId			= $oPaymentMethodDetail->AccountNumber;
					}
					else
					{
						// Ineligible due to invalid bank account
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid bank account, id = {$oAccount->DirectDebit}");
						$aIneligible[self::INELIGIBLE_BANK_ACCOUNT]++;
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
						$sOriginIdType		= 'credit_card_number';
						$mOriginId			= $oPaymentMethodDetail->CardNumber;
					}
					else if ($iNow >= $iExpiry)
					{
						// Ineligible because credit card has expired
						Log::getLog()->log("ERROR: {$iAccountId} has an expired credit card: {$sExpiry} (".date('Y-m-d', strtotime($sCompareExpiry)).")");
						$aIneligible[self::INELIGIBLE_CREDIT_CARD_EXPIRY]++;
					}
					else
					{
						// Ineligible due to invalid credit card
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid credit card, id = {$oAccount->CreditCard}");
						$aIneligible[self::INELIGIBLE_CREDIT_CARD]++;
					}
					break;
			}
			
			if ($bDirectDebitable)
			{
				$fAmount	= Rate::roundToRatingStandard($oAccount->getOverdueBalance(), 2);
				if ($fAmount < $aRow['direct_debit_minimum'])
				{
					// Not enough of a balance to be eligible
					Log::getLog()->log("ERROR: {$iAccountId} doesn't owe enough, ineligible amount: {$fAmount} (less than minimum, which is {$aRow['direct_debit_minimum']})");
					$aIneligible[self::INELIGIBLE_AMOUNT]++;
					continue;
				}
				
				// Create Payment (using origin id, payment type, account & amount)
				$oPayment =	Logic_Payment::factory(
								$iAccountId, 
								$iPaymentType, 
								$fAmount, 
								PAYMENT_NATURE_PAYMENT, 
								'', 
								$sPaidOn,
								array($sOriginIdType => $mOriginId)	// Payment_Transaction_Data details
							);
				
				// Create payment_request (linked to the payment & invoice run id)
				$oPaymentRequest	= 	Payment_Request::generatePending(
											$oAccount->Id, 					// Account id
											$iPaymentType,					// Payment type
											$fAmount,						// Amount
											$aRow['latest_invoice_run_id'],	// Invoice run id
											Employee::SYSTEM_EMPLOYEE_ID,	// Employee id
											$oPayment->id					// Payment id
										);
				
				// Update the payments transaction reference (this done separately because the transaction reference 
				// is derived from the payment request)
				$oPayment->transaction_reference = Payment_Request::generateTransactionReference($oPaymentRequest);
				$oPayment->save();
				
				// Distribute the payment
				$oPayment->distribute();
				
				Log::getLog()->log("Account: {$oAccount->Id}, Payment: {$oPayment->id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}");
				
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
		
		// Eligible for direct debits today, list the instalments that are eligible
		$oCollectionsConfig = Collections_Config::get();
		if (!$oCollectionsConfig || $oCollectionsConfig->promise_direct_debit_due_date_offset === null)
		{
			throw new Exception("There is no promise direct debit due date offset configured in collections_config.");
		}
		
		$mResult = Query::run("	SELECT  cpi.id					AS collection_promise_instalment_id,
								        cp.account_id			AS account_id,
								        pt.direct_debit_minimum	AS direct_debit_minimum,
								        bt.description 			AS billing_type_description
								FROM    collection_promise_instalment cpi
								JOIN    collection_promise cp ON (
								            cp.id = cpi.collection_promise_id
								            AND cp.completed_datetime IS NULL
								            AND cp.use_direct_debit = 1
								        )
								JOIN    Account a ON (a.Id = cp.account_id)
								JOIN    payment_terms pt ON (pt.customer_group_id = a.CustomerGroup)
								JOIN    billing_type bt ON (bt.id = a.BillingType)
								WHERE   NOW() > DATE_ADD(cpi.due_date, INTERVAL {$oCollectionsConfig->promise_direct_debit_due_date_offset} DAY)
								AND 	a.Archived IN (".ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.")
								AND 	pt.id IN (SELECT MAX(id) FROM payment_terms WHERE customer_group_id = a.CustomerGroup)
								AND 	bt.id IN (".BILLING_TYPE_CREDIT_CARD.", ".BILLING_TYPE_DIRECT_DEBIT.")");
		
		// Process each instalment
		$iAppliedCount 	= 0;
		$sDatetime		= date('Y-m-d H:i:s');
		$sPaidOn		= date('Y-m-d');
		
		// Arrays for recording error information
		$aIneligible = 	array(
							self::INELIGIBLE_BANK_ACCOUNT 		=> 0, 
							self::INELIGIBLE_CREDIT_CARD 		=> 0,
							self::INELIGIBLE_CREDIT_CARD_EXPIRY	=> 0, 
							self::INELIGIBLE_AMOUNT 			=> 0
						);
		
		while ($aRow = $mResult->fetch_assoc())
		{
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
						$sOriginIdType		= 'bank_account_number';
						$mOriginId			= $oPaymentMethodDetail->AccountNumber;
					}
					else
					{
						// Ineligible due to invalid bank account
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid bank account, id = {$oAccount->DirectDebit}");
						$aIneligible[self::INELIGIBLE_BANK_ACCOUNT]++;
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
						$sOriginIdType		= 'credit_card_number';
						$mOriginId			= $oPaymentMethodDetail->CardNumber;
					}
					else if ($iNow >= $iExpiry)
					{
						// Ineligible because credit card has expired
						Log::getLog()->log("ERROR: {$iAccountId} has an expired credit card: {$sExpiry} (".date('Y-m-d', strtotime($sCompareExpiry)).")");
						$aIneligible[self::INELIGIBLE_CREDIT_CARD_EXPIRY]++;
					}
					else
					{
						// Ineligible due to invalid credit card
						Log::getLog()->log("ERROR: {$iAccountId} has an invalid credit card, id = {$oAccount->CreditCard}");
						$aIneligible[self::INELIGIBLE_CREDIT_CARD]++;
					}
					break;
			}
			
			if ($bDirectDebitable)
			{
				$oInstalment	= Collection_Promise_Instalment::getForId($aRow['collection_promise_instalment_id']);
				$oPayable		= new Logic_Collection_Promise_Instalment($oInstalment);
				$fAmount 		= Rate::roundToRatingStandard($oPayable->getBalance(), 2);
				if ($fAmount < $aRow['direct_debit_minimum'])
				{
					// Not enough of a balance to be eligible
					Log::getLog()->log("ERROR: {$iAccountId}, instalment {$oInstalment->id} doesn't have enough balance, ineligible amount: {$fAmount} (less than minimum, which is {$aRow['direct_debit_minimum']})");
					$aIneligible[self::INELIGIBLE_AMOUNT]++;
					continue;
				}
				
				// Create Payment (using origin id, payment type, account & amount)
				$oPayment =	Logic_Payment::factory(
								$oAccount->Id, 
								$iPaymentType, 
								$fAmount, 
								PAYMENT_NATURE_PAYMENT, 
								'', 
								$sPaidOn,
								array($sOriginIdType => $mOriginId)	// Payment_Transaction_Data details
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
				
				// Update the payments transaction reference (this done separately because the transaction reference 
				// is derived from the payment request)
				$oPayment->transaction_reference = Payment_Request::generateTransactionReference($oPaymentRequest);
				$oPayment->save();
				
				// Distribute the payment
				$oPayment->distribute();
				
				Log::getLog()->log("Account: {$oAccount->Id}, Payment: {$oPayment->id}, payment_request: {$oPaymentRequest->id}, Amount: {$fAmount}");
				
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
				self::ARG_REQUIRED		=> FALSE,
				self::ARG_DESCRIPTION	=> "for testing script outcome [fully functional EXCEPT that all database changes are rolled back (i.e. NO direct debits will actually be applied)]",
				self::ARG_DEFAULT		=> FALSE,
				self::ARG_VALIDATION	=> 'Cli::_validIsSet()'
			)
		);
	}
}

?>
