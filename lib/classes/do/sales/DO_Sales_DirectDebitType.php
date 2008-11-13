<?php

class DO_Sales_DirectDebitType extends DO_Sales_Base_DirectDebitType
{
	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>