<?php

class DO_Sales_SaleItemServiceLandlineBusiness extends DO_Sales_Base_SaleItemServiceLandlineBusiness
{
	public function __set($propertyName, $value)
	{
		switch ($propertyName)
		{
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

			case 'abn':
				// We know it is 11 chars long
				
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
}

?>