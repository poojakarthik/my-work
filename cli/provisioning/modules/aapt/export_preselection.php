<?php
/**
 * ExportAAPTPreselection
 *
 * Exports AAPT Preselection File Requests
 *
 * @class		ExportAAPTPreselection
 */
 class ExportAAPTPreselection extends ExportBase
 {
 	const	WHITELIST_CODE		= 101;
 	const	ASD_CODE_TELSTRA	= 2;
 	
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
	public $intBaseFileType			= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_PRESELECTION;
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
 		$this->strDescription		= "Preselection Activation";
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		/*
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
 		*/
 		
 		// <DEBUG>
		$this->_strDeliveryType	= 'EmailAttach';
 		
 		$this->_arrModuleConfig['Destination']		['Default']		= 'rdavis@ybs.net.au';
 		$this->_arrModuleConfig['Destination']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Destination']		['Description']	= "Destination Email Address";
 		
 		$this->_arrModuleConfig['Subject']			['Default']		= 'AAPT Preselection File';
 		$this->_arrModuleConfig['Subject']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Subject']			['Description']	= "Email Subject";
 		
 		$this->_arrModuleConfig['ReplyTo']			['Default']		= 'provisioning@yellowbilling.com.au';
 		$this->_arrModuleConfig['ReplyTo']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ReplyTo']			['Description']	= "Reply-To Email Address";
 		
 		$this->_arrModuleConfig['EmailContent']		['Default']		= 'AAPT Preselection File';
 		$this->_arrModuleConfig['EmailContent']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['EmailContent']		['Description']	= "Content for the Email";
 		// </DEBUG>
 		
 		// Additional
 		$this->_arrModuleConfig['ResellerCode']		['Default']		= '';
 		$this->_arrModuleConfig['ResellerCode']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ResellerCode']		['Description']	= "Reseller Code (3-character)";
 		
 		$this->_arrModuleConfig['System']			['Default']		= 'PWT';
 		$this->_arrModuleConfig['System']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['System']			['Description']	= "Receiving System (3-character)";
 		
 		$this->_arrModuleConfig['PreselectionASD']	['Default']		= self::ASD_CODE_TELSTRA;
 		$this->_arrModuleConfig['PreselectionASD']	['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['PreselectionASD']	['Description']	= "Preselection ASD";
		
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
		$arrDefine['FileTypeB']		['Value']		= 'M';
		
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
 		// Preselection
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RowCode']				['Index']		= 0;
		$arrDefine['RowCode']				['Value']		= 'D';
		
		$arrDefine['BatchNo']				['Index']		= 1;
		$arrDefine['BatchNo']				['Value']		= date('YmdHis', $this->_iTimestamp);
		
		$arrDefine['IDNo']					['Index']		= 2;
		
		$arrDefine['ASD']					['Index']		= 3;
		$arrDefine['ASD']					['Type']		= 'Integer';
		$arrDefine['ASD']					['Config']		= 'PreselectionASD';
		$arrDefine['ASD']					['PadChar']		= '0';
		$arrDefine['ASD']					['PadType']		= STR_PAD_LEFT;
		$arrDefine['ASD']					['Length']		= 3;
		
		$arrDefine['ServiceNumber']			['Index']		= 4;
		
		$arrDefine['Spectrum']				['Index']		= 5;
		$arrDefine['Spectrum']				['Value']		= 'N';	// WTF is a SPECTRUM Service?  Do we want to support this?  We don't store this information in Flex.
		
		$arrDefine['WhitelistRefCode']		['Index']		= 6;
		$arrDefine['WhitelistRefCode']		['Value']		= self::WHITELIST_CODE;
		
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION] = $arrDefine;
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
 		$arrRendered					= array();
		$arrRendered['IDNo']			= $arrRequest['Id'];
		$arrRendered['ServiceNumber']	= $arrRequest['FNN'];
		
 		$this->intCarrierReference++;
 		
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