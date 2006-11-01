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
			$arrCDRLine["CDRFileName"]	= $arrCDRFile["Filename"];
			$arrCDRLine["Carrier"]		= $arrCDRFile["Carrier"];
						
			// Insert every CDR Line into the database
			$fileCDRFile = fopen($arrCDRFile["Location"], "r");
			$intSequence = 0;
			while (!feof($fileCDRFile))
			{
				$arrCDRLine["CDR"]			= fgets($fileCDRFile);
				$arrCDRLine["SequenceNo"]	= $intSequence;
				$arrCDRLine["Status"]		= CDR_READY;
				
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
	 * @param	integer		$intErrorType		The message type - use constants
	 * 											from definition.php
	 * @param	string		$strFailedOn		The name of the object on which the
	 * 											message is reporting
	 * @param	string		$strReason			optional Reason why the operation
	 * 											failed
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
 	function AddToNormalisationReport($intErrorType, $strFailedOn, $strReason = "")
 	{
 		$strMessage = str_replace("<reason>", $strReason, constant($intErrorType));
 		$strMessage = str_replace("<object>", $strFailedOn, $strMessage);
 		$this->_rptNormalisationReport->AddMessage($strMessage);
 		
 		switch ($intErrorType)
 		{
 			case CDR_NORMALISE_SUCCESS:
 			case CDR_NORMALISE_FAILED:
 			case CDR_FILE_NORMALISE_FAIL:
 			case CDR_FILE_NORMALISE_SUCCESS:
 			case CDR_FILE_IMPORT_FAIL:
 			case CDR_FILE_IMPORT_SUCCESS:
 		}
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
 		// TODO
 	}
 }
?>
