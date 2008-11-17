<?php

class Product_Type_Module_Service_Landline extends Product_Type_Module
{

	public function loadData()
	{
		$data = new stdClass();

		$data->landlineType = $this->_toKeyArray(DO_Sales_LandlineType::listAll());
		$data->landlineServiceStreetType = $this->_toKeyArray(DO_Sales_LandlineServiceStreetType::listAll());
		$data->landlineServiceAddressType = $this->_toKeyArray(DO_Sales_LandlineServiceAddressType::listAll(), array('id', 'description', 'landlineServiceAddressTypeCategoryId'));
		$data->landlineServiceStreetTypeSuffix = $this->_toKeyArray(DO_Sales_LandlineServiceStreetTypeSuffix::listAll());
		$data->landlineServiceState = $this->_toKeyArray(DO_Sales_LandlineServiceState::listAll());
		$data->landlineEndUserTitle = $this->_toKeyArray(DO_Sales_LandlineEndUserTitle::listAll());

		$data->landlineServiceAddressTypeCategory = array_combine($data->landlineServiceAddressType->id, $data->landlineServiceAddressType->landlineServiceAddressTypeCategoryId);
		unset($data->landlineServiceAddressType->landlineServiceAddressTypeCategoryId);

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
		$products = DO_Sales_SaleItemServiceLandline::listForSaleItem($saleItem);
		if (!count($products)) 
		{
			throw new Exception('No sale item details found for Landline Service.');
		}
		$product = $products[0];
		
		$productDetails = new stdClass();
		$productDetails->id = $product->id;
		$productDetails->fnn = $product->fnn;
		$productDetails->bill_address_line_1 = $product->billAddressLine1;
		$productDetails->bill_address_line_2 = $product->billAddressLine2;
		$productDetails->bill_locality = $product->billLocality;
		$productDetails->bill_name = $product->billName;
		$productDetails->bill_postcode = $product->billPostcode;
		$productDetails->has_extension_level_billing = $product->hasExtensionLevelBilling;
		echo '/*         ' . gettype($product->hasExtensionLevelBilling) . '        */';
		$productDetails->is_indial_100 = $product->isIndial100;
		$productDetails->landline_service_address_type_id = $product->landlineServiceAddressTypeId;
		$productDetails->landline_service_state_id = $product->landlineServiceStateId;
		$productDetails->landline_service_street_type_id = $product->landlineServiceStreetTypeId;
		$productDetails->landline_service_street_type_suffix_id = $product->landlineServiceStreetTypeSuffixId;
		$productDetails->landline_type_id = $product->landlineTypeId;
		$productDetails->service_address_type_number = $product->serviceAddressTypeNumber;
		$productDetails->service_address_type_suffix = $product->serviceAddressTypeSuffix;
		$productDetails->service_locality = $product->serviceLocality;
		$productDetails->service_postcode = $product->servicePostcode;
		$productDetails->service_property_name = $product->servicePropertyName;
		$productDetails->service_street_name = $product->serviceStreetName;
		$productDetails->service_street_number_start = $product->serviceStreetNumberStart;
		$productDetails->service_street_number_end = $product->serviceStreetNumberEnd;
		$productDetails->service_street_number_suffix = $product->serviceStreetNumberSuffix;
		
		$productDetails->landline_type_details = new stdClass();
		
		switch($product->landlineTypeId)
		{

			case 1: // WIP - Code this properly! 1 = Residential
				$arrLandlineType = DO_Sales_SaleItemServiceLandlineResidential::listForSaleItemServiceLandline($product);
				if (!count($arrLandlineType))
				{
					throw new Exception('Residential landline details not found for residential landline service.');
				}
				$objLandlineType = $arrLandlineType[0];
				$productDetails->landline_type_details->id = $objLandlineType->id;
				$productDetails->landline_type_details->landline_end_user_title_id = $objLandlineType->landlineEndUserTitleId;
				$productDetails->landline_type_details->end_user_occupation = $objLandlineType->endUserOccupation;
				$productDetails->landline_type_details->end_user_given_name = $objLandlineType->endUserGivenName;
				$productDetails->landline_type_details->end_user_family_name = $objLandlineType->endUserFamilyName;
				$productDetails->landline_type_details->end_user_employer = $objLandlineType->endUserEmployer;
				$productDetails->landline_type_details->end_user_dob = $objLandlineType->endUserDob;
				break;

			case 2: // WIP - Code this properly! 2 = Business
				$arrLandlineType = DO_Sales_SaleItemServiceLandlineBusiness::listForSaleItemServiceLandline($product);
				if (!count($arrLandlineType))
				{
					throw new Exception('Residential landline details not found for residential landline service.');
				}
				$objLandlineType = $arrLandlineType[0];
				$productDetails->landline_type_details->id = $objLandlineType->id;
				$productDetails->landline_type_details->company_name = $objLandlineType->companyName;
				$productDetails->landline_type_details->abn = $objLandlineType->abn;
				$productDetails->landline_type_details->trading_name = $objLandlineType->tradingName;
				break;

		}
		
		return $productDetails;
	}

