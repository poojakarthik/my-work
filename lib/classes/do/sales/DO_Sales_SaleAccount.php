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
		if ($value !== null)
		{
			// Only string values need to be sanitized at this high a level.  Everything else is done at a lower level
			switch ($propertyName)
			{
				case 'acn':
					$value = DO_SalesSanitation::cleanACN($value);
					break;
	
				case 'abn':
					$value = DO_SalesSanitation::cleanABN($value);
					break;
	
				case 'businessName':
					$value = DO_SalesSanitation::cleanBusinessName($value);
					break;
	
				case 'tradingName':
					$value = DO_SalesSanitation::cleanTradingName($value);
					break;
				
				case 'addressLine1':
				case 'addressLine2':
					$value = DO_SalesSanitation::cleanAddressLine($value);
					break;
				
				case 'suburb':
					$value = DO_SalesSanitation::cleanAddressSuburb($value);
					break;

				case 'postcode':
					$value = DO_SalesSanitation::cleanPostcode($value);
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
		
		// We don't really need to validate values representing foreign keys, because they will be enforced by the database

		switch ($propertyName)
		{
			case 'postcode':
				return DO_SalesValidation::isValidPostcode($value);
				
			case 'acn':
				return DO_SalesValidation::isValidACN($value);
								
			case 'abn':
				return DO_SalesValidation::isValidABN($value);
				
			case 'businessName':
				return DO_SalesValidation::isValidBusinessName($value);
				
			case 'tradingName':
				return DO_SalesValidation::isValidTradingName($value);
				
			case 'addressLine1':
			case 'addressLine2':
				return DO_SalesValidation::isValidAddressLine($value);
			
			case 'suburb':
				return DO_SalesValidation::isValidAddressSuburb($value);

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
		
		if ($this->hasHistoricalChange())
		{
			$history = new DO_Sales_SaleAccountHistory();
			$history->saleAccountId = $this->id;
			$history->changedOn = Data_Source_Time::currentTimestamp($this->getDataSource());
			$history->changedBy = $dealerId;
			$history->billPaymentTypeId = $this->billPaymentTypeId;
			$history->directDebitTypeId = $this->directDebitTypeId;
			$history->billDeliveryTypeId = $this->billDeliveryTypeId;
			$history->save();
		}
		
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
	
	// Retrieves the value part from the sale_account.external_reference string
	// This string should be of the form "Account.Id=123" where 123 is the value 
	public function getExternalReferenceValue()
	{
		return intval(substr($this->externalReference, 11));
	}
}

?>
