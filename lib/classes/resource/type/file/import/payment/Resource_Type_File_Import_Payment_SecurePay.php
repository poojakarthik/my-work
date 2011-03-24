<?php
/**
 * Resource_Type_File_Import_Payment_SecurePay
 *
 * @class	Resource_Type_File_Import_Payment_SecurePay
 */
class Resource_Type_File_Import_Payment_SecurePay extends Resource_Type_File_Import_Payment
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD;
	
	const	NEW_LINE_DELIMITER	= "\n";
	const	FIELD_DELIMITER		= '|';
	const	FIELD_ENCAPSULATOR	= '';
	const	ESCAPE_CHARACTER	= '';
	
	const	RECORD_TYPE_TRANSACTION	= 'TRANSCATION';
	const	RECORD_TYPE_TRAILER		= 'TRAILER';

	public function getRecords()
	{
		$this->_oFileImporter->setDataFile($this->_oFileImport->getWrappedLocation());
		
		$aRecords	= array();
		while (($sRecord = $this->_oFileImporter->fetch()) !== false)
		{
			$aRecords[]	= $sRecord;
		}
		return $aRecords;
	}
	
	public function processRecord($sRecord)
	{
		switch (self::calculateRecordType($sRecord))
		{
			case self::RECORD_TYPE_TRANSACTION:
				return $this->_processTransaction($sRecord);
				break;
			default:
				// Unknown or unhandled Record Type
				return null;
				break;
		}
	}
	
	protected function _processTransaction($sRecord)
	{
		//Log::getLog()->log($sRecord);

		// Process the Record
		//--------------------------------------------------------------------//
		$oRecord			= $this->_oFileImporter->getRecordType(self::RECORD_TYPE_TRANSACTION)->newRecord($sRecord);
		//Log::getLog()->log(print_r($oRecord, true));
		
		// Create a new Payment_Response Record
		//--------------------------------------------------------------------//
		$oPaymentResponse	= new Payment_Response();
		
		// Paid Date
		$oPaymentResponse->paid_date	= date('Y-m-d', strtotime($oRecord->TransactionDate));
		
		// Amount
		$oPaymentResponse->amount		= round($oRecord->AmountCents / 100, 2);
		
		// Account
		$sReference	= trim($oRecord->Reference);
		if (!!preg_match('/^(?P<account_id>\d+)(R)(?P<payment_request_id>\d+)$/i', $sLodgementReference, $aLodgementReferenceMatches))
		{
			// Payment Request
			$oPaymentResponse->account_id			= (int)$aLodgementReferenceMatches['account_id'];
			$oPaymentResponse->payment_request_id	= (int)$aLodgementReferenceMatches['payment_request_id'];
			break;
		}
 		elseif (strlen($sReference) == 10)
 		{
 			// Legacy Direct Debit
			$oPaymentResponse->account_id	= (int)$sReference;
 		}
 		else
 		{
 			// IVR
			$oPaymentResponse->account_id	= (int)substr($sReference, 6);
 		}
 		
 		// Payment Type
 		switch ((int)$oRecord->TransactionType)
 		{
 			// Handled Types
			case self::TRANSACTION_TYPE_ONLINE_PAYMENT:
 			case self::TRANSACTION_TYPE_IVR_PAYMENT:
		 		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_CREDIT_CARD;
 				break;
 			
 			case self::TRANSACTION_TYPE_BATCH_PAYMENT:
 				// Only so far as I understand
		 		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
 				break;

			// Ignored Types
			// As Unhandled Types come in and get identified as irrelevant, add them here
			case TRANSACTION_TYPE_DIRECT_ENTRY_DEBIT:
				return null;
				break;

			// Unhandled Types
 			default:
				throw new Exception_Assertion(
					"Unhandled SecurePay Transaction Type '{$oRecord->TransactionType}'",
					array(
						'sRecord'	=> $sRecord,
						'oRecord'	=> $oRecord->toArray()
					),
					"Payment Processing: Unhandled SecurePay Transaction Type '{$oRecord->TransactionType}'"
				);
 				break;
 		}
 		
 		// Transaction Data
		$aTransactionData	= array();
		$iTransactionType	= (int)$oRecord->TransactionType;
 		switch ($iTransactionType)
 		{
 			case self::TRANSACTION_TYPE_ONLINE_PAYMENT:
 			case self::TRANSACTION_TYPE_IVR_PAYMENT:
 			case self::TRANSACTION_TYPE_BATCH_PAYMENT:
		 		$aTransactionData[]	= Payment_Transaction_Data::factory(Payment_Transaction_Data::CREDIT_CARD_NUMBER, $oRecord->AbbreviatedCreditCardNumber);
 				break;
 		}
 		
 		// Transaction Reference
 		$oPaymentResponse->transaction_reference	= $sReference;
 		
 		// Payment Response Type
		$iResponseCode	= (int)$oRecord->ResponseCode;
		if (array_key_exists($iResponseCode, self::$_aResponseCodes['aApproved']))
		{
			// Confirmation/Settlement
			$oPaymentResponse->payment_response_type_id	= PAYMENT_RESPONSE_TYPE_CONFIRMATION;
		}
		elseif (array_key_exists($iResponseCode, self::$_aResponseCodes['aDeclined']))
		{
			throw new Exception_Assertion(
				"Reversal Encountered in SecurePay File",
				array(
					'sRecord'	=> $sRecord,
					'oRecord'	=> $oRecord->toArray()
				),
				"Payment Processing: SecurePay Reversal Encountered"
			);
			// Rejection/Dishonour (payment_reversal_type_id is handled elsewhere)
			$oPaymentResponse->payment_response_type_id		= PAYMENT_RESPONSE_TYPE_REJECTION;
			$oPaymentResponse->payment_reversal_reason_id	= Payment_Reversal_Reason::getForSystemName('DISHONOUR_REVERSAL');
		}
		else
		{
			Flex::assert(false, "Unhandled SecurePay Response Code: '{$iResponseCode}'", print_r(array('oRecord'=>$oRecord, 'oPaymentResponse'=>$oPaymentResponse), true), "Unhandled SecurePay Response Code: '{$iResponseCode}'");
		}
		
		// Return an Array of Records added/modified
		//--------------------------------------------------------------------//
		return array(
			'oPaymentResponse'	=> $oPaymentResponse,
			'aTransactionData'	=> $aTransactionData
		);
	}
	
	public static function calculateRecordType($sLine)
	{
		return (!is_numeric(substr($sLine, 0, 1)) || !trim($sLine)) ? self::RECORD_TYPE_TRAILER : self::RECORD_TYPE_TRANSACTION;
	}
	
	protected function _configureFileImporter()
	{
		// File Importer
		$this->_oFileImporter	= new File_Importer_CSV();
		
		$this->_oFileImporter->setNewLine(self::NEW_LINE_DELIMITER);

		// Record Types
		$oRecordTypeTransaction	= $this->_oFileImporter->createRecordType(self::RECORD_TYPE_TRANSACTION, 'File_Importer_CSV_RecordType')
			->setDelimiter(self::FIELD_DELIMITER)
			->setQuote(self::FIELD_ENCAPSULATOR)
			->setEscape(self::ESCAPE_CHARACTER);

		// Fields
		$oRecordTypeTransaction->createField('Reference', 'File_Importer_CSV_Field')
			->setColumn(0);
		$oRecordTypeTransaction->createField('TransactionDate', 'File_Importer_CSV_Field')
			->setColumn(1);
		$oRecordTypeTransaction->createField('TransactionTime', 'File_Importer_CSV_Field')
			->setColumn(2);
		$oRecordTypeTransaction->createField('TransactionType', 'File_Importer_CSV_Field')
			->setColumn(3);
		$oRecordTypeTransaction->createField('ReturnsTransactionSource', 'File_Importer_CSV_Field')
			->setColumn(4);
		$oRecordTypeTransaction->createField('AmountCents', 'File_Importer_CSV_Field')
			->setColumn(5);
		$oRecordTypeTransaction->createField('BankTransactionId', 'File_Importer_CSV_Field')
			->setColumn(6);
		$oRecordTypeTransaction->createField('ResponseCode', 'File_Importer_CSV_Field')
			->setColumn(7);
		$oRecordTypeTransaction->createField('AbbreviatedCreditCardNumber', 'File_Importer_CSV_Field')
			->setColumn(8);
		$oRecordTypeTransaction->createField('SettlementDate', 'File_Importer_CSV_Field')
			->setColumn(9);
	}

	// Transaction Types
 	const	TRANSACTION_TYPE_ONLINE_PAYMENT				= 0;
 	const	TRANSACTION_TYPE_MOBILE_PAYMENT				= 1;
 	const	TRANSACTION_TYPE_BATCH_PAYMENT				= 2;
 	const	TRANSACTION_TYPE_PERIODIC_PAYMENT			= 3;
 	const	TRANSACTION_TYPE_REFUND_RETURN				= 4;
 	const	TRANSACTION_TYPE_ERROR_REVERSAL				= 5;	// Void
 	const	TRANSACTION_TYPE_CLIENT_REVERSAL			= 6;	// Void
 	const	TRANSACTION_TYPE_PREAUTHORISE				= 10;
 	const	TRANSACTION_TYPE_PREAUTH_COMPLETE			= 11;	// Advice
 	const	TRANSACTION_TYPE_RECURRING_PAYMENT			= 14;
 	const	TRANSACTION_TYPE_DIRECT_ENTRY_DEBIT			= 15;
 	const	TRANSACTION_TYPE_DIRECT_ENTRY_CREDIT		= 17;
 	const	TRANSACTION_TYPE_CARD_PRESENT_PAYMENT		= 19;
 	const	TRANSACTION_TYPE_IVR_PAYMENT				= 20;

	// Transaction Sources
	const	TRANSACTION_SOURCE_UNKNOWN						= 0;
	const	TRANSACTION_SOURCE_SECURELINK					= 1;
	const	TRANSACTION_SOURCE_MERCHANT_LOGIN				= 2;
	const	TRANSACTION_SOURCE_SATM							= 3;
	const	TRANSACTION_SOURCE_SECUREBILL_PORTAL			= 4;
	const	TRANSACTION_SOURCE_SECUREBILL_LINK				= 5;
	// 6: Reserved
	const	TRANSACTION_SOURCE_SECUREPOS					= 7;
	const	TRANSACTION_SOURCE_SECUREJAVA					= 8;
	const	TRANSACTION_SOURCE_CALL_CENTRE_PAYMENT_SWITCH	= 9;
	const	TRANSACTION_SOURCE_BATCH_PERIODIC_SERVER		= 10;
	const	TRANSACTION_SOURCE_IVR_1						= 11;
	const	TRANSACTION_SOURCE_IVR_2						= 12;
	const	TRANSACTION_SOURCE_SECUREMOBILE					= 13;
	const	TRANSACTION_SOURCE_RECONCILIATION_ENGINE		= 14;
	// 15: Reserved
	const	TRANSACTION_SOURCE_HELPDESK_LOGIN				= 16;
	const	TRANSACTION_SOURCE_ESEC_CLIENT					= 18;
	const	TRANSACTION_SOURCE_PERIODIC_BATCH_SERVER		= 19;	// Batch / Periodic Server
	// 20: Reserved
	// 21: Reserved
	// 22: Reserved
	const	TRANSACTION_SOURCE_SECUREXML					= 23;
	// 90: Reserved

	// Response Codes
	static protected	$_aResponseCodes	= array(
		'aApproved'	=> array(
			0	=> 'Approved',
			8	=> 'Honour with ID ',
			11	=> 'Approved VIP (not used)',
			16	=> 'Approved, Update Track 3 (not used)',
			77	=> 'Approved (ANZ only)'
		),
		'aDeclined'	=> array(
			1	=> 'Refer to Card Issuer',
			2	=> 'Refer to Issuer’s Special Conditions',
			3	=> 'Invalid Merchant',
			4	=> 'Pick Up Card',
			5	=> 'Do Not Honour',
			6	=> 'Error',
			7	=> 'Pick Up Card, Special Conditions',
			9	=> 'Request in Progress',
			10	=> 'Partial Amount Approved',
			12	=> 'Invalid Transaction',
			13	=> 'Invalid Amount',
			14	=> 'Invalid Card Number',
			15	=> 'No Such Issuer',
			17	=> 'Customer Cancellation',
			18	=> 'Customer Dispute',
			19	=> 'Re-enter Transaction',
			20	=> 'Invalid Response',
			21	=> 'No Action Taken',
			22	=> 'Suspected Malfunction',
			23	=> 'Unacceptable Transaction Fee',
			24	=> 'File Update not Supported by Receiver ',
			25	=> 'Unable to Locate Record on File',
			26	=> 'Duplicate File Update Record',
			27	=> 'File Update Field Edit Error',
			28	=> 'File Update File Locked Out',
			29	=> 'File Update not Successful',
			30	=> 'Format Error',
			31	=> 'Bank not Supported by Switch',
			32	=> 'Completed Partially',
			33	=> 'Expired Card—Pick Up',
			34	=> 'Suspected Fraud—Pick Up',
			35	=> 'Contact Acquirer—Pick Up',
			36	=> 'Restricted Card—Pick Up',
			37	=> 'Call Acquirer Security—Pick Up',
			38	=> 'Allowable PIN Tries Exceeded',
			39	=> 'No CREDIT Account',
			40	=> 'Requested Function not Supported',
			41	=> 'Lost Card—Pick Up ',
			42	=> 'No Universal Amount ',
			43	=> 'Stolen Card—Pick Up ',
			44	=> 'No Investment Account ',
			51	=> 'Insufficient Funds ',
			52	=> 'No Cheque Account ',
			53	=> 'No Savings Account ',
			54	=> 'Expired Card ',
			55	=> 'Incorrect PIN ',
			56	=> 'No Card Record ',
			57	=> 'Trans. not Permitted to Cardholder ',
			58	=> 'Transaction not Permitted to Terminal ',
			59	=> 'Suspected Fraud ',
			60	=> 'Card Acceptor Contact Acquirer ',
			61	=> 'Exceeds Withdrawal Amount Limits ',
			62	=> 'Restricted Card ',
			63	=> 'Security Violation ',
			64	=> 'Original Amount Incorrect ',
			65	=> 'Exceeds Withdrawal Frequency Limit ',
			66	=> 'Card Acceptor Call Acquirer Security ',
			67	=> 'Hard Capture—Pick Up Card at ATM',
			68	=> 'Response Received Too Late ',
			75	=> 'Allowable PIN Tries Exceeded ',
			86	=> 'ATM Malfunction ',
			87	=> 'No Envelope Inserted ',
			88	=> 'Unable to Dispense ',
			89	=> 'Administration Error ',
			90	=> 'Cut-off in Progress ',
			91	=> 'Issuer or Switch is Inoperative ',
			92	=> 'Financial Institution not Found ',
			93	=> 'Trans Cannot be Completed ',
			94	=> 'Duplicate Transmission ',
			95	=> 'Reconcile Error ',
			96	=> 'System Malfunction ',
			97	=> 'Reconciliation Totals Reset ',
			98	=> 'MAC Error ',
			99	=> 'Reserved for National Use '
		)
	);


	
	/***************************************************************************
	 * COMMON METHODS FOR ALL Resource_Type_Base CHILDREN
	 **************************************************************************/

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClass, self::RESOURCE_TYPE);
	}
	
	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array());
	}
}