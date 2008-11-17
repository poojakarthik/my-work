<?php

class Product_Type_Module_Service_ADSL extends Product_Type_Module
{

	public function loadDataForSaleItem(DO_Sales_SaleItem $saleItem)
	{
		$products = DO_Sales_SaleItemServiceAdsl::listForSaleItem($saleItem);
		if (!count($products)) throw new Exception('No sale item details found for ADSL Service.');
		$product = $products[0];
		
		$productDetails = new stdClass();
		$productDetails->id = $product->id;
		$productDetails->fnn = $product->fnn;
		$productDetails->address_line_1 = $product->addressLine1;
		$productDetails->address_line_2 = $product->addressLine2;
		$productDetails->suburb = $product->suburb;
		$productDetails->postcode = $product->postcode;
		$productDetails->state_id = $product->stateId;
		
		return $productDetails;
	}

	public function validateDetails(stdClass $productDetails)
	{
		
	}

	public function saveProductDetailsForSaleItem(stdClass $productDetails, DO_Sales_SaleItem $saleItem, $bolValidateOnly=false)
	{
		$products = DO_Sales_SaleItemServiceAdsl::listForSaleItem($saleItem);
		if (!count($products))
		{
			$product = new DO_Sales_SaleItemServiceAdsl();
			$product->saleItemId = $bolValidateOnly ? 0 : $saleItem->id;
		}
		else
		{
			$product = $products[0];
		}

		$product->fnn = $productDetails->fnn;
		$product->addressLine1 = $productDetails->address_line_1;
		$product->addressLine2 = $productDetails->address_line_2;
		$product->suburb = $productDetails->suburb;
		$product->postcode = $productDetails->postcode;
		$product->stateId = $productDetails->state_id;
		$product->isValid(true);
		if (!$bolValidateOnly)
		{
			$product->save();
		}
	}

}

?>
