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
	const	TIMESTAMP_START_OF_TIME	= '1970-01-01 00:00:01';
	const	TIMESTAMP_END_OF_TIME	= '2038-01-09 03:14:07';
	
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
	 * Calculates the difference between two dates.
	 * 
	 * @param	string	$strEarlierDate				Earlier Date 
	 * @param	string	$strLaterDate				Later Date
	 * @param	string	$strAccuracy				Difference units.  Accepts 'y', 'm', 'd', 'h', 'i', or 's'. (Default: 's')
	 * @param	[string	$strRound				]	Rounding function to use. [ceil|floor (Default)]
	 *
	 * @return	mixed								Integer for rounded result, otherwise float
	 *
	 * @method
	 */
	public static function difference($strEarlierDate, $strLaterDate, $strInterval='s', $strRound='floor')
	{
		$strRoundingFunction	= (strtolower($strRound) === 'ceil') ? 'ceil' : 'floor';
		
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
		
		// Months
		$intMonthDifference	= (($intYearLater - $intYearEarlier) * self::MONTHS_IN_YEAR);
		$intMonthDifference	+= ($intMonthLater - $intMonthEarlier);
		if ((int)date("dHis", $intLaterTime) < (int)date("dHis", $intEarlierTime) && $strRoundingFunction === 'floor')
		{
			$intMonthDifference--;
		}
		if ($strInterval === 'm')
		{
			return call_user_func($strRoundingFunction, $intMonthDifference);
		}
		
		// Years
		$intYearDifference	= $intYearLater - $intYearEarlier;
		if ((int)date("mdHis", $intLaterTime) < (int)date("mdHis", $intEarlierTime) && $strRoundingFunction === 'floor')
		{
			$intYearDifference--;
		}
		if ($strInterval === 'y')
		{
			return call_user_func($strRoundingFunction, $intYearDifference);
		}
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