<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_billexpress
//----------------------------------------------------------------------------//
/**
 * module_billexpress
 *
 * Normalises a BillExpress Payment record
 *
 * Normalises a BillExpress Payment record
 *
 * @file		module_billexpress.php
 * @language	PHP
 * @package		Payment_application
 * @author		Rich Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
 
//----------------------------------------------------------------------------//
// PaymentModuleBillExpress
//----------------------------------------------------------------------------//
/**
 * PaymentModuleBillExpress
 *
 * Payment Module for BillExpress Transactions
 *
 * Payment Module for BillExpress Transactions
 *
 *
 * @prefix		pay
 *
 * @package		Payment_application
 * @class		PaymentModuleBillExpress
 */
 class PaymentModuleBillExpress extends PaymentModule
 {
 	function __construct()
 	{
 		parent::__construct();
 		
 		// Define file format
 		$arrDefine['RecordType']	['Start']	= 0;
 		$arrDefine['RecordType']	['Length']	= 2;
 		$arrDefine['AccountNo']		['Start']	= 12;
 		$arrDefine['AccountNo']		['Length']	= 10;
 		$arrDefine['Date1']			['Start']	= 40;
 		$arrDefine['Date1']			['Length']	= 8;
 		$arrDefine['Amount']		['Start']	= 79;
 		$arrDefine['Amount']		['Length']	= 12;
 		$arrDefine['Date2']			['Start']	= 91;
 		$arrDefine['Date2']			['Length']	= 8;
 		$arrDefine['ReferenceNo']	['Start']	= 99;
 		$arrDefine['ReferenceNo']	['Length']	= 4;

		
		$arrDefine['Amount']		['Validate'] = "/^\d+$/";
		$arrDefine['AccountNo']		['Validate'] = "/^\d+$/";
		$arrDefine['Date1']			['Validate'] = "/^\d+$/";
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
 		// Split the parsed record
 		$this->_SplitRaw($strPaymentRecord);
 		
 		$strRecordType = $this->_FetchRaw('RecordType');
 		
 		// Check if this is a header or footer record...
 		if ($strRecordType == "00")
 		{
 			return PAYMENT_CANT_NORMALISE_HEADER;
 		}
 		elseif ($strRecordType == "99")
 		{
 			return PAYMENT_CANT_NORMALISE_FOOTER;
 		}
		
 		// Amount
 		$mixValue	= (float)$this->_FetchRaw('Amount');
 		$this->_Append('Amount', $mixValue);
 		
 		// Transaction Reference Number
 		$mixValue	= $this->_FetchRaw('ReferenceNo');
 		$this->_Append('TXNReference', $mixValue);
 		
 		// PaidOn
 		$mixValue	= _ConvertDate($this->_FetchRaw('Date1'));
 		$this->_Append('PaidOn', $mixValue);
 		
 		// Call the base module's Normalise() to do general tasks
 		parent::Normalise($strPaymentRecord);
 		
 		// Apply AccountGroup Ownership
 		$strAccount			= $this->_FetchRaw('AccountNo');
 		$intAccount			= (int)$strAccount;
 		$intAccountGroup	= $this->_FindAccountGroup($intAccount);
 		$this->_Append('AccountGroup', $intAccountGroup);
 		$this->_Append('Account', $intAccount);
 		
 		// PaymentType
 		$this->_Append('PaymentType', PAYMENT_TYPE_BILLEXPRESS);
 		
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
