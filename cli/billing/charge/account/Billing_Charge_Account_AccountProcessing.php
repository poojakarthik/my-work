<?php
//----------------------------------------------------------------------------//
// (c) copyright 2007 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing_Charge_Account_AccountProcessing
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Account_AccountProcessing
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 * @file		Billing_Charge_Account_AccountProcessing.php
 * @language	PHP
 * @package		cli.billing.charge
 * @author		Rich Davis
 * @version		7.05
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// Billing_Charge_Account_AccountProcessing
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Account_AccountProcessing
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 * Non Direct Debit Fee module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		cli.billing.charge.account
 * @class		Billing_Charge_Account_AccountProcessing
 */
 class Billing_Charge_Account_AccountProcessing extends Billing_Charge_Account
 {
	protected static $_arrConfigDefinition	= Array(
														'ChargeType'		=> Array(
																						'Default'		=> 'APF',
																						'Type'			=> DATA_TYPE_STRING,
																						'Desctiption'	=> "The ChargeType assigned to this Module"
																					),
														'Description'		=> Array(
																						'Default'		=> "Account Processing Fee",
																						'Type'			=> DATA_TYPE_STRING,
																						'Desctiption'	=> "The Description that will appear on the Invoice"
																					),
														'Amount'			=> Array(
																						'Default'		=> 2.75,
																						'Type'			=> DATA_TYPE_FLOAT,
																						'Desctiption'	=> "The Fixed Amount to charge all Services that qualify"
																					),
														'InvoiceMinimum'	=> Array(
																						'Default'		=> 2.75,
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
	 * @return											Billing_Charge_Account_AccountProcessing
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
															"Charge.Status = ".CHARGE_APPROVED." AND " .
															"Charge.Nature = 'DR'\n" .
															"LIMIT 1");
															
		$this->_selCreditCard = new StatementSelect(	"CreditCard",
														"Id",
														"AccountGroup = <AccountGroup> AND " .
														"Archived = 0 AND " .
														"DATE(CONCAT(ExpYear, '-', ExpMonth, '-01')) >= CURDATE()");
		
		$this->_selDirectDebit = new StatementSelect(	"DirectDebit",
														"Id",
														"AccountGroup = <AccountGroup> AND " .
														"Archived = 0");
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Generate
	//------------------------------------------------------------------------//
	/**
	 * Generate()
	 *
	 * Generates a Non Direct Debit Charge for the given Invoice
	 *
	 * Generates a Non Direct Debit Charge for the given Invoice
	 *
	 * @return	mixed			float	: Amount charged
	 * 							FALSE	: Charge could not be added 		
	 *
	 * @method
	 */
 	function Generate($arrInvoice, $arrAccount)
 	{
 		// Does this account qualify?
 		if ($arrAccount['DisableDDR'] == 1)
 		{
 			// No, return TRUE
 			return TRUE;
 		}
 		
 		// Do we have a valid CreditCard/DirectDebit entry?
 		if ($arrAccount['BillingType'] == BILLING_TYPE_CREDIT_CARD)
 		{
 			// Check for Credit Card
 			if ($this->_selCreditCard->Execute(Array('AccountGroup' => $arrAccount['AccountGroup'])))
 			{
 				// Valid Credit Card
 				return TRUE; 
 			}
 		}
 		elseif ($arrAccount['BillingType'] == BILLING_TYPE_DIRECT_DEBIT)
 		{
 			// Check for DD Details
 			if ($this->_selDirectDebit->Execute(Array('AccountGroup' => $arrAccount['AccountGroup'])))
 			{
 				// Valid DD Details
 				return TRUE;
 			}
 		}
 		
 		// Is the Invoice Total > NON_DDR_MINIMUM_CHARGE?
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
	 * @return	integer							Insert Id
	 *
	 * @method
	 */
 	protected static function CreateModule()
 	{
 		// Call Parent CreateModule, because PHP 5.2 doesn't support Late Static Binding :(
 		return parent::CreateModule(__CLASS__, self::$_arrConfigDefinition);
 	}
 }
 
 ?>