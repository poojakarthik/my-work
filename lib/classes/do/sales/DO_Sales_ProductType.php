<?php

class DO_Sales_ProductType extends DO_Sales_Base_ProductType
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}

	public static function getForModule($strModule)
	{
		return self::getFor(array('module' => $strModule));
	}
	
	static function listProductTypesForDealerAndVendor($intDealerId, $intVendorId)
	{
		$details = self::getProductTypesForDealerAndVendor($intDealerId, $intVendorId);

		$arrInstances = array();
		$matched = false;
		foreach($details as $detail)
		{
			$instance = new DO_Sales_ProductType($detail, true);
			$instance->setSaved(true);
			$arrInstances[] = $instance;
			$matched = true;
		}
		
		return $arrInstances;
	}

	/*
	 * function getProductTypesForVendor()
	 *
	 * Returns an array, builds a list of products available for the selected vendor.
	 */
	static function getProductTypesForVendor($intVendorId)
	{

		$dataSource = self::getDataSource();

		$strSQL = "
		SELECT id, description, module, product_category_id, name 
			FROM product_type 
			WHERE id IN (
				SELECT product_type_id 
				FROM product 
				WHERE vendor_id = '$intVendorId') ORDER BY name ASC";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to build a list of products: " . $result->getMessage());

		}

		$arrProductTypes = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrProductTypes;

	}


	/*
	 * function getProductTypesForDealerAndVendor()
	 *
	 * Returns an array, builds a list of products available to the selected($intDealerId) vendor.
	 */
	static function getProductTypesForDealerAndVendor($intDealerId, $intVendorId)
	{

		$dataSource = self::getDataSource();

		$strSQL = "
		SELECT id, description, module, product_category_id, name 
			FROM product_type 
			WHERE id IN (
				SELECT product_type_id 
				FROM product 
				WHERE vendor_id = '$intVendorId'
			AND id IN (
				SELECT product_id 
				FROM dealer_product 
				WHERE dealer_id = '$intDealerId'
			)) ORDER BY name ASC";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to build a list of products: " . $result->getMessage());

		}

		$arrProductTypes = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

		return $arrProductTypes;

	}


	/*
	 * function getProductTypeNameFromId()
	 * Returns the name of a product type.
	 *
	 */
	static function getProductTypeNameFromId($intId)
	{

		$dataSource = self::getDataSource();

		$strSQL = "
		SELECT name 
			FROM product_type 
			WHERE id='$intId';";

		$result = $dataSource->query($strSQL);

		if(PEAR::isError($result))
		{
			throw new Exception("Failed to build a list of products: " . $result->getMessage());

		}

		$arrProductTypeName = $result->fetchRow(MDB2_FETCHMODE_OBJECT);

		return $arrProductTypeName;

	}

}

?>