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
	 *
	 * @return											Billing_Charge_Service
	 *
	 * @method
	 */
 	function __construct($intModuleId)
 	{
 		// Call parent constructor
 		parent::__construct($intModuleId);
 		
 		// Statements					
		$this->_qryDelete		= new Query();
		$this->_selGetAccounts	= new StatementSelect("Invoice", "Account", "InvoiceRun = <InvoiceRun> UNION SELECT Account FROM InvoiceTemp WHERE InvoiceRun = <InvoiceRun>");
		$this->_selGetServices	= new StatementSelect("ServiceTotal", "Service", "InvoiceRun = <InvoiceRun>");
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
 	function Generate($arrInvoiceRun, $arrService)
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
 		return (bool)$this->_qryDelete->Execute("DELETE FROM Charge WHERE Account = $intAccount AND ChargeType = '{$this->_cfgModuleConfig->ChargeType}' AND InvoiceRun = '$strInvoiceRun'");
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