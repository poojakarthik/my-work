<?php
//----------------------------------------------------------------------------//
// (c) copyright 2008 VOIPTEL Pty Ltd
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
 *
 * @prefix		chg
 * @package		billing
 * @class		Billing_Charge
 */
 abstract class Billing_Charge
 {
 	protected static	$_arrConfigDefinition;
 	protected			$_cfgModuleConfig;
 	
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
	 * @return											Billing_Charge
	 *
	 * @method
	 */
 	function __construct($intModuleId)
 	{
 		// Module Config Object
 		$this->_cfgModuleConfig	= new Module_Config("billing_charge_module_config", "billing_charge_module_id", $intModuleId);
 		
 		// Statements
		$this->_qryDelete		= new Query();
		$this->_selGetAccounts	= new StatementSelect("Invoice", "Account", "InvoiceRun = <InvoiceRun> UNION SELECT Account FROM InvoiceTemp WHERE InvoiceRun = <InvoiceRun>");
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
 	
 	
	//------------------------------------------------------------------------//
	// CreateModule
	//------------------------------------------------------------------------//
	/**
	 * CreateModule()
	 *
	 * Creates a Module Instance in the Database
	 *
	 * Creates a Module Instance in the Database
	 *
	 * @return	integer							Insert Id
	 *
	 * @method
	 */
 	protected static function CreateModule($strClass, $arrConfigDefinition)
 	{
 		// Create the Module
 		$arrModule			= Array();
 		$arrModule['class']	= $strClass;
		$arrModule['id']	= $insChargeModule->Execute($arrModule);
		if (!$arrModule['id'])
		{
			throw new Exception("DB ERROR: ".$insChargeModule->Error());
		}
		
		// Create Module Configuration using Default Values
		Module_Config::Create("billing_charge_module_config", "billing_charge_module_id", $arrModule['id'], $arrConfigDefinition);
 		
 		return $arrModule['id'];
 	}
 	
 	
	//------------------------------------------------------------------------//
	// LoadModules
	//------------------------------------------------------------------------//
	/**
	 * LoadModules()
	 *
	 * Loads the Billing Charge Modules from the Database
	 *
	 * Loads the Billing Charge Modules from the Database
	 *
	 * @return	array						List of Modules and their Details
	 *
	 * @method
	 */
 	public static function LoadModules()
 	{
 		// Define & init static variables
 		static	$bolInit			= FALSE;
 		static	$selModules;
 		static	$selModuleConfig;
 		if (!$bolInit)
 		{
	 		$selModules			= new StatementSelect("billing_charge_module", "*", "active_status_id = ".ACTIVE_STATUS_ACTIVE);
	 		$selModuleConfig	= new StatementSelect("billing_charge_module_config", "*", "billing_module_config_id = <id>");
	 		$bolInit			= TRUE;
 		}
 		
 		// Retrieve all Billing Charge Modules
 		$arrModules	= Array();
 		if ($selModules->Execute() !== FALSE)
 		{
 			while ($arrModule = $selModules->Fetch())
 			{
 				// Retrieve the Module Config
 				if ($selModuleConfig->Execute($arrModule) !== FALSE)
 				{
 					$arrModule['**Config']	= Array();
 					while ($arrConfigField = $selModuleConfig->Fetch())
 					{
 						// Decode the Field
 						$arrModule['**Config'][$arrConfigField['name']]	= Module_Carrier::DecodeValue($arrConfigField['value'], $arrConfigField['data_type_id']);
 					}
 				}
 				else
 				{
 					throw new Exception("DB ERROR: ".$selModuleConfig->Error());
 				}
 				
 				// Instanciate the Class
 				$modModule	= new $arrModule['class']($arrModule['**Config']);
 				$arrModules[$arrModule['customer_group_id']][get_parent_class($modModule)][get_class($modModule)]	= &$modModule;
 			}
 			
 			// Return array of Billing Charge Modules
 			return $arrModules;
 		}
 		else
 		{
 			throw new Exception("DB ERROR: ".$selModules->Error());
 		}
 	}
 }
 
 ?>