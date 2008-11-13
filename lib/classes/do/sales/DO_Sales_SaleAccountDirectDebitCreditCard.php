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
		
		// We now need to check all of the properties for this record to ensure that the 
		// business rules are obeyed.
		$year = intval(date('Y'));
		if ($this->expiryYear < $year || ($this->expiryYear == $year && $this->expiryMonth < intval(date('m'))))
		{
			if ($bolThrowException) throw new Exception('The credit card has expired.');
			return false;
		}
		
		return true;
	}
	
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{

			case 'cardNumber':
			
				if (!$this->creditCardTypeId)
				{
					return false;
				}


				// Check the content
				
				@$value = Application::decrypt($value);

				if (!preg_match("/^[0-9]*$/", $value))
				{
					//echo "/* $value - bad content */\n";
					return false;
				}
				
			
				$cct = $this->getCreditCardType();
				

				// Check the length
				
				if (array_search(strlen($value), $cct->validLengths) === false) 
				{
					//echo "/* $value - bad length */\n";
					return false;
				}
			
				
				// Check the prefix
				
				$prefixes = $cct->validPrefixes;
				
				$ok = false;
				
				foreach ($prefixes as $prefix)
				{
					if (substr($value, 0, strlen($prefix)) == $prefix)
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
				return $this->checkLuhn($value);

			case 'cvv':
			
				if (!$this->creditCardTypeId)
				{
					return false;
				}
			

				// Check the content

				@$value = Application::decrypt($value);

				if (!preg_match("/^\\d*$/", $value))
				{
					return false;
				}
				

				$cct = $this->getCreditCardType();
				
				
				// Check the length
				
				if ($cct->cvvLength != strlen($value)) 
				{
					return false;
				}

			default:
				// No extra validation - assume is correct
				return true;

		}
	}
	
	
	private function checkLuhn($cardNumber)
	{
		$cardNumber = strval($cardNumber);
		$nrDigits = strlen($cardNumber);
		$digits = strrev('00'.$cardNumber);
		$total = 0;
		
		for ($i=0; $i<$nrDigits; $i+=2)
		{
			$d1 = intval($digits[$i]);
			$d2 = 2*intval($digits[$i + 1]);
			$d2 = ($d2 > 9) ? ($d2 - 9) : $d2;
			$total += $d1 + $d2;
			$total -= (($total >= 20) ? 20 : ($total >= 10 ? 10 : 0));
		}
		//echo "/* $cardNumber $nrDigits $total - bad luhn */\n";
		return ($total== 0);
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
		switch ($name)
		{
			case 'cardNumber':
			case 'cvv':
				$value = preg_replace("/[^0-9]+/", "", $value);
				$value = Application::encrypt($value);
				break;
			
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
