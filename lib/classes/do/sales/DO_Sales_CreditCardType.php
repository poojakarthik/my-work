<?php

class DO_Sales_CreditCardType extends DO_Sales_Base_CreditCardType
{
	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}

	/*
	 * function getCreditCardTypesList()
	 *
	 * Builds a list of credit card types (from credit_card_type.)
	 */
	static function getCreditCardTypesList()
	{

		$dataSource = self::getDataSource();

		$strSQL = "SELECT id,description
		FROM credit_card_type
		ORDER BY description";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to Build a list of credit card types: " . $result->getMessage());
		}

		$arrCreditCardTypesList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrCreditCardTypesList;

	}
	
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{

			case 'validLengths':
			case 'validPrefixes':
				return preg_match("/^\\d{1,2}(,(\\d){1,2})*$/", $value);

			default:
				// No extra validation
				return true;

		}
	}

	public function __set($propertyName, $value)
	{
		switch ($propertyName)
		{
			case 'validLengths':
			case 'validPrefixes':
				if (is_array($value)) $value = implode(',', $value);
		}

		parent::__set($propertyName, $value);
	}
	
	public function __get($propertyName)
	{
		$value = parent::__get($propertyName);
		
		switch ($propertyName)
		{
			case 'validLengths':
			case 'validPrefixes':
				$value = explode(',', $value);
				foreach ($value as $i => $v) $value[$i] = intval($v);
		}

		return $value;
	}

	// Assume credit card name is independent of the card type
	public static function isValidCreditCardName($strName)
	{
		// Check that the string is not empty and has no leading or trailing whitespace
		if (!DO_SalesValidation::isTrimmed($strName) || $strName === '')
		{
			return false;
		}
		
		// Check for the presence of illegal chars
		return DO_SalesValidation::hasIllegalChars($strName, "`~!@#\$%^*()=_+{}[]|\\;:\"<>,/?\n\r\t")? false : true;
	}

	// Assumes $strNumber is not encrypted
	public function isValidCreditCardNumber($strNumber)
	{
		if (!preg_match("/^[0-9]*$/", $strNumber))
		{
			//echo "/* $value - bad content */\n";
			return false;
		}

		// Check the length
		if (array_search(strlen($strNumber), $this->validLengths) === false) 
		{
			//echo "/* $value - bad length */\n";
			return false;
		}
		
		// Check the prefix
		$prefixes = $this->validPrefixes;
		
		$ok = false;
		
		foreach ($prefixes as $prefix)
		{
			if (substr($strNumber, 0, strlen($prefix)) == $prefix)
			{
				$ok = true;
				break;
			}
		}
		
		if (!$ok)
		{
			//echo "/* $value - bad prefix */\n";
			return false;
		}

		// Check the luhn
		return DO_SalesValidation::isValidLuhnNumber($strNumber);
	}

	// Assumes $strCVV is not encrypted
	public function isValidCVV($strCVV)
	{
		if (!preg_match("/^\\d*$/", $strCVV))
		{
			return false;
		}
		
		// Check the length
		if ($this->cvvLength != strlen($strCVV)) 
		{
			return false;
		}
		return true;
	}

}

?>