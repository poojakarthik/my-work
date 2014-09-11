<?php

//----------------------------------------------------------------------------//
// OutputMasks
//----------------------------------------------------------------------------//
/**
 * OutputMasks
 *
 * The OutputMasks class
 *
 * The OutputMasks class - encapsulates all output masks
 *
 * @package	ui_app
 * @class	OutputMasks
 */
class OutputMasks
{
	//------------------------------------------------------------------------//
	// instance
	//------------------------------------------------------------------------//
	/**
	 * instance()
	 *
	 * Returns a singleton instance of this class
	 *
	 * Returns a singleton instance of this class
	 *
	 * @return	__CLASS__
	 *
	 * @method
	 */
	public static function instance()
	{
		static $instance;
		if (!isset($instance))
		{
			$instance = new self();
		}
		return $instance;
	}

	//------------------------------------------------------------------------//
	// MoneyValue
	//------------------------------------------------------------------------//
	/**
	 * MoneyValue()
	 *
	 * Formats a float as a money value
	 *
	 * Formats a float as a money value
	 *
	 * @param	float	$fltValue					value to format as a money value
	 * @param	int		$intDecPlaces				optional; number of decimal places to show
	 * @param	bool	$bolIncludeDollarSign		optional; should a dollar sign be included
	 * @param	bool	$bolUseBracketsForNegative	optional; should brackets be used to denote a negative value
	 * @return	string								$fltValue formatted as a money value
	 *
	 * @method
	 */
	function MoneyValue($fltValue, $intDecPlaces=2, $bolIncludeDollarSign=FALSE, $bolUseBracketsForNegative=FALSE)
	{
		if ($fltValue < 0)
		{
			$bolIsNegative = TRUE;
			// Change it to a positive
			$fltValue = $fltValue * (-1.0);
		}
		else
		{
			$bolIsNegative = FALSE;
		}
		
		$strValue = number_format($fltValue, $intDecPlaces, ".", "");
		
		if ($bolIsNegative && ($strValue != 0))
		{
			if ($bolUseBracketsForNegative)
			{
				$strValue = '($' . $strValue . ')';
			}
			else
			{
				$strValue = '$-' . $strValue;
			}
		}
		else
		{
			$strValue = '$' . $strValue;
		}
		
		if (!$bolIncludeDollarSign)
		{
			$strValue = str_replace('$', '', $strValue);
		}
		
		return $strValue;	
	}

	//------------------------------------------------------------------------//
	// FormatFloat
	//------------------------------------------------------------------------//
	/**
	 * FormatFloat()
	 *
	 * Formats a float with respect to the minimum number of decimal places and the max num of decimal places
	 *
	 * Formats a float with respect to the minimum number of decimal places and the max num of decimal places
	 *
	 * @param	float	$fltValue					value to format
	 * @param	int		$intMinDecPlaces			optional; minimum number of decimal places to show (default is 2)
	 * @param	bool	$intMaxDecPlaces			optional; maximum number of decimaul places to show (default is 8)
	 *
	 * @return	string								$fltValue formatted accordingly
	 *
	 * @method
	 */
	function FormatFloat($fltFloat, $intMinDecPlaces=2, $intMaxDecPlaces=8)
	{
		$strFloat = number_format($fltFloat, $intMaxDecPlaces, ".", "");
		
		$mixDecimalPointPos = strpos($strFloat, ".");
		
		if ($mixDecimalPointPos === FALSE)
		{
			// There is no fraction part to this number.  Pad with zeros to the desired minumum decimal places
			$strFloat = number_format($strFloat, $intMinDecPlaces, ".", "");
		}
		else
		{
			// Trim the trailing zeros and pad up to the min decimal places
			$strFloat = rtrim($strFloat, "0");
			$strFractionPart = substr($strFloat, $mixDecimalPointPos+1);
			if (strlen($strFractionPart) < $intMinDecPlaces)
			{
				// The fraction part is less than the minimum decimal places, so pad it
				$strFloat = number_format($strFloat, $intMinDecPlaces, ".", "");
			}
		}
		
		return $strFloat;
	}


