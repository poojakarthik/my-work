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
	protected	$_sLocalPath;
	
	public function __construct()
	{
		parent::__construct();
		
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
		$oRecord->BusinessName				= $oAccount->BusinessName;
		$oRecord->ABN						= $oAccount->ABN;
		$oRecord->BusinessCommencementDate	= date('my', strtotime($oMotorpassAccount->business_commencement_date));
		$oRecord->BusinessStructure			= Motorpass_Business_Structure::getForId($oMotorpassAccount->motorpass_business_structure_id)->code_numeric;
		$oRecord->EmailAddress				= $oMotorpassAccount->email_address;
		$oRecord->EmailInvoice				= ($oMotorpassAccount->email_invoice) ? 'Y' : 'N';
		
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
		$aTradeReferences		= Motorpass_Trade_Reference::getForAccountId($iAccountId, true);
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
					.'csv';
		$this->_sLocalPath	= self::getExportPath($this->getCarrierModule()->Carrier, __CLASS__).$sFilename;
		
		// Render and write to disk
		$this->_oFileExporterCSV->renderToFile($this->_sLocalPath);
		
		// TODO: Do we need to return anything special?
		return $this;
	}
	
	public function deliver()
	{
		$this->_oFileDeliver->connect()->deliver($this->_sLocalPath)->disconnect();
		return $this;
	}
	
	protected function _configureFileExporter()
	{
		$this->_iTimestamp	= time();
		
		// Detail Record
		$this->_oFileExporterCSV->registerRecordType(self::RECORD_TYPE_DETAIL,
			File_Exporter_RecordType::factory()->addField('CurrentDate',
				File_Exporter_Field::factory()->setDefaultValue(date('d/m/Y', $this->_iTimestamp))
			),
			File_Exporter_RecordType::factory()->addField('AreaManager',
				File_Exporter_Field::factory()->setDefaultValue($this->getConfig()->AreaManager)
			),
			File_Exporter_RecordType::factory()->addField('SaleChannel',
				File_Exporter_Field::factory()->setDefaultValue($this->getConfig()->SaleChannel)
			),
			File_Exporter_RecordType::factory()->addField('CardProduct',
				File_Exporter_Field::factory()->setDefaultValue($this->getConfig()->CardProduct)
			),
			File_Exporter_RecordType::factory()->addField('LeadSource',
				File_Exporter_Field::factory()->setMaximumLength(30, true)->
												setDefaultValue($this->getConfig()->LeadSource)
			),
			File_Exporter_RecordType::factory()->addField('PromotionCode',
				File_Exporter_Field::factory()->setMinimumLength(4)->
												setMaximumLength(4)
			),
			File_Exporter_RecordType::factory()->addField('ExpiryDate',
				File_Exporter_Field::factory()->setDefaultValue(date('my', strtotime("+{$this->getConfig()->CardValidityMonths} month", $this->_iTimestamp)))
			),
			File_Exporter_RecordType::factory()->addField('ClientNumber',
				File_Exporter_Field::factory()->setMaximumLength(20)
			),
			File_Exporter_RecordType::factory()->addField('BusinessName',
				File_Exporter_Field::factory()->setMaximumLength(35, true)
			),
			File_Exporter_RecordType::factory()->addField('TradingName',
				File_Exporter_Field::factory()->setMaximumLength(26, true)
			),
			File_Exporter_RecordType::factory()->addField('Trustee',
				File_Exporter_Field::factory()->setMaximumLength(35)
			),
			File_Exporter_RecordType::factory()->addField('ABN',
				File_Exporter_Field::factory()->setMaximumLength(11)
			),
			File_Exporter_RecordType::factory()->addField('ACN',
				File_Exporter_Field::factory()->setMaximumLength(9)
			),
			File_Exporter_RecordType::factory()->addField('BusinessCommencementDate',
				File_Exporter_Field::factory()->setValidationRegex("/^(0[1-9]|1[0-2])(\d{2})$/")
			),
			File_Exporter_RecordType::factory()->addField('BusinessStructure',
				File_Exporter_Field::factory()->setValidationRegex("/^\d{2}$/")->
												setMinimumLength(2)->
												setMaximumLength(2)->
												setPaddingString('0')->
												setPaddingStyle(STR_PAD_LEFT)
			),
			File_Exporter_RecordType::factory()->addField('NatureOfBusiness',
				File_Exporter_Field::factory()->setMaximumLength(30, true)
			),
			File_Exporter_RecordType::factory()->addField('StreetAddressLine1',
				File_Exporter_Field::factory()->setMaximumLength(35, true)
			),
			File_Exporter_RecordType::factory()->addField('StreetAddressLine2',
				File_Exporter_Field::factory()->setMaximumLength(35, true)
			),
			File_Exporter_RecordType::factory()->addField('StreetSuburb',
				File_Exporter_Field::factory()->setMaximumLength(26, true)
			),
			File_Exporter_RecordType::factory()->addField('StreetState',
				File_Exporter_Field::factory()->setMaximumLength(3, true)
			),
			File_Exporter_RecordType::factory()->addField('StreetPostcode',
				File_Exporter_Field::factory()->setValidationRegex("/^\d{4}$/")
			),
			File_Exporter_RecordType::factory()->addField('PostalAddressLine1',
				File_Exporter_Field::factory()->setMaximumLength(35, true)
			),
			File_Exporter_RecordType::factory()->addField('PostalAddressLine2',
				File_Exporter_Field::factory()->setMaximumLength(35, true)
			),
			File_Exporter_RecordType::factory()->addField('PostalAddressSuburb',
				File_Exporter_Field::factory()->setMaximumLength(26, true)
			),
			File_Exporter_RecordType::factory()->addField('PostalState',
				File_Exporter_Field::factory()->setMaximumLength(3, true)
			),
			File_Exporter_RecordType::factory()->addField('PostalPostcode',
				File_Exporter_Field::factory()->setValidationRegex("/^\d{4}$/")
			),
			File_Exporter_RecordType::factory()->addField('MainContactTitle',
				File_Exporter_Field::factory()->setMaximumLength(5, true)
			),
			File_Exporter_RecordType::factory()->addField('MainContactFirstName',
				File_Exporter_Field::factory()->setMaximumLength(20, true)
			),
			File_Exporter_RecordType::factory()->addField('MainContactLastName',
				File_Exporter_Field::factory()->setMaximumLength(20, true)
			),
			File_Exporter_RecordType::factory()->addField('MainContactDateOfBirth',
				File_Exporter_Field::factory()->setValidationRegex("/^([0-2][1-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/")
			),
			File_Exporter_RecordType::factory()->addField('MainContactDriverLicence',
				File_Exporter_Field::factory()->setMaximumLength(8, true)
			),
			File_Exporter_RecordType::factory()->addField('MainContactPosition',
				File_Exporter_Field::factory()->setMaximumLength(20, true)
			),
			File_Exporter_RecordType::factory()->addField('MainContactLandlineNumber',
				File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
			),
			File_Exporter_RecordType::factory()->addField('MainContactFaxNumber',
				File_Exporter_Field::factory()->setValidationRegex("/^(|0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
			),
			File_Exporter_RecordType::factory()->addField('MainContactMobileNumber',
				File_Exporter_Field::factory()->setValidationRegex("/^(|04\d{8})$/")
			),
			File_Exporter_RecordType::factory()->addField('EmailAddress',
				File_Exporter_Field::factory()
			),
			File_Exporter_RecordType::factory()->addField('EmailedInvoice',
				File_Exporter_Field::factory()->setValidationRegex("/^(Y|N)$/i")
			),
			File_Exporter_RecordType::factory()->addField('CreditLimit',
				File_Exporter_Field::factory()->setValidationRegex("/^\d{1,11}$/")->
												setDefaultValue($this->getConfig()->DefaultCreditLimit)
			),
			File_Exporter_RecordType::factory()->addField('ContactName',
				File_Exporter_Field::factory()->setMaximumLength(30, true)
			),
			File_Exporter_RecordType::factory()->addField('ContactLandline',
				File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
			),
			File_Exporter_RecordType::factory()->addField('TradeReference1Name',
				File_Exporter_Field::factory()->setMaximumLength(40, true)
			),
			File_Exporter_RecordType::factory()->addField('TradeReference1Landline',
				File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
			),
			File_Exporter_RecordType::factory()->addField('TradeReference2Name',
				File_Exporter_Field::factory()->setMaximumLength(40, true)
			),
			File_Exporter_RecordType::factory()->addField('TradeReference2Landline',
				File_Exporter_Field::factory()->setValidationRegex("/^(0[2378]\d{8}|13\d{6}|1[38]00\d{6})$/")
			),
			File_Exporter_RecordType::factory()->addField('NumberOfCards',
				File_Exporter_Field::factory()->setValidationRegex("/^\d$/")->
												setDefaultValue(self::NUMBER_OF_CARDS)
			),
			File_Exporter_RecordType::factory()->addField('Cardholder1Title',
				File_Exporter_Field::factory()->setMaximumLength(15, true)
			),
			File_Exporter_RecordType::factory()->addField('Cardholder1FirstName',
				File_Exporter_Field::factory()->setMaximumLength(20, true)
			),
			File_Exporter_RecordType::factory()->addField('Cardholder1Surname',
				File_Exporter_Field::factory()->setMaximumLength(30, true)
			),
			File_Exporter_RecordType::factory()->addField('Cardholder1Registration',
				File_Exporter_Field::factory()->setMaximumLength(11, true)
			),
			File_Exporter_RecordType::factory()->addField('Cardholder1VehicleModel',
				File_Exporter_Field::factory()->setMaximumLength(15, true)
			),
			File_Exporter_RecordType::factory()->addField('Cardholder1Restriction',
				File_Exporter_Field::factory()->setMaximumLength(30)
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
		return array_merge(parent::defineCarrierModuleConfig(), array(
			'AreaManager'			=>	array(),
			'SaleChannel'			=>	array(),
			'CardProduct'			=>	array(),
			'LeadSource'			=>	array(),
			'DefaultCreditLimit'	=>	array('Type'=>DATA_TYPE_INTEGER)
		));
	}
}