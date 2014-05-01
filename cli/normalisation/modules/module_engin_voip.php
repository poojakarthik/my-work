<?php
class NormalisationModuleEnginVOIP extends NormalisationModule {
	const DEBUG_LOGGING = true;

	function __construct($iCarrier) {
		$this->_arrModuleConfig['service_type_id'] = array(
			'Type' => DATA_TYPE_INTEGER,
			'Description' => "Service Type `service_type.id` associated with this normaliser"
		);

		parent::__construct($iCarrier);

		// Ensure Service Type reference is valid
		try {
			$oServiceType = Service_Type::getForId($this->GetConfigField('service_type_id'));
		} catch (Exception_ORM_LoadById $oException) {
			throw new Exception('Configured Service Type is not valid');
		}

		// TODO?: Ensure $oServiceType->module is engin_voip

		// define the column delimiter
		$this->_strDelimiter = ',';

		// define row start (account for header rows)
		$this->_intStartRow = 0;

		$this->_iSequence = 0;

		// define the carrier CDR format
		$this->_arrDefineCarrier = array(
			'call_id' => array('Index' => 0),
			'calling_number' => array('Index' => 1),
			'called_number' => array('Index' => 2),
			'country' => array('Index' => 3),
			'start_datetime' => array('Index' => 4),
			'end_datetime' => array('Index' => 5),
			'duration_seconds' => array('Index' => 6),
			'call_type' => array('Index' => 7),
			'call_type_description' => array('Index' => 8),
			'plan_name' => array('Index' => 9),
			'charge_rate' => array('Index' => 10),
			'charge_type' => array('Index' => 11),
			'call_charge' => array('Index' => 12)
		);
	}

	const CALL_TYPE_INTERNATIONAL = 3;

	public function Normalise($aCDR) {
		// set up CDR
		$this->_NewCDR($aCDR);

		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);

		$this->_SplitRawCDR($aCDR['CDR']);

		//--------------------------------------------------------------------//
		Log::get()->logIf(self::DEBUG_LOGGING, "Record #{$this->_iSequence}");
		$this->_SplitRawCDR($aCDR['CDR']);
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
		$this->setNormalised('CarrierRef', trim($this->getRaw('call_id')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('CarrierRef', 'call_id'));

		// FNN
		// NOTE: This is a best guess. There is no explicit "charged number" field
		$sCallingNumber = trim($this->getRaw('calling_number'));
		$this->setNormalised('FNN', $sCallingNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'calling_number'));

		// Source
		$this->setNormalised('Source', $sCallingNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'calling_number'));

		// Destination
		$sCalledNumber = trim($this->getRaw('called_number'));
		if (preg_match('/^61/', $sCalledNumber)) {
			// Remove 61 prefix for Australian destinations for fleet matching
			$sCalledNumber = preg_replace('/^61(.+)$/', '0$1', $sCalledNumber);
		}
		$this->setNormalised('Destination', $sCalledNumber);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'called_number'));

		// Cost
		$fCallCharge = ((int)$this->getRaw('call_charge')) / 100; // call_charge is in cents
		$this->setNormalised('Cost', $fCallCharge);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'call_charge'));

		// ServiceType
		$iServiceType = $this->GetConfigField('service_type_id');
		$this->setNormalised('ServiceType', $iServiceType);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('ServiceType', 'calling_number'));

		// RecordType (Call Type Group)
		$iCallType = (int)$this->getRaw('call_type');
		$this->setNormalised('RecordType', $this->translateRecordType($iServiceType, (string)$iCallType));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'call_type', 'call_type_description'));

		// Destination (sub-Call Type)
		if ($iCallType === self::CALL_TYPE_INTERNATIONAL && $this->_intContext > 0) { // Only resolve Destination if there is a Destination Context & International
			$aDestination = $this->translateDestination(trim($this->getRaw('country')));
			$this->setNormalised('DestinationCode', $aDestination['Code']);
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode', 'call_type', 'call_type_description', 'country'));
		}

		// Description
		// NOTE: Only useful description is Country in the case of International
		if ($iCallType === self::CALL_TYPE_INTERNATIONAL) {
			$this->setNormalised('Description', trim($this->getRaw('country')));
			Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'call_type', 'call_type_description', 'country'));
		}

		// Units
		$iDuration = (int)$this->getRaw('duration_seconds');
		$this->setNormalised('Units', $iDuration);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'duration_seconds'));

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