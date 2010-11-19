<?php
/**
 * Resource_Type_File_Export_Payment_AustralianDirectEntry
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_Payment_AustralianDirectEntry
 */
class Resource_Type_File_Export_Payment_AustralianDirectEntry extends Resource_Type_File_Export_Payment
{
	const	RESOURCE_TYPE			= RESOURCE_TYPE_FILE_EXPORT_DIRECT_DEBIT_AUSTRALIAN_DIRECT_ENTRY_FILE;
	
	const	RECORD_TYPE_HEADER		= 'HEADER';
	const	RECORD_TYPE_TRANSACTION	= 'TRANSACTION';
	const	RECORD_TYPE_FOOTER		= 'FOOTER';
	
	const	NEW_LINE_DELIMITER		= "\n";
	const	FIELD_DELIMITER			= ',';
	const	FIELD_ENCAPSULATOR		= '';
	const	ESCAPE_CHARACTER		= '\\';
	
	protected	$_oFileExport;
	protected	$_oFileExporter;
	protected	$_iTimestamp;
	protected	$_iRecordCount;
	protected	$_fDebitTotalCents;
	
	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);
		
		$this->_iTimestamp			= time();
		$this->_fDebitTotalCents	= 0;
		$this->_iRecordCount		= 0;
		$this->_oFileExporter		= new File_Exporter();
		$this->_configureFileExporter();		
		$this->_addHeaderRecord();
	}
	
	public function addRecord($mPaymentRequest)
	{
		$oRecord	= $this->_oFileExporter->getRecordType(self::RECORD_TYPE_TRANSACTION)->newRecord();
		
		$oPaymentRequest	= Payment_Request::getForId(ORM::extractId($mPaymentRequest));
		$oPayment			= Payment::getForId($oPaymentRequest->payment_id);
		$aAccountHistory	= Account_History::getForAccountAndEffectiveDatetime($oPaymentRequest->account_id, $oPaymentRequest->created_datetime);
		$oBankAccount		= DirectDebit::getForId($aAccountHistory['direct_debit_id']);
		
		// Verify that the payment type is correct
		Flex::assert(
			$oPaymentRequest->payment_type_id === PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT, 
			"Non EFT Payment Request sent to Australian Direct Entry Export File", 
			print_r($oPaymentRequest->toStdClass(), true)
		);
		
		// Verify that the payment hasn't been reversed
		Flex::assert(
			$oPayment->Status !== PAYMENT_STATUS_REVERSED,
			"A Payment Request that is tied to a reversed payment was sent to Australian Direct Entry Export File",
			print_r($oPaymentRequest->toStdClass(), true)
		);
		
		// NOTE: The following fields have default values 
		//	- RecordType
		//	- Indicator
		//	- TransactionCode
		//	- TraceBSB
		//	- TraceAccount
		//	- Remitter
		//	- WithholdingTax
		$sBSB						= str_pad((int)$oBankAccount->BSB, 6, '0', STR_PAD_LEFT);
 		$oRecord->BSB				= substr($sBSB, 0, 3).'-'.substr($sBSB, -3);
		$oRecord->AccountNumber		= $oBankAccount->AccountNumber;
		$oRecord->Amount			= ceil($oPaymentRequest->amount * 100);
		$oRecord->AccountName		= strtoupper(substr(preg_replace("/[^\w\ ]+/misU", '', trim($oBankAccount->AccountName)), 0, 32));
		$oRecord->TransactionRef	= $oPayment->TXNReference;
		
		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter::RECORD_GROUP_BODY);
		
		// Add to total debit cents & increment record count
		$this->_fDebitTotalCents	+=	$oRecord->Amount;
		$this->_iRecordCount++;
		
		return;
	}
	
	public function render()
	{
		// Filename
		$sFilename			= $this->getConfig()->FileNamePrefix.'0009'.'.txt';
		$this->_sFilePath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__).$sFilename;
		
		// Add footer record
		$this->_addFooterRecord();
		
		// Render and write to disk
		$this->_oFileExporter->renderToFile($this->_sFilePath);
		
		// TODO: Do we need to return anything special?
		return $this;
	}
	
	public function deliver()
	{
		$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		return $this;
	}
	
	protected function _addHeaderRecord()
	{
		$oRecord	= $this->_oFileExporter->getRecordType(self::RECORD_TYPE_HEADER)->newRecord();
		
		// NOTE: The following fields have default values 
		//	- RecordType
		//	- ReelSequence
		//	- BankAbbreviation
		//	- SupplierUserName
		//	- SupplierUserNumber
		//	- FileDescription
		$oRecord->TransactionDate	= date("dmy");
		
		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter::RECORD_GROUP_BODY);
		
		return;
	}
	
	protected function _addFooterRecord()
	{
		$oRecord	= $this->_oFileExporter->getRecordType(self::RECORD_TYPE_FOOTER)->newRecord();
		
		// NOTE: The following fields have default values 
		//	- RecordType
		//	- BSBFormatFiller
		$oRecord->NetTotalCents		= $this->_fDebitTotalCents;
		$oRecord->DebitTotalCents	= $this->_fDebitTotalCents;
		$oRecord->CreditTotalCents	= 0;
		$oRecord->RecordCount		= $this->_iRecordCount;
		
		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter::RECORD_GROUP_BODY);
		
		return;
	}
	
	protected function _configureFileExporter()
	{
		$this->_iTimestamp	= time();
		
		// Header Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_HEADER,
			File_Exporter_RecordType::factory()
				->addField('RecordType',
					File_Exporter_Field::factory()
						->setDefaultValue('0')
						->setMinimumLength(1)
						->setMaximumLength(1)
						->setValidationRegex('/0/')
				)->addField('Blank',
					File_Exporter_Field::factory()
						->setMinimumLength(17)
						->setMaximumLength(17)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
				)->addField('ReelSequence',
					File_Exporter_Field::factory()
						->setMinimumLength(2)
						->setMaximumLength(2)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setValidationRegex('/\d+/')
						->setDefaultValue('01')
				)->addField('BankAbbreviation',
					File_Exporter_Field::factory()
						->setMinimumLength(3)
						->setMaximumLength(3)
						->setValidationRegex('/\w+/')
						->setDefaultValue($this->getConfig()->BankAbbreviation)
				)->addField('Blank2',
					File_Exporter_Field::factory()
						->setMinimumLength(7)
						->setMaximumLength(7)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
				)->addField('SupplierUserName',
					File_Exporter_Field::factory()
						->setMinimumLength(26)
						->setMaximumLength(26)
						->setPaddingStyle(STR_PAD_RIGHT)
						->setPaddingString(' ')
						->setValidationRegex('/[\w\ ]+/')
						->setDefaultValue($this->getConfig()->SupplierUserName)
				)->addField('SupplierUserNumber',
					File_Exporter_Field::factory()
						->setMinimumLength(6)
						->setMaximumLength(6)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setDefaultValue($this->getConfig()->SupplierUserNumber)
				)->addField('FileDescription',
					File_Exporter_Field::factory()
						->setMinimumLength(12)
						->setMaximumLength(12)
						->setPaddingStyle(STR_PAD_RIGHT)
						->setPaddingString(' ')
						->setValidationRegex('/[\w\ ]+/')
						->setDefaultValue($this->getConfig()->FileDescription)
				)->addField('TransactionDate',
					File_Exporter_Field::factory()
						->setMinimumLength(6)
						->setMaximumLength(6)
						->setValidationRegex('/\d+/')
				)->addField('Blank3',
					File_Exporter_Field::factory()
						->setMinimumLength(40)
						->setMaximumLength(40)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
				)
		);
		
		// Detail Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_TRANSACTION,
			File_Exporter_RecordType::factory()
				->addField('RecordType',
					File_Exporter_Field::factory()
						->setDefaultValue('1')
						->setMinimumLength(1)
						->setMaximumLength(1)
						->setValidationRegex('/1/')
				)->addField('BSB',
					File_Exporter_Field::factory()
						->setMinimumLength(7)
						->setMaximumLength(7)
						->setValidationRegex('/\d{3}-\d{3}/')
				)->addField('AccountNumber',
					File_Exporter_Field::factory()
						->setMinimumLength(9)
						->setMaximumLength(9)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
						->setValidationRegex('/[0-9a-zA-z-\ ]+/')
				)->addField('Indicator',
					File_Exporter_Field::factory()
						->setMinimumLength(1)
						->setMaximumLength(1)
						->setValidationRegex('/[\ NTWXY]/')
						->setDefaultValue(' ')
				)->addField('TransactionCode',
					File_Exporter_Field::factory()
						->setDefaultValue('13')
						->setMaximumLength(2)
						->setMinimumLength(2)
				)->addField('Amount',
					File_Exporter_Field::factory()
						->setMinimumLength(10)
						->setMaximumLength(10)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setValidationRegex('/\d+/')
				)->addField('AccountName',
					File_Exporter_Field::factory()
						->setMinimumLength(32)
						->setMaximumLength(32)
						->setPaddingStyle(STR_PAD_RIGHT)
						->setPaddingString(' ')
						->setValidationRegex('/\w+(,[\w\ ]+)?/')
				)->addField('TransactionRef',
					File_Exporter_Field::factory()
						->setMinimumLength(18)
						->setMaximumLength(18)
						->setPaddingStyle(STR_PAD_RIGHT)
						->setPaddingString(' ')
				)->addField('TraceBSB',
					File_Exporter_Field::factory()
						->setMinimumLength(7)
						->setMaximumLength(7)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
						->setValidationRegex('/\d{3}-\d{3}/')
						->setDefaultValue($this->getConfig()->TraceBSB)
				)->addField('TraceAccount',
					File_Exporter_Field::factory()
						->setMinimumLength(9)
						->setMaximumLength(9)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
						->setValidationRegex('/[0-9a-zA-z-\ ]+/')
						->setDefaultValue($this->getConfig()->TraceAccount)
				)->addField('Remitter',
					File_Exporter_Field::factory()
						->setMinimumLength(16)
						->setMaximumLength(16, true)
						->setPaddingStyle(STR_PAD_RIGHT)
						->setPaddingString(' ')
						->setValidationRegex('/[\w\ ]+/')
						->setDefaultValue($this->getConfig()->SupplierUserName)
				)->addField('WithholdingTax',
					File_Exporter_Field::factory()
						->setMinimumLength(8)
						->setMaximumLength(8)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setValidationRegex('/\d+/')
						->setDefaultValue('0')
				)
		);
		
		// Trailer Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_FOOTER,
			File_Exporter_RecordType::factory()
				->addField('RecordType',
					File_Exporter_Field::factory()
						->setDefaultValue('7')
						->setMinimumLength(1)
						->setMaximumLength(1)
						->setValidationRegex('/7/')
				)->addField('BSBFormatFiller',
					File_Exporter_Field::factory()
						->setDefaultValue('999-999')
						->setMinimumLength(7)
						->setMaximumLength(7)
						->setValidationRegex('/999-999/')
				)->addField('Blank',
					File_Exporter_Field::factory()
						->setMinimumLength(12)
						->setMaximumLength(12)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
				)->addField('NetTotalCents',
					File_Exporter_Field::factory()
						->setMinimumLength(10)
						->setMaximumLength(10)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setValidationRegex('/\d+/')
				)->addField('CreditTotalCents',
					File_Exporter_Field::factory()
						->setMinimumLength(10)
						->setMaximumLength(10)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setValidationRegex('/\d+/')
				)->addField('DebitTotalCents',
					File_Exporter_Field::factory()
						->setMinimumLength(10)
						->setMaximumLength(10)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setValidationRegex('/\d+/')
				)->addField('Blank2',
					File_Exporter_Field::factory()
						->setMinimumLength(24)
						->setMaximumLength(24)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
				)->addField('RecordCount',
					File_Exporter_Field::factory()
						->setMinimumLength(6)
						->setMaximumLength(6)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString('0')
						->setValidationRegex('/\d+/')
				)->addField('Blank3',
					File_Exporter_Field::factory()
						->setMinimumLength(40)
						->setMaximumLength(40)
						->setPaddingStyle(STR_PAD_LEFT)
						->setPaddingString(' ')
				)
		);
	}
	
	public static function getAssociatedPaymentType()
	{
		return PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT;
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
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'FileNamePrefix'		=> array('Description' => '3-Character CustomerGroup Prefix for the FileName (eg. SAE, VOI)'),
			'BankAbbreviation'		=> array('Description' => '3-Character Approved Financial Institution Abbreviation (eg. WBC for Westpac)'),
			'SupplierUserName'		=> array('Description' => 'User Name (as per User Preferred Specification)'),
			'SupplierUserNumber'	=> array('Description' => '6-Digit User Idenitification Number allocated by the Australian Payments Clearing Association (APCA)', 'Type' => DATA_TYPE_INTEGER),
			'FileDescription'		=> array('Description' => 'File Description (eg. \'DDBANK\'), limited to 12-characters', 'Value' => 'DDBANK'),
			'TraceBSB'				=> array('Description' => 'The BSB for the Account number to trace back to on payment rejection (XXX-XXX)'),
			'TraceAccount'			=> array('Description' => 'The Account number to trace back to on payment rejection')
		));
	}
}