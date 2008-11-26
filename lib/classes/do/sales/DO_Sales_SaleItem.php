<?php

class DO_Sales_SaleItem extends DO_Sales_Base_SaleItem
{
	public function save($dealerId, $comment=NULL)
	{
		$dealer = DO_Sales_Dealer::getForId($dealerId);
		if ($dealer == null)
		{
			throw new Exception('Invalid dealer ' . $dealerId . '. Unable to save ' . $this->getObjectLabel() . '.');
		}
		
		$new = $this->id == null;

		$return = parent::save();
		
		DO_Sales_SaleItemStatusHistory::recordHistoryForSaleItem($this, $dealerId, $comment);
		
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
			DO_Sales_SaleItemStatus::COMPLETED,
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
	
	public function setCompleted($dealerId)
	{
		$this->saleItemStatusId = DO_Sales_SaleItemStatus::COMPLETED;
		$this->save($dealerId, 'Completed');
	}
	
	
	// Returns the time at which the sale item was verified, or the DO_Sales_SaleItemStatusHistory object relating to this status milestone
	// Returns NULL if the sale item has not been verified
	public function getVerificationTimestamp($bolAsObject=FALSE)
	{
		$doHistory = DO_Sales_SaleItemStatusHistory::getFirstOccuranceOfStatus($this, DO_Sales_SaleItemStatus::VERIFIED);
		
		if ($doHistory === NULL)
		{
			return NULL;
		}
		
		return ($bolAsObject) ? $doHistory : $doHistory->changedOn;
	}
	
	// Returns true if the sale_item is within its cooling off period and can therefor be cancelled, if it isn't already
	public function isWithinCoolingOffPeriod()
	{
		// Get the sale and vendor objects
		$doSale		= DO_Sales_Sale::getForId($doSaleItem->saleId);
		$doVendor	= DO_Sales_Vendor::getForId($doSale->vendorId);
		
		$strVerificationTimestamp = $this->getVerificationTimestamp();
		
		if ($strVerificationTimestamp === NULL)
		{
			throw new Exception("Checking for cooling off period when sale item (id: {$this->id}) hasn't even been verified yet");
		}
		
		$strCurrentTimestamp = Data_Source_Time::currentTimestamp(self::getDataSource());
		
		if ($doVendor->coolingOffPeriod !== NULL)
		{
			$strCoolingOffEndTime = date("Y-m-d H:i:s", strtotime("+{$doVendor->coolingOffPeriod} hours {$strVerificationTimestamp}"));
			
			return ($strCoolingOffEndTime >= $strCurrentTimestamp);
		}
		else
		{
			// There is no cooling off period specified for this vendor, therefore the sale item cannot be within the cooling off period
			return FALSE;
		}
	}
	
}

?>