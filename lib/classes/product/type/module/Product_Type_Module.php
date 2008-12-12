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

	//------------------------------------------------------------------------//
	// getModuleClassNameForProduct
	//------------------------------------------------------------------------//
	/**
	 * getModuleClassNameForProduct()
	 *
	 * Returns the class name of the Product Type Module corresponding to the product
	 *
	 * Returns the class name of the Product Type Module corresponding to the product
	 * 
	 * @param	DO_Sales_SaleItem	$doSaleItem			The sale item that has been cancelled
	 * @param	integer				$intDealerId		id of the dealer who actioned the sale item cancellation
	 * @param	string				$strReason			Reason for the sale item cancellation.  This will be appended to the system note
	 * @param	bool				$bolCreateNote		if TRUE, then a system note will be created
	 * 													if FALSE, then a system note will not be created
	 * @return	void
	 *
	 * @method
	 */
	public static function getModuleClassNameForProduct(DO_Sales_Product $doProduct)
	{
		$doProductType	= $doProduct->getProductType();
		
		return __CLASS__ . "_" . $doProductType->module;
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
	public static abstract function onSaleItemCancellation(DO_Sales_SaleItem $doSaleItem, $intDealerId);

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
	public static abstract function getSaleItemDescription(DO_Sales_SaleItem $doSaleItem, $bolIncludeProductName=FALSE, $bolIncludeProductTypeName=FALSE);

	// Throw an exception containing details of validation errors
	public abstract function validateDetails(stdClass $productDetails);
	
	public abstract function saveProductDetailsForSaleItem(stdClass $productDetails, DO_Sales_SaleItem $saleItem);
}

?>
