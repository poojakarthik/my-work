<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// export_preselect
//----------------------------------------------------------------------------//
/**
 * export_preselect
 *
 * Exports Optus Preselection File Requests
 *
 * Exports Optus Preselection File Requests
 *
 * @file		export_preselect.php
 * @language	PHP
 * @package		provisioning
 * @author		Rich "Waste" Davis
 * @version		8.04
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ExportOptusPreselection
//----------------------------------------------------------------------------//
/**
 * ExportOptusPreselection
 *
 * Exports Optus Preselection File Requests
 *
 * Exports Optus Preselection File Requests
 *
 * @prefix		exp
 *
 * @package		provisioning
 * @class		ExportOptusPreselection
 */
 class ExportOptusPreselection extends ExportBase
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
	public $intBaseFileType		= RESOURCE_TYPE_FILE_EXPORT__PROVISIONING_OPTUS_PRESELECTION;
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
 		$this->strDescription		= "Preselection";
		
 		// Get Fields which are going to be modified
 		$this->intFileSequence		= &$this->GetConfigField('FileSequence');
 		$this->intFileSequence++;
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Destination']		['Default']		= 'long.distance.spsg@optus.com.au';
 		$this->_arrModuleConfig['Destination']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Destination']		['Description']	= "Destination Email Address";
 		
 		$this->_arrModuleConfig['Subject']			['Default']		= 'LD Churn Request File for <Function::DateTime> for <Config::OptusAccount>';
 		$this->_arrModuleConfig['Subject']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Subject']			['Description']	= "Email Subject";
 		
 		$this->_arrModuleConfig['ReplyTo']			['Default']		= 'provisioning@yellowbilling.com.au';
 		$this->_arrModuleConfig['ReplyTo']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ReplyTo']			['Description']	= "Reply-To Email Address";
 		
 		$this->_arrModuleConfig['EmailContent']		['Default']		= 'LD Churn Request File for <Function::DateTime> for <Config::OptusAccount>';
 		$this->_arrModuleConfig['EmailContent']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['EmailContent']		['Description']	= "Content for the Email";
 		
 		// Additional
 		$this->_arrModuleConfig['FileSequence']		['Default']		= 0;
 		$this->_arrModuleConfig['FileSequence']		['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['FileSequence']		['Description']	= "File Sequence/Batch Number";
 		$this->_arrModuleConfig['FileSequence']		['AutoUpdate']	= TRUE;
 		
 		$this->_arrModuleConfig['CarbonCopy']		['Default']		= '';
 		$this->_arrModuleConfig['CarbonCopy']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['CarbonCopy']		['Description']	= "Additional Addresses to CC to";
 		
 		$this->_arrModuleConfig['OptusAccount']		['Default']		= '';
 		$this->_arrModuleConfig['OptusAccount']		['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['OptusAccount']		['Description']	= "The CSP's Optus Billing Account Number";
 		
 		$this->_arrModuleConfig['SPName']			['Default']		= '';
 		$this->_arrModuleConfig['SPName']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['SPName']			['Description']	= "The CSP's Company Name";
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'XLS';
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
		
		$arrDefine['HoursMinutes']	['Start']		= 0;
		$arrDefine['HoursMinutes']	['Length']		= 4;
		$arrDefine['HoursMinutes']	['Type']		= 'Time::HHII';
		
		$arrDefine['Underscore']	['Start']		= 4;
		$arrDefine['Underscore']	['Length']		= 1;
		$arrDefine['Underscore']	['Value']		= "_";
		
		$arrDefine['Date']			['Start']		= 5;
		$arrDefine['Date']			['Length']		= 10;
		$arrDefine['Date']			['Type']		= 'Date::YYYY-MM-DD';
		
		$arrDefine['Underscore2']	['Start']		= 15;
		$arrDefine['Underscore2']	['Length']		= 1;
		$arrDefine['Underscore2']	['Value']		= "_";
		
		$arrDefine['BatchNo']		['Start']		= 16;
		$arrDefine['BatchNo']		['Length']		= strlen($this->intFileSequence);
		$arrDefine['BatchNo']		['Value']		= $this->intFileSequence;
		
		$arrDefine['Extension']		['Start']		= 16 + strlen($this->intFileSequence);
		$arrDefine['Extension']		['Length']		= 4;
		$arrDefine['Extension']		['Value']		= ".xls";
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['BatchNo']		['Index']		= 0;
		$arrDefine['BatchNo']		['Type']		= 'String';
		$arrDefine['BatchNo']		['Value']		= 'Batch No';
		
		$arrDefine['IDNo']			['Index']		= 1;
		$arrDefine['IDNo']			['Type']		= 'String';
		$arrDefine['IDNo']			['Value']		= 'ID No';
		
		$arrDefine['SPName']		['Index']		= 2;
		$arrDefine['SPName']		['Type']		= 'String';
		$arrDefine['SPName']		['Value']		= 'SP Name';
		
		$arrDefine['OptusAccount']	['Index']		= 3;
		$arrDefine['OptusAccount']	['Type']		= 'String';
		$arrDefine['OptusAccount']	['Value']		= 'SP CASS A/C No';
		
		$arrDefine['FNN']			['Index']		= 4;
		$arrDefine['FNN']			['Type']		= 'String';
		$arrDefine['FNN']			['Value']		= 'Service No with area code';
		
		$arrDefine['CADate']		['Index']		= 5;
		$arrDefine['CADate']		['Type']		= 'String';
		$arrDefine['CADate']		['Value']		= 'CA Date dd/mm/yyy';
		
		$arrDefine['CARequired']	['Index']		= 6;
		$arrDefine['CARequired']	['Type']		= 'String';
		$arrDefine['CARequired']	['Value']		= 'CA Required';
		
		$arrDefine['Lessee']		['Index']		= 7;
		$arrDefine['Lessee']		['Type']		= 'String';
		$arrDefine['Lessee']		['Value']		= 'Lessee Yes/No';
		
		$this->_arrDefine['Header'] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Bar
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['BatchNo']		['Index']		= 0;
		$arrDefine['BatchNo']		['Type']		= 'Integer';
		$arrDefine['BatchNo']		['Value']		= $this->intFileSequence;
		
		$arrDefine['IDNo']			['Index']		= 1;
		$arrDefine['IDNo']			['Type']		= 'Integer';
		$arrDefine['IDNo']			['Value']		= 12;
		
		$arrDefine['SPName']		['Index']		= 2;
		$arrDefine['SPName']		['Type']		= 'String';
		$arrDefine['SPName']		['Config']		= 'SPName';
		
		$arrDefine['OptusAccount']	['Index']		= 3;
		$arrDefine['OptusAccount']	['Type']		= 'Integer';
		$arrDefine['OptusAccount']	['Config']		= 'OptusAccount';
		
		$arrDefine['FNN']			['Index']		= 4;
		$arrDefine['FNN']			['Type']		= 'FNN';
		
		$arrDefine['CADate']		['Index']		= 5;
		$arrDefine['CADate']		['Type']		= 'Date::DD/MM/YYYY';
		
		$arrDefine['CARequired']	['Index']		= 6;
		$arrDefine['CARequired']	['Type']		= 'String';
		$arrDefine['CARequired']	['Value']		= 'n';
		
		$arrDefine['Lessee']		['Index']		= 7;
		$arrDefine['Lessee']		['Type']		= 'String';
		$arrDefine['Lessee']		['Value']		= 'n';
 		
		$this->_arrDefine[PROVISIONING_TYPE_PRESELECTION] = $arrDefine;
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
 		$arrRendered['CADate']		= date("d/m/Y", strtotime($arrRequest['AuthorisationDate']));
 		
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
 		$this->_arrFilename['Date']			= date("Y-m-d");
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']			= 'Header';
 		$this->_arrHeader['**Request']		= 'Header';
 		
 		// Parent Export
 		return parent::Export();
 	}
 }
?>
