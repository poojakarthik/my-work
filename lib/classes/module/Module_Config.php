<?php

//----------------------------------------------------------------------------//
// Module_Config
//----------------------------------------------------------------------------//
/**
 * Module_Config
 *
 * Handles various Module Configuration schemas in Flex
 *
 * Handles various Module Configuration schemas in Flex
 *
 * @prefix	cfg
 * @package	lib.classes.module
 * @class	Module_Config
 */
class Module_Config
{
	protected	$_arrModuleConfig	= Array();
	public		$strTable;
	public		$strForeignKey;
	public		$intModuleId;
	
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
	 * @param	string	$strTable					Name of the Table to use
	 * @param	string	$strForeignKey				Name of the Foreign Key column in Table
	 * @param	integer	$intModuleId				Id of the Module who's config we're dealing with
	 * @param	array	$arrConfigDefinition		Config Definition Array
	 * 
	 * @return	Module_Config
	 *
	 * @method
	 */
	public function __construct($strTable, $strForeignKey, $intModuleId, $arrConfigDefinition)
	{
		$this->strTable			= $strTable;
		$this->strForeignKey	= $strForeignKey;
		$this->intModuleId		= $intModuleId;
		$this->_arrModuleConfig	= $arrConfigDefinition;
		
		// Load the Module Config
		$selModuleConfig	= new StatementSelect($strTable, "*", "{$strForeignKey} = <ModuleId>");
		if ($selModuleConfig->Execute(Array('ModuleId' => $this->intModuleId)))
		{
			while ($arrConfig = $selModuleConfig->Fetch())
			{
				$this->_arrModuleConfig[$arrConfig['name']]['Value']	= self::DecodeValue($arrConfig['value'], $arrConfig['type']);
				$this->_arrModuleConfig[$arrConfig['name']]['Id']		= $arrConfig['id'];
				$this->_arrModuleConfig[$arrConfig['name']]['Parsed']	= FALSE;
			}
		}
		elseif ($selModuleConfig->Error())
		{
			throw new Exception("DB ERROR: ".$selModuleConfig->Error());
		}
	}
	
	
	//------------------------------------------------------------------------//
	// __get
	//------------------------------------------------------------------------//
	/**
	 * __get()
	 *
	 * Magic GET function
	 *
	 * Magic GET function
	 * 
	 * @param	string	$strName					Name of the Property to retrieve
	 * 
	 * @return	mixed
	 *
	 * @method
	 */
	public function __get($strName)
	{
		if (isset($this->_arrModuleConfig[$strName]))
		{
			if (!$this->_arrModuleConfig[$strName]['Parsed'])
			{
				// Parse the field for any placeholders
				$this->_arrModuleConfig[$strName]['Value']	= $this->_ParseField($strName);
				$this->_arrModuleConfig[$strName]['Parsed']	= TRUE;
			}
			return $this->_arrModuleConfig[$strName]['Value'];
		}
		else
		{
			throw new Exception("ERROR: Module_Config.{$strName} is not a valid Property");
		}
	}
	
	
	//------------------------------------------------------------------------//
	// __set
	//------------------------------------------------------------------------//
	/**
	 * __set()
	 *
	 * Magic SET function
	 *
	 * Magic SET function
	 * 
	 * @param	string	$strName					Name of the Property to modify
	 * @param	mixed	$mixValue					The new value for the Property
	 * 
	 * @return	Module_Config
	 *
	 * @method
	 */
	public function __set($strName, $mixValue)
	{
		if (isset($this->_arrModuleConfig[$strName]))
		{
			$this->_arrModuleConfig[$strName]['Value']	= FlexCast($mixValue, $this->_arrModuleConfig[$strName]['Type']);
			$this->_arrModuleConfig[$strName]['Value']	= $this->_ParseField($strName);
		}
		else
		{
			throw new Exception("ERROR: Module_Config.{$strName} is not a valid Property");
		}
	}
	
	
	//------------------------------------------------------------------------//
	// Save
	//------------------------------------------------------------------------//
	/**
	 * Save()
	 *
	 * Saves the Module Config back to the DB
	 * 
	 * Saves the Module Config back to the DB
	 *
	 * @method
	 */
	public function Save()
	{
		static	$ubiModuleConfig;
		$ubiModuleConfig	= (isset($ubiModuleConfig)) ? $ubiModuleConfig : new StatementUpdateById($this->strTable, Array('Value' => NULL));
		
		// Save each field
		foreach ($this->_arrModuleConfig as $strName=>$arrProperties)
		{
			// Do not save AutoUpdate fields
			if (!$arrProperties['AutoUpdate'])
			{
				continue;
			}
			
			$arrModuleConfig			= Array();
			$arrModuleConfig['Id']		= $arrProperties['Id'];
			$arrModuleConfig['Value']	= Module_Config::EncodeValue($arrProperties['Value'], $arrProperties['Type']);
			$mixResult					= $ubiModuleConfig->Execute($arrModuleConfig);
			if ($mixResult === FALSE)
			{
				// DB Error
				throw new Exception("DB ERROR: ".$ubiModuleConfig->Error());
			}
		}
	}
	
	
	//------------------------------------------------------------------------//
	// _ParseField
	//------------------------------------------------------------------------//
	/**
	 * _ParseField()
	 *
	 * Parses a Config Field and replaces any placeholders
	 * 
	 * Parses a Config Field and replaces any placeholders
	 * 
	 * @param	string	$strName					Field to return
	 * @param	string	$strParent		[optional]	Parent field that's reffering to this field
	 * 
	 * @return	mixed								Parsed value
	 *
	 * @method
	 */
	 private function _ParseField($strName, $strParent = NULL)
	 {
		$mixValue	= &$this->_arrModuleConfig[$strName]['Value'];
		
		// Parse the value and fill in any recognised placeholders (this should only happen once)
		if (is_string($mixValue))
		{
			$arrResults	= Array();
			$intCount	= preg_match_all("/<(Config|Function|Property)::([A-Za-z]+)>/i", $mixValue, $arrResults, PREG_SET_ORDER);
			
			foreach ($arrResults as $arrSet)
			{
				$strFullMatch	= $arrSet[0];
				$strContext		= $arrSet[1];
				$strAction		= $arrSet[2];
				
				switch (strtolower($strContext))
				{
					case 'config':
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
						}
						break;
					
					case 'function':
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
								continue 3;
						}
						break;
					
					case 'property':
						switch (strtolower($strAction))
						{
							case 'customergroup':
								$strReplace	= Customer_Group::getForId($this->_intModuleCustomerGroup)->externalName;
								break;
							
							case 'carrier':
								$strReplace	= GetConstantDescription($this->_intModuleCarrier, 'Carrier');
								break;
								
							default:
								// Unrecognised Function - ignore
								$strReplace	= "<Error::Unrecognised Property '$strAction'>";
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
			}
		}
		
		return $mixValue;
	 }
	
	
	//------------------------------------------------------------------------//
	// Create
	//------------------------------------------------------------------------//
	/**
	 * Create()
	 *
	 * Creates Module Config information in the DB
	 * 
	 * Creates Module Config information in the DB
	 * 
	 * @param	string	$strTable					Name of the Table to use
	 * @param	string	$strForeignKey				Name of the Foreign Key column in Table
	 * @param	integer	$intModuleId				Id of the Module who's config we're dealing with
	 * @param	array	$arrConfigDefinition		Config Definition Array
	 * 
	 * @return	boolean							Pass/Fail
	 *
	 * @method
	 */
	static public function Create($strTable, $strForeignKey, $intModuleId, $arrConfigDefinition)
	 {
		$intModuleConfig	= new StatementInsert($strTable);
		
		// Insert the Module Config data
		foreach ($arrConfigDefinition as $strField=>$arrProperties)
		{
			$arrModuleConfig	= Array();
			$arrModuleConfig[$strForeignKey]	= $intModuleId;
			$arrModuleConfig['name']			= $strField;
			$arrModuleConfig['data_type_id']	= $arrProperties['Type'];
			$arrModuleConfig['value']			= self::EncodeValue($arrProperties['Default'], $arrProperties['Type']);
			$arrModuleConfig['description']		= $arrProperties['Description'];
			
			$intId	= $intModuleConfig->Execute($arrModuleConfig);
			if (!$intId)
			{
				throw new Exception("DB ERROR: ".$intModuleConfig->Error());
			}
		}
		
		// Return the Insert Id
		return TRUE;
	 }
	
