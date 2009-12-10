<?php

class DO_Sales_Contact extends DO_Sales_Base_Contact
{
	public function __set($propertyName, $value)
	{
		if ($value !== null)
		{
			// Only string values need to be sanitized at this high a level.  Everything else is done at a lower level
			switch ($propertyName)
			{
				case 'externalReference':
					// Anything goes, for this one
					break;

				case 'firstName':
				case 'middleNames':
					$value = DO_SalesSanitation::cleanPersonFirstName($value);
					break;
					
				case 'lastName':
					$value = DO_SalesSanitation::cleanPersonLastName($value);
					break;
					
				case 'positionTitle':
					$value = DO_SalesSanitation::fixProperNounCasing(DO_SalesSanitation::removeExcessWhitespace($value));
					break;
					
				case 'username':
					// Just remove excess whitespace
					$value = DO_SalesSanitation::removeExcessWhitespace($value);
					break;
				
				case 'password':
					// Leave it alone
					break;
			}
		}
		
		return parent::__set($propertyName, $value);
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
			case 'firstName':
				return DO_SalesValidation::isValidPersonFirstName($value);
				break;

			case 'middleNames':
				return DO_SalesValidation::isValidPersonMiddleName($value);
				break;
				
			case 'lastName':
				return DO_SalesValidation::isValidPersonLastName($value);
				break;
				
			case 'positionTitle':
				// Check that the string is not empty and has no leading or trailing whitespace, and has no illegal chars
				return (!DO_SalesValidation::isTrimmed($value) || $value === '' || DO_SalesValidation::hasIllegalChars($value, "`~!@#\$%^*=_+{}[]|\\;:\"<>?\n\r\t"))? false : true;
				break;
				
			case 'username':
				return DO_SalesValidation::isValidUsername($value);
				break;
				
			case 'dateOfBirth':
				// Date of birth has to be in the past
				return ($value <= date("Y-m-d"));
				break;
				
			
			default:
				// No extra validation - assume is correct
				return true;
		}
	}
	
}

?>