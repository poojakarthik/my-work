 <?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// application
//----------------------------------------------------------------------------//
/**
 * application
 *
 * Contains all classes for the application
 *
 * Contains all classes for the application
 *
 * @file		application.php
 * @language	PHP
 * @package		normalisation_application
 * @author		Rich Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */




//----------------------------------------------------------------------------//
// ApplicationNormalise
//----------------------------------------------------------------------------//
/**
 * ApplicationNormalise
 *
 * Normalisation Module
 *
 * Normalisation Module
 *
 *
 * @prefix		app
 *
 * @package		normalisation_application
 * @class		ApplicationNormalise
 */
 class ApplicationNormalise extends ApplicationBaseClass
 {
 	//------------------------------------------------------------------------//
	// errErrorHandler
	//------------------------------------------------------------------------//
	/**
	 * errErrorHandler
	 *
	 * Application Error Handler instance
	 *
	 * Application Error Handler instance
	 *
	 * @type		ErrorHandler
	 *
	 * @property
	 */
	public $errErrorHandler;
	
	//------------------------------------------------------------------------//
	// rptNormalisationReport
	//------------------------------------------------------------------------//
	/**
	 * rptNormalisationReport
	 *
	 * Normalisation report
	 *
	 * Normalisation Report, including information on errors, failed import
	 * and normalisations, and a total of each
	 *
	 * @type		Report
	 *
	 * @property
	 */
	public $rptNormalisationReport;
	
	//------------------------------------------------------------------------//
	// arrDelinquents
	//------------------------------------------------------------------------//
	/**
	 * arrDelinquents
	 *
	 * Delinquent phone numbers
	 *
	 * Delinquent phone numbers.  Is a set, so a phone number can only appear once.
	 *
	 * @type		array
	 *
	 * @property
	 */
	public $_arrDelinquents;
	
	//------------------------------------------------------------------------//
	// arrNormaliseReportCount
	//------------------------------------------------------------------------//
	/**
	 * arrNormaliseReportCount
	 *
	 * Counts the different types of report messages
	 *
	 * Counts the different types of report messages.  Associative array where the
	 * key is the message type and the
	 *
	 * @type		array
	 *
	 * @property
	 */
	public $_arrNormaliseReportCount;
 	
	//------------------------------------------------------------------------//
	// _arrNormalisationModule
	//------------------------------------------------------------------------//
	/**
	 * _arrNormalisationModule
	 *
	 * Array of normalisation modules
	 *
	 * Array of normalisation modules
	 *
	 * @type		array
	 *
	 * @property
	 */
	public $_arrNormalisationModule;
	
	//------------------------------------------------------------------------//
	// _intImportPass
	//------------------------------------------------------------------------//
	/**
	 * _intImportPass
	 *
	 * Number of imports that passed
	 *
	 * Number of imports that passed
	 *
	 * @type		integer
	 *
	 * @property
	 */
	private $_intImportPass;
	
	//------------------------------------------------------------------------//
	// _intImportFail
	//------------------------------------------------------------------------//
	/**
	 * _intImportFail
	 *
	 * Number of imports that failed
	 *
	 * Number of imports that failed
	 *
	 * @type		integer
	 *
	 * @property
	 */
	private $_intImportFail;
	
	//------------------------------------------------------------------------//
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Normalising Application
	 *
	 * Constructor for the Normalising Application
	 *
	 * @param	mixed	$$mixEmailAddress	Array or string of Address(es) to send report to
	 * @return		ApplicationNormalise
	 *
	 * @method
	 */
 	function __construct($mixEmailAddress)
 	{
		parent::__construct();
		
	 	// Initialise framework components
		$this->rptNormalisationReport	= new Report("Normalisation Report for " . date("Y-m-d H:i:s"), "rich@voiptelsystems.com.au");
		$this->rptDelinquentsReport		= new Report("Delinquents Report for ". date("Y-m-d H:i:s"), $$mixEmailAddress, FALSE);
		$this->errErrorHandler			= new ErrorHandler();
		
		/*
		// Create an instance of each Normalisation module (OLD METHOD)
 		$this->_arrNormalisationModule[CDR_UNITEL_RSLCOM]		= new NormalisationModuleRSLCOM();
 		$this->_arrNormalisationModule[CDR_UNTIEL_SE]			= new NormalisationModuleRSLCOM();
 		//$this->_arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
 		$this->_arrNormalisationModule[CDR_UNITEL_COMMANDER]	= new NormalisationModuleCommander();
 		$this->_arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
 		$this->_arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();
 		$this->_arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleOptus();	// Uses Optus' format
 		*/
 		
 		// Load CDR Normalisation CarrierModules
 		CliEcho(" * NORMALISATION MODULES");
 		$this->_selCarrierModules->Execute(Array('Type' => MODULE_TYPE_NORMALISATION_CDR));
 		while ($arrModule = $this->_selCarrierModules->Fetch())
 		{
 			$this->_arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
 			CliEcho("\t + ".GetConstantDescription($arrModule['Carrier'], 'Carrier')." : ".$this->_arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
 		}
		
		
		// STATEMENTS
		$this->_selCreditCDRs = new StatementSelect("CDR", "Id, FNN, Source, Destination, Cost, Units, StartDatetime", "Credit = 1 AND Status = ".CDR_RATED, NULL, "1000");
		
		$strStatus = " AND (Status = ".CDR_RATED." OR Status = ".CDR_NORMALISED." OR Status = ".CDR_BAD_OWNER." OR Status = ".CDR_BAD_RECORD_TYPE." OR Status = ".CDR_BAD_DESTINATION." OR Status = ".CDR_FIND_OWNER." OR Status = ".CDR_RENORMALISE." OR Status = ".CDR_RATE_NOT_FOUND.")";
		$this->_selDebitCDR = new StatementSelect("CDR", "Id", "Id != <Id> AND FNN = <FNN> AND Source = <Source> AND Destination = <Destination> AND Cost = <Cost> AND Units = <Units> AND StartDatetime = <StartDatetime> $strStatus", NULL, 1);
	 	//$this->_selRatedCDR = new StatementSelect("CDR", "Id", "Id != <Id> AND FNN = <FNN> AND Source = <Source> AND Destination = <Destination> AND Cost = <Cost> AND Units = <Units> AND StartDatetime = <StartDatetime> AND Status = ".CDR_RATED, NULL, 1);
		
		$arrUpdateColumns = Array();
 		$arrUpdateColumns['Status']	= '';
 		$this->_ubiCDRStatus = new StatementUpdateById("CDR", $arrUpdateColumns);
		
		$this->_insCreditLink = new StatementInsert("cdr_credit_link");
		
		// Update CDR Query
		$arrDefine = $this->db->FetchClean("CDR");
		$arrDefine['NormalisedOn'] = new MySQLFunction("NOW()");
 		$this->_updUpdateCDRs = new StatementUpdate("CDR", "Id = <CdrId>", $arrDefine);
		
		$this->_arrDelinquents = Array();
		
		// Duplicate CDR Query
	 	/*$this->_selFindDuplicate	= new StatementSelect(	"CDR",
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
															NULL,
															1);*/
 	}


	//------------------------------------------------------------------------//
	// Import
	//------------------------------------------------------------------------//
	/**
	 * Import()
	 *
	 * Imports CDRs from CDR Files
	 *
	 * Imports CDRs from CDR Files
	 * 
	 * @param	integer	$intLimit	optional	Limits the number of files processed
	 *
	 * @return	void
	 *
	 * @method
	 */
 	function Import($intLimit=NULL)
 	{
		// Start a Transaction
		DataAccess::getDataAccess()->TransactionStart();
		
		try
		{
			$this->AddToNormalisationReport("\n\n".MSG_HORIZONTAL_RULE);
			$this->AddToNormalisationReport(MSG_IMPORTING_TITLE);
			$this->Framework->StartWatch();
			
			// Retrieve list of CDR Files marked as either ready to process, or failed process
			$arrFileTypes	= Array();
			foreach ($this->_arrNormalisationModule as $intCarrier=>$arrCarrierFileTypes)
			{
				foreach (array_keys($arrCarrierFileTypes) as $intFileType)
				{
					$arrFileTypes[]	= $intFileType;
				}
			}
			
			// Do we have any FileTypes to import?
			if (!count($arrFileTypes))
			{
				return FALSE;
			}
			
			$strWhere			= "FileType IN (".implode(', ', $arrFileTypes).") AND Status IN (".FILE_COLLECTED.", ".FILE_REIMPORT.")";
			$selSelectCDRFiles 	= new StatementSelect("FileImport JOIN compression_algorithm ON FileImport.compression_algorithm_id = compression_algorithm.id", "FileImport.*, compression_algorithm.file_extension, compression_algorithm.php_stream_wrapper", $strWhere, NULL, $intLimit);
			
			$insInsertCDRLine	= new StatementInsert("CDR");
			
			$arrDefine = Array();
			$arrDefine['Status']		= TRUE;
			$arrDefine['ImportedOn'] 	= new MySQLFunction("NOW()");
			$updUpdateCDRFiles			= new StatementUpdate("FileImport", "Id = <id>", $arrDefine);
			
			
			if ($selSelectCDRFiles->Execute() === FALSE)
			{
				Debug($selSelectCDRFiles);
				exit(1);
			}
			
			$intCount = 0;
			$this->_intImportFail = 0;
			$this->_intImportPass = 0;
			
			// Loop through the CDR File entries
			while ($arrCDRFile = $selSelectCDRFiles->Fetch())
			{
				// Make sure the file exists
				if (!file_exists($arrCDRFile['Location']))
				{
					// Report the error, and UPDATE the database with a new status, then move to the next file
					new ExceptionVixen("Specified CDR File doesn't exist", $this->_errErrorHandler, CDR_FILE_DOESNT_EXIST);
					$arrCDRFile["Status"] = FILE_IMPORT_FAILED;
					if ($updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"])) === FALSE)
					{
	
					}
					
					// Add to the Normalisation report
					$this->AddToNormalisationReport(MSG_FAIL_FILE_MISSING, Array('<Path>' => $arrCDRFile["Location"]));
					continue;
				}
				
				// Determine exact status, and act accordingly
				switch($arrCDRFile["Status"])
				{
					/*
					case FILE_REIMPORT:
						$this->CascadeDeleteCDRs();
					*/
					case FILE_COLLECTED:
						$this->InsertCDRFile($arrCDRFile, $insInsertCDRLine, $updUpdateCDRFiles);
	
						break;
					default:
						new ExceptionVixen("Unexpected CDR File Status", $this->_errErrorHandler, UNEXPECTED_CDRFILE_STATUS);
				}
				
				$intCount++;
			}
			
			// Report totals
			$arrReportLine['<Action>']		= "Imported";
			$arrReportLine['<Total>']		= $this->_intImportPass + $this->_intImportFail;
			$arrReportLine['<Time>']		= $this->Framework->LapWatch();
			$arrReportLine['<Pass>']		= (int)$this->_intImportPass;
			$arrReportLine['<Fail>']		= (int)$this->_intImportFail;
			$this->AddToNormalisationReport(MSG_REPORT, $arrReportLine);
		}
		catch (Exception $eException)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			throw $eException;
		}
		
		// Commit the Transaction
		DataAccess::getDataAccess()->TransactionCommit();
 	}
 	
	//------------------------------------------------------------------------//
	// DeleteCDRsByFile
	//------------------------------------------------------------------------//
	/**
	 * DeleteCDRsByFile()
	 *
	 * Deletes all data in the DB associated with a particular CDR File
	 *
	 * Deletes all data in the DB associated with a particular CDR File
	 *
	 * @param	integer		$intFileImportId		Delete CDRs from this File
	 * 
	 * @return	boolean								FALSE: error or unable to delete
	 *
	 * @method
	 */
 	function DeleteCDRsByFile($intFileImportId)
 	{
		// if TEMP_INVOICE or INVOICED return false
		$selCanDeleteCDRs = new StatementSelect("CDR", "COUNT(Id)", "File = $intFileImportId AND (Status = ".CDR_TEMP_INVOICE." OR Status = ".CDR_INVOICED.")");
		if ($selCanDeleteCDRs->Execute() === FALSE)
		{

		}
		$arrResult = $selCanDeleteCDRs->Fetch();
		if ($arrResult[0] > 0)
		{
			return FALSE;
		}
		
		// remove cdrs
		$qryDeleteCDRs = new Query();
		$intResult = $qryDeleteCDRs->Execute("DELETE FROM CDR WHERE File = ".$intFileImportId);
		if ($intResult == FALSE)
		{

		}
		return $intResult;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// CascadeDeleteCDRFile
	//------------------------------------------------------------------------//
	/**
	 * CascadeDeleteCDRFile()
	 *
	 * Deletes a CDR File from the DB, and all associated CDRs
	 *
	 * Deletes a CDR File from the DB, and all associated CDRs
	 *
	 * @param	integer		$intFileImportId		Delete this File
	 * 
	 * @return	boolean								FALSE: error or unable to delete
	 *
	 * @method
	 */
 	function CascadeDeleteCDRFile($intFileImportId)
 	{
		// Delete all associated CDRs
		if ($this->DeleteCDRsByFile($intFileImportId) === FALSE)
		{
			return FALSE;
		}
		
		// Delete CDR File entry in FileImport
		$qryDeleteCDRFile = new Query();
		$intResult = $qryDeleteCDRFile->Execute("DELETE FROM FileImport WHERE Id = ".$intFileImportId);
		if ($intResult === FALSE)
		{

		} 
		return $intResult;
 	}
 	
 	
	//------------------------------------------------------------------------//
	// InsertCDRFile
	//------------------------------------------------------------------------//
	/**
	 * InsertCDRFile()
	 *
	 * Inserts a CDR File to the database
	 *
	 * Inserts a CDR File to the database
	 *
	 * @param	array		$arrCDRFile			Associative array of data returned
	 * 											from a SELECT * statement on this file
	 * 
	 * @return	integer							Number of CDRs imported
	 *
	 * @method
	 */
 	function InsertCDRFile($arrCDRFile, $insInsertCDRLine, $updUpdateCDRFiles)
 	{
		unset($arrCDRFile['NormalisedOn']);

		// Report
		$this->rptNormalisationReport->AddMessage("\tImporting ".TruncateName($arrCDRFile['FileName'], 30)."...");
		
		try
		{
			// Set the File status to "Importing"
			$arrCDRFile["Status"] = FILE_IMPORTING;
			$arrCDRFile['ImportedOn'] 	= new MySQLFunction("NOW()");
			if ($updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"])) === FALSE)
			{

			}
			
			// Set fields that are consistent over all CDRs for this file
			$arrCDRLine["File"]			= $arrCDRFile["Id"];
			$arrCDRLine["Carrier"]		= $arrCDRFile["Carrier"];
			
			// check for a preprocessor
			$bolPreprocessor = FALSE;
			if ($this->_arrNormalisationModule[$arrCDRFile['Carrier']][$arrCDRFile["FileType"]])
			{
				if (method_exists ($this->_arrNormalisationModule[$arrCDRFile['Carrier']][$arrCDRFile["FileType"]], "Preprocessor" ))
				{
					$bolPreprocessor = TRUE;
				}
			}
			
			// Insert every CDR Line into the database
			$fileCDRFile	= fopen($arrCDRFile['php_stream_wrapper'].$arrCDRFile['Location'], "r");
			$intSequence	= 1;
			while (!feof($fileCDRFile))
			{
				// Add to report <Action> CDR <SeqNo> from <FileName>...");
				$arrReportLine['<Action>']		= "Importing";
				$arrReportLine['<SeqNo>']		= $intSequence;
				$arrReportLine['<FileName>']	= TruncateName($arrCDRFile['FileName'], MSG_MAX_FILENAME_LENGTH);
				//$this->AddToNormalisationReport(MSG_LINE, $arrReportLine);
				
				$arrCDRLine["SequenceNo"]	= $intSequence;
				$arrCDRLine["Status"]		= CDR_READY;
				
				// run Preprocessor
				if ($bolPreprocessor)
				{
					$arrCDRLine["CDR"]		= $this->_arrNormalisationModule[$arrCDRFile['Carrier']][$arrCDRFile["FileType"]]->Preprocessor(fgets($fileCDRFile));
				}
				else
				{
					$arrCDRLine["CDR"]		= fgets($fileCDRFile);
				}
				
				if (trim($arrCDRLine["CDR"]))
				{
					$insInsertCDRLine->Execute($arrCDRLine);
					if ($insInsertCDRLine->Error())
					{
						// error inserting

					}
				}
				$intSequence++;
				
				// Report
				//$this->AddToNormalisationReport(MSG_OK);
				
				$this->_intImportPass++;
				
				// Break here for fast normalisation test
				if (FAST_NORMALISATION_TEST === TRUE && $intSequence > 100)
				{
					break;
				}
			}
			fclose($fileCDRFile);
			
			// Set the File status to "Normalised"
			$arrCDRFile["Status"]		= FILE_IMPORTED;
			//$arrCDRFile['NormalisedOn'] = new MySQLFunction("Now()");
			if ($updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"])) === FALSE)
			{

			}
		}
		catch (ExceptionVixen $exvException)
		{
			$errErrorHandler->PHPExceptionCatcher($exvException);
			
			// Set the File status to "Failed"
			$arrCDRFile["Status"] = FILE_IMPORT_FAILED;
			if ($updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"])) === FALSE)
			{
				$updUpdateCDRFiles->Error();
			}
			
			// Report
			//$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_CORRUPT);
		}
		
		// Report
		$intPassed = $this->_intImportPass;
		$intFailed = ($intSequence - 1) - $intPassed;
		$this->_intImportFail += $intFailed; 
		$this->rptNormalisationReport->AddMessage("\t$intPassed passed, $intFailed failed.");
		
		return $intSequence;
 	}
 
 	//------------------------------------------------------------------------//
	// AddToNormalisationReport
	//------------------------------------------------------------------------//
	/**
	 * AddToNormalisationReport()
	 *
	 * Adds a message to the normalisation report
	 *
	 * Adds a message to the normalisation report
	 *
	 * @param	string		$strMessage			The message - use constants
	 * 											from definition.php.
	 * @param	array		$arrAliases			Associative array of alises.
	 * 											MUST use the same aliases as used in the 
	 * 											constant being used.  Key is the alias (including the <>'s)
	 * 											, and the Value is the value to be inserted.
	 * 
	 * @method
	 */
 	function AddToNormalisationReport($strMessage, $arrAliases = Array())
 	{
 		foreach ($arrAliases as $arrAlias => $arrValue)
 		{
 			$strMessage = str_replace($arrAlias, $arrValue, $strMessage);
 		}
 		
 		$this->rptNormalisationReport->AddMessage($strMessage, FALSE);
 	}
	
	//------------------------------------------------------------------------//
	// Normalise
	//------------------------------------------------------------------------//
	/**
	 * Normalise()
	 *
	 * Normalises new CDRs
	 *
	 * Normalises new CDRs
	 *
	 * @param	integer	$intRemaining	optional	Number of CDRs left to normalise [NULL]
	 * @param	bool	$bolOnlyNew		optional	Only normalises new CDRs [FALSE]
	 *
	 * @return	bool	returns true until all CDRs have been normalised
	 *
	 * @method
	 */
 	function Normalise($intRemaining = NULL, $bolOnlyNew = FALSE)
 	{
		// Start a Transaction
		DataAccess::getDataAccess()->TransactionStart();
		
		try
		{
			// Only new CDRs?
			if ($bolOnlyNew)
			{
				$strWhere	= "CDR.Status = ".CDR_READY;
			}
			else
			{
				$strWhere	= "CDR.Status = ".CDR_READY." OR CDR.Status = ".CDR_FIND_OWNER." OR CDR.Status = ".CDR_RENORMALISE." OR CDR.Status = ".CDR_NORMALISE_NOW;
			}
			
			// Select CDR Query
			$strTables	= "CDR INNER JOIN FileImport ON CDR.File = FileImport.Id";
			$mixColumns	= Array("CDR.*" => "", "FileType" => "FileImport.FileType", "FileName" => "FileImport.FileName");
			//$strOrder	= "CDR.Status";
			$strOrder	= NULL;
			$intLimit	= ($intRemaining < 1000 && $intRemaining > 0) ? $intRemaining : 1000;
	 		$this->_selSelectCDRs = new StatementSelect($strTables, $mixColumns, $strWhere, $strOrder, $intLimit);
	 		
	 		// Select all CDRs ready to be Normalised
			if ($this->_selSelectCDRs->Execute() === FALSE)
			{
	
			}
	 		$arrCDRList = $this->_selSelectCDRs->FetchAll();
	 		
			// we will return FALSE if there are no CDRs to normalise
			$bolReturn = FALSE;		
	
			// Report
			$this->rptNormalisationReport->AddMessage(MSG_NORMALISATION_TITLE);
			
			$this->Framework->StartWatch();
			
			$intNormalisePassed	= 0;
			$intNormaliseTotal	= 0;
			
			$intDelinquents = 0;
			$arrDelinquents = Array();
	
			$qryQuery	= new Query();
	 		foreach ($arrCDRList as $arrCDR)
	 		{
				$intNormaliseTotal++;
				
				// return TRUE if we have normalised (or tried to normalise) any CDRs
				$bolReturn = TRUE;
				
				// Report
				$arrReportLine['<Action>']		= "Normalising";
				$arrReportLine['<SeqNo>']		= $arrCDR['SequenceNo'];
				$arrReportLine['<FileName>']	= TruncateName($arrCDR['FileName'], MSG_MAX_FILENAME_LENGTH);
				
	 			// Is there a normalisation module for this type?
				if ($this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]])
				{
					// normalise
					switch ($arrCDR['Status'])
					{
						case CDR_FIND_OWNER:
							$arrCDR = $this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]]->FindOwner($arrCDR);
							break;
						case CDR_READY:
						case CDR_RENORMALISE:
						default:
							$arrCDR = $this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]]->Normalise($arrCDR);
							break;
					}
				}
				else
				{
					// there is no normalisation module for this type, report an error
	 				//new ExceptionVixen("No normalisation module for carrier" . $arrCDR["CDR.Carrier"] . ".", $this->_errErrorHandler, NO_NORMALISATION_MODULE);
					//$this->AddToNormalisationReport(CDR_NORMALISATION_FAIL, $arrCDR["CDR.CDRFilename"] . "(" . $arrCDR["CDR.SequenceNo"] . ")", $strReason = "No normalisation module for carrierNo normalisation module for carrierNo normalisation module for carrier");
					CliEcho("There is no Normalisation module for File Type {$arrCDR['FileType']}");
					// set the CDR status
					$arrCDR['Status'] = CDR_CANT_NORMALISE_NO_MODULE;
				}
				
				if ($arrCDR['Status'] != CDR_NORMALISED && $arrCDR['Status'] != CDR_CANT_NORMALISE_HEADER && $arrCDR['Status'] != CDR_CANT_NORMALISE_NON_CDR)
				{
					$this->rptNormalisationReport->AddMessageVariables(MSG_LINE, $arrReportLine, FALSE);
				}
				
				// Report
				$arrMatchCDR	= Array();
				foreach ($arrCDR as $strField=>$mixValue)
				{
					if ($mixValue === NULL)
					{
						$mixValue	= 'NULL';
					}
					elseif (is_string($mixValue))
					{
						$mixValue	= "'".str_replace("'", '\\\'', $mixValue)."'";
					}
					$arrMatchCDR[$strField]	= $mixValue;
				}
				$strFindDuplicateSQL	= "SELECT Id, CASE WHEN CarrierRef <=> {$arrMatchCDR['CarrierRef']} THEN ".CDR_DUPLICATE." ELSE ".CDR_RECHARGE." END AS Status 
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
											LIMIT 1";
				switch ($arrCDR['Status'])
				{
					case CDR_CANT_NORMALISE_NO_MODULE:
						$arrAliases['<Module>'] = $arrCDR["FileType"];
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_MODULE, $arrAliases);
						break;
					case CDR_CANT_NORMALISE_BAD_SEQ_NO:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Bad Sequence Number"));
						break;
					case CDR_CANT_NORMALISE_HEADER:
						//$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Header Row"));
						break;
					case CDR_CANT_NORMALISE_NON_CDR:
						//$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Non-CDR"));
						break;
					case CDR_BAD_OWNER:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Cannot match owner"));
						$arrDelinquents[$this->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR["FileType"]]->strFNN]++;
						$intDelinquents++;
						
						// If this is a duplicate, make sure people cannot assign this CDR to an Account
						//if ($this->_selFindDuplicate->Execute($arrCDR))
						$mixResult = $qryQuery->Execute($strFindDuplicateSQL);
						if ($arrDuplicateCDR = $mixResult->fetch_assoc())
						{
							$strMatchString			= GetConstantDescription($arrDuplicateCDR['Status'], 'CDR');
							CliEcho("!!! Bad Owner CDR #{$arrCDR['Id']} is a {$strMatchString} of #{$arrDuplicateCDR['Id']}");
							$arrCDR['Status']		= $arrDuplicateCDR['Status'];
						}
						elseif ($mixResult === FALSE)
						{
							throw new Exception($qryQuery->Error());
						}
						break;
					case CDR_CANT_NORMALISE_INVALID:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Normalised Data Invalid"));
						break;
					case CDR_CANT_NORMALISE_RAW:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Raw Data Invalid"));
						break;
					case CDR_BAD_DESTINATION:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Destination not found"));
						break;
					case CDR_BAD_RECORD_TYPE:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Record Type Invalid"));
						break;
					case CDR_NORMALISED:
						// Normalised OK
						//if ($this->_selFindDuplicate->Execute($arrCDR))
						$mixResult = $qryQuery->Execute($strFindDuplicateSQL);
						if ($arrDuplicateCDR = $mixResult->fetch_assoc())
						{
							$strMatchString			= GetConstantDescription($arrDuplicateCDR['Status'], 'CDR');
							CliEcho("!!! Normalised CDR #{$arrCDR['Id']} is a {$strMatchString} of #{$arrDuplicateCDR['Id']}");
							$arrCDR['Status']		= $arrDuplicateCDR['Status'];
						}
						elseif ($mixResult === FALSE)
						{
							throw new Exception($qryQuery->Error());
						}
						else
						{
							$intNormalisePassed++;
						}
						break;
					default:
						$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Unknown Error"));
						break;
				}
				
				$arrCDR['NormalisedOn'] = new MySQLFunction("NOW()");
	 			// Save CDR back to the DB
				if ($this->_updUpdateCDRs->Execute($arrCDR, Array("CdrId" => $arrCDR['Id'])) === FALSE)
				{
	
				} 
	 		}
	 		
	 		// Generate Delinquent Report
			$strDelinquentText = "\n[ Delinquent FNNs ]\n\n";
			if (is_array($arrDelinquents))
			{
				foreach($arrDelinquents as $strKey=>$strValue)
				{
					$strDelinquentText .= "\t+ $strKey was referenced $strValue time(s)\n";
				}
				$strDelinquentText .= "\n\tThere were ".count($arrDelinquents)." delinquent FNNs in this run.\n";
			}
			else
			{
				$strDelinquentText .= "\n\tThere were no deliquent FNNs in this run.\n\n";
			}
			$this->rptDelinquentsReport->AddMessage(MSG_HORIZONTAL_RULE.$strDelinquentText.MSG_HORIZONTAL_RULE);
			
	 		
		 	// Normalisation Report totals
			$arrReportLine['<Action>']		= "Normalised";
			$arrReportLine['<Total>']		= (int)$intNormaliseTotal;
			$arrReportLine['<Time>']		= $this->Framework->LapWatch();
			$arrReportLine['<Pass>']		= (int)$intNormalisePassed;
			$arrReportLine['<Fail>']		= (int)$intNormaliseTotal - $intNormalisePassed;
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
			return ($bolReturn) ? $intNormaliseTotal : FALSE;
 		}
		catch (Exception $eException)
		{
			DataAccess::getDataAccess()->TransactionRollback();
			throw $eException;
		}
 	}
	
	//------------------------------------------------------------------------//
	// ReNormalise()
	//------------------------------------------------------------------------//
	/**
	 * ReNormalise()
	 *
	 * Changes CDR Status from specified value to CDR_RENORMALISE
	 *
	 * Forces the Normaliseation engine to attempt to Normalise the CDRs
	 * on the next Normalisation Run
	 *
	 * @param	integer		$intStatus			Status to look for
	 *	 
	 * @return	integer							Number of CDRs affected
	 *
	 * @method
	 */
	 function ReNormalise($intStatus)
	 {
	 	$intStatus = (int)$intStatus;
	 	$arrColumns['Status']	= CDR_RENORMALISE;
	 	$updReRate = new StatementUpdate("CDR", "Status = $intStatus", $arrColumns);
	 	$mixReturn = $updReRate->Execute($arrColumns, NULL);
	 	return (int)$mixReturn;
	 }
	 
	//------------------------------------------------------------------------//
	// ReFindOwner()
	//------------------------------------------------------------------------//
	/**
	 * ReFindOwner()
	 *
	 * Changes CDR Status from specified value to CDR_FIND_OWNER
	 *
	 * Forces the Normaliseation engine to attempt to find an owner for the CDRs
	 * on the next Normalisation Run
	 *
	 * @param	integer		$intStatus			Status to look for
	 *	 
	 * @return	integer							Number of CDRs affected
	 *
	 * @method
	 */
	 function ReFindOwner($intStatus)
	 {
	 	$intStatus = (int)$intStatus;
	 	$arrColumns['Status']	= CDR_FIND_OWNER;
	 	$updReRate = new StatementUpdate("CDR", "Status = $intStatus", $arrColumns);
	 	$mixReturn = $updReRate->Execute($arrColumns, NULL);
	 	return (int)$mixReturn;
	 }
	 
	//------------------------------------------------------------------------//
	// MatchCredits()
	//------------------------------------------------------------------------//
	/**
	 * MatchCredits()
	 *
	 * Attempts to link Credit CDRs to their Debit counterparts, and excludes them from rating
	 *
	 * Attempts to link Credit CDRs to their Debit counterparts, and excludes them from rating
	 *	 
	 * @return	integer							Number of CDRs affected
	 *
	 * @method
	 */
	 function MatchCredits()
	 {
	 	// Find the normalised Credit CDRs
	 	if (($intTotalCount = $this->_selCreditCDRs->Execute()) === FALSE)
	 	{
	 		Debug("Could not select credit CDRS!");
	 		return FALSE;
	 	}
 		
	 	// Attempt to match the CDRs up
	 	$intCount = FALSE;
		while ($arrCreditCDR = $this->_selCreditCDRs->Fetch())
	 	{
			$bolFail = FALSE;
			if ($intCount === FALSE)
			{
				$intCount = 0;
			}
	 		// Find matching Debit
	 		if ($this->_selDebitCDR->Execute($arrCreditCDR))
	 		{
				$arrDebitCDR = $this->_selDebitCDR->Fetch();
			}
			else
			{
				// Try to match credit to a rated CDR
				/*if ($this->_selRatedCDR->Execute($arrCreditCDR))
				{
					$arrDebitCDR = $this->_selRatedCDR->Fetch();
					
					//unrate the CDR
					/*$bolResult = $this->Framework->UnRateCDR($arrDebitCDR['Id'], CDR_DEBIT_MATCHED);
					if (!$bolResult)
					{
						$bolFail = TRUE;
					}*/
				/*}
				else
				{
					$bolFail = TRUE;
				}*/
				$bolFail = TRUE;
	 		}
			
			if ($bolFail === TRUE)
			{
					//TODO!rich! add to report
					//echo " ! Couldn't Match Debit to Credit with CDR.Id {$arrCreditCDR['Id']}!\n";
					
					// Update the Credit CDR
					$arrUpdateColumns['Id']		= $arrCreditCDR['Id'];
					$arrUpdateColumns['Status']	= CDR_CREDIT_MATCH_NOT_FOUND;
					$this->_ubiCDRStatus->Execute($arrUpdateColumns);
					continue;
			}
	 		
	 		// Add to the link table
			$arrInsertColumns = Array();
	 		$arrInsertColumns['credit_cdr_id']	= $arrCreditCDR['Id'];
	 		$arrInsertColumns['debit_cdr_id']	= $arrDebitCDR['Id'];
	 		$this->_insCreditLink->Execute($arrInsertColumns);
	 		
	 		// Update the Credit CDR
			$arrUpdateColumns = Array();
	 		$arrUpdateColumns['Id']		= $arrCreditCDR['Id'];
 			$arrUpdateColumns['Status']	= CDR_CREDIT_MATCHED;
	 		$this->_ubiCDRStatus->Execute($arrUpdateColumns);
	 		
	 		// Update the Debit CDR
			$arrUpdateColumns = Array();
	 		$arrUpdateColumns['Id']		= $arrDebitCDR['Id'];
 			$arrUpdateColumns['Status']	= CDR_DEBIT_MATCHED;
	 		$this->_ubiCDRStatus->Execute($arrUpdateColumns);
	 		
	 		$intCount++;
	 	}
	 	
		//TODO!rich! add to report
	 	echo "Matched $intCount out of $intTotalCount credit CDRs.\n";
		return $intCount;
	 }
 }
?>
