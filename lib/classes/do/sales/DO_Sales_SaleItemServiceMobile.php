<?php

class DO_Sales_SaleItemServiceMobile extends DO_Sales_Base_SaleItemServiceMobile
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
				
				case 'simPuk':
					$value = self::cleanSimPUK($value);
					break;

				case 'currentProvider':
					$value = DO_SalesSanitation::cleanBusinessName($value);
					break;
					
				case 'comments':
				case 'currentAccountNumber':
					$value = DO_SalesSanitation::removeExcessWhitespace($value);
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
			case 'dob':
				// Make sure the date of birth is in the past (or today)
				return ($value <= date('Y-m-d'));
				break;
			
			case 'fnn':
				return DO_SalesValidation::isValidMobileMSN($value);
				break;
				
			case 'simPuk':
				return self::isValidSimPUK($value);
				break;

			case 'currentProvider':
				return DO_SalesValidation::isValidBusinessName($value);
				break;

			default:
				// No validation - assume is correct already as is not for data source
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
	
			// Make sure the Service Mobile Origin has been set appropriately
			// DO_Sales_Base_SaleItemServiceMobile::_isValidValue() checks that serviceMobileOriginId is not null, and points to an actual service_mobile_origin record
			$objServiceMobileOrigin = $this->getServiceMobileOrigin();
			
			// Retrieve the associated sale item if one has been set (sometimes this isn't set, if we are just validating an object that hasn't been saved to the database yet)
			if ($this->isSaved() || $this->saleItemId !== null)
			{
				$doSaleItem = $this->getSaleItem();
				
				// Retrieve the associated product (we are not validating the sale item so don't bother checking if $doSaleItem.productId is set)
				$doProduct = $doSaleItem->getProduct();

				// Make sure the product relating to this object (via the sale item) is of product_type 'Mobile' (product_type.id == 2)
				if ($doProduct->productTypeId != DO_Sales_ProductType::SERVICE_MOBILE)
				{
					$arrErrors[] = "The related product '{$doProduct->name}' isn't a mobile service.";
				}
			}
			
			// Make sure all manditory fields have been supplied, based on the origin of the Mobile Service (the origin dictates which fields are manditory and which aren't)
			switch ($objServiceMobileOrigin->id)
			{
				case DO_Sales_ServiceMobileOrigin::NEW_SERVICE:
					// fnn is only manditory if the sale item's status is 'dispatched', or 'completed' (sale_item_service_mobile.fnn should be sale_item_service_mobile.msn)
					if (($this->fnn === null) && ($doSaleItem->saleItemStatusId == DO_Sales_SaleItemStatus::DISPATCHED || $doSaleItem->saleItemStatusId == DO_Sales_SaleItemStatus::COMPLETED))
					{
						$arrErrors[] = "Phone Number must be specified.";
					}
					break;
					
				case DO_Sales_ServiceMobileOrigin::EXISTING_PRE_PAID:
					// dob and fnn are manditory
					if ($this->fnn === null)
					{
						$arrErrors[] = "Phone Number must be specified.";
					}
					if ($this->dob === null)
					{
						$arrErrors[] = "Date Of Birth must be specified.";
					}
					break;
					
				case DO_Sales_ServiceMobileOrigin::EXISTING_POST_PAID:
					// fnn, current_provider, current_account_number
					if ($this->fnn === null)
					{
						$arrErrors[] = "Phone Number must be specified.";
					}
					if ($this->currentProvider === null)
					{
						$arrErrors[] = "Current Provider must be specified.";
					}
					if ($this->currentAccountNumber === null)
					{
						$arrErrors[] = "Current Account Number must be specified.";
					}
					break;
					
				default:
					throw new Exception("ServiceMobileOrigin: '{$objServiceMobileOrigin->name}' does not support validating SaleItemServiceMobile details.");
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
	 * Returns a DO_Sales_SaleItemServiceMobile object corresponding to the passed DO_Sales_SaleItem object
	 *
	 * Returns a DO_Sales_SaleItemServiceMobile object corresponding to the passed DO_Sales_SaleItem object
	 * There should only ever be one sale_item_service_mobile object relating to a sale_item
	 * 
	 * @param	DO_Sales_SaleItem	$doSaleItem					The sale item representing an intance of a Mobile Service product
	 * @param	boolean				$bolExceptionOnNotFound		If TRUE, then throws an exception if the sale_item_service_mobile record can't be found
	 * 															If FALSE, then returns NULL if the sale_item_service_mobile record can't be found
	 * 
	 * @return	mixed				DO_Sales_SaleItemServiceMobile object when found
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
				throw new Exception("Cannot find SaleItemServiceMobile object for SaleItem with id: {$doSaleItem->id}");
			}
			else
			{
				return NULL;
			}
		}
		elseif ($intCount > 1)
		{
			throw new Exception("Found multiple SaleItemServiceMobile objects ($intCount found) relating to the SaleItem with id: {$doSaleItem->id}");
		}
		else
		{
			return current($arrSaleItems);
		}
	}
	
	// Sanitizes a sim PUK code
	public static function cleanSimPUK($strSimPUK)
	{
		// Clean whitespace
		$strSimPUK = DO_SalesSanitation::removeExcessWhitespace($strSimPUK);
		
		// Remove any reasonable punctuation
		return str_replace(array(' ', '-'), '', $strSimPUK);
	}
	
	// Validates a sim PUK code
	public static function isValidSimPUK($strSimPUK)
	{
		// Just make sure every character is a digit and it is at least 7 digits long
		return preg_match("/^\\d{7,}$/", $strSimPUK);
	}
	
}

?>
