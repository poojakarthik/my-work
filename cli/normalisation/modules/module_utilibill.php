<?php
class NormalisationModuleUtilibill extends NormalisationModule {
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_UTILIBILL_WHOLESALECDRFILE;
	const DEBUG_LOGGING = true;

	function __construct($iCarrier) {
		$this->_arrModuleConfig['call_type_carrier_translation_context_id'] = array(
			'Type' => DATA_TYPE_INTEGER,
			'Description' => "Translation Set used to translate Utilibill Call Type to Flex Service Type, Record Type, and Destination"
		);
		$this->_arrModuleConfig['call_type_exclusion_filter'] = array(
			'Type' => DATA_TYPE_STRING,
			'Description' => "PCRE regular expression to filter records whose call_type field matches the provided expression"
		);

		parent::__construct($iCarrier);

		if (
			(!$this->GetConfigField('call_type_carrier_translation_context_id')) ||
			(false === Carrier_Translation_Context::getForId($this->GetConfigField('call_type_carrier_translation_context_id'), false))
		) {
			// Flex::assert(false, 'Utilibill Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Carrier Translation Context');
			throw new Exception('Utilibill Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Carrier Translation Context for translating Call Types');
		}

		$this->_sCallTypeExclusionFilter = $this->GetConfigField('call_type_exclusion_filter');

		// define row start (account for header rows)
		$this->_intStartRow = 0;

		$this->_iSequence = 0;
	}

	private function _shouldExcludeCDR($sLine) {
		if (is_string($this->_sCallTypeExclusionFilter) && strlen($this->_sCallTypeExclusionFilter)) {
			$aParsed = File_CSV::parseLineRFC4180($sLine);
			if (preg_match($this->_sCallTypeExclusionFilter, $aParsed[self::COLUMN_CALLTYPE])) {
				Log::get()->formatLog('Excluding Utilibill CDR #%d for matching exclude pattern: %s ~= %s', $aParsed[self::COLUMN_SEQUENCENUMBER], var_export($aParsed[self::COLUMN_CALLTYPE], true), var_export($this->_sCallTypeExclusionFilter, true));
				return true;
			}
		}
		return;
	}

	const COLUMN_SEQUENCENUMBER = 0;
	const COLUMN_CHARGETYPE = 1;
	const COLUMN_CALLTYPE = 10;
	public function Preprocessor($sLine) {
		if ($this->_shouldExcludeCDR($sLine)) {
			return ''; // Returning an empty string effectively removes the line
		}
		return $sLine;
	}

	const CHARGETYPE_USAGE = 1;
	const CHARGETYPE_NONUSAGE = 2;
	public function Normalise($aCDR) {
		// set up CDR
		$this->_NewCDR($aCDR);

		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);

