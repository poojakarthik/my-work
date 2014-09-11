<?php

class DO_Sales_SaleItemServiceAdsl extends DO_Sales_Base_SaleItemServiceAdsl
{
	public function __set($propertyName, $value)
	{
		if ($value !== null)
		{
			// Only string values need to be sanitized at this high a level.  Everything else is done at a lower level
			switch ($propertyName)
			{
				case 'fnn':
					$value = DO_SalesSanitation::cleanFNN($value);
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
			case 'fnn':
				return DO_SalesValidation::isValidLandlineFNN($value);

			case 'addressLine1':
			case 'addressLine2':
				return DO_SalesValidation::isValidAddressLine($value);

			case 'suburb':
				return DO_SalesValidation::isValidAddressSuburb($value);

			case 'postcode':
				return DO_SalesValidation::isValidPostcode($value);

			default:
				// No more validation - assume is correct
				return true;
		}
	}
	
	public function isValid($bolThrowException=false)
	{
		$arrErrors = array();
		
		try
		{
			// Do standard validation
			if (!parent::isValid($bolThrowException))
			{
				// This is caught in this try block which will return false because $bolThrowException == false
				throw new DO_Exception_Validation($this->getObjectLabel(), "Something was invalid, but bolThrowException == false, so a DO_Exception_Validation wasn't thrown before now");
			}
	
			// Retrieve the associated sale item if one has been set (sometimes this isn't set, if we are just validating an object that hasn't been saved to the database yet)
			if ($this->isSaved() || $this->saleItemId !== null)
			{
				$doSaleItem = $this->getSaleItem();
			
				// Retrieve the associated product (we are not validating the sale item so don't bother checking if $doSaleItem.productId is set)
				$doProduct = $doSaleItem->getProduct();
		
				// Make sure the product relating to this object (via the sale item) is of product_type 'ADSL' (product_type.id == 3)
				if ($doProduct->productTypeId != DO_Sales_ProductType::SERVICE_ADSL)
				{
					$arrErrors[] = "The related product '{$doProduct->name}' isn't an ADSL service.";
				}
			}
		}
		catch (DO_Exception_Validation $e)
		{
			// Add the errors to the greater list of errors
			$arrErrors = array_merge($arrErrors, $e->errors);
		}

		if (count($arrErrors))
		{
			// Errors were encountered
			if (!$bolThrowException)
			{
				return false;
			}
			throw new DO_Exception_Validation($this->getObjectLabel(), $arrErrors);
		}
		
		return true;
	}

	
	//------------------------------------------------------------------------//
	// getForSaleItem
	//------------------------------------------------------------------------//
	/**
	 * getForSaleItem()
	 *
	 * Returns a DO_Sales_SaleItemServiceAdsl object corresponding to the passed DO_Sales_SaleItem object
	 *
	 * Returns a DO_Sales_SaleItemServiceAdsl object corresponding to the passed DO_Sales_SaleItem object
	 * There should only ever be one sale_item_service_Adsl object relating to a sale_item
	 * 
	 * @param	DO_Sales_SaleItem	$doSaleItem					The sale item representing an intance of an Adsl Service product
	 * @param	boolean				$bolExceptionOnNotFound		If TRUE, then throws an exception if the sale_item_service_adsl record can't be found
	 * 															If FALSE, then returns NULL if the sale_item_service_adsl record can't be found
	 * 
	 * @return	mixed				DO_Sales_SaleItemServiceAdsl object when found
	 * 								NULL when can't be found and $bolExceptionOnNotFound == FALSE
	 *
	 * @method
	 */
	public static function getForSaleItem(DO_Sales_SaleItem $doSaleItem, $bolExceptionOnNotFound=FALSE)
	{
		$arrSaleItems = self::listForSaleItem($doSaleItem);
		
		$intCount = count($arrSaleItems);
		
		if ($intCount == 0)
		{
			if ($bolExceptionOnNotFound)
			{
				throw new Exception("Cannot find SaleItemServiceAdsl object for SaleItem with id: {$doSaleItem->id}");
			}
			else
			{
				return NULL;
			}
		}
		elseif ($intCount > 1)
		{
			throw new Exception("Found multiple SaleItemServiceAdsl objects ($intCount found) relating to the SaleItem with id: {$doSaleItem->id}");
		}
		else
		{
			return current($arrSaleItems);
		}
	}
	
}

?>