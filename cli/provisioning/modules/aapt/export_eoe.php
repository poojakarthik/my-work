<?php
/**
 * ExportAAPTEOE
 *
 * Exports AAPT Preselection File Requests
 *
 * @class		ExportAAPTEOE
 */
 class ExportAAPTEOE extends ExportBase
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
	
	public $intBaseCarrier			= CARRIER_AAPT;
	public $intBaseFileType			= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_EOE;
	public $_strDeliveryType		= 'WWW';
	
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
 		$this->strDescription		= "Electronic Order Entry (EOE) File";
		
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
 		$this->_arrModuleConfig['FileSequence']		['Description']	= "File Sequence (YYMMDD.NNN)";
 		$this->_arrModuleConfig['FileSequence']		['AutoUpdate']	= TRUE;
 		
 		$this->_arrModuleConfig['CSPCode']			['Default']		= '';
 		$this->_arrModuleConfig['CSPCode']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['CSPCode']			['Description']	= "YBS Customer's CSP Code/File Identifier";
 		
 		$this->_arrModuleConfig['WholesaleAccountNumber']	['Default']		= '';
 		$this->_arrModuleConfig['WholesaleAccountNumber']	['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['WholesaleAccountNumber']	['Description']	= "YBS Customer's CSP Wholesale Account Number";
 		
 		$this->_arrModuleConfig['WholesalerName']			['Default']		= '';
 		$this->_arrModuleConfig['WholesalerName']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['WholesalerName']			['Description']	= "YBS Customer's CSP Wholesaler Name";
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'CSV';
 		$this->_strDelimiter	= "";
 		$this->_strNewLine		= "\n";
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['CSP']			['Start']		= 0;
		$arrDefine['CSP']			['Length']		= 2;
		$arrDefine['CSP']			['Config']		= 'CSPCode';
		
		$arrDefine['Date']			['Start']		= 2;
		$arrDefine['Date']			['Length']		= 6;
		
		$arrDefine['Separator']		['Start']		= 10;
		$arrDefine['Separator']		['Length']		= 1;
		$arrDefine['Separator']		['Value']		= ".";
		
		$arrDefine['Sequence']		['Start']		= 11;
		$arrDefine['Sequence']		['Length']		= 3;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['HeaderIdentifier']			['Start']		= 0;
		$arrDefine['HeaderIdentifier']			['Length']		= 11;
		$arrDefine['HeaderIdentifier']			['Value']		= 'CPN HEADER ';
		
		$arrDefine['Date']						['Start']		= 11;
		$arrDefine['Date']						['Length']		= 10;
		
		$arrDefine['Time']						['Start']		= 21;
		$arrDefine['Time']						['Length']		= 8;
		
		$arrDefine['RecordCount']				['Start']		= 29;
		$arrDefine['RecordCount']				['Length']		= 7;
		$arrDefine['RecordCount']				['Type']		= 'Integer';
		$arrDefine['RecordCount']				['PadChar']		= ' ';
		$arrDefine['RecordCount']				['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['ProgramVersion']			['Start']		= 36;
		$arrDefine['ProgramVersion']			['Length']		= 2;
		$arrDefine['ProgramVersion']			['Value']		= '00';
		
		$arrDefine['WholesaleAccountNumber']	['Start']		= 38;
		$arrDefine['WholesaleAccountNumber']	['Length']		= 11;
		$arrDefine['WholesaleAccountNumber']	['Config']		= 'WholesaleAccountNumber';
		
		$arrDefine['FileSequence']				['Start']		= 49;
		$arrDefine['FileSequence']				['Length']		= 9;
		
		$arrDefine['WholesalerName']			['Start']		= 58;
		$arrDefine['WholesalerName']			['Length']		= 20;
		$arrDefine['WholesalerName']			['Config']		= 'WholesalerName';
		
		$arrDefine['Whitespace']				['Start']		= 78;
		$arrDefine['Whitespace']				['Length']		= 422;
		$arrDefine['Whitespace']				['PadChar']		= ' ';
		$arrDefine['Whitespace']				['PadType']		= STR_PAD_LEFT;
		
		$this->_arrDefine['Header'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// FOOTER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['Trailer']	['Start']		= 0;
		$arrDefine['Trailer']	['Length']		= 11;
		$arrDefine['Trailer']	['Value']		= 'CPN TRAILER';
		
		$arrDefine['Whitespace']	['Start']		= 11;
		$arrDefine['Whitespace']	['Length']		= 500 - 11;
		$arrDefine['RecordCount']	['PadChar']		= ' ';
		$arrDefine['RecordCount']	['PadType']		= STR_PAD_LEFT;
		
		$this->_arrDefine['Footer'] = $arrDefine;
 		
 		
 		//--------------------------------------------------------------------//
 		// Detail Record
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['ReturnCode']				['Start']		= 0;	// Blank
		$arrDefine['ReturnCode']				['Length']		= 1;
		$arrDefine['ReturnCode']				['Value']		= ' ';
		
		$arrDefine['ActionCode']				['Start']		= 1;
		$arrDefine['ActionCode']				['Length']		= 1;
		
		$arrDefine['CPN']						['Start']		= 2;	// Calling Party Number (aka FNN)
		$arrDefine['CPN']						['Length']		= 30;
		$arrDefine['CPN']						['Type']		= 'FNN';
		
		$arrDefine['CustomerReferenceNumber']	['Start']		= 32;
		$arrDefine['CustomerReferenceNumber']	['Length']		= 12;
		
		$arrDefine['BusinessResidentialCode']	['Start']		= 44;
		$arrDefine['BusinessResidentialCode']	['Length']		= 1;
		
		$arrDefine['ApplicationDate']			['Start']		= 45;	// DDMMYYYY
		$arrDefine['ApplicationDate']			['Length']		= 8;
		
		$arrDefine['Description']				['Start']		= 53;	// Blank
		$arrDefine['Description']				['Length']		= 30;
		
		$arrDefine['RemoteLocationIndicator']	['Start']		= 83;
		$arrDefine['RemoteLocationIndicator']	['Length']		= 1;
		$arrDefine['RemoteLocationIndicator']	['Value']		= 'Y';
		
		$arrDefine['WorkSpecFlag']				['Start']		= 84;
		$arrDefine['WorkSpecFlag']				['Length']		= 1;
		$arrDefine['WorkSpecFlag']				['Value']		= 'N';
		
		$arrDefine['UserLastName']				['Start']		= 85;
		$arrDefine['UserLastName']				['Length']		= 40;
		
		$arrDefine['UserFirstName']				['Start']		= 125;
		$arrDefine['UserFirstName']				['Length']		= 20;
		
		$arrDefine['UserMiddleInitial']			['Start']		= 145;
		$arrDefine['UserMiddleInitial']			['Length']		= 1;
		
		$arrDefine['StreetName']				['Start']		= 146;
		$arrDefine['StreetName']				['Length']		= 40;
		
		$arrDefine['Suburb']					['Start']		= 186;
		$arrDefine['Suburb']					['Length']		= 40;
		
		$arrDefine['UserCity']					['Start']		= 226;
		$arrDefine['UserCity']					['Length']		= 25;
		
		$arrDefine['UserState']					['Start']		= 251;
		$arrDefine['UserState']					['Length']		= 3;
		
		$arrDefine['UserPostCode']				['Start']		= 254;
		$arrDefine['UserPostCode']				['Length']		= 4;
		
		$arrDefine['CustomerTitle']				['Start']		= 258;
		$arrDefine['CustomerTitle']				['Length']		= 4;
		
		$arrDefine['AttentionName']				['Start']		= 262;
		$arrDefine['AttentionName']				['Length']		= 20;
		
		$arrDefine['AttentionPosition']			['Start']		= 282;
		$arrDefine['AttentionPosition']			['Length']		= 20;
		
		$arrDefine['CountryCode']				['Start']		= 302;
		$arrDefine['CountryCode']				['Length']		= 4;
		$arrDefine['CountryCode']				['Value']		= '0000';
		
		$arrDefine['TelephoneNumber']			['Start']		= 306;
		$arrDefine['TelephoneNumber']			['Length']		= 30;
		
		$arrDefine['FaxCountryCode']			['Start']		= 336;
		$arrDefine['FaxCountryCode']			['Length']		= 4;
		$arrDefine['FaxCountryCode']			['Value']		= '0000';
		
		$arrDefine['FaxNumber']					['Start']		= 340;
		$arrDefine['FaxNumber']					['Length']		= 30;
		
		$arrDefine['CPNTerminationDate']		['Start']		= 370;	// Must be a future date
		$arrDefine['CPNTerminationDate']		['Length']		= 8;
		
		$arrDefine['ScopeOfBusiness']			['Start']		= 378;
		$arrDefine['ScopeOfBusiness']			['Length']		= 3;
		$arrDefine['ScopeOfBusiness']			['Value']		= 'I/N';
		
		$arrDefine['Filler']					['Start']		= 381;
		$arrDefine['Filler']					['Length']		= 95;
		
		$arrDefine['FieldInError1']				['Start']		= 476;
		$arrDefine['FieldInError1']				['Length']		= 3;
		
		$arrDefine['ErrorCode1']				['Start']		= 479;
		$arrDefine['ErrorCode1']				['Length']		= 5;
		
		$arrDefine['FieldInError2']				['Start']		= 484;
		$arrDefine['FieldInError2']				['Length']		= 3;
		
		$arrDefine['ErrorCode2']				['Start']		= 487;
		$arrDefine['ErrorCode2']				['Length']		= 5;
		
		$arrDefine['FieldInError3']				['Start']		= 492;
		$arrDefine['FieldInError3']				['Length']		= 3;
		
		$arrDefine['ErrorCode3']				['Start']		= 495;
		$arrDefine['ErrorCode3']				['Length']		= 5;
		
		// All Detail Records are in the same form
		$this->_arrDefine[PROVISIONING_TYPE_ACTIVATION]				= $arrDefine;
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION]			= $arrDefine;
		$this->_arrDefine[PROVISIONING_TYPE_DEACTIVATION]			= $arrDefine;
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION_REVERSE]	= $arrDefine;
		$this->_arrDefine[PROVISIONING_TYPE_BAR]					= $arrDefine;
		$this->_arrDefine[PROVISIONING_TYPE_UNBAR]					= $arrDefine;
		
		$this->_arrDefine[PROVISIONING_TYPE_ACTIVATION]				['ActionCode']	['Value']	= 'A';
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION]			['ActionCode']	['Value']	= 'A';
		$this->_arrDefine[PROVISIONING_TYPE_UNBAR]					['ActionCode']	['Value']	= 'A';
		
		$this->_arrDefine[PROVISIONING_TYPE_DEACTIVATION]			['ActionCode']	['Value']	= 'U';
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION_REVERSE]	['ActionCode']	['Value']	= 'U';
		$this->_arrDefine[PROVISIONING_TYPE_BAR]					['ActionCode']	['Value']	= 'U';
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
 		
		$arrRendered['FNN']			= $arrRequest['FNN'];
 		
 		$arrRendered['Sequence']		= $this->intCarrierReference;
 		$arrRendered['**Type']			= $arrRequest['Type'];
 		$arrRendered['**Request']		= $arrRequest['Id'];
	 	$arrRendered['**CarrierRef']	= $this->intCarrierReference;
	 	
 		$this->_arrFileContent[]		= $arrRendered;
 		
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