	//------------------------------------------------------------------------//
	// ShortDate
	//------------------------------------------------------------------------//
	/**
	 * ShortDate()
	 *
	 * Converts a Date from YYYY-MM-DD (MySql Date) to DD/MM/YYYY
	 *
	 * Converts a Date from YYYY-MM-DD (MySql Date) to DD/MM/YYYY
	 * Should also be able to handle Datetime datatype (YYYY-MM-DD HH:MM:SS)
	 *
	 * @param	string	$strISODate				in the format YYYY-MM-DD (standard ISO Date data type)
	 * @return	string							date in format DD/MM/YYYY
	 *
	 * @method
	 */
	function ShortDate($strISODate)
	{
		// Don't change the date if it is alread in the format DD/MM/YYYY
		if (Validate("ShortDate", $strISODate))
		{
			return $strISODate;
		}

		// If $strMySqlDate is a Datetime data type, truncate the time
		$arrDateParts = explode(" ", $strISODate);
		$strISODate = $arrDateParts[0];

		// The following line can't handle dates like 9999-12-31
		//$strDate = date("Y-m-d", strtotime($strMySqlDate));
		
		// if it is a date and time, then just grab the date
		$arrDate = explode(" ", $strISODate);
		
		// split the date into year, month and day
		$arrDate = explode("-", $arrDate[0]);
		
		if (count($arrDate) > 1)
		{
			$strDate = $arrDate[2] ."/". $arrDate[1] ."/". $arrDate[0];
		}
		else
		{
			$strDate = $strISODate;
		}
		return $strDate;
	}
	
	function ShortDateTime($strISODateTime, $bolMonthAsWord=FALSE)
	{
		if ($bolMonthAsWord)
		{
			// 17:47:12 Jun 3, 2008
			$strFormat = "H:i:s M j, Y";
		}
		else
		{
			// 17:47:12 03/06/2008
			$strFormat = "H:i:s d/m/Y";
		}
		return date($strFormat, strtotime($strISODateTime));
	}

	//------------------------------------------------------------------------//
	// LongDateAndTime
	//------------------------------------------------------------------------//
	/**
	 * LongDateAndTime()
	 *
	 * Converts date and time from YYYY-MM-DD HH:MM:SS (MySql Datetime) to "Wednesday, Jun 21, 2007 11:36:54 AM" format
	 *
	 * Converts date and time from YYYY-MM-DD HH:MM:SS (MySql Datetime) to "Wednesday, Jun 21, 2007 11:36:54 AM" format
	 *
	 * @param	string	$strMySqlDatetime			in the format YYYY-MM-DD HH:MM:SS (MySql Datetime data type)
	 * @return	string								date in format "Wednesday, Jun 21, 2007 11:36:54 AM"
	 *
	 * @method
	 */
	function LongDateAndTime($strMySqlDatetime)
	{
		if ($strMySqlDatetime == END_OF_TIME)
		{
			return "Indefinite";
		}
		$intUnixTime = strtotime($strMySqlDatetime);
		$strDateAndTime = date("l, M j, Y g:i:s A", $intUnixTime);
	
		return $strDateAndTime;
	}

	//------------------------------------------------------------------------//
	// LongDate
	//------------------------------------------------------------------------//
	/**
	 * LongDate
	 *
	 * Converts date and time from YYYY-MM-DD to "Wednesday, Jun 21, 2007" format
	 *
	 * Converts date and time from YYYY-MM-DD to "Wednesday, Jun 21, 2007" format
	 *
	 * @param	string	$strMySqlDate				in the format YYYY-MM-DD
	 * @return	string								date in format "Wednesday, Jun 21, 2007"
	 *
	 * @method
	 */
	function LongDate($strMySqlDate)
	{
		$arrDate = explode("-", $strMySqlDate);
		$intUnixTime = mktime(0,0,0,$arrDate[1], $arrDate[2], $arrDate[0]);
		if (!$intUnixTime)
		{
			return "Indefinite";
		}
		
		$strDateAndTime = date("l, M j, Y", $intUnixTime);
		return $strDateAndTime;
	}

	//------------------------------------------------------------------------//
	// BooleanYesNo
	//------------------------------------------------------------------------//
	/**
	 * BooleanYesNo()
	 *
	 * Converts a boolean into a string of either "Yes" or "No"
	 *
	 * Converts a boolean into a string of either "Yes" or "No"
	 *
	 * @param	bool	$bolYes				boolean value
	 * @return	string						"Yes" or "No"
	 *
	 * @method
	 */
	function BooleanYesNo($bolYes)
	{
		if ($bolYes)
		{
			return "Yes";
		}
		
		return "No";
	}
	
}

?>
