<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing_Charge_Account_Postage
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Account_Postage
 *
 * Invoice Postage Fee module for the Billing Application
 *
 * Invoice Postage Fee module for the Billing Application
 *
 * @file		Billing_Charge_Account_Postage.php
 * @language	PHP
 * @package		cli.billing.charge.account
 * @author		Rich Davis
 * @version		8.08
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// Billing_Charge_Account_Postage
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Account_Postage
 *
 * Invoice Postage Fee module for the Billing Application
 *
 * Invoice Postage Fee module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		cli.billing.charge.account
 * @class		Billing_Charge_Account_Postage
 */
 class Billing_Charge_Account_Postage extends Billing_Charge_Account
 {
	protected static $_arrConfigDefinition	= Array(
														'ChargeType'		=> Array(
																						'Default'		=> 'POST',
																						'Type'			=> DATA_TYPE_STRING,
																						'Desctiption'	=> "The ChargeType assigned to this Module"
																					),
														'Description'		=> Array(
																						'Default'		=> "Postage Fee",
																						'Type'			=> DATA_TYPE_STRING,
																						'Desctiption'	=> "The Description that will appear on the Invoice"
																					),
														'Amount'			=> Array(
																						'Default'		=> 2.00,
																						'Type'			=> DATA_TYPE_FLOAT,
																						'Desctiption'	=> "The Fixed Amount to charge all Services that qualify"
																					),
														'InvoiceMinimum'	=> Array(
																						'Default'		=> 2.00,
																						'Type'			=> DATA_TYPE_FLOAT,
																						'Desctiption'	=> "The Minimum Invoice Total to charge this fee"
																					)
													);
	
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
	 * @param	integer	$intModuleId					The billing_charge_module.id for this Module
	 *
	 * @return											Billing_Charge_Account_Postage
	 *
	 * @method
	 */
 	function __construct($intModuleId)
 	{
 		// Call parent constructor
 		parent::__construct($intModuleId);
 		
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
															"Charge.Status = ".CHARGE_TEMP_INVOICE." AND " .
															"Charge.Nature = 'DR'\n" .
															"LIMIT 1");
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Generate
	//------------------------------------------------------------------------//
	/**
	 * Generate()
	 *
	 * Generates a Invoice Postage Fee for the given Invoice
	 *
	 * Generates a Invoice Postage Fee for the given Invoice
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
 		
 		// Is the Invoice Total > Minimum Invoice Total?
 		// FIXME -- Move this figure
 		if ($arrInvoice['Total'] < $this->_cfgModuleConfig->InvoiceMinimum)
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
		$arrCharge['Description']	= $this->_cfgModuleConfig->Description;
		$arrCharge['ChargeType']	= $this->_cfgModuleConfig->ChargeType;
		$arrCharge['ChargedOn']		= date("Y-m-d");
		$arrCharge['CreatedOn']		= date("Y-m-d");
		$arrCharge['Amount']		= $this->_cfgModuleConfig->Amount;
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
 	
 	
	//------------------------------------------------------------------------//
	// CreateModule
	//------------------------------------------------------------------------//
	/**
	 * CreateModule()
	 *
	 * Creates a Module Instance in the Database
	 *
	 * Creates a Module Instance in the Database.  Remove when we start using PHP v5.3
	 * 
	 * @param	integer	$intCustomerGroup		The Customer Group that this will apply to.  NULL = ALL
	 *
	 * @return	integer							Insert Id
	 *
	 * @method
	 */
 	protected static function CreateModule($intCustomerGroup)
 	{
 		// Call Parent CreateModule, because PHP 5.2 doesn't support Late Static Binding :(
 		return parent::CreateModule(__CLASS__, self::$_arrConfigDefinition);
 	}
 }
 
 ?>