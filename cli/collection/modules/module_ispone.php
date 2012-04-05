<?php
class CollectionModuleISPOne extends CollectionModuleBase {
	const RESOURCE_TYPE = RESOURCE_TYPE_FILE_RESOURCE_ISPONE;

	public $intBaseFileType = RESOURCE_TYPE_FILE_RESOURCE_ISPONE;

	const DEBUG_LOGGING = false;

	private $_aMissingDates;

	public static function getConfigDefinition() {
		// Values defined in here are DEFAULT values
		return array(
			'URL' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Secure URL (without parameters)',
				'Value' => 'https://www.ispone.com.au/toolsV2/reporting/index.php'
			),
			'Username' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Username'
			),
			'Password' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Password'
			),
			'Type' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Report Type'
			),
			'ResourceType' => array(
				'Type' => DATA_TYPE_INTEGER,
				'Description' => 'Record from Flex\'s `resource_type` table to map files downloaded to'
			),
			'EarliestDate' => array(
				'Type' => DATA_TYPE_STRING,
				'Description' => 'Earliest Date Flex will look for files (YYYY-MM-DD)'
			)
		);
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

		if (list($iKey, $sMissingDate) = each($this->_aMissingDates)) {
			// Download file
			$sFileName = $this->_makeFileName($sMissingDate);
			$sLocalPath = "{$sLocalDirectory}/{$sFileName}";

			$sRemoteURL = $this->_getFullURL($sMissingDate);
			Log::get()->logIf(self::DEBUG_LOGGING, "Downloading file for {$sMissingDate} @ {$sRemoteURL}");
			$oCURL = CURL::get($sRemoteURL, array(
				CURLOPT_RETURNTRANSFER => true
			));
			$mResult = $oCURL->getResult();

			//Log::get()->logIf(self::DEBUG_LOGGING, 'Transfer Info: '.var_export($oCURL->getTransferInfo(), true));
			//Log::get()->logIf(self::DEBUG_LOGGING, 'Result: '.var_export($oCURL->getResult(), true));
			//throw new Exception("Debugging");

			// Verify response data
			if ($oCURL->HTTP_CODE != 200) {
				throw new Exception("cURLing {$sRemoteURL} gave a non-200 HTTP response code: ".print_r($oCURL->getTransferInfo(), true));
			}
			if (!self::_isValidResponseContentType($oCURL->CONTENT_TYPE)) {
				throw new Exception("cURLing {$sRemoteURL} gave an unhandle Content Type: ".print_r($oCURL->getTransferInfo(), true));
			}
			if (!$mResult || !is_string($mResult)) {
				throw new Exception("cURLing {$sRemoteURL} gave no result: ".print_r($oCURL->getTransferInfo(), true));
			}

			// Write to local file
			if (false === @file_put_contents($sLocalPath, $mResult, 0777)) {
				throw new Exception("Unable to write {$sRemoteURL} data to local path {$sLocalPath}".(isset($php_errormsg) ? ": {$php_errormsg}" : ''));
			}

			// Return file details
			return array(
				'LocalPath' => realpath($sLocalPath),
				'RemotePath' => $sRemoteURL,
				'FileType' => array(
					'FileImportType' => $this->_oConfig->ResourceType,
					'Uniqueness' => 'FileName = <FileName>'
				)
			);
		} else {
			// No more files
			return false;
		}
	}

	private function _getMissingDates() {
		if (!isset($this->_aMissingDates)) {
			// Get a list of files we already have
			Log::get()->logIf(self::DEBUG_LOGGING, "Retrieving list of already downloaded files");
			$mResult = Query::run("
				SELECT	*,
						CAST(FileName AS DATE) AS filename_date
				FROM	FileDownload fd
				WHERE	fd.Carrier = <carrier_id>
						AND FileName LIKE CONCAT('________.', <type>, '.csv')
			", array(
				'carrier_id' => $this->getCarrierModule()->Carrier,
				'type' => trim($this->_oConfig->Type)
			));
			$aPastFiles = array();
			while ($aFileDownload = $mResult->fetch_assoc()) {
				$aPastFiles[$aFileDownload['filename_date']] = $aFileDownload;
			}
			Log::get()->logIf(self::DEBUG_LOGGING, "Found ".count($aPastFiles)." files downloaded in the past");

			// Calculate what dates we're missing
			$sToday = date('Y-m-d');
			$sCurrentDate = date('Y-m-d', strtotime($this->_oConfig->EarliestDate));
			Log::get()->logIf(self::DEBUG_LOGGING, "Checking for missing dates (between {$sCurrentDate} and {$sToday})");
			$this->_aMissingDates = array();
			while ($sCurrentDate <= $sToday) {
				if (!isset($aPastFiles[$sCurrentDate])) {
					Log::get()->logIf(self::DEBUG_LOGGING, "Missing {$sCurrentDate}");
					$this->_aMissingDates[] = $sCurrentDate;
				}
				$sCurrentDate = date('Y-m-d', strtotime('+1 day', strtotime($sCurrentDate)));
			}
			Log::get()->logIf(self::DEBUG_LOGGING, "Found ".count($this->_aMissingDates)." missing dates");
			reset($this->_aMissingDates);
		}
		return $this->_aMissingDates;
	}

	private function _getFullURL($sDate) {
		// NOTE: Tried using `http_build_query`, but it uses non-standard quoting < PHP 5.4
		return "{$this->_oConfig->URL}?".
			'username='.rawurlencode($this->_oConfig->Username).'&'.
			'password='.rawurlencode($this->_oConfig->Password).'&'.
			'type='.rawurlencode(trim($this->_oConfig->Type)).'&'.
			'date='.rawurlencode(date('dmY', strtotime($sDate)));
	}

	private function _makeFileName($sDate) {
		return date('Ymd', strtotime($sDate)).'.'.trim($this->_oConfig->Type).'.csv';
	}

	private static function _isValidResponseContentType($sContentType) {
		return in_array($sContentType, array(
			'application/vnd.ms-excel'
		));
	}
}