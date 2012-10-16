<?php
// Provisioning Debug Mode (don't deliver files)
define("PROVISIONING_DEBUG_MODE", false);

class ApplicationProvisioning extends ApplicationBaseClass { 	
	public $_arrExportFiles;
	public $_arrImportFiles;
	public $_arrExportModules;
	
	function __construct() {
		parent::__construct();
		
		$this->_arrImportFiles = array();
		$this->_arrExportFiles = array();
		$this->_arrExportModules = array();

		CliEcho("\n[ INIT PROVISIONING MODULES ]\n");
		
		// Init Import Modules
		CliEcho(" * IMPORT MODULES");
		$this->_selCarrierModules->Execute(array('Type' => MODULE_TYPE_PROVISIONING_INPUT));
		while ($arrModule = $this->_selCarrierModules->Fetch()) {
			$this->_arrImportFiles[$arrModule['Carrier']][$arrModule['FileType']] = new $arrModule['Module']($arrModule['Carrier']);
			CliEcho("\t + ".Carrier::getForId($arrModule['Carrier'])->description." : ".$this->_arrImportFiles[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
		}
		
		// Init Export Modules
		CliEcho("\n * EXPORT MODULES");
		$this->_selCarrierModules->Execute(array('Type' => MODULE_TYPE_PROVISIONING_OUTPUT));
		while ($arrModule = $this->_selCarrierModules->Fetch()) {
			$this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']] = new $arrModule['Module']($arrModule['Carrier']);
			
			// Link Provisioning Types to the file
			foreach ($this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']]->GetTypes() as $intType) {
				$this->_arrExportModules[$arrModule['Carrier']][$intType] = $this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']];
			}
			
			CliEcho("\t + ".Carrier::getForId($arrModule['Carrier'])->description." : ".$this->_arrExportFiles[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
		}
		CliEcho('');
	}
	
	function Import() {
		// Get list of Provisioning Files to import
		$arrFileTypes = array();
		foreach ($this->_arrImportFiles as $intCarrier=>$arrCarrierFileTypes) {
			foreach (array_keys($arrCarrierFileTypes) as $intFileType) {
				$arrFileTypes[] = $intFileType;
			}
		}
		
		// Do we have any FileTypes to import?
		if (!count($arrFileTypes)) {
			return false;
		}
		
		// Statements
		$arrCols = array();
		$arrCols['Response'] = null;
		$arrCols['LastUpdated'] = null;
		$arrCols['Status'] = null;
		$arrCols['Description'] = null;
		$ubiRequest = new StatementUpdateById("ProvisioningRequest", $arrCols);
		
		$selImport = new StatementSelect("FileImport JOIN compression_algorithm ON FileImport.compression_algorithm_id = compression_algorithm.id", "FileImport.*, compression_algorithm.file_extension, compression_algorithm.php_stream_wrapper", "FileType IN (".implode(', ', $arrFileTypes).") AND Status = ".FILE_COLLECTED);
		$selServiceCarrier = new StatementSelect("Service", "Carrier, CarrierPreselect", "Id = <Service>");
		
		$arrCols = $this->db->FetchClean("ProvisioningResponse");
		$arrCols['ImportedOn'] = new MySQLFunction("NOW()");
		$insResponse = new StatementInsert("ProvisioningResponse", $arrCols);
		
		$arrData = array();
		$arrData['Status'] = FILE_IMPORTED;
		$arrData['NormalisedOn'] = new MySQLFunction("NOW()");
		$ubiFileImport = new StatementUpdateById("FileImport", $arrData);
		
		
		// Select all Provisioning Files waiting to be imported
		$selImport->Execute();
		
		// For each File
		$arrDelinquents = array();
		while ($arrFile = $selImport->Fetch()) {
			CliEcho("\nOpening {$arrFile['FileName']}...");
			
			// Is there a module?
			if (!$this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']]) {
				// TODO: Error
				//CliEcho("\t* No module found");
				continue;
			}
				
			// Open File for Reading
			if (!@$ptrFile = fopen($arrFile['php_stream_wrapper'].$arrFile['Location'], 'r')) {
				// TODO: Error!
				CliEcho("\t* Unable to read file '{$arrFile['Location']}'");
				continue;
			}
			
			// Read file
			$arrRawContent = array();
			while (!feof($ptrFile)) {
				$arrRawContent[] = fgets($ptrFile);
			}
			
			// Run File PreProcessor
			$arrFileContent = $this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']]->PreProcess($arrRawContent);
			//Debug($arrFileContent);
			
			// Process Lines
			$intLineNumber = 0;
			foreach ($arrFileContent as $strLine) {
				// Incremember Line Number
				$intLineNumber++;
				
				// Normalise line
				$arrNormalised = $this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']]->Normalise($strLine, $intLineNumber);
				
				// Add generic fields
				$arrNormalised['Carrier'] = $arrFile['Carrier'];
				$arrNormalised['Raw'] = $strLine;
				$arrNormalised['ImportedOn'] = new MySQLFunction("NOW()");
				$arrNormalised['FileImport'] = $arrFile['Id'];
				
				// Is this a valid record?
				switch ($arrNormalised['Status']) {
					case RESPONSE_STATUS_BAD_OWNER:
						$arrDelinquents[$arrNormalised['FNN']]++;
						break;
						
					case RESPONSE_STATUS_CANT_NORMALISE:
						Debug("Unhandled Error!");
						break;
						
					default:
						// Is this a duplicate?
						if (!isset($arrNormalised['Id'])) {
							$arrNormalised['Id'] = null;
						}

						// Check for another provisioning response with the same data
						$this->_selDuplicate = new StatementSelect(
							"ProvisioningResponse",
							"Id",
							"Id != <Id> AND Service = <Service> AND FNN = <FNN> AND Carrier = <Carrier> AND Type = <Type> AND Description = <Description> AND EffectiveDate = <EffectiveDate> AND Status = 402"
						);
						if (!$this->_selDuplicate->Execute($arrNormalised)) {
							// Valid Response
							$arrNormalised['Status'] = RESPONSE_STATUS_IMPORTED;
							
							// Attempt to link to a Request
							if (isset($arrNormalised['Request'])) {
								// The normaliser found a request
								$oRequest = new Provisioning_Request(array('Id' => $arrNormalised['Request']), true);
								$arrLinkedRequest = $oRequest->toArray();
							} else if ($arrLinkedRequest = $this->_arrImportFiles[$arrFile['Carrier']][$arrFile['FileType']]->LinkToRequest($arrNormalised)) {
								// Found the request via the import modules LinkToRequest function
								$arrNormalised['Request'] = $arrLinkedRequest['Id'];
							}
							
							/*
							// If the File Carrier doesn't match up with Service Carrier, then mark as redundant
							$selServiceCarrier->Execute($arrNormalised);
							$arrServiceCarrier = $selServiceCarrier->Fetch();
							switch ($arrNormalised['Type']) {
								case PROVISIONING_TYPE_LOSS_FULL:
									if ($arrNormalised['Carrier'] != $arrServiceCarrier['Carrier']) {
										$arrNormalised['Status'] = RESPONSE_STATUS_REDUNDANT;
									}
									break;
									
								case PROVISIONING_TYPE_LOSS_PRESELECT:
									if ($arrNormalised['Carrier'] != $arrServiceCarrier['CarrierPreselect']) {
										$arrNormalised['Status'] = RESPONSE_STATUS_REDUNDANT;
									}
									break;
							}*/
							
							// Update the Service (if needed)
							//$this->_UpdateService($arrNormalised);
						} else {
							// This Response is a duplicate
							$arrNormalised['Status'] = RESPONSE_STATUS_DUPLICATE;
							$arrNormalised['Duplicate'] = $this->_selDuplicate->Fetch();
						}
				}
				
				// Insert into ProvisioningResponse Table
				if (($arrNormalised['Id'] = $insResponse->Execute($arrNormalised)) === false) {
					Debug($insResponse->Error());
				} elseif ($arrNormalised['Status'] === RESPONSE_STATUS_DUPLICATE) {
					CliEcho("Response #{$arrNormalised['Id']} is a duplicate of {$arrNormalised['Duplicate']['Id']}");
				} elseif ($arrNormalised['Request']) {
					// Update Request Table if this is the most recent Response for this Request
					if (strtotime($arrLinkedRequest['LastUpdated']) < strtotime($arrNormalised['EffectiveDate'])) {
						$arrLinkedRequest['Response'] = $arrNormalised['Id'];
						$arrLinkedRequest['LastUpdated'] = $arrNormalised['EffectiveDate'];
						$arrLinkedRequest['Status'] = $arrNormalised['request_status'];
						$arrLinkedRequest['Description'] = $arrNormalised['Description'];
						if ($ubiRequest->Execute($arrLinkedRequest) === false) {
							CliEcho("WARNING: Request Link Update failed: ".$ubiRequest->Error());
						}
					}
				}
			}
			
			// Update FileImport
			$arrFile['Status'] = FILE_IMPORTED;
			$arrFile['NormalisedOn'] = new MySQLFunction("NOW()");
			$ubiFileImport->Execute($arrFile);
		}
		
		// Delete all Duplicates
		CliEcho("\n * Deleting Duplicate Responses...", false);
		$qryQuery = new Query();
		if ($qryQuery->Execute("DELETE FROM ProvisioningResponse WHERE Status = ".RESPONSE_STATUS_DUPLICATE) === false) {
			CliEcho("\t\t\t[ FAILED ]\n\t -- ".$qryQuery->Error()); 			
		} else {
			CliEcho("\t\t\t[   OK   ]");
		}
	}
	
	function Export() {
		// Statements
		$arrCols = array();
		$arrCols['Status'] = null;
		$arrCols['Description'] = null;
		$arrCols['CarrierRef'] = null;
		$ubiRequest = new StatementUpdateById("ProvisioningRequest", $arrCols);
		$selRequests = new StatementSelect("ProvisioningRequest", "*", "Status = ".REQUEST_STATUS_WAITING);
		
		CliEcho("[ PROVISIONING EXPORT ]\n");
		
		// Select all Requests waiting to be sent out
		$selRequests->Execute();
		
		// Loop through Requests
		CliEcho(" * Processing Requests...");
		while ($arrRequest = $selRequests->Fetch()) {
			CliEcho("\t+ Exporting #{$arrRequest['Id']}...\t\t\t", false);
			
			if ($this->_arrExportModules[$arrRequest['Carrier']][$arrRequest['Type']]) {
				// Prepare output for this request
				$arrRequest = $this->_arrExportModules[$arrRequest['Carrier']][$arrRequest['Type']]->Output($arrRequest);
				
				if ($arrRequest['Status'] !== REQUEST_STATUS_EXPORTING) {
					CliEcho("[ FAILED ]\n\t\t- {$arrRequest['Description']}");
				} elseif (!$this->_arrExportModules[$arrRequest['Carrier']][$arrRequest['Type']]->bolCanRunModule) {
					// Too early to run the module
					CliEcho("[  SKIP  ]");
					continue;
				} else {
					CliEcho("[   OK   ]");
				}
			} else {
				// No module
				$arrRequest['Status'] = REQUEST_STATUS_NO_MODULE;
				
				$strCarrier = Carrier::getForId($arrRequest['Carrier'])->description;
				$strType = GetConstantDescription($arrRequest['Type'], 'provisioning_type');
				CliEcho("[ FAILED ]\n\t\t- No module ($strCarrier: $strType)");
			}

			// Update Request Status and Details
			$ubiRequest->Execute($arrRequest);
		}
		
		// Finalise files
		CliEcho("\n * Finalising and Delivering Files...");
		foreach ($this->_arrExportFiles as $intCarrier=>$arrModules) {
			foreach ($arrModules as $intType=>$prvModule) {
				if ($arrModules[$intType]->bolCanRunModule && !$arrModules[$intType]->bolExported) {
					$strCarrier = Carrier::getForId($intCarrier)->description;
					$strType = $arrModules[$intType]->strDescription;
					CliEcho("\t + $strCarrier: $strType...\t\t\t", false);
					$mixResult = $arrModules[$intType]->Export();
					if ($mixResult['Pass']) {
						CliEcho("[   OK   ]");
						if (PROVISIONING_DEBUG_MODE) {
							CliEcho($mixResult['Description']);
						}
					} elseif ($mixResult['Pass'] === false) {
						CliEcho("[ FAILED ]\n\t\tReason: {$mixResult['Description']}");
					} else {
						CliEcho("[  SKIP  ]");
						if (PROVISIONING_DEBUG_MODE) {
							CliEcho($mixResult['Description']);
						}
					}
				}
			}
		}
		CliEcho('');
	}
	
	function SOAPRequest($intService, $intCarrier, $intRequestType) {
		// Add ProvisioningRequest entry
		
		// Communicate with SOAP server
		$arrResponse = $this->arrSOAPModules[$intCarrier]->Request($intService, $intRequestType);
		
		// Add ProvisioningResponse entry
		
		// If successful, update Service
		
		// Return message & Status
	}
	
	function _UpdateService($arrResponse) {
		// Get Service
		$this->_selService = new StatementSelect("Service", "*", "Id = <Service>");
		if ($this->_selService->Execute() === false) {
			return array('Pass' => false, 'Message' => "SELECT Query Failed: ".$this->_selService->Error());
		}
		
		if ($arrService = $this->_selService->Fetch()) {
			// Determine if we need to update
			if ($arrResponse['LineStatus']) {
				
			}
		} else {
			return array('Pass' => false, 'Message' => "Service '{$arrResponse['Service']}' ({$arrResponse['FNN']}) not found!");
		}
	}
}

?>