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
		$arrData['DisableLatePayment']	= new MySQLFunction("CASE WHEN DisableLatePayment = 0 THEN DisableLatePayment = NULL ELSE DisableLatePayment + 1 END");
		$this->_ubiDecreaseLatePayment = new StatementUpdateById("Account", $arrData);
		
		$this->_selAccount = new StatementSelect("Account", "*", "Id = <Account>");
		
		$this->_strChargeType	= "LP".date("my");
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
	 * @return	boolean
	 *
	 * @method
	 */
 	function Generate($arrInvoice, $arrAccount)
 	{
 		// Does this account qualify?
 		if ($arrAccount['DisableLatePayment'] == 1)
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Does this Account have more than $10 Overdue?
 		if ($this->Framework->GetOverdueBalance($arrAccount['Id']) <= 10.0)
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Are we ignoring this Late Payment Fee?
 		if ($arrAccount['DisableLatePayment'] !== NULL)
 		{
 			// Update the number of times we ignore, and return
 			return (bool)$this->_ubiDecreaseLatePayment->Execute($arrAccount);
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
		return (bool)$this->Framework->AddCharge($arrCharge);
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
 		// Do we have a Late Payment Fee?
 		if (!$this->_selHasLatePayment->Execute(Array('InvoiceRun' => $strInvoiceRun, 'Account' => $intAccount)))
 		{
 			// No, do we have a limited number of times we're ignoring Late Payment?
 			$this->_selAccount->Execute(Array('Account' => $intAccount));
 			$arrData = $this->_selAccount->Fetch();
 			if ($arrData['DisableLatePayment'] <= 0)
 			{
 				return (bool)$this->_ubiIncreaseLatePayment->Execute($arrData);
 			}
 			
 			// No, just return TRUE
 			return TRUE;
 		}
 		
 		// Call Parent Revoke()
 		if (!parent::Revoke($strInvoiceRun))
 		{
 			return FALSE;
 		}
 	}
 }
 
 ?>