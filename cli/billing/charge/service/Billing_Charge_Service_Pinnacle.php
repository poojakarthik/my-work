<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing_Charge_Service_Pinnacle
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Service_Pinnacle
 *
 * Pinnacle Service Fee module for the Billing Application
 *
 * Pinnacle Service Fee module for the Billing Application
 *
 * @file		Billing_Charge_Service_Pinnacle.php
 * @language	PHP
 * @package		cli.billing.charge.service
 * @author		Rich Davis
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// Billing_Charge_Service_Pinnacle
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Service_Pinnacle
 *
 * Pinnacle Service Fee module for the Billing Application
 *
 * Pinnacle Service Fee module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		cli.billing.charge.service
 * @class		Billing_Charge_Service_Pinnacle
 */
 class Billing_Charge_Service_Pinnacle extends Billing_Charge_Service
 {
	protected static $_arrConfigDefinition	= Array(
														'ChargeType'	=> Array(
																					'Default'		=> 'PMF',
																					'Type'			=> DATA_TYPE_STRING,
																					'Desctiption'	=> "The ChargeType assigned to this Module"
																				),
														'Description'	=> Array(
																					'Default'		=> "Pinnacle Mobile Service Fee",
																					'Type'			=> DATA_TYPE_STRING,
																					'Desctiption'	=> "The Description that will appear on the Invoice"
																				),
														'Amount'		=> Array(
																					'Default'		=> 15.00,
																					'Type'			=> DATA_TYPE_FLOAT,
																					'Desctiption'	=> "The Fixed Amount to charge all Services that qualify"
																				) 
													);
 	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Pinnacle Service Fee Object
	 *
	 * Constructor for the Pinnacle Service Fee Object
	 * 
	 * @param	integer	$intModuleId					The billing_charge_module.id for this Module
	 *
	 * @return											Billing_Charge_Service_Pinnacle
	 *
	 * @method
	 */
 	function __construct($intModuleId)
 	{
 		// Call parent constructor
 		parent::__construct($intModuleId);
		
 		// Statements
		$this->_selPM15Services = new StatementSelect(	"Service JOIN ServiceRatePlan ON Service.Id = ServiceRatePlan.Service",
														"Service, Account, AccountGroup",
														"(Service.ClosedOn IS NULL OR Service.ClosedOn > CURDATE()) AND " .
														"Service = <Service> AND ServiceRatePlan.RatePlan = 20 AND " .
														"ServiceRatePlan.Id = (" .
														" SELECT SRP.Id" .
														" FROM ServiceRatePlan SRP" .
														" WHERE SRP.Service = Service.Id AND Active = 1" .
														" AND NOW() BETWEEN SRP.StartDatetime AND SRP.EndDatetime" .
														" ORDER BY CreatedOn DESC" .
														" LIMIT 1 )");
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Generate
	//------------------------------------------------------------------------//
	/**
	 * Generate()
	 *
	 * Generates a Pinnacle Service Fee for the given Invoice
	 *
	 * Generates a Pinnacle Service Fee for the given Invoice
	 *
	 * @return	mixed			float	: Amount charged
	 * 							FALSE	: Charge could not be added 							
	 *
	 * @method
	 */
 	function Generate($arrInvoiceRun, $arrService)
 	{
		$fltTotalCharged	= 0.0;
 		if ($this->_selPM15Services->Execute($arrService))
 		{
			while ($arrServiceDetails = $this->_selPM15Services->Fetch())
			{
				$arrCharge = Array();
				$arrCharge['Nature']		= 'DR';
				$arrCharge['Description']	= $this->_cfgModuleConfig->Description;
				$arrCharge['ChargeType']	= $this->_cfgModuleConfig->ChargeType;
				$arrCharge['CreatedOn']		= date("Y-m-d");
				$arrCharge['ChargedOn']		= date("Y-m-d");
				$arrCharge['Amount']		= $this->_cfgModuleConfig->Amount;
				$arrCharge['Status']		= CHARGE_TEMP_INVOICE;
				$arrCharge['Service'] 		= $arrServiceDetails['Service'];
				$arrCharge['Account'] 		= $arrServiceDetails['Account'];
				$arrCharge['AccountGroup'] 	= $arrServiceDetails['AccountGroup'];
				$arrCharge['InvoiceRun']	= $arrInvoiceRun['InvoiceRun'];
 				$GLOBALS['fwkFramework']->AddCharge($arrCharge);
 				
 				$fltTotalCharged			+= $arrCharge['Amount'];
			}
 		}
 		
 		return $fltTotalCharged;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Revoke
	//------------------------------------------------------------------------//
	/**
	 * Revoke()
	 *
	 * Revokes a Pinnacle Service Fee for the given Invoice
	 *
	 * Revokes a Pinnacle Service Fee for the given Invoice
	 *
	 * @return	boolean
	 *
	 * @method
	 */
 	function Revoke($strInvoiceRun, $intAccount)
 	{
 		// Call Parent Revoke()
 		return parent::Revoke($strInvoiceRun, $intAccount);
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
 		return parent::CreateModule(__CLASS__, self::$_arrConfigDefinition, $intCustomerGroup);
 	}
 }
 
 ?>