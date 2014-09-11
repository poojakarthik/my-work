<?php

class DO_Sales_Product extends DO_Sales_Base_Product
{

	public static function listProductIdAndNameForDealerIdProductTypeIdAndVendorId($dealerId, $productTypeId, $vendorId)
	{
		$dataSource = self::getDataSource();

		// Sanitise the parameters because they are being directly inserted into SQL
		$dealerId		= intval($dealerId);
		$productTypeId	= intval($productTypeId);
		$vendorId		= intval($vendorId);

		$strSQL = "
		SELECT product.id as \"id\",product.name as \"name\"
		FROM product, dealer_product
		WHERE product.id = dealer_product.product_id 
		AND dealer_product.dealer_id = {$dealerId}
		AND product.vendor_id = {$vendorId}
		AND product.product_type_id = {$productTypeId}
		AND product.product_status_id = 1; "; // Active // WIP - this should be coded properly!

		$result = $dataSource->query($strSQL);

		if(MDB2::isError($result))
		{
			throw new Exception("Failed to build a list of products: " . $result->getMessage());
		}

		$arrProductList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrProductList;
	}

	public static function listProductIdAndNameForProductTypeIdAndVendorId($productTypeId, $vendorId)
	{
		$dataSource = self::getDataSource();

		// Sanitise the parameters because they are being directly inserted into SQL
		$productTypeId	= intval($productTypeId);
		$vendorId		= intval($vendorId);

		$strSQL = "
		SELECT product.id as \"id\",product.name as \"name\"
		FROM product
		WHERE product.vendor_id = {$vendorId}
		AND product.product_type_id = {$productTypeId}
		AND product.product_status_id = 1; "; // Active // WIP - this should be coded properly!

		$result = $dataSource->query($strSQL);

		if(MDB2::isError($result))
		{
			throw new Exception("Failed to build a list of products: " . $result->getMessage());
		}

		$arrProductList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrProductList;
	}
}

?>