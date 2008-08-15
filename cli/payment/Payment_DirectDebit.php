<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Payment_DirectDebit
//----------------------------------------------------------------------------//
/**
 * Payment_DirectDebit
 *
 * Base Class for Charging Direct Debit Payments
 *
 * Base Class for Charging Direct Debit Payments
 *
 * @file		Payment_DirectDebit.php
 * @language	PHP
 * @package		cli.payment
 * @author		Rich "Waste" Davis
 * @version		8.08
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// Payment_DirectDebit
//----------------------------------------------------------------------------//
/**
 * Payment_DirectDebit
 *
 * Base Class for Charging Direct Debit Payments
 *
 * Base Class for Charging Direct Debit Payments
 *
 * @prefix		exp
 *
 * @package		cli.payment
 * @class		Payment_DirectDebit
 */
 class Payment_DirectDebit extends CarrierModule
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
 		parent::__construct($intCarrier, MODULE_TYPE_PAYMENT_DIRECT_DEBIT, $intCustomerGroup);
 		
 		// Defaults
 		$this->intCarrier		= $intCarrier;
 		$this->intCustomerGroup	= $intCustomerGroup;
 		$this->_arrDefine		= Array();
 		$this->bolExported		= FALSE;
 		$this->_intMinRequests	= 0;
 		
 		// Statements
 		$this->_selCustomerDetails	= new StatementSelect(	"Account",
 															"*",
 															"Id = <Account>");
 		
 		$this->_selCreditCard		= new StatementSelect(	"Account JOIN CreditCard ON Account.CreditCard = CreditCard.Id",
 															"*",
 															"Account.Id = <Account>");
 		
 		$this->_selBankDetails		= new StatementSelect(	"Account JOIN DirectDebit ON Account.DirectDebit = DirectDebit.Id",
 															"*",
 															"Account.Id = <Account>");
 	}
 	
 	//------------------------------------------------------------------------//
	// Output
	//------------------------------------------------------------------------//
	/**
	 * Output()
	 *
	 * Exports a Direct Debit Request to a format accepted by the Carrier
	 *
	 * Exports a Direct Debit Request to a format accepted by the Carrier
	 * 
	 * @param	array	$arrRequest		Request to Export
	 * 
	 * @return	array					Modified Request
	 *
	 * @method
	 */
	abstract function Output($arrRequest)
 	{
 		// No Default Functionality
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
	 * @return	array					'Success'		: TRUE/FALSE/NULL (Skipped)
	 * 									'Description'	: Error message
	 *
	 * @method
	 */
	abstract function Export()
 	{
 		// No Default Functionality
 	}
 	
 	//------------------------------------------------------------------------//
	// _GetAccountDetails
	//------------------------------------------------------------------------//
	/**
	 * _GetAccountDetails()
	 *
	 * Retrieves Account Details for Direct Debit purposes
	 *
	 * Retrieves Account Details for Direct Debit purposes
	 * 
	 * @param	integer	$intAccount		Account to get details for
	 * 
	 * @return	mixed						array: Account Details
	 * 										FALSE: Error
	 *
	 * @method
	 */
	protected function _GetAccountDetails($intAccount)
 	{
 		// Get Account.*
 		if ($this->_selCustomerDetails->Execute(Array('Account' => $intAccount)))
 		{
			$arrAccountDetails	= $this->_selCustomerDetails->Fetch();
 			
 			// Get Bank Account Details
 			if ($this->_selBankDetails->Execute(Array('Account' => $intAccount)) === FALSE)
 			{
 				// DB Error
 				Debug($this->_selBankDetails->Error());
 				return FALSE;
 			}
 			else
 			{
 				$arrAccountDetails['DirectDebit']	= ($arrBankDetails = $this->_selBankDetails->Fetch()) ? $arrBankDetails : NULL;
 			}
 			
 			// Get Credit Card Details
 			if (!$this->_selCreditCard->Execute(Array('Account' => $intAccount)))
 			{
 				// DB Error
 				Debug($this->_selCreditCard->Error());
 				return FALSE;
 			}
 			else
 			{
 				$arrAccountDetails['DirectDebit']	= ($arrBankDetails = $this->_selCreditCard->Fetch()) ? $arrBankDetails : NULL;
 			}
 			
 			// Return Account Details
 			return $arrAccountDetails;
 		}
 		else
 		{
 			if ($this->_selCustomerDetails->Error())
 			{
 				// DB Error
 				Debug($this->_selCustomerDetails->Error());
 				return FALSE;
 			}
 			else
 			{
 				// Bad Account #
 				return FALSE;
 			}
 		}
 	}
}
?>