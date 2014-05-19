<?php
class Resource_Type_File_Import_Payment_Westpac_RECall extends Resource_Type_File_Import_Payment {
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC_RECALL;

	// Implementation Reference: https://www.payway.com.au/downloads/WBC/PayWay_User_Guide.pdf (p40–48)

	/*
		HEADER:
001784TELCO BLUE                  20333054958850000011112013

		TRANSACTION:
110001954980                  B00000004368IB          2   0000000000CBA0911201306486118119310000

		TRAILER:
9000000051000000000000000000501074000000000000000000000000000000000000000000000000000000000000000000000000000
	*/

	const RECORD_TYPE_HEADER = '0';
	const RECORD_TYPE_TRANSACTION = '1';
	const RECORD_TYPE_TRAILER = '9';

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

		switch ($sRecord[0]) {
			case self::RECORD_TYPE_HEADER:
				$this->_processHeader($sRecord);
				break;

			case self::RECORD_TYPE_TRANSACTION:
				return $this->_processTransaction($sRecord);
		}

		// Unknown or unhandled Record Type
		return null;
	}

	private function _processHeader($sRecord) {
		// Process the Record
		//--------------------------------------------------------------------//
		$oRecord = (object)self::_extractFixed(array(
			'record_type' => array('start' => 0, 'length' => 1),
			'payway_client_code' => array('start' => 1, 'length' => 5),
			'client_name' => array('start' => 6, 'length' => 28),
			'entry_state' => array('start' => 34, 'length' => 1),
			'biller_bsb' => array('start' => 35, 'length' => 6),
			'biller_account' => array('start' => 41, 'length' => 6),
			'unit_charge' => array('start' => 47, 'length' => 5), // Will be zeros
			'processing_day' => array('start' => 52, 'length' => 2),
			'processing_month' => array('start' => 54, 'length' => 2),
			'processing_year' => array('start' => 56, 'length' => 4),
			// Filler
		), $sRecord);

		$this->_sProcessingDate = "{$oRecord->processing_year}-{$oRecord->processing_month}-{$oRecord->processing_day}";
	}

	protected function _processTransaction($sRecord) {
		// Process the Record
		//--------------------------------------------------------------------//
		$oRecord = (object)self::_extractFixed(array(
			'record_type' => array('start' => 0, 'length' => 1),
			'customer_reference' => array('start' => 1, 'length' => 29),
			'transaction_nature' => array('start' => 30, 'length' => 1),
			'amount' => array('start' => 31, 'length' => 11),
			'originating_system' => array('start' => 42, 'length' => 2),
			'receipt_number' => array('start' => 44, 'length' => 8), // Blank for BPAY and Australia Post transactions
			'voucher_trace_number' => array('start' => 52, 'length' => 16), // Will be blank
			'extended_receipt_number' => array('start' => 68, 'length' => 21),
			'transaction_type' => array('start' => 89, 'length' => 4),
			'transaction_sequence' => array('start' => 93, 'length' => 3) // Will be blank
			// Filler
		), $sRecord);

		// Create a new Payment_Response Record
		//--------------------------------------------------------------------//
		$oPaymentResponse = new Payment_Response();

		// Payment Response Type
		// TODO: Support refund notifications
		Flex::assert($oRecord->transaction_nature === self::TRANSACITON_NATURE_PAYMENT,
			'Westpac RECall: Encountered Refund Record (unsupported)',
			array(
				'Source' => $sRecord,
				'Parsed' => $oRecord
			)
		);
		$oPaymentResponse->payment_response_type_id = PAYMENT_RESPONSE_TYPE_CONFIRMATION;

		// Amount
		$oPaymentResponse->amount = round((float)preg_replace('/(0*)(\d+)(\d{2})/', '$2.$3', $oRecord->amount), 2);

		// Payment Type-specific processing
		// TODO: Support all originating systems/transaction types
		switch ($oRecord->originating_system) {
			case self::ORIGINATING_SYSTEM_BPAY:
				$oPaymentResponse = $this->_processTransactionBPAY($oRecord, $oPaymentResponse);
				break;

			case self::ORIGINATING_SYSTEM_AUSPOST:
				$oPaymentResponse = $this->_processTransactionAustraliaPost($oRecord, $oPaymentResponse);
				break;

			default:
				throw new Exception_Assertion(
					'Westpac RECall: Encountered unhanded/unrecognised "originating system": '
						. var_export($oRecord->originating_system, true)
						. (
							isset(self::$_aPayWayModules[$oRecord->originating_system])
								? '(' . self::$_aPayWayModules[$oRecord->originating_system] . ')'
								: ''
						),
					array(
						'Source' => $sRecord,
						'Parsed' => $oRecord
					)
				);
		}

		// Return an Array of Records added/modified
		//--------------------------------------------------------------------//
		return array(
			'oRawRecord' => $oRecord,
			'oPaymentResponse' => $oPaymentResponse
		);
	}

	private function _processTransactionBPAY($oRecord, Payment_Response $oPaymentResponse) {
		// Payment Type
		$oPaymentResponse->payment_type_id = PAYMENT_TYPE_BPAY;

		// Paid Date
		$oExtendedReceipt = (object)self::_extractFixed(array(
			'bank' => array('start' => 0, 'length' => 3),
			'day' => array('start' => 3, 'length' => 2),
			'month' => array('start' => 5, 'length' => 2),
			'year' => array('start' => 7, 'length' => 4),
			'receipt' => array('start' => 11)
		), $oRecord->extended_receipt_number);
		$oPaymentResponse->paid_date = "{$oExtendedReceipt->year}-{$oExtendedReceipt->month}-{$oExtendedReceipt->day}";

		// Account
		$sCustomerReference = trim($oRecord->customer_reference);
		$iCustomerReferenceCheckDigit = (int)substr($sCustomerReference, -1);
		$oPaymentResponse->account_id = (int)substr($sCustomerReference, 0, -1);
		$iCalculatedAccountCheckDigit = (int)MakeLuhn($oPaymentResponse->account_id);
		if ($iCalculatedAccountCheckDigit !== $iCustomerReferenceCheckDigit) {
			throw new Exception("Client Reference Check Digit '{$iCustomerReferenceCheckDigit}' doesn't match calculated value of '{$iCalculatedAccountCheckDigit}' for Account '{$oPaymentResponse->account_id}'");
		}

		// Transaction Data
		// No additional transaction data

		// Transaction Reference
		$oPaymentResponse->transaction_reference = trim($oRecord->extended_receipt_number);

		return $oPaymentResponse;
	}

	private function _processTransactionAustraliaPost($oRecord, Payment_Response $oPaymentResponse) {
		// Payment Type
		$oPaymentResponse->payment_type_id = PAYMENT_TYPE_AUSTRALIAPOST;

		// Paid Date
		// NOTE: No date more specific than file processing date
		$oPaymentResponse->paid_date = $this->_sProcessingDate;

		// Account
		$sCustomerReference = trim($oRecord->customer_reference);
		$iCustomerReferenceCheckDigit = (int)substr($sCustomerReference, -1);
		$oPaymentResponse->account_id = (int)substr($sCustomerReference, 0, -1);
		$iCalculatedAccountCheckDigit = (int)MakeLuhn($oPaymentResponse->account_id);
		if ($iCalculatedAccountCheckDigit !== $iCustomerReferenceCheckDigit) {
			throw new Exception("Client Reference Check Digit '{$iCustomerReferenceCheckDigit}' doesn't match calculated value of '{$iCalculatedAccountCheckDigit}' for Account '{$oPaymentResponse->account_id}'");
		}

		// Transaction Data
		// No additional transaction data

		// Transaction Reference
		$oPaymentResponse->transaction_reference = trim($oRecord->extended_receipt_number);

		return $oPaymentResponse;
	}

	private static function _processTransactionRecurringBilling() {
		// TODO
	}

	private static function _extractFixed(array $aDefinition, $sRecord) {
		$aParsed = array();
		foreach ($aDefinition as $mKey=>$aField) {
			if (isset($aField['length'])) {
				$aParsed[$mKey] = substr($sRecord, $aField['start'], $aField['length']);
			} elseif (isset($aField['end'])) {
				$aParsed[$mKey] = substr($sRecord, $aField['start'], $aField['end'] - $aField['start']);
			} else {
				$aParsed[$mKey] = substr($sRecord, $aField['start']);
			}
		}
		return $aParsed;
	}

	const TRANSACITON_NATURE_PAYMENT = 'B';
	const TRANSACTION_NATURE_REFUND = 'R';

	const ORIGINATING_SYSTEM_BPAY = 'IB';
	const ORIGINATING_SYSTEM_AUSPOST = 'AP';
	private static $_aPayWayModules = array(
		'CD' => 'PayWay Phone',
		'NC' => 'PayWay Net and PayWay Virtual Terminal',
		'CC' => 'PayWay API',
		'RD' => 'PayWay Recurring Billing Direct Debit',
		'RC' => 'PayWay Recurring Billing Credit Card',
		self::ORIGINATING_SYSTEM_BPAY => 'BPAY',
		self::ORIGINATING_SYSTEM_AUSPOST => 'Australia Post'
	);

	private static $_aRECallTransacitonTypes = array(
		'9300' => 'BPAY - (WBC) Debit Account',
		'9301' => 'BPAY - (WBC) Visa card',
		'9302' => 'BPAY - (WBC) MasterCard',
		'9303' => 'BPAY - (WBC) Bankcard',
		'9304' => 'BPAY – Internet WBC Debit',
		'9305' => 'BPAY – Internet WBC Visa',
		'9306' => 'BPAY – Internet WBC MasterCard',
		'9307' => 'BPAY – Internet WBC Bankcard',
		'9310' => 'BPAY - (other bank) Debit Account',
		'9311' => 'BPAY - (other bank) Visa card',
		'9312' => 'BPAY - (other bank) MasterCard',
		'9313' => 'BPAY - (other bank) Bankcard',
		'9314' => 'BPAY – Electronic Bill Presentment – from Debit account',
		'9315' => 'BPAY – Electronic Bill Presentment – from a Visa',
		'9316' => 'BPAY – Electronic Bill Presentment – from a Mastercard',
		'9317' => 'BPAY – Electronic Bill Presentment – from a Bankcard',
		'9318' => 'BPAY – Electronic Bill Presentment – from a Debit account',
		'9319' => 'BPAY – Electronic Bill Presentment – from a Visa',
		'9320' => 'BPAY – Electronic Bill Presentment – from a Mastercard',
		'9321' => 'BPAY – Electronic Bill Presentment – From a Bankcard',
		'9602' => 'Australia Post Third Party Bill Payment',
		'9603' => 'Australia Post Adjustments / Error Corrections',
		'9604' => 'Australia Post Dishonour',
		'9605' => 'Australia Post Dishonour Fee',
		'9606' => 'Australia Post NSW Government Deposits',
		'9610' => 'Australia Post Third Party Bill Payment - BankCard',
		'9611' => 'Australia Post Third Party Bill Payment – Visa',
		'9612' => 'Australia Post Third Party Bill Payment – MasterCard',
		'9613' => 'Australia Post Third Party Bill Payment – non-credit card Telephone BillPay',
		'9614' => 'Australia Post Third Party Bill Payment – non-credit card Internet BillPay',
		'9615' => 'Australia Post Third Party Bill Payment – Credit Card Charge Back',
		'9616' => 'Australia Post Third Party Bill Payment – credit card Telephone BillPay',
		'9617' => 'Australia Post Third Party Bill Payment – credit card Internet BillPay',
		'9700' => 'PayWay Virtual Terminal',
		'9701' => 'PayWay API',
		'9702' => 'PayWay Recurring Billing Direct Debit',
		'9703' => 'PayWay Recurring Billing Credit Card',
		'9704' => 'PayWay Net',
		'9705' => 'PayWay Phone'
	);

	public static function test($sFilePath='php://stdin') {
		$oDB = DataAccess::get();
		$oDB->TransactionStart(false);
		try {
			// Fake FileImport and CarrierModule records
			$oCarrierModule = new Carrier_Module(array());
			$oFileImport = new File_Import(array(
				'Location' => $sFilePath
			));

			$oInstance = new self($oCarrierModule, $oFileImport);

			// Get Records
			$aRecords = $oInstance->getRecords();

			// Process Records
			foreach ($aRecords as $mKey=>$sRecord) {
				$mRecordData = $oInstance->processRecord($sRecord);
				Log::get()->log(sprintf('[+] Record #%s (%s) produced: %s',
					$mKey,
					var_export($sRecord, true),
					self::_debugTestData($mRecordData)
				));
			}
		} catch (Exception $oException) {
			$oDB->TransactionRollback(false);
			throw $oException;
		}
		$oDB->TransactionRollback(false); // ALWAYS ROLL BACK
	}

	private static function _debugTestData($mResultData) {
		if (is_scalar($mResultData)) {
			return var_export($mResultData, true);
		}

		if (is_array($mResultData)) {
			$aDebugData = array();
			foreach ($mResultData as $mKey=>$mResultDataItem) {
				if (is_object($mResultDataItem) && method_exists($mResultDataItem, 'toArray')) {
					$aDebugData[$mKey] = $mResultDataItem->toArray();
				} else {
					$aDebugData[$mKey] = $mResultDataItem;
				}
			}
			return print_r($aDebugData, true);
		}

		return print_r($mResultData, true);
	}

	/***************************************************************************
	 * COMMON METHODS FOR ALL Resource_Type_Base CHILDREN
	 **************************************************************************/

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClass, self::RESOURCE_TYPE);
	}

	static public function defineCarrierModuleConfig() {
		return array_merge(parent::defineCarrierModuleConfig(), array());
	}
}