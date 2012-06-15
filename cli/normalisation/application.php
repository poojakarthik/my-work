<?php
class ApplicationNormalise extends ApplicationBaseClass {
	const DEBUG_LOGGING = true;

	const FILE_MINIMUM_PERCENT_VALID = .9;
	const FILE_IMPORT_LIMIT = 1000000;

	private $_arrCDRErrorStatuses =	array(
		CDR_CANT_NORMALISE,
		CDR_CANT_NORMALISE_RAW,
		CDR_CANT_NORMALISE_BAD_SEQ_NO,
		CDR_CANT_NORMALISE_NON_CDR,
		CDR_BAD_RECORD_TYPE,
		CDR_BAD_DESTINATION,
		CDR_CANT_NORMALISE_NO_MODULE,
		CDR_CANT_NORMALISE_INVALID,
		CDR_DUPLICATE
	);

	public $errErrorHandler;
	public $rptNormalisationReport;
	public $_arrDelinquents;
	public $_arrNormaliseReportCount;
	public $_arrNormalisationModule;

	private $_intImportPass;
	private $_intImportFail;

	function __construct($mixEmailAddress) {
		parent::__construct();

		// Initialise framework components
		$this->rptNormalisationReport = new Report("Normalisation Report for " . date("Y-m-d H:i:s"), "rdavis@ybs.net.au");
		$this->rptDelinquentsReport = new Report("Delinquents Report for ". date("Y-m-d H:i:s"), $mixEmailAddress, false);
		$this->errErrorHandler = new ErrorHandler();

		// Load CDR Normalisation CarrierModules
		CliEcho(" * NORMALISATION MODULES");
		$this->_selCarrierModules->Execute(array('Type' => MODULE_TYPE_NORMALISATION_CDR));
		while ($arrModule = $this->_selCarrierModules->Fetch()) {
			$this->_arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']] = new $arrModule['Module']($arrModule['Carrier']);
			CliEcho("\t + ".Carrier::getForId($arrModule['Carrier'])->Description." : {$arrModule['description']} ({$arrModule['Id']})");
		}

 		// Load CDR Normalisation CarrierModules
 		CliEcho(" * NORMALISATION MODULES");
 		$this->_selCarrierModules->Execute(Array('Type' => MODULE_TYPE_NORMALISATION_CDR));
 		while ($arrModule = $this->_selCarrierModules->Fetch())
 		{
 			$this->_arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
 			CliEcho("\t + ".Carrier::getForId($arrModule['Carrier'])->description." : ".(isset($arrModule['description']) ? $arrModule['description'] : ''));
 		}


		// STATEMENTS
		$this->_selCreditCDRs = new StatementSelect("CDR", "Id, FNN, Source, Destination, Cost, Units, StartDatetime", "Credit = 1 AND Status = ".CDR_RATED, null, "1000");

		$strStatus = " AND (Status = ".CDR_RATED." OR Status = ".CDR_NORMALISED." OR Status = ".CDR_BAD_OWNER." OR Status = ".CDR_BAD_RECORD_TYPE." OR Status = ".CDR_BAD_DESTINATION." OR Status = ".CDR_FIND_OWNER." OR Status = ".CDR_RENORMALISE." OR Status = ".CDR_RATE_NOT_FOUND.")";
		$this->_selDebitCDR = new StatementSelect("CDR", "Id", "Id != <Id> AND FNN = <FNN> AND Source = <Source> AND Destination = <Destination> AND Cost = <Cost> AND Units = <Units> AND StartDatetime = <StartDatetime> {$strStatus}", null, 1);
		//$this->_selRatedCDR = new StatementSelect("CDR", "Id", "Id != <Id> AND FNN = <FNN> AND Source = <Source> AND Destination = <Destination> AND Cost = <Cost> AND Units = <Units> AND StartDatetime = <StartDatetime> AND Status = ".CDR_RATED, null, 1);

		$arrUpdateColumns = array();
		$arrUpdateColumns['Status'] = '';
		$this->_ubiCDRStatus = new StatementUpdateById("CDR", $arrUpdateColumns);

		$this->_insCreditLink = new StatementInsert("cdr_credit_link");

		// Update CDR Query
		$arrDefine = $this->db->FetchClean("CDR");
		$arrDefine['NormalisedOn'] = new MySQLFunction("NOW()");
		$this->_updUpdateCDRs = new StatementUpdate("CDR", "Id = <CdrId>", $arrDefine);

		$this->_arrDelinquents = array();

		// Duplicate CDR Query
		/*$this->_selFindDuplicate = new StatementSelect(	"CDR",
															"Id, CASE WHEN CarrierRef <=> <CarrierRef> THEN ".CDR_DUPLICATE." ELSE ".CDR_RECHARGE." END AS Status",
															"Id != <Id> AND " .
															"FNN = <FNN> AND " .
															"Source <=> <Source> AND " .
															"Destination <=> <Destination> AND " .
															"StartDatetime <=> <StartDatetime> AND " .
															"EndDatetime <=> <EndDatetime> AND " .
															"Units = <Units> AND " .
															"Cost = <Cost> AND " .
															"RecordType = <RecordType> AND " .
															"RecordType NOT IN (10, 15, 33, 21) AND " .
															"Credit = <Credit> AND " .
															"Description <=> <Description> AND " .
															"Status NOT IN (".CDR_DUPLICATE.", ".CDR_RECHARGE.")",
															null,
															1);*/
	}

