<?php

class DO_Sales_LandlineType extends DO_Sales_Base_LandlineType
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>