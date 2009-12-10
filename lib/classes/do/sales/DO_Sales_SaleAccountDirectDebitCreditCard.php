<?php

class DO_Sales_SaleAccountDirectDebitCreditCard extends DO_Sales_Base_SaleAccountDirectDebitCreditCard
{
	private $_creditCardType = null;
	
	public function getCreditCardType()
	{
		if ($this->_creditCardType == null || $this->_creditCardType->id != $this->creditCardTypeId)
		{
			$this->_creditCardType = parent::getCreditCardType();
		}
		return $this->_creditCardType;
	}
	
	public function isValid($bolThrowException=false)
	{
		if (!parent::isValid($bolThrowException))
		{
			return false;
		}
		
		// Only check if it has expired, if it hasn't already been saved to the data source
		if ($this->id == null && $this->hasExpired())
		{
			if (!$bolThrowException)
			{
				return false;
			}
			throw new DO_Exception_Validation($this->getObjectLabel(), "This credit card has already expired");
		}
		
		return true;
	}
	
	// Returns true if the credit card has expired, else false
	// (assumes $this->expiryYear and $this->expiryMonth are valid)
	public function hasExpired()
	{
		$year = intval(date('Y'));
		if ($this->expiryYear < $year || ($this->expiryYear == $year && $this->expiryMonth < intval(date('m'))))
		{
			return true;
		}
		
		return false;
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
			case 'cardName':
				return DO_Sales_CreditCardType::isValidCreditCardName($value);
				break;
			
			case 'cardNumber':
				if (!$this->creditCardTypeId)
				{
					return false;
				}

				// Get the CreditCardType object
				$cct = $this->getCreditCardType();
				
				// Decrypt the credit card number
				@$value	= Application::decrypt($value);
				
				return $cct->isValidCreditCardNumber($value);
				break;
				
			case 'cvv':
				if (!$this->creditCardTypeId)
				{
					return false;
				}

				// Get the CreditCardType object
				$cct = $this->getCreditCardType();
				
				// Decrypt the cvv
				@$value = Application::decrypt($value);
				
				return $cct->isValidCVV($value);
				break;
				
			case 'expiryMonth':
				// Must be an integer between 1 and 12 inclusive
				return (DO_SalesValidation::isValidPositiveInteger($value) && ($value >= 1) && ($value <= 12));
				break;

			case 'expiryYear':
				// Must be a positive integer
				return (DO_SalesValidation::isValidPositiveInteger($value));
				break;

			default:
				// No extra validation - assume is correct
				return true;

		}
	}

	public function __get($name)
	{
		$value = parent::__get($name);
		switch ($name)
		{
			case 'cardNumber':
				$value = Application::decrypt($value);
				$value =  substr($value,0,4) 
						. str_repeat('#', strlen($value) < 8 ? 0 : (strlen($value) - 8) ) 
						. substr($value,-4);
				break;
			
			case 'cvv':
				$value = Application::decrypt($value);
				$value = str_repeat('#', strlen($value));
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
				case 'cardName':
					$value = DO_SalesSanitation::cleanCreditCardName($value);
					break;
					
				case 'cardNumber':
					$value = DO_SalesSanitation::cleanCreditCardNumber($value);
					$value = Application::encrypt($value);
					break;
					
				case 'cvv':
					$value = DO_SalesSanitation::cleanCreditCardCVV($value);
					$value = Application::encrypt($value);
					break;
			}
		}
		
		return parent::__set($name, $value);
	}

	protected function setValueFromDataSource($propertyName, $value)
	{
		switch ($propertyName)
		{

			case 'cardNumber':
			case 'cvv':
				// No conversion needed (string to string)
				$this->properties[$propertyName] = $value;
				return;

		}
		parent::setValueFromDataSource($propertyName, $value);
	}
	
}

?>
