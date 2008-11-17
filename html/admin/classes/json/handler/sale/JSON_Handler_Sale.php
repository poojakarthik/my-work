<?php

class JSON_Handler_Sale extends JSON_Handler
{
	public function load($saleId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$sale = DO_Sales_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}
		return Sales_Portal::getStdClassForDOSale($sale);
	}
	
	public function submit($saleDetails)
	{
		return $this->confirm($saleDetails, true);
	}
	
	public function confirm($saleDetails, $bolValidateOnly=false)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		// WIP - NEED TO SAVE THE SALE DETAILS!!!
		
		
		
	}

	public function cancelSale($saleId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$sale = Sales_Portal_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}

		// Sale can only be moved to cancelled if it is :-
		// submitted
		// rejected
		// manual intervention
		// provisioned
		// verified
		// i.e. pretty much any state except 'ready for provisioning'
		if ($sale->canBeCancelled())
		{
			$sale->cancel();
		}
		else
		{
			throw new Exception("The sale cannot be cancelled at this time.");
		}
	}

	public function rejectSale($saleId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$sale = Sales_Portal_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}

		// Sale can only be moved to rejected if it is :-
		// submitted
		if ($sale->canBeRejected())
		{
			$sale->reject();
		}
		else
		{
			throw new Exception("The sale cannot be rejected at this time.");
		}
	}

	public function verifySale($saleId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$sale = Sales_Portal_Sale::getForId(intval($saleId));
		if (!$sale)
		{
			throw new Exception("The specified sale '$saleId' could not be found.");
		}

		// Sale can only be moved to verified if it is :-
		// submitted
		if ($sale->canBeVerified())
		{
			$sale->verify();
		}
		else
		{
			throw new Exception("The sale cannot be verified at this time.");
		}
	}


	// This is invoked via ajax
	public function listProductTypesForVendor($intVendorId)
	{
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

		$intVendorId = intval($intVendorId);
		
		$list = DO_Sales_ProductType::getProductTypesForVendor($intVendorId);
		
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
		$dealer = Dealer::getForEmployeeId(Flex::getUserId());
		if (!$dealer || !$dealer->isActive())
		{
			throw new Exception("You do not have sufficient permissions to perform that action.");
		}

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
