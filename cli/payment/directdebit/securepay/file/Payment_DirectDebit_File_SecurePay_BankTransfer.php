<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//
 
//----------------------------------------------------------------------------//
// Payment_DirectDebit_File_SecurePay_BankTransfer
//----------------------------------------------------------------------------//
/**
 * Payment_DirectDebit_File_SecurePay_BankTransfer
 *
 * Processes SecurePay Bank Transfer Requests
 *
 * Processes SecurePay Bank Transfer Requests
 *
 * @file		Payment_DirectDebit_File_SecurePay_BankTransfer.php
 * @language	PHP
 * @package		cli.payment.directdebit
 * @author		Rich "Waste" Davis
 * @version		8.08
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// Payment_DirectDebit_File_SecurePay_BankTransfer
//----------------------------------------------------------------------------//
/**
 * Payment_DirectDebit_File_SecurePay_BankTransfer
 *
 * Processes SecurePay Bank Transfer Requests
 *
 * Processes SecurePay Bank Transfer Requests
 *
 * @prefix		exp
 *
 * @package		cli.payment.directdebit
 * @class		Payment_DirectDebit_File_SecurePay_BankTransfer
 */
 class Payment_DirectDebit_File_SecurePay_BankTransfer extends Payment_DirectDebit_File
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
	
	public $intBaseCarrier		= CARRIER_SECUREPAY;
	public $intBaseFileType		= RESOURCE_TYPE_FILE_EXPORT_SECUREPAY_BANK_TRANSFER_FILE;
	public $_strDeliveryType	= 'EmailAttach';
	
	public	$strDescription;
	public	$intBillingType		= BILLING_TYPE_DIRECT_DEBIT;
	
	
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
 	function __construct($intCarrier, $intCustomerGroup)
 	{
 		// Parent Constructor
 		parent::__construct($intCarrier, $intCustomerGroup);
 		
 		// Carrier Reference / Line Number Init
 		$this->intCarrierReference	= 0;
 		
 		// Module Description
 		$this->strDescription		= "Bank Transfer (File)";
 		
		//##----------------------------------------------------------------##//
		// Define Module Configuration and Defaults
		//##----------------------------------------------------------------##//
		
		// Mandatory
 		$this->_arrModuleConfig['Destination']		['Default']		= '';
 		$this->_arrModuleConfig['Destination']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Destination']		['Description']	= "Destination Email Address";
 		
 		$this->_arrModuleConfig['Subject']			['Default']		= '<Property::CustomerGroup> Direct Debit (Bank Transfer) Report for <Function::Date>';
 		$this->_arrModuleConfig['Subject']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['Subject']			['Description']	= "Email Subject";
 		
 		$this->_arrModuleConfig['ReplyTo']			['Default']		= '';
 		$this->_arrModuleConfig['ReplyTo']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['ReplyTo']			['Description']	= "Reply-To Email Address";
 		
 		$this->_arrModuleConfig['EmailContent']		['Default']		= "<Addressee>,\n\n Please find the <Property::CustomerGroup> Direct Debit (Bank Transfer) Report for <Function::Date> attached to this email.  Please reply to this email if you have any issues.\n\nYellow Billing Services ";
 		$this->_arrModuleConfig['EmailContent']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['EmailContent']		['Description']	= "Content for the Email";
		
		// Additional
 		$this->_arrModuleConfig['CarbonCopy']		['Default']		= '';
 		$this->_arrModuleConfig['CarbonCopy']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['CarbonCopy']		['Description']	= "Additional Addresses to CC to";
 		
 		$this->_arrModuleConfig['FileNamePrefix']	['Default']		= '';
 		$this->_arrModuleConfig['FileNamePrefix']	['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['FileNamePrefix']	['Description']	= "3-Character CustomerGroup Prefix for the FileName (eg. SAE, VOI)";
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'TXT';
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['Prefix']		['Start']		= 0;
		$arrDefine['Prefix']		['Length']		= 3;
		$arrDefine['Prefix']		['Config']		= 'FileNamePrefix';
		
		$arrDefine['Suffix']		['Start']		= 3;
		$arrDefine['Suffix']		['Length']		= 2;
		$arrDefine['Suffix']		['Value']		= '00';
		
		$arrDefine['Extension']		['Start']		= 5;
		$arrDefine['Extension']		['Length']		= 4;
		$arrDefine['Extension']		['Value']		= ".txt";
		
		$this->_arrDefine['Filename'] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Bank Transfer
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['BSB']			['Index']		= 0;
		$arrDefine['BSB']			['Type']		= 'Integer';
		$arrDefine['BSB']			['Length']		= 6;
		$arrDefine['BSB']			['PadChar']		= '0';
		
		$arrDefine['BankAccount']	['Index']		= 1;
		$arrDefine['BankAccount']	['Type']		= 'Integer';
		
		$arrDefine['AccountName']	['Index']		= 2;
		$arrDefine['AccountName']	['Type']		= 'String';
		
		$arrDefine['AmountCharged']	['Index']		= 3;
		$arrDefine['AmountCharged']	['Type']		= 'Integer';
		
		$arrDefine['FlexAccount']	['Index']		= 4;
		$arrDefine['FlexAccount']	['Type']		= 'Integer';
		
		$arrDefine['CustomerName']	['Index']		= 5;
		$arrDefine['CustomerName']	['Type']		= 'String';
		
		$this->_arrDefine[BILLING_TYPE_DIRECT_DEBIT] = $arrDefine;
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
	 * @return	boolean					Success/Failure
	 *
	 * @method
	 */
 	function Output($arrRequest)
 	{
 		$this->intCarrierReference++;
 		
 		// Get Account Details
 		$arrAccountDetails	= $this->_GetAccountDetails($arrRequest['Account']);
 		if (!$arrAccountDetails || !$arrAccountDetails['BankDetails'])
 		{
 			return Array('Success' => FALSE, 'Description' => "Unable to retrieve Account Details");
 		}
 		
 		//--------------------------------------------------------------------//
 		// RENDER
 		//--------------------------------------------------------------------//
 		$arrRendered	= Array();
 		$arrRendered['BSB']				= (int)$arrAccountDetails['BankDetails']['BSB'];
 		$arrRendered['BankAccount']		= (int)$arrAccountDetails['BankDetails']['AccountNumber'];
 		$arrRendered['AccountName']		= substr(preg_replace("/\W+/misU", '_', trim($arrAccountDetails['BankDetails']['AccountName'])), 0, 32);
 		$arrRendered['AmountCharged']	= ceil($arrRequest['Charge'] * 100);
 		$arrRendered['FlexAccount']		= $arrRequest['Account'];
 		$arrRendered['CustomerName']	= substr(preg_replace("/\W+/misU", '_', trim($arrRequest['BusinessName'])), 0, 32);
 		
 		$arrRendered['**Type']		= BILLING_TYPE_DIRECT_DEBIT;
 		$this->_arrFileContent[]	= $arrRendered;
 		
 		// Return the modified Request
 		return Array('Success' => TRUE);
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
 		
 		// Parent Export
 		return parent::Export();
 	}
 }
?>