	function Import($intCDRLimit=null) {
		$this->AddToNormalisationReport("\n\n".MSG_HORIZONTAL_RULE);
		$this->AddToNormalisationReport(MSG_IMPORTING_TITLE);
		$this->Framework->StartWatch();

		// Retrieve list of CDR Files marked as either ready to process, or failed process
		$arrFileTypes = array();
		foreach ($this->_arrNormalisationModule as $intCarrier=>$arrCarrierFileTypes) {
			foreach (array_keys($arrCarrierFileTypes) as $intFileType) {
				$arrFileTypes[] = $intFileType;
			}
		}

		// Do we have any FileTypes to import?
		if (!count($arrFileTypes)) {
			return false;
		}

		$strWhere = "FileType IN (".implode(', ', $arrFileTypes).") AND Status IN (".FILE_COLLECTED.", ".FILE_REIMPORT.")";
		$selSelectCDRFiles = new StatementSelect(
			"FileImport JOIN compression_algorithm ON FileImport.compression_algorithm_id = compression_algorithm.id",
			"FileImport.*, compression_algorithm.file_extension, compression_algorithm.php_stream_wrapper",
			$strWhere,
			null,
			self::FILE_IMPORT_LIMIT
		);
		Log::get()->logIf(self::DEBUG_LOGGING, "File Fetch Query template: {$selSelectCDRFiles->aProfiling['sQuery']}");

		$insInsertCDRLine = new StatementInsert("CDR");

		$arrDefine = array();
		$arrDefine['Status'] = true;
		$arrDefine['ImportedOn'] = new MySQLFunction("NOW()");
		$updUpdateCDRFiles = new StatementUpdate("FileImport", "Id = <id>", $arrDefine);


		if ($selSelectCDRFiles->Execute() === false) {
			Debug($selSelectCDRFiles);
			exit(1);
		}

		$intCount = 0;
		$this->_intImportFail = 0;
		$this->_intImportPass = 0;
		
		$iTotalCDRsImported = 0;

		// Loop through the CDR File entries until we have imported the minimum number of CDRs (or there are no files left)
		while (($arrCDRFile = $selSelectCDRFiles->Fetch()) && (!$intCDRLimit || $iTotalCDRsImported < $intCDRLimit)) {
			// Start a Transaction
			if (!DataAccess::getDataAccess()->TransactionStart()) {
				throw new Exception("Unable to start a transaction");
			}
			
			try {
				// Make sure the file exists
				if (!file_exists($arrCDRFile['Location'])) {
					// Report the error, and UPDATE the database with a new status, then move to the next file
					new ExceptionVixen("Specified CDR File doesn't exist", $this->errErrorHandler, CDR_FILE_DOESNT_EXIST);
					$arrCDRFile["Status"] = FILE_IMPORT_FAILED;
					if ($updUpdateCDRFiles->Execute($arrDefine, array("Id" => $arrCDRFile["Id"])) === false) {
						throw new Exception("Unable to mark file as not existing on disk");
					}

					// Add to the Normalisation report
					$this->AddToNormalisationReport(MSG_FAIL_FILE_MISSING, array('<Path>' => $arrCDRFile["Location"]));
					throw new Exception("File not found");
				}

				// Determine exact status, and act accordingly
				switch($arrCDRFile["Status"]) {
					/*
					case FILE_REIMPORT:
						$this->CascadeDeleteCDRs();
					*/
					case FILE_COLLECTED:
						$iSequence = $this->InsertCDRFile($arrCDRFile, $insInsertCDRLine, $updUpdateCDRFiles);
						$iTotalCDRsImported	+= ($iSequence - 1);
						break;
					default:
						new ExceptionVixen("Unexpected CDR File Status", $this->errErrorHandler, UNEXPECTED_CDRFILE_STATUS);
				}

				$intCount++;
				
				// Commit the Transaction
				if (!DataAccess::getDataAccess()->TransactionCommit()) {
					throw new Exception("Unable to commit the transaction");
				}
			} catch (Exception $eException) {
				// Rollback the Transaction
				DataAccess::getDataAccess()->TransactionRollback();
				//throw $eException;
			}
		}

		// Report totals
		$arrReportLine['<Action>'] = "Imported";
		$arrReportLine['<Total>'] = $this->_intImportPass + $this->_intImportFail;
		$arrReportLine['<Time>'] = $this->Framework->LapWatch();
		$arrReportLine['<Pass>'] = (int)$this->_intImportPass;
		$arrReportLine['<Fail>'] = (int)$this->_intImportFail;
		$this->AddToNormalisationReport(MSG_REPORT, $arrReportLine);
	}

