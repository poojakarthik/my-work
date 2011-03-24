<?php
/**
 * Resource_Type_File_Export_Provisioning_RetailDecisions_Applications
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_Provisioning_RetailDecisions_Applications
 */
class Resource_Type_File_Export_Provisioning_RetailDecisions_Applications extends Resource_Type_File_Export_Provisioning
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_MOTORPASS_PROVISIONING_EXPORT;
	const	RESOURCE_TYPE		= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_RETAILDECISIONS_APPLICATIONS;
	
	const	RECORD_TYPE_DETAIL	= 'detail';
	const	NUMBER_OF_CARDS		= 1;
	
	const	NEW_LINE_DELIMITER	= "\n";
	const	FIELD_DELIMITER		= ',';
	const	FIELD_ENCAPSULATOR	= '';
	const	ESCAPE_CHARACTER	= '\\';
	
	protected	$_oFileExport;
	protected	$_oFileExporterCSV;
	protected	$_iTimestamp;
	
	public function __construct($mCarrierModule)
	{
		parent::__construct($mCarrierModule);
		
		$this->_iTimestamp	= time();
		
		$this->_oFileExporterCSV	= new File_Exporter_CSV();
		$this->_configureFileExporter();
	}
	
	public function addRecord($mRebillMotorpass)
	{
		$oRecord	= $this->_oFileExporterCSV->getRecordType(self::RECORD_TYPE_DETAIL)->newRecord();
		
		$oRebillMotorpass	= Rebill_Motorpass::getForId(ORM::extractId($mRebillMotorpass));
		$oMotorpassAccount	= Motorpass_Account::getForId($oRebillMotorpass->motorpass_account_id);
		$oAccount			= Account::getForId(Rebill::getForId($oRebillMotorpass->rebill_id)->account_id);
		$oStreetAddress		= Motorpass_Address::getForId($oMotorpassAccount->street_address_id);
		$oPostalAddress		= ($oMotorpassAccount->postal_address_id) ? Motorpass_Address::getForId($oMotorpassAccount->postal_address_id) : null;
		$oMotorpassContact	= Motorpass_Contact::getForId($oMotorpassAccount->motorpass_contact_id);
		$oMotorpassCard		= Motorpass_Card::getForId($oMotorpassAccount->motorpass_card_id);
		
		// Account Details
		$oRecord->PromotionCode				= Motorpass_Promotion_Code::getForId($oMotorpassAccount->motorpass_promotion_code_id)->name;
		$oRecord->ClientNumber				= $oAccount->Id;
		$oRecord->BusinessName				= $oMotorpassAccount->account_name;
		$oRecord->ABN						= $oMotorpassAccount->abn;
		$oRecord->BusinessCommencementDate	= date('my', strtotime($oMotorpassAccount->business_commencement_date));
		$oRecord->BusinessStructure			= Motorpass_Business_Structure::getForId($oMotorpassAccount->motorpass_business_structure_id)->code_numeric;
		$oRecord->EmailAddress				= $oMotorpassAccount->email_address;
		$oRecord->EmailedInvoice			= ($oMotorpassAccount->email_invoice) ? 'Y' : 'N';
		
		// Street Address
		$oRecord->StreetAddressLine1	= $oStreetAddress->line_1;
		$oRecord->StreetAddressLine2	= $oStreetAddress->line_2;
		$oRecord->StreetSuburb			= $oStreetAddress->suburb;
		$oRecord->StreetState			= State::getForId($oStreetAddress->state_id)->code;
		$oRecord->StreetPostcode		= $oStreetAddress->postcode;
		
		// Postal Address
		if ($oPostalAddress)
		{
			$oRecord->PostalAddressLine1	= $oPostalAddress->line_1;
			$oRecord->PostalAddressLine2	= $oPostalAddress->line_2;
			$oRecord->PostalSuburb			= $oPostalAddress->suburb;
			$oRecord->PostalState			= State::getForId($oPostalAddress->state_id)->code;
			$oRecord->PostalPostcode		= $oPostalAddress->postcode;
		}
		
		// Main Contact
		$oRecord->MainContactTitle			= ($oMotorpassContact->contact_title_id) ? Contact_Title::getForId($oMotorpassContact->contact_title_id)->name : '';
		$oRecord->MainContactFirstName		= $oMotorpassContact->first_name;
		$oRecord->MainContactLastName		= $oMotorpassContact->last_name;
		$oRecord->MainContactDateOfBirth	= date('d/m/Y', strtotime($oMotorpassContact->dob));
		$oRecord->MainContactDriverLicence	= $oMotorpassContact->drivers_licence;
		$oRecord->MainContactPosition		= $oMotorpassContact->position;
		$oRecord->MainContactLandlineNumber	= $oMotorpassContact->landline_number;
		$oRecord->ContactName				= "{$oMotorpassContact->first_name} {$oMotorpassContact->last_name}";
		$oRecord->ContactLandline			= $oMotorpassContact->landline_number;
		
		// Trade References
		$aTradeReferences		= Motorpass_Trade_Reference::getForAccountId($oMotorpassAccount->id, true);
		if (count($aTradeReferences) < 2)
		{
			throw new Exception("Motorpass Account has less than 2 active Trade References");
		}
		
		$oTradeReference2	= array_pop($aTradeReferences);	// Trade Reference 2 should be the Trade Reference with the highest Id
		$oTradeReference1	= array_pop($aTradeReferences);	// Trade Reference 1 should be the Trade Reference with the second highest Id
		
		$oRecord->TradeReference1Name		= $oTradeReference1->company_name;
		$oRecord->TradeReference1Landline	= $oTradeReference1->phone_number;
		
		$oRecord->TradeReference2Name		= $oTradeReference2->company_name;
		$oRecord->TradeReference2Landline	= $oTradeReference2->phone_number;
		
		// Cardholder
		$oRecord->Cardholder1Title			= ($oMotorpassCard->holder_contact_title_id) ? Contact_Title::getForId($oMotorpassCard->holder_contact_title_id)->name : '';
		$oRecord->Cardholder1FirstName		= $oMotorpassCard->holder_first_name;
		$oRecord->Cardholder1Surname		= $oMotorpassCard->holder_last_name;
		$oRecord->Cardholder1Registration	= $oMotorpassCard->vehicle_rego;
		$oRecord->Cardholder1VehicleModel	= $oMotorpassCard->vehicle_model;
		$oRecord->Cardholder1Restriction	= Motorpass_Card_Type::getForId($oMotorpassCard->motorpass_card_type_id)->name;
		
		// Add to the file
		$this->_oFileExporterCSV->addRecord($oRecord, File_Exporter_CSV::RECORD_GROUP_BODY);
		
		// TODO: Do we need to return anything special?
		return;
	}
	
	public function render()
	{
		// Filename
		$sFilename	= $this->getConfig()->ReDCustomerName
					.'_APPS_'
					.date('Ymd_His', $this->_iTimestamp)
					.'.csv';
		$this->_sFilePath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__).$sFilename;
		
		// Render and write to disk
		$this->_oFileExporterCSV->renderToFile($this->_sFilePath);
		
		// TODO: Do we need to return anything special?
		return $this;
	}
	
	public function deliver()
	{
		$this->_oFileDeliver->connect()->deliver($this->_sFilePath)->disconnect();
		return $this;
	}
	
	protected function _configureFileExporter()
	{
		$this->_iTimestamp	= time();
		
		// Detail Record
		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_DETAIL,
			File_Exporter_RecordType::factory()
				->addField('CurrentDate',
					File_Exporter_Field::factory()->setDefaultValue(date('d/m/Y', $this->_iTimestamp))
				)->addField('AreaManager',
					File_Exporter_Field::factory()->setDefaultValue($this->getConfig()->AreaManager)
				)->addField('SaleChannel',
					File_Exporter_Field::factory()->setDefaultValue($this->getConfig()->SaleChannel)
				)->addField('CardProduct',
					File_Exporter_Field::factory()->setDefaultValue($this->getConfig()->CardProduct)
				)->addField('LeadSource',
					File_Exporter_Field::factory()->setMaximumLength(30, true)->
													setDefaultValue($this->getConfig()->LeadSource)
				)->addField('PromotionCode',
					File_Exporter_Field::factory()->setMinimumLength(4)->
													setMaximumLength(4)
				)->addField('ExpiryDate',
					File_Exporter_Field::factory()->setDefaultValue(date('my', strtotime("+{$this->getConfig()->CardValidityMonths} month", $this->_iTimestamp)))
				)->addField('ClientNumber',
					File_Exporter_Field::factory()->setMaximumLength(20)
				)->addField('BusinessName',
					File_Exporter_Field::factory()->setMaximumLength(35, true)
				)->addField('TradingName',
					File_Exporter_Field::factory()->setMaximumLength(26, true)
				)->addField('Trustee',
					File_Exporter_Field::factory()->setMaximumLength(35)
				)->addField('ABN',
					File_Exporter_Field::factory()->setMaximumLength(11)
				)->addField('ACN',
					File_Exporter_Field::factory()->setMaximumLength(9)
				)->addField('BusinessCommencementDate',
					File_Exporter_Field::factory()->setValidationRegex("/^(0[1-9]|1[0-2])(\d{2})$/")
				)->addField('BusinessStructure',
					File_Exporter_Field::factory()->setValidationRegex("/^\d{1,2}$/")->
													setMinimumLength(2)->
													setMaximumLength(2)->
													setPaddingString('0')->
													setPaddingStyle(STR_PAD_LEFT)
				)->addField('NatureOfBusiness',
					File_Exporter_Field::factory()->setMaximumLength(30, true)
				)->addField('StreetAddressLine1',
					File_Exporter_Field::factory()->setMaximumLength(35, true)
				)->addField('StreetAddressLine2',
					File_Exporter_Field::factory()->setMaximumLength(35, true)
				)->addField('StreetSuburb',
					File_Exporter_Field::factory()->setMaximumLength(26, true)
				)->addField('StreetState',
					File_Exporter_Field::factory()->setMaximumLength(3, true)
				)->addField('StreetPostcode',
					File_Exporter_Field::factory()->setValidationRegex("/^\d{4}$/")
				)->addField('PostalAddressLine1',
					File_Exporter_Field::factory()->setMaximumLength(35, true)
				)->addField('PostalAddressLine2',
					File_Exporter_Field::factory()->setMaximumLength(35, true)
				)->addField('PostalSuburb',
					File_Exporter_Field::factory()->setMaximumLength(26, true)
				)->addField('PostalState',
					File_Exporter_Field::factory()->setMaximumLength(3, true)
				)->addField('PostalPostcode',
					File_Exporter_Field::factory()->setValidationRegex("/^\d{4}$/")
				)->addField('MainContactTitle',
					File_Exporter_Field::factory()->setMaximumLength(5, true)
				)->addField('MainContactFirstName',
					File_Exporter_Field::factory()->setMaximumLength(20, true)
				)->addField('MainContactLastName',
					File_Exporter_Field::factory()->setMaximumLength(20, true)
				)->addField('MainContactDateOfBirth',
					File_Exporter_Field::factory()->setValidationRegex("/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/")
				)->addField('MainContactDriverLicence',
					File_Exporter_Field::factory()->setMaximumLength(8, true)
				)->addField('MainContactPosition',
					File_Exporter_Field::factory()->setMaximumLength(20, true)
				)->addField('MainContactLandlineNumber',
					File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
				)->addField('MainContactFaxNumber',
					File_Exporter_Field::factory()->setValidationRegex("/^(|0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
				)->addField('MainContactMobileNumber',
					File_Exporter_Field::factory()->setValidationRegex("/^(|04\d{8})$/")
				)->addField('EmailAddress',
					File_Exporter_Field::factory()
				)->addField('EmailedInvoice',
					File_Exporter_Field::factory()->setValidationRegex("/^(Y|N)$/i")
				)->addField('CreditLimit',
					File_Exporter_Field::factory()->setValidationRegex("/^\d{1,11}$/")->
													setDefaultValue($this->getConfig()->DefaultCreditLimit)
				)->addField('ContactName',
					File_Exporter_Field::factory()->setMaximumLength(30, true)
				)->addField('ContactLandline',
					File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
				)->addField('TradeReference1Name',
					File_Exporter_Field::factory()->setMaximumLength(40, true)
				)->addField('TradeReference1Landline',
					File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
				)->addField('TradeReference2Name',
					File_Exporter_Field::factory()->setMaximumLength(40, true)
				)->addField('TradeReference2Landline',
					File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
				)->addField('NumberOfCards',
					File_Exporter_Field::factory()->setValidationRegex("/^\d$/")->
													setDefaultValue(self::NUMBER_OF_CARDS)
				)->addField('Cardholder1Title',
					File_Exporter_Field::factory()->setMaximumLength(15, true)
				)->addField('Cardholder1FirstName',
					File_Exporter_Field::factory()->setMaximumLength(20, true)
				)->addField('Cardholder1Surname',
					File_Exporter_Field::factory()->setMaximumLength(30, true)
				)->addField('Cardholder1Registration',
					File_Exporter_Field::factory()->setMaximumLength(11, true)
				)->addField('Cardholder1VehicleModel',
					File_Exporter_Field::factory()->setMaximumLength(15, true)
				)->addField('Cardholder1Restriction',
					File_Exporter_Field::factory()->setMaximumLength(30)
			)
		);
	}
	
	/***************************************************************************
	 * COMMON METHODS FOR ALL Resource_Type_Base CHILDREN
	 **************************************************************************/
	
	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClass=__CLASS__) {
		if ($iCustomerGroup !== null) {
			throw new Exception(GetConstantName(self::CARRIER_MODULE_TYPE, 'carrier_module_type')." Carrier Modules cannot be Customer Group specific");
		}
		parent::createCarrierModule($iCarrier, $sClass, self::RESOURCE_TYPE, self::RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_RETAILDECISIONS_APPLICATIONS);
	}
	
	static public function defineCarrierModuleConfig()
	{
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'ReDCustomerName'		=>	array('Description'=>'ReD Customer Name'),
			'AreaManager'			=>	array('Description'=>'Area Manager/Sales Person'),
			'SaleChannel'			=>	array('Description'=>'Sales Channel'),
			'CardProduct'			=>	array('Description'=>'Card Product'),
			'LeadSource'			=>	array('Description'=>'Lead Source'),
			'DefaultCreditLimit'	=>	array('Description'=>'Default Credit Limit (whole dollars)','Type'=>DATA_TYPE_INTEGER)
		));
	}
}