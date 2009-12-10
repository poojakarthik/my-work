<?php

class DO_Sales_SaleAccountDirectDebitBankAccount extends DO_Sales_Base_SaleAccountDirectDebitBankAccount
{
	public function __get($name)
	{
		$value = parent::__get($name);
		switch ($name)
		{
			case 'accountNumber':
				//$value = Application::decrypt($value);
				$value = substr($value,0,2) 
						. str_repeat('#',  (strlen($value) < 6) ? 0 : (strlen($value) - 6)) 
						. substr($value,-4);
				$value = implode(' ', str_split($value, 4));
				break;
			
			case 'bankBsb':
				//$value = Application::decrypt($value);
				break;
			
		}
		
		return $value;
	}
	
	public function __set($name, $value)
	{
		if ($value !== null)
		{
			// Only string values need to be sanitized at this high a level.  Everything else is done at a lower level
			switch ($name)
			{
				case 'bankName':
					// Treat it as a buisness name
					$value = DO_SalesSanitation::cleanBusinessName($value);
					break;
				
				case 'bankBsb':
					$value = DO_SalesSanitation::cleanBankBSB($value);
					break;
					
				case 'accountName':
					$value = DO_SalesSanitation::cleanBankAccountName($value);
					break;
					
				case 'accountNumber':
					$value = DO_SalesSanitation::cleanBankAccountNumber($value);
					break;
			}
		}
		
		return parent::__set($name, $value);
	}

	protected function _isValidValue($propertyName, $value)
	{
		// This bit does low-level validation based on the associated field of the database table that the class represents.
		// It handles things such as string length, data type and nullability constraints
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
			case 'bankName':
				return DO_SalesValidation::isValidBusinessName($value);
				break;
				
			case 'bankBsb':
				return DO_SalesValidation::isValidBankBSB($value);
				break;
				
			case 'accountName':
				return DO_SalesValidation::isValidBankAccountName($value);
				break;
				
			case 'accountNumber':
				return DO_SalesValidation::isValidBankAccountNumber($value);
				break;
			
			default:
				// No extra validation - assume is correct
				return true;
		}
	}

}

?>