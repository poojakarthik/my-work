<?php

class DO_Sales_SaleItemStatus extends DO_Sales_Base_SaleItemStatus
{
	const SUBMITTED					= 1;
	const VERIFIED					= 2;
	const REJECTED					= 3;
	const CANCELLED					= 4;
	const READY_FOR_PROVISIONING	= 5;
	const PROVISIONED				= 6;
	const MANUAL_INTERVENTION		= 7;
}

?>