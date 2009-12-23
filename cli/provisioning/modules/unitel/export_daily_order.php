HIl<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// export_daily_order
//----------------------------------------------------------------------------//
/**
 * export_daily_order
 *
 * Exports Unitel Full Service File Requests
 *
 * Exports Unitel Full Service File Requests
 *
 * @file		export_daily_order.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.03
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ExportUnitelDailyOrder
//----------------------------------------------------------------------------//
/**
 * ExportUnitelDailyOrder
 *
 * Exports Unitel Full Service File Requests
 *
 * Exports Unitel Full Service File Requests
 *
 * @prefix		exp
 *
 * @package		provisioning
 * @class		ExportUnitelDailyOrder
 */
 class ExportUnitelDailyOrder extends ExportBase
 {
 	//------------------------------------------------------------------------//
	// Properties
	//------------------------------------------------------------------------//
	protected	$_arrFileContent;
	protected	$_arrDefine;
	protected	$_arrFilename;
	protected	$_arrHeader;
	protected	$_arrFooter;
	protected	$_ptrFile;
	
	public $intBaseCarrier			= CARRIER_UNITEL;
	public $intBaseFileType			= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_UNITEL_DAILY_ORDER;
	public $_strDeliveryType		= 'FTP';
	
	public $_intFrequencyType		= FREQUENCY_DAY;
	public $_intFrequency			= 1;
	public $_intEarliestDelivery	= 54000;
	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor
	 *
	 * Constructor
	 * 
	 * @param	integer	$intCarrier				The Carrier using this Module
	 * 
	 * @return	ExportBase
	 *
	 * @method
	 */
 	function __construct($intCarrier)
 	{
 		// Parent Constructor
 		parent::__construct($intCarrier);
 		
 		// Module Description
 		$this->strDescription		= "Daily Order";
		
 		// Get Fields which are going to be modified
 		$this->intCarrierReference	= &$this->GetConfigField('RecordSequence');
 		$this->intFileSequence		= &$this->GetConfigField('FileSequence');
 		$this->strLastSent			= &$this->GetConfigField('LastSent');
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Server']			['Default']		= 'ftp.rslcom.com.au';
 		$this->_arrModuleConfig['Server']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Server']			['Description']	= "FTP Server to connect to";
 		
 		$this->_arrModuleConfig['User']				['Default']		= '';
 		$this->_arrModuleConfig['User']				['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['User']				['Description']	= "FTP Username";
 		
 		$this->_arrModuleConfig['Password']			['Default']		= '';
 		$this->_arrModuleConfig['Password']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']			['Description']	= "FTP Password";
 		
 		$this->_arrModuleConfig['Path']				['Default']		= '/ebill_dailyorderfiles/';
 		$this->_arrModuleConfig['Path']				['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Path']				['Description']	= "Directory to drop the file in";
 		
 		// Additional
 		$this->_arrModuleConfig['FileSequence']		['Default']		= 0;
 		$this->_arrModuleConfig['FileSequence']		['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['FileSequence']		['Description']	= "File Sequence Number";
 		$this->_arrModuleConfig['FileSequence']		['AutoUpdate']	= TRUE;
 		
 		$this->_arrModuleConfig['RecordSequence']	['Default']		= 0;
 		$this->_arrModuleConfig['RecordSequence']	['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['RecordSequence']	['Description']	= "Record Sequence Number";
 		$this->_arrModuleConfig['RecordSequence']	['AutoUpdate']	= TRUE;
 		
 		$this->_arrModuleConfig['CarrierCode']		['Default']		= 'rsl';
 		$this->_arrModuleConfig['CarrierCode']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['CarrierCode']		['Description']	= "Receiving Carrier Code";
 		
 		$this->_arrModuleConfig['System']			['Default']		= 'w';
 		$this->_arrModuleConfig['System']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['System']			['Description']	= "Receiving Processing System";
 		
 		$this->_arrModuleConfig['CSPCode']			['Default']		= '';
 		$this->_arrModuleConfig['CSPCode']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['CSPCode']			['Description']	= "YBS Customer's CSP Code";
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'CSV';
 		$this->_strDelimiter	= "";
 		$this->_strNewLine		= "\r\n";
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['CSP']			['Start']		= 0;
		$arrDefine['CSP']			['Length']		= 3;
		$arrDefine['CSP']			['Config']		= 'CSPCode';
		
		$arrDefine['RSL']			['Start']		= 3;
		$arrDefine['RSL']			['Length']		= 3;
		$arrDefine['RSL']			['Config']		= 'CarrierCode';
		
		$arrDefine['System']		['Start']		= 6;
		$arrDefine['System']		['Length']		= 1;
		$arrDefine['System']		['Config']		= 'System';
		
		$arrDefine['Sequence']		['Start']		= 7;
		$arrDefine['Sequence']		['Length']		= 4;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['Date']			['Start']		= 11;
		$arrDefine['Date']			['Length']		= 8;
		$arrDefine['Date']			['Type']		= 'Date::YYYYMMDD';
		
		$arrDefine['Extension']		['Start']		= 19;
		$arrDefine['Extension']		['Length']		= 4;
		$arrDefine['Extension']		['Value']		= ".txt";
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '01';
		
		$arrDefine['CSP']			['Start']		= 2;
		$arrDefine['CSP']			['Length']		= 3;
		$arrDefine['CSP']			['Config']		= 'CSPCode';
		
		$arrDefine['RSL']			['Start']		= 5;
		$arrDefine['RSL']			['Length']		= 3;
		$arrDefine['RSL']			['Config']		= 'CarrierCode';
		
		$arrDefine['System']		['Start']		= 8;
		$arrDefine['System']		['Length']		= 1;
		$arrDefine['System']		['Config']		= 'System';
		
		$arrDefine['Sequence']		['Start']		= 9;
		$arrDefine['Sequence']		['Length']		= 4;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['Date']			['Start']		= 13;
		$arrDefine['Date']			['Length']		= 8;
		$arrDefine['Date']			['Type']		= 'Date::YYYYMMDD';
		
		$arrDefine['Extension']		['Start']		= 21;
		$arrDefine['Extension']		['Length']		= 4;
		$arrDefine['Extension']		['Value']		= ".txt";
		
		$this->_arrDefine['Header'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// FOOTER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '99';
		
		$arrDefine['RecordCount']	['Start']		= 2;
		$arrDefine['RecordCount']	['Length']		= 7;
		$arrDefine['RecordCount']	['Type']		= 'Integer';
		$arrDefine['RecordCount']	['PadChar']		= '0';
		$arrDefine['RecordCount']	['PadType']		= STR_PAD_LEFT;
		
		$this->_arrDefine['Footer'] = $arrDefine;
 		
 		
 		//--------------------------------------------------------------------//
 		// Churn/Full Service
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '12';
		
		$arrDefine['Sequence']		['Start']		= 2;
		$arrDefine['Sequence']		['Length']		= 9;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['FNN']			['Start']		= 11;
		$arrDefine['FNN']			['Length']		= 17;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['Basket']		['Start']		= 28;
		$arrDefine['Basket']		['Length']		= 3;
		$arrDefine['Basket']		['Type']		= 'Integer';
		$arrDefine['Basket']		['PadChar']		= '0';
		$arrDefine['Basket']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['AgreementDate']	['Start']		= 31;
		$arrDefine['AgreementDate']	['Length']		= 8;
		$arrDefine['AgreementDate']	['Type']		= 'Date::YYYYMMDD';
		
		$arrDefine['BillName']		['Start']		= 39;
		$arrDefine['BillName']		['Length']		= 30;
		
		$arrDefine['BillAddress1']	['Start']		= 69;
		$arrDefine['BillAddress1']	['Length']		= 30;
		
		$arrDefine['BillAddress2']	['Start']		= 99;
		$arrDefine['BillAddress2']	['Length']		= 30;
		
		$arrDefine['BillLocality']	['Start']		= 129;
		$arrDefine['BillLocality']	['Length']		= 23;
		
		$arrDefine['BillPostcode']	['Start']		= 152;
		$arrDefine['BillPostcode']	['Length']		= 4;
		$arrDefine['BillPostcode']	['Type']		= 'Integer';
		$arrDefine['BillPostcode']	['PadChar']		= '0';
		$arrDefine['BillPostcode']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['EndUserTitle']	['Start']		= 156;
		$arrDefine['EndUserTitle']	['Length']		= 4;
		
		$arrDefine['FirstName']		['Start']		= 160;
		$arrDefine['FirstName']		['Length']		= 30;
		
		$arrDefine['LastName']		['Start']		= 190;
		$arrDefine['LastName']		['Length']		= 50;
		
		$arrDefine['CompanyName']	['Start']		= 240;
		$arrDefine['CompanyName']	['Length']		= 50;
		
		$arrDefine['DateOfBirth']	['Start']		= 290;
		$arrDefine['DateOfBirth']	['Length']		= 8;
		$arrDefine['DateOfBirth']	['Type']		= 'Date::YYYYMMDD';
		$arrDefine['DateOfBirth']	['Optional']	= '        ';
		
		$arrDefine['Employer']		['Start']		= 298;
		$arrDefine['Employer']		['Length']		= 30;
		
		$arrDefine['Occupation']	['Start']		= 328;
		$arrDefine['Occupation']	['Length']		= 30;
		
		$arrDefine['ABN']			['Start']		= 358;
		$arrDefine['ABN']			['Length']		= 11;
		
		$arrDefine['TradingName']	['Start']		= 369;
		$arrDefine['TradingName']	['Length']		= 50;
		
		$arrDefine['AddressType']	['Start']		= 419;
		$arrDefine['AddressType']	['Length']		= 3;
		
		$arrDefine['AdTypeNumber']	['Start']		= 422;
		$arrDefine['AdTypeNumber']	['Length']		= 5;
		$arrDefine['AdTypeNumber']	['Type']		= 'Integer';
		$arrDefine['AdTypeNumber']	['PadChar']		= '0';
		$arrDefine['AdTypeNumber']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['AdTypeSuffix']	['Start']		= 427;
		$arrDefine['AdTypeSuffix']	['Length']		= 2;
		
		$arrDefine['StNumberStart']	['Start']		= 429;
		$arrDefine['StNumberStart']	['Length']		= 5;
		$arrDefine['StNumberStart']	['Type']		= 'Integer';
		$arrDefine['StNumberStart']	['PadChar']		= '0';
		$arrDefine['StNumberStart']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['StNumberEnd']	['Start']		= 434;
		$arrDefine['StNumberEnd']	['Length']		= 5;
		$arrDefine['StNumberEnd']	['Type']		= 'Integer';
		$arrDefine['StNumberEnd']	['PadChar']		= '0';
		$arrDefine['StNumberEnd']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['StNumSuffix']	['Start']		= 439;
		$arrDefine['StNumSuffix']	['Length']		= 1;
		
		$arrDefine['StreetName']	['Start']		= 440;
		$arrDefine['StreetName']	['Length']		= 30;
		
		$arrDefine['StreetType']	['Start']		= 470;
		$arrDefine['StreetType']	['Length']		= 4;
		
		$arrDefine['StTypeSuffix']	['Start']		= 474;
		$arrDefine['StTypeSuffix']	['Length']		= 2;
		
		$arrDefine['PropertyName']	['Start']		= 476;
		$arrDefine['PropertyName']	['Length']		= 30;
		
		$arrDefine['Locality']		['Start']		= 506;
		$arrDefine['Locality']		['Length']		= 30;
		
		$arrDefine['State']			['Start']		= 536;
		$arrDefine['State']			['Length']		= 3;
		
		$arrDefine['Postcode']		['Start']		= 539;
		$arrDefine['Postcode']		['Length']		= 4;
		
		$this->_arrDefine[PROVISIONING_TYPE_FULL_SERVICE] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Churn/Full Service Reversal
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '52';
		
		$arrDefine['Sequence']		['Start']		= 2;
		$arrDefine['Sequence']		['Length']		= 9;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['FNN']			['Start']		= 11;
		$arrDefine['FNN']			['Length']		= 17;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['Basket']		['Start']		= 28;
		$arrDefine['Basket']		['Length']		= 3;
		$arrDefine['Basket']		['Type']		= 'Integer';
		$arrDefine['Basket']		['PadChar']		= '0';
		$arrDefine['Basket']		['PadType']		= STR_PAD_LEFT;
		
		$this->_arrDefine[PROVISIONING_TYPE_FULL_SERVICE_REVERSE] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Virtual Preselection
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '13';
		
		$arrDefine['Sequence']		['Start']		= 2;
		$arrDefine['Sequence']		['Length']		= 9;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['FNN']			['Start']		= 11;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['Date']			['Start']		= 21;
		$arrDefine['Date']			['Length']		= 8;
		$arrDefine['Date']			['Type']		= 'Date::YYYYMMDD';
		$arrDefine['Date']			['PadChar']		= '0';
		$arrDefine['Date']			['PadType']		= STR_PAD_LEFT;
		
		$this->_arrDefine[PROVISIONING_TYPE_VIRTUAL_PRESELECTION] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Virtual Preselection Reversal
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '13';
		
		$arrDefine['Sequence']		['Start']		= 2;
		$arrDefine['Sequence']		['Length']		= 9;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['FNN']			['Start']		= 11;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$this->_arrDefine[PROVISIONING_TYPE_VIRTUAL_PRESELECTION_REVERSE] = $arrDefine;
 	}
 	
 	//------------------------------------------------------------------------//
	// Output
	//------------------------------------------------------------------------//
	/**
	 * Output()
	 *
	 * Exports a ProvisioningRequest Record to a format accepted by the Carrier
	 *
	 * Exports a ProvisioningRequest Record to a format accepted by the Carrier
	 * 
	 * @param	array	$arrRequest		Request to Export
	 * 
	 * @return	array					Modified Request
	 *
	 * @method
	 */
 	function Output($arrRequest)
 	{ 		
 		//--------------------------------------------------------------------//
 		// RENDER
 		//--------------------------------------------------------------------//
 		$arrRendered				= Array();
 		
 		// Service Address
 		if ($arrRequest['Type'] == PROVISIONING_TYPE_FULL_SERVICE || $arrRequest['Type'] == PROVISIONING_TYPE_PRESELECTION)
 		{
			$arrServiceAddress	= $this->_CleanServiceAddress($arrRequest['Service']);
			
			if (is_string($arrServiceAddress))
			{
				// Service Address Problems
		 		$arrRequest['Status']		= REQUEST_STATUS_REJECTED_FLEX;
		 		$arrRequest['Description']	= $arrServiceAddress;
				return $arrRequest;
			}
			
			// Common
			$arrRendered['AgreementDate']		= date("Ymd", strtotime($arrRequest['AuthorisationDate']));
			$arrRendered['BillName']			= $arrServiceAddress['BillName'];
			$arrRendered['BillAddress1']		= $arrServiceAddress['BillAddress1'];
			$arrRendered['BillAddress2']		= $arrServiceAddress['BillAddress2'];
			$arrRendered['BillLocality']		= $arrServiceAddress['BillLocality'];
			$arrRendered['BillPostcode']		= $arrServiceAddress['BillPostcode'];
			
			// Residential
			$arrRendered['EndUserTitle']		= $arrServiceAddress['EndUserTitle'];
			$arrRendered['FirstName']			= $arrServiceAddress['EndUserGivenName'];
			$arrRendered['LastName']			= $arrServiceAddress['EndUserFamilyName'];
			$arrRendered['DateOfBirth']			= $arrServiceAddress['DateOfBirth'];
			$arrRendered['Employer']			= $arrServiceAddress['Employer'];
			$arrRendered['Occupation']			= $arrServiceAddress['Occupation'];
			
			// Business
			$arrRendered['CompanyName']			= $arrServiceAddress['EndUserCompanyName'];
			$arrRendered['ABN']					= $arrServiceAddress['ABN'];
			$arrRendered['TradingName']			= $arrServiceAddress['TradingName'];
			
			// Service Location Details
			$arrRendered['AddressType']			= $arrServiceAddress['ServiceAddressType'];
			$arrRendered['AdTypeNumber']		= $arrServiceAddress['ServiceAddressTypeNumber'];
			$arrRendered['AdTypeSuffix']		= $arrServiceAddress['ServiceAddressTypeSuffix'];
			$arrRendered['StNumberStart']		= $arrServiceAddress['ServiceStreetNumberStart'];
			$arrRendered['StNumberEnd']			= $arrServiceAddress['ServiceStreetNumberEnd'];
			$arrRendered['StNumSuffix']			= $arrServiceAddress['ServiceStreetNumberSuffix'];
			$arrRendered['StreetName']			= $arrServiceAddress['ServiceStreetName'];
			$arrRendered['StreetType']			= $arrServiceAddress['ServiceStreetType'];
			$arrRendered['StTypeSuffix']		= $arrServiceAddress['ServiceStreetTypeSuffix'];
			$arrRendered['PropertyName']		= $arrServiceAddress['ServicePropertyName'];
			$arrRendered['Locality']			= $arrServiceAddress['ServiceLocality'];
			$arrRendered['State']				= $arrServiceAddress['ServiceState'];
			$arrRendered['Postcode']			= $arrServiceAddress['ServicePostcode'];
 		}
 		
		$arrRendered['FNN']			= $arrRequest['FNN'];
 		switch ($arrRequest['Type'])
 		{
 			case PROVISIONING_TYPE_FULL_SERVICE:
 				for ($intBasket = 1; $intBasket <= 5; $intBasket++)
 				{
 					$this->intCarrierReference++;
					$arrRendered['Sequence']		= $this->intCarrierReference;
 					$arrRendered['Basket']			= $intBasket;
			 		$arrRendered['**Type']			= $arrRequest['Type'];
			 		$arrRendered['**Request']		= $arrRequest['Id'];
			 		$arrRendered['**CarrierRef']	= $this->intCarrierReference;
			 		$this->_arrFileContent[]		= $arrRendered;
 				}
 				break;
 				
 			case PROVISIONING_TYPE_FULL_SERVICE_REVERSE:
 				for ($intBasket = 1; $intBasket <= 5; $intBasket++)
 				{
 					$this->intCarrierReference++;
					$arrRendered['Sequence']		= $this->intCarrierReference;
 					$arrRendered['Basket']			= $intBasket;
			 		$arrRendered['**Type']			= $arrRequest['Type'];
			 		$arrRendered['**Request']		= $arrRequest['Id'];
			 		$arrRendered['**CarrierRef']	= $this->intCarrierReference;
			 		$this->_arrFileContent[]		= $arrRendered;
 				}
 				break;
 				
 			case PROVISIONING_TYPE_VIRTUAL_PRESELECTION:
		 		/*// Add Basket 2 Re-Request
				$this->intCarrierReference++;
				$arrRendered['Sequence']		= $this->intCarrierReference;
				$arrRendered['Basket']			= 2;
		 		$arrRendered['**Type']			= PROVISIONING_TYPE_FULL_SERVICE;
		 		$arrRendered['**Request']		= $arrRequest['Id'];
		 		$arrRendered['**CarrierRef']	= $this->intCarrierReference;
		 		$this->_arrFileContent[]		= $arrRendered;*/
 				
 				// Add Virtual Preselection Request
 				$this->intCarrierReference++;
 				$arrRendered['Sequence']		= $this->intCarrierReference;
 				$arrRendered['Date']			= date("Ymd", strtotime($arrRequest['AuthorisationDate']));
		 		$arrRendered['**Type']			= $arrRequest['Type'];
		 		$arrRendered['**Request']		= $arrRequest['Id'];
			 	$arrRendered['**CarrierRef']	= $this->intCarrierReference;
		 		$this->_arrFileContent[]		= $arrRendered;
		 		
 				break;
 				
 			case PROVISIONING_TYPE_VIRTUAL_PRESELECTION_REVERSE:
 				$this->intCarrierReference++;
 				$arrRendered['Sequence']		= $this->intCarrierReference;
		 		$arrRendered['**Type']			= $arrRequest['Type'];
		 		$arrRendered['**Request']		= $arrRequest['Id'];
			 	$arrRendered['**CarrierRef']	= $this->intCarrierReference;
		 		$this->_arrFileContent[]		= $arrRendered;
 				break;
 		}
 		
 		//--------------------------------------------------------------------//
 		// MODIFICATIONS TO REQUEST RECORD
 		//--------------------------------------------------------------------//
 		$arrRequest['CarrierRef']	= $this->intCarrierReference;
 		$arrRequest['Status']		= REQUEST_STATUS_EXPORTING;
 		
 		// Return the modified Request
 		return $arrRequest;
 	}
 	
 	//------------------------------------------------------------------------//
	// Export
	//------------------------------------------------------------------------//
	/**
	 * Export()
	 *
	 * Builds the output file/email for delivery to Carrier
	 *
	 * Builds the output file/email for delivery to Carrier
	 * 
	 * @return	array					'Pass'			: TRUE/FALSE
	 * 									'Description'	: Error message
	 *
	 * @method
	 */
 	function Export()
 	{
 		$this->intFileSequence++;
 		
 		// Generate File Name
 		$this->_arrFilename	= Array();
 		$this->_arrFilename['**Type']		= 'Filename';
 		$this->_arrFilename['**Request']	= 'Filename';
 		$this->_arrFilename['Sequence']		= $this->intFileSequence;
 		$this->_arrFilename['Date']			= date("Ymd");
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']			= 'Header';
 		$this->_arrHeader['**Request']		= 'Header';
 		$this->_arrHeader['Sequence']		= $this->intFileSequence;
 		$this->_arrHeader['Date']			= date("Ymd");
 		
 		// Generate Footer
 		$this->_arrFooter	= Array();
 		$this->_arrFooter['**Type']			= 'Footer';
 		$this->_arrFooter['**Request']		= 'Footer';
 		$this->_arrFooter['RecordCount']	= count($this->_arrFileContent);
 		
 		// Parent Export
 		return parent::Export();
 	}
 }
?>
