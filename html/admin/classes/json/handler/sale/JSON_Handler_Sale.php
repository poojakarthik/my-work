<?php

class JSON_Handler_Sale extends JSON_Handler
{
	public function load($saleId)
	{
		$sale = DO_Sales_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}
		return Sales_Portal::getStdClassForDOSale($sale);
	}

	// This is invoked via ajax
	public function listProductTypesForVendor($intVendorId)
	{
		$intVendorId = intval($intVendorId);
		
		$list = DO_Sales_ProductType::getProductTypesVendor($intVendorId);
		
		$arrModuleProductType = new stdClass();
		$arrModuleProductType->ids = array();
		$arrModuleProductType->labels = array();
		foreach($list as $instance) 
		{
			$arrModuleProductType->ids[] = $instance['module'];
			$arrModuleProductType->labels[] = $instance['name'];
		}
		return $arrModuleProductType;
	}
	
	// This is invoked via ajax
	public function listProductsForProductTypeModuleAndVendor($strProductTypeModule, $intVendorId)
	{
		$productType = DO_Sales_ProductType::getForModule($strProductTypeModule);
		if (!$productType) throw new Exception('Invalid product type selected: ' . $strProductTypeModule);

		$intVendorId = intval($intVendorId);

		$intProductTypeId = $productType->id;
		
		$list = DO_Sales_Product::listProductIdAndNameForProductTypeIdAndVendorId($intProductTypeId, $intVendorId);
		
		$arrProductIdName = new stdClass();
		$arrProductIdName->ids = array();
		$arrProductIdName->labels = array();
		foreach($list as $instance) 
		{
			$arrProductIdName->ids[] = $instance['id'];
			$arrProductIdName->labels[] = $instance['name'];
		}
		return $arrProductIdName;
		
	}
}


?>