		// Re-check rules for filtering CDRs
		if ($this->_shouldExcludeCDR($aCDR['CDR'])) {
			// NOTE: Not really a "non-CDR", but most appropriate. The record should really be pulled out in the pre-processor, anyway.
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		$aParsed = File_CSV::parseLineRFC4180($aCDR['CDR']);

		//--------------------------------------------------------------------//
		Log::get()->logIf(self::DEBUG_LOGGING, "Record #{$this->_iSequence}");
		switch ((int)$aParsed[self::COLUMN_CHARGETYPE]) {
			case self::CHARGETYPE_USAGE:
				$this->_arrRawData = array_associate(array(
					0 => 'sequence_number',
					1 => 'charge_type',
					2 => 'start_datetime',
					4 => 'duration_seconds',
					5 => 'a_party',
					6 => 'b_party',
					7 => 'charged_party',
					8 => 'wholesale_cost',
					9 => 'retail_cost',
					10 => 'call_type',
					13 => 'originating_location',
					14 => 'destination_location'
				), $aParsed);
				Log::get()->log(print_r($this->_arrRawData, true));
				$this->_normaliseUsage();
				break;

			case self::CHARGETYPE_NONUSAGE:
				$this->_arrRawData = array_associate(array(
					0 => 'sequence_number',
					1 => 'charge_type',
					2 => 'start_datetime',
					3 => 'end_date',
					7 => 'charged_party',
					8 => 'wholesale_cost',
					9 => 'retail_cost',
					10 => 'call_type',
					11 => 'description',
					12 => 'quantity'
				), $aParsed);
				Log::get()->log(print_r($this->_arrRawData, true));
				$this->_normaliseNonUsage();
				break;

			default:
				throw new DomainException('Unknown/unhandled Utilibill \'charge_type\': ' . $aParsed[1]);
		}

		// CarrierRef
		$this->setNormalised('CarrierRef', trim($this->getRaw('sequence_number')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('CarrierRef', 'sequence_number'));
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

	private function _normaliseUsage() {
		$oCallTypeTranslation = $this->_translateCallType($this->getRaw('call_type'));

		if (property_exists($oCallTypeTranslation, 'non_usage') && $oCallTypeTranslation->non_usage === true) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		// FNN
		$sChargedParty = trim($this->getRaw('charged_party'));
		$this->setNormalised('FNN', $sChargedParty);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'charged_party'));

		if (isset($oCallTypeTranslation->swap_originating_destination) && $oCallTypeTranslation->swap_originating_destination) {
			// Some call types have the origin/destination swapped
			Log::get()->log('[!] A-Party ("Origin") and B-Party ("Destination") swapped');
			$sCallingNumber = trim($this->getRaw('b_party'));
			$sCalledNumber = trim($this->getRaw('a_party'));
		} else {
			$sCallingNumber = trim($this->getRaw('a_party'));
			$sCalledNumber = trim($this->getRaw('b_party'));
		}

		// Source
		if ($sCallingNumber) {
			$sCallingNumber = self::_localiseAustralianNumber($sCallingNumber);
			$this->setNormalised('Source', $sCallingNumber);
		} else {
			// Some records don't have a Source, which, at least as far as I can see, is implicitly the Charged Party
			$this->setNormalised('Source', $sChargedParty);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'a_party', 'b_party', 'charged_party'));

