<?php

class DO_Sales_SaleItemServiceAdsl extends DO_Sales_Base_SaleItemServiceAdsl
{
	protected function _isValidValue($propertyName, $value)
	{
		if (!parent::_isValidValue($propertyName, $value))
		{
			return false;
		}

		switch ($propertyName)
		{

			case 'fnn':
				return preg_match("/^0[12378]\\d{8}$/", $value);

			case 'postcode':
				return preg_match("/^\\d{4}$/", $value);

			default:
				// No more validation - assume is correct
				return true;

		}
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