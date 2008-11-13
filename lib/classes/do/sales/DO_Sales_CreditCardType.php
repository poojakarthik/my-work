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

}

?>