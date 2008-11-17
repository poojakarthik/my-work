<?php

class Sales_Portal_Sale extends DO_Sales_Sale
{
	public function canBeCancelled()
	{
		// Sale can only be moved to cancelled if it is :-
		// submitted
		// rejected
		// manual intervention
		// provisioned
		// verified
		// i.e. pretty much any state except 'ready for provisioning'
		return array_search($this->saleStatusId, 
							array(	DO_Sales_SaleStatus::SUBMITTED, 
									DO_Sales_SaleStatus::REJECTED, 
									DO_Sales_SaleStatus::MANUAL_INTERVENTION, 
									DO_Sales_SaleStatus::PROVISIONED, 
									DO_Sales_SaleStatus::VERIFIED)) !== false;
		
	}
	
	public function canBeRejected()
	{
		// Sale can only be moved to rejected if it is :-
		// submitted
		return array_search($this->saleStatusId, 
							array(	DO_Sales_SaleStatus::SUBMITTED)) !== false;
		
	}
	
	public function canBeVerified()
	{
		// Sale can only be moved to verified if it is :-
		// submitted
		return array_search($this->saleStatusId, 
							array(	DO_Sales_SaleStatus::SUBMITTED)) !== false;
		
	}
	
}



?>
