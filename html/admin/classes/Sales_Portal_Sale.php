<?php

class Sales_Portal_Sale
{
	public static function canBeCancelled(DO_Sales_Sale $sale)
	{
		// Sale can only be moved to cancelled if it is :-
		// submitted
		// rejected
		// manual intervention
		// provisioned
		// verified
		// i.e. pretty much any state except 'ready for provisioning'
		return array_search(intval($sale->saleStatusId), 
							array(	DO_Sales_SaleStatus::SUBMITTED, 
									DO_Sales_SaleStatus::REJECTED, 
									DO_Sales_SaleStatus::MANUAL_INTERVENTION, 
									DO_Sales_SaleStatus::PROVISIONED, 
									DO_Sales_SaleStatus::VERIFIED), true) !== false;
		
	}
	
	public static function canBeAmended(DO_Sales_Sale $sale)
	{
		// Sale can only be amended if it is :-
		// submitted 
		// manual intervention
		return array_search(intval($sale->saleStatusId), 
							array(	DO_Sales_SaleStatus::SUBMITTED, 
									DO_Sales_SaleStatus::MANUAL_INTERVENTION), true) !== false;
		
	}
	
	public static function canBeRejected(DO_Sales_Sale $sale)
	{
		// Sale can only be moved to rejected if it is :-
		// submitted
		return array_search(intval($sale->saleStatusId), 
							array(	DO_Sales_SaleStatus::SUBMITTED), true) !== false;
		
	}
	
	public static function canBeVerified(DO_Sales_Sale $sale)
	{
		// Sale can only be moved to verified if it is :-
		// submitted
		return array_search(intval($sale->saleStatusId), 
							array(	DO_Sales_SaleStatus::SUBMITTED), true) !== false;
		
	}
	
}



?>
