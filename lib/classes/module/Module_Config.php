<?php

//----------------------------------------------------------------------------//
// Module_Config
//----------------------------------------------------------------------------//
/**
 * Module_Config
 *
 * Helper Class for Module Configuration Tables
 *
 * Helper Class for Module Configuration Tables (eg. CarrierModuleConfig, billing_charge_module_config)
 *
 * @package	lib.classes.module
 * @class	Module_Config
 */
class Module_Config
{ 	
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
	 * @return	Module_Config
	 *
	 * @method
	 */
 	private function __construct()
 	{
 		// This class should not be instanciated
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
	 static function DecodeValue($strValue, $intDataType = DATA_TYPE_STRING)
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
	 static function EncodeValue($mixValue, $intDataType = DATA_TYPE_STRING)
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