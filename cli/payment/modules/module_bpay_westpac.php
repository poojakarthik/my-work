<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_bpay
//----------------------------------------------------------------------------//
/**
 * module_bpay
 *
 * Normalises a BPAY Payment record
 *
 * Normalises a BPAY Payment record
 *
 * @file		module_bpay.php
 * @language	PHP
 * @package		Payment_application
 * @author		Rich Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
 
//----------------------------------------------------------------------------//
// PaymentModuleBPayWestpac
//----------------------------------------------------------------------------//
/**
 * PaymentModuleBPayWestpac
 *
 * Payment Module for BPay Transactions through Westpac
 *
 * Payment Module for BPay Transactions through Westpac
 *
 *
 * @prefix		pay
 *
 * @package		payment
 * @class		PaymentModuleBPayWestpac
 */
 class PaymentModuleBPayWestpac extends PaymentModule
 {
	public $intBaseCarrier	= CARRIER_PAYMENT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_STANDARD;
	
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
 		// Define file format
 		$this->_strDelimiter	= ",";
 		$this->_strEnclosedBy	= '"';
 		
 		$arrDefine['Amount']					['Index']	= 0;
 		$arrDefine['CustomerReference']			['Index']	= 1;
 		$arrDefine['Date']						['Index']	= 2;
 		$arrDefine['FileId']					['Index']	= 3;
 		$arrDefine['OriginatingSystem']			['Index']	= 4;
 		$arrDefine['ReceiptNumber']				['Index']	= 5;
 		$arrDefine['ServiceID']					['Index']	= 6;
 		$arrDefine['ServiceName']				['Index']	= 7;
 		$arrDefine['TransactionCode']			['Index']	= 8;
		
		//$arrDefine['Amount']			['Validate'] = "/^\$\d+\.\d{2}$/";
		//$arrDefine['CustomerReference']	['Validate'] = "/^\d+$/";			// FIXME: Find out how customer ref #s are generated
		//$arrDefine['RemittanceDate']	['Validate'] = "/^\d{1,2}\/\d{1,2}\/\d{2}$/";
		
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
 		if (!trim($strPaymentRecord))
 		{
 			return PAYMENT_CANT_NORMALISE_FOOTER;
 		}
 		
 		if (is_int(stripos($strPaymentRecord, 'Amount,Client')))
 		{
 			return PAYMENT_CANT_NORMALISE_HEADER;
 		}
 		
 		//Debug($strRawRecord);
 		
 		// Split the parsed record
 		$this->_SplitRaw($strPaymentRecord);
		
 		// PaymentType
 		$this->_Append('PaymentType', PAYMENT_TYPE_BPAY);
 		
 		// Amount
 		$mixValue	= (float)$this->_FetchRaw('Amount');
 		$this->_Append('Amount', $mixValue);
 		
 		// Transaction Reference Number
 		$mixValue	= $this->_FetchRaw('ReceiptNumber');
 		$this->_Append('TXNReference', $mixValue);
 		
 		// PaidOn
 		$mixValue	= $this->_ConvertDate($this->_FetchRaw('Date'));
 		$this->_Append('PaidOn', $mixValue);
 		
 		// Call the base module's Normalise() to do general tasks
 		parent::Normalise($strPaymentRecord);
 		
 		// Apply AccountGroup Ownership
 		$strAccount			= trim($this->_FetchRaw('CustomerReference'));
 		// Remove Check digit
 		$intAccount			= (int)substr($strAccount, 0, -1);
 		// FIXME: Try to account for bad reference numbers.  Is this right?
 		if ($intAccount < 1000000000)
 		{
 			$intAccount = (int)"1".str_pad($intAccount, 9, "0", STR_PAD_LEFT);
 		}
 		$this->_Append('Account', $intAccount);
 		if (($intAccountGroup = $this->_FindAccountGroup($intAccount)) === FALSE)
 		{
			$this->_Append('Status', PAYMENT_BAD_OWNER);
 			return $this->_Output();
 		}
 		$this->_Append('AccountGroup', $intAccountGroup);
 		
 		// Validate Check Digit
 		if (MakeLuhn($intAccount) != (int)substr($strAccount, -1, 1))
 		{
			$this->_Append('Status', PAYMENT_INVALID_CHECK_DIGIT);
 			return $this->_Output();
 		}
 		
 		//----------------------------------------------------------------------
 		
 		// Validate Normalised Data
 		if (!$this->Validate())
 		{
			$this->_Append('Status', PAYMENT_CANT_NORMALISE_INVALID);
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
	 * Converts from "DDMMYYYY" date format to "YYYY-MM-DD".  Assumes that dates are in the 21st Century
	 *
	 * @param		string	$strPaymentRecord	String containing raw record
	 * 
	 * @return		string
	 *
	 * @method
	 */
 	function _ConvertDate($strDate)
 	{
		$strDate		= str_pad(trim($strDate), 8, '0', STR_PAD_LEFT);
		$strValidDate	= substr($strDate, -4)."-".substr($strDate, 2, 2)."-".substr($strDate, 0, 2);
		return $strValidDate;
 	}
 }
?>
