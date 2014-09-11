<?php

class DO_Sales_DealerVendor extends DO_Sales_Base_DealerVendor
{
	public static function listForDealerId($dealerId)
	{
		return self::getFor(array('dealerId' => $dealerId), true);
	}

}

?>