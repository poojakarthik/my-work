<?php

class JSON_Handler_Email_Queue extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	
	public function getDataset($bCountOnly, $iLimit, $iOffset, $oSort, $oFilter) {
		$iRecordCount = Email_Queue::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
		if ($bCountOnly) {
			return array('iRecordCount' => $iRecordCount);
		}
		
		$iLimit		= ($iLimit === null ? 0 : $iLimit);
		$iOffset	= ($iOffset === null ? 0 : $iOffset);
		$aData	 	= Email_Queue::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
		$aResults	= array();
		$i			= $iOffset;
		
		foreach ($aData as $aRecord) {
			$aResults[$i] = $aRecord;
			$i++;
		}
		
		return array(
			'aRecords'		=> $aResults,
			'iRecordCount'	=> $iRecordCount
		);
	}
	
	public function cancelQueueDelivery($iQueueId) {
		Email_Queue::getForId($iQueueId)->cancel();
	}
}

?>