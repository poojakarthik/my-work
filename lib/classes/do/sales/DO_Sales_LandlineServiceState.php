<?php

class DO_Sales_LandlineServiceState extends DO_Sales_Base_LandlineServiceState
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>