<?php
class NormalisationModuleM2NBN extends NormalisationModule {
	const RESOURCE_TYPE	= RESOURCE_TYPE_FILE_IMPORT_CDR_M2_NBN;
	const DEBUG_LOGGING = true;
	const DIRECTION_UPLOAD = 'Upload';
    const DIRECTION_DOWNLOAD = 'Download';

	function __construct($iCarrier) {
		$this->_arrModuleConfig = self::getConfigDefinition();
		parent::__construct($iCarrier);

		// Verify linked Service Type
		if (
			!$this->GetConfigField('service_type_id') ||
			(false === ($this->_oServiceType = Service_Type::getForId($this->GetConfigField('service_type_id'), false)))
		) {
			throw new Exception_Assertion('M2 NBN Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Service Type');
		}

		// Verify linked Record Types
		if (
			!$this->GetConfigField('upload_record_type_id') ||
			(false === ($this->_oRecordType = Record_Type::getForId($this->GetConfigField('upload_record_type_id'), false)))
		) {
			throw new Exception_Assertion('M2 NBN Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Record Type');
		}
		if (
			!$this->GetConfigField('download_record_type_id') ||
			(false === ($this->_oRecordType = Record_Type::getForId($this->GetConfigField('download_record_type_id'), false)))
		) {
			throw new Exception_Assertion('M2 NBN Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Record Type');
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
					'upload_record_type_id' => array
						(
							'Type' => DATA_TYPE_INTEGER,
							'Description' => 'Record Type that represents Upload in M2 NBN Account'
						),
					'download_record_type_id' => array
						(
							'Type' => DATA_TYPE_INTEGER,
							'Description' => 'Record Type that represents Download in M2 NBN Account'
						)
				);
	}

	function Preprocessor($sCDR) {
		$aCDRLines = [];
		// Split the CDR
		$aCDRItems = File_CSV::parseLineRFC4180($sCDR);

		if (count($aCDRItems) != 4) {
			throw new Exception_Assertion("M2 NBN Usage Normalisation Module Preprocessor Expected 4 Columns; encountered " . count($aCDRItems) . ":" . $sCDR);
		}

		return [
		File_CSV::buildLineRFC4180([$aCDRItems[0], $aCDRItems[1], self::DIRECTION_UPLOAD, $aCDRItems[2]]),
		File_CSV::buildLineRFC4180([$aCDRItems[0], $aCDRItems[1], self::DIRECTION_DOWNLOAD, $aCDRItems[3]])
		];
	}

	public function Normalise($aCDR) {
		// set up CDR
		$this->_NewCDR($aCDR);

		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);

		//--------------------------------------------------------------------//
		Log::get()->logIf(self::DEBUG_LOGGING, "Record #{$this->_iSequence}");
		$aParsed = File_CSV::parseLineRFC4180($aCDR['CDR']);
		if (count($aParsed) < 4) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		$this->_arrRawData = array_associate(array(
			0 => 'Username',
			1 => 'ServiceReference',
			2 => 'Direction',
			3 => 'MB_Quantity'
		), $aParsed);
		Log::get()->log(print_r($this->_arrRawData, true));
		$this->_normalise();
		//--------------------------------------------------------------------//

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
		$iUsageKiloBytes = intval(ceil( floatval($this->getRaw('MB_Quantity')) * 1024));
		$this->setNormalised('Units', $iUsageKiloBytes);
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('Units', 'MB_Quantity'));

		// StartDatetime
		//Get Filename and replace text and compute start datetime
		$sFileDate = trim(preg_replace("/[a-zA-Z_.]/", '', $this->getNormalised('FileName')));
		$sStartDate = substr($sFileDate, 0, 10);
		$this->setNormalised('StartDatetime', $sStartDate . " 00:00:00");
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . "StartDatetime: " . var_export($this->getNormalised('StartDatetime'), true) . " (FileName: '" . $this->getNormalised('FileName') . "')");

		// EndDatetime
		$this->setNormalised('EndDatetime', $sStartDate . " 23:59:59");
		Log::get()->logIf(self::DEBUG_LOGGING, '  ' . "EndDatetime: " . var_export($this->getNormalised('EndDatetime'), true) . " (FileName: '" . $this->getNormalised('FileName') . "')");

		//RecordType
		switch ($this->getRaw('Direction')) {
			case self::DIRECTION_UPLOAD:
				$this->setNormalised('RecordType', $this-> GetConfigField('upload_record_type_id'));
				Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('RecordType', 'Direction'));
				break;

			case self::DIRECTION_DOWNLOAD:
				$this->setNormalised('RecordType', $this->GetConfigField('download_record_type_id'));
				Log::get()->logIf(self::DEBUG_LOGGING, '  ' . $this->_describeNormalisedField('RecordType', 'Direction'));
				break;

			default:
				throw new Exception("Couldn't determine data direction: " . var_export($this->getRaw('Direction'), true));
        }

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
			('Upload', 'Upload', 'Upload Usage', SERVICE_TYPE, 0, 1, 0, 0, 3, 0),
			('Download', 'Download', 'Download Usage', SERVICE_TYPE, 0, 1, 0, 0, 3, 0)
		;
		UPDATE RecordType
		SET GroupId = Id
		WHERE ServiceType = SERVICE_TYPE;
	*/

}