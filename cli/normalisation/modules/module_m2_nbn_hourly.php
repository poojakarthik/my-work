<?php
class NormalisationModuleM2NBNHourly extends NormalisationModule {
	const RESOURCE_TYPE	= RESOURCE_TYPE_FILE_IMPORT_CDR_M2_NBN_HOURLY;
	const DEBUG_LOGGING = true;

	function __construct($iCarrier) {
		$this->_arrModuleConfig = self::getConfigDefinition();
		parent::__construct($iCarrier);

		// Verify linked Service Type
		if (
			!$this->GetConfigField('service_type_id') ||
			(false === ($this->_oServiceType = Service_Type::getForId($this->GetConfigField('service_type_id'), false)))
		) {
			throw new Exception_Assertion('M2 Hourly NBN Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Service Type');
		}

		// Verify linked Record Types
		if (
			!$this->GetConfigField('period_allowance_consumed_type_id') ||
			(false === ($this->_oRecordType = Record_Type::getForId($this->GetConfigField('period_allowance_consumed_type_id'), false)))
		) {
			throw new Exception_Assertion('M2 Hourly NBN Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Record Type');
		}

		// define row start (there is no header row)
		$this->_intStartRow = 0;

		$this->_iSequence = 0;
	}

	public static function getConfigDefinition() {
		// Values defined in here are DEFAULT values
		return	array
				(
					'service_type_id' => array
						(
							'Type' => DATA_TYPE_INTEGER,
							'Description' => 'Service Type that represents this M2 NBN account'
						),
					'period_allowance_consumed_type_id' => array
						(
							'Type' => DATA_TYPE_INTEGER,
							'Description' => 'Record Type that represents allowed consumed record type for a perioed in M2 NBN Account'
						)
				);
	}

	function Preprocessor($sCDR) {
		$aCDRLines = [];
		// Split the CDR
		$aCDRItems = File_CSV::parseLine($sCDR);

		if (count($aCDRItems) != 9) {
			throw new Exception_Assertion("M2 NBN Usage Normalisation Module Preprocessor Expected 9 Columns; encountered " . count($aCDRItems) . ":" . $sCDR);
		}
		// Calculate StartDatetime from Anniversary day
		$iAnniversaryDay = intval($aCDRItems[7]);
		$sFileDate = date("Y-m-d");
		$iFileDay = intval(substr($sFileDate, 8, 2));
		$iStartMonth = (($iFileDay - $iAnniversaryDay) >= 0) ? substr($sFileDate, 5, 2) : sprintf("%02d", intval(substr($sFileDate, 5, 2) - 1));
		$sStartDatetime = substr($sFileDate, 0, 4) . "-" . $iStartMonth . "-" . sprintf("%02d", $iAnniversaryDay) . " 00:00:00";

		if (!checkdate($iStartMonth, $iAnniversaryDay, intval(substr($sFileDate, 0, 4)))) {
			$iStartMonth++;
			$sStartDatetime = substr($sFileDate, 0, 4) . "-" . sprintf("%02d", $iStartMonth) . "-01 00:00:00";
		}

		$iUsageKiloBytes = intval(ceil(floatval($aCDRItems[4]) * 1024));

		$aParams = [
			"FNN" => substr(trim($aCDRItems[0]), 2, 10) . "n",
			"StartDatetime" => $sStartDatetime,
			"RecordType" => $this->GetConfigField('period_allowance_consumed_type_id'),
			"ConsumedData" => $iUsageKiloBytes
		];
		//If ConsumedData is less than previous CDR, it is old CDR
		$oCDRResult = Query::run("
				SELECT Id, CDR, Units
				FROM CDR
				WHERE FNN = <FNN> AND CDR.StartDatetime = <StartDatetime> AND RecordType = <RecordType> AND Units <= <ConsumedData>", $aParams);
		if ($oCDRResult === false) {
			throw new Exception($qryQuery->Error());
		}

		if ($oCDRResult->num_rows > 0) {
			$aCDR = $oCDRResult->fetch_assoc();
			//Log::get()->log(print_r($aCDRDowload, true));
			$aCDR["CDR"] = $sCDR;
			return [$aCDR];
		} else {
			return $sCDR;
		}
	}

	public function Normalise($aCDR) {
		// set up CDR
		$this->_NewCDR($aCDR);

		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);

		//--------------------------------------------------------------------//
		Log::get()->logIf(self::DEBUG_LOGGING, "Record #{$this->_iSequence}");
		$aParsed = File_CSV::parseLine($aCDR['CDR']);
		if (count($aParsed) < 9) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		$this->_arrRawData = array_associate(array(
			0 => 'Username',
			1 => 'ServiceReference',
			2 => 'Allowance',
			3 => 'Status',
			4 => 'Consumed_data',
			5 => 'Remaining_data',
			6 => 'Consumed_Percentage',
			7 => 'Anniversary_date',
			8 => 'Date_Shaped'
		), $aParsed);

		//Check if more recent CDR is already there
		$sFileDate = trim(preg_replace("/[a-zA-Z_.]/", '', $this->getNormalised('FileName')));
		$iAnniversaryDay = intval($this->getRaw('Anniversary_date'));
		$iFileDate = intval(substr($sFileDate, 8, 2));
		$iStartMonth = (($iFileDate - $iAnniversaryDay) >= 0) ? substr($sFileDate, 5, 2) : sprintf("%02d", intval(substr($sFileDate, 5, 2) - 1));
		$sStartDatetime = substr($sFileDate, 0, 4) . "-" . $iStartMonth . "-" . sprintf("%02d", $iAnniversaryDay) . " 00:00:00";

		if (!checkdate($iStartMonth, $iAnniversaryDay, intval(substr($sFileDate, 0, 4)))) {
			$iStartMonth++;
			$sStartDatetime = substr($sFileDate, 0, 4) . "-" . $iStartMonth . "-01 00:00:00";
		}

		$sEndDatetime = substr($sFileDate, 0, 10) . " " . substr($sFileDate, 10,2). ":00:00";

		$aParams = [
			"FNN" => substr(trim($this->getRaw('ServiceReference')), 2, 10) . "n",
			"StartDatetime" =>  date('Y-m-d H:i:s', strtotime($sStartDatetime)), //to avoid the invalid datetime values such as 2015-02-29 which is not valid
			"RecordType" => $this->GetConfigField('period_allowance_consumed_type_id'),
			"EndDatetime" => $sEndDatetime
		];

		$oCDRResult = Query::run("
				SELECT Id, CDR, Units
				FROM CDR
				WHERE FNN = <FNN> AND CDR.StartDatetime = <StartDatetime> AND CDR.RecordType = <RecordType> AND CDR.EndDatetime > <EndDatetime>", $aParams);
		if ($oCDRResult === false) {
			throw new Exception($qryQuery->Error());
		}

		if($oCDRResult->num_rows > 0) {
			//More recent CDR already downloaded so ignore this one
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_BAD_SEQ_NO);
		}
		Log::get()->log(print_r($this->_arrRawData, true));
		$this->_normalise();
		//--------------------------------------------------------------------//

		//SET STATUS TO CDR_NOT_BILLABLE
		$this->_UpdateStatus(CDR_NOT_BILLABLE);

		//Debug($this->_arrNormalisedData);

		// Apply Ownership
		$this->ApplyOwnership();

		// Validation of Normalised data
		$this->Validate();

		// throw new Exception("TESTING");

		// return output array
		return $this->_OutputCDR();
	}

