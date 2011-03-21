<?php
/**
 * Resource_Type_File_Import_Payment_Motorpass
 *
 * @class	Resource_Type_File_Import_Payment_Motorpass
 */
class Resource_Type_File_Import_Payment_Motorpass extends Resource_Type_File_Import_Payment
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT;
	
	const	NEW_LINE_DELIMITER	= "\n";
	const	FIELD_DELIMITER		= ',';
	const	FIELD_ENCAPSULATOR	= '"';
	const	ESCAPE_CHARACTER	= '';
	
	const	RECORD_TYPE_HEADER		= 'HEADER';
	const	RECORD_TYPE_TRANSACTION	= 'TRANSCATION';
	const	RECORD_TYPE_FOOTER		= 'FOOTER';
	
	public function getRecords()
	{
		$this->_oFileImporter->setDataFile($this->_oFileImport->Location);
		
		$aRecords	= array();
		$sFileDate	= null;
		while (($sRecord = $this->_oFileImporter->fetch()) !== false)
		{
			$sRecordType	= self::calculateRecordType($sRecord);
			if ($sRecordType === self::RECORD_TYPE_HEADER)
			{
				// Extract the File Date from the Header
				$aRegexMatches	= array();
				preg_match("/^(?P<RecordType>00),(?P<Sender>[A-Z]+),(?P<Receiver>[A-Z]+),(?P<Date>\d{2}\/\d{2}\/\d{4}),(?P<Time>\d{2}\:\d{2})/i", $sRecord, $aRegexMatches);
	 			
				$sFileDate	= substr($aRegexMatches['Date'], 6, 4).'-'.substr($aRegexMatches['Date'], 3, 2).'-'.substr($aRegexMatches['Date'], 0, 2);
			}
			elseif ($sRecordType === self::RECORD_TYPE_TRANSACTION)
			{
				if (!$sFileDate)
				{
					throw new Exception("No File Date Found (used as Payment Date)");
				}
				$sRecord	.= self::FIELD_DELIMITER.self::FIELD_ENCAPSULATOR.$sFileDate.self::FIELD_ENCAPSULATOR;
			}
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
		$oPaymentResponse->paid_date	= $oRecord->EffectiveDate;
		
		// Amount
		$fBillingAmount	= (float)$oRecord->BillingAmount;
		$fFee			= (float)$oRecord->Fee;
		$fAmount		= round($fBillingAmount + $fFee, 2);
		if ($fAmount <= 0)
		{
			throw new Exception("Billing Amount + Fee must be greater than $0.00 to pass through");
		}
		$oPaymentResponse->amount		= $fAmount;
		
		// Account
		$sReference						= trim($oRecord->ClientReferenceNo);
 		$oPaymentResponse->account_id	= (int)$sReference;
 		
 		// AccountGroup
 		$oPaymentResponse->account_group_id	= Account::getForId($oPaymentResponse->account_id)->AccountGroup;
 		
 		// Payment Type
		$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_REBILL_PAYOUT;
 		
 		// Transaction Data
 		// No Transaction Data
 		
 		// Transaction Reference
 		$oPaymentResponse->transaction_reference	= $sReference;
 		
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
		switch (substr($sLine, 0, 2))
		{
			case '00':
				return self::RECORD_TYPE_HEADER;
				break;
			case '99':
				return self::RECORD_TYPE_FOOTER;
				break;
			case '':
				return null;
				break;
			default:
				return self::RECORD_TYPE_TRANSACTION;
				break;
		}
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
				->addField('ClientReferenceNo', File_Importer_CSV_Field::factory()
					->setColumn(0)
				)->addField('AccountNumber', File_Importer_CSV_Field::factory()
					->setColumn(1)
				)->addField('BillingAmount', File_Importer_CSV_Field::factory()
					->setColumn(2)
				)->addField('Fee', File_Importer_CSV_Field::factory()
					->setColumn(3)
				)->addField('EffectiveDate', File_Importer_CSV_Field::factory()
					->setColumn(4)
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