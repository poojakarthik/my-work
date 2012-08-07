<?php
abstract class NormalisationModule extends CarrierModule {
	protected $_intContext; // Destination Context
	protected $_arrRawData;
	protected $_arrNormalisedData;
	protected $_rptNormalisationReport;
	protected $_strDelimiter;
	protected $_intStartRow;
	protected $_arrDefineCarrier;
	protected $_arrDefineOutput;
	protected $_errErrorHandler;
	protected $_selFindOwner;
	protected $_selFindOwnerIndial100;
	
	public $strFNN;
	public $_intCarrier;
	
	function __construct($intCarrier) {
		$this->_arrModuleConfig['CallGroupCarrierTranslationContextId'] = array(
			'Type' => DATA_TYPE_INTEGER,
			'Description' => "Call Group (Record Type) Carrier Translation Context Id"
		);
		$this->_arrModuleConfig['CallTypeCarrierTranslationContextId'] = array(
			'Type' => DATA_TYPE_INTEGER,
			'Description' => "Call Type (Destination) Carrier Translation Context Id"
		);

 		// Call CarrierModule Constructor
 		parent::__construct($intCarrier, MODULE_TYPE_NORMALISATION_CDR);

		//$->_selFindOwner 			= new StatementSelect("Service", "AccountGroup, Account, Id", "FNN = <fnn> AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
		//$this->_selFindOwnerIndial100	= new StatementSelect("Service", "AccountGroup, Account, Id", "(FNN LIKE <fnn>) AND (Indial100 = TRUE)AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
		$strAccountStatus = ACCOUNT_STATUS_ACTIVE.", ".ACCOUNT_STATUS_CLOSED.", ".ACCOUNT_STATUS_DEBT_COLLECTION.", ".ACCOUNT_STATUS_SUSPENDED;
		$strServiceStatus = SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED;
		$this->_selFindOwner = new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "FNN = <fnn> AND ((CAST(<date> AS DATE) BETWEEN Service.CreatedOn AND Service.ClosedOn AND Status = ".SERVICE_ARCHIVED.") OR Service.Status IN ($strServiceStatus)) AND Account.Archived IN ($strAccountStatus)", "(Service.ClosedOn IS NULL) DESC, Service.CreatedOn DESC, Account DESC", "1");
		$this->_selFindOwnerIndial100 = new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "FNN LIKE <fnn> AND (Indial100 = TRUE) AND ((CAST(<date> AS DATE) BETWEEN Service.CreatedOn AND Service.ClosedOn AND Service.Status = ".SERVICE_ARCHIVED.") OR Service.Status IN ($strServiceStatus)) AND Account.Archived IN ($strAccountStatus)", "(Service.ClosedOn IS NULL) DESC, Service.CreatedOn DESC, Account DESC", "1");
		
		$this->_selFindOwnerNow = new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "FNN = <fnn> AND Service.Status != ".SERVICE_ARCHIVED." AND Account.Archived IN ($strAccountStatus)", "(Service.Status IN ($strServiceStatus)) DESC, Service.ClosedOn DESC, Account DESC", "1");
		$this->_selFindOwnerNowIndial100 = new StatementSelect("Service JOIN Account ON Account.Id = Service.Account", "Service.*", "(FNN LIKE <fnn>) AND (Indial100 = TRUE) AND Service.Status != ".SERVICE_ARCHIVED." AND Account.Archived IN ($strAccountStatus)", "(Service.Status IN ($strServiceStatus)) DESC, Service.ClosedOn DESC, Account DESC", "1");
		
		$this->_selFindRecordType = new StatementSelect("RecordType", "Id, Context", "ServiceType = <ServiceType> AND Code = <Code>", "", "1");
		//$this->_selFindRecordCode = new StatementSelect("cdr_call_group_translation", "code", "carrier_id = <Carrier> AND carrier_code = <CarrierCode>", "", "1");
		
		/*$this->_selFindDestination = new StatementSelect(
			"Destination, cdr_call_type_translation",
			"Destination.Code AS Code, Destination.Description AS Description",
			"Destination.Code = cdr_call_type_translation.code AND cdr_call_type_translation.carrier_id = <Carrier> AND cdr_call_type_translation.carrier_code = <CarrierCode> AND Destination.Context = <Context>",
			null,
			1
		);*/
		$this->_sFindDestinationSQL = "
			SELECT		d.Code,
						d.Description
			FROM		carrier_translation ct
						JOIN Destination d ON (ct.out_value = d.Code)
			WHERE		ct.carrier_translation_context_id = <carrier_translation_context_id>
						AND ct.in_value = <in_value>
			LIMIT		1;
		";
		
		$this->_selGetCDR = new StatementSelect("CDR", "CDR.CDR AS CDR", "Id = <Id>");
		
		$this->_arrValid = array();
	}
	
	function Validate($bolAsArray=false) {
		// Validate our normalised data
		$arrValid = array();
		
		// DestinationCode : required for any record type with a context
		if ($this->_intContext > 0) {
			// requires a destination code
			if (!is_numeric($this->_arrNormalisedData["DestinationCode"])) {
				throw new Exception("No Destination Code @ Validation");
				$this->_UpdateStatus(CDR_BAD_DESTINATION);
				return false;
			}
		} else {
			// doesn't require a destination code
			$arrValid['DestinationCode'] = (!$this->_arrNormalisedData["DestinationCode"] || is_numeric($this->_arrNormalisedData["DestinationCode"]));	// 9
		}
		
		// FNN : valid FNN
		$arrValid['FNN'] = preg_match("/^0\d{9}[i]?|13\d{4}|1[89]00\d{6}$/", $this->_arrNormalisedData["FNN"]); // 1
		
		// CarrierRef : required (non empty)
		$arrValid['CarrierRef'] = ($this->_arrNormalisedData["CarrierRef"] != ""); // 2
		
		// source : empty or valid FNN
		if ($this->_arrNormalisedData["Source"] != "") { // 3
			$arrValid['Source'] = preg_match("/^\d+$|^\+\d+$|^\d{5}(X+|\d+| +|\d+REV)I?$/", $this->_arrNormalisedData["Source"]);
		} else {
			$arrValid['Source'] = true;
		}
		
		// destination : empty or valid FNN
		if ($this->_arrNormalisedData["Destination"] != "") { // 4
			$arrValid['Destination'] = preg_match("/^\d+$|^\+\d+$|^\d{5}(X+|\d+| +|\d+REV)I?$/", $this->_arrNormalisedData["Destination"]);
		} else {
			$arrValid['Destination'] = true;
		}
		
		// 5
		// start time : valid date/time
		$arrValid['StartDatetime'] = preg_match("/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/",	$this->_arrNormalisedData["StartDatetime"]);

		// end time : empty or valid date/time
		if ($this->_arrNormalisedData["EndDatetime"] != "") { // 6
			$arrValid['EndDatetime'] = preg_match("/^\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d$/", $this->_arrNormalisedData["EndDatetime"]);
		} else {
			$arrValid['EndDatetime'] = true;
		}
		
		// units : numeric
		$arrValid['Units'] = is_numeric($this->_arrNormalisedData["Units"]); // 7
		if (is_numeric($this->_arrNormalisedData["Units"]) && (int)$this->_arrNormalisedData["Units"] == 0) {
			// convert 0 units to 1 units
			$this->_arrNormalisedData["Units"] = 1;
		}
		
		// cost : numeric
		$arrValid['Cost'] = is_numeric($this->_arrNormalisedData["Cost"]); // 8
		
		$this->_arrValid = $arrValid;
		
		if ($bolAsArray) {
			return $arrValid;
		}
		
		$i = 0;
		foreach ($arrValid as $strKey=>$bolValid) {
			$i++;
			if(!$bolValid) {
				$this->_UpdateStatus(CDR_CANT_NORMALISE_INVALID);
				Debug($strKey." : '{$this->_arrNormalisedData[$strKey]}' : ".(string)$i);
				Debug($this->_arrNormalisedData);
				return false;
			}
		}
		
		return true;
	}

	abstract function Normalise($arrCDR);

	public static function RemoveAusCode($strFNN) {
		if (strpos($strFNN, '+61') === 0) {
			return '0'.substr($strFNN, 3);
		}
		elseif (strpos($strFNN, '61') === 0) {
			return '0'.substr($strFNN, 2);
		}
		elseif (strpos($strFNN, '001161') === 0) {
			return '0'.substr($strFNN, 6);
		}
		elseif (strpos($strFNN, '1161') === 0) {
			return '0'.substr($strFNN, 4);
		}
		return $strFNN;
	}
	
	protected function IsValidFNN($strFNN) {
		return IsValidFNN($strFNN);
	}
	
	protected function _SplitRawCDR($strCDR=null) {
		if ($strCDR === null) {
			$strCDR = $this->_arrNormalisedData['CDR'];
		}
		// keep a record of the raw CDR
		$this->_strRawCDR = $strCDR;
		
		//CliEcho("RAW CDR: '{$this->_strRawCDR}'");
		
		// clean the array
		$this->_arrRawData = array();
		
		// build the array
		if ($this->_strDelimiter) {
			// delimited record
			$arrRawData = explode($this->_strDelimiter, rtrim($strCDR, "\n"));
			foreach($this->_arrDefineCarrier as $strKey=>$aValue) {
				if (isset($arrRawData[$aValue['Index']])) {
					$this->_arrRawData[$strKey] = trim($arrRawData[$aValue['Index']]);
					// delimited fields may have fixed width contents
					if (isset($aValue['Start']) && isset($aValue['Length'])) {
						$this->_arrRawData[$strKey] = trim(substr($this->_arrRawData[$strKey], $aValue['Start'], $aValue['Length']));
					}
				} else {
					$this->_arrRawData[$strKey] = null;
				}
			}
		} else {
			// fixed width record
			foreach($this->_arrDefineCarrier as $strKey=>$aValue) {
				$this->_arrRawData[$strKey] = trim(substr($strCDR, $aValue['Start'], $aValue['Length']));
			}
		}
		
		//Debug($this->_arrDefineCarrier);
		//Debug($this->_arrRawData);
	}
	
	protected function _ValidateRawCDR() {
		if (is_array($this->_arrDefineCarrier)) {
			foreach($this->_arrDefineCarrier as $strKey=>$aValue) {
				if ($aValue['Validate']) {
					if (!preg_match($aValue['Validate'], $this->_arrRawData[$strKey])) {
						Debug("$strKey: '".$this->_arrRawData[$strKey]."' != '".$aValue['Validate']."'");
						return false;
					}
				}
			}
			return true;
		}
		// return false if there is no define array for the carrier (should never happen)
		return false;
	}
	
	protected function _FetchRawCDR($strKey) {
		return (isset($this->_arrRawData[$strKey]) ? $this->_arrRawData[$strKey] : null);
	}
	
	protected function _NewCDR($arrCDR) {
		// set CDR
		$this->_arrNormalisedData = $arrCDR;
		
		// Status = Normalised by default
		$this->_arrNormalisedData['Status'] = CDR_NORMALISED;
		
		// not a credit by default
		if (!$this->_arrNormalisedData['Credit']) {
			$this->_arrNormalisedData['Credit'] = 0;
		}
		
		// set Default Context
		$this->_intContext = 0;
	}
	
	protected function _AppendCDR($strKey, $mixValue) {
		$this->_arrNormalisedData[$strKey] = $mixValue;
	}
	
	protected function _UpdateStatus($intStatus) {
		// only set status if our current status is CDR_NORMALISED, CDR_FIND_OWNER, CDR_RENORMALISE or CDR_BAD_OWNER
		$intCurStatus = $this->_arrNormalisedData['Status'];
		if ($intCurStatus == CDR_NORMALISED || $intCurStatus == CDR_FIND_OWNER || $intCurStatus == CDR_BAD_OWNER || $intCurStatus == CDR_RENORMALISE) {
			// Set new status
			$this->_arrNormalisedData['Status'] = $intStatus;
			return true;
		} else {
			// can't set status
			return false;
		}
	}
	
	protected function _OutputCDR() {
		return $this->_arrNormalisedData;
	}
	
	protected function _ErrorCDR($intStatus) {
		$this->_arrNormalisedData['Status'] = $intStatus;
		return $this->_arrNormalisedData;
	}

	protected function ApplyOwnership($bolOwnerNow=false, $bolUpdateCDRStatus=true) {
		// Determine Timestamp to Use
		if ($bolOwnerNow) {
			// Use the current timestamp
			$strDate = date("Y-m-d");
		} else {
			// Use the CDR's StartDatetime
			$strDate = $this->_arrNormalisedData['StartDatetime'];
		}
		
		// Find the Owner
		if (is_array($mixResult = FindFNNOwner($this->_arrNormalisedData['FNN'], $strDate))) {
			// Found an Owner
			$this->_arrNormalisedData['AccountGroup'] = $mixResult['AccountGroup'];
			$this->_arrNormalisedData['Account'] = $mixResult['Account'];
			$this->_arrNormalisedData['Service'] = $mixResult['Service'];
			return true;
		}
		
		// Is there only one instance of this FNN?
		$arrFNNInstances = Service::getFNNInstances($this->_arrNormalisedData['FNN'], null, true);
		//CliEcho("There are ".count($arrFNNInstances)." instances of {$this->_arrNormalisedData['FNN']}");
		if (count($arrFNNInstances) === 1) {
			//CliEcho("Only one instance");
			
			// Yes, automatically assume that this is the correct Service
			$this->_arrNormalisedData['AccountGroup'] = $arrFNNInstances[0]['AccountGroup'];
			$this->_arrNormalisedData['Account'] = $arrFNNInstances[0]['Account'];
			$this->_arrNormalisedData['Service'] = $arrFNNInstances[0]['Id'];
			return true;
		}
		
		// Return false if there was no match, or more than one match
		if ($bolUpdateCDRStatus) {
			$this->_UpdateStatus(CDR_BAD_OWNER);
		}
		
		//Debug("Cannot match FNN: ".$this->_arrNormalisedData['FNN']);
		$this->strFNN = $this->_arrNormalisedData['FNN'];
		return false;
	}
	
	protected function ApplyOwnershipNow() {
		return $this->ApplyOwnership(true);
		/*
		$intResult = $this->_selFindOwnerNow->Execute(Array("fnn" => (string)$this->_arrNormalisedData['FNN']));
		if ($arrResult = $this->_selFindOwnerNow->Fetch()) {
			$this->_arrNormalisedData['AccountGroup'] = $arrResult['AccountGroup'];
			$this->_arrNormalisedData['Account'] = $arrResult['Account'];
			$this->_arrNormalisedData['Service'] = $arrResult['Id'];
			return true;
		} else {
			$arrParams['fnn'] = substr((string)$this->_arrNormalisedData['FNN'], 0, -2) . "__";
			$intResult = $this->_selFindOwnerNowIndial100->Execute($arrParams);
			if(($arrResult = $this->_selFindOwnerNowIndial100->Fetch())) {
				$this->_arrNormalisedData['AccountGroup'] = $arrResult['AccountGroup'];
				$this->_arrNormalisedData['Account'] = $arrResult['Account'];
				$this->_arrNormalisedData['Service'] = $arrResult['Id'];
				return true;
			}
		}
		
		// Return false if there was no match, or more than one match
		$this->_UpdateStatus(CDR_BAD_OWNER);
		//Debug("Cannot match FNN: ".$this->_arrNormalisedData['FNN']);
		$this->strFNN = $this->_arrNormalisedData['FNN'];
		return false;*/
	}
	
	public function FindOwner($arrCDR) {
		// set local copy of CDR
		$this->_arrNormalisedData = $arrCDR;
		
		// set context to 0
		$this->_intContext = 0;
		
		// default to status = normalised
		$this->_arrNormalisedData['Status']	= CDR_NORMALISED;
		
		// apply ownership
		if ($arrCDR['Status'] === CDR_NORMALISE_NOW) {
			// Find the current or most recent owner
			$this->ApplyOwnershipNow();
		} else {
			// Find the owner at the time the call was made
			$this->ApplyOwnership();
		}
		
		// validate
		$this->Validate();
		
		return $this->_arrNormalisedData;
	}
	
	protected function FindRecordCode($mixCarrierCode) {
		$iCarrierTranslationContextId = $this->GetConfigField('CallGroupCarrierTranslationContextId');
		Flex::assert($iCarrierTranslationContextId !== null, "No Call Group (Record Type) Carrier Translation Context defined for Module {$this->_arrCarrierModule['Id']}:{$this->_arrCarrierModule['description']} (".get_class($this).")");
		$mResult = Query::run("
			SELECT		ct.out_value AS code
			FROM		carrier_translation ct
			WHERE		ct.carrier_translation_context_id = <carrier_translation_context_id>
						AND ct.in_value = <in_value>
			LIMIT		1;
		", array(
			'carrier_translation_context_id' => $iCarrierTranslationContextId,
			'in_value' => (string)$mixCarrierCode
		));
		
		if ($arrResult = $mResult->fetch_assoc()) {
			return $arrResult['code'];
		}
		
		// Return false if there was no match
		$this->_UpdateStatus(CDR_BAD_RECORD_TYPE);
		return false;
	}
	
	protected function FindRecordType($intServiceType, $strRecordCode) {
		$intResult = $this->_selFindRecordType->Execute(array("ServiceType" => $intServiceType, "Code" => $strRecordCode));
		
		if ($intResult === false) {
			// Yes?  And???
		}
		
		if ($arrResult = $this->_selFindRecordType->Fetch()) {
			$this->_intContext = $arrResult['Context'];
			return $arrResult['Id'];
		}
		
		// Return false if there was no match
		$this->_UpdateStatus(CDR_BAD_RECORD_TYPE);
		return false;
	}
	
	protected function FindDestination($mixCarrierCode, $bolDontError=false) {
		// This is now handled by a reimplementation
		return $this->_findDestination($mixCarrierCode);
		/*
		static	$selUnknownDestination;
		
		//CliEcho("Finding Destination Translation for Carrier {$this->intBaseCarrier} with Code '{$mixCarrierCode}' in Context {$this->_intContext}");
		
		// See if we have translation data for this destination
		$arrData = array("Carrier" => $this->intBaseCarrier, "CarrierCode" => $mixCarrierCode, "Context" => $this->_intContext);
		$intResult = $this->_selFindDestination->Execute($arrData);
		
		if ($intResult === false) {
			// Yes?  And???
		}
		
		if ($arrResult = $this->_selFindDestination->Fetch()) {
			return $arrResult;
		}
		
		// No translation data -- Use the 'Unknown Destination' Destination for this Context
		$selUnknownDestination = ($selUnknownDestination) ? $selUnknownDestination : new StatementSelect(
			"destination_context JOIN Destination ON destination_context.fallback_destination_id = Destination.Id",
			"Destination.*",
			"destination_context.id = <Context>"
		);
		if ($selUnknownDestination->Execute(array('Context'=>$this->_intContext)) === false) {
			throw new Exception_Database($selUnknownDestination->Error());
		}
		if ($arrUnknownDestination = $selUnknownDestination->Fetch()) {
			$arrUnknownDestination['bolUnknownDestination']	= true;
			return $arrUnknownDestination;
		}
		
		throw new Exception("No Default Destination found for Context {$this->_intContext}!");

		// Set an error status
		if ($bolDontError !== true) {
			$this->_UpdateStatus(CDR_BAD_DESTINATION);
		}
		
		// Return false if there was no match
		return false;
		*/
	}
	
	protected function _findDestination($mCarrierCode, $bExactMatchOnly=false, $bSilentFail=false) {
		static	$oGetUnknownDestination;
		
		// Check for exact match destination
		$iCarrierTranslationContextId = $this->GetConfigField('CallTypeCarrierTranslationContextId');
		Flex::assert($iCarrierTranslationContextId !== null, "No Call Type (Destination) Carrier Translation Context defined for Module {$this->_arrCarrierModule['Id']}:{$this->_arrCarrierModule['description']} (".get_class($this).")");
		$mResult = Query::run($this->_sFindDestinationSQL, array('carrier_translation_context_id'=>$iCarrierTranslationContextId, 'in_value'=>(string)$mCarrierCode));
		if ($aDestination = $mResult->fetch_assoc()) {
			return $aDestination;
		}
		
		// Check for Unknown Destination
		if (!$bExactMatchOnly) {
			if ($aUnknownDestination = $this->_getUnknownDestination()) {
				$aUnknownDestination['bolUnknownDestination'] = true;
				return $aUnknownDestination;
			} else {
				throw new Exception("No Default Destination found for Context {$this->_intContext}!");
			}
		}
		
		// No Destination -- Error
		if ($bSilentFail) {
			return false;
		} else {
			throw new Exception("No Destination found for Context {$this->_intContext}! (Code: '{$mCarrierCode}'; Carrier Translation Context: {$iCarrierTranslationContextId};)");
		}
	}

	protected function _getUnknownDestination() {
		return Query::run("
			SELECT	d.*
			FROM	destination_context dc
					JOIN Destination d ON (d.Id = dc.fallback_destination_id)
			WHERE	dc.id = <destination_context_id>
		", array(
			'destination_context_id' => $this->_intContext
		))->fetch_assoc();
	}

	protected function _GenerateUID() {
		return "UID_{$this->_arrNormalisedData["FileName"]}_{$this->_arrNormalisedData["SequenceNo"]}";
	}
	
	protected function _IsInbound($strFNN) {
		$strPrefix = substr(trim($strFNN), 0, 2);
		if ($strPrefix === '13' || $strPrefix === '18') {
			return true;
		}
		return false;
	}
	
	protected function _IsCredit() {
		if(!isset($this->_arrNormalisedData['Units']) || !isset($this->_arrNormalisedData['Cost'])) {
			// Either Units or Cost are not set yet
			$this->_AppendCDR('Credit', 0);
			return false;
		}
		
		$intUnits = (int)$this->_arrNormalisedData['Units'];
		$fltCost = (float)$this->_arrNormalisedData['Cost'];
		if ($fltCost < 0.0) {
			$this->_arrNormalisedData['Units'] = abs($intUnits);
			$this->_arrNormalisedData['Cost'] = abs($fltCost);
			$this->_AppendCDR('Credit', 1);
			return true;
		} else {
			$this->_AppendCDR('Credit', 0);
			return false;
		}
	}
	
	public function DebugCDR() {
		$arrDebugData = array();
		
		// Add the Raw CDR string
		$arrDebugData['CDR'] = $this->_strRawCDR;
		$arrDebugData['Raw'] = $this->_arrRawData;
		$arrDebugData['Normalised'] = $this->_arrNormalisedData;
		$arrDebugData['Valid'] = $this->_arrValid;
		$arrDebugData['Define'] = $this->_arrDefineCarrier;
		return $arrDebugData;
	}
	
	public function RawCDR($strCDR=null) {
		// Split the Raw CDR
		if ($strCDR) {
			$this->_SplitRawCDR($strCDR);
		}
		
		// return the Raw CDR string
		return $this->_arrRawData;
	}

	public function setNormalised($sField, $mValue) {
		$this->_AppendCDR($sField, $mValue);
	}

	public function getNormalised($sField) {
		return (isset($this->_arrNormalisedData[$sField])) ? $this->_arrNormalisedData[$sField] : null;
	}

	public function getRaw($sField) {
		return $this->_FetchRawCDR($sField);
	}

	public function translateRecordType($iServiceType, $sFromValue) {
		return Flex::assert(
			$this->FindRecordType($iServiceType, $sRecordCode = $this->FindRecordCode($sFromValue)),
			"Unable to resolve Record Type in ".get_class($this)." for Translation Context ".var_export($this->GetConfigField('CallGroupCarrierTranslationContextId'), true).", Service Type ".var_export($iServiceType, true).", Source Value ".var_export($sFromValue, true),
			array(
				'Service Type' => $iServiceType,
				'Source Value' => $sFromValue,
				'Record Code' => $sRecordCode
			)
		);
	}

	public function translateDestination($sFromValue) {
		return $this->_findDestination($sFromValue);
	}

	protected function _describeNormalisedField($sNormalisedField/*, $sRawField1, $sRawField2, ...*/) {
		$aRawFields = func_get_args();
		array_shift($aRawFields);

		$aRawFieldDescriptions = array();
		foreach ($aRawFields as $sRawField) {
			$aRawFieldDescriptions[] = "{$sRawField}: ".var_export($this->getRaw($sRawField), true);
		}

		return "{$sNormalisedField}: ".
			var_export($this->getNormalised($sNormalisedField), true).
			(count($aRawFieldDescriptions) ? ' ('.implode('; ', $aRawFieldDescriptions).')' : '');
	}
	
	/**
	* getFileImport()
	*
	* Returns the FileImport record for a CDR Id.  Uses caching.
	*
	* @method
	*/
	public static function getFileImport($intFileImportId) {
		static $qryQuery;
		static $arrFileImports	= array();
		
		$qryQuery = ($qryQuery) ? $qryQuery : new Query();
		
		// Cache the FileImport record if we don't already have it
		$intFileImportId = (int)$intFileImportId;
		if (!array_key_exists($intFileImportId, $arrFileImports) || !$arrFileImports[$intFileImportId]) {
			$resFileImport	= $qryQuery->Execute("SELECT * FROM FileImport WHERE Id = {$intFileImportId} LIMIT 1");
			if ($resFileImport === false) {
				throw new Exception_Database($qryQuery->Error());
			} elseif ($arrFileImport = $resFileImport->fetch_assoc()) {
				$arrFileImports[$intFileImportId]	= $arrFileImport;
			} else {
				throw new Exception("Unable to find FileImport with Id '{$intFileImportId}'!");
			}
		}
		
		// Return the FileImport record
		return $arrFileImports[$intFileImportId];
	}
	
	protected static function _getServiceTypeForFNN($sFNN) {
		if (preg_match("/^1([83]00\d{6}|3{4})$/", $sFNN)) {
			// Inbound
			return SERVICE_TYPE_INBOUND;
		} elseif (preg_match("/^04\d{8}$/", $sFNN)) {
			// Mobile
			return SERVICE_TYPE_MOBILE;
		} elseif (preg_match("/^0\d{9}i$/", $sFNN)) {
			return SERVICE_TYPE_ADSL;
		} elseif (preg_match("/^0\d{9}$/", $sFNN)) {
			// Landline
			return SERVICE_TYPE_LAND_LINE;
		} else {
			throw new Exception_Assertion("Unable to derive Service Type from FNN '{$sFNN}'", $sFNN, "Unable to derive Service Type from FNN");
		}
	}
}

?>