<?php

class DO_Sales_ServiceMobileOrigin extends DO_Sales_Base_ServiceMobileOrigin
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>