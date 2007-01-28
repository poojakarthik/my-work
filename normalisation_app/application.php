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

echo "<pre>\n";

// set addresses for report
$mixEmailAddress = 'flame@telcoblue.com.au';

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise($mixEmailAddress);

// Change status of all CDRs with missing destination 
$appNormalise->ReNormalise(CDR_BAD_DESTINATION);

// Change status of all CDRs with missing owner 
//$appNormalise->ReFindOwner(CDR_BAD_OWNER);

// Import lines from CDR files into the database
$appNormalise->Import();

// run the Normalise method until there is nothing left to normalise
while ($appNormalise->Normalise())
{
	//break;
}

// finished
echo("\n-- End of Normalisation --\n");
echo "</pre>\n";
die();


//----------------------------------------------------------------------------//
// ClassName
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
	private $_arrNormalisationModule;
	
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
	 * @param	mixed	$$mixEmailAddress	Array or string of Addresse(s) to send report to
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
		
		// Create an instance of each Normalisation module
 		$this->_arrNormalisationModule[CDR_UNITEL_RSLCOM]		= new NormalisationModuleRSLCOM();
 		$this->_arrNormalisationModule[CDR_UNTIEL_SE]			= new NormalisationModuleRSLCOM();
 		$this->_arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
 		$this->_arrNormalisationModule[CDR_UNITEL_COMMANDER]	= new NormalisationModuleCommander();
 		$this->_arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
 		$this->_arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();
		
		// Select CDR Query
		$strTables	= "CDR INNER JOIN FileImport ON CDR.File = FileImport.Id";
		$mixColumns	= Array("" => "CDR.*", "FileType" => "FileImport.FileType", "FileName" => "FileImport.FileName");
		$strWhere	= "CDR.Status = ".CDR_READY." OR CDR.Status = ".CDR_FIND_OWNER." OR CDR.Status = ".CDR_RENORMALISE;
		$strOrder	= "CDR.Status";
		$strLimit	= "1000";
 		$this->_selSelectCDRs = new StatementSelect($strTables, $mixColumns, $strWhere, $strOrder, $strLimit);
		
		// Update CDR Query
		$arrDefine = $this->db->FetchClean("CDR");
		$arrDefine['NormalisedOn'] = new MySQLFunction("NOW()");
 		$this->_updUpdateCDRs = new StatementUpdate("CDR", "Id = <CdrId>", $arrDefine);
		
		$this->_arrDelinquents = Array();
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
	 * @return		void
	 *
	 * @method
	 */
 	function Import()
 	{
		$this->AddToNormalisationReport("\n\n".MSG_HORIZONTAL_RULE);
		$this->AddToNormalisationReport(MSG_IMPORTING_TITLE);
		$this->Framework->StartWatch();
		
		// Retrieve list of CDR Files marked as either ready to process, or failed process
		$strWhere			= "Status = <status1> OR Status = <status2>";
		$arrWhere[status1]	= CDRFILE_WAITING;
		$arrWhere[status2]	= CDRFILE_REIMPORT;
		$selSelectCDRFiles 	= new StatementSelect("FileImport", "*", $strWhere);
		$insInsertCDRLine	= new StatementInsert("CDR");
		$arrDefine = Array();
		$arrDefine['Status']		= TRUE;
		$arrDefine['ImportedOn'] 	= new MySQLFunction("NOW()");
		$updUpdateCDRFiles			= new StatementUpdate("FileImport", "Id = <id>", $arrDefine);
		
		
		if ($selSelectCDRFiles->Execute($arrWhere) === FALSE)
		{

		}
		
		$intCount = 0;
		$this->_intImportFail = 0;
		$this->_intImportPass = 0;

		// Loop through the CDR File entries
		while ($arrCDRFile = $selSelectCDRFiles->Fetch())
		{
			// Make sure the file exists
			if (!file_exists($arrCDRFile["Location"]))
			{
				// Report the error, and UPDATE the database with a new status, then move to the next file
				new ExceptionVixen("Specified CDR File doesn't exist", $this->_errErrorHandler, CDR_FILE_DOESNT_EXIST);
				$arrCDRFile["Status"] = CDRFILE_IMPORT_FAILED;
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
				case CDRFILE_REIMPORT:
					$this->CascadeDeleteCDRs();
				*/
				case CDRFILE_WAITING:
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
			$arrCDRFile["Status"] = CDRFILE_IMPORTING;
			$arrCDRFile['ImportedOn'] 	= new MySQLFunction("NOW()");
			if ($updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"])) === FALSE)
			{

			}
			
			// Set fields that are consistent over all CDRs for this file
			$arrCDRLine["File"]			= $arrCDRFile["Id"];
			$arrCDRLine["Carrier"]		= $arrCDRFile["Carrier"];
			
			// check for a preprocessor
			$bolPreprocessor = FALSE;
			if ($this->_arrNormalisationModule[$arrCDRFile["FileType"]])
			{
				if (method_exists ($this->_arrNormalisationModule[$arrCDRFile["FileType"]], "Preprocessor" ))
				{
					$bolPreprocessor = TRUE;
				}
			}
			
			// Insert every CDR Line into the database
			$fileCDRFile	= fopen($arrCDRFile["Location"], "r");
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
					$arrCDRLine["CDR"]		= $this->_arrNormalisationModule[$arrCDRFile["FileType"]]->Preprocessor(fgets($fileCDRFile));
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
			$arrCDRFile["Status"]		= CDRFILE_NORMALISED;
			//$arrCDRFile['NormalisedOn'] = new MySQLFunction("Now()");
			if ($updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"])) === FALSE)
			{

			}
		}
		catch (ExceptionVixen $exvException)
		{
			$errErrorHandler->PHPExceptionCatcher($exvException);
			
			// Set the File status to "Failed"
			$arrCDRFile["Status"] = CDRFILE_IMPORT_FAILED;
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
	 * @return	bool	returns true untill all CDRs have been normalised
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function Normalise()
 	{
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
			if ($this->_arrNormalisationModule[$arrCDR["FileType"]])
			{
				// normalise
				switch ($arrCDR['Status'])
				{
					case CDR_FIND_OWNER:
						$arrCDR = $this->_arrNormalisationModule[$arrCDR["FileType"]]->FindOwner($arrCDR);
						break;
					case CDR_READY:
					case CDR_RENORMALISE:
					default:
						$arrCDR = $this->_arrNormalisationModule[$arrCDR["FileType"]]->Normalise($arrCDR);
						break;
				}
			}
			else
			{
				// there is no normalisation module for this type, report an error
 				new ExceptionVixen("No normalisation module for carrier" . $arrCDR["CDR.Carrier"] . ".", $this->_errErrorHandler, NO_NORMALISATION_MODULE);
				$this->AddToNormalisationReport(CDR_NORMALISATION_FAIL, $arrCDR["CDR.CDRFilename"] . "(" . $arrCDR["CDR.SequenceNo"] . ")", $strReason = "No normalisation module for carrierNo normalisation module for carrierNo normalisation module for carrier");
				// set the CDR status
				$arrCDR['Status'] = CDR_CANT_NORMALISE_NO_MODULE;
			}
			
			if ($arrCDR['Status'] != CDR_NORMALISED && $arrCDR['Status'] != CDR_CANT_NORMALISE_HEADER && $arrCDR['Status'] != CDR_CANT_NORMALISE_NON_CDR)
			{
				$this->rptNormalisationReport->AddMessageVariables(MSG_LINE, $arrReportLine, FALSE);
			}
			
			// Report
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
					$arrDelinquents[$this->_arrNormalisationModule[$arrCDR["FileType"]]->strFNN]++;
					$intDelinquents++;
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
					$intNormalisePassed++;
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
		
		// Return TRUE or FALSE
		return $bolReturn;
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
 }
?>
