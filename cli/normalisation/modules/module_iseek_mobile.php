<?php
class NormalisationModuleIseekMobile extends NormalisationModule {
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_MOBILE;

	const RECORD_TYPE_HEADER = '01';
	const RECORD_TYPE_TRAILER = '99';

	const RECORD_TYPE_HOMECALL = '10';
	const RECORD_TYPE_ROAMCALL = '20';
	const RECORD_TYPE_MESSAGEEVENT = '30';
	const RECORD_TYPE_CONTENTRECORD = '40';
	const RECORD_TYPE_DATARECORD = '50';

	const DEBUG_LOGGING = true;

	function __construct($iCarrier) {
		parent::__construct($iCarrier);
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		$this->_iSequence = 0;
	}

	public function Normalise($aCDR) {
		// set up CDR
		$this->_NewCDR($aCDR);
		
		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);
		
		//--------------------------------------------------------------------//
		Log::get()->logIf(self::DEBUG_LOGGING, "Record #{$this->_iSequence}");

		// CarrierRef
		// NOTE: There is no Unique Identifier supplied in this file format: generate one
		$sGeneratedUID = $this->_GenerateUID();
		$this->setNormalised('CarrierRef', $sGeneratedUID);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.'Generated UID: '.var_export($sGeneratedUID, true));
		
		// ServiceType
		// NOTE: Always Mobile
		$this->setNormalised('ServiceType', SERVICE_TYPE_MOBILE);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.'Service Type: '.var_export(SERVICE_TYPE_MOBILE, true));

		$sCDR = trim($aCDR['CDR']);
		$sCDRRecordType = substr($sCDR, 0, 2);
		switch ($sCDRRecordType) {
			case self::RECORD_TYPE_HOMECALL: // Home Call
				$this->_normaliseHome();
				break;

			case self::RECORD_TYPE_ROAMCALL: // Roam Call
				Flex::assert(false, "iSeek Mobile Normalisation: Roam Call Encountered (currently unsupported)", $aCDR['CDR']);
				//$this->_normaliseRoam();
				break;

			case self::RECORD_TYPE_MESSAGEEVENT: // Message Event
				$this->_normaliseMessage();
				break;

			case self::RECORD_TYPE_CONTENTRECORD: // Content Record
				$this->_normaliseContent();
				//$this->_normaliseContent();
				break;

			case self::RECORD_TYPE_DATARECORD: // Data Record
				$this->_normaliseData();
				break;

			case self::RECORD_TYPE_HEADER: // Header
			case self::RECORD_TYPE_TRAILER: // Trailer
			default: // Leading/Trailing lines
				Log::get()->logIf(self::DEBUG_LOGGING, "  Skipped (".var_export($sCDRRecordType, true).")");
				return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
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

	const CALL_TYPE_INTERNATIONAL = 3; // Call types are constants defined by the file format, not configuration
	protected static $_aHomeTranslatePriorities = array('record_type', 'gsm_service_type', 'call_medium', 'call_type', 'is_pushtotalk');
	private function _normaliseHome() {
		$this->_arrDefineCarrier = self::$_aRecordDefinitions[self::RECORD_TYPE_HOMECALL];
		$this->_SplitRawCDR();
		Log::get()->logIf(self::DEBUG_LOGGING, '  Raw Data: '.var_export($this->_arrRawData, true));

		// FNN
		$sCellularNumber = trim($this->getRaw('cellular_number'));
		$this->setNormalised('FNN', $sCellularNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'cellular_number'));
		
		// Source
		// NOTE: This seems the most appropriate. There don't seem to be any alternatives.
		$this->setNormalised('Source', $sCellularNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'cellular_number'));
		
		// Destination
		$sCalledNumber = trim($this->getRaw('called_number'));
		$bCalledNumberIsPushToTalk = !!preg_match('/^3\d{13}$/', $sCalledNumber);
		if (!$bCalledNumberIsPushToTalk) {
			// Looks like a phone number
			$this->setNormalised('Destination', $sCalledNumber);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'called_number'));
		
		// Cost
		// NOTE: This seems to be the most appropriate field
		$fTotalCharges = self::_parseCurrency($this->getRaw('total_charges'));
		$this->setNormalised('Cost', $fTotalCharges);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'total_charges'));

		// RecordType (Call Type Group)
		$sCallMedium = $this->getRaw('call_medium'); // Indicates Mobile2Mobile or Mobile2Fixed
		$sCallType = $this->getRaw('call_type'); // Indicates Call Type
		$sGSMServiceType = $this->getRaw('gsm_service_type'); // Inticates Voice vs Data
		$iIsPushToTalk = (int)$bCalledNumberIsPushToTalk;
		$this->setNormalised(
			'RecordType',
			Record_Type::getForServiceTypeAndCode(
				$this->getNormalised('ServiceType'),
				Carrier_Translation_Context::getForId($this->GetConfigField('CallGroupCarrierTranslationContextId'))->translateJSON(
					array(
						'record_type' => (int)self::RECORD_TYPE_HOMECALL,
						'gsm_service_type' => $this->getRaw('gsm_service_type'), // Inticates Voice vs Data
						'call_medium' => $this->getRaw('call_medium'), // Indicates Mobile2Mobile or Mobile2Fixed
						'call_type' => $this->getRaw('call_type'), // Indicates Call Type
						'is_pushtotalk' => $bCalledNumberIsPushToTalk
					),
					self::$_aDataTranslatePriorities
				)->out_value
			)->id
		);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'record_type', 'call_medium', 'gsm_service_type', 'call_type', 'called_number'));

		// Destination (sub-Call Type)
		if ($this->_intContext > 0) { // Only resolve Destination if there is a Destination Context
			if ((int)$sCallType === self::CALL_TYPE_INTERNATIONAL) {
				// International Calls
				Flex::assert(false, "iSeek Mobile Normalisation: Mobile to International Record Encountered", $this->_arrRawData);
			} else {
				// NOTE: We're currently assuming no call sub-types.  This may change in the future.
				//Flex::assert(false, "iSeek Mobile Normalisation: Non-International Destination Context Record Encountered", $this->_arrRawData);
				$aDestination = $this->_getUnknownDestination();
				$this->setNormalised('DestinationCode', $aDestination['Code']);
			}
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode', 'call_medium', 'gsm_service_type', 'call_type', 'called_number'));
		}

		// Description
		// NOTE: This is probably acceptable as the description.  Seems to be a end-service description or country name
		$this->setNormalised('Description', trim($this->getRaw('called_place')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'called_place'));
		
		// Units
		$aElapsedTime = array();
		preg_match('/^(?<minutes>\d{4})(?<seconds>\d{2})$/', $this->getRaw('elapsed_time'), $aElapsedTime);
		$iElapsedSeconds = (int)$aElapsedTime['seconds'] + ((int)$aElapsedTime['minutes'] * Flex_Date::SECONDS_IN_MINUTE);
		if ($bCalledNumberIsPushToTalk) {
			// Push-to-Talk
			// TODO
			Flex::assert(false, "iSeek Mobile Normalisation: Push to Talk Record Encountered", $this->_arrRawData);
			$iDataInKB = (int)substr($this->getRaw('called_number'), 1);
		} else {
			// Call
			$this->setNormalised('Units', $iElapsedSeconds);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'elapsed_time', 'called_number'));

		// StartDatetime
		$sStartDatetime = date('Y-m-d H:i:s', strtotime($this->getRaw('call_placed_date').'t'.$this->getRaw('call_placed_time')));
		$this->setNormalised('StartDatetime', $sStartDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'call_placed_date', 'call_placed_time'));
		
		// EndDatetime
		$sEndDatetime = date('Y-m-d H:i:s', strtotime("+{$iElapsedSeconds} seconds", strtotime($sStartDatetime)));
		$this->setNormalised('EndDatetime', $sEndDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'call_placed_date', 'call_placed_time', 'elapsed_time'));
		
		// Credit
		// NOTE: There doesn't appear to be a way to determine this.  Assume debit.
		$this->setNormalised('Credit', 0);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.'Credit: 0');

		//throw new Exception("TESTING");
	}

	protected static $_aMessageTranslatePriorities = array('record_type', 'direction', 'event_type');
	private function _normaliseMessage() {
		$this->_arrDefineCarrier = self::$_aRecordDefinitions[self::RECORD_TYPE_MESSAGEEVENT];
		$this->_SplitRawCDR();
		Log::get()->logIf(self::DEBUG_LOGGING, '  Raw Data: '.var_export($this->_arrRawData, true));

		// FNN
		$sCellularNumber = trim($this->getRaw('cellular_number'));
		$this->setNormalised('FNN', $sCellularNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'cellular_number'));
		
		// Source
		// NOTE: This seems the most appropriate. There don't seem to be any alternatives.
		$this->setNormalised('Source', $sCellularNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'cellular_number'));
		
		// Destination
		$sCalledNumber = trim($this->getRaw('called_number'));
		$this->setNormalised('Destination', $sCalledNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'called_number'));
		
		// Cost
		// NOTE: This seems to be the most appropriate field
		$fTotalCharges = self::_parseCurrency($this->getRaw('total_charges'));
		$this->setNormalised('Cost', $fTotalCharges);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'total_charges'));

		// RecordType (Call Type Group)
		$sDirection = $this->getRaw('direction'); // Indicates Mobile Terminating or Mobile Originating
		$sEventType = $this->getRaw('event_type'); // Indicates Event Type
		$this->setNormalised(
			'RecordType',
			Record_Type::getForServiceTypeAndCode(
				$this->getNormalised('ServiceType'),
				Carrier_Translation_Context::getForId($this->GetConfigField('CallGroupCarrierTranslationContextId'))->translateJSON(
					array(
						'record_type' => (int)self::RECORD_TYPE_MESSAGEEVENT,
						'direction' => $this->getRaw('direction'),
						'event_type' => $this->getRaw('event_type')
					),
					self::$_aMessageTranslatePriorities
				)->out_value
			)->id
		);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'record_type', 'direction', 'event_type'));

		// Destination (sub-Call Type)
		if ($this->_intContext > 0) {
			// NOTE: Not implemented (or even required at this point?)
			$aDestination = $this->_getUnknownDestination();
			$this->setNormalised('DestinationCode', $aDestination['Code']);
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode'). " (always Unknown Destination)");
		}

		// Description
		// NOTE: This is probably acceptable as the description.  Seems to be a end-service description
		$this->setNormalised('Description', trim($this->getRaw('event_description')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'event_description'));
		
		// Units
		// NOTE: This *might* be incorrect
		$this->setNormalised('Units', (int)$this->getRaw('chargeable_units'));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'chargeable_units'));

		// StartDatetime
		$sStartDatetime = date('Y-m-d H:i:s', strtotime($this->getRaw('event_submission_date').'t'.$this->getRaw('event_submission_time')));
		$this->setNormalised('StartDatetime', $sStartDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'event_submission_date', 'event_submission_time'));

		// EndDatetime
		// NOTE: Not currently used; could possibly look into using Event Submission timestamp
		
		// Credit
		// NOTE: There doesn't appear to be a way to determine this.  Assume debit.
		$this->setNormalised('Credit', 0);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.'Credit: 0');

		//throw new Exception("TESTING");
	}

	const DATA_CHARGING_METHOD_DURATION = 'D';
	const DATA_CHARGING_METHOD_VOLUME = 'V';
	const DATA_CHARGING_METHOD_EVENT = 'E';
	protected static $_aDataTranslatePriorities = array('record_type', 'gsm_service_type', 'network_type', 'call_type', 'data_charging_method');
	private function _normaliseData() {
		$this->_arrDefineCarrier = self::$_aRecordDefinitions[self::RECORD_TYPE_DATARECORD];
		$this->_SplitRawCDR();
		Log::get()->logIf(self::DEBUG_LOGGING, '  Raw Data: '.var_export($this->_arrRawData, true));

		// FNN
		$sCellularNumber = trim($this->getRaw('cellular_number'));
		$this->setNormalised('FNN', $sCellularNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'cellular_number'));
		
		// Source
		// NOTE: This seems the most appropriate. There don't seem to be any alternatives.
		$this->setNormalised('Source', $sCellularNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'cellular_number'));
		
		// Destination
		$sCalledNumber = trim($this->getRaw('called_number'));
		if ($sCalledNumber) {
			$this->setNormalised('Destination', $sCalledNumber);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'called_number'));
		
		// Cost
		// NOTE: This seems to be the most appropriate field
		$fTotalCharges = self::_parseCurrency($this->getRaw('total_charges'));
		$this->setNormalised('Cost', $fTotalCharges);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'total_charges'));

		// RecordType (Call Type Group)
		$sNetworkType = $this->getRaw('network_type'); // GPRS, 3G, etc
		$sCallType = $this->getRaw('call_type'); // Indicates Call Type
		$sGSMServiceType = $this->getRaw('gsm_service_type'); // Inticates Voice vs Data
		$sDataChargingMethod = $this->getRaw('data_charging_method'); // (D)uration, (V)olume, (E)vent.  This is not ideal, though necessary, as can indicate duration-based or item-based usage, which mandates a different Record Type.
		$this->setNormalised(
			'RecordType',
			Record_Type::getForServiceTypeAndCode(
				$this->getNormalised('ServiceType'),
				Carrier_Translation_Context::getForId($this->GetConfigField('CallGroupCarrierTranslationContextId'))->translateJSON(
					array(
						'record_type' => (int)self::RECORD_TYPE_DATARECORD,
						'gsm_service_type' => $this->getRaw('gsm_service_type'), // Inticates Voice vs Data
						'network_type' => $this->getRaw('network_type'), // GPRS, 3G, etc
						'call_type' => $this->getRaw('call_type'), // Indicates Call Type
						'data_charging_method' => $sDataChargingMethod
					),
					self::$_aDataTranslatePriorities
				)->out_value
			)->id
		);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'record_type', 'network_type', 'gsm_service_type', 'call_type', 'data_charging_method'));

		// Destination (sub-Call Type)
		if ($this->_intContext > 0) {
			// NOTE: Not implemented (or even required at this point?)
			// The service_class field (identifies different services, e.g. Facebook and YouTube) might be appropriate
			$aDestination = $this->_getUnknownDestination();
			$this->setNormalised('DestinationCode', $aDestination['Code']);
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode'). " (always Unknown Destination)");
		}

		// Description
		// NOTE: Haven't found any meaningful description in the source data
		
		// Units
		$oFlexRecordType = Record_Type::getForId($this->getNormalised('RecordType'));
		$iFlexRecordTypeDisplayType = $oFlexRecordType->DisplayType;
		if ($sDataChargingMethod === self::DATA_CHARGING_METHOD_VOLUME && $iFlexRecordTypeDisplayType === RECORD_DISPLAY_DATA) {
			// NOTE: I assume that we're supposed to be adding these together...
			// Volume is measured in kilobytes
			$iPeakUsageKB = (int)$this->getRaw('peak_usage');
			$iOffPeakUsageKB = (int)$this->getRaw('off_peak_usage');
			$iOtherUsageKB = (int)$this->getRaw('other_usage');
			$this->setNormalised('Units', $iPeakUsageKB + $iOffPeakUsageKB + $iOtherUsageKB);
		} elseif ($sDataChargingMethod === self::DATA_CHARGING_METHOD_DURATION && $iFlexRecordTypeDisplayType === RECORD_DISPLAY_CALL) {
			// Duration is measured in seconds
			$iPeakUsageSeconds = (int)$this->getRaw('peak_usage');
			$iOffPeakUsageSeconds = (int)$this->getRaw('off_peak_usage');
			$iOtherUsageSeconds = (int)$this->getRaw('other_usage');
			$this->setNormalised('Units', $iPeakUsageSeconds + $iOffPeakUsageSeconds + $iOtherUsageSeconds);
		} elseif ($sDataChargingMethod === self::DATA_CHARGING_METHOD_EVENT && $iFlexRecordTypeDisplayType === RECORD_DISPLAY_S_AND_E) {
			// Event Count
			$this->setNormalised('Units', 1);
		} else {
			Flex::assert(
				false,
				"iSeek Mobile Normalisation: Data Charging Method (".var_export($sDataChargingMethod, true).")/Record Type Display Type (".var_export($iFlexRecordTypeDisplayType, true).") Mismatch",
				array('Record Type' => $oFlexRecordType->toArray(), 'Raw Data' => $this->_arrRawData)
			);
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'data_charging_method', 'peak_usage', 'off_peak_usage', 'other_usage'));

		// StartDatetime
		$sStartDatetime = date('Y-m-d H:i:s', strtotime($this->getRaw('call_placed_date').'t'.$this->getRaw('call_placed_time')));
		$this->setNormalised('StartDatetime', $sStartDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'call_placed_date', 'call_placed_time'));
		
		// EndDatetime
		$sEndDatetime = date('Y-m-d H:i:s', strtotime($this->getRaw('call_end_date').'t'.$this->getRaw('call_end_time')));
		$this->setNormalised('EndDatetime', $sEndDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'call_end_date', 'call_end_time'));
		
		// Credit
		// NOTE: There doesn't appear to be a way to determine this.  Assume debit.
		$this->setNormalised('Credit', 0);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.'Credit: 0');

		//throw new Exception("TESTING");
	}

	protected static $_aContentTranslatePriorities = array('record_type', 'provider_id', 'category_type', 'content_category', 'content_id', 'content_charge_type');
	private function _normaliseContent() {
		$this->_arrDefineCarrier = self::$_aRecordDefinitions[self::RECORD_TYPE_CONTENTRECORD];
		$this->_SplitRawCDR();
		Log::get()->logIf(self::DEBUG_LOGGING, '  Raw Data: '.var_export($this->_arrRawData, true));

		// FNN
		$sCellularNumber = trim($this->getRaw('cellular_number'));
		$this->setNormalised('FNN', $sCellularNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'cellular_number'));

		// Source
		// NOTE: Not applicable

		// Destination
		// NOTE: Not applicable
		
		// Cost
		// NOTE: This seems to be the most appropriate field
		$fTotalCharges = self::_parseCurrency($this->getRaw('total_charges'));
		$this->setNormalised('Cost', $fTotalCharges);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'total_charges'));

		// RecordType (Call Type Group)
		$sProviderId = $this->getRaw('provider_id');
		$sCategoryType = $this->getRaw('category_type');
		$sContentCategory = $this->getRaw('content_category');
		$sContentId = $this->getRaw('content_id');
		$sContentChargeType = $this->getRaw('content_charge_type');
		$this->setNormalised(
			'RecordType',
			Record_Type::getForServiceTypeAndCode(
				$this->getNormalised('ServiceType'),
				Carrier_Translation_Context::getForId($this->GetConfigField('CallGroupCarrierTranslationContextId'))->translateJSON(
					array(
						'record_type' => (int)self::RECORD_TYPE_CONTENTRECORD,
						'provider_id' => $this->getRaw('provider_id'),
						'category_type' => $this->getRaw('category_type'),
						'content_category' => $this->getRaw('content_category'),
						'content_id' => $this->getRaw('content_id'),
						'content_charge_type' => $this->getRaw('content_charge_type')
					),
					self::$_aContentTranslatePriorities
				)->out_value
			)->id
		);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'record_type', 'provider_id', 'category_type', 'content_category', 'content_id', 'content_charge_type', 'content_description'));

		// Destination (sub-Call Type)
		if ($this->_intContext > 0) {
			// NOTE: Not implemented (or even required at this point?)
			// The service_class field (identifies different services, e.g. Facebook and YouTube) might be appropriate
			$aDestination = $this->_getUnknownDestination();
			$this->setNormalised('DestinationCode', $aDestination['Code']);
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode'). " (always Unknown Destination)");
		}

		// Description
		$this->setNormalised('Description', $this->getRaw('content_description'));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'content_description'));
		
		// Units
		// NOTE: There is a `time_elapsed` field, though is seldom appropriate for rating units
		$this->setNormalised('Units', 1);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.'Units: 1');

		// StartDatetime
		$sStartDatetime = date('Y-m-d H:i:s', strtotime($this->getRaw('request_date').'t'.$this->getRaw('request_time')));
		$this->setNormalised('StartDatetime', $sStartDatetime);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'request_date', 'request_time'));
		
		// EndDatetime
		if ($iElapsedTime = (int)$this->getRaw('elapsed_time')) {
			$sEndDatetime = date('Y-m-d H:i:s', strtotime("+{$iElapsedTime} seconds", strtotime($sStartDatetime)));
			$this->setNormalised('EndDatetime', $sEndDatetime);
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'request_date', 'request_time', 'elapsed_time'));
		}
		
		// Credit
		$this->setNormalised('Credit', (int)self::_chargeSignIsCredit($this->getRaw('charge_sign')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Credit', 'charge_sign'));

		//throw new Exception("TESTING");
	}

	// INTERNAL UTILITY
	//-----------------------------------------------------------------------//
	private static function _parseCurrency($sValue) {
		// Format is $$$$$$$$CCC
		return (float)(substr($sValue, 0, 8).'.'.substr($sValue, -3));
	}

	private static function _chargeSignIsCredit($sChargeSign) {
		switch (trim(strtoupper($sChargeSign))) {
			case 'DR':
				return false;
				break;
			case 'CR':
				return true;
				break;
		}
		Flex::assert(
			false,
			"Unhandled Charge Sign ".var_export($sChargeSign, true)." encountered",
			$sChargeSign,
			"iSeek Mobile Normaliser: Unhandled Charge Sign ".var_export($sChargeSign, true)." encountered"
		);
	}

	// RECORD DEFINITIONS
	//-----------------------------------------------------------------------//
	static private $_aRecordDefinitions = array(
		// Home call
		//-------------------------------------------------------------------//
		self::RECORD_TYPE_HOMECALL => array(
			'record_type' => array(
				'Start' => 0,
				'Length' => 2
			),
			'cellular_number' => array(
				'Start' => 2,
				'Length' => 18
			),
			'imsi_number' => array(
				'Start' => 20,
				'Length' => 15
			),
			'imei' => array(
				'Start' => 35,
				'Length' => 15
			),
			'receiving_company_id' => array(
				'Start' => 50,
				'Length' => 8
			),
			'sending_company_id' => array(
				'Start' => 58,
				'Length' => 8
			),
			'serving_company_id' => array(
				'Start' => 66,
				'Length' => 8
			),
			'call_placed_date' => array(
				'Start' => 74,
				'Length' => 8
			),
			'call_placed_time' => array(
				'Start' => 82,
				'Length' => 6
			),
			'elapsed_time' => array(
				'Start' => 88,
				'Length' => 6
			),
			'chargeable_time' => array(
				'Start' => 94,
				'Length' => 6
			),
			'chargeable_units' => array(
				'Start' => 100,
				'Length' => 6
			),
			'rated_date' => array(
				'Start' => 106,
				'Length' => 8
			),
			'called_number' => array(
				'Start' => 114,
				'Length' => 21
			),
			'call_medium' => array(
				'Start' => 135,
				'Length' => 1
			),
			'completion_indicator' => array(
				'Start' => 136,
				'Length' => 1
			),
			'termination_indicator' => array(
				'Start' => 137,
				'Length' => 1
			),
			'latitude' => array(
				'Start' => 138,
				'Length' => 8
			),
			'longitude' => array(
				'Start' => 146,
				'Length' => 8
			),
			'air_time_rate_period' => array(
				'Start' => 154,
				'Length' => 1
			),
			'air_time_charges' => array(
				'Start' => 155,
				'Length' => 11
			),
			'surcharges' => array(
				'Start' => 166,
				'Length' => 11
			),
			'action_code_1' => array(
				'Start' => 177,
				'Length' => 1
			),
			'supplimentary_service_code_1' => array(
				'Start' => 178,
				'Length' => 2
			),
			'action_code_2' => array(
				'Start' => 180,
				'Length' => 1
			),
			'supplimentary_service_code_2' => array(
				'Start' => 181,
				'Length' => 2
			),
			'action_code_3' => array(
				'Start' => 183,
				'Length' => 1
			),
			'supplimentary_service_code_3' => array(
				'Start' => 184,
				'Length' => 2
			),
			'action_code_4' => array(
				'Start' => 186,
				'Length' => 1
			),
			'supplimentary_service_code_4' => array(
				'Start' => 187,
				'Length' => 2
			),
			'action_code_5' => array(
				'Start' => 189,
				'Length' => 1
			),
			'supplimentary_service_code_5' => array(
				'Start' => 190,
				'Length' => 2
			),
			'called_place' => array(
				'Start' => 192,
				'Length' => 15
			),
			'calling_place' => array(
				'Start' => 207,
				'Length' => 15
			),
			'zone_indicator' => array(
				'Start' => 222,
				'Length' => 1
			),
			'toll_rate_period' => array(
				'Start' => 223,
				'Length' => 1
			),
			'toll_call_placed_date' => array(
				'Start' => 224,
				'Length' => 8
			),
			'toll_call_placed_time' => array(
				'Start' => 232,
				'Length' => 6
			),
			'toll_call_time' => array(
				'Start' => 238,
				'Length' => 6
			),
			'toll_charges' => array(
				'Start' => 244,
				'Length' => 11
			),
			'distance' => array(
				'Start' => 255,
				'Length' => 7
			),
			'transaction_code' => array(
				'Start' => 262,
				'Length' => 3
			),
			'product_plan_code' => array(
				'Start' => 265,
				'Length' => 5
			),
			'bill_cycle' => array(
				'Start' => 270,
				'Length' => 4
			),
			'total_taxes' => array(
				'Start' => 274,
				'Length' => 11
			),
			'total_charges' => array(
				'Start' => 285,
				'Length' => 11
			),
			'call_type' => array(
				'Start' => 296,
				'Length' => 1
			),
			'gsm_service_type' => array(
				'Start' => 297,
				'Length' => 1
			),
			'gsm_service_code' => array(
				'Start' => 298,
				'Length' => 2
			),
			'msc_id' => array( // Mobile Switching Centre
				'Start' => 300,
				'Length' => 15
			),
			'relaxed_rate_discount_flag' => array(
				'Start' => 315,
				'Length' => 2
			),
			'filler' => array(
				'Start' => 317,
				'Length' => 1
			)
		),

		// Roam Call
		//-------------------------------------------------------------------//
		self::RECORD_TYPE_ROAMCALL => array(
			'record_type' => array(
				'Start' => 0,
				'Length' => 2
			),
			'cellular_number' => array(
				'Start' => 2,
				'Length' => 18
			),
			'imsi_number' => array(
				'Start' => 20,
				'Length' => 15
			),
			'imei' => array(
				'Start' => 35,
				'Length' => 15
			),
			'receiving_company_id' => array(
				'Start' => 50,
				'Length' => 8
			),
			'sending_company_id' => array(
				'Start' => 58,
				'Length' => 8
			),
			'serving_company_id' => array(
				'Start' => 66,
				'Length' => 8
			),
			'call_placed_date' => array(
				'Start' => 74,
				'Length' => 8
			),
			'call_placed_time' => array(
				'Start' => 82,
				'Length' => 6
			),
			'elapsed_time' => array(
				'Start' => 88,
				'Length' => 6
			),
			'chargeable_time' => array(
				'Start' => 94,
				'Length' => 6
			),
			'chargeable_units' => array(
				'Start' => 100,
				'Length' => 6
			),
			'rated_date' => array(
				'Start' => 106,
				'Length' => 8
			),
			'called_number' => array(
				'Start' => 114,
				'Length' => 21
			),
			'call_direction' => array(
				'Start' => 135,
				'Length' => 1
			),	
			'completion_indicator' => array(
				'Start' => 136,
				'Length' => 1
			),	
			'termination_indicator' => array(
				'Start' => 137,
				'Length' => 1
			),
			'latitude' => array(
				'Start' => 138,
				'Length' => 8
			),
			'longitude' => array(
				'Start' => 146,
				'Length' => 8
			),
			'air_time_rate_period' => array(
				'Start' => 154,
				'Length' => 1
			),
			'air_time_charges' => array(
				'Start' => 155,
				'Length' => 11
			),
			'surcharges' => array(
				'Start' => 166,
				'Length' => 11
			),
			'action_code_1' => array(
				'Start' => 177,
				'Length' => 1
			),
			'supplimentary_service_code_1' => array(
				'Start' => 178,
				'Length' => 2
			),
			'action_code_2' => array(
				'Start' => 180,
				'Length' => 1
			),
			'supplimentary_service_code_2' => array(
				'Start' => 181,
				'Length' => 2
			),
			'action_code_3' => array(
				'Start' => 183,
				'Length' => 1
			),
			'supplimentary_service_code_3' => array(
				'Start' => 184,
				'Length' => 2
			),
			'action_code_4' => array(
				'Start' => 186,
				'Length' => 1
			),
			'supplimentary_service_code_4' => array(
				'Start' => 187,
				'Length' => 2
			),
			'action_code_5' => array(
				'Start' => 189,
				'Length' => 1
			),
			'supplimentary_service_code_5' => array(
				'Start' => 190,
				'Length' => 2
			),
			'called_place' => array(
				'Start' => 192,
				'Length' => 15
			),	
			'calling_place' => array(
				'Start' => 207,
				'Length' => 15
			),	
			'zone_indicator' => array(
				'Start' => 222,
				'Length' => 1
			),
			'toll_rate_period' => array(
				'Start' => 223,
				'Length' => 1
			),
			'toll_call_placed_date' => array(
				'Start' => 224,
				'Length' => 8
			),
			'toll_call_placed_time' => array(
				'Start' => 232,
				'Length' => 6
			),
			'toll_call_chargeable_time' => array(
				'Start' => 238,
				'Length' => 6
			),
			'toll_charges' => array(
				'Start' => 244,
				'Length' => 11
			),
			'distance' => array(
				'Start' => 255,
				'Length' => 7
			),
			'transactional_code' => array(
				'Start' => 262,
				'Length' => 3
			),
			'product_plan_code' => array(
				'Start' => 265,
				'Length' => 5
			),
			'bill_cycle' => array(
				'Start' => 270,
				'Length' => 4
			),
			'total_taxes' => array(
				'Start' => 274,
				'Length' => 11
			),
			'total_charges' => array(
				'Start' => 285,
				'Length' => 11
			),
			'call_type' => array(
				'Start' => 296,
				'Length' => 1
			),
			'gsm_service_type' => array(
				'Start' => 297,
				'Length' => 1
			),	
			'gsm_service_code' => array(
				'Start' => 298,
				'Length' => 2
			),
			'filler_1' => array(
				'Start' => 300,
				'Length' => 15
			),
			'relaxed_rate_discount_flag' => array(
				'Start' => 315,
				'Length' => 2
			),
			'filler_2' => array(
				'Start' => 317,
				'Length' => 1
			),
			'exchange_rate' => array(
				'Start' => 318,
				'Length' => 10
			),
			'mark_up_charge' => array(
				'Start' => 328,
				'Length' => 11
			),
			'mark_up_percentage' => array(
				'Start' => 339,
				'Length' => 4
			),
			'type_of_number' => array(
				'Start' => 343,
				'Length' => 1
			),
			'numbering_plan' => array(
				'Start' => 344,
				'Length' => 1
			),
			'service_type' => array(
				'Start' => 345,
				'Length' => 1
			),
			'service_code' => array(
				'Start' => 346,
				'Length' => 2
			),
			'radio_channel_used' => array(
				'Start' => 348,
				'Length' => 1
			),
			'msc_id' => array(
				'Start' => 349,
				'Length' => 15
			),
			'location_area' => array(
				'Start' => 364,
				'Length' => 5
			),
			'cell_id' => array(
				'Start' => 369,
				'Length' => 5
			),
			'mobiles_at_class_mark' => array(
				'Start' => 374,
				'Length' => 1
			),
			'types_of_units' => array(
				'Start' => 375,
				'Length' => 1
			),
			'calling_number' => array(
				'Start' => 376,
				'Length' => 21
			),
			'data_volume' => array(
				'Start' => 397,
				'Length' => 6
			),
			'transparency_indicator' => array(
				'Start' => 403,
				'Length' => 1
			),
			'filler_3' => array(
				'Start' => 404,
				'Length' => 12
			)
		),

		// Message Record
		//-------------------------------------------------------------------//
		self::RECORD_TYPE_MESSAGEEVENT => array(
			'record_type' => array(
				'Start' => 0,
				'Length' => 2
			),
			'cellular_number' => array(
				'Start' => 2,
				'Length' => 18
			),
			'imsi_number' => array(
				'Start' => 20,
				'Length' => 15
			),
			'imei' => array(
				'Start' => 35,
				'Length' => 15
			),
			'receiving_company_id' => array(
				'Start' => 50,
				'Length' => 8
			),
			'sending_company_id' => array(
				'Start' => 58,
				'Length' => 8
			),
			'serving_company_id' => array(
				'Start' => 66,
				'Length' => 8
			),
			'event_submission_date' => array(
				'Start' => 74,
				'Length' => 8
			),
			'event_submission_time' => array(
				'Start' => 82,
				'Length' => 6
			),
			'event_termination_date' => array(
				'Start' => 88,
				'Length' => 8
			),
			'event_termination_time' => array(
				'Start' => 96,
				'Length' => 6
			),
			'elapsed_time' => array(
				'Start' => 102,
				'Length' => 6
			),
			'chargeable_time' => array(
				'Start' => 108,
				'Length' => 6
			),
			'chargeable_units' => array(
				'Start' => 114,
				'Length' => 6
			),
			'rated_date' => array(
				'Start' => 120,
				'Length' => 8
			),
			'calling_number_plan_indicator' => array(
				'Start' => 128,
				'Length' => 6
			),	
			'called_number' => array(
				'Start' => 134,
				'Length' => 21
			),
			'direction' => array(
				'Start' => 155,
				'Length' => 1
			),
			'transaction_code' => array(
				'Start' => 156,
				'Length' => 4
			),
			'event_description' => array(
				'Start' => 160,
				'Length' => 15
			),
			'charge_period' => array(
				'Start' => 175,
				'Length' => 1
			),
			'bill_cycle' => array(
				'Start' => 176,
				'Length' => 4
			),
			'surcharges' => array(
				'Start' => 180,
				'Length' => 11
			),
			'toll_charges' => array(
				'Start' => 191,
				'Length' => 11
			),
			'event_charges' => array(
				'Start' => 202,
				'Length' => 11
			),
			'total_charges' => array(
				'Start' => 213,
				'Length' => 11
			),
			'event_type' => array(
				'Start' => 224,
				'Length' => 2
			),
			'event_size' => array(
				'Start' => 226,
				'Length' => 6
			),
			'priority_flag' => array(
				'Start' => 232,
				'Length' => 1
			),		
			'deferred_flag' => array(
				'Start' => 233,
				'Length' => 1
			),		
			'receipt_flag' => array(
				'Start' => 234,
				'Length' => 1
			),		
			'product_plan_code' => array(
				'Start' => 235,
				'Length' => 5
			),
			'event_service' => array(
				'Start' => 240,
				'Length' => 5
			),
			'sms relaxed_rule' => array(
				'Start' => 245,
				'Length' => 11
			),
			'filler' => array(
				'Start' => 256,
				'Length' => 17
			)
		),

		// Content Record
		//-------------------------------------------------------------------//
		self::RECORD_TYPE_CONTENTRECORD => array(
			'record_type' => array(
				'Start' => 0,
				'Length' => 2
			),
			'cellular_number' => array(
				'Start' => 2,
				'Length' => 18
			),
			'imsi_number' => array(
				'Start' => 20,
				'Length' => 15
			),
			'imei' => array(
				'Start' => 35,
				'Length' => 15
			),
			'receiving_company_id' => array(
				'Start' => 50,
				'Length' => 8
			),
			'sending_company_id' => array(
				'Start' => 58,
				'Length' => 8
			),
			'serving_company_id' => array(
				'Start' => 66,
				'Length' => 8
			),
			'request_date' => array(
				'Start' => 74,
				'Length' => 8
			),
			'request_time' => array(
				'Start' => 82,
				'Length' => 6
			),
			'source_application_id' => array(
				'Start' => 88,
				'Length' => 2
			),
			'source_reference' => array(
				'Start' => 90,
				'Length' => 8
			),
			'content_transaction_id' => array(
				'Start' => 98,
				'Length' => 15
			),
			'filler' => array(
				'Start' => 113,
				'Length' => 22
			),
			'provider_id' => array(
				'Start' => 135,
				'Length' => 15
			),
			'provider_name' => array(
				'Start' => 150,
				'Length' => 24
			),
			'content_id' => array(
				'Start' => 174,
				'Length' => 15
			),
			'content_category' => array(
				'Start' => 189,
				'Length' => 2
			),
			'category_type' => array(
				'Start' => 191,
				'Length' => 2
			),
			'content_description' => array(
				'Start' => 193,
				'Length' => 24
			),
			'content_charge_type' => array(
				'Start' => 217,
				'Length' => 2
			),
			'content_charge_reason' => array(
				'Start' => 219,
				'Length' => 2
			),
			'content_delivery_method' => array(
				'Start' => 221,
				'Length' => 2
			),
			'content_delivery_status' => array(
				'Start' => 223,
				'Length' => 1
			),	
			'total_charges' => array(
				'Start' => 224,
				'Length' => 11
			),
			'charge_sign' => array(
				'Start' => 235,
				'Length' => 2
			),
			'tax_amount' => array(
				'Start' => 237,
				'Length' => 11
			),
			'tax_sign' => array(
				'Start' => 248,
				'Length' => 2
			),
			'tax_flag' => array(
				'Start' => 250,
				'Length' => 1
			),
			'elapsed_time' => array(
				'Start' => 251,
				'Length' => 6
			),
			'filler_1' => array(
				'Start' => 257,
				'Length' => 5
			),
			'filler_2' => array(
				'Start' => 262,
				'Length' => 3
			),
			'filler_3' => array(
				'Start' => 265,
				'Length' => 15
			),
			'url' => array(
				'Start' => 280,
				'Length' => 50
			),
			'filler_4' => array(
				'Start' => 330,
				'Length' => 50
			)
		),

		// Data Records
		//-------------------------------------------------------------------//
		self::RECORD_TYPE_DATARECORD => array(
			'record_type' => array(
				'Start' => 0,
				'Length' => 2
			),
			'cellular_number' => array(
				'Start' => 2,
				'Length' => 18
			),
			'imsi_number' => array(
				'Start' => 20,
				'Length' => 15
			),
			'receiving_company_id' => array(
				'Start' => 35,
				'Length' => 8
			),
			'sending_company_id' => array(
				'Start' => 43,
				'Length' => 8
			),
			'serving_company_id' => array(
				'Start' => 51,
				'Length' => 8
			),
			'call_placed_date' => array(
				'Start' => 59,
				'Length' => 8
			),
			'call_placed_time' => array(
				'Start' => 67,
				'Length' => 6
			),
			'rated_date' => array(
				'Start' => 73,
				'Length' => 8
			),
			'called_number' => array(
				'Start' => 81,
				'Length' => 21
			),
			'direction_flag' => array(
				'Start' => 102,
				'Length' => 1
			),
			'completion_indicator' => array(
				'Start' => 103,
				'Length' => 1
			),
			'termination_indicator' => array(
				'Start' => 104,
				'Length' => 1
			),
			'air_time_charges' => array(
				'Start' => 105,
				'Length' => 11
			),
			'called_place' => array(
				'Start' => 116,
				'Length' => 12
			),
			'calling_placed' => array(
				'Start' => 128,
				'Length' => 12
			),
			'toll_charges' => array(
				'Start' => 140,
				'Length' => 11
			),
			'product_plan_code' => array(
				'Start' => 151,
				'Length' => 10
			),
			'bill_cycle' => array(
				'Start' => 161,
				'Length' => 4
			),
			'total_charges' => array(
				'Start' => 165,
				'Length' => 11
			),
			'call_type' => array(
				'Start' => 176,
				'Length' => 1
			),
			'gsm_service_type' => array(
				'Start' => 177,
				'Length' => 1
			),
			'gsm_service_code' => array(
				'Start' => 178,
				'Length' => 2
			),
			'msc_id' => array(
				'Start' => 180,
				'Length' => 15
			),
			'relaxed_rate_discnt_flag' => array(
				'Start' => 195,
				'Length' => 2
			),
			'filler' => array(
				'Start' => 197,
				'Length' => 8
			),
			'usage_identifier' => array(
				'Start' => 205,
				'Length' => 12
			),
			'data_charging_method' => array(
				'Start' => 217,
				'Length' => 1
			),
			'relaxed_rule_number' => array(
				'Start' => 218,
				'Length' => 8
			),
			'network_type' => array(
				'Start' => 226,
				'Length' => 2
			),
			'call_end_date' => array(
				'Start' => 228,
				'Length' => 8
			),
			'call_end_time' => array(
				'Start' => 236,
				'Length' => 6
			),
			'peak_usage' => array(
				'Start' => 242,
				'Length' => 9
			),
			'off_peak_usage' => array(
				'Start' => 251,
				'Length' => 9
			),
			'other_usage' => array(
				'Start' => 260,
				'Length' => 9
			),
			'peak_charges' => array(
				'Start' => 269,
				'Length' => 11
			),		
			'off_peak_charges' => array(
				'Start' => 280,
				'Length' => 11
			),		
			'other_charges' => array(
				'Start' => 291,
				'Length' => 11
			),		
			'flag_fall_charges' => array(
				'Start' => 302,
				'Length' => 11
			),		
			'charge_override' => array(
				'Start' => 313,
				'Length' => 86
			),			
			'quality_of_service' => array(
				'Start' => 399,
				'Length' => 4
			),
			'service_class' => array(
				'Start' => 403,
				'Length' => 2
			),		
			'filler_1' => array(
				'Start' => 405,
				'Length' => 10
			)
		)
	);
}
