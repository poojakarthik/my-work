<?php

class DO_Sales_SaleItemServiceMobile extends DO_Sales_Base_SaleItemServiceMobile
{
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{
			case 'currentAccountNumber':
				// Required for Existing Post-Paid (3)
				if ($value == null && $this->serviceMobileOriginId == 3) // WIP - Code this properly!!!
				{
					return false;
				}
				return true;

			case 'dob':
				// Required for Existing Pre-Paid (2)
				if ($value == null && $this->serviceMobileOriginId == 2) // WIP - Code this properly!!!
				{
					return false;
				}
				return true;
			
			case 'fnn':
				if ($value == null || $value == '') 
				{
					// Required for Existing Post-Paid (3) and Existing Pre-Paid (2)
					if ($this->serviceMobileOriginId == 2 || $this->serviceMobileOriginId == 3) // WIP - Code this properly!!!
					{
						return false;
					}
					
					$saleItem = $this->getSaleItem();
					if ($saleItem->saleItemStatusId == DO_Sales_SaleItemStatus::DISPATCHED || $saleItem->saleItemStatusId == DO_Sales_SaleItemStatus::COMPLETED)
					{
						return false;
					}
					return true;
				}
				return preg_match("/^04\\d{8}$/", $value);

			default:
				// No validation - assume is correct already as is not for data source
				return true;

		}
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
}

?>
