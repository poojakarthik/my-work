<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//
/**
 * module_directentryreport
 *
 * Normalises a Direct Entry Returns Processing System Payment record
 *
 * @file		module_derps.php
 * @language	PHP
 * @package		cli.payment.import
 * @author		Rich Davis
 * @version		8.12
 * @copyright	2006-2008 Yellow Billing Services
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 * @class		PaymentModuleDirectEntryReport
 */
 class PaymentModuleDirectEntryReport extends PaymentModule
 {
	public $intBaseCarrier	= CARRIER_PAYMENT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT;
	
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
 		// Define file format
 		$arrDefine['RecordType']				['Start']	= 0;
 		$arrDefine['RecordType']				['Length']	= 1;
 		
 		$arrDefine['BSB']						['Start']	= 1;
 		$arrDefine['BSB']						['Length']	= 7;
 		
 		$arrDefine['AccountNumber']				['Start']	= 8;
 		$arrDefine['AccountNumber']				['Length']	= 9;
 		
 		$arrDefine['Indicator']					['Start']	= 17;
 		$arrDefine['Indicator']					['Length']	= 1;
 		
 		$arrDefine['TransactionCode']			['Start']	= 18;
 		$arrDefine['TransactionCode']			['Length']	= 2;
 		
 		$arrDefine['Amount']					['Start']	= 20;
 		$arrDefine['Amount']					['Length']	= 10;
 		
 		$arrDefine['AccountName']				['Start']	= 30;
 		$arrDefine['AccountName']				['Length']	= 32;
 		
 		$arrDefine['LodgementReference']		['Start']	= 62;
 		$arrDefine['LodgementReference']		['Length']	= 18;
 		
 		$arrDefine['TraceBSB']					['Start']	= 80;
 		$arrDefine['TraceBSB']					['Length']	= 7;
 		
 		$arrDefine['TraceAccount']				['Start']	= 87;
 		$arrDefine['TraceAccount']				['Length']	= 9;
 		
 		$arrDefine['RemitterName']				['Start']	= 96;
 		$arrDefine['RemitterName']				['Length']	= 16;
 		
 		$arrDefine['WithholdingTax']			['Start']	= 112;
 		$arrDefine['WithholdingTax']			['Length']	= 8;
 		
 		$arrDefine['StatusIndicator']			['Start']	= 120;
 		$arrDefine['StatusIndicator']			['Length']	= 1;
		
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
 		// Check if this is a header/footer record...
 		$strRecordType	= substr($strPaymentRecord, 0, 1);
 		switch ($strRecordType)
 		{
 			case '1':
 				// Valid Record
 				break;
 				
 			case '0':
 				$this->_strPaymentDate	= $this->_convertDate(substr($strPaymentRecord, 74, 6));
 				return PAYMENT_CANT_NORMALISE_HEADER;
 			case '7':
 				return PAYMENT_CANT_NORMALISE_FOOTER;
 			default:
 				return PAYMENT_CANT_NORMALISE_INVALID;
 		}
 		
 		// Split the parsed record
 		$this->_SplitRaw($strPaymentRecord);
 		
 		// PaymentType
 		if ($this->_FetchRaw('TransactionCode') == '13')
 		{
 			// We only care about Direct Debit records
 			$this->_Append('PaymentType', PAYMENT_TYPE_DIRECT_DEBIT_BANK_TRANSFER);
 		}
 		else
 		{
 			// Mark any non-DD records as invalid
 			return PAYMENT_CANT_NORMALISE_INVALID;
 		}
 		
 		// Amount
 		$mixValue	= ((float)$this->_FetchRaw('Amount')) / 100;
 		$this->_Append('Amount', $mixValue);
 		
 		// Transaction Reference Number
 		$strTransactionReference	= $this->_FetchRaw('LodgementReference');
 		$this->_Append('TXNReference', $strTransactionReference);
 		
 		// Transaction Type
		$this->_Append('OriginId', $this->_FetchRaw('AccountNumber'));
		$this->_Append('OriginType', PAYMENT_TYPE_BANK_TRANSFER);
 		 
 		// PaidOn
 		$this->_Append('PaidOn', $this->_strPaymentDate);
 		
 		// Call the base module's Normalise() to do general tasks
 		parent::Normalise($strPaymentRecord);
 		
 		// Apply Account Ownership
 		$arrTransactionReference	= explode('_', $strTransactionReference);
 		$intAccount					= (int)$arrTransactionReference[0];
 		$this->_Append('Account', $intAccount);
 		if (($intAccountGroup = $this->_FindAccountGroup($intAccount)) === FALSE)
 		{
			$this->_Append('Status', PAYMENT_BAD_OWNER);
 			return $this->_Output();
 		}
 		$this->_Append('AccountGroup', $intAccountGroup);
 		
 		// Payment Status
 		$mixValue	= $this->_FetchRaw('StatusIndicator');
 		switch (strtoupper($mixValue))
		{
			// Released -- Valid
			case 'G':
				break;
			
			// Recall -- Rejected
			case 'R':
 				return PAYMENT_CANT_NORMALISE_INVALID;
 				//return PAYMENT_REJECTED;
				break;
		}
 		
 		//----------------------------------------------------------------------
 		
 		// Validate Normalised Data
 		if (!$this->Validate())
 		{
 			return PAYMENT_CANT_NORMALISE_INVALID;
 		}
 		
 		return $this->_Output();
 	}
 	
	/**
	 * _convertDate()
	 *
	 * Converts from "DDMMYY" date format to "YYYY-MM-DD".  Assumes that dates are in the 21st Century
	 *
	 * @param		string	$strDate				String containing raw record
	 * 
	 * @return		string
	 *
	 * @method
	 */
 	function _convertDate($strDate)
 	{
		$strYear		= '20'.substr($strDate, 5, 7);
		$strMonth		= substr($strDate, 3, 2);
		$strDay			= substr($strDate, 0, 2);
		$strValidDate	= $strYear."-".$strMonth."-".$strDay; 
		return $strValidDate;
 	}
 }
?>
