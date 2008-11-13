<?php

class DO_Sales_LandlineServiceStreetType extends DO_Sales_Base_LandlineServiceStreetType
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>