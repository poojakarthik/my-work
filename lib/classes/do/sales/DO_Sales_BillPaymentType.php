<?php

class DO_Sales_BillPaymentType extends DO_Sales_Base_BillPaymentType
{
	// These constants should directly relate to the records of this table
	const ACCOUNT		= 1;
	const DIRECT_DEBIT	= 2;
	
	static function listAll()
	{
		$ds = self::getPropertyDataSourceMappings();
		return self::getFor(null, true, $ds['description']);
	}
}

?>