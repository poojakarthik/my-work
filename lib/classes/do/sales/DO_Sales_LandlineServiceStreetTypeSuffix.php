<?php

class DO_Sales_LandlineServiceStreetTypeSuffix extends DO_Sales_Base_LandlineServiceStreetTypeSuffix
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>