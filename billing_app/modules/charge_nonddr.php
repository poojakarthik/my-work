<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// charge_nonddr
//----------------------------------------------------------------------------//
/**
 * charge_nonddr
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 * @file		charge_nonddr.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ChargeNonDirectDebit
//----------------------------------------------------------------------------//
/**
 * ChargeNonDirectDebit
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		billing_app
 * @class		ChargeNonDirectDebit
 */
 class ChargeNonDirectDebit extends ChargeBase
 {
 	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Non Direct Debit Charge Object
	 *
	 * Constructor for the Non Direct Debit Charge Object
	 *
	 * @return			ChargeNonDirectDebit
	 *
	 * @method
	 */
 	function __construct()
 	{
 		// Call parent constructor
 		parent::__construct();
 		
 		// Statements		
		$this->_selTollingAccounts = new StatementSelect(	"CDR USE INDEX (Account_2)",
															"CDR.Id AS Id",
															"CDR.Account = <Account> AND " .
															"CDR.Status IN (".CDR_RATED.", ".CDR_TEMP_INVOICE.") AND " .
															"CDR.Credit = 0 " .
															"\nLIMIT 1\n" .
															"UNION\n" .
															"SELECT Charge.Id AS Id\n" .
															"FROM Charge\n" .
															"WHERE Charge.Account = <Account> AND \n" .
															"Charge.Status = ".CHARGE_APPROVED." AND " .
															"Charge.Nature = 'DR'\n" .
															"LIMIT 1");
															
		$this->_selCreditCard = new StatementSelect(	"CreditCard",
														"Id",
														"AccountGroup = <AccountGroup> AND " .
														"Archived = 0 AND " .
														"DATE(CONCAT(ExpYear, '-', ExpMonth, '-01')) >= CURDATE()");
		
		$this->_selDirectDebit = new StatementSelect(	"DirectDebit",
														"Id",
														"AccountGroup = <AccountGroup> AND " .
														"Archived = 0");
		
		$this->_strChargeType	= "AP250";
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Generate
	//------------------------------------------------------------------------//
	/**
	 * Generate()
	 *
	 * Generates a Non Direct Debit Charge for the given Invoice
	 *
	 * Generates a Non Direct Debit Charge for the given Invoice
	 *
	 * @return	mixed			float	: Amount charged
	 * 							FALSE	: Charge could not be added 		
	 *
	 * @method
	 */
 	function Generate($arrInvoice, $arrAccount)
 	{
 		// Does this account qualify?
 		if ($arrAccount['DisableDDR'] == 1)
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Do we have a valid CreditCard/DirectDebit entry?
 		if ($arrAccount['BillingType'] == BILLING_TYPE_CREDIT_CARD)
 		{
 			// Check for Credit Card
 			if ($this->_selCreditCard->Execute(Array('AccountGroup' => $arrAccount['AccountGroup'])))
 			{
 				// Valid Credit Card
 				return TRUE; 
 			}
 		}
 		elseif ($arrAccount['BillingType'] == BILLING_TYPE_DIRECT_DEBIT)
 		{
 			// Check for DD Details
 			if ($this->_selDirectDebit->Execute(Array('AccountGroup' => $arrAccount['AccountGroup'])))
 			{
 				// Valid DD Details
 				return TRUE;
 			}
 		}
 		
 		// Is the Invoice Total > NON_DDR_MINIMUM_CHARGE?
 		if ($arrInvoice['Total'] < NON_DDR_MINIMUM_CHARGE)
 		{
 			// Yes, return TRUE
 			return TRUE;
 		}
 		
 		// Is this Account tolling?
 		if (!$this->_selTollingAccounts->Execute(Array('Account' => $arrAccount['Id'])))
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Yes, add the charge
		$arrCharge = Array();
		$arrCharge['Nature']		= 'DR';
		$arrCharge['Description']	= "Account Processing Fee";
		$arrCharge['ChargeType']	= $this->_strChargeType;
		$arrCharge['ChargedOn']		= date("Y-m-d");
		$arrCharge['CreatedOn']		= date("Y-m-d");
		$arrCharge['Amount']		= 2.50;
		$arrCharge['Status']		= CHARGE_TEMP_INVOICE;
		$arrCharge['Notes']			= "Automatically Added Charge";
		$arrCharge['Account'] 		= $arrAccount['Id'];
		$arrCharge['AccountGroup'] 	= $arrAccount['AccountGroup'];
		$arrCharge['InvoiceRun']	= $arrInvoice['InvoiceRun'];
		
		// Return FALSE or amount charged
		if (!$GLOBALS['fwkFramework']->AddCharge($arrCharge))
		{
			return FALSE;
		}
		else
		{
			return $arrCharge['Amount'];
		}
 	}
 }
 
 ?>