		// Destination
		if (preg_match('/\d/', $sCalledNumber)) {
			// B-Party can sometimes have useless values, e.g. "testra.internet". Wild assumption that these will never contain digits.
			$sCalledNumber = self::_localiseAustralianNumber($sCalledNumber);
			$this->setNormalised('Destination', $sCalledNumber);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'a_party', 'b_party', 'charged_party'));

		// Cost
		// NOTE: We use the wholesale_cost, as this represents the cost to us
		$fCost = ((float)$this->getRaw('wholesale_cost')) / 100; // cost is in cents
		$this->setNormalised('Cost', $fCost);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'wholesale_cost', 'retail_cost'));

		$oCallTypeTranslation = $this->_translateCallType($this->getRaw('call_type'));

		// ServiceType
		$iServiceType = Service_Type::getForId($oCallTypeTranslation->service_type_id)->id;
		$this->setNormalised('ServiceType', $iServiceType);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('ServiceType', 'call_type', 'charged_party'));

		// RecordType (Call Type Group)
		// $oRecordType = Record_Type::getForId($this->translateRecordType($iServiceType, (string)$oCallTypeTranslation->record_type_code));
		$oRecordType = Record_Type::getForServiceTypeAndCode($iServiceType, (string)$oCallTypeTranslation->record_type_code);
		$this->setNormalised('RecordType', $oRecordType->Id);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'call_type'));

		// Destination (sub-Call Type)
		$this->_intContext = $oRecordType->Context;
		if ($oRecordType->Context > 0) {
			if (isset($oCallTypeTranslation->destination_code)) {
				$this->setNormalised('DestinationCode', $oCallTypeTranslation->destination_code);
			} else {
				Log::get()->log('No Destination Code, using fallback for Carrier Translation Context #' . $oRecordType->Context);
				$this->setNormalised('DestinationCode', Destination::getForId(Destination_Context::getForId($oRecordType->Context)->fallback_destination_id)->code);
			}
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode', 'call_type'));

		// Description
		// NOTE: Not particulary important for usage
		// $sDescription = trim($this->getRaw('origin_destination'));
		// if (preg_match('/[a-z]/i', $sDescription)) {
		// 	// Some descriptions/locations are numeric (or empty), and therefore a bit useless
		// 	$this->setNormalised('Description', $sDescription);
		// }
		// Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'originating_location'));

		// Units
		$iDuration = (int)$this->getRaw('duration_seconds');
		switch ($oRecordType->DisplayType) {
			case RECORD_DISPLAY_CALL:
				$this->setNormalised('Units', $iDuration);
				break;

			case RECORD_DISPLAY_S_AND_E:
				// NOTE: True S&E shouldn't be appearing as usage
				Flex::assert('Utilibill: Unimplemented support for S&E-style usage encountered (Call Type: ' . $this->getRaw('call_type') . ')');
				break;

			case RECORD_DISPLAY_DATA:
				// NOTE: duration_seconds represents combined download/upload in KB
				$this->setNormalised('Units', $iDuration);
				break;

			case RECORD_DISPLAY_SMS:
				// NOTE: SMS-like items have a duration_seconds of 0
				$this->setNormalised('Units', 1);
				break;
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'duration_seconds'));

		// StartDatetime
		$sStartDatetime = trim($this->getRaw('start_datetime'));
		$this->setNormalised('StartDatetime', self::_extractStartDatetime($sStartDatetime));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'start_datetime'));

		if ($oRecordType->DisplayType === RECORD_DISPLAY_CALL) {
			// EndDatetime
			// NOTE: end_date field only appears to be applicable to non-usage
			$this->setNormalised('EndDatetime', date('Y-m-d H:i:s', strtotime("+{$iDuration} seconds", strtotime($sStartDatetime))));
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'start_datetime', 'duration_seconds', 'end_date'));

		// Credit
		// NOTE: Only encountered credit examples for non-usage
		$this->setNormalised('Credit', intval($fCost < 0));

		// throw new Exception("TESTING");
	}

	private function _normaliseNonUsage() {
		$oCallTypeTranslation = $this->_translateCallType($this->getRaw('call_type'));

		if (property_exists($oCallTypeTranslation, 'non_usage') && $oCallTypeTranslation->non_usage === true) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		// FNN
		$sChargedParty = trim($this->getRaw('charged_party'));
		$this->setNormalised('FNN', $sChargedParty);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'charged_party'));

		// Source
		// NOTE: non-usage records don't have a Source, which is implicitly the Charged Party
		$this->setNormalised('Source', $sChargedParty);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'charged_party'));

		// Cost
		// NOTE: We use the wholesale_cost, as this represents the cost to us
		$fCost = ((float)$this->getRaw('wholesale_cost')) / 100; // cost is in cents
		$this->setNormalised('Cost', abs($fCost));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'wholesale_cost', 'retail_cost'));

		// ServiceType
		$iServiceType = Service_Type::getForId($oCallTypeTranslation->service_type_id)->id;
		$this->setNormalised('ServiceType', $iServiceType);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('ServiceType', 'call_type', 'charged_party'));

		// RecordType (Call Type Group)
		// $oRecordType = Record_Type::getForId($this->translateRecordType($iServiceType, (string)$oCallTypeTranslation->record_type_code));
		$oRecordType = Record_Type::getForServiceTypeAndCode($iServiceType, (string)$oCallTypeTranslation->record_type_code);
		$this->setNormalised('RecordType', $oRecordType->Id);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'call_type'));

		// Destination (sub-Call Type)
		$this->_intContext = $oRecordType->Context;
		if ($oRecordType->Context > 0) {
			if (isset($oCallTypeTranslation->destination_code)) {
				$this->setNormalised('DestinationCode', $oCallTypeTranslation->destination_code);
			} else {
				Log::get()->log('No Destination Code, using fallback for Carrier Translation Context #' . $oRecordType->Context);
				$this->setNormalised('DestinationCode', Destination::getForId(Destination_Context::getForId($oRecordType->Context)->fallback_destination_id)->code);
			}
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode', 'call_type'));

		// Description
		$this->setNormalised('Description', trim($this->getRaw('description')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'description'));

		// Units
		$iQuantity = (int)$this->getRaw('quantity');
		$this->setNormalised('Units', abs($iQuantity));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'quantity'));

		// StartDatetime
		$sStartDatetime = trim($this->getRaw('start_datetime'));
		$this->setNormalised('StartDatetime', self::_extractStartDatetime($sStartDatetime));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'start_datetime'));

		// EndDatetime
		// NOTE: end_date field only appears to be applicable to non-usage, and only for range charges
		$sEndDate = $this->getRaw('end_date');
		if (trim($sEndDate)) {
			$this->setNormalised('EndDatetime', self::_extractEndDatetime($sEndDate));
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'end_date'));

		// Credit
		// NOTE: wholesale_cost, retail_cost, and quantity all have negative values to represent a credit
		$this->setNormalised('Credit', intval($iQuantity < 0));

		// throw new Exception("TESTING");
	}

	private static function _extractStartDatetime($sStartDatetime) {
		return preg_replace('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})(?:.\d+)$/', '$1', $sStartDatetime);
	}

	private static function _extractEndDatetime($sEndDatetime) {
		return preg_replace('/^(\d{4}-\d{2}-\d{2})(?: \d{2}:\d{2}:\d{2})?(?:.\d+)$/', '$1 23:59:59', $sEndDatetime);
	}

	/*
		call_type => Service Type, Record Type, and Destination mapping values should be in the form:

		{
			service_type_id: 123,
			record_type_code: '456',
			destination_code: 789 (nullable),
			swap_originating_destination: true/false (nullable, null == false)
		}
	*/

	private $_aTranslations;
	private function _translateCallType($sCallType) {
		// Fetch translations
		if (!isset($this->_aTranslations)) {
			$oTranslationsResult = DataAccess::get()->query('
				SELECT *
				FROM carrier_translation
				WHERE carrier_translation_context_id = <carrier_translation_context_id>
			', array(
				'carrier_translation_context_id' => $this->GetConfigField('call_type_carrier_translation_context_id')
			));

			$this->_aTranslations = array();
			while ($oTranslation = $oTranslationsResult->fetch_object()) {
				$this->_aTranslations[] = $oTranslation;
			}
		}

		foreach ($this->_aTranslations as $oTranslation) {
			if (trim($oTranslation->in_value) === trim($sCallType)) {
				$oTranslationValue = @json_decode($oTranslation->out_value);
				if (false === $oTranslationValue) {
					throw new Exception(
						'Translation Set #' .
						$this->GetConfigField('call_type_carrier_translation_context_id') . ':' . Carrier_Translation_Context::getForId($this->GetConfigField('call_type_carrier_translation_context_id'))->name .
						' result for ' . var_export($sCallType, true) . ' (#' . $oTranslation->carrier_translation_context_id .') should be a JSON object (Error: ' . $php_errormsg . '): ' . var_export($oTranslation->out_value, true)
					);
				}
				return $oTranslationValue;
			}
		}
		throw new Exception(
			'Translation Set #' .
			$this->GetConfigField('call_type_carrier_translation_context_id') . ':' . Carrier_Translation_Context::getForId($this->GetConfigField('call_type_carrier_translation_context_id'))->name .
			' is missing a translation for: ' . var_export($sCallType, true)
		);
	}

	private static function _localiseAustralianNumber($sNumber) {
		// Remove 61 prefix for Australian destinations for fleet matching
		if (preg_match('/^((00)?11)611/', $sNumber)) {
			// Inbound (13/1300/1800/19) don't get a 0 prepended
			return preg_replace('/^(?:(?:00)?11)61(.+)$/', '$1', $sNumber);
		} elseif (preg_match('/^((00)?11)61[23456789]/', $sNumber)) {
			// Everything else gets a 0 prepended
			return preg_replace('/^(?:(?:00)?11)61(.+)$/', '0$1', $sNumber);
		}
		return $sNumber;
	}
}