<?php

class DO_Exception_ObjectNotFoundInDataSource extends Exception
{
	/*
	 * __construct()
	 *
	 * constructor
	 * 
	 * constructor
	 * 
	 * @param	string	$strDataSourceName		The name of the data source 
	 * @param	string	$strObjectName			The name of the type of object
	 * @param	string	$strObjectKeyName		The name of identifying key for this type of object in the data source
	 * @param	string	$strObjectKeyValue		The name of the type of object
	 * 
	 * @return	void
	 * 
	 * @constructor
	 */
	public function __construct($strDataSourceName, $strObjectName, $strObjectKeyName, $strObjectKeyValue)
	{
		parent::__construct("Could not find '{$strObjectName}' object in the '{$strDataSourceName}' data source where '{$strObjectKeyName}' == '{$strObjectKeyValue}'");
	}
}

?>