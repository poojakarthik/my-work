<?php
/*Comment added in Flex*/
abstract class Product_Type_Module
{

	public function loadData()
	{
		return array();
	}
	
	public function loadDataForSaleItem(DO_Sales_SaleItem $saleItem)
	{
		return new stdClass();
	}
	
	// Throw an exception containing details of validation errors
	public abstract function validateDetails(stdClass $productDetails);
	
	public abstract function saveProductDetailsForSaleItem(stdClass $productDetails, DO_Sales_SaleItem $saleItem);

}

?>