	// Usage Records
	private function _normalise() {
		// CarrierRef
		$this->setNormalised('CarrierRef', $this->getNormalised('FileName') . ":" . trim($this->getRaw('Username')) . ":" . $this->getRaw('Direction'));

		// FNN
		$sServiceReference = substr(trim($this->getRaw('ServiceReference')), 2, 10) . "n";
		$this->setNormalised('FNN', $sServiceReference);
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('FNN', 'ServiceReference'));

		// Cost
		$this->setNormalised('Cost', 0.0);
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('Cost'));

		// ServiceType
		$this->setNormalised('ServiceType', $this->_oServiceType->id);
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('ServiceType', 'Username'));

		// Units
		$iUsageKiloBytes = intval(ceil(floatval($this->getRaw('Consumed_data')) * 1024));
		$this->setNormalised('Units', $iUsageKiloBytes);
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('Units', 'Consumed_data'));

		// StartDatetime
		//Get Filename and replace text and compute start datetime
		$sFileDate = trim(preg_replace("/[a-zA-Z_.]/", '', $this->getNormalised('FileName')));

		// StartDatetime
		$iAnniversaryDay = intval($this->getRaw('Anniversary_date'));
		$iFileDate = intval(substr($sFileDate, 8, 2));
		$iStartMonth = (($iFileDate - $iAnniversaryDay) >= 0) ? substr($sFileDate, 5, 2) : sprintf("%02d", intval(substr($sFileDate, 5, 2) - 1));
		$sStartDatetime = substr($sFileDate, 0, 4) . "-" . $iStartMonth . "-" . sprintf("%02d", $iAnniversaryDay) . " 00:00:00";

		if (!checkdate($iStartMonth, $iAnniversaryDay, intval(substr($sFileDate, 0, 4)))) {
			$iStartMonth++;
			$sStartDatetime = substr($sFileDate, 0, 4) . "-" . $iStartMonth . "-01 00:00:00";
		}
		$this->setNormalised('StartDatetime', $sStartDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . "StartDatetime: " . var_export($this->getNormalised('StartDatetime'), true) . " (FileName: '" . $this->getNormalised('FileName') . "', Anniversary Date: '" . $this->getRaw('Anniversary_date') . "') ");

		// EndDatetime
		$this->setNormalised('EndDatetime', substr($sFileDate, 0, 10) . " " . substr($sFileDate, 10,2). ":00:00");
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . "EndDatetime: " . var_export($this->getNormalised('EndDatetime'), true) . " (FileName: '" . $this->getNormalised('FileName') . "')");

		//RecordType
		$this->setNormalised('RecordType', $this->GetConfigField('period_allowance_consumed_type_id'));
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('RecordType', 'Consumed_data'));

		// throw new Exception("TESTING");
	}

	public function Validate() {
		$iUnits = $this->getNormalised('Units');

		$result = parent::Validate();

		//Validate in base module set 0 Units to 1 which is not desirable for NBN Usage so reset them to 0 here
		if ($iUnits === 0) {
			$this->setNormalised('Units' , $iUnits);
		}

		return $result;
	}

	/* SUGGESTED RECORD TYPES

		INSERT INTO RecordType
			(Code, Name, Description, ServiceType, Context, Required, Itemised, GroupId, DisplayType, global_tax_exempt)
		VALUES
			('PeriodAllowanceConsumed', 'Period Allowance Consumed', 'Period Allowance Consumed', NBN_SERVICE_TYPE, 0, 1, 0, 0, 3, 0)
		;
		UPDATE RecordType
		SET GroupId = Id
		WHERE ServiceType = NBN_SERVICE_TYPE;
	*/
}