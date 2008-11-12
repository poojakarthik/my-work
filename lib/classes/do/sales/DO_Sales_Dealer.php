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

	// Returns array containing all Dealers under the management hierarchy of $objDealer
	// This will NOT include $objDealer in the array
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
	
	
}

?>