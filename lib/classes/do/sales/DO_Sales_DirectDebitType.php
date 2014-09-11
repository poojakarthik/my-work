<?php

class DO_Sales_DirectDebitType extends DO_Sales_Base_DirectDebitType
{
	const	BANK_ACCOUNT	= 1;
	const	CREDIT_CARD		= 2;
	
	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>