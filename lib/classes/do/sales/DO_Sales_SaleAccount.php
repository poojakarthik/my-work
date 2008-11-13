<?php

class DO_Sales_SaleAccount extends DO_Sales_Base_SaleAccount
{
	public function __set($propertyName, $value)
	{
		switch ($propertyName)
		{
			case 'acn':
			case 'abn':
				if ($value !== null)
				{
					$value = preg_replace("/[^0-9]+/", "", $value);
				}
				break;
		}
		
		return parent::__set($propertyName, $value);
	}
	
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{

			case 'postcode':
				return preg_match("/^\\d{4}$/", $value);
				
			case 'acn':
				// We know it is null or 9 chars long
				if ($value === null) return true;
				
				// Ensure that they are all digits
				if (!preg_match("/^[0-9]{9}$/", $value))
				{
					return false;
				}

				// Check the check digit
				
				// (i) apply weighting to digits 0 to 7 and (ii) sum the products
				$total = 0;
				for ($i = 0; $i < 8; $i++)
				{
					$total += ((8 - $i) * intval($value[$i]));
				}
				
				// (iii) divide by 10 to obtain remainder, (iv) complement the remainder to 10 (if complement = 10, set to 0) and (v) compare to character 8
				return intval($value[8]) == ((10 - ($total % 10)) % 10);
								
			case 'abn':
				// We know it is null or 11 chars long
				if ($value === null) return true;
				
				// Ensure that they are all digits
				if (!preg_match("/^[0-9]{11}$/", $value))
				{
					return false;
				}
		
				// Official ABN validation Step 1:
				// Subtract 1 from the first (left most) digit to give a new eleven digit number
				$strABNStep1 = (intval($value[0]) - 1) . substr($value, 1);
			
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


			default:
				// No validation - assume has already been validated
				return true;

		}
	}

	public function save($dealerId)
	{
		$dealer = DO_Sales_Dealer::getForId($dealerId);
		if ($dealer == null)
		{
			throw new Exception('Invalid dealer ' . $dealerId . '. Unable to save ' . $this->getObjectLabel() . '.');
		}
		
		$return = parent::save();
		
		$history = new DO_Sales_SaleAccountHistory();
		$history->saleAccountId = $this->id;
		$history->changedOn = date('Y-m-d H:i:s');
		$history->changedBy = $dealerId;
		$history->billPaymentTypeId = $this->billPaymentTypeId;
		$history->directDebitTypeId = $this->directDebitTypeId;
		$history->billDeliveryTypeId = $this->billDeliveryTypeId;
		$history->save();
		
		return $return;
	}
}

?>
