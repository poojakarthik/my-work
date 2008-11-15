<?php

class DO_Sales_Product extends DO_Sales_Base_Product
{

	public static function listProductIdAndNameForProductTypeIdAndVendorId($productTypeId, $vendorId)
	{

		$dataSource = self::getDataSource();



		$strSQL = "
		SELECT product.id as \"id\",product.name as \"name\"
		FROM product
		WHERE product.vendor_id='$vendorId'
		AND product.product_type_id = '$productTypeId'
		AND product.product_status_id = 1; "; // Active // WIP - this should be coded properly!

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to build a list of products: " . $result->getMessage());
		}

		$arrProductList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrProductList;
	}

	/*
	 * function getProductTypesForVendor()
	 *
	 * Returns an array, builds a list of products available to the selected($intDealerId) vendor.
	 */
	static function getProductListForVendor($intProductTypeId,$intVendorId)
	{

		$dataSource = self::getDataSource();

		// Escape.. $intDealerId

		$strSQL = "
		SELECT id,description 
		FROM product
		WHERE product_type_id='$intProductTypeId' 
		AND vendor_id='$intVendorId';";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to build a list of products: " . $result->getMessage());

		}

		$arrProductList = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrProductList;

	}

}

?>