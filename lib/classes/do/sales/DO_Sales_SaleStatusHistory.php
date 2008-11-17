<?php

class DO_Sales_SaleStatusHistory extends DO_Sales_Base_SaleStatusHistory
{

	public static function recordHistoryForSale(DO_Sales_Sale $sale, $intDealerId, $comment="")
	{
		$strSQL = 'SELECT ' . self::getPropertyDataSourceName('saleStatusId') . 
				  '  FROM ' . self::getDataSourceObjectName() . 
				  ' WHERE ' . self::getPropertyDataSourceName('saleId') . " = " . $sale->id .
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
			$lastStatusId = $records[0][self::getPropertyDataSourceName('saleStatusId')];
			if ($lastStatusId == $sale->saleItemStatusId)
			{
				// No change to record history for
				return;
			}
		}
		
		if (!$comment)
		{
			$comment = $new ? "New (original) sale" : "Status changed";
		}
		
		$history = new DO_Sales_SaleStatusHistory();
		$history->saleId = $sale->id;
		$history->saleStatusId = $sale->saleStatusId;
		$history->changedOn = $new ? $sale->createdOn : date('Y-m-d H:i:s');
		$history->changedBy = $intDealerId;
		$history->description = strval($comment);
		$history->save();
		
		
	}
}

?>