<?php

class DO_Sales_LandlineEndUserTitle extends DO_Sales_Base_LandlineEndUserTitle
{

	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>