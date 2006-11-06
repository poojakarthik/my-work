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

// Application entry point - create an instance of the application object
$appNormalise = new ApplicationNormalise();
$appNormalise->Import();
$appNormalise->Normalise();
echo("\n-- End of Normalisation --\n");
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
 class ApplicationNormalise
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
	public $_errErrorHandler;
	
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
	public $_rptNormalisationReport;
	
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
	 * @see	<MethodName()||typePropertyName>
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
	 * @see	<MethodName()||typePropertyName>
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
	// __construct
	//------------------------------------------------------------------------//
	/**
	 * __construct()
	 *
	 * Constructor for the Normalising Application
	 *
	 * Constructor for the Normalising Application
	 *
	 * @return		ApplicationNormalise
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function __construct()
 	{
	 	// Initialise framework components
		$this->_errErrorHandler = new ErrorHandler();
		set_exception_handler($this->_errErrorHandler, "PHPExceptionCatcher");
		set_error_handler($this->_errErrorHandler, "PHPErrorCatcher");
		
		// Create an instance of each Normalisation module
 		$this->_arrNormalisationModule[CDR_UNTIEL_RSLCOM]		= new NormalisationModuleRSLCOM();
 		$this->_arrNormalisationModule[CDR_ISEEK_STANDARD]		= new NormalisationModuleIseek();
 		$this->_arrNormalisationModule[CDR_UNTIEL_COMMANDER]	= new NormalisationModuleCommander();
 		$this->_arrNormalisationModule[CDR_AAPT_STANDARD]		= new NormalisationModuleAAPT();
 		$this->_arrNormalisationModule[CDR_OPTUS_STANDARD]		= new NormalisationModuleOptus();
		
 	}
 	
 	function Import()
 	{
		// Retrieve list of CDR Files marked as either ready to process, or failed process
		$strWhere			= "Status = <status1> OR Status = <status2>";
		$arrWhere[status1]	= CDRFILE_WAITING;
		$arrWhere[status2]	= CDRFILE_REIMPORT;
		$selSelectCDRFiles 	= new StatementSelect("FileImport", "*", $strWhere);
		$updUpdateCDRFiles	= new StatementUpdate("FileImport", "Id = <id>");
		$insInsertCDRLine	= new StatementInsert("CDR");
		
		$selSelectCDRFiles->Execute($arrWhere);

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
				$this->AddToNormalisationReport(CDR_FILE_IMPORT_FAIL, $arrCDRFile["Location"], $strReason = "File Not Found");
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
				case default:
					new ExceptionVixen("Unexpected CDR File Status", $this->_errErrorHandler, UNEXPECTED_CDRFILE_STATUS);
			}
		}
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
	 * @see	<MethodName()||typePropertyName>
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
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
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
			if ($this->_arrNormalisationModule[$arrCDR["FileType"]])
			{
				if (method_exists ($this->_arrNormalisationModule[$arrCDR["FileType"]], "Preprocessor" ))
				{
					$bolPreprocessor = TRUE;
				}
				else
				{
					$bolPreprocessor = FALSE;
				}
			}
			else
			{
				$bolPreprocessor = FALSE;
			}
			
			// Insert every CDR Line into the database
			$fileCDRFile = fopen($arrCDRFile["Location"], "r");
			$intSequence = 1;
			while (!feof($fileCDRFile))
			{
				$arrCDRLine["CDR"]			= fgets($fileCDRFile);
				$arrCDRLine["SequenceNo"]	= $intSequence;
				$arrCDRLine["Status"]		= CDR_READY;
				
				// run Preprocessor
				if ($bolPreprocessor)
				{
					$arrCDRLine["CDR"]		= fgets($fileCDRFile);
				}
				else
				{
					$arrCDRLine["CDR"]		= $this->_arrNormalisationModule[$arrCDR["FileType"]]->Preprocessor(fgets($fileCDRFile);
				}
				
				$insInsertCDRLine->Execute($arrCDRLine);
				$intSequence++;
			}
			fclose($fileCDRFile);
			
			// Set the File status to "Imported"
			$arrCDRFile["Status"]		= CDRFILE_IMPORTED;
			$arrCDRFile["ImportedOn"]	= "NOW()";
			$updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"]));
			$this->AddToNormalisationReport(CDR_FILE_IMPORT_SUCCESS, $arrCDRFile["Location"]);
		}
		catch (ExceptionVixen $exvException)
		{
			$errErrorHandler->PHPExceptionCatcher($exvException);
			
			// Set the File status to "Failed"
			$arrCDRFile["Status"] = CDRFILE_IMPORT_FAILED;
			$updUpdateCDRFiles->Execute($arrCDRFile, Array("id" => $arrCDRFile["Id"]));
			
			$this->AddToNormalisationReport(CDR_FILE_IMPORT_FAIL, $arrCDRFile["Location"], $strReason = "File corrupted");
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
	 * @param	integer		$strErrorType		The message type - use constants
	 * 											from definition.php.
	 * 											Make sure you supply the NAME of the
	 * 											constant encapsulated in ""s
	 * @param	string		$strFailedOn		The name of the object on which the
	 * 											message is reporting
	 * @param	string		$strReason			optional Reason why the operation
	 * 											failed
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function AddToNormalisationReport($strErrorType, $strFailedOn, $strReason = "")
 	{
 		$strMessage = str_replace("<reason>", $strReason, constant($strErrorType));
 		$strMessage = str_replace("<object>", $strFailedOn, $strMessage);
 		$this->_rptNormalisationReport->AddMessage($strMessage);
 		
 		// Increment the number of times this message has occurred
 		$this->_arrNormaliseReportCount[$strErrorType]++;
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
 		$selSelectCDRs = new StatementSelect("CDR INNER JOIN FileImport ON CDR.File = FileImport.Id", Array("CDR.*" => "", "FileImport.FileType" => "FileType", "FileImport.FileName" => "FileName"), $strWhere = "Status = <status>");
 		$selSelectCDRs->Execute(Array("status" => CDR_READY));
 		$arrCDRList = $selSelectCDRs->FetchAll();
 		
 		
 		
 		foreach ($arrCDRList as $arrCDR)
 		{
 			// Is there a normalisation module for this type?  If not, report an error.  If so, then normalise.
 			switch ($arrCDR["FileType"])
 			{
 				case CDR_UNTIEL_RSLCOM:
 					// TODO
 					break;
 				case CDR_UNTIEL_COMMANDER:
 					// TODO
 					break;
 				case CDR_OPTUS_STANDARD:
 					// TODO
 					break;
 				case CDR_AAPT_STANDARD:
 					// TODO
 					break;
 				case CDR_ISEEK_STANDARD:
 					// TODO
 					break;
 				case default:
 					new ExceptionVixen("No normalisation module for carrier" . $arrCDR["CDR.Carrier"] . ".", $this->_errErrorHandler, NO_NORMALISATION_MODULE);
					$this->AddToNormalisationReport(CDR_NORMALISATION_FAIL, $arrCDR["CDR.CDRFilename"] . "(" . $arrCDR["CDR.SequenceNo"] . ")", $strReason = "No normalisation module for carrierNo normalisation module for carrierNo normalisation module for carrier");
 			}
 			
 			// Apply ownership to the CDR.  What does this mean exacly?  Link up FNNs?
 			
 			// Save CDR back to the DB
 		}
 		
 		// TODO: Update any CDR File entries that have been fully normalised
		$selSelectCDRFiles = new StatementSelect("FileImport", "*", "Status = <status>");
		$selSelectCDRFiles->Execute(Array("status" => CDRFILE_IMPORTED));
		$arrCDRFiles = $selSelectCDRFiles->FetchAll();
		$updUpdateFileStatus = new StatementUpdate();
		foreach ($arrCDRFiles as $arrFile)
		{
			
		}
 	}
 }
?>
