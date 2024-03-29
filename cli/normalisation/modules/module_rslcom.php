<?php
class NormalisationModuleRSLCOM extends NormalisationModule {
	public $intBaseFileType = RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD;

	public function __construct($intCarrier) {
		// call parent constructor
		parent::__construct($intCarrier);
		
		// define the column delimiter
		$this->_strDelimiter = ",";
		
		// define row start (account for header rows)
		$this->_intStartRow = 1;
		
		// define the carrier CDR format
		$arrDefine['EventId']['Index'] = 0; // Unique Identifier
		$arrDefine['RecordType']['Index'] = 1; // 1 = Usage; 7 = S&E; 8 = OC&C
		$arrDefine['DateTime']['Index'] = 2; // Starting Datetime of the call
		$arrDefine['Duration']['Index'] = 3; // Duration in seconds
		$arrDefine['OriginNo']['Index'] = 4; // Originating phone number
		$arrDefine['DestinationNo']['Index'] = 5; // Destination phone number
		$arrDefine['ChargedParty']['Index'] = 6; // Charged phone number
		$arrDefine['Currency']['Index'] = 7; // Usually AUD
		$arrDefine['Price']['Index'] = 8; // Price charged to VOIPTel
		$arrDefine['PlanId']['Index'] = 9; // Unitel Plan ID
		$arrDefine['Distance']['Index'] = 10; // Distance in KM travelled by call
		$arrDefine['IsLocal']['Index'] = 11; // 1 = Local Call; 0 = Non-Local
		$arrDefine['CallType']['Index'] = 12; // Unitel Call Type ID
		$arrDefine['BeginDate']['Index'] = 13; // Starting Date (RecordType 7&8 Only)
		$arrDefine['EndDate']['Index'] = 14; // Ending Date (RecordType 7&8 Only)
		$arrDefine['Description']['Index'] = 15; // Description (RecordType 7&8 Only)
		$arrDefine['ItemCount']['Index'] = 16; // Item Count (RecordType 7&8 Only)
		$arrDefine['CarrierId']['Index'] = 17; // 1 = Telstra; 2 = Optus; 3 = Unitel
		$arrDefine['RateId']['Index'] = 18; // Unitel's Rate ID
		
		$arrDefine['EventId']['Validate'] = "/^\d+$/";
		$arrDefine['RecordType']['Validate'] = "/^[178]$/";
		$arrDefine['DateTime']['Validate'] = "/^((\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d)|(\"\d{2}\/\d{2}\/\d{4})\")$/";
		$arrDefine['Duration']['Validate'] = "/^(-?\d+|)$/";
		$arrDefine['OriginNo']['Validate'] = "/^(\+?\d+(REV|I)?|)$/";
		$arrDefine['DestinationNo']['Validate'] = "/^(\+?\d+(REV|I)?|)$/";
		$arrDefine['ChargedParty']['Validate'] = "/^\"?\+?\d+\"?$/";
		$arrDefine['Currency']['Validate'] = "/^(AUD|\"AUD\\$\")$/";
		$arrDefine['Price']['Validate'] = "/^-?\\$?\d+\.\d\d*$/";
		$arrDefine['CallType']['Validate'] = "/^(\d+|)$/";
		$arrDefine['RateId']['Validate'] = "/^(\d+|)$/";
		
		$this->_arrDefineCarrier = $arrDefine;
	}