	//------------------------------------------------------------------------//
	// DecodeValue
	//------------------------------------------------------------------------//
	/**
	 * DecodeValue()
	 *
	 * Converts a Module Config Field to its PHP counterpart
	 * 
	 * Converts a Module Config Field to its PHP counterpart
	 * 
	 * @param	string	$strValue					DB Value to convert to PHP variable
	 * @param	integer	$intDataType	[optional]	Data Type to cast; Default: DATA_TYPE_STRING
	 * 
	 * @return	mixed								Cast value 
	 *
	 * @method
	 */
	public static function DecodeValue($strValue, $intDataType = DATA_TYPE_STRING)
	{
		$mixValue	= NULL;
		switch ($intDataType)
		{
			
			case DATA_TYPE_INTEGER:
				$mixValue	= (int)$strValue;
				break;
			
			case DATA_TYPE_FLOAT:
				$mixValue	= (float)$strValue;
				break;
			
			case DATA_TYPE_BOOLEAN:
				$mixValue	= (bool)$strValue;
				break;
			
			case DATA_TYPE_SERIALISED:
			case DATA_TYPE_ARRAY:
				$mixValue	= unserialize($strValue);
				break;
			
			default:
			case DATA_TYPE_STRING:
				$mixValue	= (string)$strValue;
			break;
		}
		
		return $mixValue;
	}
	 
	//------------------------------------------------------------------------//
	// EncodeValue
	//------------------------------------------------------------------------//
	/**
	 * EncodeValue()
	 *
	 * Converts a PHP value to its Module Config counterpart
	 * 
	 * Converts a PHP value to its Module Config counterpart
	 * 
	 * @param	mixed	$mixValue					PHP value to convert to DB
	 * @param	integer	$intDataType	[optional]	Data Type to cast; Default: DATA_TYPE_STRING
	 * 
	 * @return	mixed								Cast value
	 *
	 * @method
	 */
	public static function EncodeValue($mixValue, $intDataType = DATA_TYPE_STRING)
	{
		switch ($intDataType)
		{
			case DATA_TYPE_SERIALISED:
			case DATA_TYPE_ARRAY:
				$mixValue	= serialize($mixValue);
				break;
			
			default:
			case DATA_TYPE_STRING:
			case DATA_TYPE_INTEGER:
			case DATA_TYPE_FLOAT:
			case DATA_TYPE_BOOLEAN:
				$mixValue	= (string)$mixValue;
				break;
		}
		
		return $mixValue;
	}
}

?>