	public function validateDetails(stdClass $productDetails)
	{
		
	}

	public function saveProductDetailsForSaleItem(stdClass $productDetails, DO_Sales_SaleItem $saleItem, $bolValidateOnly=false)
	{
		$products = DO_Sales_SaleItemServiceLandline::listForSaleItem($saleItem);
		$new = false;
		if (!count($products))
		{
			$product = new DO_Sales_SaleItemServiceLandline();
			$product->saleItemId = $bolValidateOnly ? 0 : $saleItem->id;
			$new = true;
		}
		else
		{
			$product = $products[0];
			$oldLandlineTypes = array();
			// Delete the old landline type details
			if ($product->landlineTypeId == 1) // WIP - Code this properly! 1 = Residential
			{
				$oldLandlineTypes = DO_Sales_SaleItemServiceLandlineResidential::listForSaleItemServiceLandline($product);
			}
			else if ($product->landlineTypeId == 2) // WIP - Code this properly! 2 = Business
			{
				$oldLandlineTypes = DO_Sales_SaleItemServiceLandlineBusiness::listForSaleItemServiceLandline($product);
			}
			foreach ($oldLandlineTypes as $oldLandlineType)
			{
				$oldLandlineType->delete();
			}
		}
		
		$product->fnn = $productDetails->fnn;
		$product->billAddressLine1 = $productDetails->bill_address_line_1;
		$product->billAddressLine2 = $productDetails->bill_address_line_2;
		$product->billLocality = $productDetails->bill_locality;
		$product->billName = $productDetails->bill_name;
		$product->billPostcode = $productDetails->bill_postcode;
		$product->hasExtensionLevelBilling = $productDetails->has_extension_level_billing;
		$product->isIndial100 = $productDetails->is_indial_100;
		$product->landlineServiceAddressTypeId = $productDetails->landline_service_address_type_id;
		$product->landlineServiceStateId = $productDetails->landline_service_state_id;
		$product->landlineServiceStreetTypeId = $productDetails->landline_service_street_type_id;
		$product->landlineServiceStreetTypeSuffixId = $productDetails->landline_service_street_type_suffix_id;
		$product->landlineTypeId = $productDetails->landline_type_id;
		$product->serviceAddressTypeNumber = $productDetails->service_address_type_number;
		$product->serviceAddressTypeSuffix = $productDetails->service_address_type_suffix;
		$product->serviceLocality = $productDetails->service_locality;
		$product->servicePostcode = $productDetails->service_postcode;
		$product->servicePropertyName = $productDetails->service_property_name;
		$product->serviceStreetName = $productDetails->service_street_name;
		$product->serviceStreetNumberStart = $productDetails->service_street_number_start;
		$product->serviceStreetNumberEnd = $productDetails->service_street_number_end;
		$product->serviceStreetNumberSuffix = $productDetails->service_street_number_suffix;
		$product->isValid(true);
		if (!$bolValidateOnly)
		{
			$product->save();
		}
		
		$landlineTypeDetails = $productDetails->landline_type_details;
		
		switch($product->landlineTypeId)
		{

			case 1: // WIP - Code this properly! 1 = Residential
				$objLandlineType = new DO_Sales_SaleItemServiceLandlineResidential();
				$objLandlineType->saleItemServiceLandlineId = $bolValidateOnly ? 0 : $product->id;
				$objLandlineType->landlineEndUserTitleId = $landlineTypeDetails->landline_end_user_title_id;
				$objLandlineType->endUserOccupation = $landlineTypeDetails->end_user_occupation;
				$objLandlineType->endUserGivenName = $landlineTypeDetails->end_user_given_name;
				$objLandlineType->endUserFamilyName = $landlineTypeDetails->end_user_family_name;
				$objLandlineType->endUserEmployer = $landlineTypeDetails->end_user_employer;
				$objLandlineType->endUserDob = $landlineTypeDetails->end_user_dob;
				break;

			case 2: // WIP - Code this properly! 2 = Business
				$objLandlineType = new DO_Sales_SaleItemServiceLandlineBusiness();
				$objLandlineType->saleItemServiceLandlineId = $bolValidateOnly ? 0 : $product->id;
				$objLandlineType->companyName = $landlineTypeDetails->company_name;
				$objLandlineType->abn = $landlineTypeDetails->abn;
				$objLandlineType->tradingName = $landlineTypeDetails->trading_name;
				break;

		}
		
		$objLandlineType->isValid(true);
		if (!$bolValidateOnly)
		{
			$objLandlineType->save();
		}
	}

}

?>
