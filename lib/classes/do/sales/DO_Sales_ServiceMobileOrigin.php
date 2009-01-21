<?php

class DO_Sales_ServiceMobileOrigin extends DO_Sales_Base_ServiceMobileOrigin
{
	protected static $_cache = NULL;

	// Returns array of all the objects, with the id of the records as the key
	public static function listAll($bolForceRefresh=FALSE)
	{
		if (!isset(self::$_cache) || $bolForceRefresh)
		{
			$arrProps		= self::getPropertyDataSourceMappings();
			$arrOrigins		= self::getFor(NULL, TRUE, "{$arrProps['name']} ASC");
			self::$_cache	= array();
			foreach ($arrOrigins as $doServiceMobileOrigin)
			{
				self::$_cache[$doServiceMobileOrigin->id] = $doServiceMobileOrigin;
			}
		}
		
		return self::$_cache;
	}
	
	public static function getForId($intId)
	{
		$arrOrigins = self::listAll();
		return (array_key_exists($intId, $arrOrigins))? $arrOrigins[$intId] : NULL;
	}
	
}

?>