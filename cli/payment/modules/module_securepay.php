<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_securepay
//----------------------------------------------------------------------------//
/**
 * module_securepay
 *
 * Normalises a SecurePay Payment record
 *
 * Normalises a SecurePay Payment record
 *
 * @file		module_securepay.php
 * @language	PHP
 * @package		Payment_application
 * @author		Rich Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */



//----------------------------------------------------------------------------//
// PaymentModuleSecurePay
//----------------------------------------------------------------------------//
/**
 * PaymentModuleSecurePay
 *
 * Payment Module for SecurePay Transactions
 *
 * Payment Module for SecurePay Transactions
 *
 *
 * @prefix		pay
 *
 * @package		Payment_application
 * @class		PaymentModuleSecurePay
 */
 class PaymentModuleSecurePay extends PaymentModule
 {
	public $intBaseCarrier	= CARRIER_PAYMENT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD;
	
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
 		// Define file format
 		$this->_strDelimiter	= "|";
 		
 		$arrDefine['ReferenceNo']				['Index']	= 0;
 		$arrDefine['DatePaid']					['Index']	= 1;
 		$arrDefine['TimePaid']					['Index']	= 2;
 		$arrDefine['TransactionType']			['Index']	= 3;
 		$arrDefine['ReturnsTransactionSource']	['Index']	= 4;
 		$arrDefine['AmountCents']				['Index']	= 5;
 		$arrDefine['BankTransactionId']			['Index']	= 6;
 		$arrDefine['ResponseCode']				['Index']	= 7;
 		$arrDefine['CCNo']						['Index']	= 8;
 		$arrDefine['SettlementDate']			['Index']	= 9;

		$arrDefine['Amount']		['Validate'] = "/^\d+$/";
		$arrDefine['ReferenceNo']	['Validate'] = "/^\d+$/";
		$arrDefine['DatePaid']		['Validate'] = "/^\d{8}$/";
		
		$this->_arrDefine = $arrDefine;
 	}

	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises a Payment record
	 *
	 * Normalises a Payment record
	 *
	 * @param		string	$strPaymentRecord	String containing raw record
	 * 
	 * @return		array
	 *
	 * @method
	 */
 	function Normalise($strPaymentRecord)
 	{ 		
 		// Check if this is a footer record...
 		if (!is_numeric(substr($strPaymentRecord, 0, 1)) || !trim($strPaymentRecord))
 		{
 			return PAYMENT_CANT_NORMALISE_FOOTER;
 		}
 		
 		// Split the parsed record
 		$this->_SplitRaw($strPaymentRecord);
 		
 		// PaymentType
 		$this->_Append('PaymentType', PAYMENT_TYPE_SECUREPAY);
 		
 		// Amount
 		$mixValue	= ((float)$this->_FetchRaw('AmountCents')) / 100;
 		$this->_Append('Amount', $mixValue);
 		
 		// Transaction Reference Number
 		$mixValue	= $this->_FetchRaw('ReferenceNo');
 		$this->_Append('TXNReference', $mixValue);
 		
 		// Transaction Type
		$this->_Append('OriginId', $this->_FetchRaw('CCNo'));
 		switch ((int)$this->_FetchRaw('TransactionType'))
 		{
 			case 0:			// Credit Card
		 		$this->_Append('OriginType', PAYMENT_TYPE_CREDIT_CARD);
 				break;
 			
 			case 2:			// Batch Payment
 				break;
 			
 			case 15:		// Direct Debit Bank Transfer
 				break;
 			
 			case 16:		// Direct Debit Reject
 				break;
 				
 			case 20:		// IVR (Credit Card)
		 		$this->_Append('OriginType', PAYMENT_TYPE_CREDIT_CARD);
 				break;
 		}
 		
 		// PaidOn
 		$mixValue	= $this->_ConvertDate($this->_FetchRaw('DatePaid'));
 		$this->_Append('PaidOn', $mixValue);
 		
 		// Call the base module's Normalise() to do general tasks
 		parent::Normalise($strPaymentRecord);
 		
 		// Apply AccountGroup Ownership
 		$strReference		= $this->_FetchRaw('ReferenceNo');
 		if (strlen(trim($strReference)) == 10)
 		{
 			$intAccount		= (int)$strReference;
 		}
 		else
 		{
 			$intAccount		= (int)substr($strReference, 6);
 		}
 		$this->_Append('Account', $intAccount);
 		if (($intAccountGroup = $this->_FindAccountGroup($intAccount)) === FALSE)
 		{
			$this->_Append('Status', PAYMENT_BAD_OWNER);
 			return $this->_Output();
 		}
 		$this->_Append('AccountGroup', $intAccountGroup);
 		
 		//----------------------------------------------------------------------
 		
 		// Validate Normalised Data
 		if (!$this->Validate())
 		{
 			return PAYMENT_CANT_NORMALISE_INVALID;
 		}
 		
 		return $this->_Output();
 	}
 	
	//------------------------------------------------------------------------//
	// _ConvertDate
	//------------------------------------------------------------------------//
	/**
	 * _ConvertDate()
	 *
	 * Converts from BPay date format to our own 
	 *
	 * Converts from "YYYYMMDD" date format to "YYYY-MM-DD".  Assumes that dates are in the 21st Century
	 *
	 * @param		string	$strPaymentRecord	String containing raw record
	 * 
	 * @return		string
	 *
	 * @method
	 */
 	function _ConvertDate($strDate)
 	{
		$strYear		= substr($strDate, 0, 4);
		$strMonth		= substr($strDate, 4, 2);
		$strDay			= substr($strDate, 6, 2);
		$strValidDate	= $strYear."-".$strMonth."-".$strDay; 
		return $strValidDate;
 	}
 }
?>
