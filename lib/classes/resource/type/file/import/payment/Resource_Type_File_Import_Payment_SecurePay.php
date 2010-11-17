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
	
 	const	TRANSACION_TYPE_CREDIT_CARD					= 0;
 	const	TRANSACION_TYPE_BATCH_PAYMENT				= 2;
 	const	TRANSACION_TYPE_DIRECT_DEBIT_BANK_TRANSFER	= 15;
 	const	TRANSACION_TYPE_DIRECT_DEBIT_REJECT			= 16;
 	const	TRANSACION_TYPE_IVR_CREDIT_CARD				= 20;
	
	public function getRecords()
	{
		$this->_oFileImporter->setDataFile($this->_oFileImport->Location);
		
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
		// Process the Record
		//--------------------------------------------------------------------//
		$oRecord			= $this->_oFileImporter->getRecordType(self::RECORD_TYPE_TRANSACTION)->newRecord($sRecord);
		
		// Create a new Payment_Response Record
		//--------------------------------------------------------------------//
		$oPaymentResponse	= new Payment_Response();
		
		// Paid Date
		$oPaymentResponse->paid_date	= date('Y-m-d', strtotime($oRecord->DatePaid));
		
		// Amount
		$oPaymentResponse->amount		= round($oRecord->AmountCents / 100, 2);
		
		// Account
		$sReference	= trim($oRecord->ReferenceNo);
 		if (strlen($sReference) == 10)
 		{
 			$oPaymentResponse->account_id	= (int)$sReference;
 		}
 		else
 		{
 			$oPaymentResponse->account_id	= (int)substr($sReference, 6);
 		}
 		
 		// AccountGroup
 		$oPaymentResponse->account_group_id	= Account::getForId($oPaymentResponse->account_id)->AccountGroup;
 		
 		// Payment Type
 		switch ((int)$oRecord->TransactionType)
 		{
 			case self::TRANSACION_TYPE_CREDIT_CARD:
 			case self::TRANSACION_TYPE_IVR_CREDIT_CARD:
		 		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_CREDIT_CARD;
 				break;
 			
 			case self::TRANSACION_TYPE_BATCH_PAYMENT:
 				// Only so far as I understand
		 		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD;
 				break;
 			
 			case self::TRANSACION_TYPE_DIRECT_DEBIT_BANK_TRANSFER:
		 		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
 				break;
 			
 			case self::TRANSACION_TYPE_DIRECT_DEBIT_REJECT:
 				// Unknown??
		 		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
 				break;
 			
 			default:
 				// TODO
 				break;
 		}
 		
 		// Origin Id
 		switch ((int)$oRecord->TransactionType)
 		{
 			case self::TRANSACION_TYPE_CREDIT_CARD:
 			case self::TRANSACION_TYPE_IVR_CREDIT_CARD:
 			case self::TRANSACION_TYPE_BATCH_PAYMENT:
		 		$oPaymentResponse->origin_id	= $oRecord->CCNo;
 				break;
 			
 			default:
 				// TODO
 				break;
 		}
 		
 		// Transaction Reference
 		$oPaymentResponse->transaction_reference	= $sReference;
 		
 		// Payment Response Type
 		$oPaymentResponse->payment_response_type_id	= PAYMENT_RESPONSE_TYPE_SETTLEMENT;
		
		// Return an Array of Records added/modified
		//--------------------------------------------------------------------//
		return array($oPaymentResponse);
	}
	
	public static function calculateRecordType($sLine)
	{
		return (!is_numeric(substr($sLine, 0, 1)) || !trim($sLine)) ? self::RECORD_TYPE_TRAILER : self::RECORD_TYPE_TRANSACTION;
	}
	
	protected function _configureFileImporter()
	{
		$this->_oFileImporter	= new File_Importer_CSV();
		
		$this->_oFileImporter->setNewLine(self::NEW_LINE_DELIMITER)
							->setDelimiter(self::FIELD_DELIMITER)
							->setQuote(self::FIELD_ENCAPSULATOR)
							->setEscape(self::ESCAPE_CHARACTER);
		
		$this->_oFileImporter->registerRecordType(self::RECORD_TYPE_TRANSACTION,
			File_Importer_CSV_RecordType::factory()
				->addField('ReferenceNo', File_Importer_CSV_Field::factory()
					->setColumn(0)
				)->addField('DatePaid', File_Importer_CSV_Field::factory()
					->setColumn(1)
				)->addField('TimePaid', File_Importer_CSV_Field::factory()
					->setColumn(2)
				)->addField('TransactionType', File_Importer_CSV_Field::factory()
					->setColumn(3)
				)->addField('ReturnsTransactionSource', File_Importer_CSV_Field::factory()
					->setColumn(4)
				)->addField('AmountCents', File_Importer_CSV_Field::factory()
					->setColumn(5)
				)->addField('BankTransactionId', File_Importer_CSV_Field::factory()
					->setColumn(6)
				)->addField('ResponseCode', File_Importer_CSV_Field::factory()
					->setColumn(7)
				)->addField('CCNo', File_Importer_CSV_Field::factory()
					->setColumn(8)
				)->addField('SettlementDate', File_Importer_CSV_Field::factory()
					->setColumn(9)
				)
		);
	}
	
	/***************************************************************************
	 * COMMON METHODS FOR ALL Resource_Type_Base CHILDREN
	 **************************************************************************/
	
	static public function createCarrierModule($iCarrier, $sClass=__CLASS__)
	{
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE);
	}
	
	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array());
	}
}