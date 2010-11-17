<?php
/**
 * Resource_Type_File_Import_Provisioning_RetailDecisions_Approvals
 *
 * @class	Resource_Type_File_Import_Provisioning_RetailDecisions_Approvals
 */
class Resource_Type_File_Import_Provisioning_RetailDecisions_Approvals extends Resource_Type_File_Import_Provisioning_RetailDecisions
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_MOTORPASS_PROVISIONING_EXPORT;
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_APPROVALS;
	
	const	NEW_LINE_DELIMITER	= "\n";
	const	FIELD_DELIMITER		= '\t';
	const	FIELD_ENCAPSULATOR	= '';
	const	ESCAPE_CHARACTER	= '';
	
	const	RECORD_TYPE_HEADER		= 'HEADER';
	const	RECORD_TYPE_TRANSACTION	= 'TRANSCATION';
	const	RECORD_TYPE_TRAILER		= 'TRAILER';
	
	public function getRecords()
	{
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
		
		// Find the Record to update
		//--------------------------------------------------------------------//
		$oMotorpassAccount	= Motorpass_Account::getCurrentForAccount((int)$oRecord->ClientReference);
		
		// Update the Database
		//--------------------------------------------------------------------//
		
		// Status
		$mStatus	= self::getMotorpassAccountStatusForStatusCode($oRecord->StatusCode);
		Flex::assert($mStatus !== false, "Unhandled Motorpass Advice Status", array('Raw Record'=>$sRecord, 'Processed Record'=>$oRecord->toArray()), "Unhandled Motorpass Advice Status '{$sCode}'");
		
		if (is_int($mStatus))
		{
			$oMotorpassAccount->motorpass_status_id	= $mStatus;
			
			if ($oMotorpassAccount->motorpass_status_id === MOTORPASS_ACCOUNT_STATUS_APPROVED)
			{
				// File Import
				$oMotorpassAccount->file_import_id	= $this->getFileImport()->Id;
				$oMotorpassAccount->account_number	= (int)$oRecord->AccountNumber;
			}
		}
		else
		{
			// Recognised, but not handled
			return null;
		}
		
		$oMotorpassAccount->save();
		
		// FIXME: What should be returned?  Record updated?  What if multiple records updated?
		return $oMotorpassAccount;
	}
	
	public function __destruct()
	{
		// TODO: Send off alert emails
	}
	
	public static function calculateRecordType($sLine)
	{
		switch (substr($sLine, 0, 2))
		{
			case '00':
				return self::RECORD_TYPE_HEADER;
				break;
			case '99':
				return self::RECORD_TYPE_TRAILER;
				break;
			default:
				return self::RECORD_TYPE_TRANSACTION;
				break;
		}
	}
	
	protected static function getMotorpassAccountStatusForStatusCode($sCode)
	{
		$sCode	= strtoupper(trim($sCode));
		switch ($sCode)
		{
			case 'OPENED':
				return MOTORPASS_ACCOUNT_STATUS_APPROVED;
				break;
			
			case 'SUSPENDED':
			case 'CLOSED':
			case 'REMOVED':
				return $sCode;
				break;
			
			default:
				return false;
				break;
		}
	}
	
	protected function _configureFileImporter()
	{
		$this->_oFileImporter	= new File_Importer_CSV();
		
		$this->_oFileImporter->setDataFile($this->_oFileImport->Location)
							->setNewLine(self::NEW_LINE_DELIMITER)
							->setDelimiter(self::FIELD_DELIMITER)
							->setQuote(self::FIELD_ENCAPSULATOR)
							->setEscape(self::ESCAPE_CHARACTER);
		
		$this->_oFileImporter->registerRecordType(self::RECORD_TYPE_TRANSACTION,
			File_Importer_CSV_RecordType::factory()
				->addField('ClientReference', File_Importer_CSV_Field::factory()
					->setColumn(0)
				)->addField('AccountNumber', File_Importer_CSV_Field::factory()
					->setColumn(1)
				)->addField('StatusCode', File_Importer_CSV_Field::factory()
					->setColumn(2)
				)->addField('StatusDate', File_Importer_CSV_Field::factory()
					->setColumn(3)
				)->addField('PersonalTitle', File_Importer_CSV_Field::factory()
					->setColumn(4)
				)->addField('PersonalFirstName', File_Importer_CSV_Field::factory()
					->setColumn(5)
				)->addField('PersonalLastName', File_Importer_CSV_Field::factory()
					->setColumn(6)
				)->addField('StreetAddress1', File_Importer_CSV_Field::factory()
					->setColumn(7)
				)->addField('StreetAddress2', File_Importer_CSV_Field::factory()
					->setColumn(8)
				)->addField('StreetSuburb', File_Importer_CSV_Field::factory()
					->setColumn(9)
				)->addField('StreetState', File_Importer_CSV_Field::factory()
					->setColumn(10)
				)->addField('StreetPostcode', File_Importer_CSV_Field::factory()
					->setColumn(11)
				)->addField('PostalAddress1', File_Importer_CSV_Field::factory()
					->setColumn(12)
				)->addField('PostalAddress2', File_Importer_CSV_Field::factory()
					->setColumn(13)
				)->addField('PostalSuburb', File_Importer_CSV_Field::factory()
					->setColumn(14)
				)->addField('PostalState', File_Importer_CSV_Field::factory()
					->setColumn(15)
				)->addField('PostalPostcode', File_Importer_CSV_Field::factory()
					->setColumn(16)
				)->addField('PhoneNumber', File_Importer_CSV_Field::factory()
					->setColumn(17)
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