	public function Normalise($arrCDR) {
		// set up CDR
		$this->_NewCDR($arrCDR);
		
		// ignore header rows
		if ((int)$arrCDR["SequenceNo"] < 1) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_BAD_SEQ_NO);
		} elseif ((int)$arrCDR["SequenceNo"] < $this->_intStartRow) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_HEADER);
		}
		
		// covert CDR string to array
		$this->_SplitRawCDR($arrCDR["CDR"]);
		
		// ignore non-CDR rows
		$intRowType = (int)$this->_FetchRawCDR('RecordType');
		if ($intRowType != 1 && $intRowType != 7 && $intRowType != 8) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_NON_CDR);
		}

		// validation of Raw CDR
		if (!$this->_ValidateRawCDR()) {
			return $this->_ErrorCDR(CDR_CANT_NORMALISE_RAW);
		}
		
		//--------------------------------------------------------------------//
		// add fields to CDR
		//--------------------------------------------------------------------//
		
		// Remove double-quotes from fields
		foreach($this->_arrRawData as $strKey=>$strField) {
			$this->_arrRawData[$strKey] = str_replace("\"", "", $strField);
		}
		
		// FNN
		$strFNN = $this->_FetchRawCDR('ChargedParty');
		$strFNN = $this->RemoveAusCode($strFNN);
		$this->_AppendCDR('FNN', $strFNN);

		// Carrier Record Type
		$intCarrierRecordType = (int)$this->_FetchRawCDR('RecordType');
		
		// ServiceType
		if ($this->_IsInbound($strFNN)) {
			$intServiceType = SERVICE_TYPE_INBOUND;
		} else {
			$intServiceType = SERVICE_TYPE_LAND_LINE;
		}
		$this->_AppendCDR('ServiceType', $intServiceType);
		
		// RecordType
		if ($intCarrierRecordType == "7") {
			// S&E
			$strRecordCode = 'S&E';
		} elseif ($intCarrierRecordType == "8") {
			// OC&C
			// Look over there while I Change these to look like an S&E Record
			$strRecordCode = 'S&E';
		} else {
			// normal calls
			$mixRateId = $this->_FetchRawCDR('RateId');
			$mixCallType = $this->_FetchRawCDR('CallType');
			$strRecordCode = $this->FindRecordCode($mixCallType);
			
			// Check for Tesltra OnBill with IDD
			/*if ($mixRateId === '915')
			{
				// Is it not an IDD Call?
				if ($strRecordCode !== 'IDD')
				{
					// Re-route to Other Charges
					$strRecordCode = $this->FindRecordCode('915');
				}
			}*/
		}
		
		$mixValue = $this->FindRecordType($intServiceType, $strRecordCode); 
		$this->_AppendCDR('RecordType', $mixValue);

		// Destination Code & Description (context based)
		$strDescription = '';
		if ($this->_intContext > 0) {
			if ($intCarrierRecordType == "7") {
				// get S&E Description 
				$mixCarrierDestination = $this->_FetchRawCDR('Description');
				switch ($mixCarrierDestination) {
					case "ISDN 30 Access":
						$intItemCount = (int)$this->_FetchRawCDR('ItemCount');
						$mixCarrierDestination .= " {$intItemCount}";
						break;

					default:
						break;
				}
			} elseif ($intCarrierRecordType == "8") {
				// OC&C
				// Look over there while I Change these to look like an S&E Record
				$mixCarrierDestination = $this->_FetchRawCDR('Description');
			} else {
				// normal calls
				$mixCarrierDestination = $this->_FetchRawCDR('RateId');
			}
			
			// Get Flex Destination
			$arrDestinationCode = $this->FindDestination($mixCarrierDestination);
			$this->_AppendCDR('DestinationCode', $arrDestinationCode['Code']);
			
			// Determine Description
			if ($arrDestinationCode['bolUnknownDestination'] === true) {
				// Use the Raw Description
				$strDescription = $this->RawDescription();
			} else {
				// Use the Destination's Description
				$strDescription = $arrDestinationCode['Description'];
			}
		}
		
		// CarrierRef
		$mixValue = $this->_FetchRawCDR('EventId');
		$this->_AppendCDR('CarrierRef', $mixValue);
		
		// StartDateTime & EndDateTime
		if ($intCarrierRecordType == "1") {
		 	// For normal usage CDRs
		 	$mixValue = $this->_FetchRawCDR('DateTime');
		 	$this->_AppendCDR('StartDatetime', $mixValue);
		 	
		 	$intStart = strtotime($this->_FetchRawCDR('DateTime'));
		 	$intEnd = strtotime(" +" . abs($this->_FetchRawCDR('Duration')) . "seconds", $intStart);
		 	$mixValue = date("Y-m-d H:i:s", $intEnd);
			$this->_AppendCDR('EndDatetime', $mixValue);
		} else {
		 	// For S&E and OC&C CDRs
		 	$mixValue = $this->ConvertTime($this->_FetchRawCDR('BeginDate'));
		 	$this->_AppendCDR('StartDatetime', $mixValue);
		 	$mixValue = $this->ConvertTime($this->_FetchRawCDR('EndDate'));
		 	$this->_AppendCDR('EndDatetime', $mixValue);
		}
		
		// Description
		if ($intCarrierRecordType == "1") {
			if($strDescription) {
				// already has a description
			} elseif ($intServiceType === SERVICE_TYPE_INBOUND) {
				// inbound service
				//TODO!LATER! set this to the state or city the call originated from
				$strDescription = "Call from ".$this->_FetchRawCDR('OriginNo');
			} else {
		 		//TODO!LATER! more desrciptions
			}
		} else {
		 	// For S&E and OC&C CDRs
			if (!$strDescription) {
				// use description from file for unknown S&E types
				$strDescription = $this->_FetchRawCDR('Description');
			}
			// add dates
			$strDescription .= " ".$this->_FetchRawCDR('BeginDate')." to ".$this->_FetchRawCDR('EndDate');
		}
		if ($strDescription) {
			$this->_AppendCDR('Description', $strDescription);
		}
		
		// Units
		if ($intCarrierRecordType == "1") {
		 	// For normal usage CDRs
		 	$mixValue = $this->_FetchRawCDR('Duration');
		 	$this->_AppendCDR('Units', (int)$mixValue);
		} else {
		 	// For S&E and OC&C CDRs
		 	$mixValue = $this->_FetchRawCDR('ItemCount');
		 	$this->_AppendCDR('Units', (int)$mixValue);
		}
		
		// Cost
		$mixValue = $this->_FetchRawCDR('Price');
		$mixValue = str_replace('$', '', $mixValue);
		$this->_AppendCDR('Cost', (float)$mixValue);
		
		// Source
		$mixValue = $this->_FetchRawCDR('OriginNo');
		$this->_AppendCDR('Source', $this->RemoveAusCode($mixValue));
		
		// Destination
		$mixValue = $this->_FetchRawCDR('DestinationNo');
		$this->_AppendCDR('Destination', $this->RemoveAusCode($mixValue));
		
		// Is Credit?
		$this->_IsCredit();

		//--------------------------------------------------------------------//
		
		// Apply Ownership
		$this->ApplyOwnership();
		
		// Validation of Normalised data
		$this->Validate();
		
		// return output array
		return $this->_OutputCDR();
	}

	private function ConvertTime($strTime) {
		$strReturn = substr($strTime, 6, 4); // Year
		$strReturn .=  "-" . substr($strTime, 3, 2); // Month
		$strReturn .=  "-" . substr($strTime, 0, 2); // Day
		$strReturn .=  " 00:00:00"; // Time
		return $strReturn;
	}
	
	private function RawDestinationCode() {
		return $this->_FetchRawCDR('RateId');
	}
	
	private function RawDescription() {
		return $this->_FetchRawCDR('Description');
	}
	
	private function RawRecordType() {
		return (int)$this->_FetchRawCDR('CallType');
	}
}