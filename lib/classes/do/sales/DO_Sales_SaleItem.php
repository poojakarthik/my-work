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
	
	public function verify()
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::VERIFIED;
		$this->save();
	}
	
	public function cancel()
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::CANCELLED;
		$this->save();
	}
	
	public function reject()
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::REJECTED;
		$this->save();
	}
}

?>