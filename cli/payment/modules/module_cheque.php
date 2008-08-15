<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// module_cheque
//----------------------------------------------------------------------------//
/**
 * module_cheque
 *
 * Normalises a Cheque Payment record
 *
 * Normalises a Cheque Payment record
 *
 * @file		module_cheque.php
 * @language	PHP
 * @package		Payment_application
 * @author		Rich Davis
 * @version		7.01
 * @copyright	2006-2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
 
//----------------------------------------------------------------------------//
// PaymentModuleCheque
//----------------------------------------------------------------------------//
/**
 * PaymentModuleCheque
 *
 * Payment Module for Cheque Transactions
 *
 * Payment Module for Cheque Transactions
 *
 *
 * @prefix		pay
 *
 * @package		Payment_application
 * @class		PaymentModuleCheque
 */
 class PaymentModuleCheque extends PaymentModule
 {
	public $intBaseCarrier	= CARRIER_PAYMENT;
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_PAYMENT_CHEQUE;
	
 	function __construct($intCarrier)
 	{
 		parent::__construct($intCarrier);
 		
 		// Define file format
 		$this->_strDelimiter	= ",";
 		
 		$arrDefine['Id']			['Index']	= 0;
 		$arrDefine['AccountNo']		['Index']	= 1;
 		$arrDefine['Blank1']		['Index']	= 2;
 		$arrDefine['Amount']		['Index']	= 3;
 		$arrDefine['Branch']		['Index']	= 4;
 		$arrDefine['Blank2']		['Index']	= 5;
 		$arrDefine['BSB']			['Index']	= 6;
 		$arrDefine['ChequeName']	['Index']	= 7;
		
		$arrDefine['Amount']		['Validate'] = "/^\d+(\.\d{1,2}|)$/";
		$arrDefine['AccountNo']		['Validate'] = "/^\d+$/";
		
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
 		// Split the parsed record
 		$this->_SplitRaw($strPaymentRecord);
 		
 		$intId = (int)$this->_FetchRaw('Id');
 		
 		// Check if this is a header or footer record...
 		if ($intId == 0)
 		{
 			return PAYMENT_CANT_NORMALISE_HEADER;
 		}
 		 		
 		// PaymentType
 		$this->_Append('PaymentType', PAYMENT_TYPE_CHEQUE);

 		// Amount
 		$mixValue	= (float)$this->_FetchRaw('Amount');
 		$this->_Append('Amount', $mixValue);
 		
 		// Transaction Reference Number
 		$mixValue	= $this->_FetchRaw('ReferenceNo');
 		$this->_Append('TXNReference', $mixValue);
 		
 		// PaidOn
 		$mixValue	= date("Y-m-d");
 		$this->_Append('PaidOn', $mixValue);
 		
 		// Call the base module's Normalise() to do general tasks
 		parent::Normalise($strPaymentRecord);
 		
 		// Apply AccountGroup Ownership
 		$strAccount			= $this->_FetchRaw('AccountNo');
 		if ((int)$strAccount < 1000000000)
 		{
 			$strAccount = "1".str_pad($strAccount, 9, "0", STR_PAD_LEFT);
 		}
 		$intAccount			= (int)$strAccount;
 		$intAccountGroup	= $this->_FindAccountGroup($intAccount);
 		$this->_Append('AccountGroup', $intAccountGroup);
 		$this->_Append('Account', $intAccount);
 		
 		//----------------------------------------------------------------------
 		
 		// Validate Normalised Data
 		if (!$this->Validate())
 		{
 			return PAYMENT_CANT_NORMALISE_INVALID;
 		}
 		
 		return $this->_Output();
 	}
 }
?>
