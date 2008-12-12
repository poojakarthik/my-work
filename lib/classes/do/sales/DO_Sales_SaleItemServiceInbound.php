<?php

class DO_Sales_SaleItemServiceInbound extends DO_Sales_Base_SaleItemServiceInbound
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
				return preg_match("/^((13\\d{4})|(1[389]00\\d{6}))$/", $value);

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
	 * Returns a DO_Sales_SaleItemServiceInbound object corresponding to the passed DO_Sales_SaleItem object
	 *
	 * Returns a DO_Sales_SaleItemServiceInbound object corresponding to the passed DO_Sales_SaleItem object
	 * There should only ever be one sale_item_service_inbound object relating to a sale_item
	 * 
	 * @param	DO_Sales_SaleItem	$doSaleItem					The sale item representing an intance of an inbound Service product
	 * @param	boolean				$bolExceptionOnNotFound		If TRUE, then throws an exception if the sale_item_service_inbound record can't be found
	 * 															If FALSE, then returns NULL if the sale_item_service_inbound record can't be found
	 * 
	 * @return	mixed				DO_Sales_SaleItemServiceInbound object when found
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
				throw new Exception("Cannot find SaleItemServiceInbound object for SaleItem with id: {$doSaleItem->id}");
			}
			else
			{
				return NULL;
			}
		}
		elseif ($intCount > 1)
		{
			throw new Exception("Found multiple SaleItemServiceInbound objects ($intCount found) relating to the SaleItem with id: {$doSaleItem->id}");
		}
		else
		{
			return current($arrSaleItems);
		}
	}

}

?>