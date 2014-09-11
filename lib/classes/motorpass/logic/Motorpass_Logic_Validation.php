<?php

class Motorpass_Logic_Validation
{
	// Returns true if the value has no leading or trailing whitespace, else false
	public static function isTrimmed($strValue)
	{
		// Check that the string has no leading or trailing whitespace
		return (trim($strValue) == $strValue)? true : false;
	}

	// Returns true if any character in $strIllegal is present in $strValue
	public static function hasIllegalChars($strValue, $strIllegal)
	{
		return (strpbrk($strValue, $strIllegal) === false)? false : true;
	}

	// Returns true if the ABN is valid, else false
	public static function isValidABN($strABN)
	{
		$strABN = strval($strABN);

		$strABN = preg_replace("/[^\d]/", '', $strABN);

		// Ensure that they are all digits and 11 digits long
		if (!preg_match("/^[0-9]{11}$/", $strABN))
		{
			return false;
		}

		// Official ABN validation Step 1:
		// Subtract 1 from the first (left most) digit to give a new eleven digit number
		$strABNStep1 = (intval($strABN[0]) - 1) . substr($strABN, 1);

		$arrWeight = array(10, 1, 3, 5, 7, 9, 11, 13, 15, 17, 19);

		// Steps 2 and 3:
		// Multiply each of the digits in this new number, by its weighting factor and sum the resulting 11 products
		$intABNStep3 = 0;

		for ($i=0; $i < 11; $i++)
		{
			$intABNStep3 += intval($strABNStep1[$i]) * $arrWeight[$i];
		}

		// Steps 4 and 5:
		// Divide the total by 89.  If the remainder is zero then the number is valid
		return (($intABNStep3 % 89) == 0);
	}

	// Returns true if the ACN is valid, else false
	public static function isValidACN($strACN)
	{
		$strACN = strval($strACN);

		// Ensure that they are all digits
		if (!preg_match("/^[0-9]{9}$/", $strACN))
		{
			return false;
		}

		// Check the check digit

		// (i) apply weighting to digits 0 to 7 and (ii) sum the products
		$total = 0;
		for ($i = 0; $i < 8; $i++)
		{
			$total += ((8 - $i) * intval($strACN[$i]));
		}

		// (iii) divide by 10 to obtain remainder, (iv) complement the remainder to 10 (if complement = 10, set to 0) and (v) compare to character 8
		return intval($strACN[8]) == ((10 - ($total % 10)) % 10);
	}

	// Assumes Australian landline FNN with no international coding
	public static function isValidLandlineFNN($strFNN)
	{
		return preg_match("/^0[12378]\\d{8}$/", $strFNN);
	}

	// An MSN is the same as an FNN, but for a mobile service
	// Assumes Australian MSN with no international coding
	public static function isValidMobileMSN($strMSN)
	{
		return preg_match("/^04\\d{8}$/", $strMSN);
	}

	// Assumes Australian Inbound FNN with no international coding
	public static function isValidInboundFNN($strFNN)
	{
		return preg_match("/^((13\\d{4})|(1[389]00\\d{6}))$/", $strFNN);
	}

	// Assumes Australian postcode only
	public static function isValidPostcode($strPostcode)
	{
		return preg_match("/^\\d{4}$/", $strPostcode);
	}

	public static function isValidEmailAddress($strEmailAddress)
	{
		return preg_match("/^[a-z0-9!#\$%&'\*\+\/=\?\^_`\{\|\}~\-]+(?:\.[a-z0-9!#\$%&'\*\+\/=\?\^_`\{\|\}~\-]+)*@(?:[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?$/i", $strEmailAddress);
	}

	// It assumes the number is not encrypted
	public static function isValidCreditCardNumber($strNumber, DO_Sales_CreditCardType $doCreditCardType)
	{
		return $doCreditCardType->isValidCreditCardNumber($strNumber);
	}

	// It assumes the cvv is not encrypted
	public static function isValidCreditCardCVV($strCVV, DO_Sales_CreditCardType $doCreditCardType)
	{
		return $doCreditCardType->isValidCVV($strCVV);
	}

	public static function isValidCreditCardName($strName)
	{
		return DO_Sales_CreditCardType::isValidCreditCardName($strName);
	}

