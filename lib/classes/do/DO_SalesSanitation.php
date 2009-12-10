<?php

/**
 * DO_SalesSanitation
 *
 * Used to encapsulate all the high-level input sanitation rules that are used in the various DO_Sales model classes
 * 
 * This class should probably just be called DataSanitation, and located in the classes root dir, but it is used by both the Flex project and the Sales project
 * so I wanted to keep it in the do directory
 * 
 * NOTE: The methods of this class assume the value being sanitized is not null
 *
 * @class	DO_SalesSanitation
 * @abstract
 */
abstract class DO_SalesSanitation
{
	// This will convert any span of whitespace chars into a single space char, and then trim any spaces from the begining and end of the string
	// The string "   wha  t\t\nup cracka  !     " would be converted to "wha t up cracka !"
	public static function removeExcessWhitespace($strString)
	{
		return trim(preg_replace('/(\s)+/', ' ', $strString));
	}
	
	// This will fix the case of each word in the string
	// For example, "billy-bob" will become "Billy-Bob".  However "QANTAS" will become "Qantas" and "O'Maily's" will become "O'maily's"
	public static function fixProperNounCasing($strString)
	{
		static $fncPregReplaceCallback;
		
		if (!isset($fncPregReplaceCallback))
		{
			$fncPregReplaceCallback = create_function('$arrMatches', 'return ucfirst($arrMatches[0]);');
		}
		
		// Correct the string
		// TODO! this would currently convert o'maily's into O'maily's.  It would be nice to get it to convert it to O'Maily's  (I guess the special cases would be 's, 't, 're and I think also 'd)
		return preg_replace_callback("/(?:[A-Za-z'])+/", $fncPregReplaceCallback, strtolower($strString));
	}
	
	public static function fixSuspectCasing($strString, $fltUpperCaseWordTollerence=0.5)
	{
		return "TODO";
	}
	
