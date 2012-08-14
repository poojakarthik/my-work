<?php

 class ImportBase extends CarrierModule {
	public $intLineNumber;
	
	function __construct($intCarrier) {
		parent::__construct($intCarrier, MODULE_TYPE_PROVISIONING_INPUT);
		
		// Defaults
		$this->intCarrier = null;
		$this->_strDelimiter = ",";
		$this->_strEndOfLine = "\n";
		$this->_strEnclosed = '';
		$this->_arrDefine = array();
		
		// Statements
		$this->_selRequestByCarrierRef = new StatementSelect("ProvisioningRequest", "*", "CarrierRef = <CarrierRef> AND Status IN (301, 302, 303)");
		$this->_selRequestByFNN = new StatementSelect(
			"ProvisioningRequest",
			"*",
			"Carrier = <Carrier> AND FNN = <FNN> AND Type = <Type> AND RequestedOn < <EffectiveDate> AND Status IN (301, 302, 303, 307)",
			"RequestedOn DESC"
		);
		$this->_selTranslateCarrierCode = new StatementSelect("carrier_translation", "out_value AS flex_code", "carrier_translation_context_id = <Context> AND in_value = <CarrierCode>");
		
		$this->_selCarrierModule = new StatementSelect("CarrierModule", "*", "Carrier = <Carrier> AND Module = <Module> AND Type = ".MODULE_TYPE_PROVISIONING_INPUT);
		
		$this->_selLineStatus = new StatementSelect("Service", "LineStatus, LineStatusDate, PreselectionStatus, PreselectionStatusDate", "Id = <Id>");
		
		$arrColumns	= array();
		$arrColumns['LineStatus'] = null;
		$arrColumns['LineStatusDate'] = null;
		$arrColumns['PreselectionStatus'] = null;
		$arrColumns['PreselectionStatusDate'] = null;
		$this->_ubiLineStatus = new StatementUpdateById("Service", $arrColumns);
		
		$this->_selLineStatusAction = new StatementSelect(
			"provisioning_type LEFT JOIN service_line_status_update ON service_line_status_update.provisioning_type = provisioning_type.id",
			"new_line_status, provisioning_type_nature",
			"(current_line_status = <LineStatus> OR current_line_status IS NULL) AND provisioning_request_status = <RequestStatus> AND provisioning_type = <Request>",
			"ISNULL(current_line_status) ASC",
			1
		);

		$this->_selProvisioningType = new StatementSelect("provisioning_type", "*", "id = <id>");
	}
	
	function PreProcess($arrRawData) {
		// Just return the data.  Function can be overridden to pre-process
		return $arrRawData;
	}
	
	function Normalise($arrNormalised, $intLineNumber) {
		DebugBacktrace();
		throw new Exception("ImportBase::Normalised() is a virtual function!");
	}
	
	function Validate($arrLine) {
		// Validate Line
		return true;
	}
	
	protected function _SplitLine($strLine) {
		// build the array
		if ($this->_strDelimiter) {
			// delimited record
			$arrRawData = explode($this->_strDelimiter, rtrim($strLine, "\n"));
			foreach($this->_arrDefine as $strKey=>$strValue) {
				$_arrData[$strKey] = '';
				if (isset($strValue['Index']) && isset($arrRawData[$strValue['Index']])) {
					$_arrData[$strKey] = $arrRawData[$strValue['Index']];
				}
				
				// delimited fields may have fixed width contents
				if (isset($strValue['Start']) && isset($strValue['Length']) && $strValue['Length']) {
					$_arrData[$strKey] = substr($_arrData[$strKey], $strValue['Start'], $strValue['Length']);
				}

				$_arrData[$strKey] = trim($_arrData[$strKey]);
			}
		} else {
			// fixed width record
			foreach($this->_arrDefine as $strKey=>$strValue) {
				$_arrData[$strKey] = trim(substr($strLine, $strValue['Start'], $strValue['Length']));
			}
		}
		
		return $_arrData;
	}
	
	function LinkToRequest($arrResponse) {
		// Match by FNN and Type
		if ($this->_selRequestByFNN->Execute($arrResponse)) {
			// Found a match, return the Id
			$arrReturn = $this->_selRequestByFNN->Fetch();
			return $arrReturn;
		}
		
		// No Match
		return null;
	}
	
	function TranslateCarrierCode($intContext, $mixValue) {
		$arrWhere = array();
		$arrWhere['Context'] = (int)$intContext;
		$arrWhere['CarrierCode'] = (string)$mixValue;
		if (!$this->_selTranslateCarrierCode->Execute($arrWhere)) {
			return false;
		}
		
		$arrValue = $this->_selTranslateCarrierCode->Fetch();
		return $arrValue['flex_code'];
	}
	
	function FindFNNOwner($arrPDR, $bLogError=false) {
		// Find Owner
		if (is_array($arrOwner = FindFNNOwner($arrPDR['FNN'], $arrPDR['EffectiveDate'], true))) {
			// Owner found
			$arrPDR = array_merge($arrOwner, $arrPDR);
		} else {
			// Unable to find owner
			if ($bLogError) {
				Log::get()->log("[!] Unable to find FNN Owner: ".var_export($arrOwner, true));
			}

			$arrPDR['Status'] = RESPONSE_STATUS_BAD_OWNER;
		}
		
		return $arrPDR;
	}
	
	static function UpdateLineStatus($arrResponse) {
		//Debug($arrResponse);
		
		// Init Statements
		static $selLineStatus = null;
		static $selProvisioningType = null;
		static $selLineStatusAction = null;
		static $ubiLineStatus = null;
		
		if (!isset($selLineStatus)) {
			$selLineStatus = new StatementSelect("Service", "Id, LineStatus, LineStatusDate, PreselectionStatus, PreselectionStatusDate", "Id = <Service>");
		}
		if (!isset($selProvisioningType)) {
			$selProvisioningType = new StatementSelect("provisioning_type", "*", "id = <id>");
		}
		if (!isset($selLineStatusAction)) {
			$selLineStatusAction = new StatementSelect("provisioning_type LEFT JOIN service_line_status_update ON service_line_status_update.provisioning_type = provisioning_type.id", "new_line_status, provisioning_type_nature", "(current_line_status = <LineStatus> OR current_line_status IS NULL) AND provisioning_request_status = <RequestStatus> AND provisioning_type = <Request>", "ISNULL(current_line_status) ASC", 1);
		}
		if (!isset($ubiLineStatus)) {
			$arrColumns	= array();
			$arrColumns['LineStatus'] = null;
			$arrColumns['LineStatusDate'] = null;
			$arrColumns['PreselectionStatus'] = null;
			$arrColumns['PreselectionStatusDate'] = null;
			$ubiLineStatus = new StatementUpdateById("Service", $arrColumns);
		}
		
		// Get Current Line Status for the Service
		if ($selLineStatus->Execute($arrResponse)) {
			$arrLineStatus	= $selLineStatus->Fetch();
			
			// Get the Provisioning Type Nature Details
			if ($selProvisioningType->Execute(array('id' => $arrResponse['Type']))) {
				$arrProvisioningType = $selProvisioningType->Fetch();
				if ($arrProvisioningType['provisioning_type_nature'] === REQUEST_TYPE_NATURE_PRESELECTION) {
					// Land Line Preselection Status
					$strCurrentEffectiveDate = &$arrLineStatus['PreselectionStatusDate'];
					$intCurrentLineStatus = &$arrLineStatus['PreselectionStatus'];
				} else {
					// Line Status
					$strCurrentEffectiveDate = &$arrLineStatus['LineStatusDate'];
					$intCurrentLineStatus = &$arrLineStatus['LineStatus'];
				}
				
				// Is this Status newer than the current Status?
				if (strtotime($arrResponse['EffectiveDate']) > strtotime($strCurrentEffectiveDate)) {
					// Current Status is older than this Status
					$intCurrentLineStatus	= null;
					
				} elseif (strtotime($arrResponse['EffectiveDate']) === strtotime($strCurrentEffectiveDate)) {
					// Same Date
					$intActionLineStatus = $intCurrentLineStatus;
				} else {
					// Current Status is newer, don't update
					//CliEcho("({$arrResponse['Id']}) -- Current Status ($strCurrentEffectiveDate) is newer than ({$arrResponse['EffectiveDate']})");
					return false;
				}
				
				// Get the Update Details for the Current Status + the Request Type
				if ($selLineStatusAction->Execute(array('LineStatus' => $intCurrentLineStatus, 'Request' => $arrProvisioningType['id'], 'RequestStatus' => $arrResponse['request_status']))) {
					$arrLineStatusAction = $selLineStatusAction->Fetch();
					$strCurrentEffectiveDate = $arrResponse['EffectiveDate'];
					$intCurrentLineStatus = $arrLineStatusAction['new_line_status'];
					
					// Save the new Line Status
					if ($ubiLineStatus->Execute($arrLineStatus) === false) {
						return "DB Error for _ubiLineStatus: ".$ubiLineStatus->Error();
					} else {
						//CliEcho("Line Status Updated to ".GetConstantDescription($arrLineStatusAction['new_line_status'], 'service_line_status'));
						return true;
					}
				} else {
					// No Definition or Default for this Relationship, don't update
					CliEcho("No Definition for {$arrResponse['Id']} (LineStatus: {$intCurrentLineStatus}; Type: {$arrProvisioningType['id']}; RequestStatus: {$arrResponse['request_status']}) -- not updating");
					return true;
				}
			} elseif ($selProvisioningType->Error()) {
				// Error
				return "DB Error for _selProvisioningType: ".$selProvisioningType->Error();
			} else {
				return "Unable to retrieve ProvisioningType details for id '{$arrResponse['Type']}'";
			}
		} elseif ($selLineStatus->Error()) {
			// Error
			return "DB Error for _selLineStatus: ".$selLineStatus->Error();
		} else {
			Debug($arrResponse);
			return "Unable to retrieve Line Status Details for Service '{$arrResponse['Service']}'";
		}
	}
}
