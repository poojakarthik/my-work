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
	
	public static $intBaseCarrier	= CARRIER_OPTUS;
	public static $intBaseFileType	= FILE_EXPORT_OPTUS_PRESELECTION_REVERSAL;
	public static $_strDeliveryType	= 'Email';
	
	
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
 		$this->_arrModuleConfig['ContentGreeting']	['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['ContentGreeting']	['Description']	= "Text to preceed the Request content";
 		
 		$this->_arrModuleConfig['ContentSignature']	['Default']		= '';
 		$this->_arrModuleConfig['ContentSignature']	['Type']		= DATA_TYPE_INTEGER;
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
 		$this->_strOutput		= 'CSV';
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['HeaderContent']	['Start']		= 0;
		$arrDefine['HeaderContent']	['Length']		= 2;
		$arrDefine['HeaderContent']	['Type']		= 'String';
		$arrDefine['HeaderContent']	['Config']		= 'ContentGreeting';
		
		$this->_arrDefine['Header'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// FOOTER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['FooterContent']	['Start']		= 0;
		$arrDefine['FooterContent']	['Length']		= 2;
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
 		// Parent Export
 		parent::Export();
 	}
 }
?>
