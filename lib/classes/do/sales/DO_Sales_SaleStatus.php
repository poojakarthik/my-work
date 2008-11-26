<?php

class DO_Sales_SaleStatus extends DO_Sales_Base_SaleStatus
{
	const SUBMITTED					= 1;
	const VERIFIED					= 2;
	const REJECTED					= 3;
	const CANCELLED					= 4;
	const AWAITING_DISPATCH			= 5;
	const DISPATCHED				= 6;
	const MANUAL_INTERVENTION		= 7;
	const COMPLETED					= 8;

	protected static $_cache = NULL;
	
	// Returns array of all the objects, with the id of the records as the key
	public static function getAll($bolForceRefresh=FALSE)
	{
		if (!isset(self::$_cache) || $bolForceRefresh)
		{
			$arrProps		= self::getPropertyDataSourceMappings();
			$arrStatuses	= self::getFor(NULL, TRUE, "{$arrProps['name']} ASC");
			self::$_cache	= array();
			foreach ($arrStatuses as $objStatus)
			{
				self::$_cache[$objStatus->id] = $objStatus;
			}
			 
		}
		
		return self::$_cache;
	}
	
	public static function getForId($intId)
	{
		$arrStatuses = self::getAll();
		return (array_key_exists($intId, $arrStatuses))? $arrStatuses[$intId] : NULL;
	}
}

?>