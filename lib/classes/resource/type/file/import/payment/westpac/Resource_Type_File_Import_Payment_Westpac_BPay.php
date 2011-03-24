<?php
/**
 * Resource_Type_File_Import_Payment_Westpac_BPay
 *
 * @class	Resource_Type_File_Import_Payment_Westpac_BPay
 */
class Resource_Type_File_Import_Payment_Westpac_BPay extends Resource_Type_File_Import_Payment
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC;
	
	const	NEW_LINE_DELIMITER	= "\n";
	const	FIELD_DELIMITER		= ',';
	const	FIELD_ENCAPSULATOR	= '"';
	const	ESCAPE_CHARACTER	= '';
	
	const	RECORD_TYPE_HEADER		= 'HEADER';
	const	RECORD_TYPE_TRANSACTION	= 'TRANSCATION';
	
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
		// Process the Record
		//--------------------------------------------------------------------//
		$oRecord			= $this->_oFileImporter->getRecordType(self::RECORD_TYPE_TRANSACTION)->newRecord($sRecord);
		
		// Create a new Payment_Response Record
		//--------------------------------------------------------------------//
		$oPaymentResponse	= new Payment_Response();
		
		// Paid Date
		$oPaymentResponse->paid_date	= substr($oRecord->Date, 4, 4).'-'.substr($oRecord->Date, 2, 2).'-'.substr($oRecord->Date, 0, 2);
		
		// Amount
		$oPaymentResponse->amount		= round((float)$oRecord->Amount, 2);
		
		// Account
		$sClientReference				= trim($oRecord->ClientReference);
		$iClientReferenceCheckDigit		= (int)substr($sClientReference, -1);
 		$oPaymentResponse->account_id	= (int)substr($sClientReference, 0, -1);
		$iCalculatedAccountCheckDigit	= (int)MakeLuhn($oPaymentResponse->account_id);
		if ($iCalculatedAccountCheckDigit !== $iClientReferenceCheckDigit) {
			throw new Exception("Client Reference Check Digit '{$iClientReferenceCheckDigit}' doesn't match calculated value of '{$iCalculatedAccountCheckDigit}' for Account '{$oPaymentResponse->account_id}'");
		}
 		
 		// Payment Type
		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_BPAY;
 		
 		// Transaction Data
 		// No additional transaction data
 		
 		// Transaction Reference
 		$oPaymentResponse->transaction_reference	= $oRecord->ReceiptNumber;
 		
 		// Payment Response Type
 		$oPaymentResponse->payment_response_type_id	= PAYMENT_RESPONSE_TYPE_CONFIRMATION;
		
		// Return an Array of Records added/modified
		//--------------------------------------------------------------------//
		return array(
			'oPaymentResponse'	=> $oPaymentResponse
		);
	}
	
	public static function calculateRecordType($sLine)
	{
		if (stripos($sLine, 'Amount,Client') !== false)
		{
			return self::RECORD_TYPE_HEADER;
		}
		elseif (!trim($sLine))
		{
			return null;
		}
		else
		{
			return self::RECORD_TYPE_TRANSACTION;
		}
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
			->setEscape(self::ESCAPE_CHARACTER);;

		// Fields
		$oRecordTypeTransaction->createField('Amount', 'File_Importer_CSV_Field')
			->setColumn(0);
		$oRecordTypeTransaction->createField('ClientReference', 'File_Importer_CSV_Field')
			->setColumn(1);
		$oRecordTypeTransaction->createField('Date', 'File_Importer_CSV_Field')
			->setColumn(2);
		$oRecordTypeTransaction->createField('FileId', 'File_Importer_CSV_Field')
			->setColumn(3);
		$oRecordTypeTransaction->createField('OriginatingSystem', 'File_Importer_CSV_Field')
			->setColumn(4);
		$oRecordTypeTransaction->createField('ReceiptNumber', 'File_Importer_CSV_Field')
			->setColumn(5);
		$oRecordTypeTransaction->createField('ServiceId', 'File_Importer_CSV_Field')
			->setColumn(6);
		$oRecordTypeTransaction->createField('ServiceName', 'File_Importer_CSV_Field')
			->setColumn(7);
		$oRecordTypeTransaction->createField('TransactionCode', 'File_Importer_CSV_Field')
			->setColumn(8);
	}
	
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