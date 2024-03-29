<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//
 
//----------------------------------------------------------------------------//
// export_bar
//----------------------------------------------------------------------------//
/**
 * export_bar
 *
 * Exports Optus Bar File Requests
 *
 * Exports Optus Bar File Requests
 *
 * @file		export_bar.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ExportOptusBar
//----------------------------------------------------------------------------//
/**
 * ExportOptusBar
 *
 * Exports Optus Bar File Requests
 *
 * Exports Optus Bar File Requests
 *
 * @prefix		exp
 *
 * @package		provisioning
 * @class		ExportOptusBar
 */
 class ExportOptusBar extends ExportBase
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
	
	public $intBaseCarrier		= null;
	public $intBaseFileType		= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_BAR;
	public $_strDeliveryType	= 'EmailAttach';
	
	
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
 		
 		// Carrier Reference / Line Number Init
 		$this->intCarrierReference	= 0;
 		
 		// Module Description
 		$this->strDescription		= "Bar";
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Destination']		['Default']		= 'long.distance.spsg@optus.com.au';
 		$this->_arrModuleConfig['Destination']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Destination']		['Description']	= "Destination Email Address";
 		
 		$this->_arrModuleConfig['Subject']			['Default']		= 'Barring Request File for <Function::DateTime> for <Config::OptusAccount>';
 		$this->_arrModuleConfig['Subject']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Subject']			['Description']	= "Email Subject";
 		
 		$this->_arrModuleConfig['ReplyTo']			['Default']		= 'provisioning@yellowbilling.com.au';
 		$this->_arrModuleConfig['ReplyTo']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ReplyTo']			['Description']	= "Reply-To Email Address";
 		
 		$this->_arrModuleConfig['EmailContent']		['Default']		= 'Barring Request File for <Function::DateTime> for <Config::OptusAccount>';
 		$this->_arrModuleConfig['EmailContent']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['EmailContent']		['Description']	= "Content for the Email";
 		
 		// Additional
 		$this->_arrModuleConfig['CarbonCopy']		['Default']		= '';
 		$this->_arrModuleConfig['CarbonCopy']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['CarbonCopy']		['Description']	= "Additional Addresses to CC to";
 		
 		$this->_arrModuleConfig['OptusAccount']		['Default']		= '';
 		$this->_arrModuleConfig['OptusAccount']		['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['OptusAccount']		['Description']	= "The CSP's Optus Billing Account Number";
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'XLS';
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['FileType']		['Start']		= 0;
		$arrDefine['FileType']		['Length']		= 4;
		$arrDefine['FileType']		['Value']		= "bar_";
		
		$arrDefine['HoursMinutes']	['Start']		= 4;
		$arrDefine['HoursMinutes']	['Length']		= 4;
		$arrDefine['HoursMinutes']	['Type']		= 'Time::HHII';
		
		$arrDefine['Underscore']	['Start']		= 8;
		$arrDefine['Underscore']	['Length']		= 1;
		$arrDefine['Underscore']	['Value']		= "_";
		
		$arrDefine['Date']			['Start']		= 9;
		$arrDefine['Date']			['Length']		= 8;
		$arrDefine['Date']			['Type']		= 'Date::YYYYMMDD';
		
		$arrDefine['Extension']		['Start']		= 17;
		$arrDefine['Extension']		['Length']		= 4;
		$arrDefine['Extension']		['Value']		= ".xls";
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['ServiceNumber']	['Index']		= 0;
		$arrDefine['ServiceNumber']	['Type']		= 'String';
		$arrDefine['ServiceNumber']	['Value']		= 'Service Number';
		
		$arrDefine['OptusAccount']	['Index']		= 1;
		$arrDefine['OptusAccount']	['Type']		= 'String';
		$arrDefine['OptusAccount']	['Value']		= 'Billable Account Number';
		
		$arrDefine['ServiceType']	['Index']		= 2;
		$arrDefine['ServiceType']	['Type']		= 'String';
		$arrDefine['ServiceType']	['Value']		= 'Service Type';
		
		$arrDefine['CustomerRef']	['Index']		= 3;
		$arrDefine['CustomerRef']	['Type']		= 'String';
		$arrDefine['CustomerRef']	['Value']		= 'Customer Reference';
		
		$arrDefine['AccountId']		['Index']		= 4;
		$arrDefine['AccountId']		['Type']		= 'String';
		$arrDefine['AccountId']		['Value']		= 'TelcoBlue Account Number';
		
		$this->_arrDefine['Header'] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Bar
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['FNN']			['Index']		= 0;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['OptusAccount']	['Index']		= 1;
		$arrDefine['OptusAccount']	['Type']		= 'String';
		$arrDefine['OptusAccount']	['Config']		= 'OptusAccount';
		
		$arrDefine['ServiceType']	['Index']		= 2;
		$arrDefine['ServiceType']	['Type']		= 'String';
		$arrDefine['ServiceType']	['Value']		= 'UT';
		
		$arrDefine['CustomerRef']	['Index']		= 3;
		$arrDefine['CustomerRef']	['Type']		= 'String';
		$arrDefine['CustomerRef']	['Value']		= '';
		
		$arrDefine['AccountId']		['Index']		= 4;
		$arrDefine['AccountId']		['Type']		= 'Integer';
		
		$this->_arrDefine[PROVISIONING_TYPE_BAR] = $arrDefine;
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
 		$arrRendered['FNN']			= $arrRequest['FNN'];
 		$arrRendered['AccountId']	= $arrRequest['Account'];
 		
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
 		$this->_arrFilename['HoursMinutes']	= date("Hi");
 		$this->_arrFilename['Date']			= date("Ymd");
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']			= 'Header';
 		$this->_arrHeader['**Request']		= 'Header';
 		
 		// Parent Export
 		return parent::Export();
 	}
 }
?>
