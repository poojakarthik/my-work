<?php
class NormalisationModuleEnginVOIP extends NormalisationModule {
	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_ENGIN_WHOLESALECDR;
	const DEBUG_LOGGING = true;

	function __construct($iCarrier) {
		$this->_arrModuleConfig['service_type_id'] = array(
			'Type' => DATA_TYPE_INTEGER,
			'Description' => "Service Type that represents this Engin VOIP wholesale account"
		);
		$this->_arrModuleConfig['call_type_carrier_translation_context_id'] = array(
			'Type' => DATA_TYPE_INTEGER,
			'Description' => "Translation Set used to translate Utilibill Call Type to Flex Service Type, Record Type, and Destination"
		);

		parent::__construct($iCarrier);

		// Verify linked Service Type
		if (
			!$this->GetConfigField('service_type_id') ||
			(false === ($this->_oServiceType = Service_Type::getForId($this->GetConfigField('service_type_id'), false)))
		) {
			// Flex::assert(false, 'Engin Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Service Type');
			throw new Exception('Engin Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Service Type');
		}

		// Verify linked Carrier Translation Context
		if (
			(!$this->GetConfigField('call_type_carrier_translation_context_id')) ||
			(false === Carrier_Translation_Context::getForId($this->GetConfigField('call_type_carrier_translation_context_id'), false))
		) {
			// Flex::assert(false, 'Engin Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Carrier Translation Context for translating Call Types');
			throw new Exception('Engin Usage Normalisation Module #' . $this->_arrCarrierModule['Id'] . ' is missing, or has an invalid, Carrier Translation Context for translating Call Types');
		}

		$this->_iSequence = 0;
	}

	const CALL_TYPE_INTERNATIONAL = 3;

	public function Normalise($aCDR) {
		// set up CDR
		$this->_NewCDR($aCDR);

		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);

		//--------------------------------------------------------------------//
		Log::get()->logIf(self::DEBUG_LOGGING, "Record #{$this->_iSequence}");
		$aParsed = File_CSV::parseLineRFC4180($aCDR['CDR']);
		$this->_arrRawData = array_associate(array(
			0 => 'call_id',
			1 => 'calling_number',
			2 => 'called_number',
			3 => 'country',
			4 => 'start_datetime',
			5 => 'end_datetime',
			6 => 'call_duration',
			7 => 'call_type',
			8 => 'call_type_description',
			9 => 'plan_name',
			10 => 'charge_rate',
			10 => 'charge_type',
			10 => 'call_charge'
		), $aParsed);
		Log::get()->log(print_r($this->_arrRawData, true));
		$this->_normalise();
		//--------------------------------------------------------------------//

		//Debug($this->_arrNormalisedData);

		// Apply Ownership
		$this->ApplyOwnership();

		// Validation of Normalised data
		$this->Validate();

		throw new Exception("TESTING");

