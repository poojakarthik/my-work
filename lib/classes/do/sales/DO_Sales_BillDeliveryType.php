<?php

class DO_Sales_BillDeliveryType extends DO_Sales_Base_BillDeliveryType
{
	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>