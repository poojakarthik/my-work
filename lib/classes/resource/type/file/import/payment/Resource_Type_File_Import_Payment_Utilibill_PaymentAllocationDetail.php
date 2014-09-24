<?php
class Resource_Type_File_Import_Payment_Utilibill_PaymentAllocationDetail extends Resource_Type_File_Import_Payment {
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_IMPORT_PAYMENT_UTILIBILL_PAYMENTALLOCATIONDETAIL;

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

		if (preg_match('/"?MGR Payment Allocation Detail Report/', $sRecord)) {
			// Header: no-op
			return null;
		} elseif (preg_match('/"?This report was generated/', $sRecord)) {
			// Header: no-op
			return null;
		} elseif (preg_match('/"?Group Name/', $sRecord)) {
			// Header: no-op
			return null;
		} else {
			return $this->_processTransaction($sRecord);
		}

		// Unknown or unhandled Record Type
		return null;
	}

	protected function _processTransaction($sRecord) {
		// Process the Record
		//--------------------------------------------------------------------//
		$oRecord = (object)array_associate(array(
			0 => 'group_name',
			1 => 'group_number',
			2 => 'customer_number',
			3 => 'transaction_date', // DD-MM-YYYY HH:MM A
			4 => 'cleared_payment_date', // DD-MM-YYYY HH:MM A
			5 => 'payment_type',
			6 => 'card_type',
			7 => 'allocated_amount',
			8 => 'allocated_statement_number'
		), File_CSV::parseLineRFC4180($sRecord));

		// Create a new Payment_Response Record
		//--------------------------------------------------------------------//
		$oPaymentResponse = new Payment_Response();

		// Payment Response Type
		$oPaymentResponse->payment_response_type_id = PAYMENT_RESPONSE_TYPE_CONFIRMATION;

		// Amount
		$oPaymentResponse->amount = floatval(str_replace(',', '', $oRecord->allocated_amount));

		// Payment Type
		$oPaymentResponse->payment_type_id = self::_getFlexPaymentType($oRecord);

		// Paid Date
		$oPaymentResponse->paid_date = date('Y-m-d', strtotime(self::_parseDate($oRecord->cleared_payment_date, $oRecord)));

		// Account
		$sRegex = $this->getConfig()->account_number_translation_regex;
		$sRegexReplace = $this->getConfig()->account_number_translation_regex_replace;
		if ($sRegex && $sRegexReplace) {
			$iAccountId = intval(preg_replace($sRegex, $sRegexReplace, trim($oRecord->customer_number)));
		} else {
			$iAccountId = intval(trim($oRecord->customer_number));
		}

		$oAccount = Account::getForId($iAccountId);
		if (!$oAccount) {
			throw new Exception('Unable to find Account with Id: ' . var_export($iAccountId, true) . ' from: ' . print_r($oRecord, true));
		}
		$oPaymentResponse->account_id = $oAccount->Id;
		// $oPaymentResponse->account_id = $iAccountId; // DEBUG ONLY

		// Account Group
		$oPaymentResponse->account_group_id = $oAccount->AccountGroup;

		// Transaction Data
		$aTransactionData = array();
		if (trim($oRecord->card_type)) {
			// Card Type
			$aTransactionData[]	= Payment_Transaction_Data::factory(Payment_Transaction_Data::CREDIT_CARD_TYPE, self::_getFlexCreditCardType($oRecord));
		}

		// Transaction Reference
		$oPaymentResponse->transaction_reference = sprintf('%s%s:%s:%s:%d:%f@%s',
			$this->getConfig()->transaction_reference_prefix ? $this->getConfig()->transaction_reference_prefix . ':' : '',
			$oRecord->group_number,
			$oRecord->customer_number,
			self::_constantifyUtilibillPaymentType($oRecord->payment_type),
			$oRecord->allocated_statement_number,
			$oPaymentResponse->amount,
			date('YmdHis', strtotime(self::_parseDate($oRecord->transaction_date, $oRecord)))
		);

		// NOTE: Because this is not a true payment export file (just a report), there can be gaps/duplicates, and we want to prevent these issues,
		// 	and because there is no "unique identifier" for PADR records, we need to use our hacked-together transaction reference to detect this.
		$bDuplicate = !!(DataAccess::get()->query('
			SELECT pr.id
			FROM payment_response pr
				JOIN payment_response_status prs ON (
					prs.id = pr.payment_response_status_id
					AND prs.system_name = \'PROCESSED\'
				)
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
		))->num_rows);

		if ($bDuplicate) {
			// Create a ticket with the error
			$this->_createProcessTransactionExceptionTicket($oRecord, $oPaymentResponse);
			throw new Exception('Utilibill Payment Allocation Detail: Duplicate payment response found with transaction reference: ' . var_export($oPaymentResponse->transaction_reference, true) . ' and same Carrier/Resource Type');
		}

		// Return an Array of Records added/modified
		//--------------------------------------------------------------------//
		return array(
			'oRawRecord' => $oRecord,
			'oPaymentResponse' => $oPaymentResponse,
			'aTransactionData' => $aTransactionData
		);
	}

	protected function _createProcessTransactionExceptionTicket($oRecord, $oPaymentResponse) {
		// Each email should be processed in its own db transaction,
		// as each email will be deleted separately
		$dbAccess = DataAccess::getDataAccess();
		$dbAccess->TransactionStart(false)
		try {
			$oAccount = Account::getForId($oPaymentResponse->account_id);
			$oCustomerGroup = Customer_Group::getForId($oAccount->CustomerGroup);
			$oCustGroupConfig = Ticketing_Customer_Group_Config::getForCustomerGroupId($oCustomerGroup->Id);

			// Ticket Details
			$aTicketDetails = array();
			$aTicketDetails['default_email_id'] = $oCustGroupConfig->default_email_id;
			$aTicketDetails['customer_group_id'] = $oCustGroupConfig->customer_group_id;
			$aTicketDetails['from'] = array();
			$aTicketDetails['from']['address'] = $oCustomerGroup->outbound_email;
			$aTicketDetails['from']['name'] = null;
			$aTicketDetails['subject'] = 'Exception report failed payment import check';
			$aTicketDetails['timestamp'] = date('Y-m-d H:i:s');

			$aTicketDetails['message'] = "
				Utilibill Payment Allocation Detail: Duplicate payment response found with transaction reference

				Group Name: {$oRecord->group_name}
				Customer: {$oAccount->Id}
				Transaction Date: {$oRecord->transaction_date}
				Cleared Payment Date: {$oRecord->cleared_payment_date}
				Payment Type: {$oRecord->payment_type}
				Allocated Amount: {$oRecord->allocated_amount}
				Allocated Statement Number: {$oRecord->allocated_statement_number}

				Please investigate and manually apply payment if required.";
			$aTicketDetails['attachments'] = array();

			// Check that there is a sender
			$oCorrespondence = null;

			if (array_key_exists('from', $aTicketDetails)) {
				// Set delivery status to received (this is inbound)
				$aTicketDetails['delivery_status'] = TICKETING_CORRESPONDANCE_DELIVERY_STATUS_RECEIVED;

				// XML files originate from emails
				$aTicketDetails['source_id'] = TICKETING_CORRESPONDANCE_SOURCE_WEB;
				$aTicketDetails['user_id'] = null;

				// Set delivery time (to system) same as creation time (now)
				$aTicketDetails['delivery_datetime'] = $aTicketDetails['creation_datetime'] = date('Y-m-d H:i:s');

				// Load the details into the ticketing system
				$oCorrespondence = Ticketing_Correspondance::createForDetails($aTicketDetails, true);
			}
			$dbAccess->TransactionCommit();
		} catch (Exception $oException) {
			$dbAccess->TransactionRollback();
			throw $oException;
		}
	}

	private static function _parseDate($sUtilibillDate, $oRecord) {
		$aDate = array();
		if (!preg_match('/^(?<day>\d{2})\/(?<month>\d{2})\/(?<year>\d{4})\s+(?<hour>\d{2}):(?<minute>\d{2})\s*(?<meridian>[AP]M)$/', trim($sUtilibillDate), $aDate)) {
			throw new Exception_Assertion('Utilibill Payment Allocation Detail: Unable to parse date: ' . var_export($sUtilibillDate), $oRecord);
		}
		return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
			$aDate['year'],
			$aDate['month'],
			$aDate['day'],
			($aDate['meridian'] === 'PM') ? 12 + intval($aDate['hour']) : $aDate['hour'],
			$aDate['minute'],
			0 // Source doesn't supply seconds
		);
	}

	private static function _constantifyUtilibillPaymentType($sUtilibillPaymentType) {
		return strtoupper(preg_replace('/\s+/', '_', trim($sUtilibillPaymentType)));
	}

	private static function _getFlexPaymentType($oRecord) {
		switch (self::_constantifyUtilibillPaymentType($oRecord->payment_type)) {
			case self::PAYMENTTYPE_AMERICANEXPRESS:
			case self::PAYMENTTYPE_MASTERCARD:
			case self::PAYMENTTYPE_VISA:
				return PAYMENT_TYPE_CREDIT_CARD;

			case self::PAYMENTTYPE_BANKPAYMENTDIRECTDEBIT:
				return PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;

			case self::PAYMENTTYPE_BANKPAYMENTWESTPAC:
				return PAYMENT_TYPE_EFT;

			case self::PAYMENTTYPE_AUSTRALIAPOST:
				return PAYMENT_TYPE_AUSTRALIAPOST;

			case self::PAYMENTTYPE_BPAY:
				return PAYMENT_TYPE_BPAY;

			case self::PAYMENTTYPE_CHEQUE:
				return PAYMENT_TYPE_CHEQUE;
		}

		throw new Exception_Assertion('Utilibill Payment Allocation Detail: Unknown Payment Type: ' . $oRecord->payment_type, array(self::_constantifyUtilibillPaymentType($oRecord->payment_type), $oRecord));
	}

	private static function _getFlexCreditCardType($oRecord) {
		switch (trim($oRecord->card_type)) {
			case self::CARDTYPE_AMERICANEXPRESS:
				return CREDIT_CARD_TYPE_AMEX;

			case self::CARDTYPE_MASTERCARD:
				return CREDIT_CARD_TYPE_MASTERCARD;

			case self::CARDTYPE_VISA:
				return CREDIT_CARD_TYPE_VISA;

			case self::CARDTYPE_DINERSCLUB:
				return CREDIT_CARD_TYPE_DINERS;
		}

		throw new Exception_Assertion('Utilibill Payment Allocation Detail: Unknown Credit Card Type: ' . $oRecord->card_type, array(self::_constantifyUtilibillPaymentType($oRecord->card_type), $oRecord));
	}

	const PAYMENTTYPE_BANKPAYMENTDIRECTDEBIT = 'BANK_PAYMENT_DIRECT_DEBIT';
	const PAYMENTTYPE_BANKPAYMENTWESTPAC = 'BANK_PAYMENT_WESTPAC';
	const PAYMENTTYPE_AUSTRALIAPOST = 'AUSTRALIA_POST';
	const PAYMENTTYPE_BPAY = 'BPAY';
	const PAYMENTTYPE_CHEQUE = 'CHEQUE';
	const PAYMENTTYPE_AMERICANEXPRESS = 'AMERICAN_EXPRESS';
	const PAYMENTTYPE_MASTERCARD = 'MASTER_CARD';
	const PAYMENTTYPE_VISA = 'VISA';
	const PAYMENTTYPE_DINERSCLUB = 'DINERS_CLUB';

	const CARDTYPE_AMERICANEXPRESS = 'AX';
	const CARDTYPE_MASTERCARD = 'MA';
	const CARDTYPE_VISA = 'VI';
	const CARDTYPE_DINERSCLUB = 'DI';

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
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'transaction_reference_prefix' => array(
				'Description' => 'Prefix to apply to the generated Transaction Reference'
			),
			'account_number_translation_regex' => array(
				'Description' => 'PCRE Regular Expression to match components of the Utilibill Customer Number to replace ([match] part of s/[match]/[replace]/g expressions)'
			),
			'account_number_translation_regex_replace' => array(
				'Description' => 'Replacements string to translate the Utilibill Customer Number to replace ([replace] part of s/[match]/[replace]/g expressions)'
			)
		));
	}
}