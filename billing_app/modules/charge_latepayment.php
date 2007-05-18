<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// charge_latepayment
//----------------------------------------------------------------------------//
/**
 * charge_latepayment
 *
 * Late Payment Fee module for the Billing Application
 *
 * Late Payment Fee module for the Billing Application
 *
 * @file		charge_latepayment.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ChargeLatePayment
//----------------------------------------------------------------------------//
/**
 * ChargeLatePayment
 *
 * Late Payment Fee module for the Billing Application
 *
 * Late Payment Fee module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		billing_app
 * @class		ChargeLatePayment
 */
 class ChargeLatePayment extends ChargeBase
 {
 	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Late Payment Charge Object
	 *
	 * Constructor for the Late Payment Charge Object
	 *
	 * @return			ChargeLatePayment
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
		
		$arrData = Array();
		$arrData['DisableLatePayment']	= new MySQLFunction("DisableLatePayment - 1");
		$this->_ubiIncreaseLatePayment = new StatementUpdateById("Account", $arrData);
		
		$arrData = Array();
		$arrData['DisableLatePayment']	= new MySQLFunction("CASE WHEN DisableLatePayment = 0 THEN NULL ELSE DisableLatePayment + 1 END");
		$this->_ubiDecreaseLatePayment = new StatementUpdateById("Account", $arrData);
		
		$this->_selAccount = new StatementSelect("Account", "*", "Id = <Account>");
		
 		$this->_selAccounts = new StatementSelect("Account", "Id", "Archived = 0");
		
		$this->_strChargeType	= "LP".date("my");
		
		$this->_selLatePaymentAccounts = new StatementSelect("Account", "Id, DisableLatePayment", "DisableLatePayment < 1 AND DisableLatePayment IS NOT NULL AND Archived = 0 AND Id = <Account>");
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Generate
	//------------------------------------------------------------------------//
	/**
	 * Generate()
	 *
	 * Generates a Late Payment Charge for the given Invoice
	 *
	 * Generates a Late Payment Charge for the given Invoice
	 *
	 * @return	mixed			float	: Amount charged
	 * 							FALSE	: Charge could not be added 							
	 *
	 * @method
	 */
 	function Generate($arrInvoice, $arrAccount)
 	{ 		
 		// Does this account qualify?
 		if ($arrAccount['DisableLatePayment'] === 1)
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Are we ignoring this Late Payment Fee?
 		if ($arrAccount['DisableLatePayment'] !== NULL)
 		{
 			$arrData = Array();
 			$arrData['Id']					= $arrAccount['Id'];
 			$arrData['DisableLatePayment']	= new MySQLFunction("CASE WHEN DisableLatePayment = 0 THEN NULL ELSE DisableLatePayment + 1 END");
 			
 			// Update the number of times we ignore, and return
 			$this->_ubiDecreaseLatePayment->Execute($arrData);
 			
 			// Was this actually ignored?
 			if ($arrAccount['DisableLatePayment'] !== 0)
 			{
 				// Yes
 				return TRUE;
 			}
 			
 			// No, we were just finalising the number, continue
 		}
 		
 		// Does this Account have more than $10 Overdue?
 		if ($GLOBALS['fwkFramework']->GetOverdueBalance($arrAccount['Id']) <= 10.0)
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Add the charge
		$arrCharge = Array();
		$arrCharge['Nature']		= 'DR';
		$arrCharge['Notes']			= "Automatically Added Charge";
		$arrCharge['Description']	= "Late Payment Fee";
		$arrCharge['ChargeType']	= $this->_strChargeType;
		$arrCharge['Amount']		= 17.27;
		$arrCharge['Status']		= CHARGE_TEMP_INVOICE;
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
 	
 	
	//------------------------------------------------------------------------//
	// Revoke
	//------------------------------------------------------------------------//
	/**
	 * Revoke()
	 *
	 * Revokes a Late Payment Charge for the given Invoice
	 *
	 * Revokes a Late Payment Charge for the given Invoice
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	function Revoke($strInvoiceRun, $intAccount)
 	{
 		// Update LP Ignoring Accounts
 		$this->_selLatePaymentAccounts->Execute(Array('Account' => $intAccount));
 		while ($arrAccount = $this->_selLatePaymentAccounts->Fetch())
 		{
	 		// Do we have a limited number of times we're ignoring Late Payment?
 			$arrData = Array();
 			$arrData['Id']					= $arrAccount['Id'];
 			$arrData['DisableLatePayment']	= new MySQLFunction("DisableLatePayment - 1");
 			$this->_ubiIncreaseLatePayment->Execute($arrData);
 		}
		
 		// Call Parent Revoke()
 		return parent::Revoke($strInvoiceRun);
 	}
 }
 
 ?>