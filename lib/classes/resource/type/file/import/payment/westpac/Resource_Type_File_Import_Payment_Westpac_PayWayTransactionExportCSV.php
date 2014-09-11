<?php
class Resource_Type_File_Import_Payment_Westpac_PayWayTransactionExportCSV extends Resource_Type_File_Import_Payment {
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_IMPORT_PAYMENT_WESTPAC_PAYWAYTRANSACTIONEXPORTCSV;

	// Implementation Reference: https://www.payway.com.au/downloads/WBC/PayWay_User_Guide.pdf (p31–39)

	public function getRecords() {
		$sData = @file_get_contents($this->_oFileImport->getWrappedLocation());
		if ($sData === false) {
			throw new Exception('Unable to read in contents of: ' . $this->_oFileImport->getWrappedLocation());
		}

		$aLines = preg_split('/\r?\n/', $sData, null, PREG_SPLIT_NO_EMPTY);

		return $aLines;
	}

	public function processRecord($sRecord) {
		if (!trim($sRecord)) {
			return null;
		}

		if (preg_match('/"?PayWayClientNumber/', $sRecord)) {
			// Header: no-op
			return null;
		} elseif (preg_match('/^"?Q\d+/', $sRecord)) {
			// NOTE: If we ever encounter PayWay Client Numbers that don't match the Q##### form, we'll need to either
			//	remove this, or
			return $this->_processTransaction($sRecord);
		}

		// Unknown or unhandled Record Type
		return null;
	}

