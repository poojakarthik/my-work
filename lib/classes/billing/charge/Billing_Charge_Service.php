<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing_Charge_Service
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Service
 *
 * Service Base Charge module for the Billing Application
 *
 * Service Base Charge module for the Billing Application
 *
 * @file		Billing_Charge_Service.php
 * @language	PHP
 * @package		cli.billing.charge
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// Billing_Charge_Service
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Service
 *
 * Service Base Charge module for the Billing Application
 *
 * Service Base Charge module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		cli.billing.charge
 * @class		Billing_Charge_Service
 */
 abstract class Billing_Charge_Service extends Billing_Charge
 {
	protected	$_selGetAccounts;
	protected	$_selGetServices;
	
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
	 * @param	integer	$intModuleId					The billing_charge_module.id for this Module
	 * @param	array	$arrConfigDefinition			The Config Definition for this module
	 *
	 * @return											Billing_Charge_Service
	 *
	 * @method
	 */
 	function __construct($intModuleId, $arrConfigDefinition)
 	{
 		// Call parent constructor
 		parent::__construct($intModuleId, $arrConfigDefinition);
 		
 		// Statements					
		$this->_qryDelete		= new Query();
		$this->_selGetAccounts	= new StatementSelect("Invoice", "Account", "invoice_run_id = <invoice_run_id>");
		$this->_selGetServices	= new StatementSelect("ServiceTotal", "Service", "invoice_run_id = <invoice_run_id>");
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
 	function Generate($objInvoiceRun, $objService)
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
 	function Revoke($objInvoice, $objAccount)
 	{
 		// Delete the charge
 		return (bool)$this->_qryDelete->Execute("DELETE FROM Charge WHERE Account = {$objAccount->Id} AND ChargeType = '{$this->_cfgModuleConfig->ChargeType}' AND invoice_run_id = '{$objInvoice->invoice_run_id}'");
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
 	function RevokeAll($objInvoiceRun)
 	{
 		// Delete the charges
 		if (!$this->_selGetAccounts->Execute(Array('invoice_run_id' => $objInvoiceRun->Id)))
 		{
 			Debug($this->_selGetAccounts->Error());
 		}
 		while ($arrAccount = $this->_selGetAccounts->Fetch())
 		{
 			$objAccount	= new Account($arrAccount);
 			$this->Revoke($objInvoiceRun, $objAccount);
 		}
 	}
 }
 
 ?>