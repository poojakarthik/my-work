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
 * @package		framework
 * @author		Rich Davis
 * @version		6.11
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
echo "<pre>";

// set addresses for report
$mixEmailAddress = 'flame@telcoblue.com.au';

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise($mixEmailAddress);

// Import lines from CDR files into the database
$appNormalise->Import();

// Normalise CDR records in the database
$appNormalise->Normalise();

// finished
echo("\n-- End of Normalisation --\n");
echo "</pre>";
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
	 * @see	<MethodName()||typePropertyName>
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
	 * @see	<MethodName()||typePropertyName>
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
		$this->rptNormalisationReport = new Report("Normalisation Report for " . date("Y-m-d H:i:s"), $mixEmailAddress);
		$this->errErrorHandler = new ErrorHandler();
		//set_exception_handler(Array($this->_errErrorHandler, "PHPExceptionCatcher"));
		//set_error_handler(Array($this->_errErrorHandler, "PHPErrorCatcher"));
		
		// Create an instance of each Normalisation module
 		$this->_arrNormalisationModule[CDR_UNTIEL_RSLCOM]		= new NormalisationModuleRSLCOM();
 		$this->_arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
 		$this->_arrNormalisationModule[CDR_UNTIEL_COMMANDER]	= new NormalisationModuleCommander();
 		$this->_arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
 		$this->_arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();
		
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
		$updUpdateCDRFiles	= new StatementUpdate("FileImport", "Id = <id>");
		$insInsertCDRLine	= new StatementInsert("CDR");
		
		$selSelectCDRFiles->Execute($arrWhere);
		
		$intCount = 0;

		// Loop through the CDR File entries
		while ($arrCDRFile = $selSelectCDRFiles->Fetch())
		{
			// Make sure the file exists
			if (!file_exists($arrCDRFile["Location"]))
			{
				// Report the error, and UPDATE the database with a new status, then move to the next file
				new ExceptionVixen("Specified CDR File doesn't exist", $this->_errErrorHandler, CDR_FILE_DOESNT_EXIST);
				$arrCDRFile["Status"] = CDRFILE_IMPORT_FAILED;
				$updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"]));
				
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
	// CascadeDeleteCDRs
	//------------------------------------------------------------------------//
	/**
	 * CascadeDeleteCDRs()
	 *
	 * Deletes all data in the DB associated with a particular CDR File
	 *
	 * Deletes all data in the DB associated with a particular CDR File
	 *
	 * @param	<type>		<name>			<desc>
	 * @return	<type>
	 *
	 * @method
	 */
 	function CascadeDeleteCDRs()
 	{
		// TODO: Not for a long time, though ;)
		//		 Will be implemented as a future feature
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
		try
		{
			// Set the File status to "Importing"
			$arrCDRFile["Status"] = CDRFILE_IMPORTING;
			$updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"]));
			
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
			$fileCDRFile = fopen($arrCDRFile["Location"], "r");
			$intSequence = 1;
			while (!feof($fileCDRFile))
			{
				// Add to report <Action> CDR <SeqNo> from <FileName>...");
				$arrReportLine['<Action>']		= "Importing";
				$arrReportLine['<SeqNo>']		= $intSequence;
				$arrReportLine['<FileName>']	= TruncateName($arrCDRFile['FileName'], MSG_MAX_FILENAME_LENGTH);
				$this->AddToNormalisationReport(MSG_LINE, $arrReportLine);
				
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
				}
				$intSequence++;
				
				// Report
				$this->AddToNormalisationReport(MSG_OK);
				
				$this->_intImportPass++;
				
				//REMOVE THIS !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
				if ($intSequence > 1000)
				{
					return $intSequence;
				}
			}
			fclose($fileCDRFile);
			
			// Set the File status to "Normalised"
			$arrCDRFile["Status"]		= CDRFILE_NORMALISED;
			$arrCDRFile["ImportedOn"]	= "NOW()";
			$updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"]));
			
			return $intSequence;
		}
		catch (ExceptionVixen $exvException)
		{
			$errErrorHandler->PHPExceptionCatcher($exvException);
			
			// Set the File status to "Failed"
			$arrCDRFile["Status"] = CDRFILE_IMPORT_FAILED;
			$updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"]));
			
			// Report
			$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_CORRUPT);
			
			$this->_intImportFail++;
			
			return $intSequence;
		}
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
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function Normalise()
 	{
 		// Select all CDRs ready to be Normalised
		$strTables	= "CDR INNER JOIN FileImport ON CDR.File = FileImport.Id";
		$mixColumns	= Array("CDR.*" => "", "FileImport.FileType" => "FileType", "FileImport.FileName" => "FileName");
		$strWhere	= "CDR.Status = <status>";
		$strOrder	= "";
		$strLimit	= "1000";
 		$selSelectCDRs = new StatementSelect($strTables, $mixColumns, $strWhere, $strOrder, $strLimit);
		$selSelectCDRs->Execute(Array("status" => CDR_READY));
 		$arrCDRList = $selSelectCDRs->FetchAll();
 		
		
		// setup update query
		$arrDefine = $this->db->FetchClean("CDR");
		$arrDefine['NormalisedOn'] = new MySQLFunction("NOW()");
 		$updUpdateCDRs = new StatementUpdate("CDR", "Id = <CdrId>", $arrDefine); 		

		// Report
		$this->rptNormalisationReport->AddMessage(MSG_NORMALISATION_TITLE);
		
		$this->Framework->StartWatch();
		
		$intNormalisePassed = 0;
		$intNormaliseFailed = 0;

 		foreach ($arrCDRList as $arrCDR)
 		{
			// Report
			$arrReportLine['<Action>']		= "Normalising";
			$arrReportLine['<SeqNo>']		= $arrCDR['SequenceNo'];
			$arrReportLine['<FileName>']	= TruncateName($arrCDR['FileName'], MSG_MAX_FILENAME_LENGTH);
			$this->rptNormalisationReport->AddMessageVariables(MSG_LINE, $arrReportLine);
			
 			// Is there a normalisation module for this type?
			if ($this->_arrNormalisationModule[$arrCDR["FileType"]])
			{
				// set status to normalised
				$arrCDR['Status'] = CDR_NORMALISED;
				
				// normalise
				$arrCDR = $this->_arrNormalisationModule[$arrCDR["FileType"]]->Normalise($arrCDR);
			}
			else
			{
				// there a normalisation module for this type, report an error
 				new ExceptionVixen("No normalisation module for carrier" . $arrCDR["CDR.Carrier"] . ".", $this->_errErrorHandler, NO_NORMALISATION_MODULE);
				$this->AddToNormalisationReport(CDR_NORMALISATION_FAIL, $arrCDR["CDR.CDRFilename"] . "(" . $arrCDR["CDR.SequenceNo"] . ")", $strReason = "No normalisation module for carrierNo normalisation module for carrierNo normalisation module for carrier");
				// set the CDR status
				$arrCDR['Status'] = CDR_CANT_NORMALISE_NO_MODULE;
			}
			
			// Report
			switch ($arrCDR['Status'])
			{
				case CDR_CANT_NORMALISE_NO_MODULE:
					$arrAliases['<Module>'] = $arrCDR["FileType"];
					$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_MODULE, $arrAliases);
					$intNormaliseFailed++;
					break;
				case CDR_CANT_NORMALISE_BAD_SEQ_NO:
					$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Bad Sequence Number"));
					$intNormaliseFailed++;
					break;
				case CDR_CANT_NORMALISE_HEADER:
					$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Header Row"));
					$intNormaliseFailed++;
					break;
				case CDR_CANT_NORMALISE_NON_CDR:
					$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Non-CDR"));
					$intNormaliseFailed++;
					break;
				case CDR_BAD_OWNER:
					$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Cannot match owner"));
					$intNormaliseFailed++;
					break;
				case CDR_CANT_NORMALISE_INVALID:
					$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Normalised Data Invalid"));
					$intNormaliseFailed++;
					break;
				case CDR_CANT_NORMALISE_RAW:
					$this->AddToNormalisationReport(MSG_FAILED.MSG_FAIL_LINE, Array('<Reason>' => "Raw Data Invalid"));
					$intNormaliseFailed++;
					break;
				default:
					// Normalised OK
					$this->AddToNormalisationReport(MSG_OK);
					$intNormalisePassed++;
					break;
			}
			
			$arrCDR['NormalisedOn'] = new MySQLFunction("NOW()");
 			// Save CDR back to the DB
			$updUpdateCDRs->Execute($arrCDR, Array("CdrId" => $arrCDR['Id'])); 
 		}
 		
	 	// Report totals
		$arrReportLine['<Action>']		= "Normalised";
		$arrReportLine['<Total>']		= $intNormalisePassed + $intNormaliseFailed;
		$arrReportLine['<Time>']		= $this->Framework->LapWatch();
		$arrReportLine['<Pass>']		= (int)$intNormalisePassed;
		$arrReportLine['<Fail>']		= (int)$intNormaliseFailed;
		$this->AddToNormalisationReport(MSG_REPORT."\n".MSG_HORIZONTAL_RULE, $arrReportLine);
		
		$this->AddToNormalisationReport("Normalisation module completed in ".$this->Framework->uptime()." seconds");

		// Deliver the report
		$this->rptNormalisationReport->Finish();
		
 	}
 }
?>
