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
	
	public static $intBaseCarrier	= CARRIER_UNITEL;
	public static $intBaseFileType	= FILE_EXPORT_UNITEL_PRESELECTION;
	
	
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
 		
 		// Carrier
 		$this->intBaseCarrier	= CARRIER_UNITEL;
 		$this->intBaseFileType	= FILE_EXPORT_UNITEL_PRESELECTION;
 		
 		// Carrier Reference / Line Number Init
 		$this->intCarrierReference	= 1;
 		
 		// Module Description
 		$this->strDescription		= "Preselection";
 		
 		// File Type
 		$this->intFileType			= FILE_EXPORT_UNITEL_PRESELECTION;
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Server']			['Default']	= 'ftp.rslcom.com.au';
 		$this->_arrModuleConfig['Server']			['Type']	= DATA_TYPE_STRING;
 		
 		$this->_arrModuleConfig['User']				['Default']	= '';
 		$this->_arrModuleConfig['User']				['Type']	= DATA_TYPE_STRING;
 		
 		$this->_arrModuleConfig['Password']			['Default']	= '';
 		$this->_arrModuleConfig['Password']			['Type']	= DATA_TYPE_STRING;
 		
 		$this->_arrModuleConfig['Path']				['Default']	= '/dailychurn/';
 		$this->_arrModuleConfig['Path']				['Type']	= DATA_TYPE_STRING;
 		
 		// Additional
 		$this->_arrModuleConfig['FileSequence']		['Default']	= 0;
 		$this->_arrModuleConfig['FileSequence']		['Type']	= DATA_TYPE_INTEGER;
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strOutput		= 'CSV';
 		$this->_strDelimiter	= "";
 		$this->_strNewLine		= "\r\n";
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['Sender']		['Start']		= 0;
		$arrDefine['Sender']		['Length']		= 2;
		
		$arrDefine['Recipient']		['Start']		= 2;
		$arrDefine['Recipient']		['Length']		= 2;
		$arrDefine['Recipient']		['Value']		= "rs";
		
		$arrDefine['System']		['Start']		= 4;
		$arrDefine['System']		['Length']		= 1;
		$arrDefine['System']		['Value']		= "w";
		
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
		$arrDefine['Sender']		['Value']		= "sa";						// Put this in Customer Config!!
		
		$arrDefine['Recipient']		['Start']		= 16;
		$arrDefine['Recipient']		['Length']		= 2;
		$arrDefine['Recipient']		['Value']		= "rs";
		
		$arrDefine['System']		['Start']		= 18;
		$arrDefine['System']		['Length']		= 1;
		$arrDefine['System']		['Value']		= "w";
		
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
		
		$this->_arrDefine[REQUEST_PRESELECTION] = $arrDefine;
		
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
		
		$this->_arrDefine[REQUEST_BAR_SOFT] = $arrDefine;
		$this->_arrDefine[REQUEST_BAR_HARD] = $arrDefine;
		
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
		
		$this->_arrDefine[REQUEST_UNBAR_SOFT] = $arrDefine;
		$this->_arrDefine[REQUEST_UNBAR_HARD] = $arrDefine;
 		
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
		
		$this->_arrDefine[REQUEST_ACTIVATION] = $arrDefine;
 		
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
		
		$this->_arrDefine[REQUEST_DEACTIVATION] = $arrDefine;
 		
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
		
		$this->_arrDefine[REQUEST_PRESELECTION_REVERSE] = $arrDefine;
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
 			case REQUEST_PRESELECTION:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				$arrRendered['AgreementDate']	= date("Ymd", strtotime($arrRequest['RequestedOn']));
 				break;
 				
 			case REQUEST_BAR_SOFT:
 			case REQUEST_BAR_HARD:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				break;
 				
 			case REQUEST_UNBAR_SOFT:
 			case REQUEST_UNBAR_HARD:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				break;
 				
 			case REQUEST_ACTIVATION:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				$arrRendered['AgreementDate']	= date("Ymd", strtotime($arrRequest['RequestedOn']));
 				break;
 				
 			case REQUEST_DEACTIVATION:
 				$arrRendered['FNN']				= $arrRequest['FNN'];
 				break;
 				
 			case REQUEST_PRESELECTION_REVERSE:
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
 		// Generate File Name
 		$this->_arrFilename	= Array();
 		$this->_arrFilename['**Type']		= 'Filename';
 		$this->_arrFilename['**Request']	= 'Filename';
 		$this->_arrFilename['Sequence']		= $this->_GetCarrierProperty('File');
 		$this->_arrFilename['Sender']		= $GLOBALS['**arrCustomerConfig']['Provisioning']['Carrier'][CARRIER_UNITEL]['SenderCode'];
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']			= 'Header';
 		$this->_arrHeader['**Request']		= 'Header';
 		$this->_arrHeader['FileSequence']	= $this->_GetCarrierProperty('File');
 		$this->_arrHeader['AgreementDate']	= date("Ymd");
 		
 		// Generate Footer
 		$this->_arrFooter	= Array();
 		$this->_arrFooter['**Type']			= 'Footer';
 		$this->_arrFooter['**Request']		= 'Footer';
 		$this->_arrFooter['RecordCount']	= count($this->_arrFileContent);
 		
 		// Parent Export
 		parent::Export();
 	}
 }
?>
