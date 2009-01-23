<?php

class DO_Sales_SaleType extends DO_Sales_Base_SaleType
{
	const NEW_CUSTOMER		= 1;
	const EXISTING_CUSTOMER	= 2;
	const WIN_BACK			= 3;
	
	protected static $_cache = NULL;
	
	// Returns array of all the objects, with the id of the records as the key
	public static function getAll($bolForceRefresh=FALSE)
	{
		if (!isset(self::$_cache) || $bolForceRefresh)
		{
			$arrProps		= self::getPropertyDataSourceMappings();
			$arrSaleTypes	= self::getFor(NULL, TRUE, "{$arrProps['name']} ASC");
			self::$_cache	= array();
			foreach ($arrSaleTypes as $objSaleType)
			{
				self::$_cache[$objSaleType->id] = $objSaleType;
			}
			 
		}
		
		return self::$_cache;
	}
	
	public static function getForId($intId)
	{
		$arrSaleTypes = self::getAll();
		return (array_key_exists($intId, $arrSaleTypes))? $arrSaleTypes[$intId] : NULL;
	}
}

?>