<?php

class DO_Sales_SaleItemStatusHistory extends DO_Sales_Base_SaleItemStatusHistory
{

	public static function recordHistoryForSaleItem(DO_Sales_SaleItem $saleItem, $intDealerId, $comment="")
	{
		$strSQL = 'SELECT ' . self::getPropertyDataSourceName('saleItemStatusId') . 
				  '  FROM ' . self::getDataSourceObjectName() . 
				  ' WHERE ' . self::getPropertyDataSourceName('saleItemId') . ' = ' . $saleItem->id . 
				  ' ORDER BY ' . self::getPropertyDataSourceName('id') . ' DESC' .
				  ' LIMIT 1 OFFSET 0';
		
		$new = true;
		
		$dataSource = self::getDataSource();
		if (PEAR::isError($results = $dataSource->query($strSQL)))
		{
			throw new Exception('Failed to check for ' . __CLASS__ . ' :: ' . $results->getMessage());
		}
		
		$records = $results->fetchAll(MDB2_FETCHMODE_ASSOC);
		
		if (count($records))
		{
			$new = false;
			$lastStatusId = $records[0][self::getPropertyDataSourceName('saleItemStatusId')];
			if ($lastStatusId == $saleItem->saleItemStatusId)
			{
				// No change to record history for
				return;
			}
		}

		if (!$comment)
		{
			$comment = $new ? "New (original) sale" : "Status changed";
		}

		$history = new DO_Sales_SaleItemStatusHistory();
		$history->saleItemId = $saleItem->id;
		$history->changedOn = $new ? $saleItem->createdOn : date('Y-m-d H:i:s');
		$history->changedBy = $intDealerId;
		$history->saleItemStatusId = $saleItem->saleItemStatusId;
		$history->description = strval($comment);
		$history->save();
	}
}

?>