<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// ERROR
//----------------------------------------------------------------------------//
/**
 * ERROR
 *
 * Classes regarding Error Handling
 *
 * Classes regarding Error Handling
 *
 * @file		error.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// ErrorHandler
//----------------------------------------------------------------------------//
/**
 * ErrorHandler
 *
 * Receives and acts on errors
 *
 * Deals with error handling and reporting, and acts according to error type.
 *
 *
 * @prefix	err
 *
 * @package	framework
 * @class	ErrorHandler
 */
class ErrorHandler
{
	//----------------------------------------------------------------------------//
	// PROPERTIES
	//----------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// _rptReport
	//------------------------------------------------------------------------//
	/**
	 * _rptReport
	 *
	 * Report to dump errors to
	 *
	 * If we are recording errors, then this is where they will be logged
	 *
	 * @type	Report
	 *
	 * @property
	 * @see		Report
	 * @see		this->RecordError()
	 * @see		this->StartReport()
	 * @see		this->EndReport()
	 */
	private $_rptReport = null;
	
	
	//----------------------------------------------------------------------------//
	// FUNCTIONS
	//----------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// ErrorHandler() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * ErrorHandler()
	 *
	 * Constructs the Error Handler
	 *
	 * Constructs the Error Handler
	 *
	 * @return	void
	 *
	 * @method
	 * @see	this->_rptReport
	 */
	function __construct()
	{
		unset($this->_rptReport);
	}
	
	//------------------------------------------------------------------------//
	// RecordError()
	//------------------------------------------------------------------------//
	/**
	 * RecordError()
	 *
	 * Records an Error
	 *
	 * Records an error, acts upon it, and writes to the report (if one is active)
	 *
	 * @param	string		$strErrorCode		The Code of the error being reported
	 * @param	string		$strUser			User who forced the error
	 * @param	string		$strLocation		Where the error occurred (page or module)
	 * @param	string		$strDescription		PHP Description of error
	 * @return	void
	 *
	 * @method
	 * @see	this->_rptReport
	 * @see Report
	 */
	function RecordError($strErrorCode, $strUser, $strLocation, $strDescription)
	{
		/*
		// Insert into to database
		$insInsertStatement = new StatementInsert(DATABASE_ERROR_TABLE);
		$arrData = Array();
		// TODO: Fill $arrData with error information
		$insInsertStatement->Execute($arrData);
		*/
		
		// build error msg
		$strMessage = date("Y-m-d H:i:s") . " -- " . $strUser . " caused an Error "
		. "(Code: " . $strErrorCode . ") in module " . $strLocation . ".\n"
		. $strDescription . "\n";
		
		// output error message
		if (DEBUG_MODE === TRUE)
		{
			// output debug message
			echo ("<pre>\n $strMessage \n\n</pre>");
		}
		else
		{
			// output user "friendly" error msg
			// TODO!!!! - add error Id from db
			ob_clean();
			
			// redirects to an error page
			header ("Location: error.php");
		}
		
		// If we're writing a report, then append the error
		if (isset($this->_rptReport))
		{
			$this->_rptReport->AddMessage($strMessage);
		}
	}
	
	//------------------------------------------------------------------------//
	// StartReport()
	//------------------------------------------------------------------------//
	/**
	 * StartReport()
	 *
	 * Start an error report
	 *
	 * Start an error report
	 *
	 * @param	string		$strTitle			Title of the report
	 * @param	string		$strEmailAddressee	The person who will be emailed
	 * @return	void
	 *
	 * @method
	 * @see	this->_rptReport
	 */
	function StartReport($strTitle, $strEmailAddressee)
	{
		// Initialise _rptReport
		$this->_rptReport = new Report($strTitle, $strEmailAddressee);
	}
	
	//------------------------------------------------------------------------//
	// EndReport()
	//------------------------------------------------------------------------//
	/**
	 * EndReport()
	 *
	 * End an error report
	 *
	 * End an error report
	 *
	 * @return	boolean							true	: Email successful
	 * 											false	: Email failed
	 *
	 * @method
	 * @see	this->_rptReport
	 */
	function EndReport()
	{
		return $this->_rptReport->Finish();
	}
	
	//------------------------------------------------------------------------//
	// DestroyReport()
	//------------------------------------------------------------------------//
	/**
	 * DestroyReport()
	 *
	 * Discard an error report
	 *
	 * End the error report without sending an email, effectively discarding it
	 *
	 * @return	void
	 *
	 * @method
	 * @see	this->_rptReport
	 */
	function DestroyReport()
	{
		unset($this->_rptReport);
	}

	//------------------------------------------------------------------------//
	// PHPExceptionCatcher
	//------------------------------------------------------------------------//
	/**
	 * PHPExceptionCatcher()
	 *
	 * Catches all PHP Exceptions
	 *
	 * Catches all PHP Exceptions, filters informations, then passes to the
	 * ErrorHandler object
	 *
	 * @param	Exception	$excException		The exception object to be handled
	 * @return	void
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	 function PHPExceptionCatcher($excException)
	 {
	 	$strUser 		= "";
	 	$strLocation 	= $excException->getFile() . " (Line " .  $excException->getLine() . ")";
	 	$strMessage		= $excException->getMessage() . "\n" . $excException->getTraceAsString() . "\n";
	 	
	 	// Redirect to RecordError
	 	$this->RecordError($excException->getCode(), $strUser, $strLocation, $strMessage);
	 }
	 
	//------------------------------------------------------------------------//
	// PHPErrorCatcher
	//------------------------------------------------------------------------//
	/**
	 * PHPErrorCatcher()
	 *
	 * Catches PHP Errors
	 *
	 * Catches PHP Errors, filters informations, then passes to the
	 * ErrorHandler object
	 *
	 * @param	<type>	<$name>	<description>
	 * @return	void
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	 function PHPErrorCatcher($intErrorNo, $strErrorMessage, $strErrorFile, $intErrorLine)
	 {
	 	// build error msg
	 	$strUser 		= defined("USER_NAME") ? USER_NAME : '_unknown_user_';
	 	$strLocation 	= $strErrorFile . " (Line " .  $intErrorLine . ")";
	 	$strMessage		= $strErrorMessage . "\n";
	 	$arrTrace = debug_backtrace();
	 	foreach ($arrTrace as $strKey => $mixTraceData)
	 	{
	 		$strMessage .= $strKey . ": " . $mixTraceData . "\n";
	 	}
	 	$strMessage .= "\n";
	 	
		switch ($intErrorNo)
		{
			case E_WARNING:
			case E_NOTICE:
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			case E_STRICT:
				if (DEBUG_MODE === TRUE)
				{
					return FALSE;
				}
				else
				{
					// ignore these errors
					return TRUE;
				}
				break;
				
			default:
				// Fatal Error : Redirect to RecordError
	 			$this->RecordError($intErrorNo, $strUser, $strLocation, $strMessage);
				die();
		}
	 }
}

?>
