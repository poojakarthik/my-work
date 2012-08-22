<?php

class JSON_Handler_Ticketing_Config extends JSON_Handler implements JSON_Handler_Loggable, JSON_Handler_Catchable {
	public function getAll() {
		$aRecords = Ticketing_Config::getAll();
		$aStdRecords = array();
		foreach ($aRecords as $oRecord) {
			$aStdRecords[$oRecord->id] = $oRecord->toStdClass();
		}
		return array(
			'oRecords' => $aStdRecords
		);
	}

	public function updateRecord($iId, $oData) {
		$oRecord = Ticketing_Config::getForId($iId);
		if (!$oRecord) {
			throw new Exception("Invalid ticketing_config id supplied: {$iId}");
		}

		self::_updateRecord($oRecord, $oData);
		return array(
			'oRecord' => $oRecord->toArray()
		);
	}

	public function createRecord($oData) {
		$oRecord = new Ticketing_Config();
		self::_updateRecord($oRecord, $oData);
		return array(
			'oRecord' => $oRecord->toArray()
		);
	}

	private static function _updateRecord(&$oRecord, $oData) {
		self::_validateData($oData);
		$oRecord->protocol = $oData->protocol;
		$oRecord->host = $oData->host;
		$oRecord->port = (int)$oData->port;
		$oRecord->username = $oData->username;
		$oRecord->password = $oData->password;
		$oRecord->use_ssl = ($oData->use_ssl == '1' ? 1 : 0);
		$oRecord->archive_folder_name = $oData->archive_folder_name;
		$oRecord->is_active = ($oData->is_active == '1' ? 1 : 0);
		$oRecord->save();
	}

	private static function _validateData($oData) {
		$oExceptionSet = new Exception_Set();
		if (!property_exists($oData, 'protocol') || ($oData->protocol == '')) {
			$oExceptionSet->push("A Protocol must be supplied");
		}

		if (!property_exists($oData, 'host') || ($oData->host == '')) {
			$oExceptionSet->push("A Host must be supplied");
		}

		if (!$oExceptionSet->isEmpty()) {
			throw new $oExceptionSet;
		}
	}
}

?>