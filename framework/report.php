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
	 * Address the report will be emailed to
	 *
	 * The email address to which this report will be sent
	 *
	 * @type	string
	 *
	 * @property
	 * @see	this->Report()
	 * @see	this->Finish()
	 */
	private	$_strEmailAddressee = "flame@telcoblue.com.au";
	
	//------------------------------------------------------------------------//
	// _strEmailFrom
	//------------------------------------------------------------------------//
	/**
	 * _strEmailFrom
	 *
	 * Who the report will be emailed from
	 *
	 * The email address from which this report will be emailed
	 *
	 * @type	string
	 *
	 * @property
	 * @see	this->Report()
	 * @see	this->Finish()
	 */
	private	$_strEmailFrom = "flame@telcoblue.com.au";
	
	
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
	 * @param	mixed		$mixEmailAddressee		Address that will receive
	 * 													the report (string or array)
	 * @param	string		$strEmailFrom			optional Address to send the report
	 * 													from
	 * @return	void
	 * 
	 * @see		this->_strTitle
	 */
	public function __construct($strReportTitle, $mixEmailAddressee, $bolDebugPrint = TRUE, $strEmailFrom = '')
	{
		// Assign passed parameters to member variables
		$this->_strTitle		= $strReportTitle;
		$this->_bolDebugPrint	= $bolDebugPrint;
		
		if($bolDebugPrint)
		{
			Debug($strReportTitle."\n", "rpt");	
		}
		
		if (is_array($mixEmailAddressee))
		{
			$this->_arrEmailAddressee = $mixEmailAddressee;
		}
		else
		{
			$this->_arrEmailAddressee = Array($mixEmailAddressee);
		}
		$this->_strEmailFrom = $strEmailFrom;
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
	 * @return	int								no of emails sent
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
		
		// set sender address
		$strMailHeaders = "From: {$this->_strEmailFrom}";
		
		$intSent = 0;
		
		foreach($this->_arrEmailAddressee as $strEmailAddressee)
		{
			// Send the email
			$intSent += mail($strEmailAddressee, $this->_strTitle . "(Automated Report)", $strEmailMessage, $strMailHeaders);
		}

		// return
		return (int)$intSent;
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
	 * @param	boolean		$bolNewLine			optional Whether the message will be on a new line
	 * 											Defaults to TRUE
	 * @return	void
	 * 
	 * @method
	 * @see		this->_arrLines
	 */
	public function AddMessage($strMessage, $bolNewLine = TRUE)
	{
		// Add a new line character to the end of the message
		if ($bolNewLine)
		{
			$strMessage .= "\n";
		}
		
		// Append the message to the end of the message array
		$this->_arrLines[] = $strMessage;
		
		// Debug the line
		if($this->_bolDebugPrint)
		{		
			Debug($strMessage, "rpt");
		}
	}	
	
	//------------------------------------------------------------------------//
	// AddMessageVariables()
	//------------------------------------------------------------------------//
	/**
	 * AddMessageVariables()
	 * 
	 * Add a new message line with string replaced variables
	 * 
	 * Add a new message line with string replaced variables
	 * 
	 * @param	string		$strMessage			The new message line to be added
	 * @param	array		$arrAliases			Associative array of alises.
	 * 											MUST use the same aliases as used in the 
	 * 											constant being used.  Key is the alias (including the <>'s)
	 * 											, and the Value is the value to be inserted.
	 * @param	boolean		$bolNewLine			optional Whether the message will be on a new line
	 * 											Defaults to TRUE
	 * @return	void
	 * 
	 * @method
	 * @see		this->_arrLines
	 */
	public function AddMessageVariables($strMessage, $arrAliases, $bolNewLine = TRUE)
	{
		if (is_array($arrAliases))
		{
			foreach ($arrAliases as $arrAlias => $arrValue)
			{
				$strMessage = str_replace($arrAlias, $arrValue, $strMessage);
			}
		}
		$this->AddMessage($strMessage, $bolNewLine);
	}	
		
	
}

?>