	public static function cleanBusinessName($strName)
	{
		static $fncPregReplaceCallback;
		if (!isset($fncPregReplaceCallback))
		{
			$fncPregReplaceCallback = create_function('$arrMatches', '
										/* Only fix the casing if the word is completely in upper case or completely in lower case */
										if (strtolower($arrMatches[0]) == $arrMatches[0] || strtoupper($arrMatches[0]) == $arrMatches[0])
										{
											return ucwords(strtolower($arrMatches[0]));
										}
										else
										{
											return $arrMatches[0];
										}
										');
		}
		
		$strName = self::removeExcessWhitespace($strName);
		
		// If the name is all lower case chars, or 50% or more of the words are in all upper case, or it has PTY, LTD or CO in upper case, then fix the whole name
		$arrMatches					= array();
		$intUpperCaseWordCount		= preg_match_all('/\b(?:[A-Z]){2,}\b/', $strName, $arrMatches);
		$intWordCount				= str_word_count($strName);
		$strNameInLowerCase			= strtolower($strName);
		$bolHasKeyWordInUpperCase	= preg_match('/\b(?:PTY|LTD|CO)+\b/', $strName)? true : false;
		if ($strName == $strNameInLowerCase || $intUpperCaseWordCount >= round(0.5 * $intWordCount) || $bolHasKeyWordInUpperCase)
		{
			// Correct the name
			// TODO! this would currently convert o'maily's into O'maily's.  It would be nice to get it to convert it to O'Maily's  (I guess the special cases would be 's, 't, 're and I think also 'd)
			$strName = preg_replace_callback("/(?:[A-Za-z'])+/", $fncPregReplaceCallback, $strName);
		}
		
		return $strName;
	}
	
	public static function cleanTradingName($strName)
	{
		return self::cleanBusinessName($strName);
	}
	
	public static function cleanPersonFirstName($strName)
	{
		return self::fixProperNounCasing(self::removeExcessWhitespace($strName));
	}
	
	public static function cleanPersonLastName($strName)
	{
		return self::fixProperNounCasing(self::removeExcessWhitespace($strName));
	}

	public static function cleanAddressSuburb($strSuburb)
	{
		return self::fixProperNounCasing(self::removeExcessWhitespace($strSuburb));
	}
	
	public static function cleanAddressLocality($strLocality)
	{
		return self::fixProperNounCasing(self::removeExcessWhitespace($strLocality));
	}
	
	public static function cleanABN($strABN)
	{
		// Clean whitespace
		$strABN = self::removeExcessWhitespace($strABN);
		
		// Remove any reasonable punctuation
		return str_replace(array('-', ' '), '', $strABN);
	}
	
	public static function cleanACN($strACN)
	{
		// Same as ABN sanitation
		return self::cleanABN($strACN);
	}
	
	public static function cleanPostcode($strPostcode)
	{
		// Clean whitespace
		$strPostcode = self::removeExcessWhitespace($strPostcode);
		
		// Remove any reasonable punctuation
		return str_replace(array(' '), '', $strPostcode);
	}
	
	public static function cleanFNN($strFNN)
	{
		// Clean whitespace
		$strFNN = self::removeExcessWhitespace($strFNN);
		
		// Remove any reasonable punctuation
		return str_replace(array(' ', '-', '(', ')'), '', $strFNN);
	}
	
	public static function cleanEmailAddress($strEmailAddress)
	{
		return strtolower(self::removeExcessWhitespace($strEmailAddress));
	}

	
	public static function cleanAddressLine($strLine)
	{
		static $fncPregReplaceCallback;
		
		if (!isset($fncPregReplaceCallback))
		{
			$fncPregReplaceCallback = create_function('$arrMatches', '
										/* Only fix the casing if the word is completely in upper case or completely in lower case */
										if (strtolower($arrMatches[0]) == $arrMatches[0] || strtoupper($arrMatches[0]) == $arrMatches[0])
										{
											return ucwords(strtolower($arrMatches[0]));
										}
										else
										{
											return $arrMatches[0];
										}
										');
		}

		$strLine = self::removeExcessWhitespace($strLine);
		
		// If the name is all lower case chars, or 50% or more of the words are in all upper case
		$arrMatches					= array();
		$intUpperCaseWordCount		= preg_match_all('/\b(?:[A-Z]){2,}\b/', $strLine, $arrMatches);
		$intWordCount				= str_word_count($strLine);
		$strNameInLowerCase			= strtolower($strLine);
		if ($strLine == $strNameInLowerCase || $intUpperCaseWordCount >= round(0.5 * $intWordCount))
		{
			// Correct the name
			// TODO! this would currently convert o'maily's into O'maily's.  It would be nice to get it to convert it to O'Maily's  (I guess the special cases would be 's, 't, 're and I think also 'd)
			$strLine = preg_replace_callback("/(?:[A-Za-z'])+/", $fncPregReplaceCallback, $strLine);
		}
		
		return $strLine;
	}
	
	public static function cleanBankBSB($strBSB)
	{
		// Clean whitespace
		$strBSB = self::removeExcessWhitespace($strBSB);
		
		// Remove any reasonable punctuation
		return str_replace(array('-', ' '), '', $strBSB);
	}

	public static function cleanBankAccountNumber($strNumber)
	{
		// Clean whitespace
		$strNumber = self::removeExcessWhitespace($strNumber);
		
		// Remove any reasonable punctuation
		return str_replace(array('-', ' '), '', $strNumber);
	}
	
	public static function cleanBankAccountName($strName)
	{
		// Just treat it like a person's last name
		return self::cleanPersonLastName($strName);
	}
	
	public static function cleanCreditCardName($strName)
	{
		// Just treat it like a bank account name
		return self::cleanBankAccountName($strName);
	}
	
	public static function cleanCreditCardNumber($strNumber)
	{
		// Clean whitespace
		$strNumber = self::removeExcessWhitespace($strNumber);
		
		// Remove any reasonable punctuation
		return str_replace(array('-', ' '), '', $strNumber);
	}
	
	public static function cleanCreditCardCVV($strCVV)
	{
		// Clean whitespace
		$strCVV = self::removeExcessWhitespace($strCVV);
		
		return $strCVV; 
	}
	
}

?>