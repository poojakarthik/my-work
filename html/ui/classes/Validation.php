<?php

//----------------------------------------------------------------------------//
// Validation
//----------------------------------------------------------------------------//
/**
 * Validation
 *
 * The Validation class
 *
 * The Validation class - encapsulates all validation rules
 * It can also handle validation against a regex
 * Each validation rule that isn't a regex will have a method defined in this class.
 *
 * @package	ui_app
 * @class	Validation
 */
class Validation
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
	// RegexValidate
	//------------------------------------------------------------------------//
	/**
	 * RegexValidate()
	 *
	 * Validates a value using a regular expression as the validation rule
	 *
	 * Validates a value using a regular expression as the validation rule
	 *
	 * @param	string		$strValidationRule	the validation rule as a regex
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function RegexValidate($strValidationRule, $mixValue)
	{
		//echo "entered";
		// return false if not a valid regex
		/*
		if ((substr($strValidationRule, 0, 1) != '/') || (!strrpos($strValidationRule, '/') > 0))
		{
			return FALSE;
		}
		*/

		// try to match with a regex
		if (preg_match($strValidationRule, $mixValue))
		{
			return TRUE;
		}
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// IsValidABN
	//------------------------------------------------------------------------//
	/**
	 * IsValidABN()
	 *
	 * Checks if a value is a valid ABN Number
	 *
	 * Checks if a value is a valid ABN Number
	 *
	 * @param	mix			$strValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsValidABN($strValue)
	{
		// 1. If the length is 0, it is invalid
		if (strlen($strValue) == 0)
		{
			return FALSE;
		}
		
		// 2. Check that the item has only Numbers and Spaces
		if (ereg("/[^\d\s]/g", $strValue) != FALSE)
		{
			return FALSE;
		}
		
		$strABN_without_spaces = ereg_replace(" ","", $strValue);
		
		// 3. Check there are 11 integers
		if ((strlen($strABN_without_spaces) > 11) || (strlen($strABN_without_spaces) < 11))
		{
			return FALSE;
		}
			
		// 4. ABN Calculation
		// http://www.ato.gov.au/businesses/content.asp?doc=/content/13187.htm&pc=001/003/021/002/001&mnu=610&mfp=001/003&st=&cy=1
		
		//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
		//   2. Multiply each of the digits in this new number by its weighting factor
		//   3. Sum the resulting 11 products
		//   4. Divide the total by 89, noting the remainder
		//   5. If the remainder is zero the number is valid
		$arrWeights = Array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);
		
		//   1. Subtract 1 from the first (left) digit to give a new eleven digit number
		$intFirstDigitABN = substr($strABN_without_spaces, 0, 1) - 1;
		$intNewABN =$intFirstDigitABN .= substr($strABN_without_spaces, 1);
		
		//   2. Multiply each of the digits in this new number by its weighting factor
		//   3. Sum the resulting 11 products
		$intNumberSum = 0;
		
		for ($i = 0; $i < 11; $i ++)
		{
			$intNumberSum += substr($intNewABN,$i,1) * $arrWeights[$i];
		}
		
		//   4. Divide the total by 89, noting the remainder
		//   5. If the remainder is zero the number is valid
		
		if ($intNumberSum % 89 != 0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	//------------------------------------------------------------------------//
	// IsValidPostcode
	//------------------------------------------------------------------------//
	/**
	 * IsValidPostcode()
	 *
	 * Checks if a value is a valid Australian Postcode
	 *
	 * Checks if a value is a valid Australian Postcode
	 *
	 * @param	mix			$mixValue	postcode to validate
	 * 
	 * @return	bool
	 * @method
	 */
	static function IsValidPostcode($mixValue)
	{
		return preg_match("/^\d{4}$/", $mixValue);
	}

	//------------------------------------------------------------------------//
	// IsValidPhoneNumber
	//------------------------------------------------------------------------//
	/**
	 * IsValidPhoneNumber()
	 * 
	 * Check the format of a phone number
	 * 
	 * Check the format of a phone number
	 *
	 * @param	str	$strNumber	The phone number to check
	 *
	 * @return	bool
	 * 
	 * @function
	 */
	static function IsValidPhoneNumber ($strNumber)
	{
		return preg_match ("/^\+?[\d\s]{10,}$/", $strNumber);
	}
	
	
	//------------------------------------------------------------------------//
	// IsValidInteger
	//------------------------------------------------------------------------//
	/**
	 * IsValidInteger()
	 *
	 * Checks if a value is a valid integer
	 *
	 * Checks if a value is a valid integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsValidInteger($mixValue)
	{
		if ((string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// Integer
	//------------------------------------------------------------------------//
	/**
	 * Integer()
	 *
	 * Checks if a value is a valid integer
	 *
	 * Checks if a value is a valid integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 * 
	 * @deprecated - use Validation::IsValidInteger - hadrian - 12/03/2008
	 */
	static function Integer($mixValue)
	{
		return self::IsValidInteger($mixValue);
	}
	
	//------------------------------------------------------------------------//
	// UnsignedInteger
	//------------------------------------------------------------------------//
	/**
	 * UnsignedInteger()
	 *
	 * Checks if a value is a valid unsigned integer
	 *
	 * Checks if a value is a valid unsigned integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function UnsignedInteger($mixValue)
	{
		if ((int)$mixValue > -1 && (string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// NonZeroInteger
	//------------------------------------------------------------------------//
	/**
	 * UnsignedInteger()
	 *
	 * Checks if a value is a valid non-zero integer
	 *
	 * Checks if a value is a valid non-zero integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function NonZeroInteger($mixValue)
	{
		if ((int)$mixValue != 0 && (string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// UnsignedNonZeroInteger
	//------------------------------------------------------------------------//
	/**
	 * UnsignedNonZeroInteger()
	 *
	 * Checks if a value is a valid unsigned non-zero integer
	 *
	 * Checks if a value is a valid unsigned non-zero integer
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function UnsignedNonZeroInteger($mixValue)
	{
		if ((int)$mixValue > 0 && (string)(int)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	//------------------------------------------------------------------------//
	// UnsignedFloat
	//------------------------------------------------------------------------//
	/**
	 * UnsignedFloat()
	 *
	 * Checks if a value is a valid unsigned float
	 *
	 * Checks if a value is a valid unsigned float
	 *
	 * @param	mix			$mixValue			the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function UnsignedFloat($mixValue)
	{
		if ((float)$mixValue >= 0 && (string)(float)$mixValue == (string)$mixValue)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	
	//------------------------------------------------------------------------//
	// ShortDate
	//------------------------------------------------------------------------//
	/**
	 * ShortDate()
	 *
	 * Checks if a value is in a valid date format
	 *
	 * Checks if a value is in a valid date format
	 *
	 * @param	mix			$mixDateAndTime		the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function ShortDate($mixDate)
	{
		if ($mixDate == "00/00/0000")
		{
			return TRUE;
		}
		else
		{
			$bolValidDateFormat = self::RegexValidate('^(0[1-9]|[12][0-9]|3[01])[/](0[1-9]|1[012])[/](19|20)[0-9]{2}$^' , $mixDate); 
			
			if (!$bolValidDateFormat)
			{
				return FALSE;
			}
			
			// The Date is in the correct format, but now check that it is a proper date
			// IE check that it isn't the 31st of February
			$arrParts = explode("/", $mixDate);
			return checkdate((int)$arrParts[1], (int)$arrParts[0], (int)$arrParts[2]);
		}
	}	
	
	//------------------------------------------------------------------------//
	// Time
	//------------------------------------------------------------------------//
	/**
	 * Time()
	 *
	 * Checks if a value is in a valid time format (HH:MM:SS)
	 *
	 * Checks if a value is in a valid time format (HH:MM:SS)
	 *
	 * @param	string	$strTime	the value to validate
	 * 
	 * @return	bool
	 * @method
	 */
	static function Time($strTime)
	{
		return self::RegexValidate('^(0[0-9]|[1][0-9]|2[0-3])(:(0[0-9]|[1-5][0-9])){2}$^' , $strTime); 
	}	
	
	//------------------------------------------------------------------------//
	// IsValidDate
	//------------------------------------------------------------------------//
	/**
	 * IsValidDate()
	 * 
	 * Check the validity of a short date
	 * 
	 * Check the validity of a short date, which should be in the format yyyy-mm-dd
	 *
	 * @param	str	$strShortDate	The date to check
	 *
	 * @return	bool
	 * 
	 * @function
	 */
	static function IsValidDate($strShortDate)
	{
		$dateParts = array();
		$ok = preg_match ("/^(?:(\d\d\d\d)\-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01]))$/", $strShortDate, $dateParts);
		if (!$ok)
		{
			return FALSE;
		}
		return checkdate((int)$dateParts[2], (int)$dateParts[3], (int)$dateParts[1]);
	}
	
	//------------------------------------------------------------------------//
	// IsValidDateInPast
	//------------------------------------------------------------------------//
	/**
	 * IsValidDateInPast()
	 *
	 * Checks if a value is in valid date in the past
	 *
	 * Checks if a value is in valid date in the past
	 *
	 * @param	str	$strShortDate	the date to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsValidDateInPast($strShortDate)
	{
		$dateParts = array();
		$ok = preg_match ("/^(?:(\d\d\d\d)\-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01]))$/", $strShortDate, $dateParts);
		if (!$ok)
		{
			return FALSE;
		}
		$year = (int)$dateParts[1];
		$month = (int)$dateParts[2];
		$day = (int)$dateParts[3];
		$ok = checkdate($month, $day, $year);
		if (!$ok)
		{
			return FALSE;
		}
		
		$yearNow = (int)date("Y");
		$monthNow = (int)date("m");
		$dayNow = (int)date("d");
		
		if ($year > $yearNow || ($year == $yearNow && ($month > $monthNow || ($month == $monthNow && $day > $dayNow))))
		{
			return FALSE;
		}
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// DateAndTime
	//------------------------------------------------------------------------//
	/**
	 * DateAndTime()
	 *
	 * Checks if a value is in a valid date and time format
	 *
	 * Checks if a value is in a valid date and time format
	 *
	 * @param	mix			$mixDateAndTime		the value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function DateAndTime($mixDateAndTime)
	{
		// TODO! Joel  Test against all variations of the MySql datetime data type
		return TRUE;
	}
	
	//------------------------------------------------------------------------//
	// IsMoneyValue
	//------------------------------------------------------------------------//
	/**
	 * IsMoneyValue()
	 *
	 * Checks if a value is in a valid monetary format and is not NULL
	 *
	 * Checks if a value is in a valid monetary format and is not NULL
	 * The valid format is a float that can start with a '$' char
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsMoneyValue($mixValue)
	{
		// remove whitespace and the $ if they are present
		$mixValue = trim($mixValue);
		$mixValue = ltrim($mixValue, "$");
		
		//check that the value is a float
		list($fltValue, $strAppendedText) = sscanf($mixValue, "%f%s");
		
		if ($strAppendedText)
		{
			// there was some text after the float
			return FALSE;
		}
		
		return (is_numeric($fltValue));
	}
	
	//------------------------------------------------------------------------//
	// IsNotNull
	//------------------------------------------------------------------------//
	/**
	 * IsNotNull()
	 *
	 * Returns TRUE if the value is not NULL
	 *
	 * Returns TRUE if the value is not NULL
	 * This will return TRUE if $mixValue == 0
	 * 
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsNotNull($mixValue)
	{
		// take care of the special case where $mixValue == 0
		if (is_numeric($mixValue))
		{
			// if the value is a number then it can't be NULL
			return TRUE;
		}
		
		return (bool)($mixValue != NULL);
	}

	//------------------------------------------------------------------------//
	// IsNotEmptyString
	//------------------------------------------------------------------------//
	/**
	 * IsNotEmptyString()
	 *
	 * Returns TRUE if the value is not an empty string and is not just whitespace
	 *
	 * Returns TRUE if the value is not an empty string and is not just whitespace
	 * 
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsNotEmptyString($mixValue)
	{
		$mixValue = trim($mixValue);
		return (strlen($mixValue) > 0)? TRUE : FALSE;
	}
	
	//------------------------------------------------------------------------//
	// IsAlphaString
	//------------------------------------------------------------------------//
	/**
	 * IsAlphaString()
	 *
	 * Returns TRUE if the value is comprises of only alpha characters (A-Z and a-z)
	 *
	 * Returns TRUE if the value is comprises of only alpha characters (A-Z and a-z)
	 * 
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsAlphaString($mixValue)
	{
		return self::RegexValidate('/^[A-Za-z]+$/', $mixValue);
	}
	
	//------------------------------------------------------------------------//
	// IsValidEmail
	//------------------------------------------------------------------------//
	/**
	 * IsValidEmail()
	 *
	 * Returns TRUE if the value has all the components of a valid email 
	 * address i.e. minimum length the '@' symbol and atleast one period '.'
	 *
	 * Returns FALSE if the value has some components of a valid email
	 * address missing
	 *
	 * Uses RegexValidate and custom regex validation to check email address
	 * 
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsValidEmail($mixValue)
	{
		return self::RegexValidate('^([[:alnum:]]([-_.]?[[:alnum:]])*)@([[:alnum:]]([.]?[-[:alnum:]])*[[:alnum:]])\.([[:alpha:]]){2,25}$^', $mixValue);
	}

	//------------------------------------------------------------------------//
	// IsValidFNN
	//------------------------------------------------------------------------//
	/**
	 * IsValidFNN()
	 *
	 * Returns TRUE if the value is a valid FNN
	 *
	 * Returns TRUE if the value is a valid FNN
	 * Wrapper for the function IsValidFNN found in framework/functions.php
	 *
	 * @param	mix			$mixValue		value to validate
	 * 
	 * @return	bool
	 *
	 * @method
	 */
	static function IsValidFNN($mixValue)
	{
		return IsValidFNN($mixValue);
	}	
}

?>
