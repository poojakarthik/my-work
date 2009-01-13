<?php

// Wraps up time functionality, queried from a datasource
class Data_Source_Time
{
	private static $_arrCachedTimestamps = array();
	
	public static function formatTime($strISODateTime, $strFormat)
	{
		return date($strFormat, strtotime($strISODateTime));
	}
	
	// Returns the current timestamp from the database, in ISO datetime format, ommiting fractions of a second (YYYY-MM-DD HH:MM:SS)
	// Note that we only cache the timestamp if $objDataSource->getName() is not NULL
	public static function currentTimestamp($objDataSource=NULL, $bolForceRefresh=FALSE, $bolUpdateCache=FALSE)
	{
		if ($objDataSource === NULL)
		{
			$objDataSource = Data_Source::get();
		}
		$strName = method_exists($objDataSource, "getName") ? $objDataSource->getName() : NULL;

		if ($bolForceRefresh || ($strName === NULL) || !array_key_exists($strName, self::$_arrCachedTimestamps))
		{
			// Retrieve a fresh value for "Current" time
			$strTime = $objDataSource->queryOne("SELECT NOW();");
			if (PEAR::isError($strTime))
			{
				throw new Exception("Could not retrieve current time from data source - ". $strTime->getMessage());
			}
			
			// Truncate the fractions of a second, if it is specified, and the GMT offset;
			// (YYYY-MM-DD HH:MM:SS is 19 chars long)
			$strTime = substr($strTime, 0, 19);
			
			if ($strName !== NULL)
			{
				// We must be retrieving the time for this named data source for the first time.  ALWAYS update the cache
				$bolUpdateCache = TRUE;
			}
		}
		else
		{
			// Retrieve the cached "Current" time
			$strTime = self::$_arrCachedTimestamps[$strName];
		}
		
		if ($bolUpdateCache)
		{
			self::$_arrCachedTimestamps[$strName] = $strTime;
		}
		
		return $strTime;
	}
	
	// Returns the current date, from the data source (YYYY-MM-DD)
	public static function currentDate($objDataSource=NULL, $bolForceRefresh=FALSE, $bolUpdateCache=FALSE)
	{
		$strTime = self::currentTimestamp($objDataSource, $bolForceRefresh, $bolUpdateCache);
		
		// Truncate the time of day part from the timestamp
		return substr($strTime, 0, 10);
	}

	// Returns the current time of day, from the data source (HH:MM:SS)	
	public static function currentTimeOfDay($objDataSource=NULL, $bolForceRefresh=FALSE, $bolUpdateCache=FALSE)
	{
		$strTime = self::currentTimestamp($objDataSource, $bolForceRefresh, $bolUpdateCache);
		$arrTimeParts = explode(' ', $strTime);
		return substr($arrTimeParts[1], 0, 8);
	}
	
}

?>
