<?php

class DO_Sales_SaleItem extends DO_Sales_Base_SaleItem
{
	public function save($dealerId, $comment)
	{
		$dealer = DO_Sales_Dealer::getForId($dealerId);
		if ($dealer == null)
		{
			throw new Exception('Invalid dealer ' . $dealerId . '. Unable to save ' . $this->getObjectLabel() . '.');
		}
		
		$new = $this->id == null;

		$return = parent::save();
		
		DO_Sales_SaleItemStatusHistory::recordHistoryForSaleItem($this, $dealerId);
		
		return $return;
	}
	
	public function verify($dealerId)
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::VERIFIED;
		$this->save($dealerId, 'Item verified');
	}
	
	public function cancel($dealerId)
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::CANCELLED;
		$this->save($dealerId, 'Item cancelled');
	}
	
	public function reject($dealerId)
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::REJECTED;
		$this->save($dealerId, 'Item rejected');
	}
	
	public function setAwaitingDispatch($dealerId)
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::AWAITING_DISPATCH;
		$this->save($dealerId, 'Awaiting dispatch');
	}
}

?>