	protected function _processTransaction($sRecord) {
		// Process the Record
		//--------------------------------------------------------------------//
		$oRecord = (object)array_associate(array(
			0 => 'payway_client_number',
			1 => 'merchant_id',
			2 => 'card_pan', // Truncated Card Number, preceeded by card "scheme"
			3 => 'card_cvn',
			4 => 'card_expiry', // YYYY-MM
			5 => 'customer_bank_account',
			6 => 'your_bank_account', // Corporate bank account
			7 => 'your_bank_reference',
			8 => 'transaction_source',
			9 => 'order_type',
			10 => 'principal_amount', // Dollars, pre surchage (negative for refund)
			11 => 'surchage_amount', // Dollars
			12 => 'amount', // Dollars, total amount customer was charged
			13 => 'currency',
			14 => 'order_number', // API Transaction Reference (also Recurring Billing Order Number)
			15 => 'customer_reference_number', // Customer Reference number for Virtual Terminal, Net, Phone, or Recurring Billing
			16 => 'customer_name',
			17 => 'eci', // Electronic Commerce Indicator (blank for DD)
			18 => 'user', // Virtual Terminal operator (possible for Recurring Billing)
			19 => 'retry_count', // Recurring Billing only
			20 => 'original_order_number', // Refund API Transactions, referring to the original transaction
			21 => 'original_customer_reference_number', // Refund Virtual Terminal & Recurring Billing, referring to the original transaction
			22 => 'summary_code', // Recommended to determining success/failure
			23 => 'response_code',
			24 => 'response_text',
			25 => 'receipt_number', // PayWay internal receipt number (used for referring to support)
			26 => 'settlement_date', // Date funds are released into corporate account (YYYYMMDD)
			27 => 'card_scheme_name',
			28 => 'credit_group',
			29 => 'transaction_datetime', // Date/time transaction was processed (Sydney time), DD/MM/YYYY HH:MM:SS
			30 => 'status',
			31 => 'authorisation_id',
			32 => 'file_name', // Recurring Billing Upload File filename
			33 => 'bpay_reference',
			34 => 'bpay_reference_for_excel', // Because, Excel
			35 => 'your_surcharge_account',
			36 => 'customer_paypal_account',
			37 => 'your_paypal_account',
			38 => 'parent_transaction_receipt_number' // Refunds, reference to the original transaction's receipt
		), File_CSV::parseLineRFC4180($sRecord));

		// Create a new Payment_Response Record
		//--------------------------------------------------------------------//
		$oPaymentResponse = new Payment_Response();

		// Payment Response Type
		switch (trim($oRecord->order_type)) {
			case self::ORDERTYPE_CAPTURE:
				// CAPTURE represents a regular payment
				$oPaymentResponse->payment_response_type_id = self::_getPaymentResponseTypeForStatus($oRecord);
				if ($oPaymentResponse->payment_response_type_id === PAYMENT_RESPONSE_TYPE_REJECTION) {
					$oPaymentResponse->payment_reversal_reason_id = Payment_Reversal_Reason::getForSystemName('DISHONOUR_REVERSAL')->id;
				}
				break;

			// TODO: Possibly add new Payment Nature "REFUND" to represent "debit" payments that aren't reversals of other payments, though can be related to an "original" payment
			case self::ORDERTYPE_REFUND:
				throw new Exception_Assertion('PayWay Transaction File: Refund Record encountered', $oRecord);

			// TODO: We don't have anything in the system to truly support pre-auths.
			// 	We may want to treat them as CONFIRMATION or ignore them completely.
			case self::ORDERTYPE_PREAUTH:
				throw new Exception_Assertion('PayWay Transaction File: Pre-Auth Record encountered', $oRecord);

			default:
				throw new Exception_Assertion('PayWay Transaction File: Unknown or unhandled Order Type: ' . $oRecord->order_type, $oRecord);
		}

		// Amount
		Flex::assert(trim($oRecord->currency) === self::CURRENCY_AUSTRALIANDOLLAR,
			'PayWay Transaction File: Non-AUD currency encountered: "' . $oRecord->currency . '"',
			$oRecord
		);
		$oPaymentResponse->amount = (float)$oRecord->amount;

		// Paid Date
		$aSettlementDate = array();
		$bSettlementDateMatch = !!preg_match(
			'/^(?<year>\d{4})(?<month>\d{2})(?<day>\d{2})$/',
			$oRecord->settlement_date,
			$aSettlementDate
		);
		Flex::assert($bSettlementDateMatch, 'PayWay Transaction File: Couldn\'t parse Settlement Date: ' . $oRecord->settlement_date, $oRecord);
		$oPaymentResponse->paid_date = "{$aSettlementDate['year']}-{$aSettlementDate['month']}-{$aSettlementDate['day']}";

		// Transaction Reference
		$oPaymentResponse->transaction_reference = trim($oRecord->receipt_number);

		// Payment
		// If this is an update to a payment we've already processed, we want to make sure we're not creating a duplicate
		$oExistingPaymentResult = DataAccess::get()->query('
			SELECT p.id
			FROM payment_response pr
				JOIN payment p ON (p.id = pr.payment_id)
				JOIN payment_response_status prs ON (prs.id = pr.payment_response_status_id)
				JOIN file_import_data fid ON (fid.id = pr.file_import_data_id)
				JOIN FileImport fi ON (
					fi.Id = fid.file_import_id
					AND fi.Carrier = <carrier_id>
					AND fi.FileType = <resource_type_id>
				)
			WHERE pr.transaction_reference = <transaction_reference>
		', array(
			'carrier_id' => $this->getCarrierModule()->Carrier,
			'resource_type_id' => $this->getCarrierModule()->FileType,
			'transaction_reference' => $oPaymentResponse->transaction_reference
		));
		if ($aExistingPayment = $oExistingPaymentResult->fetch_assoc()) {
			$oPaymentResponse->payment_id = $aExistingPayment['id'];
		}

