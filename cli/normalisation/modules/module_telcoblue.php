<?php

class NormalisationModuleTelcoblue extends NormalisationModule {
	const DEBUG_LOGGING = true;

	public $intBaseFileType	= RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE;

	public function __construct($iCarrier) {
		// Extra Module Config - Must be defined prior to calling the super constructor
		$this->_arrModuleConfig['DisplayTypeCarrierTranslationContextId'] = array(
			'Type' => DATA_TYPE_INTEGER,
			'Description' => "Display Type Carrier Translation Context Id - Translates wholesale unit types to display type constant values"
		);

		parent::__construct($iCarrier);

		// define the column delimiter
		$this->_strDelimiter = ",";
		
		// define row start (account for header rows)
		$this->_intStartRow = 0;
		
		$this->_iSequence = 0;
		
		// define the carrier CDR format
		$this->_arrDefineCarrier = array(
			'reference' => array('Index' => 0, 'Start' => null, 'Length' => null),
			'type' => array('Index' => 1, 'Start' => null, 'Length' => null),
			'origin_identifier' => array('Index' => 2, 'Start' => null, 'Length' => null),
			'target_identifier' => array('Index' => 3, 'Start' => null, 'Length' => null),
			'billed_identifier' => array('Index' => 4, 'Start' => null, 'Length' => null),
			'origin_point' => array('Index' => 5, 'Start' => null, 'Length' => null),
			'origin_point_name' => array('Index' => 6, 'Start' => null, 'Length' => null),
			'target_point' => array('Index' => 7, 'Start' => null, 'Length' => null),
			'target_point_name' => array('Index' => 8, 'Start' => null, 'Length' => null),
			'billed_point' => array('Index' => 9, 'Start' => null, 'Length' => null),
			'billed_point_name' => array('Index' => 10, 'Start' => null, 'Length' => null),
			'units_primary' => array('Index' => 11, 'Start' => null, 'Length' => null),
			'unit_type_primary' => array('Index' => 12, 'Start' => null, 'Length' => null),
			'units_secondary' => array('Index' => 13, 'Start' => null, 'Length' => null),
			'unit_type_secondary' => array('Index' => 14, 'Start' => null, 'Length' => null),
			'units_tertiary' => array('Index' => 15, 'Start' => null, 'Length' => null),
			'unit_type_tertiary' => array('Index' => 16, 'Start' => null, 'Length' => null),
			'cost' => array('Index' => 17, 'Start' => null, 'Length' => null),
			'event_start' => array('Index' => 18, 'Start' => null, 'Length' => null),
			'event_end' => array('Index' => 19, 'Start' => null, 'Length' => null),
			'rate' => array('Index' => 20, 'Start' => null, 'Length' => null),
			'description' => array('Index' => 21, 'Start' => null, 'Length' => null)
		);
	}

	public function Normalise($aCDR) {
		$oLog = Log::get();
		$oLog->logIf(self::DEBUG_LOGGING, "[*] Normalising Telcoblue Usage data: ".var_export($aCDR, true));

		// set up CDR
		$this->_NewCDR($aCDR);
		
		// SequenceNo
		$this->setNormalised('SequenceNo', $this->_iSequence++);

		// Detail Record
		$this->_SplitRawCDR($aCDR['CDR']);
		$this->_normalise();
		
		//Debug($this->_arrNormalisedData);
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}

