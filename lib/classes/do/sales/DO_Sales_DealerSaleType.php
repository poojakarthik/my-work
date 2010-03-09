<?php

class DO_Sales_DealerSaleType extends DO_Sales_Base_DealerSaleType
{
	protected static $_cache = NULL;
	
	// Returns array of all the objects, with the id of the records as the key
	public static function getAll($bolForceRefresh=FALSE)
	{
		if (!isset(self::$_cache) || $bolForceRefresh)
		{
			$arrProps			= self::getPropertyDataSourceMappings();
			$arrDealerSaleTypes	= self::getFor(NULL, TRUE, "{$arrProps['name']} ASC");
			self::$_cache	= array();
			foreach ($arrDealerSaleTypes as $objDealerSaleType)
			{
				self::$_cache[$objDealerSaleType->id] = $objDealerSaleType;
			}
			 
		}
		
		return self::$_cache;
	}
	
	public static function getForId($intId)
	{
		$arrDealerSaleTypes = self::getAll();
		return (array_key_exists($intId, $arrDealerSaleTypes))? $arrDealerSaleTypes[$intId] : NULL;
	}
	
	public static function listForDealerId($dealerId)
	{
		return self::getFor(array('dealerId' => (int)$dealerId), true);
	}
}

?>