		// Payment Type processing
		switch (trim($oRecord->transaction_source)) {
			case self::TRANSACTIONSOURCE_RECURRING:
				return $this->_processRecurringBilling($oRecord, $oPaymentResponse);

			case self::TRANSACTIONSOURCE_PHONE:
				return $this->_processPhone($oRecord, $oPaymentResponse);

			case self::TRANSACTIONSOURCE_CREDITCARDADHOC:
				return $this->_processCreditCardAdhoc($oRecord, $oPaymentResponse);

			case self::TRANSACTIONSOURCE_NET:
				return $this->_processNet($oRecord, $oPaymentResponse);

			default:
				throw new Exception_Assertion('PayWay Transaction File: Unknown or unhandled Transaction Source: ' . $oRecord->transaction_source, $oRecord);
		}
	}

	private function _processRecurringBilling($oRecord, Payment_Response $oPaymentResponse) {
		// Account/Payment Response
		$aOrderNumber = array();
		$bOrderNumberMatch = !!preg_match('/^(?<account_id>\d+)R(?<payment_request_id>\d+)$/', trim($oRecord->order_number), $aOrderNumber);
		if ($bOrderNumberMatch) {
			// Looks like we have a Payment Request reference
			$oPaymentRequest = Payment_Request::getForId($aOrderNumber['payment_request_id']);
			// Log::get()->log('DEBUG: Creating fake Account record for Id: ' . $aOrderNumber['account_id']);
			// $oPaymentRequest = new Payment_Request(array('id' => intval($aOrderNumber['payment_request_id']), 'account_id' => intval($aOrderNumber['account_id'])));
			$oPaymentResponse->payment_id = $oPaymentRequest->payment_id;
			$oPaymentResponse->account_id = $oPaymentRequest->account_id;
			// DEBUG
			Flex::assert($oPaymentRequest->account_id === intval($aOrderNumber['account_id']),
				'PayWay Transaction File: Payment Reference/Order Number Account mismatch: ' . $oRecord->order_number,
				array(
					'Payment Request' => $oPaymentRequest->toArray(),
					'Order Number: Account' => $aOrderNumber['account_id'],
					'Order Number: Payment Request' => $aOrderNumber['payment_request_id'],
					'Parsed Record' => $oRecord
				)
			);

			// Transaction Reference
			$oPaymentResponse->transaction_reference = trim($oRecord->order_number);
		} else {
			// No explicit Payment Request reference
			$oPaymentResponse->account_id = $this->_getAccountForCustomerReferenceNumber($oRecord)->Id;
		}

		// Payment Type
		$aTransactionData = array();
		if ($oRecord->card_pan) {
			$oPaymentResponse->payment_type_id = PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
		} elseif ($oRecord->customer_bank_account) {
			$oPaymentResponse->payment_type_id = PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
		} else {
			throw new Exception_Assertion('PayWay Transaction File: Unable to determine Recurring Billing Payment Type', $oRecord);
		}

		return array(
			'oRawRecord' => $oRecord,
			'oPaymentResponse' => $oPaymentResponse,
			'aTransactionData' => $this->_getTransactionData($oRecord, $oPaymentResponse)
		);
	}

	private function _processPhone($oRecord, Payment_Response $oPaymentResponse) {
		return $this->_processCreditCard($oRecord, $oPaymentResponse);
	}

	private function _processCreditCardAdhoc($oRecord, Payment_Response $oPaymentResponse) {
		return $this->_processCreditCard($oRecord, $oPaymentResponse);
	}

	private function _processNet($oRecord, Payment_Response $oPaymentResponse) {
		return $this->_processCreditCard($oRecord, $oPaymentResponse);
	}

	private function _processCreditCard($oRecord, Payment_Response $oPaymentResponse) {
		// Payment Type
		$oPaymentResponse->payment_type_id = PAYMENT_TYPE_CREDIT_CARD;

		// Account
		$oPaymentResponse->account_id = $this->_getAccountForCustomerReferenceNumber($oRecord)->Id;

		return array(
			'oRawRecord' => $oRecord,
			'oPaymentResponse' => $oPaymentResponse,
			'aTransactionData' => $this->_getTransactionData($oRecord, $oPaymentResponse)
		);
	}

	private function _getAccountForCustomerReferenceNumber($oRecord) {
		$sCustomerReferenceNumber = trim($oRecord->customer_reference_number);
		if (!preg_match('/^\d+$/', $sCustomerReferenceNumber)) {
			throw new Exception('Customer Reference Number does not appear to refer to a Flex account');
		}

		$iAccountId = intval($oRecord->customer_reference_number);

		// Check digit verification
		if (
			is_array($this->getConfig()->transaction_source_customer_reference_checkdigit)
			&& isset($this->getConfig()->transaction_source_customer_reference_checkdigit[trim($oRecord->transaction_source)])
		) {
			// Check digit
			$sCheckdigitScheme = $this->getConfig()->transaction_source_customer_reference_checkdigit[trim($oRecord->transaction_source)];
			switch (trim(strtoupper($sCheckdigitScheme))) {
				case 'MOD10':
					$iAccountId = substr($sCustomerReferenceNumber, 0, -1);
					$iCustomerReferenceNumberCheckDigit = intval(substr(intval($sCustomerReferenceNumber), -1));
					$iAccountCheckDigit = intval(generateLuhnCheckDigit($iAccountId));
					if ($iCustomerReferenceNumberCheckDigit !== $iAccountCheckDigit) {
						throw new Exception("Client Reference Number Check Digit '{$iCustomerReferenceNumberCheckDigit}' doesn't match calculated value of '{$iAccountCheckDigit}' for Account '{$iAccountId}'");
					}
					break;

				default:
					throw new Exception_Assertion(
						sprintf('PayWay Transaction File (Carrier Module: #%d): Invalid Customer Reference Checkdigit scheme: %s for Tranaction Source: %s',
							$this->getCarrierModule()->Id,
							$sCheckdigitScheme,
							trim($oRecord->transaction_source)
						),
						array(
							'Parsed Record' => $oRecord,
							'Carrier Module Config' => $this->getConfig()->toArray()
						)
					);
			}
		}

		// Ensure the Account exists
		$oAccount = Account::getForId($iAccountId);
		if ($oAccount === null) {
			throw new Exception(sprintf('Can\'t find Account from Customer Reference Number: %s (derived Account number: %d)', $oRecord->customer_reference_number, $iAccountId));
			// Log::get()->log('DEBUG: Creating fake Account record for Id: ' . $iAccountId);
			// $oAccount = new Account(array('Id' => $iAccountId));
		}
		return $oAccount;
	}

	private function _getTransactionData($oRecord, Payment_Response $oPaymentResponse) {
		$aTransactionData = array();

		if (trim($oRecord->card_pan)) {
			// Credit Card Number/Type
			$aCardPAN = array();
			if (!preg_match('/(?<card_scheme>[A-Z]+)\s+(?<card_number>\d+(?:\.+\d+)?)/', trim($oRecord->card_pan), $aCardPAN)) {
				throw new Exception_Assertion('PayWay Transaction File: Unable to process Card PAN: ' . $oRecord->card_pan, $oRecord);
			}

			$aTransactionData []= Payment_Transaction_Data::factory(Payment_Transaction_Data::CREDIT_CARD_NUMBER, $aCardPAN['card_number']);
			$aTransactionData []= Payment_Transaction_Data::factory(Payment_Transaction_Data::CREDIT_CARD_TYPE, self::_getCreditCardTypeForCardScheme($aCardPAN['card_scheme'], $oRecord));
		}
		if (trim($oRecord->customer_bank_account)) {
			// Bank BSB/Account
			$aCardPAN = array();
			if (!preg_match('/(?<bank_bsb>\d{3}-\d{3})\s+(?<bank_account>\d+)/', trim($oRecord->customer_bank_account), $aCardPAN)) {
				throw new Exception_Assertion('PayWay Transaction File: Unable to process Customer Bank Account: ' . $oRecord->customer_bank_account, $oRecord);
			}

			$aTransactionData []= Payment_Transaction_Data::factory(Payment_Transaction_Data::BANK_ACCOUNT_NUMBER, $aCardPAN['bank_account']);
			$aTransactionData []= Payment_Transaction_Data::factory(Payment_Transaction_Data::BANK_ACCOUNT_BSB, $aCardPAN['bank_bsb']);
		}

		// PayWay Receipt Number
		$aTransactionData []= Payment_Transaction_Data::factory(self::PAYMENTTRANSACTIONDATA_PAYWAYRECEIPTNUMBER, trim($oRecord->receipt_number), null, self::$_aTransactionDataSchema);

		// Response Code
		$aTransactionData []= Payment_Transaction_Data::factory(self::PAYMENTTRANSACTIONDATA_RESPONSECODE, trim($oRecord->response_code), null, self::$_aTransactionDataSchema);

		// Response Description
		$aTransactionData []= Payment_Transaction_Data::factory(self::PAYMENTTRANSACTIONDATA_RESPONSEDESCRIPTION, trim($oRecord->response_text), null, self::$_aTransactionDataSchema);

		return $aTransactionData;
	}

	private static function _getPaymentResponseTypeForStatus($oRecord) {
		switch (strtoupper(trim($oRecord->status))) {
			case self::STATUS_APPROVED:
			case self::STATUS_APPROVEDRETURNABLE:
				return PAYMENT_RESPONSE_TYPE_CONFIRMATION;

			case self::STATUS_DECLINED:
				return PAYMENT_RESPONSE_TYPE_REJECTION;

			case self::STATUS_VOIDED:
				// NOTE: This is an assumed equivalent
				return PAYMENT_RESPONSE_TYPE_REJECTION;

			default:
				throw new Exception_Assertion('PayWay Transaction File: Unknown or unhandled Status: ' . $oRecord->status, $oRecord);
		}
	}

	private static function _getCreditCardTypeForCardScheme($sCardScheme, $oRecord) {
		switch (trim($sCardScheme)) {
			case self::CARDSCHEME_VISA:
			case self::CARDSCHEME_ABBREVIATED_VISA:
				return CREDIT_CARD_TYPE_VISA;

			case self::CARDSCHEME_MASTERCARD:
			case self::CARDSCHEME_ABBREVIATED_MASTERCARD:
				return CREDIT_CARD_TYPE_MASTERCARD;

			case self::CARDSCHEME_AMEX:
			case self::CARDSCHEME_ABBREVIATED_AMEX:
				return CREDIT_CARD_TYPE_AMEX;

			case self::CARDSCHEME_ABBREVIATED_DINERS:
				return CREDIT_CARD_TYPE_DINERS;
		}

		throw new Exception_Assertion('PayWay Transaction File: Unknown or unhandled Card Scheme: ' . $sCardScheme, $oRecord);
	}

	const PAYMENTTRANSACTIONDATA_PAYWAYRECEIPTNUMBER = 'payway_receipt_number';
	const PAYMENTTRANSACTIONDATA_RESPONSECODE = 'response_code';
	const PAYMENTTRANSACTIONDATA_RESPONSEDESCRIPTION = 'response_description';
	private static $_aTransactionDataSchema = array(
		self::PAYMENTTRANSACTIONDATA_PAYWAYRECEIPTNUMBER => array(
			'iDataType' => DATA_TYPE_STRING
		),
		self::PAYMENTTRANSACTIONDATA_RESPONSECODE => array(
			'iDataType' => DATA_TYPE_STRING
		),
		self::PAYMENTTRANSACTIONDATA_RESPONSEDESCRIPTION => array(
			'iDataType' => DATA_TYPE_STRING
		)
	);

	const TRANSACTIONSOURCE_RECURRING = 'RECURRING';
	const TRANSACTIONSOURCE_CREDITCARDAPI = 'CREDIT_CARD_API';
	const TRANSACTIONSOURCE_NET = 'NET';
	const TRANSACTIONSOURCE_PHONE = 'PHONE';
	const TRANSACTIONSOURCE_BPAY = 'BPAY';
	const TRANSACTIONSOURCE_AUSTRALIAPOST = 'AUSTRALIA_POST';
	const TRANSACTIONSOURCE_CREDITCARDADHOC = 'ADHOC_CC';

	const ORDERTYPE_CAPTURE = 'Capture'; // Sale/purchase
	const ORDERTYPE_REFUND = 'Refund';
	const ORDERTYPE_PREAUTH = 'Pre-Auth';

	const CURRENCY_AUSTRALIANDOLLAR = 'AUD';

	const STATUS_APPROVED = 'APPROVED';
	const STATUS_APPROVEDRETURNABLE = 'APPROVED*';
	const STATUS_DECLINED = 'DECLINED';
	const STATUS_VOIDED = 'VOIDED';

	const CARDSCHEME_VISA = 'VISA';
	const CARDSCHEME_MASTERCARD = 'MASTERCARD';
	const CARDSCHEME_AMEX = 'AMEX';

	const CARDSCHEME_ABBREVIATED_VISA = 'VI';
	const CARDSCHEME_ABBREVIATED_MASTERCARD = 'MC';
	const CARDSCHEME_ABBREVIATED_AMEX = 'AX';
	const CARDSCHEME_ABBREVIATED_DINERS = 'DC';

	public static function test($sFilePath='php://stdin', $iCarrierModuleId=null) {
		$oDB = DataAccess::get();
		$oDB->TransactionStart(false);
		try {
			// Fake FileImport and CarrierModule records
			if ($iCarrierModuleId === null) {
				$oCarrierModule = new Carrier_Module(array());
			} else {
				$oCarrierModule = Carrier_Module::getForId($iCarrierModuleId);
			}
			$oFileImport = new File_Import(array(
				'Location' => $sFilePath
			));

			$oInstance = new self($oCarrierModule, $oFileImport);

			// Get Records
			$aRecords = $oInstance->getRecords();

			// Process Records
			foreach ($aRecords as $mKey=>$sRecord) {
				try {
					$mRecordData = $oInstance->processRecord($sRecord);
				} catch (Exception $oRecordException) {
					// If the normalisation fails, log and continue
					Log::get()->formatLog('[!] Record #%s (%s) erred: %s',
						$mKey,
						var_export($sRecord, true),
						$oRecordException->__toString()
					);
					continue;
				}
				Log::get()->log(sprintf('[+] Record #%s (%s) produced: %s',
					$mKey,
					var_export($sRecord, true),
					print_r(self::_debugTestData($mRecordData), true)
				));
			}
		} catch (Exception $oException) {
			$oDB->TransactionRollback(false);
			throw $oException;
		}
		$oDB->TransactionRollback(false); // ALWAYS ROLL BACK
	}

	private static function _debugTestData($mResultData) {
		if (is_array($mResultData)) {
			$aDebugData = array();
			foreach ($mResultData as $mKey=>$mResultDataItem) {
				$aDebugData[$mKey] = self::_debugTestData($mResultDataItem);
			}
			return $aDebugData;
		}

		if (is_object($mResultData) && method_exists($mResultData, 'toArray')) {
			return $mResultData->toArray();
		}

		return $mResultData;
	}

	/***************************************************************************
	 * COMMON METHODS FOR ALL Resource_Type_Base CHILDREN
	 **************************************************************************/

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClass, self::RESOURCE_TYPE);
	}

	static public function defineCarrierModuleConfig() {
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'transaction_source_customer_reference_checkdigit' => array(
				'Description' => 'Configures which Transaction Sources have checkdigits, and how that checkdigit is derived',
				'Type' => DATA_TYPE_ARRAY,
				'Value' => array()
			)
		));
	}
}