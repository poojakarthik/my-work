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
 abstract class BaseCharge
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
	 * @return	boolean
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
	 * Revokes a Charge for the given Invoice
	 *
	 * Revokes a Charge for the given Invoice
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	function Revoke($strInvoiceRun, $intAccount)
 	{
 		// Delete the charge
 		return (bool)$this->_qryDelete->Execute("DELETE FROM Charge WHERE ChargeType = '$this->_strChargeType' AND Account = $intAccount AND InvoiceRun = '$strInvoiceRun'");
 	}
 }
 
 ?>