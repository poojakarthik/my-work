<?php
/**
 * ExportAAPTFullServiceRebill
 *
 * Exports AAPT Full Service File Requests
 *
 * @class		ExportAAPTFullServiceRebill
 */
 class ExportAAPTFullServiceRebill extends ExportBase
 {
 	const	WHITELIST_CODE	= 101;
 	
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
	public $intBaseFileType			= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_FULLSERVICEREBILL;
	public $_strDeliveryType		= 'FTP';
	
	public $_intFrequencyType		= FREQUENCY_DAY;
	public $_intFrequency			= 1;
	public $_intEarliestDelivery	= 54000;
	
	/**
	 * __construct()
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
 		$this->strDescription		= "Full Service Rebill Activation";
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		// Mandatory
 		$this->_arrModuleConfig['Server']			['Default']		= 'ftp.powertel.com.au';
 		$this->_arrModuleConfig['Server']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Server']			['Description']	= "FTP Server to connect to";
 		
 		$this->_arrModuleConfig['User']				['Default']		= '';
 		$this->_arrModuleConfig['User']				['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['User']				['Description']	= "FTP Username";
 		
 		$this->_arrModuleConfig['Password']			['Default']		= '';
 		$this->_arrModuleConfig['Password']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Password']			['Description']	= "FTP Password";
 		
 		$this->_arrModuleConfig['Path']				['Default']		= '';
 		$this->_arrModuleConfig['Path']				['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Path']				['Description']	= "Directory to drop the file in";
 		
 		// Additional
 		$this->_arrModuleConfig['ResellerCode']		['Default']		= '';
 		$this->_arrModuleConfig['ResellerCode']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ResellerCode']		['Description']	= "Reseller Code (3-character)";
 		
 		$this->_arrModuleConfig['System']			['Default']		= 'PWT';
 		$this->_arrModuleConfig['System']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['System']			['Description']	= "Receiving System (3-character)";
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'CSV';
 		$this->_strDelimiter	= ",";
 		$this->_strNewLine		= "\n";
 		
 		$this->_arrDefine		= Array();
 		
 		$this->_iTimestamp	= time();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['FileTypeA']		['Start']		= 0;
		$arrDefine['FileTypeA']		['Length']		= 1;
		$arrDefine['FileTypeA']		['Value']		= 'D';
		
		$arrDefine['ResellerCode']	['Start']		= 1;
		$arrDefine['ResellerCode']	['Length']		= 3;
		$arrDefine['ResellerCode']	['Config']		= 'ResellerCode';
		
		$arrDefine['System']		['Start']		= 4;
		$arrDefine['System']		['Length']		= 3;
		$arrDefine['System']		['Config']		= 'System';
		
		$arrDefine['FileTypeB']		['Start']		= 7;
		$arrDefine['FileTypeB']		['Length']		= 1;
		$arrDefine['FileTypeB']		['Value']		= 'F';
		
		$arrDefine['Date']			['Start']		= 8;
		$arrDefine['Date']			['Length']		= 8;
		$arrDefine['Date']			['Value']		= date('Ymd', $this->_iTimestamp);
		
		$arrDefine['Time']			['Start']		= 16;
		$arrDefine['Time']			['Length']		= 6;
		$arrDefine['Time']			['Value']		= date('His', $this->_iTimestamp);
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RowCode']		['Index']		= 0;
		$arrDefine['RowCode']		['Value']		= 'H';
		
		$arrDefine['FileName']		['Index']		= 1;
		$arrDefine['FileName']		['Length']		= 22;
		
		$this->_arrDefine['Header'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// FOOTER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RowCode']		['Index']		= 0;
		$arrDefine['RowCode']		['Value']		= 'T';
		
		$arrDefine['RecordCount']	['Index']		= 2;
		$arrDefine['RecordCount']	['Type']		= 'Integer';
		
		$this->_arrDefine['Footer'] = $arrDefine;
 		
 		
 		//--------------------------------------------------------------------//
 		// Churn/Full Service
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RowCode']				['Index']		= 0;
		$arrDefine['RowCode']				['Value']		= 'D';
		
		$arrDefine['BatchNo']				['Index']		= 1;
		$arrDefine['BatchNo']				['Value']		= date('YmdHis', $this->_iTimestamp);
		
		$arrDefine['IDNo']					['Index']		= 2;
		
		$arrDefine['RecordType']			['Index']		= 3;
		$arrDefine['RecordType']			['Value']		= 12;
		$arrDefine['RecordType']			['Type']		= 'Integer';
		
		$arrDefine['ServiceNumber']			['Index']		= 4;
		
		$arrDefine['CADate']				['Index']		= 5;
		
		$arrDefine['BillName']				['Index']		= 6;
		
		$arrDefine['BillAddr1']				['Index']		= 7;
		
		$arrDefine['BillAddr2']				['Index']		= 8;
		
		$arrDefine['BillAddrLoc']			['Index']		= 9;
		
		$arrDefine['BillAddrPostcode']		['Index']		= 10;
		$arrDefine['BillAddrPostcode']		['Length']		= 4;
		$arrDefine['BillAddrPostcode']		['Type']		= 'Integer';
		$arrDefine['BillAddrPostcode']		['PadChar']		= '0';
		$arrDefine['BillAddrPostcode']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['EndUserTitle']			['Index']		= 11;
		
		$arrDefine['EndUserFirstName']		['Index']		= 12;
		
		$arrDefine['EndUserSurname']		['Index']		= 13;
		
		$arrDefine['EndUserCmp']			['Index']		= 14;
		
		$arrDefine['EndUserDOB']			['Index']		= 15;
		$arrDefine['EndUserDOB']			['Optional']	= '        ';
		
		$arrDefine['AddressType']			['Index']		= 16;
		
		$arrDefine['AddressTypeNumber']		['Index']		= 17;
		$arrDefine['AddressTypeNumber']		['Length']		= 5;
		$arrDefine['AddressTypeNumber']		['Type']		= 'Integer';
		$arrDefine['AddressTypeNumber']		['PadChar']		= '0';
		$arrDefine['AddressTypeNumber']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['AddressTypeSuffix']		['Index']		= 18;
		
		$arrDefine['StreetNumberStart']		['Index']		= 19;
		$arrDefine['StreetNumberStart']		['Length']		= 5;
		$arrDefine['StreetNumberStart']		['Type']		= 'Integer';
		$arrDefine['StreetNumberStart']		['PadChar']		= '0';
		$arrDefine['StreetNumberStart']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['StreetNumberEnd']		['Index']		= 20;
		$arrDefine['StreetNumberEnd']		['Length']		= 5;
		$arrDefine['StreetNumberEnd']		['Type']		= 'Integer';
		$arrDefine['StreetNumberEnd']		['PadChar']		= '0';
		$arrDefine['StreetNumberEnd']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['StreetNumberSuffix']	['Index']		= 21;
		
		$arrDefine['StreetName']			['Index']		= 22;
		
		$arrDefine['StreetType']			['Index']		= 23;
		
		$arrDefine['StreetTypeSuffix']		['Index']		= 24;
		
		$arrDefine['PropertyName']			['Index']		= 25;
		
		$arrDefine['Locality']				['Index']		= 26;
		
		$arrDefine['State']					['Index']		= 27;
		
		$arrDefine['Postcode']				['Index']		= 28;
		
		$arrDefine['WhitelistRefCode']		['Index']		= 29;
		$arrDefine['WhitelistRefCode']		['Value']		= self::WHITELIST_CODE;
		
		$this->_arrDefine[PROVISIONING_TYPE_FULL_SERVICE] = $arrDefine;
 	}
 	
	/**
	 * Output()
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
 		$arrRendered				= array();
 		
 		$arrRendered['IDNo']	= $arrRequest['Id'];
 		
 		// Service Address
		$arrServiceAddress	= $this->_CleanServiceAddress($arrRequest['Service']);
		
		if (is_string($arrServiceAddress))
		{
			// Service Address Problems
	 		$arrRequest['Status']		= REQUEST_STATUS_REJECTED_FLEX;
	 		$arrRequest['Description']	= $arrServiceAddress;
			return $arrRequest;
		}
		
		// Remove all commas from the Service Address details
		foreach ($arrServiceAddress as $sField=>$mValue)
		{
			$arrServiceAddress[$sField]	= str_replace(',', '', $mValue);
		}
		
		// Common
		$arrRendered['CADate']				= date("Ymd", strtotime($arrRequest['AuthorisationDate']));
		$arrRendered['BillName']			= $arrServiceAddress['BillName'];
		$arrRendered['BillAddr1']			= $arrServiceAddress['BillAddress1'];
		$arrRendered['BillAddr2']			= $arrServiceAddress['BillAddress2'];
		$arrRendered['BillAddrLoc']			= $arrServiceAddress['BillLocality'];
		$arrRendered['BillAddrPostcode']	= $arrServiceAddress['BillPostcode'];
		
		// Residential
		$arrRendered['EndUserTitle']		= $arrServiceAddress['EndUserTitle'];
		$arrRendered['EndUserFirstName']	= $arrServiceAddress['EndUserGivenName'];
		$arrRendered['EndUserSurname']		= $arrServiceAddress['EndUserFamilyName'];
		$arrRendered['EndUserDOB']			= $arrServiceAddress['DateOfBirth'];
		
		// Business
		$arrRendered['EndUserCmp']			= $arrServiceAddress['EndUserCompanyName'];
		
		// Service Location Details
		$arrRendered['AddressType']			= $arrServiceAddress['ServiceAddressType'];
		$arrRendered['AddressTypeNumber']	= $arrServiceAddress['ServiceAddressTypeNumber'];
		$arrRendered['AddressTypeSuffix']	= $arrServiceAddress['ServiceAddressTypeSuffix'];
		$arrRendered['StreetNumberStart']	= $arrServiceAddress['ServiceStreetNumberStart'];
		$arrRendered['StreetNumberEnd']		= $arrServiceAddress['ServiceStreetNumberEnd'];
		$arrRendered['StreetNumSuffix']		= $arrServiceAddress['ServiceStreetNumberSuffix'];
		$arrRendered['StreetName']			= $arrServiceAddress['ServiceStreetName'];
		$arrRendered['StreetType']			= $arrServiceAddress['ServiceStreetType'];
		$arrRendered['StreetTypeSuffix']	= $arrServiceAddress['ServiceStreetTypeSuffix'];
		$arrRendered['PropertyName']		= $arrServiceAddress['ServicePropertyName'];
		$arrRendered['Locality']			= $arrServiceAddress['ServiceLocality'];
		$arrRendered['State']				= $arrServiceAddress['ServiceState'];
		$arrRendered['Postcode']			= $arrServiceAddress['ServicePostcode'];
 		
		$arrRendered['ServiceNumber']		= $arrRequest['FNN'];
 		
 		$arrRendered['**Type']			= $arrRequest['Type'];
 		$arrRendered['**Request']		= $arrRequest['Id'];
 		$arrRendered['**CarrierRef']	= $arrRendered['IDNo'];
 		$this->_arrFileContent[]		= $arrRendered;
 		
 		//--------------------------------------------------------------------//
 		// MODIFICATIONS TO REQUEST RECORD
 		//--------------------------------------------------------------------//
 		$arrRequest['CarrierRef']	= $arrRequest['Id'];
 		$arrRequest['Status']		= REQUEST_STATUS_EXPORTING;
 		
 		// Return the modified Request
 		return $arrRequest;
 	}
 	
	/**
	 * Export()
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
 		// Generate File Name
 		$this->_arrFilename	= Array();
 		$this->_arrFilename['**Type']		= 'Filename';
 		$this->_arrFilename['**Request']	= 'Filename';
 		
 		$aFileName	= $this->_RenderLineTXT($this->_arrFilename, FALSE, '');
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']			= 'Header';
 		$this->_arrHeader['**Request']		= 'Header';
 		$this->_arrHeader['FileName']		= $aFileName['Line'];
 		
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