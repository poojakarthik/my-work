<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// postage
//----------------------------------------------------------------------------//
/**
 * postage
 *
 * Invoice Postage Fee module for the Billing Application
 *
 * Invoice Postage Fee module for the Billing Application
 *
 * @file		postage.php
 * @language	PHP
 * @package		billing.charge.account
 * @author		Rich Davis
 * @version		8.08
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// BillingChargeAccountPostage
//----------------------------------------------------------------------------//
/**
 * BillingChargeAccountPostage
 *
 * Invoice Postage Fee module for the Billing Application
 *
 * Invoice Postage Fee module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		billing.charge.account
 * @class		BillingChargeAccountPostage
 */
 class BillingChargeAccountPostage extends BillingChargeAccountBase
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
		
		// Config
		$this->_arrConfig = $GLOBALS['**arrCustomerConfig']['Billing']['BillingTimeModules']['ChargePostage'];
		
		$this->_strChargeType	= $this->_arrConfig['Code'];
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
 		if ($arrAccount['BillingMethod'] !== BILLING_METHOD_POST)
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Is the Invoice Total > NON_DDR_MINIMUM_CHARGE?
 		if ($arrInvoice['Total'] < $this->_arrConfig['MinimumTotal'])
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
		$arrCharge['Description']	= $this->_arrConfig['Description'];
		$arrCharge['ChargeType']	= $this->_arrConfig['Code'];
		$arrCharge['ChargedOn']		= date("Y-m-d");
		$arrCharge['CreatedOn']		= date("Y-m-d");
		$arrCharge['Amount']		= $this->_arrConfig['Amount'];
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