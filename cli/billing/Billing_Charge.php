<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing_Charge
//----------------------------------------------------------------------------//
/**
 * Billing_Charge
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
// Billing_Charge
//----------------------------------------------------------------------------//
/**
 * Billing_Charge
 *
 * Base Charge module for the Billing Application
 *
 * Base Charge module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		billing
 * @class		Billing_Charge
 */
 abstract class Billing_Charge
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
	 * @return			Billing_Charge
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
 	function Generate()
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
 	function Revoke()
 	{
 		
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
 	function RevokeAll()
 	{
 		
 	}
 }
 
 ?>