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
 	function __construct()
 	{
 		parent::__construct();
 		
 		// Define file format
 		$this->_strDelimiter	= "|";
 		
 		$arrDefine['ReferenceNo']	['Index']	= 0;
 		$arrDefine['DatePaid']		['Index']	= 1;
 		$arrDefine['BSB']			['Index']	= 2;
 		$arrDefine['Number1']		['Index']	= 3;
 		$arrDefine['Number2']		['Index']	= 4;
 		$arrDefine['AmountCents']	['Index']	= 5;
 		$arrDefine['Number3']		['Index']	= 6;
 		$arrDefine['Number4']		['Index']	= 7;
 		$arrDefine['Number5']		['Index']	= 8;
 		$arrDefine['FileDate']		['Index']	= 9;

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
 		
 		// Amount
 		$mixValue	= ((float)$this->_FetchRaw('Amount')) / 100;
 		$this->_Append('Amount', $mixValue);
 		
 		// Transaction Reference Number
 		$mixValue	= $this->_FetchRaw('ReferenceNo');
 		$this->_Append('TXNReference', $mixValue);
 		
 		// PaidOn
 		$mixValue	= $this->_ConvertDate($this->_FetchRaw('DatePaid'));
 		$this->_Append('PaidOn', $mixValue);
 		
 		// Call the base module's Normalise() to do general tasks
 		parent::Normalise($strPaymentRecord);
 		
 		// Apply AccountGroup Ownership
 		$strAccount			= $this->_FetchRaw('ReferenceNo');
 		$intAccount			= (int)substr($strAccount, 6);
 		$intAccountGroup	= $this->_FindAccountGroup($intAccount);
 		$this->_Append('AccountGroup', $intAccountGroup);
 		$this->_Append('Account', $intAccount);
 		 		
 		// PaymentType
 		$this->_Append('PaymentType', PAYMENT_TYPE_SECUREPAY);
 		
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
