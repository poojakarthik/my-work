<?php

//----------------------------------------------------------------------------//
// Singleton
//----------------------------------------------------------------------------//
/**
 * Singleton
 *
 * Singleton Static Class
 *
 * Singleton Static Class
 *
 * @package	framework
 * @class	Singleton
 */
class Singleton
{
	// Hold an instance of the class
	private static $arrInstance;
	
 	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Inaccessible Private Constructor
	 *
	 * Inaccessible Private Constructor

	 * @return	bool
	 *
	 * @method
	 */ 
	private function __construct()
	{
		return FALSE;
	}
	

 	//------------------------------------------------------------------------//
	// Instance
	//------------------------------------------------------------------------//
	/**
	 * Instance()
	 *
	 * Returns an instance of the specified class
	 *
	 * Returns an instance of the specified class

	 * @return	mixed
	 *
	 * @method
	 */ 
	public static function Instance($strClass)
	{
		if (!isset(self::$arrInstance[$strClass]))
		{
			self::$arrInstance[$strClass] = new $strClass;
		}
		
		return self::$arrInstance[$strClass];
	}
	
 	//------------------------------------------------------------------------//
	// __clone
	//------------------------------------------------------------------------//
	/**
	 * __clone()
	 *
	 * Prevents cloning
	 *
	 * Prevents cloning
	 *
	 * @method
	 */ 
	public function __clone()
	{
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}

}

?>
