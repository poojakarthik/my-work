<?php

class DO_Sales_SaleStatus extends DO_Sales_Base_SaleStatus
{
	const SUBMITTED					= 1;
	const VERIFIED					= 2;
	const REJECTED					= 3;
	const CANCELLED					= 4;
	const READY_FOR_PROVISIONING	= 5;
	const PROVISIONED				= 6;
	const MANUAL_INTERVENTION		= 7;

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