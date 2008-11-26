<?php

class DO_Sales_SaleItemStatusHistory extends DO_Sales_Base_SaleItemStatusHistory
{

	public static function recordHistoryForSaleItem(DO_Sales_SaleItem $saleItem, $intDealerId, $comment=NULL)
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
		$history->changedOn = $new ? $saleItem->createdOn : Data_Source_Time::currentTimestamp(self::getDataSource());
		$history->changedBy = $intDealerId;
		$history->saleItemStatusId = $saleItem->saleItemStatusId;
		$history->description = strval($comment);
		$history->save();
	}
	
	// Returns a DO_Sales_SaleItemStatusHistory object representing the first time $doSaleItem was set to $intSaleItemStatusId
	// Returns NULL, if it has never been set to the status
	public static function getFirstOccuranceOfStatus($doSaleItem, $intSaleItemStatusId)
	{
		$arrPropsMap = self::getPropertyDataSourceMappings();
		// This should really be sorted by changedOn then id, but id should suffice and is much faster
		$strOrderBy = "{$arrPropsMap['id']} ASC";
		
		$arrWhere = array(	"saleItemId"		=> $doSaleItem->id,
							"saleItemStatusId"	=> $intSaleItemStatusId
						);
		
		return self::getFor($arrWhere, false, $strOrderBy, 1);
	}
}

?>