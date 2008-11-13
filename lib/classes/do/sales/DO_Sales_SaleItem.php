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
		
		$history = new DO_Sales_SaleItemStatusHistory();
		$history->saleItemId = $this->id;
		$history->changedOn = $new ? $this->createdOn : date('Y-m-d H:i:s');
		$history->changedBy = $dealerId;
		$history->saleItemStatusId = $this->saleItemStatusId;
		$history->description = strval($comment);
		$history->save();
		
		return $return;
	}
}

?>