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
 * Exports Optus Preselection Reversal File Requests
 *
 * Exports Optus Preselection Reversal File Requests
 *
 * @file		export_preselect_reversal.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ExportOptusPreselectReversal
//----------------------------------------------------------------------------//
/**
 * ExportOptusPreselectReversal
 *
 * Exports Optus Preselection Reversal File Requests
 *
 * Exports Optus Preselection Reversal File Requests
 *
 * @prefix		exp
 *
 * @package		provisioning
 * @class		ExportOptusBar
 */
 class ExportOptusPreselectReversal extends ExportBase
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
	
	public $intBaseCarrier		= CARRIER_OPTUS;
	public $intBaseFileType		= RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_PRESELECTION_REVERSAL;
	public $_strDeliveryType	= 'Email';
	
	
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
 		$this->strDescription		= "Preselection Reversal";
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Destination']		['Default']		= 'long.distance.spsg@optus.com.au';
 		$this->_arrModuleConfig['Destination']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Destination']		['Description']	= "Destination Email Address";
 		
 		$this->_arrModuleConfig['Subject']			['Default']		= 'LD Churn Reversal';
 		$this->_arrModuleConfig['Subject']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Subject']			['Description']	= "Email Subject";
 		
 		$this->_arrModuleConfig['ReplyTo']			['Default']		= 'provisioning@yellowbilling.com.au';
 		$this->_arrModuleConfig['ReplyTo']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ReplyTo']			['Description']	= "Reply-To Email Address";
 		
 		$this->_arrModuleConfig['ContentGreeting']	['Default']		= '';
 		$this->_arrModuleConfig['ContentGreeting']	['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ContentGreeting']	['Description']	= "Text to preceed the Request content";
 		
 		$this->_arrModuleConfig['ContentSignature']	['Default']		= '';
 		$this->_arrModuleConfig['ContentSignature']	['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ContentSignature']	['Description']	= "Signature for the Email";
 		
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
 		$this->_strFileFormat	= 'CSV';
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['FileType']		['Start']		= 0;
		$arrDefine['FileType']		['Length']		= 12;
		$arrDefine['FileType']		['Value']		= "ld_reversal_";
		
		$arrDefine['HoursMinutes']	['Start']		= 12;
		$arrDefine['HoursMinutes']	['Length']		= 4;
		$arrDefine['HoursMinutes']	['Type']		= 'Time::HHII';
		
		$arrDefine['Underscore']	['Start']		= 16;
		$arrDefine['Underscore']	['Length']		= 1;
		$arrDefine['Underscore']	['Value']		= "_";
		
		$arrDefine['Date']			['Start']		= 17;
		$arrDefine['Date']			['Length']		= 8;
		$arrDefine['Date']			['Type']		= 'Date::YYYYMMDD';
		
		$arrDefine['Extension']		['Start']		= 25;
		$arrDefine['Extension']		['Length']		= 4;
		$arrDefine['Extension']		['Value']		= ".txt";
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['HeaderContent']	['Start']		= 0;
		$arrDefine['HeaderContent']	['Length']		= -1;
		$arrDefine['HeaderContent']	['Type']		= 'String';
		$arrDefine['HeaderContent']	['Config']		= 'ContentGreeting';
		
		$this->_arrDefine['Header'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// FOOTER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['FooterContent']	['Start']		= 0;
		$arrDefine['FooterContent']	['Length']		= -1;
		$arrDefine['FooterContent']	['Type']		= 'String';
		$arrDefine['FooterContent']	['Config']		= 'ContentSignature';
		
		$this->_arrDefine['Footer'] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Preselection Reversal
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['FNN']			['Start']		= 0;
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
 		$arrRendered['FNN']			= $arrRequest['FNN'];
 		
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
 		
 		// Generate Footer
 		$this->_arrFooter	= Array();
 		$this->_arrFooter['**Type']			= 'Footer';
 		$this->_arrFooter['**Request']		= 'Footer';
 		
 		// Parent Export
 		return parent::Export();
 	}
 }
?>
