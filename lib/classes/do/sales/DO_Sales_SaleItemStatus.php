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
	const PROVISIONED				= 8;

}

?>