	function DeleteCDRsByFile($intFileImportId) {
		// if TEMP_INVOICE or INVOICED return false
		$selCanDeleteCDRs = new StatementSelect("CDR", "COUNT(Id)", "File = {$intFileImportId} AND (Status = ".CDR_TEMP_INVOICE." OR Status = ".CDR_INVOICED.")");
		if ($selCanDeleteCDRs->Execute() === false) {

		}
		$arrResult = $selCanDeleteCDRs->Fetch();
		if ($arrResult[0] > 0) {
			return false;
		}

		// remove cdrs
		$qryDeleteCDRs = new Query();
		$intResult = $qryDeleteCDRs->Execute("DELETE FROM CDR WHERE File = {$intFileImportId}");
		if ($intResult == false) {

		}
		return $intResult;
	}

	function CascadeDeleteCDRFile($intFileImportId) {
		// Delete all associated CDRs
		if ($this->DeleteCDRsByFile($intFileImportId) === false) {
			return false;
		}

		// Delete CDR File entry in FileImport
		$qryDeleteCDRFile = new Query();
		$intResult = $qryDeleteCDRFile->Execute("DELETE FROM FileImport WHERE Id = {$intFileImportId}");
		if ($intResult === false) {

		}
		return $intResult;
	}

	function InsertCDRFile($arrCDRFile, $insInsertCDRLine, $updUpdateCDRFiles) {
		$oStopwatch = new Stopwatch(true);

		unset($arrCDRFile['NormalisedOn']);

		// Report
		$this->rptNormalisationReport->AddMessage("\tImporting ".TruncateName($arrCDRFile['FileName'], 30)."...");

		try {
			// Set the File status to "Importing"
			$arrCDRFile["Status"] = FILE_IMPORTING;
			$arrCDRFile['ImportedOn'] = new MySQLFunction("NOW()");
			if ($updUpdateCDRFiles->Execute($arrCDRFile, array("id" => $arrCDRFile["Id"])) === false) {
				throw new Exception("Unable to mark the file as 'Importing'");
			}

			//$this->rptNormalisationReport->AddMessage("\t\tMark as Importing: ".$oStopwatch->lap(4));

			// Set fields that are consistent over all CDRs for this file
			//$arrCDRLine["File"] = $arrCDRFile["Id"];
			//$arrCDRLine["Carrier"] = $arrCDRFile["Carrier"];

			// check for a preprocessor
			$bolPreprocessor = false;
			if ($this->_arrNormalisationModule[$arrCDRFile['Carrier']][$arrCDRFile["FileType"]]) {
				if (method_exists ($this->_arrNormalisationModule[$arrCDRFile['Carrier']][$arrCDRFile["FileType"]], "Preprocessor" )) {
					//$this->rptNormalisationReport->AddMessage("\tusing a pre-processor...");
					$bolPreprocessor = true;
				}
			}
			//$this->rptNormalisationReport->AddMessage("\t\tTesting for preprocessor: ".$oStopwatch->lap(4));

			// Insert every CDR Line into the database
			$sWrappedLocation = $arrCDRFile['php_stream_wrapper'].$arrCDRFile['Location'];
			$intSequence = 1;
			$sFileContents = file_get_contents($sWrappedLocation);
			//$this->rptNormalisationReport->AddMessage("\t\tFile contents retrieved: ".$oStopwatch->lap(4));
			if ($sFileContents) {
				$aFileContents = explode("\n", $sFileContents);

				//$this->rptNormalisationReport->AddMessage("\t\tProcessing ".count($aFileContents)." lines..... (sleeping for 5 seconds)");
				//sleep(5);
				foreach ($aFileContents as $sLine) {
					$oStopwatch->lap();

					// Set fields that are consistent over all CDRs for this file
					$arrCDRLine = array(
						"File" => $arrCDRFile["Id"],
						"Carrier" => $arrCDRFile["Carrier"]
					);

					// Add to report <Action> CDR <SeqNo> from <FileName>...");
					$arrReportLine['<Action>'] = "Importing";
					$arrReportLine['<SeqNo>'] = $intSequence;
					$arrReportLine['<FileName>'] = TruncateName($arrCDRFile['FileName'], MSG_MAX_FILENAME_LENGTH);
					//$this->AddToNormalisationReport(MSG_LINE, $arrReportLine);

					$arrCDRLine["SequenceNo"] = $intSequence;
					$arrCDRLine["Status"] = CDR_READY;

					//$this->rptNormalisationReport->AddMessage("\t\tSequence {$intSequence} configured in : ".$oStopwatch->lapSplit(4));

					// run Preprocessor
					if ($bolPreprocessor) {
						$arrCDRLine["CDR"] = $this->_arrNormalisationModule[$arrCDRFile['Carrier']][$arrCDRFile["FileType"]]->Preprocessor($sLine);
					} else {
						$arrCDRLine["CDR"] = $sLine;
					}
					//$this->rptNormalisationReport->AddMessage("\t\tSequence {$intSequence} pre-processed in : ".$oStopwatch->lapSplit(4));

					if ($arrCDRLine["CDR"] === false) {
						throw new Exception("Attempting to read line {$intSequence} from the file failed: {$php_errormsg}");
					}

					if (trim($arrCDRLine["CDR"])) {
						//$this->rptNormalisationReport->AddMessage("Line {$intSequence} is: '{$arrCDRLine["CDR"]}'");
						$insInsertCDRLine->Execute($arrCDRLine);
						if ($insInsertCDRLine->Error()) {
							// error inserting
							throw new Exception("There was an error inserting line {$intSequence} into the database");
						}
						//$this->rptNormalisationReport->AddMessage("\t\tSequence {$intSequence} inserted in : ".$oStopwatch->lapSplit(4));
					} else {
						//$this->rptNormalisationReport->AddMessage("Line {$intSequence} is empty");
					}
					$intSequence++;

					// Report
					//$this->AddToNormalisationReport(MSG_OK);

					$this->_intImportPass++;

					// Break here for fast normalisation test
					if (FAST_NORMALISATION_TEST === true && $intSequence > 100) {
						break;
					}
					//$this->rptNormalisationReport->AddMessage("\t\tSequence {$intSequence} processed in : ".$oStopwatch->lapSplit(4));
				}
			}

			// Set the File status to "Normalised"
			$arrCDRFile["Status"] = FILE_IMPORTED;
			//$arrCDRFile['NormalisedOn'] = new MySQLFunction("Now()");
			if ($updUpdateCDRFiles->Execute($arrCDRFile, array("id" => $arrCDRFile["Id"])) === false) {
				throw new Exception("Unable to mark the file as 'Imported'");
			}
		} catch (ExceptionVixen $exvException) {
			//$errErrorHandler->PHPExceptionCatcher($exvException);
			CliEcho($exvException->getMessage());

			// Set the File status to "Failed"
			$arrCDRFile["Status"] = FILE_IMPORT_FAILED;
			if ($updUpdateCDRFiles->Execute($arrCDRFile, array("id" => $arrCDRFile["Id"])) === false) {
				$updUpdateCDRFiles->Error();
			}

			// Report
			//$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_CORRUPT);
		}

		// Report
		$intPassed = $this->_intImportPass;
		$intFailed = ($intSequence - 1) - $intPassed;
		$this->_intImportFail += $intFailed;
		$this->rptNormalisationReport->AddMessage("\t{$intPassed} passed, {$intFailed} failed. in ".$oStopwatch->split(4));

		return $intSequence;
	}

