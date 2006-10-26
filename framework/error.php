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
 
require_once("report.php");

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
		unset(this->_rptReport);
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
	 * @param	string		$strErrorLevel		The level of importance of error
	 * @param	string		$strLocation		Where the error occurred (page or module)
	 * @param	string		$strDescription		PHP Description of error
	 * @return	void
	 *
	 * @method
	 * @see	this->_rptReport
	 * @see Report
	 */
	function RecordError($strErrorCode, $strUser, $strErrorLevel, $strLocation, $strDescription)
	{
		// Write to database
		// TODO
		
		// If we're writing a report, then append the error
		if (isset(this->_rptReport))
		{
			$strMessage = date("D/M/Y\@H:M:S");
			$strMessage .= " -- " . $strUser . "caused a " . $strErrorLevel
						. " Error (Code: " . $strErrorCode . " in module " . $strLocation . ".\n";
			$strMessage .= "\t\"" . $strDescription . "\"\n";
			
			this->_rptReport->AddMessage($strMessage);
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
		this->_rptReport = new Report($strTitle, $strEmailAddressee);
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
		return this->_rptReport->Finish();
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
		unset(this->_rptReport);
	}

	//------------------------------------------------------------------------//
	// PHPErrorCatcher
	//------------------------------------------------------------------------//
	/**
	 * PHPErrorCatcher()
	 *
	 * Catches all PHP Errors
	 *
	 * Catches all PHP Errors, filters informations, then passes to the
	 * ErrorHandler object
	 *
	 * @param	<type>	<$name>	<description>
	 * @return	<type>
	 *
	 * @method
	 * @see	<MethodName()||typePropertyName>
	 */
	 function PHPErrorCatcher()
	 {
	 	// TODO
	 }
}

?>
