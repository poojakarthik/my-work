<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// charge_service_base
//----------------------------------------------------------------------------//
/**
 * charge_service_base
 *
 * Service Base Charge module for the Billing Application
 *
 * Service Base Charge module for the Billing Application
 *
 * @file		charge_service_base.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// ChargeBaseService
//----------------------------------------------------------------------------//
/**
 * ChargeBaseService
 *
 * Service Base Charge module for the Billing Application
 *
 * Service Base Charge module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		billing_app
 * @class		ChargeBaseService
 */
 abstract class ChargeBaseService extends ChargeBase
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
	 * @return			ChargeBaseService
	 *
	 * @method
	 */
 	function __construct()
 	{
 		// Statements					
		$this->_qryDelete = new Query();
		$this->_selGetAccounts	= new StatementSelect("Invoice", "Account", "InvoiceRun = <InvoiceRun> UNION SELECT Account FROM InvoiceTemp WHERE InvoiceRun = <InvoiceRun>");
		$this->_selGetServices	= new StatementSelect("ServiceTotal", "Service", "InvoiceRun = <InvoiceRun>");
		
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