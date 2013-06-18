<?php

class Logic_Collection_Event_Report extends Logic_Collection_Event {
	protected $oDO;
	
	public function __construct($mDefinition) {
		if ($mDefinition instanceof Logic_Collection_Event_Instance) {
		   $this->oCollectionEventInstance = $mDefinition;
		   $this->oParentDO = Collection_Event::getForId($mDefinition->collection_event_id);
		   $this->oDO = Collection_Event_Report::getForCollectionEventId($this->oParentDO->id);
		} else if (is_numeric($mDefinition)) {
			$this->oParentDO = Collection_Event::getForId($mDefinition);
			$this->oDO = Collection_Event_Report::getForCollectionEventId($this->oParentDO->id);
		} else {
		   throw new Exception('bad parameter passed into Collection_Logic_Event_Report constructor');
		}
	}

	protected function _invoke($aParameters=null) {
		// ...
	}

	public function __get($sField) {
		switch ($sField) {
			case 'name':
			case 'collection_event_type_id':
				return $this->oParentDO->$sField;
			default:
				return $this->oDO->$sField;
		}
	}

	public static function complete($aEventInstances) {
		// Get the sql for the report
		$oEventInstance	= $aEventInstances[0];
		if ($oEventInstance === NULL) {
			// Nothing to complete
			return;
		}

		$sEventName = $oEventInstance->getEventName();
		$oEventObject = self::getForEventInstance($oEventInstance);
		switch ($oEventObject->collection_event_report_output_id) {
			case COLLECTION_EVENT_REPORT_OUTPUT_CSV:
				$sFileExtension = 'csv';
				$sMimeType = 'text/plain';
				$sFileType = 'CSV';
				break;
			case COLLECTION_EVENT_REPORT_OUTPUT_EXCEL:
				$sFileExtension = 'xls';
				$sMimeType = 'application/excel';
				$sFileType = 'Excel5';
				break;
			case COLLECTION_EVENT_REPORT_OUTPUT_EXCEL_2007:
				$sFileExtension = 'xlsx';
				$sMimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
				$sFileType = 'Excel2007';
				break;
		}

		$aAccountIds = array();
		foreach ($aEventInstances as $oEventInstance) {
			$aAccountIds[] = $oEventInstance->account_id;
		}

		$sSql = $oEventObject->report_sql;
		$sSql = str_replace("<ACCOUNTS>", implode(",", $aAccountIds), $sSql);
		$oQuery = new Query();
		$mResult = $oQuery->Execute($sSql);
		$aResult = array();
		if ($mResult) {
			while ($aRecord = $mResult->fetch_assoc()) {
			   $aResult[] = $aRecord;
			}
		}

		$oEmail = new Correspondence_Email($oEventObject->email_notification_id);
		$oEmail->setSubject("$sEventName (Collection Event id $oEventInstance->collection_event_id)");
		$body = $oEmail->getBody();
		$oEmail->addTextHeader(3, $sEventName." (Collection Event id $oEventInstance->collection_event_id)");
		
		$oSpreadsheet = new Logic_Spreadsheet(array_keys($aResult[0]), $aResult, $sFileType);
		$sPath = FILES_BASE_PATH.'temp/';
		$sTimeStamp = str_replace(array(' ',':','-'), '',Data_Source_Time::currentTimestamp());
		$sFilename = "$sEventName"."_$sTimeStamp.$sFileExtension";
		$oSpreadsheet->saveAs($sPath.$sFilename, $sFileType);

		// Send the email
		$sFile = file_get_contents($sPath.$sFilename);
		$oEmail->addAttachment($sFile, $sFilename, $sMimeType);
		$oEmail->addTextHeader(4, "Please find the report attached. Summary: ");
		$table =& $oEmail->setTable();
		$oEmail->addPivotTableRow("Number of Rows", $oSpreadsheet->getRowCount());
		
		$oEmployee = Employee::getForId(Flex::getUserId());
		if (($oEmployee != null) && ($oEmployee->email != null)) {
			$oEmail->addTo($oEmployee->Email, $oEmployee->FirstName.' '.$oEmployee->LastName);
		}

		$oEmail->appendSignature();
		$oEmail->setBodyHTML();
		$oEmail->send();
		
		foreach ($aEventInstances as $oInstance) {
			$oInstance->complete();
		}		
	}
}

?>
