<?php
class JSON_Handler_Customer_ChequeEntry extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAccountDetailsForId($accountId) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

		$result = DataAccess::get()->query("
			SELECT a.Id AS id,
				a.BusinessName AS account_name,
				a.TradingName AS trading_name,
				cg.id AS customer_group_id,
				cg.internal_name AS customer_group_internal_name,
				cg.customer_primary_color AS customer_group_color_primary,
				cg.customer_secondary_color AS customer_group_color_secondary,
				cg.bank_account_name AS customer_group_bank_account_name,
				cg.bank_bsb AS customer_group_bank_bsb,
				cg.bank_account_number AS customer_group_bank_account,
				ptd_bsb.value AS cheque_bsb,
				ptd_account.value AS cheque_account
			FROM Account a
				JOIN CustomerGroup cg ON (cg.Id = a.CustomerGroup)
				LEFT JOIN payment p ON (
					p.account_id = a.Id
					AND p.payment_type_id = (SELECT id FROM payment_type WHERE const_name = 'PAYMENT_TYPE_CHEQUE')
					AND p.id = (
						SELECT id
						FROM payment
						WHERE p.account_id = a.Id
							AND p.payment_type_id = (SELECT id FROM payment_type WHERE const_name = 'PAYMENT_TYPE_CHEQUE')
						ORDER BY paid_date DESC
						LIMIT 1
					)
				)
				LEFT JOIN payment_transaction_data ptd_bsb ON (
					ptd_bsb.payment_id = p.id
					AND ptd_bsb.name = 'cheque_bsb'
				)
				LEFT JOIN payment_transaction_data ptd_account ON (
					ptd_account.payment_id = p.id
					AND ptd_account.name = 'cheque_account'
				)
			WHERE a.Id = <account_id>
		", array(
			'account_id' => (int)$accountId
		));

		if ($result->num_rows) {
			$accountDetails = $result->fetch_object();
			$accountDetails->outstanding_balance = Account::getForId($accountDetails->id)->getBalance();
			return array(
				'accountDetails' => $accountDetails,
				'bSuccess' => true
			);
		}
		return array(
			'accountDetails' => null,
			'bSuccess' => true
		);
	}

	private static $_chequeTransactionDataSchema = array(
		'cheque_bsb' => array('iDataType' => DATA_TYPE_STRING),
		'cheque_account' => array('iDataType' => DATA_TYPE_INTEGER),
		'cheque_number' => array('iDataType' => DATA_TYPE_INTEGER)
	);
	public function process($cheques) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

		$paymentIds = array();
		$validationErrors = array();
		$cheques = (array)$cheques;

		$db = DataAccess::get();
		$db->TransactionStart(false);
		try {
			foreach ($cheques as $chequeReference=>$chequeData) {
				$chequeErrors = array();
				$cheque = new stdClass();

				// Clean data
				$cheque->account_id = preg_replace('/\s/', '', $chequeData->account_id);
				$cheque->cheque_number = preg_replace('/\s/', '', $chequeData->cheque_number);
				$cheque->cheque_bsb = preg_replace('/^(\d{3})-?(\d{3})$/', '$1-$2', preg_replace('/\s/', '', $chequeData->cheque_bsb));
				$cheque->cheque_account = preg_replace('/\s/', '', $chequeData->cheque_account);
				$cheque->amount = preg_replace('/\s/', '', $chequeData->amount);

				// Validate data
				if (!preg_match('/^\d+$/', $cheque->account_id) || !Account::getForId($cheque->account_id)) {
					$chequeErrors[] = 'No such Account: ' . $chequeData->account_id;
				}
				if (!preg_match('/^\d+$/', $cheque->cheque_number)) {
					$chequeErrors[] = 'Cheque Numbers can only contain digits: ' . $chequeData->cheque_number;
				}
				if (!$cheque->cheque_bsb) {
					$chequeErrors[] = 'BSB must be in the form NNN-NNN: ' . $chequeData->cheque_bsb;
				}
				if (!preg_match('/^\d+$/', $cheque->cheque_account)) {
					$chequeErrors[] = 'Cheque Accounts can only contain digits: ' . $chequeData->cheque_account;
				}
				if (!preg_match('/^\d+(\.\d{1,2})?$/', $cheque->amount) || ((float)$cheque->amount < 0.01)) {
					$chequeErrors[] = 'Amounts must be at least $0.01: ' . $chequeData->amount;
				}

				// Check for duplicates (only if there are no other issues)
				if (!count($chequeErrors)) {
					$duplicatesResult = $db->query('
						SELECT p.*
						FROM payment p
							JOIN payment_transaction_data ptd_bsb ON (
								ptd_bsb.payment_id = p.id
								AND ptd_bsb.name = "cheque_bsb"
							)
							JOIN payment_transaction_data ptd_account ON (
								ptd_account.payment_id = p.id
								AND ptd_account.name = "cheque_account"
							)
							JOIN payment_transaction_data ptd_cheque ON (
								ptd_cheque.payment_id = p.id
								AND ptd_cheque.name = "cheque_number"
							)
						WHERE ptd_bsb.value = <cheque_bsb>
							AND ptd_account.value = <cheque_account>
							AND ptd_cheque.value = <cheque_number>
					', array(
						'cheque_bsb' => $cheque->cheque_bsb,
						'cheque_account' => $cheque->cheque_account,
						'cheque_number' => $cheque->cheque_number
					));
					if ($duplicatesResult->num_rows) {
						$duplicate = $duplicatesResult->fetch_object();
						$chequeErrors[] = "Duplicate BSB ({$cheque->cheque_bsb}), Bank Account ({$cheque->cheque_account}), and Cheque Number ({$cheque->cheque_number}) combination found from {$duplicate->paid_date}";
					}
				}

				// Don't save if there are any issues
				if (count($chequeErrors)) {
					$validationErrors[$chequeReference] = $chequeErrors;
					continue;
				}

				// Create Payment
				$payment = Logic_Payment::factory(
					$cheque->account_id,
					PAYMENT_TYPE_CHEQUE,
					$cheque->amount,
					PAYMENT_NATURE_PAYMENT,
					"{$cheque->cheque_bsb}:{$cheque->cheque_account}:{$cheque->cheque_number}",
					null,
					null, // NOTE: Transaction Data isn't actually being saved in Logic_Payment::factory()!!!!
					true
				);
				$payment->save();
				Log::get()->log('Payment added: ' . print_r($payment->toArray(), true));

				// Attach Transaction Data
				$transactionDataChequeNumber = Payment_Transaction_Data::factory('cheque_number', $cheque->cheque_number, array('payment_id' => $payment->id), self::$_chequeTransactionDataSchema);
				$transactionDataChequeBSB = Payment_Transaction_Data::factory('cheque_bsb', $cheque->cheque_bsb, array('payment_id' => $payment->id), self::$_chequeTransactionDataSchema);
				$transactionDataChequeAccount = Payment_Transaction_Data::factory('cheque_account', $cheque->cheque_account, array('payment_id' => $payment->id), self::$_chequeTransactionDataSchema);

				$transactionDataChequeNumber->save();
				$transactionDataChequeBSB->save();
				$transactionDataChequeAccount->save();

				Log::get()->log('Payment Transaction Data added: ' . print_r($transactionDataChequeNumber->toArray(), true));
				Log::get()->log('Payment Transaction Data added: ' . print_r($transactionDataChequeBSB->toArray(), true));
				Log::get()->log('Payment Transaction Data added: ' . print_r($transactionDataChequeAccount->toArray(), true));

				$paymentIds[] = $payment->id;
			}

			if (count($validationErrors)) {
				throw new Exception_Validation('Encountered ' . count($validationErrors) . ' errors with your input');
			}

			//throw new Exception('Test Mode');
			$db->TransactionCommit(false);
		} catch (Exception_Validation $exception) {
			// Validation Exception
			$db->TransactionRollback(false);
			return array(
				'bSuccess' => false,
				'validationErrors' => $validationErrors
			);
		} catch (Exception $exception) {
			// Process Exception
			$db->TransactionRollback(false);
			throw $exception;
		}

		// Success
		return array(
			'bSuccess' => true,
			'paymentIds' => $paymentIds
		);
	}

	public function generateReportForDateRange($fromDate, $toDate=null, $customerGroups=null) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

		// Clean data
		$fromDate = trim($fromDate);
		if (!$toDate) {
			$toDate = $fromDate;
		}
		$toDate = trim($toDate);
		if ($customerGroups === null) {
			$customerGroups = array_keys(Customer_Group::getAll());
		}
		if (is_int($customerGroups)) {
			$customerGroups = array($customerGroups);
		}

		// Validate data
		$validationErrors = array();
		if (strtotime($fromDate) === false || (date('Y-m-d', strtotime($fromDate)) !== $fromDate)) {
			$validationErrors[] = 'Date From isn\'t a valid date: ' . $fromDate;
		}
		if (strtotime($toDate) === false || (date('Y-m-d', strtotime($toDate)) !== $toDate)) {
			$validationErrors[] = 'Date To isn\'t a valid date: ' . $toDate;
		}
		if ($fromDate > $toDate) {
			$validationErrors[] = 'Date To must be later than or equal to Date From';
		}
		if (!is_array($customerGroups)) {
			$validationErrors[] = 'Customer Groups have been specified in an unhandled manner';
		}
		foreach ($customerGroups as $customerGroupId) {
			if (Customer_Group::getForId($customerGroupId) === null) {
				$validationErrors[] = 'Unknown Customer Group encountered';
			}
		}

		if (count($validationErrors)) {
			return array(
				'bSuccess' => false,
				'validationErrors' => $validationErrors
			);
		}

		// Find Payment Ids
		Log::get()->log(sprintf('Finding Cheque Payments between %s and %s for Customer Groups: [%s]', $fromDate, $toDate, implode(', ', $customerGroups)));

		$db = DataAccess::get();
		$paymentsResult = $db->query('
			SELECT p.id
			FROM payment p
				JOIN payment_transaction_data ptd_bsb ON (
					ptd_bsb.payment_id = p.id
					AND ptd_bsb.name = "cheque_bsb"
				)
				JOIN payment_transaction_data ptd_account ON (
					ptd_account.payment_id = p.id
					AND ptd_account.name = "cheque_account"
				)
				JOIN payment_transaction_data ptd_cheque ON (
					ptd_cheque.payment_id = p.id
					AND ptd_cheque.name = "cheque_number"
				)
				JOIN Account a ON (a.Id = p.account_id)
			WHERE p.payment_type_id = (SELECT id FROM payment_type WHERE const_name = \'PAYMENT_TYPE_CHEQUE\')
				AND p.paid_date BETWEEN <from_date> AND <to_date>
				AND a.CustomerGroup IN (<customer_groups>)
		', array(
			'from_date' => $fromDate,
			'to_date' => $toDate,
			'customer_groups' => $customerGroups
		));
		Log::get()->formatLog('Found %d payments using: %s', $paymentsResult->num_rows, $paymentsResult->getQuery());

		$paymentIds = array();
		while ($payment = $paymentsResult->fetch_object()) {
			$paymentIds[] = $payment->id;
		}
		return $this->generateReportForPaymentIds($paymentIds);
	}

	public function generateReportForPaymentIds(array $paymentIds) {
		AuthenticatedUser()->PermissionOrDie(array(PERMISSION_PROPER_ADMIN));

		$db = DataAccess::get();

		$paymentsResult = $db->query('
			SELECT a.Id AS id,
				a.BusinessName AS account_name,
				a.TradingName AS trading_name,
				cg.id AS customer_group_id,
				cg.internal_name AS customer_group_internal_name,
				cg.bank_account_name AS customer_group_bank_account_name,
				cg.bank_bsb AS customer_group_bank_bsb,
				cg.bank_account_number AS customer_group_bank_account,
				ptd_bsb.value AS cheque_bsb,
				ptd_account.value AS cheque_account,
				p.paid_date AS paid_date,
				p.amount AS amount
			FROM payment p
				JOIN payment_transaction_data ptd_bsb ON (
					ptd_bsb.payment_id = p.id
					AND ptd_bsb.name = "cheque_bsb"
				)
				JOIN payment_transaction_data ptd_account ON (
					ptd_account.payment_id = p.id
					AND ptd_account.name = "cheque_account"
				)
				JOIN payment_transaction_data ptd_cheque ON (
					ptd_cheque.payment_id = p.id
					AND ptd_cheque.name = "cheque_number"
				)
				JOIN Account a ON (a.Id = p.account_id)
				JOIN CustomerGroup cg ON (cg.id = a.CustomerGroup)
			WHERE p.payment_type_id = (SELECT id FROM payment_type WHERE const_name = \'PAYMENT_TYPE_CHEQUE\')
				AND p.id IN (<payment_ids>)
		', array(
			'payment_ids' => count($paymentIds) ? $paymentIds : null
		));

		$payments = array();
		while ($payment = $paymentsResult->fetch_object()) {
			$payments[] = $payment;
		}

		$csvLines = array();

		// Header
		$csvLines[] = File_CSV::buildLineRFC4180(array(
			'Customer Group Account Name',
			'Customer Group BSB',
			'Customer Group Account #',
			'Account',
			'Account Name',
			'Trading Name',
			'Cheque BSB',
			'Cheque Account #',
			'Date',
			'Amount'
		));

		// Data
		foreach ($payments as $payment) {
			$csvLines[] = File_CSV::buildLineRFC4180(array(
				$payment->customer_group_bank_account_name,
				$payment->customer_group_bank_bsb,
				$payment->customer_group_bank_account,
				$payment->id,
				$payment->account_name,
				$payment->trading_name,
				$payment->cheque_bsb,
				$payment->cheque_account,
				$payment->paid_date,
				number_format((float)$payment->amount, 2, '.', '')
			));
		}

		return array(
			'bSuccess' => true,
			'cheques' => count($payments),
			'csvData' => implode("\n", $csvLines)
		);
	}
}