	private function _normalise() {
		// CarrierRef
		$this->setNormalised('CarrierRef', trim($this->getRaw('reference')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('CarrierRef', 'reference'));

		// FNN
		$sFNN = trim($this->getRaw('billed_identifier'));
		$this->setNormalised('FNN', $sFNN);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('FNN', 'billed_identifier'));
		
		// Source
		$this->setNormalised('Source', trim($this->getRaw('origin_identifier')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Source', 'origin_identifier'));
	
		// Destination
		$this->setNormalised('Destination', trim($this->getRaw('target_identifier')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Destination', 'target_identifier'));
		
		// Cost
		$fCost = (float)trim($this->getRaw('cost'));
		$this->setNormalised('Cost', $fCost);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Cost', 'cost'));
		
		// ServiceType
		$iServiceType = self::_getServiceTypeForFNN($sFNN);
		$this->setNormalised('ServiceType', $iServiceType);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('ServiceType', 'billed_identifier'));
	
		// RecordType (Call Type Group)
		$sUsageType = trim($this->getRaw('type'));
		$iRecordType = $this->translateRecordType($iServiceType, $sUsageType);
		$this->setNormalised('RecordType', $iRecordType);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('RecordType', 'type'));
		
		// DestinationCode (sub-Call Type)
		if ($this->_intContext > 0) {
			// Only resolve Destination if there is a Destination Context
			$this->setNormalised('DestinationCode', $this->_translateDestination($sUsageType));
		}
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('DestinationCode', 'type'));
		
		// Description
		$this->setNormalised('Description', trim($this->getRaw('description')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Description', 'description'));
		
		// Units
		$this->setNormalised('Units', $this->_getUnits($iRecordType));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Units', 'units_primary'));
		
		// StartDatetime
		$this->setNormalised('StartDatetime', trim($this->getRaw('event_start')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('StartDatetime', 'event_start'));
		
		// EndDatetime
		$this->setNormalised('EndDatetime', trim($this->getRaw('event_end')));
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('EndDatetime', 'event_end'));
		
		// Credit
		$this->setNormalised('Credit', ($fCost < 0) ? 1 : 0);
		Log::get()->logIf(self::DEBUG_LOGGING, '  '.$this->_describeNormalisedField('Credit', 'cost'));
	}

	private function _getUnits($iRecordType) {
		$iCarrierTranslationContextId = $this->GetConfigField('DisplayTypeCarrierTranslationContextId');
		Flex::assert($iCarrierTranslationContextId !== null, "No Display Type Carrier Translation Context defined for Module {$this->_arrCarrierModule['Id']}:{$this->_arrCarrierModule['description']} (".get_class($this).")");
		$aTranslation = Query::run("SELECT	ct.in_value AS wholesale_unit_type
									FROM	carrier_translation ct
									JOIN	RecordType rt ON (
												CAST(rt.DisplayType AS CHAR) = ct.out_value
												AND rt.id = <record_type_id>
											)
									WHERE	ct.carrier_translation_context_id = <carrier_translation_context_id>;",
									array(
										'carrier_translation_context_id' => $iCarrierTranslationContextId,
										'record_type_id' => $iRecordType
									))->fetch_assoc();
		$sWholesaleUnitType = $aTranslation['wholesale_unit_type'];
		$mPrimaryUnitType = trim($this->getRaw('unit_type_primary'));
		$mSecondaryUnitType = trim($this->getRaw('unit_type_secondary'));
		$mTertiaryUnitType = trim($this->getRaw('unit_type_tertiary'));
		if ($mPrimaryUnitType == $sWholesaleUnitType) {
			// Use the primary units
			return (int)trim($this->getRaw('units_primary'));
		} else if ($mPrimaryUnitType == $sWholesaleUnitType) {
			// Use the secondary units
			return (int)trim($this->getRaw('units_secondary'));
		} else if ($mTertiaryUnitType == $sWholesaleUnitType) {
			// Use the tertiary units
			return (int)trim($this->getRaw('units_tertiary'));
		} else {
			// No compatible units in the usage record
			return null;
		}
	}

	private function _translateDestination($sUsageType) {
		// Look for a translation using the just the usage type
		$aDestination = $this->_findDestination($sUsageType, true, true);
		if ($aDestination === false) {
			// Couldn't find the desination code using only the usage type, try with the usage type, origin and target points

			// Ensure carrier translation context
			$iCarrierTranslationContextId = $this->GetConfigField('CallTypeCarrierTranslationContextId');
			Flex::assert($iCarrierTranslationContextId !== null, "No Call Type (Destination) Carrier Translation Context defined for Module {$this->_arrCarrierModule['Id']}:{$this->_arrCarrierModule['description']} (".get_class($this).")");

			// Build the 'in value' to be compared against
			$aData = array(
				'usage_type' => $sUsageType,
				'origin' => trim($this->getRaw('origin_point')),
				'target' => trim($this->getRaw('target_point'))
			);

			$mResult = Query::run("	SELECT	ct.in_value, ct.out_value
									FROM	carrier_translation ct
									WHERE	ct.carrier_translation_context_id = <carrier_translation_context_id>;",
									array('carrier_translation_context_id' => $iCarrierTranslationContextId));
			$sDestinationCode = null;
			while ($aRow = $mResult->fetch_assoc()) {
				$aInValue = (array)JSON_Services::decode($aRow['in_value']);
				$bMatch = true;
				foreach ($aData as $sProperty => $mValue) {
					if (!isset($aInValue[$sProperty])) {
						// This contstraint does not specify this property, all values match
					} else if ($aInValue[$sProperty] === null) {
						// This contstraint does not specify this property, all values match
					} else if ($aInValue[$sProperty] == $mValue) {
						// This contstraint does specify this property and the data value matches
					} else {
						// Specified and the value the doesn't match
						$bMatch = false;
						break;
					}
				}

				if ($bMatch) {
					$sDestinationCode = $aRow['out_value'];
					break;
				}
			}

			if ($sDestinationCode === null) {
				// No matching destination code found. Look for the default destination code for the destination context associated with the record type
				if ($aUnknownDestination = $this->_getUnknownDestination()) {
					$sDestinationCode = $aUnknownDestination['Code'];
				} else {
					throw new Exception("No Default Destination found for Context {$this->_intContext}!");
				}
			}

			// There was a match, return it
			return $sDestinationCode;
		}
	}
}

?>