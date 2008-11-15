<?php

class Product_Type_Module_Service_Inbound extends Product_Type_Module
{

	public function loadDataForSaleItem(DO_Sales_SaleItem $saleItem)
	{
		$products = DO_Sales_SaleItemServiceInbound::listForSaleItem($saleItem);
		if (!count($products)) throw new Exception('No sale item details found for Inbound Service.');
		$product = $products[0];
		
		$productDetails = new stdClass();
		$productDetails->id = $product->id;
		$productDetails->fnn = $product->fnn;
		$productDetails->answer_point = $product->answerPoint;
		$productDetails->configuration = $product->configuration;
		$productDetails->has_complex_configuration = $product->hasComplexConfiguration;
		
		return $productDetails;
	}

	public function validateDetails(stdClass $productDetails)
	{
		
	}

	public function saveProductDetailsForSaleItem(stdClass $productDetails, DO_Sales_SaleItem $saleItem, $bolValidateOnly=false)
	{
		$product = new DO_Sales_SaleItemServiceInbound();
		$product->saleItemId = $bolValidateOnly ? 0 : $saleItem->id;
		$product->fnn = $productDetails->fnn;
		$product->answerPoint = $productDetails->answer_point;
		$product->configuration = $productDetails->configuration;
		$product->hasComplexConfiguration = $productDetails->has_complex_configuration;
		$product->isValid(true);
		if (!$bolValidateOnly)
		{
			$product->save();
		}
	}

}

?>
