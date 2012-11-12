<?php

class CollectionModuleTelcoBlue extends CollectionModuleBase {
	const DEBUG_LOGGING = true;
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_RESOURCE_TELCOBLUE;
	const PROVISIONING_LINE_TYPE_RESPONSE = 'r';
	const PROVISIONING_LINE_TYPE_NOTIFICATION = 'n';
	const LOG_TABS = "\t\t\t\t";
	const IMPORT_FILE_PROVISIONING = 'provisioning';
	const IMPORT_FILE_USAGE = 'usage';

	public $intBaseFileType = RESOURCE_TYPE_FILE_RESOURCE_TELCOBLUE;

	protected $_aMissingPeriods;
	
	function __construct($iCarrierModuleId) {
		parent::__construct($iCarrierModuleId);
	}

	public function Connect() {
 		// Verify we have a valid Earliest Date
		if (@strtotime($this->_oConfig->EarliestDate) === false) {
			throw new Exception("Config field `EarliestDate` *MUST* be set, and be a valid date (Value: '{$this->_oConfig->EarliestDate}')");
		}

		// No persitent connection, but we want to calculate a list of files to download
		$this->_getMissingDates();

		return true;
 	}
 	
 	public function Disconnect() {
		// No-op
	}

	public function Download($sLocalDirectory) {
		$this->_getMissingDates();

		while (list($iKey, $aPeriod) = each($this->_aMissingPeriods)) {
			try {
				Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Date Period: {$aPeriod['sFrom']} - {$aPeriod['sTo']}");
				switch ($aPeriod['iImportFileType']) {
					case RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE:
						// Get data by using the wholesale reseller API
						$aResponses = $this->_APIRequest('responses', $aPeriod);
						$aNotifications = $this->_APIRequest('notifications', $aPeriod);

						// Make sure there is something to collect
						if (empty($aResponses) && empty($aNotifications)) {
							// There is no responses or notifications. 
							if ($this->_canDeferPeriod($iKey, $aPeriod)) {
								throw new CollectionModuleTelcoBlue_Exception_NoDataForLastPeriod();
							}
						}

						// Generate the csv file content lines
						$aLines = array();
						foreach ($aResponses as $oResponse) {
							$sNotifications = preg_replace('/\\\|"/', '\\\$0', JSON_Services::encode($oResponse->notifications));
							$sNotifications = preg_replace('/,/', ';', $sNotifications);
							$aLines[] = implode(',', array(
								self::PROVISIONING_LINE_TYPE_RESPONSE,
								$oResponse->client_reference,
								$oResponse->subject,
								$oResponse->action,
								$oResponse->detail,
								$oResponse->status,
								$oResponse->modified_timestamp,
								$oResponse->effective_timestamp,
								'"'.$sNotifications.'"',
								$oResponse->package_id,
								$oResponse->identifier_context
							));
						}

						foreach ($aNotifications as $oNotification) {
							$aLines[] = implode(',', array(
								self::PROVISIONING_LINE_TYPE_NOTIFICATION,
								$oNotification->identifier,
								$oNotification->identifier_context,
								$oNotification->notification_type,
								$oNotification->package_id,
								$oNotification->status,
								$oNotification->status_result,
								$oNotification->notification_timestamp,
								$oNotification->effective_timestamp,
								$oNotification->description
							));
						}

						$sFileName = $this->_makeFileName($aPeriod, 'provisioning');
						break;
					case RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE:
						// Get data by using the wholesale reseller API
						$aUsageData = $this->_APIRequest('usage', $aPeriod);

						// Make sure there is something to collect
						if (empty($aUsageData)) {
							// There is not usage for this period
							if ($this->_canDeferPeriod($iKey, $aPeriod)) {
								throw new CollectionModuleTelcoBlue_Exception_NoDataForLastPeriod();
							}
						}

						// Generate the csv file content lines
						$aLines = array();
						foreach ($aUsageData as $oUsageData) {
							$aLines[] = implode(',', array(
								$oUsageData->reference,
								$oUsageData->type,
								$oUsageData->origin_identifier,
								$oUsageData->target_identifier,
								$oUsageData->billed_identifier,
								$oUsageData->origin_point,
								$oUsageData->origin_point_name,
								$oUsageData->target_point,
								$oUsageData->target_point_name,
								$oUsageData->billed_point,
								$oUsageData->billed_point_name,
								$oUsageData->units_primary,
								$oUsageData->unit_type_primary,
								$oUsageData->units_secondary,
								$oUsageData->unit_type_secondary,
								$oUsageData->units_tertiary,
								$oUsageData->unit_type_tertiary,
								$oUsageData->cost,
								$oUsageData->event_start,
								$oUsageData->event_end,
								$oUsageData->rate,
								$oUsageData->description
							));
						}

						$sFileName = $this->_makeFileName($aPeriod, 'usage');
						break;
				}
			} catch (CollectionModuleTelcoBlue_Exception_NoDataForLastPeriod $oEx) {
				// There was not data for this period Collect the next file
				Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."\tThis period returned no data and is the last of it's kind in this run. Deferring collection to next run.");
				continue;
			}

			// Write the lines to local file
			$sLocalPath = "{$sLocalDirectory}/{$sFileName}";
			if (false === @file_put_contents($sLocalPath, implode("\n", $aLines), 0777)) {
				throw new Exception("Unable to write data to local path {$sLocalPath}".(isset($php_errormsg) ? ": {$php_errormsg}" : ''));
			}

			// Return file details
			return array(
				'LocalPath' => realpath($sLocalPath),
				'RemotePath' => '',
				'FileType' => array(
					'FileImportType' => $aPeriod['iImportFileType'],
					'Uniqueness' => 'FileName = <FileName>'
				)
			);
		}
		