	// Returns true if $strNumber is a valid Luhned number, else false
	public static function isValidLuhnNumber($strNumber)
	{
		$strNumber	= strval($strNumber);

		// Ensure that they are all digits
		if (!preg_match("/^[0-9]+$/", $strNumber))
		{
			return false;
		}

		$nrDigits	= strlen($strNumber);

		// Any number of zeros can prefix a luhned number without affecting it.  We add 2 on account of the way we loop through the digits of the number
		$digits	= strrev('00' . $strNumber);
		$total	= 0;

		for ($i=0; $i<$nrDigits; $i+=2)
		{
			// Get the digit
			$d1 = intval($digits[$i]);

			// Get the next digit and multiply it by 2
			$d2 = 2*intval($digits[$i + 1]);

			// If $d2 is double digits then add these 2 digits together (which is the same as subtracting 9)
			$d2 = ($d2 > 9) ? ($d2 - 9) : $d2;

			// Add the numbers to the total
			$total += $d1 + $d2;

			// I don't know what Hadrian was doing with this.  Perhaps it's faster than doing ($total % 10 == 0), but it's unreadable
			//$total -= (($total >= 20) ? 20 : ($total >= 10 ? 10 : 0));
		}

		// If the sum modulo 10 of the total is 0, then the number is a valid luhned number
		//echo "/* $cardNumber $nrDigits $total - bad luhn */\n";
		return ($total % 10 == 0);
	}

	public static function isValidBusinessName($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!self::isTrimmed($strName) || $strName === '')
		{
			return false;
		}

		// Check for the presence of illegal chars (currently there are no illegal chars for a business name)
		//return (strpbrk($strName, "INSERT ILLEGAL CHARS HERE") === false)? true : false;
		return true;
	}

	public static function isValidTradingName($strName)
	{
		return self::isValidBusinessName($strName);
	}

	// TODO! modify this so that it checks the suburb name against a list of all valid suburb names for Australia
	public static function isValidAddressSuburb($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!self::isTrimmed($strName) || $strName === '')
		{
			return false;
		}

		// Make sure there are only legal chars in it (legal characters being the alphabet, numbers, spaces, periods, hyphens and apostrophes)
		return preg_match("/^(?:[A-Za-z0-9\\.\\-\\' ]+)$/", $strName) ? true : false;
	}

	public static function isValidAddressLocality($strName)
	{
		return self::isValidAddressSuburb($strName);
	}

	public static function isValidAddressLine($strLine)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!self::isTrimmed($strLine) || $strLine === '')
		{
			return false;
		}

		// Check for the presence of illegal chars
		return self::hasIllegalChars($strLine, ",`~!@#\$%^*=_+{}[]|\\;:\"<>?\n\r\t") ? false : true;
	}

	public static function isValidPersonFirstName($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!self::isTrimmed($strName) || $strName === '')
		{
			return false;
		}

		// Check for the presence of illegal chars (this will allow numbers in a name, but the artist formaly known as Prince is still screwed)
		return self::hasIllegalChars($strName, "`~!@#\$%^&*()=_+{}[]|\\;:\"<>,/?\n\r\t") ? false : true;
	}

	public static function isValidPersonMiddleName($strName)
	{
		return self::isValidPersonFirstName($strName);
	}

	public static function isValidPersonLastName($strName)
	{
		return self::isValidPersonFirstName($strName);
	}

	public static function isValidUsername($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!self::isTrimmed($strName) || $strName === '')
		{
			return false;
		}

		// Make sure there are only legal chars in it (that being the alphabet, numbers, underscores)
		return preg_match("/^(?:[A-Za-z0-9_]+)$/", $strName)? true : false;
	}

	/* $value can be an integer or a string.  This disregards leading zeros. */
	public static function isValidPositiveInteger($value)
	{
		return preg_match("/^(\\+)?\\d+$/", "$value");
	}

	/* The bsb must be a 6 digit number with no formatting */
	/* A description of the bsb format can be found at http://www.bsbnumbers.com/information.php */
	public static function isValidBankBSB($strBSB)
	{
		return preg_match("/^\\d{6}$/", $strBSB);
	}

	public static function isValidBankAccountNumber($strNumber)
	{
		// Just make sure every character is a digit and it is between 6 and 20 digits long
		return preg_match("/^\\d{6,20}$/", $strNumber);
	}

	public static function isValidBankAccountName($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!self::isTrimmed($strName) || $strName === '')
		{
			return false;
		}

		// Check for the presence of illegal chars
		return self::hasIllegalChars($strName, "`~!@#\$%^*()=_+{}[]|\\;:\"<>,?\n\r\t")? false : true;
	}


}

?>