<?php

class DO_Sales_LandlineServiceAddressType extends DO_Sales_Base_LandlineServiceAddressType
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>