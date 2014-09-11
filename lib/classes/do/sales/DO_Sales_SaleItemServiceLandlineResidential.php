<?php

class DO_Sales_SaleItemServiceLandlineResidential extends DO_Sales_Base_SaleItemServiceLandlineResidential
{
	public function __set($propertyName, $value)
	{
		if ($value !== null)
		{
			switch($propertyName)
			{
				case 'endUserGivenName':
					$value = DO_SalesSanitation::cleanPersonFirstName($value);
					break;

				case 'endUserFamilyName':
					$value = DO_SalesSanitation::cleanPersonLastName($value);
					break;

				case 'endUserEmployer':
					$value = DO_SalesSanitation::cleanBusinessName($value);
					break;
				
				case 'endUserOccupation':
					$value = DO_SalesSanitation::fixProperNounCasing($value);
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
			case 'endUserGivenName':
				return DO_SalesValidation::isValidPersonFirstName($value);

			case 'endUserFamilyName':
				return DO_SalesValidation::isValidPersonLastName($value);

			case 'endUserEmployer':
				return DO_SalesValidation::isValidBusinessName($value);

			case 'endUserOccupation':
				return (!DO_SalesValidation::isTrimmed($value) || $value === '' || DO_SalesValidation::hasIllegalChars($value, "`~!@#\$%^*=_+{}[]|\\;:\"<>?\n\r\t"))? false : true;
				
			case 'endUserDob':
				// Date of birth has to be in the past
				return ($value <= date("Y-m-d"));

			default:
				// No validation - assume is correct
				return true;
		}
	}
	
}

?>