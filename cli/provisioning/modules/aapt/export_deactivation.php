<?php
/**
 * ExportAAPTDeactivation
 *
 * Exports AAPT Deactivation File Requests
 *
 * @class		ExportAAPTPreselection
 */
 class ExportAAPTDeactivation extends ExportBase
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
	public $intBaseFileType			= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_DEACTIVATIONS;
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
 		$this->strDescription		= "Deactivation";
 		
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
 		
 		$this->_arrModuleConfig['Subject']			['Default']		= 'AAPT Deactivations File';
 		$this->_arrModuleConfig['Subject']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Subject']			['Description']	= "Email Subject";
 		
 		$this->_arrModuleConfig['ReplyTo']			['Default']		= 'provisioning@yellowbilling.com.au';
 		$this->_arrModuleConfig['ReplyTo']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ReplyTo']			['Description']	= "Reply-To Email Address";
 		
 		$this->_arrModuleConfig['EmailContent']		['Default']		= 'AAPT Deactivations File';
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
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'CSV';
 		$this->_strDelimiter	= ",";
 		$this->_strNewLine		= "\r\n";
 		
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
		$arrDefine['FileTypeB']		['Value']		= 'D';
		
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
 		// Detail Records
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RowCode']				['Index']		= 0;
		$arrDefine['RowCode']				['Value']		= 'D';
		
		$arrDefine['BatchNo']				['Index']		= 1;	// Actually the Record Number
		
		$arrDefine['ServiceNumber']			['Index']		= 4;
		
		$arrDefine['Action']				['Index']		= 5;
		
		$arrDefine['WhitelistRefCode']		['Index']		= 6;
		
		/* This is for LCR (Prefix Codes)
 		//--------------------------------------------------------------------//
 		// Deactivation
 		//--------------------------------------------------------------------//
		$arrDefine['Action']				['Value']				= 'N';
		$this->_arrDefine[PROVISIONING_TYPE_DEACTIVATION]			= $arrDefine;
		*/
		
 		//--------------------------------------------------------------------//
 		// Preselection Reversal
 		//--------------------------------------------------------------------//
		$arrDefine['Action']				['Value']				= 'Y';
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION_REVERSE]	= $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Full Service Reversal
 		//--------------------------------------------------------------------//
		$arrDefine['Action']				['Value']				= 'R';
		$this->_arrDefine[PROVISIONING_TYPE_FULL_SERVICE_REVERSE]	= $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Barring
 		//--------------------------------------------------------------------//
		$arrDefine['Action']				['Value']				= 'B';
		$this->_arrDefine[PROVISIONING_TYPE_BAR]					= $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Unbarring
 		//--------------------------------------------------------------------//
		$arrDefine['Action']				['Value']				= 'U';
		$this->_arrDefine[PROVISIONING_TYPE_UNBAR]					= $arrDefine;
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
		$arrRendered['ServiceNumber']	= $arrRequest['FNN'];
		$arrRendered['BatchNo']			= $arrRequest['Id'];
 		
 		$arrRendered['**Type']			= $arrRequest['Type'];
 		$arrRendered['**Request']		= $arrRequest['Id'];
 		$arrRendered['**CarrierRef']	= $arrRendered['BatchNo'];
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
 		$this->intFileSequence++;
 		
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