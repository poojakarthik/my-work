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
		$products = DO_Sales_SaleItemServiceInbound::listForSaleItem($saleItem);
		if (!count($products))
		{
			$product = new DO_Sales_SaleItemServiceInbound();
			$product->saleItemId = $bolValidateOnly ? null : $saleItem->id;
		}
		else
		{
			$product = $products[0];
		}

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

	//------------------------------------------------------------------------//
	// onSaleItemCancellation
	//------------------------------------------------------------------------//
	/**
	 * onSaleItemCancellation()
	 *
	 * Handles Product Type specific tasks that have to be carried out when a sale item, of this product type, is cancelled
	 *
	 * Handles Product Type specific tasks that have to be carried out when a sale item, of this product type, is cancelled
	 * This should be executed after a sale item has been cancelled
	 * 
	 * @param	DO_Sales_SaleItem	$doSaleItem			The sale item that has been cancelled
	 * @param	integer				$intDealerId		id of the dealer who actioned the sale item cancellation
	 * 
	 * @return	void
	 *
	 * @method
	 */
	public static function onSaleItemCancellation(DO_Sales_SaleItem $doSaleItem, $intDealerId)
	{
		$objDealer		= Dealer::getForId($intDealerId);
		$intEmployeeId	= ($objDealer->employeeId)? $objDealer->employeeId : Employee::SYSTEM_EMPLOYEE_ID;
		
		// Cancel the service in flex, if it exists
		$objFlexSaleItem = FlexSaleItem::getForExternalReference("sale_item.id={$doSaleItem->id}");
		if ($objFlexSaleItem !== NULL)
		{
			// The sale item has been imported into flex
			$objService = Service::getForId($objFlexSaleItem->serviceId, FALSE, TRUE);
			
			// Cancel the service (It is assumed that it hasn't already been cancelled)
			$objService->onSaleItemCancellation($intEmployeeId);
		}
	}

	//------------------------------------------------------------------------//
	// getSaleItemDescription
	//------------------------------------------------------------------------//
	/**
	 * getSaleItemDescription()
	 *
	 * Returns a string defining the Product Type specific details of a sale item.  Such as the phone number of a landline
	 *
	 * Returns a string defining the Product Type specific details of a sale item.  Such as the phone number of a landline
	 * 
	 * @param	DO_Sales_SaleItem		$doSaleItem					The sale item in question
	 * @param	boolean					$bolIncludeProductName
	 * @param	boolean					$bolIncludeProductTypeName
	 * 
	 * @return	string					The description of the sale item
	 *
	 * @method
	 */
	public static function getSaleItemDescription(DO_Sales_SaleItem $doSaleItem, $bolIncludeProductName=FALSE, $bolIncludeProductTypeName=FALSE)
	{
		// Try retrieving the associated record from the sale_item_service_inbound table
		$doSaleItemServiceInbound = DO_Sales_SaleItemServiceInbound::getForSaleItem($doSaleItem, TRUE);
		
		$strDescription = $doSaleItemServiceInbound->fnn;
		
		if ($bolIncludeProductName)
		{
			$doProduct = DO_Sales_Product::getForId($doSaleItem->productId);
			$strDescription = "{$doProduct->name} - {$strDescription}";
		}
		if ($bolIncludeProductTypeName)
		{
			if (!isset($doProduct))
			{
				$doProduct = DO_Sales_Product::getForId($doSaleItem->productId);
			}
			$doProductType = DO_Sales_ProductType::getForId($doProduct->productTypeId);
			
			$strDescription = "{$doProductType->name} - {$strDescription}";
		}
		
		return $strDescription;
	}

}

?>
