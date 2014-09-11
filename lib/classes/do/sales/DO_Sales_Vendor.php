<?php

class DO_Sales_Vendor extends DO_Sales_Base_Vendor
{
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

	// Returns the time at which the cooling off period ends, based on the verifiedOn Timestamp
	public function getEndOfCoolingOffPeriodTimestamp($strVerifiedOnTimestamp)
	{
		if ($this->coolingOffPeriod === NULL)
		{
			// There is no cooling off period for this vendor
			return NULL;
		}
		else
		{
			return date("Y-m-d H:i:s", strtotime("+ {$this->coolingOffPeriod} hours $strVerifiedOnTimestamp"));
		}
	}
}

?>