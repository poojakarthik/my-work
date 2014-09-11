<?php

class DO_Sales_LandlineServiceStreetType extends DO_Sales_Base_LandlineServiceStreetType
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}

	static function listCommonTypes()
	{
		// I'm using the codes instead of descriptions, so they don't get included multiple times because the description "Lane" relates to 3 unique codes
		$ds			= self::getPropertyDataSourceMappings();
		$strCode	= $ds['code'];
		$strWhere	= "$strCode IN ('ST', 'RD', 'AVE', 'LANE', 'CT', 'NR')";
		$strOrderBy	= "($strCode = 'ST') DESC, ($strCode = 'RD') DESC, ($strCode = 'AVE') DESC, ($strCode = 'LANE') DESC, ($strCode = 'CT') DESC";
		
		return self::getFor($strWhere, true, $strOrderBy);
	}
}

?>