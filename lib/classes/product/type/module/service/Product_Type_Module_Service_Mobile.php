<?php

class Product_Type_Module_Service_Mobile extends Product_Type_Module
{

	public function loadData()
	{
		$data = new stdClass();

		$data->serviceMobileOrigin = $this->_toKeyArray(DO_Sales_ServiceMobileOrigin::listAll());

		return $data;
	}
	
	private function _toKeyArray($arrObj, $fields=array('id', 'description'))
	{
		$o = new stdClass();
		foreach ($fields as $field) $o->$field = array();
		foreach ($arrObj as $obj)
		{
			foreach ($fields as $field) $o->{$field}[] = $obj->$field;
		}
		return $o;
	}
	
	public function loadDataForSaleItem(DO_Sales_SaleItem $saleItem)
	{
		$products = DO_Sales_SaleItemServiceMobile::listForSaleItem($saleItem);
		if (!count($products)) throw new Exception('No sale item details found for Mobile Service.');
		$product = $products[0];
		
		$productDetails = new stdClass();
		$productDetails->id = $product->id;
		$productDetails->comments = $product->comments;
		$productDetails->current_account_number = $product->currentAccountNumber;
		$productDetails->current_provider = $product->currentProvider;
		$productDetails->dob = $product->dob;
		$productDetails->fnn = $product->fnn;
		$productDetails->sim_puk = $product->simPuk;
		$productDetails->sim_state_id = $product->simStateId;
		$productDetails->service_mobile_origin_id = $product->serviceMobileOriginId;
		
		return $productDetails;
	}

	public function validateDetails(stdClass $productDetails)
	{
		
	}

	public function saveProductDetailsForSaleItem(stdClass $productDetails, DO_Sales_SaleItem $saleItem, $bolValidateOnly=false)
	{
		$product = new DO_Sales_SaleItemServiceMobile();
		$product->saleItemId = $bolValidateOnly ? 0 : $saleItem->id;
		$product->comments = $productDetails->comments;
		$product->currentAccountNumber = $productDetails->current_account_number;
		$product->currentProvider = $productDetails->current_provider;
		$product->dob = $productDetails->dob;
		$product->fnn = $productDetails->fnn;
		$product->simPuk = $productDetails->sim_puk;
		$product->simStateId = $productDetails->sim_state_id;
		$product->serviceMobileOriginId = $productDetails->service_mobile_origin_id;
		$product->isValid(true);
		if (!$bolValidateOnly)
		{
			$product->save();
		}
	}
	
}

?>
