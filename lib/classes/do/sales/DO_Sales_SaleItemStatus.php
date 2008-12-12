<?php

class DO_Sales_SaleItemStatus extends DO_Sales_Base_SaleItemStatus
{
	const SUBMITTED					= 1;
	const VERIFIED					= 2;
	const REJECTED					= 3;
	const CANCELLED					= 4;
	const AWAITING_DISPATCH			= 5;
	const DISPATCHED				= 6;
	const MANUAL_INTERVENTION		= 7;
	const COMPLETED					= 8;

	public static function getAll($strSort=NULL)
	{
		if ($strSort === NULL)
		{
			$strSort = "name ASC";
		}
		
		$arrObjStatuses = self::getFor(NULL, TRUE, $strSort);
		
		$arrStatuses = array();
		foreach($arrObjStatuses as $doSaleItemStatus)
		{
			$arrStatuses[$doSaleItemStatus->id] = $doSaleItemStatus;
		}
		return $arrStatuses;
	}
}

?>