		// No more files
		return false;
	}

	private function _canDeferPeriod($iKey, $aPeriod) {
		// If this is the last provisioning period to collect it can be ignored and will be joined on the first period next time this module is run.
		$aKeys = array_keys($this->_aMissingPeriods);
		$iMaxKeyForImportFileType = $iKey;
		foreach ($aKeys as $i) {
			if (($this->_aMissingPeriods[$i]['iImportFileType'] == $aPeriod['iImportFileType']) && ($i > $iMaxKeyForImportFileType)) {
				// This period is for the same import file type and it occurs after the current file
				$iMaxKeyForImportFileType = $i;
			}
		}

		return ($iMaxKeyForImportFileType == $iKey);
	}
	
	private function _getMissingDates() {
		if (!isset($this->_aMissingPeriods)) {
			// Get a list of files we already have
			Log::get()->logIf(self::DEBUG_LOGGING, "\n\n".self::LOG_TABS."Retrieving list of already downloaded files");
			$mResult = Query::run("
				SELECT	*,
						CASE 
							WHEN FileName LIKE '______________-______________.provisioning.csv'
							THEN <import_file_type_provisioning>
							ELSE <import_file_type_usage>
						END AS import_file_type
				FROM	FileDownload fd
				WHERE	fd.Carrier = <carrier_id>
						AND (
							(
								FileName LIKE '______________-______________.provisioning.csv'
							) OR (
								FileName LIKE '______________-______________.usage.csv'
							)
						)
				ORDER BY FileName
			", array(
				'carrier_id' => $this->getCarrierModule()->Carrier,
				'import_file_type_provisioning' => RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE,
				'import_file_type_usage' => RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE
			));

			$this->_aMissingPeriods = array();
			$sEarliestDate = date('YmdHis', strtotime($this->_oConfig->EarliestDate));
			$aLatestDateByFileImportType = array(
				RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE => $sEarliestDate,
				RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE => $sEarliestDate
			);
			while ($aFileDownload = $mResult->fetch_assoc()) {
				preg_match('/(\d{14})-(\d{14})\./', $aFileDownload['FileName'], $aMatch);
				$sFromDate = $aMatch[1];
				$sToDate = $aMatch[2];
				$iImportFileType = $aFileDownload['import_file_type'];
				//Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Checking file: {$aFileDownload['FileName']}. Latest=$sLatestDate, From=$sFromDate, To=$sToDate");

				// Check if there is a gap between the end date of the previous file to the start date of this file
				$sLatestDate = $aLatestDateByFileImportType[$iImportFileType];
				if ($sLatestDate < $sFromDate) {
					Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."There is a gap period: $sLatestDate < $sFromDate");					
					$this->_aMissingPeriods[] = array(
						'iImportFileType' => $iImportFileType,
						'sFrom' => $sLatestDate,
						'sTo' => $sFromDate
					);
				}

				$aLatestDateByFileImportType[$iImportFileType] = $sToDate;
			}

			// Add the final period (if necessary) from the latest files end date until the beginning of this hour
			$sGetChangesUntil = date("Ymd{$this->_oConfig->GetChangesUntil}");
			$iGetChangesUntil = strtotime($sGetChangesUntil);
			if ($iGetChangesUntil > time()) {
				// Not yet able to get changes today, move back a day (in seconds)
				$iGetChangesUntil -= (60 * 60 * 24);
			}

			$sNow = date('YmdHis', $iGetChangesUntil);
			$sLatestProvisioningFile = $aLatestDateByFileImportType[RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE];
			Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Checking if the latest provisioning file is before now. Now: $sNow, Latest: $sLatestProvisioningFile");
			if ($sLatestProvisioningFile < $sNow) {
				$this->_aMissingPeriods[] = array(
					'iImportFileType' => RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE,
					'sFrom' => $sLatestProvisioningFile,
					'sTo' => $sNow
				);
			}

			$sLatestUsageFile = $aLatestDateByFileImportType[RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE];
			Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Checking if the latest provisioning file is before now. Now: $sNow, Latest: $sLatestUsageFile");
			if ($sLatestUsageFile < $sNow) {
				// Usage
				$this->_aMissingPeriods[] = array(
					'iImportFileType' => RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE,
					'sFrom' => $sLatestUsageFile,
					'sTo' => $sNow
				);
			}

			Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Missing date periods");
			foreach ($this->_aMissingPeriods as $aPeriod) {
				Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."\tFrom: {$aPeriod['sFrom']}; To: {$aPeriod['sTo']}; Import File Type: {$aPeriod['iImportFileType']};");
			}
			
			reset($this->_aMissingPeriods);
			Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS, false);
		}

		return $this->_aMissingPeriods;
	}

	private function _getFullURL($sType, $aPeriod) {
		return "{$this->_oConfig->APIURL}/{$sType}?from={$aPeriod['sFrom']}&to={$aPeriod['sTo']}";
	}

	private function _makeFileName($aPeriod, $sImportFileType) {
		return date('YmdHis', strtotime($aPeriod['sFrom'])).'-'.date('YmdHis', strtotime($aPeriod['sTo'])).".{$sImportFileType}.csv";
	}

	private function _APIRequest($sType, $aPeriod) {
		$sRemoteURL	= $this->_getFullURL($sType, $aPeriod);
		$iWholesaleAccountId = $this->_oConfig->WholesaleAccountId;
		$sAPIPassword = sha1($this->_oConfig->APIPassword);

		Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Downloading file for {$aPeriod['sFrom']} - {$aPeriod['sTo']} @ {$sRemoteURL}");

		$oCURL = CURL::get($sRemoteURL, array(
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_TIMEOUT => 40,
			CURLOPT_PORT => $this->_oConfig->APIPort,
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json;",
				"API_AUTHENTICATION: ".sha1("{$iWholesaleAccountId};{$sAPIPassword}")
			)
		));

		$mResult = $oCURL->getResult();
		if (!$mResult || !is_string($mResult)) {
			throw new Exception("cURLing {$sRemoteURL} gave no result: ".print_r($oCURL->getTransferInfo(), true));
		}

		// Process the response, look for errors
		$aResponseParts = explode("\r\n\r\n", $mResult);
		if (empty($aResponseParts)) {
			throw new Exception("Unexpected response from API: {$mResult}");
		}

		$iBodyPartIndex = count($aResponseParts) - 1;
		if (isset($aResponseParts[$iBodyPartIndex - 1])) {
			// A header was returned in the response
			$aHeader = explode("\n", $aResponseParts[$iBodyPartIndex - 1]);
			$aStatus = explode(" ", $aHeader[0]);
			$iResponseStatus = (int)$aStatus[1];
		} else {
			// No header, weird response
			throw new Exception("Unexpected response from API, no HTTP header: {$mResult}");
		}
		
		$sBody = $aResponseParts[$iBodyPartIndex];
		$mJSONResponse = JSON_Services::decode($sBody);
		$bInvalidJSONResponse = ($mJSONResponse === null && trim($sBody) != 'null');
		
		if ($bInvalidJSONResponse) {
			// JSON decode error
			throw new Exception("Invalid JSON response from API. URL='{$sRemoteURL}' Response='{$sBody}'");
		}

		switch ($iResponseStatus) {
			case 400: // BAD_REQUEST
			case 404: // NOT_FOUND
			case 405: // METHOD_NOT_ALLOWED
			case 500: // SERVER_ERROR
			case 501: // NOT_IMPLEMENTED
				if (!$bInvalidJSONResponse && property_exists($mJSONResponse, 'oException') && property_exists($mJSONResponse->oException, 'sMessage')) {
					// Error passed back from API
					throw new Exception("API Error: ".$mJSONResponse->oException->sMessage);
				} else {
					// Other Http error
					throw new Exception("Error response from API: {$iResponseStatus}. URL='{$sRemoteURL}' Response='{$sBody}'");
				}
				break;
		}

		return $mJSONResponse;
	}

	public static function getConfigDefinition() {
		// Values defined in here are DEFAULT values
		return array(
			'APIURL' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'URL of the API to connect to'
			),
			'APIPort' => array(
				'Type' => DATA_TYPE_INTEGER,
				'Description' => 'Port to connect to on API Server'
			),
			'APIPassword' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Password with which to authenticate with the API'
			),
			'WholesaleAccountId' => array(
				'Type' => DATA_TYPE_INTEGER,
				'Description' => 'Wholesale Account Id of this system'
			),
			'EarliestDate' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Earliest Date Flex will look for files (YYYY-MM-DD)'
			),
			'GetChangesUntil' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'When changes can be fetched until today (e.g. H0000 = Up to the current hour, 130000 = Up to 1pm today, or yesterday if not yet 1pm)'
			)
		);
	}
}

class CollectionModuleTelcoBlue_Exception_NoDataForLastPeriod extends Exception {}

?>