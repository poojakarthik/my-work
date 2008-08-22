<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing_Charge_Account
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Account
 *
 * Account Base Charge module for the Billing Application
 *
 * Account Base Charge module for the Billing Application
 *
 * @file		charge_account_base.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// Billing_Charge_Account
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Account
 *
 * Account Base Charge module for the Billing Application
 *
 * Account Base Charge module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		billing.charge
 * @class		Billing_Charge_Account
 */
 abstract class Billing_Charge_Account extends Billing_Charge
 {
 	public $strChargeType;
 	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Charge Object
	 *
	 * Constructor for the Charge Object
	 *
	 * @return			Billing_Charge_Account
	 *
	 * @method
	 */
 	function __construct()
 	{
 		// Statements					
		$this->_qryDelete = new Query();
		$this->_selGetAccounts = new StatementSelect("Invoice", "Account", "InvoiceRun = <InvoiceRun> UNION SELECT Account FROM InvoiceTemp WHERE InvoiceRun = <InvoiceRun>");
		
		$this->_strChargeType	= NULL;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Generate
	//------------------------------------------------------------------------//
	/**
	 * Generate()
	 *
	 * Generates a Charge for the given Invoice
	 *
	 * Generates a Charge for the given Invoice
	 *
	 * @return	mixed			float	: Amount charged
	 * 							FALSE	: Charge could not be added 		
	 *
	 * @method
	 */
 	function Generate($arrInvoice, $arrAccount)
 	{
 		
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Revoke
	//------------------------------------------------------------------------//
	/**
	 * Revoke()
	 *
	 * Revokes a Charge for the given Invoice Run and Account
	 *
	 * Revokes a Charge for the given Invoice Run and Account
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	function Revoke($strInvoiceRun, $intAccount)
 	{
 		//Debug("InvoiceRun: '$strInvoiceRun'\nAccount: $intAccount\nCharge Type: '$this->_strChargeType'");
 		
 		// Delete the charge
 		return (bool)$this->_qryDelete->Execute("DELETE FROM Charge WHERE Account = $intAccount AND ChargeType = '$this->_strChargeType' AND InvoiceRun = '$strInvoiceRun'");
 	}
 	
 	
	//------------------------------------------------------------------------//
	// RevokeAll
	//------------------------------------------------------------------------//
	/**
	 * RevokeAll()
	 *
	 * Revokes all Charges for the given Invoice Run
	 *
	 * Revokes all Charges for the given Invoice Run
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	function RevokeAll($strInvoiceRun)
 	{
 		// Delete the charges
 		if (!$this->_selGetAccounts->Execute(Array('InvoiceRun' => $strInvoiceRun)))
 		{
 			Debug($this->_selGetAccounts->Error());
 		}
 		while ($arrAccount = $this->_selGetAccounts->Fetch())
 		{
 			$this->Revoke($strInvoiceRun, $arrAccount['Account']);
 		}
 	}
 }
 
 ?>