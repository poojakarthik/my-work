<?php
/**
 * Flex_Date
 *
 * Date helper functions
 *
 * @class	Flex_Date
 */
class Flex_Date
{
	const	SECONDS_IN_MINUTE	= 60;
	const	SECONDS_IN_HOUR		= 3600;
	const	SECONDS_IN_DAY		= 86400;
	
	const	MINUTES_IN_DAY		= 1440;
	const	MINUTES_IN_HOUR		= 60;
	
	const	HOURS_IN_DAY		= 24;
	
	const	MONTHS_IN_YEAR		= 12;
	
	// Private Constructors to enforce Static-ness
	private function __construct(){}
	private function Flex_Date(){}
	
	/**
	 * difference()
	 *
	 * Calculates the difference between two dates
	 * 
	 * @param	string	$strEarlierDate				Earlier Date 
	 * @param	string	$strLaterDate				Later Date
	 * @param	string	$strAccuracy				Difference units.  Accepts 'y', 'm', 'd', 'h', 'i', or 's'. (Default: 's')
	 * @param	boolean	$bolCeil					TRUE	: Difference is rounded up to the nearest unit
	 * 												FALSE	: Difference is rounded down to the nearest unit
	 * 												NULL	: Difference is rounded to the nearest unit (Default)
	 *
	 * @return	integer								Units separating the two dates
	 *
	 * @method
	 */
	public static function difference($strEarlierDate, $strLaterDate, $strInterval='s', $bolCeil=null)
	{
		if ($bolCeil === true)
		{
			$strRoundingFunction	= 'ceil';
		}
		elseif ($bolCeil === false)
		{
			$strRoundingFunction	= 'floor';
		}
		else
		{
			$strRoundingFunction	= 'round';
		}
		
		$intEarlierTime	= strtotime($strEarlierDate);
		if ($intEarlierTime === false)
		{
			throw new Exception("'{$strEarlierDate}' is not a valid UNIX Date");
		}
		$intLaterTime	= strtotime($strLaterDate);
		if ($intLaterTime === false)
		{
			throw new Exception("'{$strLaterDate}' is not a valid UNIX Date");
		}
		
		// Determine total difference in seconds
		$intTotalDifferenceInSeconds	= $intLaterTime - $intEarlierTime;
		
		if ($strInterval === 's')
		{
			return $intTotalDifferenceInSeconds;
		}
		
		// Minutes
		$fltTotalDifferenceInMinutes	= $intTotalDifferenceInSeconds / self::SECONDS_IN_MINUTE;
		if ($strInterval === 'i')
		{
			return call_user_func($strRoundingFunction, $fltTotalDifferenceInMinutes);
		}
		
		// Hours
		$fltTotalDifferenceInHours	= $intTotalDifferenceInSeconds / self::SECONDS_IN_HOUR;
		if ($strInterval === 'h')
		{
			return call_user_func($strRoundingFunction, $fltTotalDifferenceInHours);
		}
		
		// Days
		$fltTotalDifferenceInDays	= $intTotalDifferenceInSeconds / self::SECONDS_IN_DAY;
		if ($strInterval === 'd')
		{
			return call_user_func($strRoundingFunction, $fltTotalDifferenceInDays);
		}
		
		$intYearEarlier			= (int)date("Y", $intEarlierTime);
		$intYearLater			= (int)date("Y", $intLaterTime);
		
		$intMonthEarlier		= (int)date("m", $intEarlierTime);
		$intMonthLater			= (int)date("m", $intLaterTime);
		
		$intEarlierMonthDays	= (int)date('t', mktime(0, 0, 0, $intMonthEarlier, 1, $intYearEarlier));
		$intLaterMonthDays		= (int)date('t', mktime(0, 0, 0, $intMonthLater, 1, $intYearLater));
		
		$intEarlierTimeOffset	= strtotime(date("1970-01-01 H:i:s", $intEarlierTime));
		$intLaterTimeOffset		= strtotime(date("1970-01-01 H:i:s", $intLaterTime));
		
		// Months
		$intMonths				= 0;
		$intMonthsInDays		= 0;
		$intEarlierMonthOffset	= ($intYearEarlier * 12)	+ $intMonthEarlier;
		$intLaterMonthOffset	= ($intYearLater * 12)		+ $intMonthLater;
		$arrMonthOffsets		= range($intEarlierMonthOffset, $intLaterMonthOffset);
		foreach ($arrMonthOffsets as $intMonthOffset)
		{
			$intMonths++;
			$intYear			= floor($intMonthOffset / 12);
			$intMonth			= $intMonthOffset % 12;
			$intDaysInMonth		= (int)date('t', mktime(0, 0, 0, $intMonth, 1, $intYear));
			$intMonthsInDays	+= $intDaysInMonth;
		}
		$intTotalDifferenceInSeconds % self::SECONDS_IN_DAY;
		$intMonthsInSeconds	= $intMonthsInDays * self::SECONDS_IN_DAY;
		
		
		// Interval Conversion multi-dimensional array
		//	$arrIntervalConversion['m']['y']	= 36
		//	This means that the difference in years, converted to months, is 36 (ie. 3 years = 36 months)
		$arrIntervalConversion	= array();
		
		// Seconds
		$intSecondEarlier		= (int)date("s", $intEarlierTime);
		$intSecondLater			= (int)date("s", $intLaterTime);
		$intSecondDifference	= $intSecondLater - $intSecondEarlier;
		$intSecondDifference	= ($intSecondDifference > 0) ? $intSecondDifference : self::SECONDS_IN_MINUTE + $intSecondDifference;
		
		// Minutes
		$intMinuteEarlier		= (int)date("i", $intEarlierTime);
		$intMinuteLater			= (int)date("i", $intLaterTime);
		$intMinuteDifference	= $intMinuteLater - $intMinuteEarlier;
		$intMinuteDifference	= ($intMinuteDifference > 0) ? $intMinuteDifference : self::MINUTES_IN_HOUR + $intMinuteDifference;
		
		// Hours
		$intHourEarlier			= (int)date("H", $intEarlierTime);
		$intHourLater			= (int)date("H", $intLaterTime);
		$intHourDifference		= $intHourEarlier - $intHourEarlier;
		$intHourDifference		= ($intHourDifference > 0) ? $intHourDifference : self::HOURS_IN_DAY + $intHourDifference;
		
		// Days
		$intDayEarlier			= (int)date("d", $intEarlierTime);
		$intDayLater			= (int)date("d", $intLaterTime);
		$intDayDifference		= max(0, $intDayLater-$intDayEarlier);
		
		// Months
		$intMonthEarlier		= (int)date("m", $intEarlierTime);
		$intMonthLater			= (int)date("m", $intLaterTime);
		$intMonthDifference		= max(0, $intMonthLater-$intMonthEarlier);
				
		// Years
		$intYearEarlier			= (int)date("Y", $intEarlierTime);
		$intYearLater			= (int)date("Y", $intLaterTime);
		$intYearDifference		= max(0, $intYearLater-$intYearEarlier);
		
		// Months in Days
		$intMonthsInDays		= 0;
		$intEarlierMonthOffset	= ($intYearEarlier * 12) + $intMonthEarlier;
		$intLaterMonthOffset	= ($intYearLater * 12) + $intMonthLater;
		$arrMonthOffsets		= range($intEarlierMonthOffset, $intLaterMonthOffset);
		foreach ($arrMonthOffsets as $intMonthOffset)
		{
			$intYear			= floor($intMonthOffset / 12);
			$intMonth			= $intMonthOffset % 12;
			$intDaysInMonth		= (int)date('t', mktime(0, 0, 0, $intMonth, 1, $intYear));
			$intMonthsInDays	+= $intDaysInMonth;
		}
		
		// Years in Days
		$arrIntervalConversion['d']['y']	= 0;
		$arrYears		= range($intYearEarlier, $intYearLater);
		foreach ($arrYears as $intYear)
		{
			if ($intYear % 4 === 0 && ($intYear % 100 || $intYear % 400 === 0))
			{
				$arrIntervalConversion['d']['y']	+= 365;
			}
			else
			{
				$arrIntervalConversion['d']['y']	+= 366;
			}
		}
		
		
		
		/*
		
		$arrIntervalConversion['y']['s']	= $intSecondDifference;
		$arrIntervalConversion['y']['i']	= $intSecondDifference;
		$arrIntervalConversion['y']['h']	= $intSecondDifference;
		$arrIntervalConversion['y']['d']	= $intSecondDifference;
		$arrIntervalConversion['y']['m']	= $intSecondDifference;
		$arrIntervalConversion['y']['y']	= $intSecondDifference;
		
		
		
		
		
		$arrIntervalConversion['y']['y']	= $intYearDifference;
		$arrIntervalConversion['m']['y']	= $intYearDifference * self::MONTHS_IN_YEAR;
		$arrIntervalConversion['h']['y']	= $arrIntervalConversion['d']['y'] * self::HOURS_IN_DAY;
		$arrIntervalConversion['i']['y']	= $arrIntervalConversion['d']['y'] * self::MINUTES_IN_DAY;
		$arrIntervalConversion['s']['y']	= $arrIntervalConversion['d']['y'] * self::SECONDS_IN_DAY;
		
		
		$arrIntervalConversion['y']['m']	= ($bolCeil) ? self::MONTHS_IN_YEAR : (($bolCeil === false) ? 0 : round($intMonthDifference / 100) * 100);
		$arrIntervalConversion['m']['m']	= $intMonthDifference;
		$arrIntervalConversion['d']['m']	= $intMonthsInDays;
		$arrIntervalConversion['h']['m']	= $intMonthsInDays * self::HOURS_IN_DAY;
		$arrIntervalConversion['i']['m']	= $intMonthsInDays * self::MINUTES_IN_DAY;
		$arrIntervalConversion['s']['m']	= $intMonthsInDays * self::SECONDS_IN_DAY;
		
		// Minutes
		$intMinuteEarlier		= (int)date("i", $intEarlierTime);
		$intMinuteLater			= (int)date("i", $intLaterTime);
		$intMinuteDifference	= max(0, $intMinuteLater-$intMinuteEarlier);
		
		$intSecondsInMinutes	= ($bolCeil) ? self::SECONDS_IN_MINUTE : (($bolCeil === false) ? 0 : round($intSecondDifference / 100) * 100);
		*/
		return	$arrIntervalConversion[strtolower($strInterval)]['y'] + 
				$arrIntervalConversion[strtolower($strInterval)]['m'] + 
				$arrIntervalConversion[strtolower($strInterval)]['d'] + 
				$arrIntervalConversion[strtolower($strInterval)]['h'] + 
				$arrIntervalConversion[strtolower($strInterval)]['i'] + 
				$arrIntervalConversion[strtolower($strInterval)]['s'];
	}
	
