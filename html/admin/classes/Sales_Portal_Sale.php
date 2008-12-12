<?php

class Sales_Portal_Sale
{
	public static function canBeCancelled(DO_Sales_Sale $sale)
	{
		// Sale can only be moved to cancelled if it is :-
		// submitted
		// rejected
		// manual intervention
		// Dispatched
		// Awaiting Dispatch
		// verified
		// Completed
		// i.e. pretty much any state except 'cancelled'
		
		$arrAllowableStati = array(	DO_Sales_SaleStatus::SUBMITTED, 
									DO_Sales_SaleStatus::REJECTED, 
									DO_Sales_SaleStatus::MANUAL_INTERVENTION, 
									DO_Sales_SaleStatus::COMPLETED, 
									DO_Sales_SaleStatus::AWAITING_DISPATCH, 
									DO_Sales_SaleStatus::DISPATCHED, 
									DO_Sales_SaleStatus::VERIFIED);
		
		// Plus it has to be within the cooling off period, if it has been verified
		$strVerifiedTimestamp = $sale->getVerificationTimestamp();
		
		if ($strVerifiedTimestamp !== NULL)
		{
			// The sale has been verified.  Check that it is within the cooling off period
			$strEndOfCoolingOffTimestamp = $sale->getEndOfCoolingOffPeriodTimestamp();
			if ($strEndOfCoolingOffTimestamp === NULL || $strEndOfCoolingOffTimestamp < Data_Source_Time::currentTimestamp($sale->getDataSource()))
			{
				return FALSE;
			}
		}
		
		return array_search(intval($sale->saleStatusId), $arrAllowableStati, TRUE) !== FALSE;
		
	}

	public static function canBeAmended(DO_Sales_Sale $sale)
	{
		// Sale can only be amended if it is :-
		// submitted 
		// rejected 
		// manual intervention
		return array_search(intval($sale->saleStatusId), 
							array(	DO_Sales_SaleStatus::SUBMITTED, 
									DO_Sales_SaleStatus::REJECTED, 
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
							array(	DO_Sales_SaleStatus::SUBMITTED, 
									DO_Sales_SaleStatus::MANUAL_INTERVENTION), true) !== false;
		
	}

	public static function canBeViewed(DO_Sales_Sale $sale)
	{
		// Sale can only be moved to verified if it is :-
		// submitted
		return true;
		
	}
	
}



?>
