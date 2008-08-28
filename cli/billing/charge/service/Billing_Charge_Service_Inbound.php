<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Billing_Charge_Service_Inbound
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Service_Inbound
 *
 * Inbound Service Fee module for the Billing Application
 *
 * Inbound Service Fee module for the Billing Application
 *
 * @file		Billing_Charge_Service_Inbound.php
 * @language	PHP
 * @package		cli.billing.charge.service
 * @author		Rich Davis
 * @version		8.05
 * @copyright	2008 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */


//----------------------------------------------------------------------------//
// Billing_Charge_Service_Inbound
//----------------------------------------------------------------------------//
/**
 * Billing_Charge_Service_Inbound
 *
 * Inbound Service Fee module for the Billing Application
 *
 * Inbound Service Fee module for the Billing Application
 *
 *
 * @prefix		chg
 *
 * @package		cli.billing.charge.service
 * @class		Billing_Charge_Service_Inbound
 */
 class Billing_Charge_Service_Inbound extends Billing_Charge_Service
 {
	protected static $_arrConfigDefinition	= Array(
														'ChargeType'	=> Array(
																					'Default'		=> 'INB',
																					'Type'			=> DATA_TYPE_STRING,
																					'Desctiption'	=> "The ChargeType assigned to this Module"
																				),
														'Description'	=> Array(
																					'Default'		=> "Inbound Service Fee",
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
	 * Constructor for the Inbound Service Fee Charge Object
	 *
	 * Constructor for the Inbound Service Fee Charge Object
	 * 
	 * @param	integer	$intModuleId					The billing_charge_module.id for this Module
	 *
	 * @return											Billing_Charge_Service_Inbound
	 *
	 * @method
	 */
 	function __construct($intModuleId)
 	{
 		// Call parent constructor
 		parent::__construct($intModuleId);
		
 		// Statements
		$this->_selINB15Services = new StatementSelect(	"CDR", 
														"Service, Account, AccountGroup, COUNT(CDR.Id) AS CDRCount", 
														"Service = <Service> AND Credit = 0 AND Status = 198 AND ServiceType = ".SERVICE_TYPE_INBOUND, 
														NULL, 
														NULL, 
														"Service \n HAVING CDRCount > 0");
 	}
 	
 	
	//------------------------------------------------------------------------//
	// Generate
	//------------------------------------------------------------------------//
	/**
	 * Generate()
	 *
	 * Generates a Inbound Service Fee Charge for the given Invoice
	 *
	 * Generates a Inbound Service Fee Charge for the given Invoice
	 *
	 * @return	mixed			float	: Amount charged
	 * 							FALSE	: Charge could not be added 							
	 *
	 * @method
	 */
 	function Generate($arrInvoiceRun, $arrService)
 	{
		$fltTotalCharged	= 0.0;
 		if ($this->_selINB15Services->Execute($arrService))
 		{
			while ($arrServiceDetails = $this->_selINB15Services->Fetch())
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
	 * Revokes a Inbound Service Fee Charge for the given Invoice
	 *
	 * Revokes a Inbound Service Fee Charge for the given Invoice
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