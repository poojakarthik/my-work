<?php

class DO_Sales_Dealer extends DO_Sales_Base_Dealer
{
	const SYSTEM_DEALER_ID = 1;
	
	// Retrieves all dealers who manager other dealers
	public static function listManagers()
	{
		$strDealerTable		= self::getDataSourceObjectName();
		$strDealerIdField	= self::getDataSourceIdName();
		$arrProps			= self::getPropertyDataSourceMappings();
		
		return self::getFor("$strDealerIdField IN (SELECT DISTINCT {$arrProps['upLineId']} FROM $strDealerTable WHERE {$arrProps['upLineId']} IS NOT NULL)", TRUE, "{$arrProps['username']} ASC");
	}
	
	public function listAll($bolIncludeSystemDealer=FALSE)
	{
		$strDealerTable		= self::getDataSourceObjectName();
		$strDealerIdField	= self::getDataSourceIdName();
		$arrProps			= self::getPropertyDataSourceMappings();
		
		$strWhere = ($bolIncludeSystemDealer)? NULL : "$strDealerTable.$strDealerIdField != ". self::SYSTEM_DEALER_ID;
		
		return self::getFor($strWhere, TRUE, "{$arrProps['username']} ASC");
	}

	public static function getForUsername($username)
	{
		$arrWhere = array(	'username'			=> $username,
							'dealerStatusId'	=> DO_Sales_DealerStatus::ACTIVE
						);
		return self::getFor($arrWhere);
	}
	
	public function passwordEquals($password)
	{
			return sha1(trim($password)) == $this->password;
	}
	
	public function getPermissions()
	{
		return $this->hasSubordinates() 
					? (Sales_Portal::PERMISSION_UPLINE_DEALER | Sales_Portal::PERMISSION_DEALER) 
					: Sales_Portal::PERMISSION_DEALER;
	}

	public function hasSubordinates()
	{
		static $bolHasSubordinates;
		if (!isset($bolHasSubordinates))
		{
			$bolHasSubordinates = self::countFor(array('upLineId' => $this->id)) > 0;
		}
		return $bolHasSubordinates;
	}
	
	// Returns array containing all Dealers under the management hierarchy of $objDealer
	// the array is not associative
	public function getSubordinates()
	{
		$arrProps			= self::getPropertyDataSourceMappings();
		$arrManagedDealers	= self::getFor("{$arrProps['upLineId']} = {$this->id}", TRUE);
		
		// Add the dealers immediately under $doDealer's management to the array of all dealers under $doDealer's management
		$arrDealers = $arrManagedDealers;
		
		// For each dealer that is immediately under $doDealer's management, find the dealers under their management (the recursion part)
		foreach ($arrManagedDealers as $doManagedDealer)
		{
			$arrDealers = array_merge($arrDealers, $doManagedDealer->getSubordinates());
		}
		return $arrDealers;
	}
	
	
	public function hasSubordinate($doSubDealer)
	{
		$arrSubs = $this->getSubordinates();
		foreach ($arrSubs as $doSub)
		{
			if ($doSub->id = $doSubDealer->id)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
	// Returns array of all the DO_Sales_SaleType objects, with the id of the records as the key, that the dealer is associated with
	public function getSaleTypes()
	{
		$arrDoDealerSaleTypes = DO_Sales_DealerSaleType::listForDealer($this);
		
		$arrAllSaleTypes = DO_Sales_SaleType::getAll();
		$arrSaleTypesForDealer = array();
		foreach ($arrDoDealerSaleTypes as $doDealerSaleType)
		{
			$arrSaleTypesForDealer[$doDealerSaleType->saleTypeId] = $arrAllSaleTypes[$doDealerSaleType->saleTypeId];
		}
		return $arrSaleTypesForDealer;
	}
	
	// Returns array of all the DO_Sales_Vendor objects, with the id of the records as the key, that the dealer is associated with
	public function getVendors()
	{
		$arrDoDealerVendors = DO_Sales_DealerVendor::listForDealer($this);
		
		$arrAllVendors = DO_Sales_Vendor::getAll();
		$arrVendorsForDealer = array();
		foreach ($arrDoDealerVendors as $doDealerVendor)
		{
			$arrVendorsForDealer[$doDealerVendor->vendorId] = $arrAllVendors[$doDealerVendor->vendorId];
		}
		return $arrVendorsForDealer;
	}
	
	// Returns TRUE if the dealer can View the sale.  The requirements being that the sale is associated with them, or any of their subordinates
	public function canViewSale($doSale)
	{
		if ($this->id == $doSale->createdBy)
		{
			// The dealer created the sale
			return TRUE;
		}
		
		// Check if any of the dealer's subordinates created the sale
		$arrSubDealers = $this->getSubordinates();
		foreach ($arrSubDealers as $doSubDealer)
		{
			if ($doDealer->id == $doSale->createdBy)
			{
				return TRUE;
			}
		}
		return FALSE;
	}
	
}

?>