	/**
	 * truncate()
	 *
	 * Truncates a Date to a specified degree of accuracy
	 * 
	 * @param	string	$strDate					The Date to truncate
	 * @param	string	$strAccuracy				Where to truncate the timestamp.  Accepts 'y', 'm', 'd', 'h', 'i', or 's'.
	 * @param	string	$strRound					TRUE	: Date is rounded up
	 * 												FALSE	: Date is rounded down
	 *
	 * @return	integer								Truncated Timestamp
	 *
	 * @method
	 */
	public static function truncate($strDate, $strAccuracy, $bolCeil)
	{
		$intTime	= strtotime($strDate);
		if ($intTime === false)
		{
			throw new Exception("'{$strDate}' is not a valid Date");
		}
		
		// Set up default values
		$arrParts		= Array();
		if ($bolCeil)
		{
			$arrParts['Y']	= 2037;
			$arrParts['m']	= 12;
			$arrParts['d']	= 31;
			$arrParts['H']	= 23;
			$arrParts['i']	= 59;
			$arrParts['s']	= 59;
		}
		else
		{
			$arrParts['Y']	= 1970;
			$arrParts['m']	= 1;
			$arrParts['d']	= 1;
			$arrParts['H']	= 0;
			$arrParts['i']	= 0;
			$arrParts['s']	= 0;
		}

		// Truncate time
		$bolTruncated	= FALSE;
		foreach ($arrParts as $strPart=>$intValue)
		{
			// If we're already truncated
			if ($bolTruncated)
			{
				// Use default
				continue;
			}
			elseif (strtolower($strPart) === strtolower($strAccuracy))
			{
				// Truncate from here onwards
				$bolTruncated	= TRUE;
			}

			// Set passed value
			$arrParts[$strPart]	= (int)date($strPart, $intTime);
		}

		return mktime($arrParts['H'], $arrParts['i'], $arrParts['s'], $arrParts['m'], $arrParts['d'], $arrParts['Y']);
	}
}
?>