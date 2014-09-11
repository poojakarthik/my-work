<?php

class DO_Sales_SaleItemServiceLandlineBusiness extends DO_Sales_Base_SaleItemServiceLandlineBusiness
{
	public function __set($propertyName, $value)
	{
		if ($value !== null)
		{
			switch($propertyName)
			{
				case 'companyName':
					$value = DO_SalesSanitation::cleanBusinessName($value);
					break;
					
				case 'tradingName':
					$value = DO_SalesSanitation::cleanTradingName($value);
					break;
					
				case 'abn':
					$value = DO_SalesSanitation::cleanABN($value);
					break;
			}
		}
		
		return parent::__set($propertyName, $value);
	}
	
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		if ($value === null)
		{
			// We have already done the low level check to see if the field is manditory, so if the value is still set to null, then it should be considered valid.
			// Although this doesn't take into account scenarios where a value can only be set to null, when some other value is set to a specific value.
			// Validation rules of that nature should be declared in the class' isValid() method
			return true;
		}

		switch ($propertyName)
		{
			case 'companyName':
				return DO_SalesValidation::isValidBusinessName($value);
				
			case 'tradingName':
				return DO_SalesValidation::isValidTradingName($value);
				
			case 'abn':
				return DO_SalesValidation::isValidABN($value);

			default:
				// No validation - assume has already been validated
				return true;
		}
	}
}

?>