		// return output array
		return $this->_OutputCDR();
	}

	// Usage Records
	private function _normalise() {
		$oCallTypeTranslation = $this->_translateCallType((object)array(
			'call_type' => intval(trim($this->getRaw('call_type'))),
			'country' => trim($this->getRaw('country'))
		));

		// CarrierRef
		$this->setNormalised('CarrierRef', trim($this->getRaw('call_id')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('CarrierRef', 'call_id'));

		// FNN
		// NOTE: This is a best guess. There is no explicit "charged number" field
		$sCallingNumber = trim($this->getRaw('calling_number'));
		$this->setNormalised('FNN', $sCallingNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'calling_number', 'called_number'));

		// Source
		$this->setNormalised('Source', $sCallingNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'calling_number'));

		// Destination
		$sCalledNumber = trim($this->getRaw('called_number'));
		$this->setNormalised('Destination', self::_localiseAustralianNumber($sCalledNumber));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'called_number'));

		// Cost
		$fCallCharge = ((float)$this->getRaw('call_charge')) / 100; // call_charge is in cents
		$this->setNormalised('Cost', $fCallCharge);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'call_charge'));

		// ServiceType
		$this->setNormalised('ServiceType', $this->_oServiceType->id);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('ServiceType', 'calling_number', 'call_type_description', 'plan_name'));

		// RecordType (Call Type Group)
		$oRecordType = Record_Type::getForServiceTypeAndCode($this->_oServiceType->id, (string)$oCallTypeTranslation->record_type_code);
		$this->setNormalised('RecordType', $oRecordType->Id);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'call_type', 'call_type_description'));

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
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode', 'call_type', 'call_type_description', 'country'));

		// Description
		// NOTE: Only useful description is Country in the case of International
		if (intval(trim($this->getRaw('call_type'))) === self::CALL_TYPE_INTERNATIONAL) {
			$this->setNormalised('Description', trim($this->getRaw('country')));
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'call_type', 'call_type_description', 'country'));
		}

		// Units
		$iDuration = (int)$this->getRaw('call_duration');
		$this->setNormalised('Units', $iDuration);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'call_duration'));

		// StartDatetime
		$this->setNormalised('StartDatetime', trim($this->getRaw('start_datetime')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'start_datetime'));

		// EndDatetime
		$this->setNormalised('EndDatetime', trim($this->getRaw('end_datetime')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'end_datetime'));

		// Credit
		// NOTE: There doesn't appear to be a way to mark credit records
		$this->setNormalised('Credit', 0);

		// Potential methods of detecting Credits
		if ($fCallCharge < 0) throw new Exception('Call Charge is less than $0.00!');
		if ($iDuration < 0) throw new Exception('Duration is less than 0 seconds!');

		// throw new Exception("TESTING");
	}

	/*
		call_type => Service Type, Record Type, and Destination mapping:

		in_value should be in the form:
		{
			call_type: 123,
			country: '456' (nullable)
		}

		out_value should be in the form:
		{
			record_type_code: '456',
			destination_code: 789 (nullable)
		}
	*/

	private $_aTranslations;
	private function _translateCallType(stdClass $oCallTypeDetails) {
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

		// Find the best translation
		$oFallbackTranslation = null;
		$oBestTranslation = null;
		foreach ($this->_aTranslations as $oTranslation) {
			$oInValue = @json_decode($oTranslation->in_value);
			if (false === $oInValue || !is_object($oInValue)) {
				throw new Exception(
					'Translation Set #' .
					$this->GetConfigField('call_type_carrier_translation_context_id') . ': ' . Carrier_Translation_Context::getForId($this->GetConfigField('call_type_carrier_translation_context_id'))->name .
					' "in" value (#' . $oTranslation->carrier_translation_context_id .') should be a JSON object: ' . var_export($oTranslation->in_value, true)
				);
			}
			// Log::get()->log(print_r($oInValue, true));

			// Attempt to match
			if (property_exists($oInValue, 'call_type') && $oCallTypeDetails->call_type === $oInValue->call_type) {
				// We only care about country matches if call_type also matches
				if (property_exists($oInValue, 'country') && trim($oCallTypeDetails->country) !== trim($oInValue->country)) {
					// We can't get a better match, so break out
					// Log::get()->formatLog('Perfect Translation: %s', var_export((array)$oTranslation, true));
					$oBestTranslation = $oTranslation;
					break;
				} elseif (!isset($oFallbackTranslation)) {
					// Log::get()->formatLog('Fallback Translation: %s', var_export((array)$oTranslation, true));
					$oFallbackTranslation = $oTranslation;
				}
			}
		}

		// If we don't have a perfect match, use a fallback (if one was found)
		if (!$oBestTranslation) {
			if (!$oFallbackTranslation) {
				throw new Exception(
					'Translation Set #' .
					$this->GetConfigField('call_type_carrier_translation_context_id') . ': ' . Carrier_Translation_Context::getForId($this->GetConfigField('call_type_carrier_translation_context_id'))->name .
					' is missing a translation for: ' . var_export((array)$oCallTypeDetails, true)
				);
			}
			$oBestTranslation = $oFallbackTranslation;
		}

		$oTranslationValue = @json_decode($oBestTranslation->out_value);
		if (false === $oTranslationValue) {
			throw new Exception(
				'Translation Set #' .
				$this->GetConfigField('call_type_carrier_translation_context_id') . ': ' . Carrier_Translation_Context::getForId($this->GetConfigField('call_type_carrier_translation_context_id'))->name .
				' result for ' . var_export($sCallType, true) . ' (#' . $oBestTranslation->carrier_translation_context_id .') should be a JSON object (Error: ' . $php_errormsg . '): ' . var_export($oBestTranslation->out_value, true)
			);
		}
		return $oTranslationValue;
	}

	private static function _localiseAustralianNumber($sNumber) {
		if (preg_match('/^((00)?11)?61/', $sNumber)) {
			// Remove 61 prefix for Australian destinations for fleet matching
			return preg_replace('/^(?:(?:00)?11)?61(.+)$/', '0$1', $sNumber);
		}
		return $sNumber;
	}

	/* SUGGESTED RECORD TYPES

		INSERT INTO RecordType
			(Code, Name, Description, ServiceType, Context, Required, Itemised, GroupId, DisplayType, global_tax_exempt)
		VALUES
			('Local', 'Local', 'Local Calls', SERVICE_TYPE, 0, 1, 1, 0, 1, 0),
			('National', 'National', 'National Calls', SERVICE_TYPE, 0, 1, 1, 0, 1, 0),
			('IDD', 'International', 'International Direct Dial', SERVICE_TYPE, 0, 1, 1, 0, 1, 0),
			('Mobile', 'Mobile', 'Calls to Mobile', SERVICE_TYPE, 0, 1, 1, 0, 1, 0),
			('FreeCall', 'Free Call', 'Free Calls', SERVICE_TYPE, 0, 1, 1, 0, 1, 0),
			('Engin', 'Engin to Engin', 'Engin to Engin Calls', SERVICE_TYPE, 0, 1, 1, 0, 1, 0),
			('Service', 'Service', 'Service', SERVICE_TYPE, 0, 1, 1, 0, 2, 0)
		;
		UPDATE RecordType
		SET GroupId = Id
		WHERE ServiceType = 110;
	*/
}