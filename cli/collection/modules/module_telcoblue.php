<?php

class CollectionModuleTelcoBlue extends CollectionModuleBase {
	const DEBUG_LOGGING = true;
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_RESOURCE_TELCOBLUE_PROVISIONING;
	const LINE_TYPE_RESPONSE = 'r';
	const LINE_TYPE_NOTIFICATION = 'n';
	const LOG_TABS = "\t\t\t\t";

	public $intBaseFileType = RESOURCE_TYPE_FILE_RESOURCE_TELCOBLUE_PROVISIONING;

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

		if (list($iKey, $aPeriod) = each($this->_aMissingPeriods)) {
			// Get data by using the wholesale reseller API
			$aResponses = $this->_APIRequest('responses', $aPeriod);
			$aNotifications = $this->_APIRequest('notifications', $aPeriod);

			// Generate the csv file content
			$aLines = array();
			foreach ($aResponses as $oResponse) {
				$aLines[] = implode(',', array(
					self::LINE_TYPE_RESPONSE,
					$oResponse->client_reference,
					$oResponse->subject,
					$oResponse->action,
					$oResponse->detail,
					$oResponse->status,
					$oResponse->timestamp
				));				
			}

			foreach ($aNotifications as $oNotification) {
				$aLines[] = implode(',', array(
					self::LINE_TYPE_NOTIFICATION,
					$oNotification->identifier,
					$oNotification->identifier_context,
					$oNotification->notification_type,
					$oNotification->status,
					$oNotification->status_result,
					$oNotification->timestamp
				));
			}

			// Write to local file
			$sFileName = $this->_makeFileName($aPeriod);
			$sLocalPath = "{$sLocalDirectory}/{$sFileName}";
			if (false === @file_put_contents($sLocalPath, implode("\n", $aLines), 0777)) {
				throw new Exception("Unable to write data to local path {$sLocalPath}".(isset($php_errormsg) ? ": {$php_errormsg}" : ''));
			}

			// Return file details
			return array(
				'LocalPath' => realpath($sLocalPath),
				'RemotePath' => '',
				'FileType' => array(
					'FileImportType' => RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE,
					'Uniqueness' => 'FileName = <FileName>'
				)
			);
		} else {
			// No more files
			return false;
		}
	}
	
	private function _getMissingDates() {
		if (!isset($this->_aMissingPeriods)) {
			// Get a list of files we already have
			Log::get()->logIf(self::DEBUG_LOGGING, "\n\n".self::LOG_TABS."Retrieving list of already downloaded files");
			$mResult = Query::run("
				SELECT	*
				FROM	FileDownload fd
				WHERE	fd.Carrier = <carrier_id>
						AND FileName LIKE '______________-______________.provisioning.csv'
				ORDER BY FileName
			", array(
				'carrier_id' => $this->getCarrierModule()->Carrier
			));

			$this->_aMissingPeriods = array();
			$sLatestDate = date('YmdHis', strtotime($this->_oConfig->EarliestDate));
			while ($aFileDownload = $mResult->fetch_assoc()) {
				preg_match('/(\d{14})-(\d{14})\./', $aFileDownload['FileName'], $aMatch);
				$sFromDate = $aMatch[1];
				$sToDate = $aMatch[2];
				//Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Checking file: {$aFileDownload['FileName']}. Latest=$sLatestDate, From=$sFromDate, To=$sToDate");

				// Check if there is a gap between the end date of the previous file to the start date of this file
				if ($sLatestDate < $sFromDate) {
					Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."There is a gap period: $sLatestDate < $sFromDate");					
					$this->_aMissingPeriods[] = array(
						'sFrom' => $sLatestDate,
						'sTo' => $sFromDate
					);
				}

				$sLatestDate = $sToDate;
			}

			// Add the final period (if necessary) from the latest files end date until the beginning of this hour
			$sGetChangesUntil = date("Ymd{$this->_oConfig->GetChangesUntil}");
			$iGetChangesUntil = strtotime($sGetChangesUntil);
			if ($iGetChangesUntil > time()) {
				// Not yet able to get changes today, move back a day (in seconds)
				$iGetChangesUntil -= (60 * 60 * 24);
			}

			$sNow = date('YmdHis', $iGetChangesUntil);
			Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Checking if the latest file is before now. Now: $sNow, Latest: $sLatestDate");
			if ($sLatestDate < $sNow) {
				$this->_aMissingPeriods[] = array(
					'sFrom' => $sLatestDate,
					'sTo' => $sNow
				);
			}

			Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."Missing date periods");
			foreach ($this->_aMissingPeriods as $aPeriod) {
				Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS."\tFrom: {$aPeriod['sFrom']}; To: {$aPeriod['sTo']}");
			}
			
			reset($this->_aMissingPeriods);
			Log::get()->logIf(self::DEBUG_LOGGING, self::LOG_TABS, false);
		}

		return $this->_aMissingPeriods;
	}

	private function _getFullURL($sType, $aPeriod) {
		return "{$this->_oConfig->APIURL}/{$sType}?from={$aPeriod['sFrom']}&to={$aPeriod['sTo']}";
	}

	private function _makeFileName($aPeriod) {
		return date('YmdHis', strtotime($aPeriod['sFrom'])).'-'.date('YmdHis', strtotime($aPeriod['sTo'])).".provisioning.csv";
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
			throw new Exception("Invalid JSON response from API. URL='{$sURL}' Response='{$sBody}'");
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
					throw new Exception("Error response from API: {$iResponseStatus}. URL='{$sURL}' Response='{$sBody}'");
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

?>