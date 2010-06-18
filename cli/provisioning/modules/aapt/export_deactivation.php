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
	public $intBaseFileType			= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_DEACTIVATION;
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
 		$this->_strDelimiter	= ",";
 		$this->_strNewLine		= "\r\n";
 		
 		$this->_arrDefine		= Array();
 		
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
		
		$arrDefine['Time']			['Start']		= 16;
		$arrDefine['Time']			['Length']		= 6;
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RowCode']		['Index']		= 0;
		$arrDefine['RowCode']		['Value']		= 'H';
		
		$arrDefine['FileName']		['Index']		= 1;
		$arrDefine['FileName']		['Length']		= 12;
		
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
		
		$arrDefine['BatchNo']				['Index']		= 1;
		
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
 		
		$arrRendered['ServiceNumber']		= $arrRequest['FNN'];
		
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
 		$this->intFileSequence++;
 		
 		// Generate File Name
 		$this->_arrFilename	= Array();
 		$this->_arrFilename['**Type']		= 'Filename';
 		$this->_arrFilename['**Request']	= 'Filename';
 		$this->_arrFilename['Sequence']		= $this->intFileSequence;
 		$this->_arrFilename['Date']			= date("Ymd");
 		$this->_arrFilename['Time']			= date("His");
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']			= 'Header';
 		$this->_arrHeader['**Request']		= 'Header';
 		$this->_arrHeader['Sequence']		= $this->intFileSequence;
 		$this->_arrHeader['FileName']		= $this->_RenderLineTXT($this->_arrFilename, FALSE, '');
 		
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