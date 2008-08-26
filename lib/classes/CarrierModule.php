<?php

//----------------------------------------------------------------------------//
// CarrierModule
//----------------------------------------------------------------------------//
/**
 * CarrierModule
 *
 * Carrier Module Base Class
 *
 * Carrier Module Base Class
 *
 * @package	framework
 * @class	CarrierModule
 */
class CarrierModule
{
 	protected $_intModuleType;
 	protected $_intModuleCarrier;
 	
 	protected $_arrCarrierModule;
	
	public $_intFrequencyType		= FREQUENCY_SECOND;
	public $_intFrequency			= 3600;
	public $_intEarliestDelivery	= 0;
	
	public $intBaseCarrier;
	public $intBaseFileType;
	public $_strDeliveryType;
	
	public $_bolCanRunModule;
	
 	public $intCarrier;
 	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor
	 *
	 * Constructor
	 * 
	 * @param	integer		$intCarrier						Carrier to load this Module for
	 * @param	integer		$intModuleType					CarrierModule type
	 * @param	integer		$intCustomerGroup	[optional]	CustomerGroup to load this Module for (default: NULL)
	 * 
	 * @return	CarrierModule
	 *
	 * @method
	 */
 	function __construct($intCarrier, $intModuleType, $intCustomerGroup = NULL)
 	{
 		// Defaults
 		$this->_arrModuleConfig			= Array();
 		$this->_intModuleType			= $intModuleType;
 		$this->_intModuleCarrier		= $intCarrier;
 		$this->_intModuleCustomerGroup	= $intCustomerGroup;
 		
 		// Statements
		$this->_selCarrierModule	= new StatementSelect("CarrierModule", "*", "Carrier = <Carrier> AND Module = <Module> AND Type = <Type> AND customer_group = <CustomerGroup>");
		$this->_selModuleConfig		= new StatementSelect("CarrierModuleConfig", "*", "CarrierModule = <Id>");
		
	 	$arrCols					= Array();
	 	$arrCols['Value']			= NULL;
	 	$this->_ubiModuleConfig		= new StatementUpdateById("CarrierModuleConfig", $arrCols);
	 	
 		$arrCols	= Array();
 		$arrCols['LastSentOn']		= new MySQLFunction("NOW()");
 		$this->_ubiCarrierModule	= new StatementUpdateById("CarrierModule", $arrCols);
 		
 		// Load Config
 		$this->LoadModuleConfig();
 		
 		// Set CanRunModule variable
 		$this->bolCanRunModule		= $this->_CanRunModule(); 
 	}
 	
 	
 	//------------------------------------------------------------------------//
	// GetConfigField
	//------------------------------------------------------------------------//
	/**
	 * GetConfigField()
	 *
	 * Retrieves a reference to a Config field
	 * 
	 * Retrieves a reference to a Config field
	 * 
	 * @param	string	$strName					Field to return
	 * @param	string	$strParent		[optional]	Parent field that's reffering to this field
	 * 
	 * @return	&mixed								Pass/Fail
	 *
	 * @method
	 */
	 function &GetConfigField($strName, $strParent = NULL)
	 {	 	
	 	//CliEcho("Fetching Config Field '$strName' (Parent: '$strParent')");
	 	
	 	$mixValue	= &$this->_arrModuleConfig[$strName]['Value'];
	 	
	 	// Parse the value and fill in any recognised placeholders (this should only happen once)
	 	$arrResults	= Array();
	 	
	 	if (is_string($mixValue))
	 	{
	 		$intCount	= preg_match_all("/<(Config|Function|Property)::([A-Za-z]+)>/i", $mixValue, $arrResults, PREG_SET_ORDER);
	 	}
	 	
	 	//CliEcho("$intCount Placeholders found in '$mixValue'");
	 	
	 	foreach ($arrResults as $arrSet)
	 	{
	 		$strFullMatch	= $arrSet[0];
	 		$strContext		= $arrSet[1];
	 		$strAction		= $arrSet[2];
	 		
	 		//CliEcho("Working with Placeholder '$strFullMatch'");
	 		
	 		switch (strtolower($strContext))
	 		{
	 			case 'config':
	 				//CliEcho("Found CONFIG Placeholder : '$strAction'");
	 				
	 				// Check if this is an endless reference loop
	 				if ($strAction != $strParent)
	 				{
	 					// Get the referred config field
	 					$strReplace	= $this->GetConfigField($strAction, $strName);
	 				}
	 				else
	 				{
	 					// Endless loop
	 					$strReplace	= "<Error::Endless Reference Loop>";
	 					
	 					//CliEcho("Endless loop");
	 				}
	 				break;
	 			
	 			case 'function':
	 				//CliEcho("Found FUNCTION Placeholder : '$strAction'");
	 				switch (strtolower($strAction))
	 				{
	 					case 'datetime':
	 						$strReplace	= date("Y-m-d H:i:s");
	 						break;
	 					
	 					case 'date':
	 						$strReplace	= date("Y-m-d");
	 						break;
	 						
	 					default:
	 						// Unrecognised Function - ignore
	 						$strReplace	= "<Error::Unrecognised Function '$strAction'>";
	 						
	 						//CliEcho("Unrecognised Function '$strAction'");
	 						continue 3;
	 				}
	 				break;
	 			
	 			case 'property':
	 				//CliEcho("Found PROPERTY Placeholder : '$strAction'");
	 				switch (strtolower($strAction))
	 				{
	 					case 'customergroup':
	 						$strReplace	= GetConstantDescription($this->_intModuleCustomerGroup, 'CustomerGroup');
	 						break;
	 					
	 					case 'carrier':
	 						$strReplace	= GetConstantDescription($this->_intModuleCarrier, 'Carrier');
	 						break;
	 						
	 					default:
	 						// Unrecognised Function - ignore
	 						$strReplace	= "<Error::Unrecognised Property '$strAction'>";
	 						
	 						//CliEcho("Unrecognised Function '$strAction'");
	 						continue 3;
	 				}
	 				break;
	 				
	 			default:
	 				// Unrecognised - ignore
	 				continue 2;
	 		}
	 			 		
	 		// Fill the Placeholders
	 		$strOld		= $mixValue;
	 		$mixValue	= str_replace($strFullMatch, $strReplace, $mixValue);
	 		
	 		//CliEcho("OLD: '$strOld'; NEW: '$mixValue'");
	 	}
	 	
	 	// Return a reference to the value, so it can be modified
	 	return $this->_arrModuleConfig[$strName]['Value'];
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// _CanRunModule
	//------------------------------------------------------------------------//
	/**
	 * _CanRunModule()
	 *
	 * Checks to see if this module can be used at the moment
	 * 
	 * Checks to see if this module can be used at the moment
	 * 
	 * @return	bool							Pass/Fail
	 *
	 * @method
	 */
	 private function _CanRunModule()
	 {
	 	if ($this->_arrCarrierModule['LastSentOn'] === '0000-00-00 00:00:00')
	 	{
	 		$this->_arrCarrierModule['LastSentOn']	= '1985-10-20 10:54:00';
	 	}
	 	
	 	switch ($this->_arrCarrierModule['FrequencyType'])
	 	{
	 		case FREQUENCY_SECOND:
	 			$strAddTime		= 'seconds';
	 			$strTruncate	= 's';
	 			break;
	 			
	 		case FREQUENCY_MINUTE:
	 			$strAddTime		= 'minutes';
	 			$strTruncate	= 'i';
	 			break;
	 			
	 		case FREQUENCY_HOUR:
	 			$strAddTime		= 'hours';
	 			$strTruncate	= 'h';
	 			break;
	 			
	 		case FREQUENCY_DAY:
	 			$strAddTime		= 'days';
	 			$strTruncate	= 'd';
	 			break;
	 	}
	 	
	 	$intEarliestRun	= strtotime($this->_arrCarrierModule['LastSentOn']);
	 	$intEarliestRun	= TruncateTime($intEarliestRun, $strTruncate, 'floor');
	 	$intEarliestRun	= strtotime("+{$this->_arrCarrierModule['Frequency']} $strAddTime", $intEarliestRun);
	 	
	 	$intEarliestRun	= max($intEarliestRun, TruncateTime(time(), 'd', 'floor') + $this->_arrCarrierModule['EarliestDelivery']);
	 	
	 	return ($intEarliestRun > time()) ? FALSE : TRUE;
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// LoadModuleConfig
	//------------------------------------------------------------------------//
	/**
	 * LoadModuleConfig()
	 *
	 * Loads the Module's Config from the DB
	 * 
	 * Loads the Module's Config from the DB
	 * 
	 * @return	bool							Pass/Fail
	 *
	 * @method
	 */
	 function LoadModuleConfig()
	 {
	 	$arrWhere = Array();
	 	$arrWhere['Carrier']		= $this->_intModuleCarrier;
	 	$arrWhere['Module']			= get_class($this);
	 	$arrWhere['Type']			= $this->_intModuleType;
	 	$arrWhere['CustomerGroup']	= $this->_intModuleCustomerGroup;
	 	if ($this->_selCarrierModule->Execute($arrWhere))
	 	{
	 		$arrModule	= $this->_selCarrierModule->Fetch();
	 		
	 		// Keep a copy of the record
	 		$this->_arrCarrierModule	= $arrModule;			
	 		
	 		// Get the Config
	 		$this->_selModuleConfig->Execute($arrModule);
	 		while ($arrConfig = $this->_selModuleConfig->Fetch())
	 		{
	 			$this->_arrModuleConfig[$arrConfig['Name']]['Value']	= Module_Config::DecodeValue($arrConfig['Value'], $arrConfig['Type']);
	 			$this->_arrModuleConfig[$arrConfig['Name']]['Id']		= $arrConfig['Id'];
	 		}
	 		
	 		return TRUE;
	 	}
	 	else
	 	{
	 	 	// There was no config
	 	 	return FALSE;
	 	}
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// _SaveModuleConfig
	//------------------------------------------------------------------------//
	/**
	 * _SaveModuleConfig()
	 *
	 * Saves the Module's Config back to the DB
	 * 
	 * Saves the Module's Config back to the DB
	 * 
	 * @return	array					'Pass'			: TRUE/FALSE
	 * 									'Description'	: Error message
	 *
	 * @method
	 */
	 private function _SaveModuleConfig()
	 {
	 	$strError	= "";
	 	$bolFailed	= FALSE;
	 	foreach ($this->_arrModuleConfig as $strName=>$arrProperties)
	 	{
	 		if (!$arrProperties['AutoUpdate'])
	 		{
	 			// This field does not get updated
	 			continue;
	 		}
	 		
	 		$arrModuleConfig			= Array();
	 		$arrModuleConfig['Id']		= $arrProperties['Id'];
	 		$arrModuleConfig['Value']	= Module_Config::EncodeValue($arrProperties['Value'], $arrProperties['Type']);
	 		$mixResult					= $this->_ubiModuleConfig->Execute($arrModuleConfig);
	 		if ($mixResult === FALSE)
	 		{
	 			// Append Error to full message
	 			//CliEcho("Could not save field '$strName' (Id: {$arrProperties['Id']}) as value '{$arrProperties['Value']}' ({$arrModuleConfig['Value']})");
	 			$strError	.= "Could not save field '$strName' (Id: {$arrProperties['Id']}) as value '{$arrProperties['Value']}' ({$arrModuleConfig['Value']})\n";
	 			$bolFailed	= TRUE;
	 		}
	 		elseif (!$mixResult)
	 		{
	 			//CliEcho("Field '$strName' (Id: {$arrProperties['Id']}) was not updated with value '{$arrProperties['Value']}' ({$arrModuleConfig['Value']})");
	 			$strError	.= "Field '$strName' (Id: {$arrProperties['Id']}) was not updated with value '{$arrProperties['Value']}' ({$arrModuleConfig['Value']})\n";
	 		}
	 		else
	 		{
	 			//CliEcho("Successfully saved field '$strName' (Id: {$arrProperties['Id']}) as value '{$arrProperties['Value']}'");
	 			$strError	.= "Successfully saved field '$strName' (Id: {$arrProperties['Id']}) as value '{$arrProperties['Value']}' ({$arrModuleConfig['Value']})\n";
	 		}
	 	}
	 	$strError	= trim($strError);
	 	
	 	// If there is an error, then return the message, else TRUE
	 	return Array('Pass' => (!$bolFailed), 'Description' => $strError); 
	 	//return ($bolFailed) ? $strError : TRUE;
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// SaveModule
	//------------------------------------------------------------------------//
	/**
	 * SaveModule()
	 *
	 * Saves the Module back to the DB
	 * 
	 * Saves the Module back to the DB
	 * 
	 * @return	array					'Pass'			: TRUE/FALSE
	 * 									'Description'	: Error message
	 *
	 * @method
	 */
	 function SaveModule()
	 {
 		// Update CarrierModule
 		$arrCols	= Array();
 		$arrCols['Id']				= $this->_arrCarrierModule['Id'];
 		$arrCols['LastSentOn']		= new MySQLFunction("NOW()");
 		if ($this->_ubiCarrierModule->Execute($arrCols) === FALSE)
 		{
 			return Array('Pass' => FALSE, 'Description' => "Unable to update CarrierModule entry!");
 		}
 		
 		// Update Module Config
 		$arrResult	= $this->_SaveModuleConfig();
	 	
	 	// If there is an error, then return the message, else TRUE
	 	return $arrResult; 
	 }
 	
 	
 	//------------------------------------------------------------------------//
	// CreateModuleConfig
	//------------------------------------------------------------------------//
	/**
	 * CreateModuleConfig()
	 *
	 * Creates Module Config information in the CarrierModule and CarrierModuleConfig tables
	 * 
	 * Creates Module Config information in the CarrierModule and CarrierModuleConfig tables
	 * 
	 * @param	integer	$intCarrier		The Carrier to create this module for
	 * 
	 * @return	mixed					TRUE	: Config Created
	 * 									string	: Failure Reason
	 *
	 * @method
	 */
	 function CreateModuleConfig()
	 {
 		$intCarrier				= $this->_intModuleCarrier;
 		
	 	$insCarrierModule		= new StatementInsert("CarrierModule");
		$insCarrierModuleConfig	= new StatementInsert("CarrierModuleConfig");
		
	 	if (!GetConstantName($intCarrier, 'Carrier'))
	 	{
	 		// Invalid Carrier Specified
	 		return "Invalid Carrier '$intCarrier' Specified";
	 	}
	 	
	 	$arrWhere = Array();
	 	$arrWhere['Carrier']			= $intCarrier;
	 	$arrWhere['Module']				= get_class($this);
	 	$arrWhere['Type']				= $this->_intModuleType;
	 	$arrWhere['CustomerGroup']		= $this->_intModuleCustomerGroup;
	 	if (!$this->_selCarrierModule->Execute($arrWhere))
	 	{
			// Insert the CarrierModule data
			$arrCarrierModule						= Array();
	 		$arrCarrierModule['Carrier']			= $intCarrier;
	 		$arrCarrierModule['Type']				= $this->_intModuleType;
	 		$arrCarrierModule['Module']				= get_class($this);
	 		$arrCarrierModule['FileType']			= $this->intBaseFileType;
	 		$arrCarrierModule['Active']				= 0;
		 	$arrCarrierModule['FrequencyType']		= $this->_intFrequencyType;
		 	$arrCarrierModule['Frequency']			= $this->_intFrequency;
		 	$arrCarrierModule['LastSentOn']			= '0000-00-00 00:00:00';
		 	$arrCarrierModule['EarliestDelivery']	= $this->_intEarliestDelivery;
		 	$arrCarrierModule['customer_group']		= $this->_intModuleCustomerGroup;
	 		if (!$intCarrierModule = $insCarrierModule->Execute($arrCarrierModule))
	 		{
	 			return "MySQL Error: ".$insCarrierModule->Error();
	 		}
			
			// Insert the CarrierModuleConfig data
			$strError	= "";
			foreach ($this->_arrModuleConfig as $strField=>$arrProperties)
			{
				$arrModuleConfig	= Array();
				$arrModuleConfig['CarrierModule']	= $intCarrierModule;
				$arrModuleConfig['Name']			= $strField;
				$arrModuleConfig['Type']			= $arrProperties['Type'];
				$arrModuleConfig['Value']			= Module_Config::EncodeValue($arrProperties['Default'], $arrProperties['Type']);
				$arrModuleConfig['Description']		= $arrProperties['Description'];
				if (!$insCarrierModuleConfig->Execute($arrModuleConfig))
				{
					$strError .= $insCarrierModuleConfig->Error()."\n";
				}
			}
			
			return ($strError) ? trim($strError) : TRUE;
			
	 	}
	 	else
	 	{
	 		return "The Module '".get_class($this)."' already exists for Carrier '$intCarrier'";
	 	}
	 }
	 
 	//------------------------------------------------------------------------//
	// GetCarrier
	//------------------------------------------------------------------------//
	/**
	 * GetCarrier()
	 *
	 * Returns the Carrier that is implementing this Module
	 * 
	 * Returns the Carrier that is implementing this Module
	 *  
	 * @return	integer								Carrier
	 *
	 * @method
	 */
	 public function GetCarrier()
	 {
	 	return $this->_intModuleCarrier;
	 }
	 
 	//------------------------------------------------------------------------//
	// GetCustomerGroup
	//------------------------------------------------------------------------//
	/**
	 * GetCustomerGroup()
	 *
	 * Returns the CustomerGroup that is implementing this Module
	 * 
	 * Returns the CustomerGroup that is implementing this Module
	 *  
	 * @return	integer								int: CustomerGroup; NULL: Available to all CustomerGroups
	 *
	 * @method
	 */
	 public function GetCustomerGroup()
	 {
	 	return $this->_intModuleCustomerGroup;
	 }
}

?>
