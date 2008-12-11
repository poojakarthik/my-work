<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//
/**
 * Payment_DirectDebit_File_AustralianDirectEntry_BankTransfer
 *
 * Processes SecurePay Bank Transfer Requests
 *
 * @file		Payment_DirectDebit_File_AustralianDirectEntry_BankTransfer.php
 * @language	PHP
 * @package		cli.payment.directdebit
 * @author		Rich "Waste" Davis
 * @version		8.11
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 * @class		Payment_DirectDebit_File_AustralianDirectEntry_BankTransfer
 */
 class Payment_DirectDebit_File_AustralianDirectEntry_BankTransfer extends Payment_DirectDebit_File
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
	
	public $intBaseCarrier		= CARRIER_WESTPAC;
	public $intBaseFileType		= RESOURCE_TYPE_FILE_EXPORT_DIRECT_DEBIT_AUSTRALIAN_DIRECT_ENTRY_FILE;
	public $_strDeliveryType	= 'EmailAttach';
	
	public	$strDescription;
	public	$intBillingType		= BILLING_TYPE_DIRECT_DEBIT;
	
	
	/**
	 * __construct()
	 *
	 * Constructor
	 * 
	 * @param	integer	$intCarrier				The Carrier using this Module
	 * @param	integer	$intCustomerGroup		The Customer Group using this Module
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
 		
 		$this->_arrModuleConfig['EmailContent']		['Default']		= "<Addressee>,\n\nPlease find the <Property::CustomerGroup> Direct Debit (Bank Transfer) Report for <Function::Date> attached to this email.  Please reply to this email if you have any issues.\n\nYellow Billing Services ";
 		$this->_arrModuleConfig['EmailContent']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['EmailContent']		['Description']	= "Content for the Email";
		
		// Additional
 		$this->_arrModuleConfig['CarbonCopy']			['Default']		= '';
 		$this->_arrModuleConfig['CarbonCopy']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['CarbonCopy']			['Description']	= "Additional Addresses to CC to";
 		
 		$this->_arrModuleConfig['BankAbbreviation']		['Default']		= '';
 		$this->_arrModuleConfig['BankAbbreviation']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['BankAbbreviation']		['Description']	= "3-Character Approved Financial Institution Abbreviation (eg. WBC for Westpac)";
 		
 		$this->_arrModuleConfig['SupplierUserName']		['Default']		= '';
 		$this->_arrModuleConfig['SupplierUserName']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['SupplierUserName']		['Description']	= "User Name (as per User Preferred Specification)";
 		
 		$this->_arrModuleConfig['SupplierUserNumber']	['Default']		= '';
 		$this->_arrModuleConfig['SupplierUserNumber']	['Type']		= DATA_TYPE_INTEGER;
 		$this->_arrModuleConfig['SupplierUserNumber']	['Description']	= "6-Digit User Idenitification Number allocated by the Australian Payments Clearing Association (APCA)";
 		
 		$this->_arrModuleConfig['FileDescription']		['Default']		= 'DDBANK';
 		$this->_arrModuleConfig['FileDescription']		['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['FileDescription']		['Description']	= "File Description (eg. 'DDBANK'), limited to 12-characters";
 		
 		$this->_arrModuleConfig['TraceBSB']				['Default']		= '';
 		$this->_arrModuleConfig['TraceBSB']				['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['TraceBSB']				['Description']	= "The BSB for the Account number to trace back to on payment rejection (XXX-XXX)";
 		
 		$this->_arrModuleConfig['TraceAccount']			['Default']		= '';
 		$this->_arrModuleConfig['TraceAccount']			['Type']		= DATA_TYPE_STRING;
 		$this->_arrModuleConfig['TraceAccount']			['Description']	= "The Account number to trace back to on payment rejection";
		
		//##----------------------------------------------------------------##//
		// Define File Format
		//##----------------------------------------------------------------##//
 		
 		// Delimiter & New Line
 		$this->_strFileFormat	= 'TXT';
 		$this->_strDelimiter	= '';
 		
 		$this->_arrDefine		= Array();
 		
 		//--------------------------------------------------------------------//
 		// FILENAME
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
		$arrDefine['UPSName']		['Index']		= 0;
		$arrDefine['UPSName']		['Config']		= 'SupplierUserName';
		
		$arrDefine['Underscore1']	['Index']		= 1;
		$arrDefine['Underscore1']	['Value']		= '_';
		
		$arrDefine['FileType']		['Index']		= 2;
		$arrDefine['FileType']		['Config']		= 'FileDescription';
		
		$arrDefine['Underscore1']	['Index']		= 3;
		$arrDefine['Underscore1']	['Value']		= '_';
		
		$arrDefine['Date']			['Index']		= 4;
		
		$arrDefine['Extension']		['Index']		= 5;
		$arrDefine['Extension']		['Value']		= '.txt';
		
		$this->_arrDefine['Filename'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// HEADER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']			['Start']		= 0;
		$arrDefine['RecordType']			['Length']		= 1;
		$arrDefine['RecordType']			['Type']		= 'Integer';
		$arrDefine['RecordType']			['Value']		= '0';
		
		$arrDefine['Blank']					['Start']		= 1;
		$arrDefine['Blank']					['Length']		= 17;
		
		$arrDefine['ReelSequence']			['Start']		= 18;
		$arrDefine['ReelSequence']			['Length']		= 2;
		$arrDefine['ReelSequence']			['Value']		= '01';
		$arrDefine['ReelSequence']			['PadChar']		= '0';
		$arrDefine['ReelSequence']			['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['BankAbbreviation']		['Start']		= 20;
		$arrDefine['BankAbbreviation']		['Length']		= 3;
		$arrDefine['BankAbbreviation']		['Config']		= 'BankAbbreviation';
		
		$arrDefine['Blank2']				['Start']		= 23;
		$arrDefine['Blank2']				['Length']		= 7;
		
		$arrDefine['SupplierUserName']		['Start']		= 30;
		$arrDefine['SupplierUserName']		['Length']		= 26;
		$arrDefine['SupplierUserName']		['Config']		= 'SupplierUserName';
		$arrDefine['SupplierUserName']		['PadChar']		= ' ';
		$arrDefine['SupplierUserName']		['PadType']		= STR_PAD_RIGHT;
		
		$arrDefine['SupplierUserNumber']	['Start']		= 56;
		$arrDefine['SupplierUserNumber']	['Length']		= 6;
		$arrDefine['SupplierUserNumber']	['Config']		= 'SupplierUserNumber';
		$arrDefine['SupplierUserNumber']	['Type']		= 'Integer';
		$arrDefine['SupplierUserNumber']	['PadChar']		= '0';
		$arrDefine['SupplierUserNumber']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['FileDescription']		['Start']		= 62;
		$arrDefine['FileDescription']		['Length']		= 12;
		$arrDefine['FileDescription']		['Config']		= 'FileDescription';
		$arrDefine['FileDescription']		['PadChar']		= ' ';
		$arrDefine['FileDescription']		['PadType']		= STR_PAD_RIGHT;
		
		$arrDefine['TransactionDate']		['Start']		= 74;
		$arrDefine['TransactionDate']		['Length']		= 6;
		
		$arrDefine['Blank3']				['Start']		= 80;
		$arrDefine['Blank3']				['Length']		= 40;
		
		$this->_arrDefine['Header'] = $arrDefine;
 		
 		//--------------------------------------------------------------------//
 		// FOOTER
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']		['Start']		= 0;
		$arrDefine['RecordType']		['Length']		= 1;
		$arrDefine['RecordType']		['Type']		= 'Integer';
		$arrDefine['RecordType']		['Value']		= '7';
		
		$arrDefine['BSBFormatFilter']	['Start']		= 1;
		$arrDefine['BSBFormatFilter']	['Length']		= 7;
		$arrDefine['BSBFormatFilter']	['Value']		= '999-999';
		
		$arrDefine['Blank']				['Start']		= 8;
		$arrDefine['Blank']				['Length']		= 12;
		
		$arrDefine['NetTotalCents']		['Start']		= 20;
		$arrDefine['NetTotalCents']		['Length']		= 10;
		$arrDefine['NetTotalCents']		['Type']		= 'Integer';
		$arrDefine['NetTotalCents']		['PadChar']		= '0';
		$arrDefine['NetTotalCents']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['CreditTotalCents']	['Start']		= 30;
		$arrDefine['CreditTotalCents']	['Length']		= 10;
		$arrDefine['CreditTotalCents']	['Type']		= 'Integer';
		$arrDefine['CreditTotalCents']	['PadChar']		= '0';
		$arrDefine['CreditTotalCents']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['DebitTotalCents']	['Start']		= 40;
		$arrDefine['DebitTotalCents']	['Length']		= 10;
		$arrDefine['DebitTotalCents']	['Type']		= 'Integer';
		$arrDefine['DebitTotalCents']	['PadChar']		= '0';
		$arrDefine['DebitTotalCents']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['Blank2']			['Start']		= 50;
		$arrDefine['Blank2']			['Length']		= 24;
		
 		$arrDefine['RecordCount']		['Start']		= 74;
		$arrDefine['RecordCount']		['Length']		= 6;
		$arrDefine['RecordCount']		['Type']		= 'Integer';
		$arrDefine['RecordCount']		['PadChar']		= '0';
		$arrDefine['RecordCount']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['Blank3']			['Start']		= 80;
		$arrDefine['Blank3']			['Length']		= 40;
		
		$this->_arrDefine['Footer'] = $arrDefine;
		
 		//--------------------------------------------------------------------//
 		// Bank Transfer
 		//--------------------------------------------------------------------//
 		
 		$arrDefine = Array();
 		$arrDefine['RecordType']		['Start']		= 0;
		$arrDefine['RecordType']		['Length']		= 1;
		$arrDefine['RecordType']		['Type']		= 'Integer';
		$arrDefine['RecordType']		['Value']		= '1';
		
 		$arrDefine['BSB']				['Start']		= 1;
		$arrDefine['BSB']				['Length']		= 7;
		
		$arrDefine['AccountNumber']		['Start']		= 8;
		$arrDefine['AccountNumber']		['Length']		= 9;
		
		$arrDefine['Indicator']			['Start']		= 17;
		$arrDefine['Indicator']			['Length']		= 1;
		$arrDefine['Indicator']			['Value']		= ' ';
		
		$arrDefine['TransactionCode']	['Start']		= 18;
		$arrDefine['TransactionCode']	['Length']		= 2;
		$arrDefine['TransactionCode']	['Value']		= '13';
		$arrDefine['TransactionCode']	['Type']		= 'Integer';
		$arrDefine['TransactionCode']	['PadChar']		= '0';
		$arrDefine['TransactionCode']	['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['AmountCents']		['Start']		= 20;
		$arrDefine['AmountCents']		['Length']		= 10;
		$arrDefine['AmountCents']		['Type']		= 'Integer';
		$arrDefine['AmountCents']		['PadChar']		= '0';
		$arrDefine['AmountCents']		['PadType']		= STR_PAD_LEFT;
		
		$arrDefine['AccountName']		['Start']		= 30;
		$arrDefine['AccountName']		['Length']		= 32;
		$arrDefine['AccountName']		['PadChar']		= ' ';
		$arrDefine['AccountName']		['PadType']		= STR_PAD_RIGHT;
		
		$arrDefine['TranscationRef']	['Start']		= 62;
		$arrDefine['TranscationRef']	['Length']		= 18;
		$arrDefine['TranscationRef']	['PadChar']		= ' ';
		$arrDefine['TranscationRef']	['PadType']		= STR_PAD_RIGHT;
		
		$arrDefine['TraceBSB']			['Start']		= 80;
		$arrDefine['TraceBSB']			['Length']		= 7;
		$arrDefine['TraceBSB']			['Config']		= 'TraceBSB';
		
		$arrDefine['TraceAccount']		['Start']		= 62;
		$arrDefine['TraceAccount']		['Length']		= 18;
		$arrDefine['TraceAccount']		['PadChar']		= ' ';
		$arrDefine['TraceAccount']		['PadType']		= STR_PAD_RIGHT;
		$arrDefine['TraceAccount']		['Config']		= 'TraceAccount';
		
		$arrDefine['Remitter']			['Start']		= 62;
		$arrDefine['Remitter']			['Length']		= 18;
		$arrDefine['Remitter']			['Config']		= 'SupplierUserName';
		$arrDefine['Remitter']			['PadChar']		= ' ';
		$arrDefine['Remitter']			['PadType']		= STR_PAD_RIGHT;
		
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
 		if (!$arrAccountDetails || is_string($arrAccountDetails))
 		{
 			return Array('Success' => FALSE, 'Description' => "Unable to retrieve Account Details : ".$arrAccountDetails);
 		}
 		elseif (!$arrAccountDetails['DirectDebit'])
 		{
 			return Array('Success' => FALSE, 'Description' => "Unable to retrieve Bank Transfer Details : ".print_r($arrAccountDetails, TRUE));
 		}
 		
 		//--------------------------------------------------------------------//
 		// RENDER
 		//--------------------------------------------------------------------//
 		$arrRendered	= Array();
 		$strBSB							= str_pad((int)$arrAccountDetails['DirectDebit']['BSB'], 6, '0', STR_PAD_LEFT);
 		$arrRendered['BSB']				= substr($strBSB, 0, 3).'-'.substr($strBSB, -3);
		$arrRendered['AccountNumber']	= (int)$arrAccountDetails['DirectDebit']['AccountNumber'];
		$arrRendered['AmountCents']		= ceil($arrRequest['Charge'] * 100);
		$arrRendered['AccountName']		= substr(preg_replace("/[^\w\ ]+/misU", '', trim($arrAccountDetails['DirectDebit']['AccountName'])), 0, 32);
		$arrRendered['TranscationRef']	= $arrRequest['Account'].'_'.date("mY");
 		
 		$arrRendered['**Type']			= BILLING_TYPE_DIRECT_DEBIT;
 		$this->_arrFileContent[]		= $arrRendered;
		
		// Add to Totals
		$this->_fltDebitTotalCents		+= $arrRendered['AmountCents'];
 		
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
 		$this->_arrFilename['Date']			= date("Ymd");
 		
 		// Generate Header
 		$this->_arrHeader	= Array();
 		$this->_arrHeader['**Type']				= 'Header';
 		$this->_arrHeader['**Request']			= 'Header';
 		$this->_arrHeader['TransactionDate']	= date("dmy");
 		
 		// Generate Footer
 		$this->_arrFooter	= Array();
 		$this->_arrFooter['**Type']				= 'Footer';
 		$this->_arrFooter['**Request']			= 'Footer';
 		$this->_arrFooter['RecordCount']		= count($this->_arrFileContent);
 		$this->_arrFooter['CreditTotalCents']	= 0;
 		$this->_arrFooter['DebitTotalCents']	= $this->_fltDebitTotalCents;
 		$this->_arrFooter['NetTotalCents']		= $this->_fltDebitTotalCents;
 		
 		// Parent Export
 		return parent::Export();
 	}
 }
?>