<?php

class DO_Sales_SaleAccount extends DO_Sales_Base_SaleAccount
{
	private static $arrHistoricalProperties = array('billDeliveryTypeId', 'billPaymentTypeId', 'directDebitTypeId');
	
	protected function setValueFromDataSource($propertyName, $value)
	{
		parent::setValueFromDataSource($propertyName, $value);
		if (array_search($propertyName, self::$arrHistoricalProperties) !== false)
		{
			$this->{'original_'.$propertyName} = $value;
		}
	}
	
	public function hasHistoricalChange()
	{
		foreach (self::$arrHistoricalProperties as $propertyName)
		{
			if ($this->{$propertyName} !== $this->{'original_'.$propertyName})
			{
				return true;
			}
		}
		return false;
	}

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
		$history->changedOn = Data_Source_Time::currentTimestamp($this->getDataSource());
		$history->changedBy = $dealerId;
		$history->billPaymentTypeId = $this->billPaymentTypeId;
		$history->directDebitTypeId = $this->directDebitTypeId;
		$history->billDeliveryTypeId = $this->billDeliveryTypeId;
		$history->save();
		
		return $return;
	}
	
	//------------------------------------------------------------------------//
	// getForSale
	//------------------------------------------------------------------------//
	/**
	 * getForSale()
	 *
	 * Returns a DO_Sales_SaleAccount object corresponding to the passed DO_Sales_Sale object
	 *
	 * Returns a DO_Sales_SaleAccount object corresponding to the passed DO_Sales_Sale object
	 * There should only ever be one sale_account record relating to a sale record
	 * 
	 * @param	DO_Sales_Sale	$doSale						The sale object
	 * @param	boolean			$bolExceptionOnNotFound		If TRUE, then throws an exception if the sale_account record can't be found
	 * 														If FALSE, then returns NULL if the sale_account record can't be found
	 * 
	 * @return	mixed				DO_Sales_SaleAccount object when found
	 * 								NULL when can't be found and $bolExceptionOnNotFound == FALSE
	 *
	 * @method
	 */
	public static function getForSale(DO_Sales_Sale $doSale, $bolExceptionOnNotFound=FALSE)
	{
		$arrSales = self::listForSale($doSale);
		
		$intCount = count($arrSales);
		
		if ($intCount == 0)
		{
			if ($bolExceptionOnNotFound)
			{
				throw new Exception("Cannot find SaleAccount object for Sale with id: {$doSale->id}");
			}
			else
			{
				return NULL;
			}
		}
		elseif ($intCount > 1)
		{
			throw new Exception("Found multiple SaleAccount objects ($intCount found) relating to the Sale with id: {$doSale->id}");
		}
		else
		{
			return current($arrSales);
		}
	}
	
}

?>
