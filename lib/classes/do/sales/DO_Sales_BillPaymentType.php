<?php

class DO_Sales_BillPaymentType extends DO_Sales_Base_BillPaymentType
{
	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>