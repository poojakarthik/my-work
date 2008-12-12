<?php

/*
 *  Extends the DO_Sales_SaleItem class, for actions specific to the Flex project
 */
class Sales_SaleItem extends DO_Sales_SaleItem
{
	/**
 	 * public listForSale()
	 *
	 * Retreives all instances of DO_Sales_SaleItem, the 
	 * source ('to-many' end) of the foreign key fk_sale_item_sale_id
	 * between tables sale_item and sale.
	 *
	 * @param $do DO_Sales_Base_Sale instance to retreive matching records for 
	 * @return array(DO_Sales_SaleItem) instances (empty array if none match)
 	 */
	public static function listForSale(DO_Sales_Base_Sale $do, $strSort=NULL, $strLimit=0, $strOffset=0)
	{
		// Retrieve the DO_Sales_SaleItem objects
		$arrDoSaleItems		= parent::listForSale($do, $strSort, $strLimit, $strOffset);
		$arrProps			= parent::getPropertyNames();
		$arrSalesSaleItems	= array();

		// Build Sales_SaleItem objects
		foreach ($arrDoSaleItems as $doSaleItem)
		{
			$arrData = array();
			
			foreach ($arrProps as $strProp)
			{
				$arrData[$strProp] = $doSaleItem->{$strProp};
			}
			$arrSalesSaleItems[] = new self($arrData, TRUE);
		}
		
		return $arrSalesSaleItems;
	}
	
	
	public static function getForId($intSaleId)
	{
		$doSale = parent::getForId($intSaleId);
		
		if ($doSale !== NULL)
		{
			$arrProps = parent::getPropertyNames();
			$arrData = array();
			
			foreach ($arrProps as $strProp)
			{
				$arrData[$strProp] = $doSale->{$strProp};
			}
			return new self($arrData, TRUE);
		}
		else
		{
			return NULL;
		}
	}
	
}
?>
