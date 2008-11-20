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
	
	/**
 	 * public listActiveForSale()
	 *
	 * Retreives all non-cancelled instances of DO_Sales_SaleItem, the 
	 * source ('to-many' end) of the foreign key fk_sale_item_sale_id
	 * between tables sale_item and sale.
	 *
	 * @param $do DO_Sales_Base_Sale instance to retreive matching records for 
	 * @return array(DO_Sales_SaleItem) instances (empty array if none match)
 	 */
	public static function listActiveForSale(DO_Sales_Base_Sale $do, $strSort=NULL, $strLimit=0, $strOffset=0)
	{
		$arrStati = array(
			DO_Sales_SaleItemStatus::SUBMITTED,
			DO_Sales_SaleItemStatus::VERIFIED,
			DO_Sales_SaleItemStatus::REJECTED,
			DO_Sales_SaleItemStatus::AWAITING_DISPATCH,
			DO_Sales_SaleItemStatus::DISPATCHED,
			DO_Sales_SaleItemStatus::MANUAL_INTERVENTION,
			DO_Sales_SaleItemStatus::PROVISIONED,
		);
		return DO_Sales_SaleItem::getFor(array("saleId" => $do->id, "saleItemStatusId" => $arrStati), true, $strSort, $strLimit, $strOffset);
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