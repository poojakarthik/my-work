<?php
/**
 * Resource_Type_File_Export_OCA_Referral_DunnAndBradstreet
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_OCA_Referral_DunnAndBradstreet
 */
class Resource_Type_File_Export_OCA_Referral_DunnAndBradstreet extends Resource_Type_File_Export_OCA_Referral
{
	const	RESOURCE_TYPE			= RESOURCE_TYPE_FILE_EXPORT_DUNN_AND_BRADSTREET_REFERRAL_FILE;
	
	const	RECORD_TYPE_HEADER		= 'HEADER';
	const	RECORD_TYPE_TRANSACTION	= 'TRANSACTION';
	
	const	NEW_LINE_DELIMITER		= "\n";
	const	FIELD_DELIMITER			= ',';
	const	FIELD_ENCAPSULATOR		= '';
	const	ESCAPE_CHARACTER		= '\\';
	
	protected	$_oFileExporter;
	protected	$_iTimestamp;
	protected	$_iRecordCount;
	
	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);
		
		$this->_iTimestamp		= time();
		$this->_iRecordCount	= 0;
		$this->_oFileExporter	= new File_Exporter_CSV();
		$this->_configureFileExporter();		
		$this->_addHeaderRecord();
	}
	
	public function addRecord($oAccountOCAReferral)
	{
		$oRecord = $this->_oFileExporter->getRecordType(self::RECORD_TYPE_TRANSACTION)->newRecord();
		
		$oAccount	= Account::getForId($oAccountOCAReferral->account_id);
		$oContact	= Contact::getForId($oAccount->PrimaryContact);
		
		$oQuery = new Query();
		$mEarliestInvoiceResult = $oQuery->Execute("SELECT	MIN(CreatedOn) as date_of_debt
													FROM	Invoice
													WHERE	Account = {$oAccountOCAReferral->account_id}");
		if ($mEarliestInvoiceResult === false)
		{
			throw new Exception("Failed to get the created date of the earliest invoice for account {$oAccountOCAReferral->account_id}. ".$oQuery->Error());
		}
		$aEarliestInvoiceRow = $mEarliestInvoiceResult->fetch_assoc();
		
		// Populate record
		$oRecord->AccountNumber	= $oAccountOCAReferral->account_id;
		$oRecord->BusinessName 	= $oAccount->BusinessName;
		$oRecord->CustomerGroup	= Customer_Group::getForId($oAccount->CustomerGroup)->external_name;
		$oRecord->ABN 			= $oAccount->ABN;
		$oRecord->ContactName 	= $oContact->getName();
		$oRecord->Address 		= "{$oAccount->Address1} {$oAccount->Address2}";
		$oRecord->Suburb 		= $oAccount->Suburb;
		$oRecord->State 		= $oAccount->State;
		$oRecord->Postcode 		= $oAccount->Postcode;
		$oRecord->Phone 		= $oContact->Phone;
		$oRecord->Mobile 		= $oContact->Mobile;
		$oRecord->Email 		= $oContact->Email;
		$oRecord->DateofDebt 	= $aEarliestInvoiceRow['date_of_debt'];
		$oRecord->BalanceDue 	= $oAccount->getOverdueBalance();
		
		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter::RECORD_GROUP_BODY);
		
		// Increment record count
		$this->_iRecordCount++;
		
		return;
	}
	
	public function render()
	{
		// Filename
		$sFilename			= $this->getConfig()->FileNamePrefix.'_'.date('YmdHis').'.csv';
		$this->_sFilePath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__).$sFilename;
		
		// Render and write to disk
		$this->_oFileExporter->renderToFile($this->_sFilePath);
		
		return $this;
	}
	
	public function deliver()
	{
		$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		return $this;
	}
	
	protected function _addHeaderRecord()
	{
		$oRecord = $this->_oFileExporter->getRecordType(self::RECORD_TYPE_HEADER)->newRecord();
		
		// NOTE: All following fields have default values 
		
		// Add to the file
		$this->_oFileExporter->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_HEADER);
		
		return;
	}
	
	protected function _configureFileExporter()
	{
		$this->_iTimestamp	= time();
		
		// Header Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_HEADER,
			File_Exporter_RecordType::factory()
				->addField('AccountNumber',
					File_Exporter_Field::factory()
						->setDefaultValue('Account Number')
				)->addField('BusinessName',
					File_Exporter_Field::factory()
						->setDefaultValue('Business Name')
				)->addField('CustomerGroup',
					File_Exporter_Field::factory()
						->setDefaultValue('Customer Group')
				)->addField('ABN',
					File_Exporter_Field::factory()
						->setDefaultValue('ABN')
				)->addField('ContactName',
					File_Exporter_Field::factory()
						->setDefaultValue('Contact Name')
				)->addField('Address',
					File_Exporter_Field::factory()
						->setDefaultValue('Address')
				)->addField('Suburb',
					File_Exporter_Field::factory()
						->setDefaultValue('Suburb')
				)->addField('State',
					File_Exporter_Field::factory()
						->setDefaultValue('State')
				)->addField('Postcode',
					File_Exporter_Field::factory()
						->setDefaultValue('Postcode')
				)->addField('Phone',
					File_Exporter_Field::factory()
						->setDefaultValue('Phone')
				)->addField('Mobile',
					File_Exporter_Field::factory()
						->setDefaultValue('Mobile')
				)->addField('Email',
					File_Exporter_Field::factory()
						->setDefaultValue('Email')
				)->addField('DateofDebt',
					File_Exporter_Field::factory()
						->setDefaultValue('Date of Debt')
				)->addField('BalanceDue',
					File_Exporter_Field::factory()
						->setDefaultValue('Balance Due')
				)
		);
		
		// Detail Record
		$this->_oFileExporter->registerRecordType(self::RECORD_TYPE_TRANSACTION,
			File_Exporter_RecordType::factory()
				->addField('AccountNumber',
					File_Exporter_Field::factory()
						->setDefaultValue('Account Number')
						//->setValidationRegex('/\d+/')
				)->addField('BusinessName',
					File_Exporter_Field::factory()
						->setDefaultValue('Business Name')
				)->addField('CustomerGroup',
					File_Exporter_Field::factory()
						->setDefaultValue('Customer Group')
				)->addField('ABN',
					File_Exporter_Field::factory()
						->setDefaultValue('ABN')
						//->setValidationRegex('/\d+/')
				)->addField('ContactName',
					File_Exporter_Field::factory()
						->setDefaultValue('Contact Name')
				)->addField('Address',
					File_Exporter_Field::factory()
						->setDefaultValue('Address')
				)->addField('Suburb',
					File_Exporter_Field::factory()
						->setDefaultValue('Suburb')
				)->addField('State',
					File_Exporter_Field::factory()
						->setDefaultValue('State')
				)->addField('Postcode',
					File_Exporter_Field::factory()
						->setDefaultValue('Postcode')
						//->setValidationRegex('/\d+/')
				)->addField('Phone',
					File_Exporter_Field::factory()
						->setDefaultValue('Phone')
						//->setValidationRegex('/\d+/')
				)->addField('Mobile',
					File_Exporter_Field::factory()
						->setDefaultValue('Mobile')
						//->setValidationRegex('/\d+/')
				)->addField('Email',
					File_Exporter_Field::factory()
						->setDefaultValue('Email')
				)->addField('DateofDebt',
					File_Exporter_Field::factory()
						->setDefaultValue('Date of Debt')
				)->addField('BalanceDue',
					File_Exporter_Field::factory()
						->setDefaultValue('Balance Due')
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
		return 	array_merge(
					parent::defineCarrierModuleConfig(), 
					array(
						'FileNamePrefix'		=> array('Description' => 'Prefix for the referral files name'),
						//'SupplierUserNumber'	=> array('Description' => '6-Digit User Idenitification Number allocated by the Australian Payments Clearing Association (APCA)', 'Type' => DATA_TYPE_INTEGER),
						//'FileDescription'		=> array('Description' => 'File Description (eg. \'DDBANK\'), limited to 12-characters', 'Value' => 'DDBANK'),
					)
				);
	}
}