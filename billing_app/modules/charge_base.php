<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// charge_base
//----------------------------------------------------------------------------//
/**
 * charge_base
 *
 * Base Charge module for the Billing Application
 *
 * Base Charge module for the Billing Application
 *
 * @file		charge_base.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ChargeBase
//----------------------------------------------------------------------------//
/**
 * ChargeBase
 *
 * Base Charge module for the Billing Application
 *
 * Base Charge module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		billing_app
 * @class		ChargeBase
 */
 abstract class ChargeBase
 {
 	
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
	 * @return			BaseCharge
	 *
	 * @method
	 */
 	function __construct()
 	{
 		// Statements					
		$this->_qryDelete = new Query();
		$this->_selGetAccounts = new StatementSelect("Invoice", "Account", "InvoiceRun = <InvoiceRun>");
		
		$this->_strChargeType	= "Error: No charge type!";
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
 	abstract function Generate($arrInvoice, $arrAccount);
 	
 	
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
 		// Delete the charge
 		return (bool)$this->_qryDelete->Execute("DELETE FROM Charge WHERE Account = $intAccount ChargeType = '$this->_strChargeType' AND InvoiceRun = '$strInvoiceRun'");
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
 		$this->_selGetAccounts->Execute(Array('InvoiceRun' => $strInvoiceRun));
 		while ($arrAccount = $this->_selGetAccounts->Fetch())
 		{
 			$this->Revoke($strInvoiceRun, $arrAccount['Account']);
 		}
 	}
 }
 
 ?>