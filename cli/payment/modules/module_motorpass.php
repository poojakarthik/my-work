<?php
/**
 * PaymentModuleMotorpass
 *
 * Payment Module for the Motorpass Billing File
 *
 * @class		PaymentModuleMotorpass
 */
 class PaymentModuleMotorpass extends PaymentModule
 {
	public $intBaseCarrier	= CARRIER_PAYMENT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT;
	
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
 		// Define file format
 		$this->_strDelimiter	= ",";
 		$this->_strEnclosedBy	= '"';
 		
 		$arrDefine['ClientReferenceNo']			['Index']	= 0;	// Flex Invoice #
 		$arrDefine['AccountNumber']				['Index']	= 1;	// Motorpass Account Number
 		$arrDefine['BillingAmount']				['Index']	= 2;	// Rebilled Value
 		$arrDefine['Fee']						['Index']	= 3;	// Fee Rebill Value
		
		$this->_arrDefine = $arrDefine;
		
		// Default Date
		$this->_sFileDate	= date("Y-m-d");
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
 		// Check for Header
 		$aRegexMatches	= array();
 		if (preg_match("/^(?P<RecordType>00),(?P<Sender>[A-Z]+),(?P<Receiver>[A-Z]+),(?P<Date>\d{2}\/\d{2}\/\d{4}),(?P<Time>\d{2}\:\d{2})/i", $strPaymentRecord, $aRegexMatches))
 		{
 			// Get the File Effective Date (convert DDMMYYYY to YYYY-MM-DD)
 			$sDateDDMMYYYY		= $aRegexMatches['Date'];
 			$this->_sFileDate	= substr($aRegexMatches['Date'], 6, 4).'-'.substr($aRegexMatches['Date'], 3, 2).'-'.substr($aRegexMatches['Date'], 0, 2);
 			
 			return PAYMENT_CANT_NORMALISE_HEADER;
 		}
 		
 		// Check for Footer
 		if (preg_match("/^(99),(?P<Date>\d{2}\/\d{2}\/\d{4}),(?P<Time>\d{2}\:\d{2}),(?P<StartDate>\d{2}\/\d{2}\/\d{4}),(?P<EndDate>\d{2}\/\d{2}\/\d{4}),(?P<TotalCount>\d+)/i", $strPaymentRecord, $aRegexMatches))
 		{
 			return PAYMENT_CANT_NORMALISE_FOOTER;
 		}
 		
 		//Debug($strRawRecord);
 		
 		// Split the parsed record
 		$this->_SplitRaw($strPaymentRecord);
		
 		// PaymentType
 		$this->_Append('PaymentType', PAYMENT_TYPE_REBILL_PAYOUT);
 		
 		// Amount
 		$fBillingAmount	= (float)$this->_FetchRaw('BillingAmount') + (float)$this->_FetchRaw('Fee');
 		$this->_Append('Amount', $fBillingAmount);
 		
 		// Transaction Reference Number
 		$mixValue	= $this->_FetchRaw('ClientReferenceNo');
 		$this->_Append('TXNReference', $mixValue);
 		
 		// PaidOn
 		$this->_Append('PaidOn', $this->_sFileDate);
 		
 		// Call the base module's Normalise() to do general tasks
 		parent::Normalise($strPaymentRecord);
 		
 		// Apply Ownership
 		if (!($oInvoice = Invoice::getForId((int)$this->_FetchRaw('ClientReferenceNo'), true)))
 		{
 			// Error: Invoice not found!
			$this->_Append('Status', PAYMENT_BAD_OWNER);
 			return $this->_Output();
 		}
 		$this->_Append('Account', $oInvoice->Account);
 		$this->_Append('AccountGroup', $oInvoice->AccountGroup);
 		
 		//----------------------------------------------------------------------
 		
 		// Validate Normalised Data
 		if (!$this->Validate())
 		{
			$this->_Append('Status', PAYMENT_CANT_NORMALISE_INVALID);
 		}
 		elseif ($fBillingAmount <= 0)
 		{
			// We can only pay-out Debit Invoices
 			$this->_Append('Status', PAYMENT_CANT_NORMALISE_INVALID);
 		}
 		
 		// DEBUG: Force it to be Invalid so that we can verify the data
		//$this->_Append('Status', PAYMENT_CANT_NORMALISE_INVALID);
 		
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
