<?php
/**
 * Resource_Type_File_Import_Payment_AustralianDirectEntry
 *
 * @class	Resource_Type_File_Import_Payment_AustralianDirectEntry
 */
class Resource_Type_File_Import_Payment_AustralianDirectEntry extends Resource_Type_File_Import_Payment
{
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC;
	
	const	RECORD_TYPE_HEADER		= 'HEADER';
	const	RECORD_TYPE_TRANSACTION	= 'TRANSCATION';
	
	const	TRANSACTION_CODE_EXTERNALLY_INITIATED_DEBIT					= 13;
	const	TRANSACTION_CODE_EXTERNALLY_INITIATED_CREDIT				= 50;
	const	TRANSACTION_CODE_AUSTRALIAN_GOVERNMENT_SECURITY_INTEREST	= 51;
	const	TRANSACTION_CODE_BASIC_FAMILY_PAYMENT						= 52;
	const	TRANSACTION_CODE_PAY										= 53;
	const	TRANSACTION_CODE_PENSION									= 54;
	const	TRANSACTION_CODE_ALLOTMENT									= 55;
	const	TRANSCATION_CODE_DIVIDEND									= 56;
	const	TRANSACTION_CODE_DEBENTURE									= 57;
	
	const	STATUS_INDICATOR_RELEASED	= 'G';
	const	STATUS_INDICATOR_RECALL		= 'R';
	
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
				$sFileDate	= '20'.substr($sRecord, 78, 2).'-'.substr($sRecord, 76, 2).'-'.substr($sRecord, 74, 2);
			}
			elseif ($sRecordType === self::RECORD_TYPE_TRANSACTION)
			{
				if (!$sFileDate)
				{
					throw new Exception("No File Date Found (used as Payment Date)");
				}
				$sRecord	.= $sFileDate;
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
		$oPaymentResponse->amount		= round(((float)$oRecord->Amount) / 100, 2);
		
		// Account
		$sLodgementReference		= trim($oRecord->LodgementReference);
		$aLodgementReferenceMatches	= array();
		switch (true)
		{
			// Payment Request
			case (!!preg_match('/^(?P<Account.Id>\d+)(R)(?P<payment_request.id>\d+)$/i', $sLodgementReference, $aLodgementReferenceMatches)):
				$oPaymentResponse->account_id			= (int)$aLodgementReferenceMatches['Account.Id'];
				
				// Payment Request
				$oPaymentResponse->payment_request_id	= (int)$aLodgementReferenceMatches['payment_request.id'];
				break;
				
			// Legacy Direct Debit
			case (!!preg_match('/^(?P<Account.Id>\d+)(\_)(?P<month_year>\d+)$/i', $sLodgementReference, $aLodgementReferenceMatches)):
				$oPaymentResponse->account_id			= (int)$aLodgementReferenceMatches['Account.Id'];
				break;
				
			default:
				throw new Exception("Unable to extract Account Id from Lodgement Reference '{$sLodgementReference}'");
				break;
		}
 		
 		// AccountGroup
 		$oPaymentResponse->account_group_id	= Account::getForId($oPaymentResponse->account_id)->AccountGroup;
 		
 		// Payment Type
 		switch ((int)$oRecord->TransactionCode)
 		{
 			case self::TRANSACTION_CODE_EXTERNALLY_INITIATED_DEBIT:
				$oPaymentResponse->payment_type_id	= PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
 				break;
 			
 			default:
 				throw new Exception("Unhandled Transaction Code '{$oRecord->TransactionCode}'");
 				break;
 		}
 		
 		// Transaction Data
		$aTransactionData	= array(
			Payment_Transaction_Data::factory(Payment_Transaction_Data::BANK_ACCOUNT_NUMBER, $oRecord->AccountNumber)
		);
 		
 		// Transaction Reference
 		$oPaymentResponse->transaction_reference	= $sLodgementReference;
 		
 		// Payment Response Type
 		switch ($oRecord->StatusIndicator)
 		{
 			case self::STATUS_INDICATOR_RELEASED:
 				$oPaymentResponse->payment_response_type_id	= PAYMENT_RESPONSE_TYPE_CONFIRMATION;
 				break;
 			case self::STATUS_INDICATOR_RECALL:
 				$oPaymentResponse->payment_response_type_id		= PAYMENT_RESPONSE_TYPE_REJECTION;
				$oPaymentResponse->payment_reversal_type_id		= PAYMENT_REVERSAL_TYPE_DISHONOUR;
				$oPaymentResponse->payment_reversal_reason_id	= Payment_Reversal_Reason::getForSystemName('DISHONOUR_REVERSAL');
 				break;
 			default:
 				throw new Exception("Unhandled Status Indicator '{$oRecord->StatusIndicator}'");
 				break;
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
		switch (substr($sLine, 0, 1))
		{
			case '0':
				return self::RECORD_TYPE_HEADER;
				break;
			case '1':
				return self::RECORD_TYPE_TRANSACTION;
				break;
			default:
				return null;
				break;
		}
	}
	
	protected function _configureFileImporter()
	{
		$this->_oFileImporter	= new File_Importer();
		
		$this->_oFileImporter->registerRecordType(self::RECORD_TYPE_TRANSACTION,
			File_Importer_CSV_RecordType::factory()
				->addField('RecordType', File_Importer_Field::factory()
					->setStartIndex(0)
					->setLength(1)
				)->addField('BSB', File_Importer_Field::factory()
					->setStartIndex(1)
					->setLength(7)
				)->addField('AccountNumber', File_Importer_Field::factory()
					->setStartIndex(8)
					->setLength(9)
				)->addField('Indicator', File_Importer_Field::factory()
					->setStartIndex(17)
					->setLength(1)
				)->addField('TransactionCode', File_Importer_Field::factory()
					->setStartIndex(18)
					->setLength(2)
				)->addField('Amount', File_Importer_Field::factory()
					->setStartIndex(20)
					->setLength(10)
				)->addField('AccountName', File_Importer_Field::factory()
					->setStartIndex(30)
					->setLength(32)
				)->addField('LodgementReference', File_Importer_Field::factory()
					->setStartIndex(62)
					->setLength(18)
				)->addField('TraceBSB', File_Importer_Field::factory()
					->setStartIndex(80)
					->setLength(7)
				)->addField('TraceAccount', File_Importer_Field::factory()
					->setStartIndex(87)
					->setLength(9)
				)->addField('RemitterName', File_Importer_Field::factory()
					->setStartIndex(96)
					->setLength(16)
				)->addField('WithholdingTax', File_Importer_Field::factory()
					->setStartIndex(112)
					->setLength(8)
				)->addField('StatusIndicator', File_Importer_Field::factory()
					->setStartIndex(120)
					->setLength(1)
				)->addField('EffectiveDate', File_Importer_Field::factory()
					->setStartIndex(121)
					->setLength(10)
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