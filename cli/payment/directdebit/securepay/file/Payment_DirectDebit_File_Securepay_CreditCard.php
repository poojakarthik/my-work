<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// export_preselection
//----------------------------------------------------------------------------//
/**
 * export_preselection
 *
 * Exports Unitel Preselection File Requests
 *
 * Exports Unitel Preselection File Requests
 *
 * @file		export_preselection.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		7.11
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ExportUnitelPreselection
//----------------------------------------------------------------------------//
/**
 * ExportUnitelPreselection
 *
 * Exports Unitel Preselection File Requests
 *
 * Exports Unitel Preselection File Requests
 *
 * @prefix		exp
 *
 * @package		provisioning
 * @class		ExportUnitelPreselection
 */
 class ExportUnitelPreselection extends ExportBase
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
	
	public $intBaseCarrier		= CARRIER_UNITEL;
	public $intBaseFileType		= FILE_EXPORT_PROVISIONING_UNITEL_PRESELECTION;
	public $_strDeliveryType	= 'FTP';
	
	
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
 		$this->strDescription		= "Preselection";
 		
 		// Carrier Reference / Line Number Init
 		$this->intCarrierReference	= 1;
		
 		// Get Fields which are going to be modified
 		$this->intFileSequence		= &$this->GetConfigField('FileSequence');
 		
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
 		
 		$this->_arrModuleConfig['Path']				['Default']		= '/dailychurn/';
 		$this->_arrModuleConfig['Path']				['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Path']				['Description']	= "Directory to drop the file in";
 		
 		// Additional
 		$this->_arrModuleConfig['FileSequence']		['Default']		= 0;
 		$this->_arrModuleConfig['FileSequence']		['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['FileSequence']		['Description']	= "File Sequence Number";
 		$this->_arrModuleConfig['FileSequence']		['AutoUpdate']	= TRUE;
 		
 		$this->_arrModuleConfig['CarrierCode']		['Default']		= 'rs';
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
		$arrDefine['Sender']		['Start']		= 0;
		$arrDefine['Sender']		['Length']		= 2;
		$arrDefine['Sender']		['Config']		= 'CSPCode';
		
		$arrDefine['Recipient']		['Start']		= 2;
		$arrDefine['Recipient']		['Length']		= 2;
		$arrDefine['Recipient']		['Config']		= 'CarrierCode';
		
		$arrDefine['System']		['Start']		= 4;
		$arrDefine['System']		['Length']		= 1;
		$arrDefine['System']		['Config']		= 'System';
		
		$arrDefine['Sequence']		['Start']		= 5;
		$arrDefine['Sequence']		['Length']		= 4;
		$arrDefine['Sequence']		['Type']		= 'Integer';
		$arrDefine['Sequence']		['PadChar']		= '0';
		$arrDefine['Sequence']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['Extension']		['Start']		= 9;
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
		
		$arrDefine['AgreementDate']	['Start']		= 2;
		$arrDefine['AgreementDate']	['Length']		= 8;
		$arrDefine['AgreementDate']	['Type']		= 'Date::YYYYMMDD';
		
		$arrDefine['FileSequence']	['Start']		= 10;
		$arrDefine['FileSequence']	['Length']		= 4;
		$arrDefine['FileSequence']	['Type']		= 'Integer';
		$arrDefine['FileSequence']	['PadChar']		= '0';
		$arrDefine['FileSequence']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['Sender']		['Start']		= 14;
		$arrDefine['Sender']		['Length']		= 2;
		$arrDefine['Sender']		['Config']		= 'CSPCode';
		
		$arrDefine['Recipient']		['Start']		= 16;
		$arrDefine['Recipient']		['Length']		= 2;
		$arrDefine['Recipient']		['Config']		= 'CarrierCode';
		
		$arrDefine['System']		['Start']		= 18;
		$arrDefine['System']		['Length']		= 1;
		$arrDefine['System']		['Config']		= 'System';
		
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
 		// Preselection
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '11';
		
		$arrDefine['FNN']			['Start']		= 2;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['AgreementDate']	['Start']		= 12;
		$arrDefine['AgreementDate']	['Length']		= 8;
		$arrDefine['AgreementDate']	['Type']		= 'Date::YYYYMMDD';
		
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Bar
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '55';
		
		$arrDefine['FNN']			['Start']		= 2;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['Action']		['Start']		= 12;
		$arrDefine['Action']		['Length']		= 1;
		$arrDefine['Action']		['Type']		= 'Integer';
		$arrDefine['Action']		['Value']		= '1';
		
		$this->_arrDefine[PROVISIONING_TYPE_BAR] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// UnBar
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '55';
		
		$arrDefine['FNN']			['Start']		= 2;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['Action']		['Start']		= 12;
		$arrDefine['Action']		['Length']		= 1;
		$arrDefine['Action']		['Type']		= 'Integer';
		$arrDefine['Action']		['Value']		= '0';
		
		$this->_arrDefine[PROVISIONING_TYPE_UNBAR] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// Activation
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '10';
		
		$arrDefine['FNN']			['Start']		= 2;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['AgreementDate']	['Start']		= 12;
		$arrDefine['AgreementDate']	['Length']		= 8;
		$arrDefine['AgreementDate']	['Type']		= 'Date::YYYYMMDD';
		
		$this->_arrDefine[PROVISIONING_TYPE_ACTIVATION] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// Deactivation
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '20';
		
		$arrDefine['FNN']			['Start']		= 2;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$this->_arrDefine[PROVISIONING_TYPE_DEACTIVATION] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// Preselection Reversal
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']	['Start']		= 0;
		$arrDefine['RecordType']	['Length']		= 2;
		$arrDefine['RecordType']	['Type']		= 'Integer';
		$arrDefine['RecordType']	['PadChar']		= '0';
		$arrDefine['RecordType']	['PadType']		= STR_PAD_LEFT;
		$arrDefine['RecordType']	['Value']		= '21';
		
		$arrDefine['FNN']			['Start']		= 2;
		$arrDefine['FNN']			['Length']		= 10;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION_REVERSE] = $arrDefine;
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
 		$this->intCarrierReference++;
 		
 		//--------------------------------------------------------------------//
 		// RENDER
 		//--------------------------------------------------------------------//
 		$arrRendered	= Array();
 		switch ($arrRequest['Type'])
 		{
 			case PROVISIONING_TYPE_PRESELECTION:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				$arrRendered['AgreementDate']	= date("Ymd", strtotime($arrRequest['RequestedOn']));
 				break;
 				
 			case PROVISIONING_TYPE_BAR:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				break;
 				
 			case PROVISIONING_TYPE_UNBAR:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				break;
 				
 			case PROVISIONING_TYPE_ACTIVATION:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				$arrRendered['AgreementDate']	= date("Ymd", strtotime($arrRequest['RequestedOn']));
 				break;
 				
 			case PROVISIONING_TYPE_DEACTIVATION:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				break;
 				
 			case PROVISIONING_TYPE_PRESELECTION_REVERSE:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				break;
 		}
 		
 		$arrRendered['**Type']		= $arrRequest['Type'];
 		$arrRendered['**Request']	= $arrRequest['Id'];
 		$this->_arrFileContent[]	= $arrRendered;
 		
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
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']			= 'Header';
 		$this->_arrHeader['**Request']		= 'Header';
 		$this->_arrHeader['FileSequence']	= $this->intFileSequence;
 		$this->_arrHeader['AgreementDate']	= date("Ymd");
 		
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
