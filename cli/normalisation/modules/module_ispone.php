<?php
class NormalisationModuleISPOne extends NormalisationModule {
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_ISPONE;

	const DEBUG_LOGGING = true;

	function __construct($iCarrier) {
		parent::__construct($iCarrier);
		
		// define the column delimiter
		$this->_strDelimiter = ",";
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		$this->_iSequence = 0;
		
		// define the carrier CDR format
		$this->_arrDefineCarrier = self::$_aRecordDefinitions['UIR'];
	}

	public function Normalise($aCDR) {
		// set up CDR
		$this->_NewCDR($aCDR);
		
		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);
		
		$this->_SplitRawCDR($aCDR['CDR']);
		
		//--------------------------------------------------------------------//
		Log::get()->logIf(self::DEBUG_LOGGING, "Record #{$this->_iSequence}");
		$sCDR = trim($aCDR['CDR']);
		$sCDRRecordType = substr($sCDR, 0, 1); // Not really, but it's currently good enough as an indicator
		switch ($sCDRRecordType) {
			case 'U': // Header
			case 'E': // Footer
			case '': // Leading/Trailing lines
				Log::get()->logIf(self::DEBUG_LOGGING, "  Skipped (".var_export($sCDRRecordType, true).")");
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
				break;

			default:
				// Detail Record
				Log::get()->logIf(self::DEBUG_LOGGING, "  Detail Record");
				$this->_SplitRawCDR($aCDR['CDR']);
				$this->_normalise();
				break;
		}
		//--------------------------------------------------------------------//
		
		//Debug($this->_arrNormalisedData);
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}
	
	// Usage Records
	private function _normalise() {
		// CarrierRef
		$sUIR_ID = trim($this->getRaw('UIR_ID'));
		$this->setNormalised('CarrierRef', $sUIR_ID);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('CarrierRef', 'UIR_ID'));
		
		// FNN
		// NOTE: This is a best guess. There is no explicit "charged number" field
		$sOriginatingNumber = trim($this->getRaw('OriginatingNumber'));
		$this->setNormalised('FNN', $sOriginatingNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'OriginatingNumber'));
		
		// Source
		$this->setNormalised('Source', $sOriginatingNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'OriginatingNumber'));
		
		// Destination
		$sDestinationNumber = trim($this->getRaw('DestinationNumber'));
		$bDestinationIsPhoneNumber = !!preg_match('/^\d+$/', $sDestinationNumber);
		if ($bDestinationIsPhoneNumber) {
			// Looks like a phone number
			$this->setNormalised('Destination', $sDestinationNumber);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'DestinationNumber'));
		
		// Cost
		// NOTE: This seems to be the most appropriate field
		$fWholesaleCallCharge = (float)$this->getRaw('WholesaleCallCharge');
		$this->setNormalised('Cost', abs($fWholesaleCallCharge));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'WholesaleCallCharge'));
		
		// ServiceType
		$iServiceType = self::_getServiceTypeForFNN($sOriginatingNumber);
		$this->setNormalised('ServiceType', $iServiceType);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('ServiceType', 'OriginatingNumber'));

		// RecordType (Call Type Group)
		$iTariffCode = (int)$this->getRaw('TariffCode');
		$this->setNormalised('RecordType', $this->translateRecordType($iServiceType, (string)$iTariffCode));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'TariffCode', 'CallDescription'));

		// Destination (sub-Call Type)
		if ($this->_intContext > 0) { // Only resolve Destination if there is a Destination Context
			$aDestination = $this->translateDestination((string)$iTariffCode);
			$this->setNormalised('DestinationCode', $aDestination['Code']);
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode', 'TariffCode', 'CallDescription'));
		}

		// Description
		if (!$bDestinationIsPhoneNumber) {
			// Some "destination numbers" are in fact descriptions
			$this->setNormalised('Description', $sDestinationNumber);
		} else {
			// NOTE: This may not be the best field to use as the description
			$this->setNormalised('Description', trim($this->getRaw('CallDescription')));
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'DestinationNumber', 'CallDescription'));
		
		// Units
		$sUnitMeasureCode = trim($this->getRaw('UnitMeasureCode'));
		$iUnits = self::_getFlexUnits($sUnitMeasureCode, $this->getRaw('CallDuration'));
		$this->setNormalised('Units', abs($iUnits));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'UnitMeasureCode', 'CallDuration'));
		
		// StartDatetime
		$sStartDatetime	= trim($this->getRaw('CallStartTime'));
		$this->setNormalised('StartDatetime', $sStartDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'CallStartTime'));
		
		// EndDatetime
		if ($sUnitMeasureCode === self::UNIT_MEASURE_CODE_TIMED) {
			$sEndDatetime = date('Y-m-d H:i:s', strtotime("+".abs($iUnits)." seconds", strtotime($sStartDatetime)));
			$this->setNormalised('EndDatetime', $sEndDatetime);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'UnitMeasureCode', 'CallStartTime', 'CallDuration'));
		
		// Credit
		// FIXME: Is this how it's done?  Or negative Price?
		$this->setNormalised('Credit', (int)($iUnits < 0 || $fWholesaleCallCharge < 0));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Credit', 'CallDuration', 'WholesaleCallCharge'));

		//throw new Exception("TESTING");
	}

	private static function _getFlexUnits($sUnitMeasureCode, $sCallDuration) {
		switch (trim($sUnitMeasureCode)) {
			case self::UNIT_MEASURE_CODE_TIMED:
				// HH:MM:SS
				$aComponents = array();
				if (!preg_match('/^(?<hours>([0-1]\d|2[0-3])):(?<minutes>[0-5]\d):(?<seconds>[0-5]\d)$/', trim($sCallDuration), $aComponents)) {
					//throw new Exception("Invalid Call Duration (Timed format): '{$sCallDuration}'");
					Flex::assert(false, "Invalid Call Duration (Timed format): '{$sCallDuration}'");
				}
				return ((int)$aComponents['hours'] * Flex_Date::SECONDS_IN_HOUR) +
					((int)$aComponents['minutes'] * Flex_Date::SECONDS_IN_MINUTE) +
					(int)$aComponents['seconds'];

			case self::UNIT_MEASURE_CODE_DATA:
				// KBs
				return (int)$sCallDuration;

			default:
				Flex::assert(false, "Unknown ispOne Unit Measure Code '{$sUnitMeasureCode}' encountered");
		}
	}

	const UNIT_MEASURE_CODE_TIMED = 'S';
	const UNIT_MEASURE_CODE_DATA = 'K';

	static private $_aUnitMeasureCodes = array(
		self::UNIT_MEASURE_CODE_TIMED => 'Call Duration in Timed format',
		self::UNIT_MEASURE_CODE_DATA => 'Data call length in Kilobytes'
	);

	static private $_aRecordDefinitions = array(
		// UIR (Usage Information Record)
		'UIR' => array(
			'UIR_ID' => array(
				'Index' => 0
			),
			'Customer_ID' => array(
				'Index' => 1
			),
			'CallStartTime' => array(
				'Index' => 2
			),
			'OriginatingNumber' => array(
				'Index' => 3
			),
			'DestinationNumber' => array(
				'Index' => 4
			),
			'TariffCode' => array(
				'Index' => 5
			),
			'CallDuration' => array(
				'Index' => 6
			),
			'UnitMeasureCode' => array(
				'Index' => 7
			),
			'CallDescription' => array(
				'Index' => 8
			),
			'CallGroup' => array(
				'Index' => 9
			),
			'WholesaleCallCharge' => array(
				'Index' => 10
			),
			'RatedCallCharge' => array(
				'Index' => 11
			)
		)
	);
}