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
		
		$arrAccuracyConversion	= array();
		
		// Seconds
		$intSecondEarlier		= (int)date("s", $intEarlierTime);
		$intSecondLater			= (int)date("s", $intLaterTime);
		$intSecondDifference	= $intSecondLater - $intSecondEarlier;
		$intSecondDifference	= ($intSecondDifference > 0) ? $intSecondDifference : self::SECONDS_IN_MINUTE - $intSecondDifference;
		
		// Minutes
		$intMinuteEarlier		= (int)date("i", $intEarlierTime);
		$intMinuteLater			= (int)date("i", $intLaterTime);
		$intMinuteDifference	= $intMinuteLater - $intMinuteEarlier;
		$intMinuteDifference	= ($intMinuteDifference > 0) ? $intMinuteDifference : self::MINUTES_IN_HOUR - $intMinuteDifference;
		
		$arrAccuracyConversion['s']['s']	= $intSecondDifference;
		$arrAccuracyConversion['s']['i']	= $intSecondDifference;
		$arrAccuracyConversion['s']['h']	= $intSecondDifference;
		$arrAccuracyConversion['s']['s']	= $intSecondDifference;
		$arrAccuracyConversion['s']['s']	= $intSecondDifference;
		$arrAccuracyConversion['s']['s']	= $intSecondDifference;
		
		
		
		
		$arrAccuracyConversion['y']['s']	= $intSecondDifference;
		$arrAccuracyConversion['y']['i']	= $intSecondDifference;
		$arrAccuracyConversion['y']['h']	= $intSecondDifference;
		$arrAccuracyConversion['y']['d']	= $intSecondDifference;
		$arrAccuracyConversion['y']['m']	= $intSecondDifference;
		$arrAccuracyConversion['y']['y']	= $intSecondDifference;
		
		
		
		
		
		// Years
		$intYearEarlier			= (int)date("Y", $intEarlierTime);
		$intYearLater			= (int)date("Y", $intLaterTime);
		$intYearDifference		= max(0, $intYearLater-$intYearEarlier);
		
		// Years in Days
		$arrAccuracyConversion['d']['y']	= 0;
		$arrYears		= range($intYearEarlier, $intYearLater);
		foreach ($arrYears as $intYear)
		{
			if ($intYear % 4 === 0)
			{
				$arrAccuracyConversion['d']['y']	+= 365;
			}
			else
			{
				$arrAccuracyConversion['d']['y']	+= 366;
			}
		}
		$arrAccuracyConversion['y']['y']	= $intYearDifference;
		$arrAccuracyConversion['m']['y']	= $intYearDifference * self::MONTHS_IN_YEAR;
		$arrAccuracyConversion['h']['y']	= $arrAccuracyConversion['d']['y'] * self::HOURS_IN_DAY;
		$arrAccuracyConversion['i']['y']	= $arrAccuracyConversion['d']['y'] * self::MINUTES_IN_DAY;
		$arrAccuracyConversion['s']['y']	= $arrAccuracyConversion['d']['y'] * self::SECONDS_IN_DAY;
		
		// Months
		$intMonthEarlier		= (int)date("m", $intEarlierTime);
		$intMonthLater			= (int)date("m", $intLaterTime);
		$intMonthDifference		= max(0, $intMonthLater-$intMonthEarlier);
		
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
		
		$arrAccuracyConversion['y']['m']	= ($bolCeil) ? self::MONTHS_IN_YEAR : (($bolCeil === false) ? 0 : round($intMonthDifference / 100) * 100);
		$arrAccuracyConversion['m']['m']	= $intMonthDifference;
		$arrAccuracyConversion['d']['m']	= $intMonthsInDays;
		$arrAccuracyConversion['h']['m']	= $intMonthsInDays * self::HOURS_IN_DAY;
		$arrAccuracyConversion['i']['m']	= $intMonthsInDays * self::MINUTES_IN_DAY;
		$arrAccuracyConversion['s']['m']	= $intMonthsInDays * self::SECONDS_IN_DAY;
		
		// Days
		$intDayEarlier			= (int)date("d", $intEarlierTime);
		$intDayLater			= (int)date("d", $intLaterTime);
		$intDayDifference		= max(0, $intDayLater-$intDayEarlier);
		
		$arrAccuracyConversion['y']['d']	= (int)($intMonthDifference / self::MONTHS_IN_YEAR);
		$arrAccuracyConversion['m']['d']	= $intMonthDifference;
		$arrAccuracyConversion['d']['d']	= $intMonthsInDays;
		$arrAccuracyConversion['h']['d']	= $intMonthsInDays * self::HOURS_IN_DAY;
		$arrAccuracyConversion['i']['d']	= $intMonthsInDays * self::MINUTES_IN_DAY;
		$arrAccuracyConversion['s']['d']	= $intMonthsInDays * self::SECONDS_IN_DAY;
		
		// Hours
		$intHourEarlier			= (int)date("H", $intEarlierTime);
		$intHourLater			= (int)date("H", $intLaterTime);
		$intHourDifference		= max(0, $intHourLater-$intHourEarlier);
		
		// Minutes
		$intMinuteEarlier		= (int)date("i", $intEarlierTime);
		$intMinuteLater			= (int)date("i", $intLaterTime);
		$intMinuteDifference	= max(0, $intMinuteLater-$intMinuteEarlier);
		
		$intSecondsInMinutes	= ($bolCeil) ? self::SECONDS_IN_MINUTE : (($bolCeil === false) ? 0 : round($intSecondDifference / 100) * 100);
		
		return	$arrAccuracyConversion[strtolower($strAccuracy)]['y'] + 
				$arrAccuracyConversion[strtolower($strAccuracy)]['m'] + 
				$arrAccuracyConversion[strtolower($strAccuracy)]['d'] + 
				$arrAccuracyConversion[strtolower($strAccuracy)]['h'] + 
				$arrAccuracyConversion[strtolower($strAccuracy)]['i'] + 
				$arrAccuracyConversion[strtolower($strAccuracy)]['s'];
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