	function AddToNormalisationReport($strMessage, $arrAliases=array()) {
		foreach ($arrAliases as $arrAlias => $arrValue) {
			$strMessage = str_replace($arrAlias, $arrValue, $strMessage);
		}

		$this->rptNormalisationReport->AddMessage($strMessage, false);
	}

	function Normalise($intRemaining=null, $bolOnlyNew=false) {
		// Start a Transaction
		DataAccess::getDataAccess()->TransactionStart();

		try {
			// Only new CDRs?
			if ($bolOnlyNew) {
				$strWhere = "CDR.Status = ".CDR_READY;
			} else {
				$strWhere = "CDR.Status = ".CDR_READY." OR CDR.Status = ".CDR_FIND_OWNER." OR CDR.Status = ".CDR_RENORMALISE." OR CDR.Status = ".CDR_NORMALISE_NOW;
			}

			// Select CDR Query
			$strTables = "CDR INNER JOIN FileImport ON CDR.File = FileImport.Id";
			$mixColumns = array("CDR.*" => "", "FileType" => "FileImport.FileType", "FileName" => "FileImport.FileName");
			//$strOrder = "CDR.Status";
			$strOrder = null;
			$intLimit = ($intRemaining < 1000 && $intRemaining > 0) ? $intRemaining : 1000;
			$this->_selSelectCDRs = new StatementSelect($strTables, $mixColumns, $strWhere, $strOrder, $intLimit);

			// Select all CDRs ready to be Normalised
			if ($this->_selSelectCDRs->Execute() === false) {

			}
			$arrCDRList = $this->_selSelectCDRs->FetchAll();

			// we will return FALSE if there are no CDRs to normalise
			$bolReturn = false;

			// Report
			$this->rptNormalisationReport->AddMessage(MSG_NORMALISATION_TITLE);

			$this->Framework->StartWatch();

			$intNormalisePassed = 0;
			$intNormaliseTotal = 0;

			$intDelinquents = 0;
			$arrDelinquents = array();

			$arrFilesTouched = array();

			$qryQuery = new Query();
			foreach ($arrCDRList as $arrCDR) {
				$intNormaliseTotal++;

				$arrFilesTouched[(int)$arrCDR['File']] = true;

				// return TRUE if we have normalised (or tried to normalise) any CDRs
				$bolReturn = true;

				// Report
				$arrReportLine['<Action>'] = "Normalising";
				$arrReportLine['<SeqNo>'] = $arrCDR['SequenceNo'];
				$arrReportLine['<FileName>'] = TruncateName($arrCDR['FileName'], MSG_MAX_FILENAME_LENGTH);

				// Is there a normalisation module for this type?
				if ($this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]]) {
					// normalise
					switch ($arrCDR['Status']) {
						case CDR_FIND_OWNER:
							$arrCDR = $this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]]->FindOwner($arrCDR);
							break;

						case CDR_READY:
						case CDR_RENORMALISE:
						default:
							$arrCDR = $this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]]->Normalise($arrCDR);
							break;
					}
				} else {
					// there is no normalisation module for this type, report an error
					//new ExceptionVixen("No normalisation module for carrier" . $arrCDR["CDR.Carrier"] . ".", $this->errErrorHandler, NO_NORMALISATION_MODULE);
					//$this->AddToNormalisationReport(CDR_NORMALISATION_FAIL, $arrCDR["CDR.CDRFilename"] . "(" . $arrCDR["CDR.SequenceNo"] . ")", $strReason = "No normalisation module for carrierNo normalisation module for carrierNo normalisation module for carrier");
					CliEcho("There is no Normalisation module for File Type {$arrCDR['FileType']}");
					// set the CDR status
					$arrCDR['Status'] = CDR_CANT_NORMALISE_NO_MODULE;
				}

				if ($arrCDR['Status'] != CDR_NORMALISED && $arrCDR['Status'] != CDR_CANT_NORMALISE_HEADER && $arrCDR['Status'] != CDR_CANT_NORMALISE_NON_CDR) {
					$this->rptNormalisationReport->AddMessageVariables(MSG_LINE, $arrReportLine, false);
				}

				// Report
				$arrMatchCDR = array();
				foreach ($arrCDR as $strField=>$mixValue) {
					if ($mixValue === null) {
						$mixValue = 'NULL';
					} elseif (is_string($mixValue)) {
						$mixValue = "'".str_replace("'", '\\\'', $mixValue)."'";
					}
					$arrMatchCDR[$strField] = $mixValue;
				}
				$strFindDuplicateSQL = "
					SELECT Id, CASE WHEN CarrierRef <=> {$arrMatchCDR['CarrierRef']} THEN ".CDR_DUPLICATE." ELSE ".CDR_RECHARGE." END AS Status
					FROM CDR
					WHERE Id != {$arrMatchCDR['Id']} AND
					FNN = {$arrMatchCDR['FNN']} AND
					Source <=> {$arrMatchCDR['Source']} AND
					Destination <=> {$arrMatchCDR['Destination']} AND
					StartDatetime <=> {$arrMatchCDR['StartDatetime']} AND
					EndDatetime <=> {$arrMatchCDR['EndDatetime']} AND
					Units = {$arrMatchCDR['Units']} AND
					Cost = {$arrMatchCDR['Cost']} AND
					RecordType = {$arrMatchCDR['RecordType']} AND
					RecordType NOT IN (10, 15, 33, 21) AND
					Credit = {$arrMatchCDR['Credit']} AND
					Description <=> {$arrMatchCDR['Description']} AND
					Status NOT IN (".CDR_DUPLICATE.", ".CDR_RECHARGE.")
					ORDER BY Id DESC
					LIMIT 1
				";
				switch ($arrCDR['Status']) {
					case CDR_CANT_NORMALISE_NO_MODULE:
						$arrAliases['<Module>'] = $arrCDR["FileType"];
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_MODULE, $arrAliases);
						break;

					case CDR_CANT_NORMALISE_BAD_SEQ_NO:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Bad Sequence Number"));
						break;

					case CDR_CANT_NORMALISE_HEADER:
						//$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Header Row"));
						break;

					case CDR_CANT_NORMALISE_NON_CDR:
						//$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Non-CDR"));
						break;

					case CDR_BAD_OWNER:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Cannot match owner"));
						$arrDelinquents[$this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]]->strFNN]++;
						$intDelinquents++;

						// If this is a duplicate, make sure people cannot assign this CDR to an Account
						//if ($this->_selFindDuplicate->Execute($arrCDR))
						$mixResult = $qryQuery->Execute($strFindDuplicateSQL);
						if ($mixResult === false) {
							throw new Exception_Database($qryQuery->Error()."\n\n{$strFindDuplicateSQL}");
						} elseif ($arrDuplicateCDR = $mixResult->fetch_assoc()) {
							$strMatchString = GetConstantDescription($arrDuplicateCDR['Status'], 'CDR');
							CliEcho("!!! Bad Owner CDR #{$arrCDR['Id']} is a {$strMatchString} of #{$arrDuplicateCDR['Id']}");
							$arrCDR['Status'] = $arrDuplicateCDR['Status'];
						}
						break;

					case CDR_CANT_NORMALISE_INVALID:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Normalised Data Invalid"));
						break;

					case CDR_CANT_NORMALISE_RAW:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Raw Data Invalid"));
						break;

					case CDR_BAD_DESTINATION:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Destination not found"));
						break;

					case CDR_BAD_RECORD_TYPE:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Record Type Invalid"));
						break;

					case CDR_NORMALISED:
						// Normalised OK
						//if ($this->_selFindDuplicate->Execute($arrCDR))
						$mixResult = $qryQuery->Execute($strFindDuplicateSQL);
						if ($mixResult === false) {
							throw new Exception_Database($qryQuery->Error()."\n\n{$strFindDuplicateSQL}");
						} elseif ($arrDuplicateCDR = $mixResult->fetch_assoc()) {
							$strMatchString = GetConstantDescription($arrDuplicateCDR['Status'], 'CDR');
							CliEcho("!!! Normalised CDR #{$arrCDR['Id']} is a {$strMatchString} of #{$arrDuplicateCDR['Id']}");
							$arrCDR['Status'] = $arrDuplicateCDR['Status'];
						} else {
							$intNormalisePassed++;
						}
						break;

					default:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, array('<Reason>' => "Unknown Error"));
						break;
				}

				$arrCDR['NormalisedOn'] = new MySQLFunction("NOW()");
				// Save CDR back to the DB
				if ($this->_updUpdateCDRs->Execute($arrCDR, array("CdrId" => $arrCDR['Id'])) === false) {

				}
			}

			// Generate Delinquent Report
			$strDelinquentText = "\n[ Delinquent FNNs ]\n\n";
			if (is_array($arrDelinquents)) {
				foreach($arrDelinquents as $strKey=>$strValue) {
					$strDelinquentText .= "\t+ {$strKey} was referenced {$strValue} time(s)\n";
				}
				$strDelinquentText .= "\n\tThere were ".count($arrDelinquents)." delinquent FNNs in this run.\n";
			} else {
				$strDelinquentText .= "\n\tThere were no deliquent FNNs in this run.\n\n";
			}
			$this->rptDelinquentsReport->AddMessage(MSG_HORIZONTAL_RULE.$strDelinquentText.MSG_HORIZONTAL_RULE);

			// ASSERTION: Each File is at least 95% correct (ie, not Normalised or Delinquent)
			foreach (array_keys($arrFilesTouched) as $intFileImportId) {
				$strPercentageValidSQL = "
					SELECT		CDR.Status, COUNT(CDR.Id) AS cdr_count
					FROM		CDR
								JOIN FileImport ON FileImport.Id = CDR.File
					WHERE		CDR.File = {$intFileImportId}
					GROUP BY	CDR.Status
				";
				$resPercentageValid = $qryQuery->Execute($strPercentageValidSQL);
				if ($resPercentageValid === false) {
					throw new Exception_Database($qryQuery->Error());
				}

				$intTotalCDRs = 0;
				$intErrorCDRs = 0;
				$strPercentageDebug = "\n\nFile #{$intFileImportId}\n";
				while ($arrPercentageValid = $resPercentageValid->fetch_assoc()) {
					$intTotalCDRs += $arrPercentageValid['cdr_count'];
					if (in_array($arrPercentageValid['Status'], $this->_arrCDRErrorStatuses)) {
						// Error Status Code
						$intErrorCDRs += $arrPercentageValid['cdr_count'];
					}

					$strPercentageDebug	.= "({$arrPercentageValid['Status']})".GetConstantDescription($arrPercentageValid['Status'], 'CDR')."\t: {$arrPercentageValid['cdr_count']}\n";
				}
				$strPercentageDebug	.=	"\n" .
										"Total CDRs\t\t: {$intTotalCDRs}\n" .
										"Error CDRs\t\t: {$intErrorCDRs}";

				if ($intTotalCDRs) {
					$fltPercentageValid = ($intTotalCDRs - $intErrorCDRs) / $intTotalCDRs;
					try {
						if (Flex::assert(
							$fltPercentageValid >= self::FILE_MINIMUM_PERCENT_VALID,
							"CDR File #{$intFileImportId} has too many invalid CDRs",
							$strPercentageDebug,
							"CDR File Minimum Percentage Valid"
						)) {
							$this->AddToNormalisationReport($strPercentageDebug);
						}
					} catch (Exception_Assertion $eException) {
						$this->AddToNormalisationReport($eException->getMessage());
					}
				}
			}

			// Normalisation Report totals
			$arrReportLine['<Action>'] = "Normalised";
			$arrReportLine['<Total>'] = (int)$intNormaliseTotal;
			$arrReportLine['<Time>'] = $this->Framework->LapWatch();
			$arrReportLine['<Pass>'] = (int)$intNormalisePassed;
			$arrReportLine['<Fail>'] = (int)$intNormaliseTotal - $intNormalisePassed;
			$this->AddToNormalisationReport(MSG_REPORT, $arrReportLine);
			$this->AddToNormalisationReport($strDelinquentText);
			$this->AddToNormalisationReport(MSG_HORIZONTAL_RULE);
			$this->AddToNormalisationReport("Normalisation module completed in ".$this->Framework->uptime()." seconds");

			// Deliver the reports
			$this->rptNormalisationReport->Finish();
			$this->rptDelinquentsReport->Finish();

			// Commit the Transaction
			DataAccess::getDataAccess()->TransactionCommit();

			// Return number normalised or FALSE
			return ($bolReturn) ? $intNormaliseTotal : false;
		} catch (Exception $eException) {
			DataAccess::getDataAccess()->TransactionRollback();
			throw $eException;
		}
	}

	function ReNormalise($intStatus) {
		$intStatus = (int)$intStatus;
		$arrColumns['Status'] = CDR_RENORMALISE;
		$updReRate = new StatementUpdate("CDR", "Status = {$intStatus}", $arrColumns);
		$mixReturn = $updReRate->Execute($arrColumns, null);
		return (int)$mixReturn;
	}

	function ReFindOwner($intStatus) {
		$intStatus = (int)$intStatus;
		$arrColumns['Status'] = CDR_FIND_OWNER;
		$updReRate = new StatementUpdate("CDR", "Status = {$intStatus}", $arrColumns);
		$mixReturn = $updReRate->Execute($arrColumns, null);
		return (int)$mixReturn;
	}

	function MatchCredits() {
		// Find the normalised Credit CDRs
		if (($intTotalCount = $this->_selCreditCDRs->Execute()) === false) {
			Debug("Could not select credit CDRS!");
			return false;
		}

		// Attempt to match the CDRs up
		$intCount = false;
		while ($arrCreditCDR = $this->_selCreditCDRs->Fetch()) {
			$bolFail = false;
			if ($intCount === false) {
				$intCount = 0;
			}
			// Find matching Debit
			if ($this->_selDebitCDR->Execute($arrCreditCDR)) {
				$arrDebitCDR = $this->_selDebitCDR->Fetch();
			} else {
				// Try to match credit to a rated CDR
				/*if ($this->_selRatedCDR->Execute($arrCreditCDR)) {
					$arrDebitCDR = $this->_selRatedCDR->Fetch();

					//unrate the CDR
					/*$bolResult = $this->Framework->UnRateCDR($arrDebitCDR['Id'], CDR_DEBIT_MATCHED);
					if (!$bolResult) {
						$bolFail = true;
					}*/
				/*} else {
					$bolFail = true;
				}*/
				$bolFail = true;
			}

			if ($bolFail === true) {
					//TODO!rich! add to report
					//echo " ! Couldn't Match Debit to Credit with CDR.Id {$arrCreditCDR['Id']}!\n";

					// Update the Credit CDR
					$arrUpdateColumns['Id'] = $arrCreditCDR['Id'];
					$arrUpdateColumns['Status'] = CDR_CREDIT_MATCH_NOT_FOUND;
					$this->_ubiCDRStatus->Execute($arrUpdateColumns);
					continue;
			}

			// Add to the link table
			$arrInsertColumns = array();
			$arrInsertColumns['credit_cdr_id'] = $arrCreditCDR['Id'];
			$arrInsertColumns['debit_cdr_id'] = $arrDebitCDR['Id'];
			$this->_insCreditLink->Execute($arrInsertColumns);

			// Update the Credit CDR
			$arrUpdateColumns = array();
			$arrUpdateColumns['Id'] = $arrCreditCDR['Id'];
			$arrUpdateColumns['Status'] = CDR_CREDIT_MATCHED;
			$this->_ubiCDRStatus->Execute($arrUpdateColumns);

			// Update the Debit CDR
			$arrUpdateColumns = array();
			$arrUpdateColumns['Id'] = $arrDebitCDR['Id'];
			$arrUpdateColumns['Status'] = CDR_DEBIT_MATCHED;
			$this->_ubiCDRStatus->Execute($arrUpdateColumns);

			$intCount++;
		}

		//TODO!rich! add to report
		echo "Matched {$intCount} out of {$intTotalCount} credit CDRs.\n";
		return $intCount;
	}
}