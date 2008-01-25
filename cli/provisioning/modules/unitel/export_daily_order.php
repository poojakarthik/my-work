<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
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
 * @version		7.12
 * @copyright	2007 VOIPTEL Pty Ltd
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
	 * @return	ImportBase
	 *
	 * @method
	 */
 	function __construct()
 	{
 		// Parent Constructor
 		parent::__construct();
 		
 		// Carrier
 		$this->intCarrier			= CARRIER_UNITEL;
 		
 		// Carrier Reference / Line Number Init
 		$this->intCarrierReference	= 1;
 		
 		// Module Description
 		$this->strDescription		= "Daily Order";
 		
 		// File Type
 		$this->intFileType			= FILE_EXPORT_UNITEL_DAILY_ORDER;
		
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
		$arrDefine['CSP']			['Start']		= 0;
		$arrDefine['CSP']			['Length']		= 3;
		
		$arrDefine['RSL']			['Start']		= 3;
		$arrDefine['RSL']			['Length']		= 3;
		$arrDefine['RSL']			['Value']		= "rsl";
		
		$arrDefine['System']		['Start']		= 6;
		$arrDefine['System']		['Length']		= 1;
		$arrDefine['System']		['Value']		= "w";
		
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
		
		$arrDefine['Filename']		['Start']		= 2;
		$arrDefine['Filename']		['Length']		= 21;
		
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
		
		$arrDefine['AdTypeSuffix']	['Start']		= 427;
		$arrDefine['AdTypeSuffix']	['Length']		= 2;
		
		$arrDefine['StNumberStart']	['Start']		= 429;
		$arrDefine['StNumberStart']	['Length']		= 5;
		
		$arrDefine['StNumberEnd']	['Start']		= 434;
		$arrDefine['StNumberEnd']	['Length']		= 5;
		
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
		
		$this->_arrDefine[REQUEST_FULL_SERVICE] = $arrDefine;
		
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
		
		$this->_arrDefine[REQUEST_FULL_SERVICE_REVERSE] = $arrDefine;
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
 		$this->_arrFilename['Sender']		= $GLOBALS['**arrCustomerConfig']['Provisioning']['Carrier'][CARRIER_UNITEL]['CSPCode'];
 		$this->_arrFilename['Date']			= date("Ymd");
 		
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
