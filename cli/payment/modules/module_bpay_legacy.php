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
// PaymentModuleBPay
//----------------------------------------------------------------------------//
/**
 * PaymentModuleBPay
 *
 * Payment Module for BPay Transactions
 *
 * Payment Module for BPay Transactions
 *
 *
 * @prefix		pay
 *
 * @package		Payment_application
 * @class		PaymentModuleBPay
 */
 class PaymentModuleBPay extends PaymentModule
 {
	public $intBaseCarrier	= CARRIER_PAYMENT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_STANDARD;
	
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
 		// Define file format
 		$this->_strDelimiter	= "|";
 		$this->_strEnclosedBy	= '"';
 		
 		$arrDefine['Bank']					['Index']	= 0;
 		$arrDefine['ABN']					['Index']	= 1;
 		$arrDefine['Receivables']			['Index']	= 2;
 		$arrDefine['RemittanceDateTitle']	['Index']	= 3;
 		$arrDefine['RemittanceDate']		['Index']	= 4;
 		$arrDefine['FileDateTitle']			['Index']	= 5;
 		$arrDefine['FileDate']				['Index']	= 6;
 		$arrDefine['TotalValueTitle']		['Index']	= 7;
 		$arrDefine['TotalValue']			['Index']	= 8;
 		$arrDefine['TotalItemsTitle']		['Index']	= 9;
 		$arrDefine['TotalItems']			['Index']	= 10;
 		$arrDefine['ReportedValueTitle']	['Index']	= 11;
 		$arrDefine['ReportedValue']			['Index']	= 12;
		$arrDefine['ReportedItemsTitle']	['Index']	= 13;
		$arrDefine['ReportedItems']			['Index']	= 14;
		$arrDefine['ServiceIdTitle']		['Index']	= 15;
		$arrDefine['ServiceId']				['Index']	= 16;
		$arrDefine['ServiceNameTitle']		['Index']	= 17;
		$arrDefine['ServiceName']			['Index']	= 18;
		$arrDefine['AccountNumberTitle']	['Index']	= 19;
		$arrDefine['AccountNumber']			['Index']	= 20;
		$arrDefine['AccountBSBTitle']		['Index']	= 21;
		$arrDefine['AccountBSB']			['Index']	= 22;
		$arrDefine['ClientTitle']			['Index']	= 23;
		$arrDefine['ItemTitle']				['Index']	= 24;
		$arrDefine['TransactionTitle']		['Index']	= 25;
		$arrDefine['OriginatingTitle']		['Index']	= 26;
		$arrDefine['TiddReceiptTitle']		['Index']	= 27;
		$arrDefine['VoucherTitle']			['Index']	= 28;
		$arrDefine['BPayReceiptTitle']		['Index']	= 29;
		$arrDefine['Transaction2Title']		['Index']	= 30;
		$arrDefine['NameTitle']				['Index']	= 31;
		$arrDefine['AmountTitle']			['Index']	= 32;
		$arrDefine['TypeTitle']				['Index']	= 33;
		$arrDefine['SystemTitle']			['Index']	= 34;
		$arrDefine['NumberTitle']			['Index']	= 35;
		$arrDefine['TraceNumberTitle']		['Index']	= 36;
		$arrDefine['Number2Title']			['Index']	= 37;
		$arrDefine['TypeCodeTitle']			['Index']	= 38;
		$arrDefine['CustomerReference']		['Index']	= 39;
		$arrDefine['Amount']				['Index']	= 40;
		$arrDefine['Type']					['Index']	= 41;
		$arrDefine['System']				['Index']	= 42;
		$arrDefine['Number']				['Index']	= 43;
		$arrDefine['Blank']					['Index']	= 44;
		$arrDefine['TraceNumber']			['Index']	= 45;
		$arrDefine['Number2']				['Index']	= 46;
		$arrDefine['Number3']				['Index']	= 47;

		$arrDefine['Amount']			['Validate'] = "/^\$\d+\.\d{2}$/";
		$arrDefine['CustomerReference']	['Validate'] = "/^\d+$/";			// FIXME: Find out how customer ref #s are generated
		$arrDefine['RemittanceDate']	['Validate'] = "/^\d{1,2}\/\d{1,2}\/\d{2}$/";
		
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
 		
 		// Check to see if there are quotes enclosing
 		if (stripos($strPaymentRecord, '"') !== 0)
 		{
 			return PAYMENT_CANT_NORMALISE_INVALID;
 		}
	 	
 		// BPay are idiots, so parse the file first, changing it from comma-delimited to pipe-delimited
	 	$strRawRecord = str_replace("\",", "\"|", $strPaymentRecord);
	 	$strRawRecord = str_replace(",\"", "|\"", $strRawRecord);
 		
 		//Debug($strRawRecord);
 		
 		// Split the parsed record
 		$this->_SplitRaw($strRawRecord);
		
 		// PaymentType
 		$this->_Append('PaymentType', PAYMENT_TYPE_BPAY);
 		
 		// Amount
 		$mixValue	= str_replace(',', '', $this->_FetchRaw('Amount'));
 		$mixValue	= (float)ltrim($mixValue, "$");
 		$this->_Append('Amount', $mixValue);
 		
 		// Transaction Reference Number
 		$mixValue	= $this->_FetchRaw('TraceNumber');
 		$this->_Append('TXNReference', $mixValue);
 		
 		// PaidOn
 		$mixValue	= $this->_ConvertDate($this->_FetchRaw('RemittanceDate'));
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
	 * Converts from "D/M/YY" date format to "YYYY-MM-DD".  Assumes that dates are in the 21st Century
	 *
	 * @param		string	$strPaymentRecord	String containing raw record
	 * 
	 * @return		string
	 *
	 * @method
	 */
 	function _ConvertDate($strDate)
 	{
		$arrElements	= explode("/", $strDate);
		$strYear		= $arrElements[2];
		$strMonth		= str_pad($arrElements[1], 2, "0", STR_PAD_LEFT);
		$strDay			= str_pad($arrElements[0], 2, "0", STR_PAD_LEFT);
		$strValidDate	= $strYear."-".$strMonth."-".$strDay; 
		return $strValidDate;
 	}
 }
?>
