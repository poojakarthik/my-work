<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// REPORT
//----------------------------------------------------------------------------//
/**
 * REPORT
 *
 * Contains all classes regarding Reporting
 *
 * Contains all classes regarding Reporting
 *
 * @file		report.php
 * @language	PHP
 * @package		framework
 * @author		Rich Davis
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */

//----------------------------------------------------------------------------//
// Report
//----------------------------------------------------------------------------//
/**
 * Report
 *
 * Report object
 *
 * A Report object, containing numerous message lines
 *
 * @prefix	rpt
 *
 * @package framework
 * @class	Report
 */
class Report
{
	//------------------------------------------------------------------------//
	// PROPERTIES
	//------------------------------------------------------------------------//

	//------------------------------------------------------------------------//
	// _arrLines
	//------------------------------------------------------------------------//
	/**
	 * _arrLines
	 *
	 * Array of messages
	 *
	 * Contains the messages which make up the report
	 *
	 * @type	Array
	 *
	 * @property
	 * @see		this->AddMessage()
	 * @see		this->Report()
	 * @see		this->Finish()
	 */
	private $_arrLines;
	
	//------------------------------------------------------------------------//
	// _strTitle
	//------------------------------------------------------------------------//
	/**
	 * _strTitle
	 *
	 * Title of the report
	 *
	 * Title of the report.  Defaults to "No Title Supplied" to save writing
	 * this later on (it should be overwritten, regardless)
	 *
	 * @type	string
	 *
	 * @property
	 * @see	this->Report()
	 * @see	this->Finish()
	 */
	private	$_strTitle = "No Title Supplied";
	
	//------------------------------------------------------------------------//
	// _strEmailAddressee
	//------------------------------------------------------------------------//
	/**
	 * _strEmailAddressee
	 *
	 * Who the report will be emailed to
	 *
	 * The person/organisation/location to which this report will be emailed
	 *
	 * @type	string
	 *
	 * @property
	 * @see	this->Report()
	 * @see	this->Finish()
	 */
	private	$_strEmailAddressee = "";
	
	
	//------------------------------------------------------------------------//
	// FUNCTIONS
	//------------------------------------------------------------------------//
	
	//------------------------------------------------------------------------//
	// Report() - Constructor
	//------------------------------------------------------------------------//
	/**
	 * Report()
	 * 
	 * Report class constructor
	 * 
	 * Creates and intanciates a Report object
	 * 
	 * @param	string		$strReportTitle			A title for the report
	 * @param	string		$strEmailAddressee		Person who will receive
	 * 															 the report
	 * @return	void
	 * 
	 * @see		this->_strTitle
	 */
	public function __construct($strReportTitle, $strEmailAddressee)
	{
		// Assign passed parameters to member variables
		$this->_strTitle = $strReportTitle;
		$this->_strEmailAddressee = $strEmailAddressee;
	}

	//------------------------------------------------------------------------//
	// Finish()
	//------------------------------------------------------------------------//
	/**
	 * Finish()
	 * 
	 * Finish the report
	 * 
	 * Closes the report, and delivers it to the specified email address
	 * 
	 * @return	boolean							true	: email sent
	 * 											false	: email failed
	 * 
	 * @method
	 * @see		this->_strTitle
	 * @see		this->_strEmailAddressee
	 * @see		this->_arrLines
	 */
	public function Finish()
	{
		// Create the final email message from _arrLines, _strTitle,
		//									 and a predifined message
		$strEmailMessage = 	AUTOMATED_REPORT_HEADER;
				
		// Loop through _arrLines, appending each line to the email.
		// Using "for" loop instead of "foreach" for improved performance
		for ($i = 0; $i < count($this->_arrLines); $i++)
		{
			$strEmailMessage .= $this->_arrLines[$i];
		}
		
		$strEmailMessage .= AUTOMATED_REPORT_FOOTER;
		
		// TODO - Once we can connect to the DB, retrieve email address list
		$strEmailAddress = "flame@telcoblue.com.au";
		
		// Send the email
		return mail($strEmailAddress, $this->_strTitle . "(Automated Report)", $strEmailMessage);
	}

	//------------------------------------------------------------------------//
	// AddMessage()
	//------------------------------------------------------------------------//
	/**
	 * AddMessage()
	 * 
	 * Add a new message line
	 * 
	 * Appends a new message line to the end of the report
	 * 
	 * @param	string		$strMessage			The new message line to be added
	 * @return	void
	 * 
	 * @method
	 * @see		this->_arrLines
	 */
	public function AddMessage($strMessage)
	{
		// Add a new line character to the end of the message
		$strMessage .= "\n";
		
		// Append the message to the end of the message array
		$this->_arrLines[] = $strMessage;
	}	
	
}

?>
