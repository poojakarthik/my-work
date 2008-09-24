W<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//


//----------------------------------------------------------------------------//
// functions
//----------------------------------------------------------------------------//
/**
 * functions
 *
 * Global Functions
 *
 * This file exclusively declares global functions
 *
 * @file		functions.php
 * @language	PHP
 * @package		framework
 * @author		Jared 'flame' Herbohn
 * @version		6.10
 * @copyright	2006 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
 
//----------------------------------------------------------------------------//
// FUNCTIONS
//----------------------------------------------------------------------------//


//------------------------------------------------------------------------//
// CleanDir INCOMPLETE
//------------------------------------------------------------------------//
/**
 * CleanDir()
 *
 * Cleans a directory
 *
 * Cleans a directory recursively
 * 
 * @param	string	$strDirectory		Directory to flush
 * 
 * @comments
 * @function
 */
function CleanDir($strDirectory)
{
	$arrContents = scandir($strDirectory);
	foreach ($arrContents as $strItem) {
		if (is_dir($strDirectory.$strItem) && $strItem != '.' && $strItem != '..')
		{
			RemoveDir($strDirectory.$strItem.'/');
			rmdir($strDirectory.$strItem);
		}
		elseif ((file_exists($strDirectory.$strItem)) && ($strItem != '.') && ($strItem != '..'))
		{
			unlink($strDirectory.$strItem);
		}
	}
}

//------------------------------------------------------------------------//
// RemoveDir
//------------------------------------------------------------------------//
/**
 * RemoveDir()
 *
 * Removes a directory
 *
 * Removes a directory and all of its files
 * 
 * @param	string	$strDirectory		Directory to flush
 * 
 * @function
 */
function RemoveDir($strDirectory)
{
	CleanDir($strDirectory);
	rmdir($strDirectory);		
}

//------------------------------------------------------------------------//
// Debug INCOMPLETE
//------------------------------------------------------------------------//
/**
 * Debug()
 *
 * display debug output
 *
 * display debug output
 *
 * @param	mixed	$mixOutput	value to output
 * @param	string	$strMode	optional mode, html (default) or txt 
 * @return	bool
 *
 * @comments
 * @function
 * @package	framework
 */
function Debug($mixOutput, $strMode="html")
{
	// check if we are in debug mode
	if (DEBUG_MODE !== TRUE)
	{
		// not in debug mode, exit
		return FALSE;
	}
	
	// If we're logging, add to that too
	if (LOG_TO_FILE && isset($GLOBALS['fwkFramework']))
	{
		$bolLog = TRUE;
	}
	else
	{
		$bolLog = FALSE;
	}
	
	// output debug info in required format
	switch (strtolower($strMode))
	{
		case 'txt':
		case 'text':
			echo "\n";
			print_r($mixOutput);
			echo "\n";
			if($bolLog)
			{
				$strData = "\n";
				$strData .= print_r($mixOutput, TRUE);
				$strData .= "\n";
				$GLOBALS['fwkFramework']->AddToLog($strData, FALSE);
			}
			break;

		case 'report':
		case 'rpt':
		case 'console':
		case 'terminal':
			echo $mixOutput;
			if ($bolLog)
			{
				$GLOBALS['fwkFramework']->AddToLog($mixOutput, FALSE);
			}
			break;

		case 'log':
			$GLOBALS['fwkFramework']->AddToLog($mixOutput, FALSE);
			break;
			
		default:
			$strPrintR = print_r($mixOutput, TRUE);
			$strOutput = ($_SERVER['TERM']) ? "\n$strPrintR\n" : "\n<pre>\n$strPrintR\n</pre>\n";
			echo $strOutput;
			if($bolLog)
			{
				$strData = "\n";
				$strData .= print_r($mixOutput, TRUE);
				$strData .= "\n";
				$GLOBALS['fwkFramework']->AddToLog($strData, FALSE);
			}
	}
	
	// Flush the output to screen/client
	ob_flush();
	
	return TRUE;
}

//------------------------------------------------------------------------//
// DebugBacktrace
//------------------------------------------------------------------------//
/**
 * DebugBacktrace()
 *
 * display debug backtrace output
 *
 * display debug backtrace output
 *
 * @param	string	$strMode	optional mode, html (default) or txt 
 * @return	bool
 *
 * @function
 * @package	framework
 */
function DebugBacktrace($strMode="html")
{
	// check if we are in debug mode
	if (DEBUG_MODE !== TRUE)
	{
		// not in debug mode, exit
		return FALSE;
	}
	
	// output debug info in required format
	switch (strtolower($strMode))
	{
		case 'txt':
		case 'text':
			echo "\n";
			debug_print_backtrace();
			echo "\n";
			break;
			
		default:
			echo "\n<pre>\n";
			debug_print_backtrace();
			echo "\n</pre>\n";

	}
	return TRUE;
}

//------------------------------------------------------------------------//
// SystemDebug
//------------------------------------------------------------------------//
/**
 * SystemDebug()
 *
 * Returns system debug details
 *
 * Returns system debug details
 *
 * @return	string
 *
 * @function
 * @package	framework
 */
function SystemDebug()
{
	// set up debug string
	$strDebug = "";
	
	// trace log
	$strDebug .= $GLOBALS['TraceLog']['Debug'];
	
	// MySQL
	$strDebug .= $GLOBALS['TraceLog']['MySQL'];
	
	// return string
	return $strDebug;
}


//------------------------------------------------------------------------//
// Trace
//------------------------------------------------------------------------//
/**
 * Trace()
 *
 * Adds a record to the trace log
 *
 * Adds a record to the trace log
 *
 * @param	string	$strString	record to add to the trace log
 * @param	string	$strLogname	optional name of log to add trace to
 * @return	bool
 *
 * @function
 * @package	framework
 */
function Trace($strString, $strLogname = 'Debug')
{
	if (!isset ($GLOBALS['TraceLog']))
	{
		$GLOBALS['TraceLog'] = Array ();
	}
	
	if (!isset ($GLOBALS['TraceLog'][$strLogname]))
	{
		$GLOBALS['TraceLog'][$strLogname] = "";
	}
	
	$GLOBALS['TraceLog'][$strLogname] .= $strString."\n";
	return TRUE;
}


//------------------------------------------------------------------------//
// Backtrace INCOMPLETE
//------------------------------------------------------------------------//
/**
 * Backtrace()
 *
 * Returns formated backtrace string
 *
 * Returns formated backtrace string
 * 
 * @param	array	$backtrace		optional Data returned from a debug_backtrace() call
 *
 * @return	string
 *
 * @comments
 * @function
 * @package	framework
 */
function Backtrace($backtrace = NULL)
{
	$output = "";
	if (!is_array($backtrace))
	{
		$backtrace = debug_backtrace();
	}
	
	foreach ($backtrace as $key=>$bt)
	{
		$args = '';
		if (is_array($bt['args']))
		{
			foreach ($bt['args'] as $a)
			{
				if (!empty($args))
				{
					$args .= ', ';
				}
				switch (gettype($a))
				{
					case 'integer':
					case 'double':
						$args .= $a;
						break;
					case 'string':
						$a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
						$args .= "\"$a\"";
						break;
					case 'array':
						$args .= 'Array('.count($a).')';
						break;
					case 'object':
						$args .= 'Object('.get_class($a).')';
						break;
					case 'resource':
						$args .= 'Resource('.strstr($a, '#').')';
						break;
					case 'boolean':
						$args .= $a ? 'TRUE' : 'FALSE';
						break;
					case 'NULL':
						$args .= 'NULL';
						break;
					default:
						$args .= 'Unknown';
				}
			}
		}
		$output .= "#{$key}  {$bt['class']}{$bt['type']}{$bt['function']}($args) called at [{$bt['file']}:{$bt['line']}]";
		$output .= "\n";
	}
	$output .= "\n";
	return $output;
}

//------------------------------------------------------------------------//
// TruncateName INCOMPLETE
//------------------------------------------------------------------------//
/**
 * TruncateName()
 *
 * Truncates a filename
 *
 * Truncates a filename to the specified number of characters
 * (eg. Input: "/tmp/really_long_filename.txt" Output: "/tmp/really_lo...name.txt")
 *
 * @param	string		$strText	Text to truncate
 * @param	integer		$intLength	Number of characters to limit to (must be > 10)
 * 
 * @return	mixed					string	: Truncated filename
 * 									FALSE	: Invalid filename
 *
 * @brokenreturn
 * @function
 * @package	framework
 */
function TruncateName($strText, $intLength)
{
	// Get the basename
	$strBase		= basename($strText);
	$intMaxLength	= $intLength - 4;
	
	if (strlen($strText) <= $intLength)
	{
		// If the whole path can be displayed
		// Pad the remaining gaps
		return str_pad($strText, $intLength);
	}
	elseif (strlen($strBase) > $intMaxLength)
	{
		// If the basename is too long to be displayed in full
		$strTruncated = substr($strText, 0, floor(($intLength - 3) / 3));
		$strTruncated .= "...";
		$strTruncated .= substr($strBase, 0 - (floor(($intLength - 3) / 3) * 2));
	}
	elseif (strlen($strText) > $intMaxLength)
	{
		// If the whole path is too long to be displayed in full
		$strTruncated = substr($strText, $intLength - 3 - strlen($strBase));
		$strTruncated .= "...";
		$strTruncated .= $strBase;
	}
	return str_pad($strTruncated, $intLength);
}

//------------------------------------------------------------------------//
// RemoveAusCode
//------------------------------------------------------------------------//
/**
 * RemoveAusCode()
 *
 * Removes +61 from FNNs
 *
 * Removes the +61 from the start of an FNN, replacing it with a 0
 * 
 * @param	string		$strFNN		FNN to be parsed
 *
 * @return	string					Modified FNN
 *
 * @method
 */	
function RemoveAusCode($strFNN)
{
	return str_replace("+61", "0", $strFNN);
}

//------------------------------------------------------------------------//
// ReplaceAliases
//------------------------------------------------------------------------//
/**
 * ReplaceAliases()
 * 
 * Returns a string with string replaced variables
 * 
 * Returns a string with string replaced variables
 * 
 * @param	string		$strMessage			The new message line to be added
 * @param	array		$arrAliases			Associative array of alises.
 * 											MUST use the same aliases as used in the 
 * 											constant being used.  Key is the alias (including the <>'s)
 * 											, and the Value is the value to be inserted.
 * @return	string
 * 
 * @method
 */
function ReplaceAliases($strMessage, $arrAliases)
{
	if (is_array($arrAliases))
	{
		foreach ($arrAliases as $arrAlias => $arrValue)
		{
			$strMessage = str_replace($arrAlias, $arrValue, $strMessage);
		}
	}
	return $strMessage;
}	

//------------------------------------------------------------------------//
// GetCarrierName
//------------------------------------------------------------------------//
/**
 * GetCarrierName()
 * 
 * Convert a Carrier ID to a Carrier Name
 * 
 * Convert a Carrier ID to a Carrier Name
 * 
 * @param	integer		$intCarrier			Carrier code to convert
 *
 * @return	mixed							string: Carrier name
 * 											FALSE: Unknown carrier code
 * 
 * @method
 */
function GetCarrierName($intCarrier)
{
	switch($intCarrier)
	{
		case CARRIER_UNITEL:
			return "Unitel";
		case CARRIER_UNITEL_VOICETALK:
			return "Unitel (VoiceTalk)";
		case CARRIER_OPTUS:
			return "Optus";
		case CARRIER_AAPT:
			return "AAPT";
		case CARRIER_ISEEK:
			return "iSeek";
		default:
			return FALSE;
	}
}	

//------------------------------------------------------------------------//
// IsValidFNN
//------------------------------------------------------------------------//
/**
 * IsValidFNN()
 * 
 * Check if an FNN is valid
 * 
 * Check if an FNN is valid
 * 
 * @param	string		$strFNN				The FNN number to check for validity
 *
 * @return	boolean							TRUE/FALSE: Depending on whether the FNN is a valid Australian Full National Number
 * 
 * @method
 */
function IsValidFNN($strFNN)
{
	return preg_match ("/^(0\d{9}[i]?)|(13\d{4})|(1[389]00\d{6})$/", $strFNN);
}

//------------------------------------------------------------------------//
// CleanFNN
//------------------------------------------------------------------------//
/**
 * CleanFNN()
 * 
 * Clean an FNN
 * 
 * Clean an FNN
 * 
 * @param	string		$strFNN				The FNN number to Clean
 * @param	string		$strAreaCode		optional Area Code to Clean
 *
 * @return	string							The Cleaned FNN
 * 
 * @method
 */
function CleanFNN($strFNN, $strAreaCode=NULL)
{
	// add the area code
	if ($strAreaCode)
	{
		$strFNN = "$strAreaCode$strFNN";
	}
	
	// trim the FNN
	$strFNN = Trim($strFNN);
	
	// keep the i from the end of an ADSL service
	$strSuffix = strtolower(substr($strFNN, -1, 1));
	if ($strSuffix != 'i' && $strSuffix != 'd')
	{
		$strSuffix = '';
	}
	
	// clean any leading zeros from the fnn
	$intFNN = (int)$strFNN;
	$strFNN = "$intFNN";
	
	// get the FNN length
	$intLength = strlen($strFNN);
	
	// add leading zeros if needed
	if ($intLength == 9)
	{
		$strFNN = "0$strFNN";
	}
	
	return $strFNN.$strSuffix;
}

//------------------------------------------------------------------------//
// ServiceType INCOMPLETE
//------------------------------------------------------------------------//
/**
 * ServiceType()
 * 
 * Find the Service Type of an FNN
 * 
 * Find the Service Type of an FNN
 * 
 * @param	string		$strFNN				The FNN number to Check
 *
 * @return	mixed					int		Service Type Constant
 *									FALSE	Service Type not found
 * 
 * @comments
 * @method
 */
function ServiceType($strFNN)
{
	$strFNN 	= Trim($strFNN);
	$strPrefix 	= substr($strFNN, 0, 2);
	$intNine	= (int)"9$strFNN";
	$strNine	= "9$strFNN";
	
	// Land Line
	if ("$intNine" === $strNine && ($strPrefix == '02' || $strPrefix == '03' || $strPrefix == '07' || $strPrefix == '08' || $strPrefix == '09'))
	{
		return SERVICE_TYPE_LAND_LINE;
	}
	
	// Mobile
	if ($strPrefix == '04')
	{
		return SERVICE_TYPE_MOBILE;
	}

	// Inbound
	if ($strPrefix == '13' || $strPrefix == '18')
	{
		return SERVICE_TYPE_INBOUND;
	}
	
	// get the suffix
	$strSuffix	= strtolower(substr($strFNN, -1, 1));
	
	// ADSL
	if ($strSuffix == 'i')
	{
		return SERVICE_TYPE_ADSL;
	}
	
	// Dialup
	if ($strSuffix == 'd')
	{
		return SERVICE_TYPE_DIALUP;
	}
	
	// NFI what this service is
	return FALSE;
}

//------------------------------------------------------------------------//
// GetConstantName
//------------------------------------------------------------------------//
/**
 * GetConstantName()
 * 
 * Find the Name of a Constant
 * 
 * Find the Name of a Constant
 * 
 * @param	string		$intCode			The Constant
 * @param	string		$strType			optional type of Constant. Default = 'CDR'
 *
 * @return	mixed					string	Constant Name
 *									FALSE	Constant not found
 * 
 * @method
 */
function GetConstantName($intCode, $strType='CDR')
{
	if (isset($GLOBALS['*arrConstant'][$strType][$intCode]['Constant']))
	{
		return $GLOBALS['*arrConstant'][$strType][$intCode]['Constant'];
	}
	else
	{
		return FALSE;
	}
}

//------------------------------------------------------------------------//
// GetConstantDescription
//------------------------------------------------------------------------//
/**
 * GetConstantDescription()
 * 
 * Find the Description of a Constant
 * 
 * Find the Description of a Constant
 * 
 * @param	string		$intCode			The Constant
 * @param	string		$strType			optional type of Constant. Default = 'CDR'
 *
 * @return	mixed					string	Constant Description
 *									FALSE	Constant not found
 * 
 * @method
 */
function GetConstantDescription($intCode, $strType='CDR')
{
	if (isset($GLOBALS['*arrConstant'][$strType][$intCode]['Description']))
	{
		return $GLOBALS['*arrConstant'][$strType][$intCode]['Description'];
	}
	else
	{
		return FALSE;
	}
}

//------------------------------------------------------------------------//
// EvalReturn
//------------------------------------------------------------------------//
/**
 * EvalReturn()
 * 
 * Eval php code and return the result
 * 
 * Eval php code and return the result
 * 
 * @param	string		$strCode			PHP code to be evaled
 *
 * @return	mixed							return value of the php code
 * 
 * @method
 */
function EvalReturn ($strCode)
{
	if (!trim($strCode))
	{
		return FALSE;
	}
	
	// by default we return FALSE
	$return = FALSE;
	
	$code = "$return = $strCode;";
	try
	{
		eval($code);
	}
	catch(Exception $e)
	{
		$return = FALSE;
	}
	
	return $return; 
}

// -------------------------------------------------------------------------- //
// Permission Functions
// -------------------------------------------------------------------------- //

//------------------------------------------------------------------------//
// AddPermission
//------------------------------------------------------------------------//
/**
 * AddPermission()
 * 
 * Add permissions to a user
 * 
 * Add permissions to a user
 * 
 * @param	int		$intUser			Current Permissions of the user
 * @param	int		$intPermission		Permissions to be added to the user
 *
 * @return	int							New permissions of the user
 * 
 * @method
 */
function AddPermission($intUser, $intPermission)
{
	// add the permission (Bitwise OR)
	$intUser = (int)$intUser | (int)$intPermission;

	// return the users permissions
	return $intUser;
}

//------------------------------------------------------------------------//
// RemovePermission
//------------------------------------------------------------------------//
/**
 * RemovePermission()
 * 
 * Remove permissions from a user
 * 
 * Remove permissions from a user
 * 
 * @param	int		$intUser			Current Permissions of the user
 * @param	int		$intPermission		Permissions to be removed from the user
 *
 * @return	int							New permissions of the user
 * 
 * @method
 */
function RemovePermission($intUser, $intPermission)
{
	// add the permission (Bitwise OR)
	$intUser = (int)$intUser | (int)$intPermission;
	
	// remove the permission (Bitwise XOR)
	$intUser = (int)$intUser ^ (int)$intPermission;
	
	// return the users permissions
	return $intUser;
}

//------------------------------------------------------------------------//
// HasPermission INCOMPLETE
//------------------------------------------------------------------------//
/**
 * HasPermission()
 * 
 * Check if a user has a specified permission
 * 
 * Check if a user has a specified permission
 * 
 * @param	mix		$mixUser			Ineger Current Permissions of the user (or array of users)
 * @param	mix		$mixPermission		Integer Permissions to be checked for (or array of permissions)
 *
 * @return	bool						TRUE if the user has the permission
 * 
 * @comments
 * @method
 */
function HasPermission($mixUser, $mixPermission)
{
	if (is_array($mixPermission))
	{
		$arrPermission = $mixPermission;
	}
	else
	{
		$arrPermission = Array($mixPermission);
	}
	
	if (is_array($mixUser))
	{
		$arrUser = $mixUser;
	}
	else
	{
		$arrUser = Array($mixUser);
	}
	
	foreach ($arrPermission AS $intPermission)
	{
		foreach ($arrUser AS $intUser)
		{
			// check for the permission (Bitwise OR)
			if ((int)$intUser && (int)$intUser == ((int)$intUser | (int)$intPermission))
			{
				// return TRUE
				return TRUE;
			}
		}
	}
	// return FALSE
	return FALSE;
}

// -------------------------------------------------------------------------- //
// PDF FUNCTIONS
// -------------------------------------------------------------------------- //

//------------------------------------------------------------------------//
// ListPDF INCOMPLETE
//------------------------------------------------------------------------//
/**
 * ListPDF()
 * 
 * Return a list of invoice PDFs
 * 
 * Return a list of invoice PDFs for the specified account
 * 
 * @param	int		$intAccount			Account to find PDFs for
 *
 * @return	mixed						array: Associative array of PDFs
 * 										FALSE: there was an error
 * 
 * @brokenreturn
 * @function
 */
function ListPDF($intAccount)
{
	$arrReturn = Array();
	
	// GLOB for year directories
	$arrYears = glob(PATH_INVOICE_PDFS ."*", GLOB_ONLYDIR);
	
	foreach($arrYears as $strYear)
	{
		// GLOB for month directories
		$arrMonths = glob("$strYear/*", GLOB_ONLYDIR);
		
		foreach($arrMonths as $strMonth)
		{
			// GLOB for account filename
			$arrInvoices = glob("$strMonth/".$intAccount."_*.pdf");
			if ($arrInvoices[0])
			{
				$arrReturn[basename ($strYear)][basename ($strMonth)]	= basename($arrInvoices[0]);
			}
		}
	}
	
	return $arrReturn;
}

//------------------------------------------------------------------------//
// GetPDF
//------------------------------------------------------------------------//
/**
 * GetPDF()
 * 
 * Return the contents of a PDF invoice in a string
 * 
 * Return the contents of a PDF invoice in a string for the specified account, year, and month
 * 
 * @param	int		$intAccount			Account to find PDFs for
 * @param	int		$intYear			Year to match
 * @param	int		$intMonth			Month to match
 *
 * @return	mixed						string: contents of the PDF invoice
 * 										FALSE: there was an error
 * 
 * @function
 */
function GetPDF($intAccount, $intYear, $intMonth)
{
	$arrReturn = Array();
	
	// GLOB for account filename
	$arrInvoices = glob(PATH_INVOICE_PDFS . $intYear ."/". $intMonth ."/". $intAccount ."_*.pdf");
	
	if (count($arrInvoices) == 0 || $arrInvoices === FALSE)
	{
		// Either glob had an error, or the filename doesn't exist
		return FALSE;
	}
	
	// Read the file contents into a string
	$strReturn = file_get_contents($arrInvoices[0]);
	
	return $strReturn;
}

// -------------------------------------------------------------------------- //
// CSV FUNCTIONS
// -------------------------------------------------------------------------- //

//------------------------------------------------------------------------//
// CSVRow
//------------------------------------------------------------------------//
/**
 * CSVRow()
 * 
 * Formats an array of data as a CSV Row
 * 
 * Formats an array of data as a CSV Row
 * 
 * @param	string	$strTable			Tabe Name, used to define the CSV format
 * @param	array	$arrData			Data to be formated as CSV
 * @param	string	$strSeparator		optional field separator. defaults to ;
 * @param	string	$strTerminator		optional line terminator. defaults to \n (newline)
 *
 * @return	mixed						string: CSV line
 * 										FALSE: Table not found
 * 
 * @function
 */
function CSVRow($strTable, $arrData, $strSeparator=';', $strTerminator="\n")
{
	if (!is_array($GLOBALS['arrDatabaseTableDefine'][$strTable]['Column']))
	{
		return FALSE;
	}
	
	$strReturn = $strSeparator; // Id
	foreach($GLOBALS['arrDatabaseTableDefine'][$strTable]['Column'] AS $strKey => $arrValue)
	{
		$strReturn .= $arrData[$strKey].$strSeparator;
	}
	$strReturn .= $strTerminator;
	
	return $strReturn;
}

//------------------------------------------------------------------------//
// CSVHeader
//------------------------------------------------------------------------//
/**
 * CSVHeader()
 * 
 * Returns a CSV header row for a table
 * 
 * Returns a CSV header row for a table
 * 
 * @param	string	$strTable			Tabe Name, used to define the CSV format
 * @param	string	$strSeparator		optional field separator. defaults to ;
 * @param	string	$strTerminator		optional line terminator. defaults to \n (newline)
 *
 * @return	mixed						string: CSV line
 * 										FALSE: Table not found
 * 
 * @function
 */
function CSVHeader($strTable, $strSeparator=';', $strTerminator="\n")
{
	if (!is_array($GLOBALS['arrDatabaseTableDefine'][$strTable]['Column']))
	{
		return FALSE;
	}
	
	$strReturn = "Id".$strSeparator;
	foreach($GLOBALS['arrDatabaseTableDefine'][$strTable]['Column'] AS $strKey => $arrValue)
	{
		$strReturn .= "$strKey".$strSeparator;
	}
	$strReturn .= $strTerminator;
	
	return $strReturn;
}

//------------------------------------------------------------------------//
// CSVStatementSelect
//------------------------------------------------------------------------//
/**
 * CSVStatementSelect()
 * 
 * Formats an executed StatementSelect object as a CSV document
 * 
 * Formats an executed StatementSelect object as a CSV document.
 * 
 * @param	StatementSelect		$selStatement		The SQL Statement (Post Execution) being parse
 * @param	string	$strSeparator		optional field separator. defaults to ;
 * @param	string	$strTerminator		optional line terminator. defaults to \n (newline)
 *
 * @return	String
 * 
 * @function
 */
function CSVStatementSelect (StatementSelect $selStatement, $strSeparator=';', $strTerminator="\n")
{
	// Start with a Blank Result
	$strResult = "";
	
	// Pull the Meta Data for the Statement
	$objMetaData = $selStatement->MetaData ();
	
	
	// Get all the Fields and write a Heading row
	$arrFields = $objMetaData->fetch_fields ();
	$arrLabels = Array ();
	
	foreach ($arrFields as $intIndex => $objField)
	{
		$arrLabels [$intIndex] = str_replace ('"', '""', $objField->name);
	}
	
	$strResult .= '"' . implode ('"' . $strSeparator . '"', $arrLabels) . '"' . $strTerminator;
	
	// Get the results one by one and write them up
	while ($arrRow = $selStatement->Fetch ())
	{
		$arrFields = Array ();
		
		foreach ($arrRow as $intIndex => $mixField)
		{
			$mixCorrectedField = $mixField;
			$mixCorrectedField = str_replace	('"',				'""',	$mixCorrectedField);
			$mixCorrectedField = preg_replace	('/[\r\n]/misU',	' ',	$mixCorrectedField);
			
			$arrFields [$intIndex] = $mixCorrectedField;
		}
		
		$strResult .= '"' . implode ('"' . $strSeparator . '"', $arrFields) . '"' . $strTerminator;
	}
	
	return $strResult;
}


//------------------------------------------------------------------------//
// XLSStatementSelect
//------------------------------------------------------------------------//
/**
 * XLSStatementSelect()
 * 
 * Formats an executed StatementSelect object as an XLS document
 * 
 * Formats an executed StatementSelect object as an XLS document.
 * 
 * @param	StatementSelect		$selStatement		The SQL Statement (Post Execution) being parse
 *
 * @return	String
 * 
 * @function
 * 
 */
function XLSStatementSelect (StatementSelect $selStatement)
{
	// Create new instance of PsXLSGen
	$xlsExcelDoc = new PhpSimpleXlsGen();
	
	// Pull the Meta Data for the Statement
	$objMetaData = $selStatement->MetaData ();
	
	
	// Get all the Fields and write a Heading row
	$arrFields = $objMetaData->fetch_fields ();
	$xlsExcelDoc->totalcol = count($arrFields);
	
	foreach ($arrFields as $intIndex => $objField)
	{
		$xlsExcelDoc->InsertText($objField->name);
	}
	
	// Get the results one by one and write them up
	while ($arrRow = $selStatement->Fetch ())
	{
		foreach ($arrRow as $intIndex => $mixField)
		{
			$mixCorrectedField = trim($mixField);
			if (is_int($mixCorrectedField) || is_float($mixCorrectedField))
			{
				$xlsExcelDoc->InsertNumber($mixCorrectedField);
			}
			else
			{
				$xlsExcelDoc->InsertText($mixCorrectedField);
			}
		}
	}
	
	return $xlsExcelDoc->GetFileStream();
}

//------------------------------------------------------------------------//
// EchoLine
//------------------------------------------------------------------------//
/**
 * EchoLine()
 * 
 * Echos out a line to the output
 *
 * Echos out a line to the output
 * 
 * @param	string	$strText	The string to be echoed
 *
 * @return	void
 * 
 * @function
 * 
 */
function EchoLine($strText)
{
	echo $strText;
	if (substr(-1, 1) != "\n")
	{
		echo "\n";
	}
}


//------------------------------------------------------------------------//
// CheckLuhn INCOMPLETE
//------------------------------------------------------------------------//
/**
 * CheckLuhn()
 * 
 * Verify a number using the Luhn algorithm
 *
 * Verify a number using the Luhn algorithm
 * 
 * @param	mix	$mixNumber	the number to be checked
 *
 * @return	bool
 * 
 * @comments
 * @function
 * 
 */
function CheckLuhn($mixNumber)
{
	$card = (string)$mixNumber;
	$card = strrev($card);
	$total = 0;
	
	for ($n=0; $n<strlen($card); $n++)
	{
		$digit = substr($card,$n,1);
		if ($n/2 != floor($n/2))
		{
			$digit *= 2;
		}
		if (strlen($digit) == 2)
		{
			$digit = substr($digit,0,1) + substr($digit,1,1);
		}
		$total += $digit;
	}
	if ($total % 10 == 0)
	{
		return true;
	}
	else
	{
		return false;
	}
}

//------------------------------------------------------------------------//
// MakeLuhn INCOMPLETE
//------------------------------------------------------------------------//
/**
 * MakeLuhn()
 * 
 * Create a check digit using the Luhn formula
 *
 * Create a check digit using the Luhn formula
 * 
 * @param	mix	$mixNumber	the number to be used
 *
 * @return	int
 * 
 * @comments
 * @function
 * 
 */
function MakeLuhn($mixNumber)
{
	$card = (string)$mixNumber;
	$intCheckDigit = 0;
	for ($n=0; $n<strlen($card); $n++)
	{
		$digit = substr($card,$n,1);
		if ($n/2 != floor($n/2))
		{
			$digit *= 2;
		}
		if (strlen($digit) == 2)
		{
			$digit = substr($digit,0,1) + substr($digit,1,1);
		}
		$intCheckDigit += $digit;
	}
	
	$intCheckDigit = $intCheckDigit % 10;
	
	if($intCheckDigit > 0)
	{
		$intCheckDigit = 10 - $intCheckDigit;
	}
	return $intCheckDigit;
}

//------------------------------------------------------------------------//
// expdate
//------------------------------------------------------------------------//
/**
 * expdate()
 * 
 * Check if credit card has not expired
 *
 * Check if credit card has not expired
 * 
 * @param	int	$month	The month to check against
 * @param	int	$year	The year to check against
 *
 * @return	bool		FALSE if the date passed is in the past
 * 
 * @function
 * 
 */
function expdate($month,$year)
{
	if ( $year < date('Y') )
	{
		return FALSE;
	}
	elseif ( $year == date('Y') )
	{
		if ( $month < date('m') )
		{
			return FALSE;
		}
	}
	return true;
}

//------------------------------------------------------------------------//
// CheckCC
//------------------------------------------------------------------------//
/**
 * CheckCC()
 * 
 * Check the validity of a credit card
 * 
 * Check the validity of a credit card 
 *
 * @param	mix	$mixNumber			The cc number to check
 * @param	int	$intCreditCardType 	The constant which describes what type 
 *									of credit card (e.g Visa, Mastercard, etc) 
 *
 * @return	bool
 * 
 * @function
 * 
 */
function CheckCC($mixNumber, $intCreditCardType)
{
	$strNumber = str_replace (" ", "", $mixNumber);
	
	// Check the LUHN of the Credit Card
	if (!CheckLuhn ($strNumber))
	{
		return FALSE;
	}
	
	// Load up the values allowed for Prefixes and Lengths of each Credit Card Type
	switch ($intCreditCardType)
	{
		case CREDIT_CARD_VISA:
			$arrPrefixes	= Array (4);
			$arrLengths		= Array (13, 16);
			
			break;
			
		case CREDIT_CARD_MASTERCARD:
			$arrPrefixes	= Array (51, 52, 53, 54, 55);
			$arrLengths		= Array (16);
			
			break;
			
		case CREDIT_CARD_BANKCARD:
			$arrPrefixes	= Array (56);
			$arrLengths = Array (16);
			
			break;
			
		case CREDIT_CARD_AMEX:
			$arrPrefixes	= Array (34, 37);
			$arrLengths = Array (15);
			break;
			
		case CREDIT_CARD_DINERS:
			$arrPrefixes	= Array (30, 36, 38);
			$arrLengths = Array (14);
			break;
			
		default:
			return FALSE;
	}
	
	// Check the Length is Correct
	$bolLengthFound = FALSE;
	
	foreach ($arrLengths as $intLength)
	{
		if (strlen ($strNumber) == $intLength)
		{
			$bolLengthFound = TRUE;
		}
	}
	
	if (!$bolLengthFound)
	{
		return FALSE;
	}
	
	// If we have a prefix, check it's correct
	if (count ($arrPrefixes) <> 0)
	{
		$bolPrefixFound = FALSE;
		
		foreach ($arrPrefixes as $intPrefix)
		{
			if (substr ($strNumber, 0, strlen ($intPrefix)) == $intPrefix)
			{
				$bolPrefixFound = TRUE;
			}
		}
		
		if (!$bolPrefixFound)
		{
			return FALSE;
		}
	}
	
	return TRUE;
}

//------------------------------------------------------------------------//
// EmailAddressValid
//------------------------------------------------------------------------//
/**
 * EmailAddressValid()
 * 
 * Check the format of an email address
 * 
 * Check the format of an email address 
 *
 * @param	str	$strEmail			The email to check
 *
 * @return	bool
 * 
 * @function
 */
 
// check valid email address
// comes from: http://www.ilovejackdaniels.com/php/email-address-validation/

// code has been modified to reflect the coding standards and allow for comma-separated emails

// "RFC 2822, that specifies what is and is not allowed in an email address, 
// states that the form of an email address must be of the form "local-part @ domain"."
function EmailAddressValid ($strEmail)
{
	//TODO!flame! Fix this p.o.s up
	// First we split on ',' to allow multiple email addresses
	$arrEmails = explode(',', $strEmail);
	
	foreach ($arrEmails as $strEmailAddress)
	{
		// trim
		$strEmailAddress = trim($strEmailAddress);
		
		// First, we check that there's one @ symbol, and that the lengths are right
		
		// The "local-part" of an email address must be between 1 and 64 characters
		
		// The most common form is a domain name, which is made up of a number of 
		// "labels", each separated by a period and between 1 and 63 characters in 
		// length. Labels may contain letters, digits and hyphens, however must not 
		// begin or end with a hyphen
		
		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $strEmailAddress)) {
			// Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
			return false;
		}
		
		// Split it into sections to make life easier
		$arrEmail = explode("@", $strEmailAddress);
		$arrLocal = explode(".", $arrEmail [0]);
		
		for ($i = 0; $i < sizeof($arrLocal); $i++) {
			if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $arrLocal [$i])) {
				return false;
			}
		}
	
		if (!ereg("^\[?[0-9\.]+\]?$", $arrEmail [1])) { // Check if domain is IP. If not, it should be valid domain name
			$arrDomain = explode(".", $arrEmail [1]);
			
			if (sizeof ($arrDomain) < 2) {
				return false; // Not enough parts to domain
			}
			
			for ($i = 0; $i < sizeof($arrDomain); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $arrDomain [$i])) {
					return false;
				}
			}
		}
	}
	
	return true;
}

//------------------------------------------------------------------------//
// PostcodeValid
//------------------------------------------------------------------------//
/**
 * PostcodeValid()
 * 
 * Check the format of a postcode
 * 
 * Check the format of a postcode
 *
 * @param	str	$strPostcode	The postcode to check
 *
 * @return	bool
 * 
 * @function
 */
function PostcodeValid ($strPostcode)
{
	return preg_match ("/^\d{4}$/", $strPostcode);
}

//------------------------------------------------------------------------//
// PhoneNumberValid
//------------------------------------------------------------------------//
/**
 * PhoneNumberValid()
 * 
 * Check the format of a postcode
 * 
 * Check the format of a postcode
 *
 * @param	str	$strPostcode	The postcode to check
 *
 * @return	bool
 * 
 * @function
 */
function PhoneNumberValid ($strNumber)
{
	return preg_match ("/^\+?[\d\s]{10,}$/", $strNumber);
}

//------------------------------------------------------------------------//
// BSBValid
//------------------------------------------------------------------------//
/**
 * BSBValid()
 * 
 * Check the format of a BSB
 * 
 * Check the format of a BSB
 *
 * @param	str	$strNumber	The BSB to check
 *
 * @return	bool
 * 
 * @function
 */
function BSBValid ($strNumber)
{
	return preg_match ("/^\d{6}$/", $strNumber);
}

//------------------------------------------------------------------------//
// BankAccountValid
//------------------------------------------------------------------------//
/**
 * BankAccountValid()
 * 
 * Check the validity of a bank account number
 * 
 * Check the validity of a bank account number
 *
 * @param	str	$strNumber	The number to check
 *
 * @return	bool
 * 
 * @function
 */
function BankAccountValid ($strNumber)
{
	return preg_match ("/^\d{4,11}$/", $strNumber);
}

//------------------------------------------------------------------------//
// CliEcho
//------------------------------------------------------------------------//
/**
 * CliEcho()
 * 
 * Writes a string to stdout
 * 
 * Writes a string to stdout
 *
 * @param	string	$strOutput				The string to write to stdout
 * @param	boolean	$bolNewLine	optional	Whether to automatically add a new line character
 *
 * @return	string							The string written to stdout
 * 
 * @function
 */
function CliEcho($strOutput, $bolNewLine=TRUE)
{
	if (!$GLOBALS['**stdout'])
	{
		$GLOBALS['**stdout'] = fopen("php://stdout","w"); 
	}
	$stdout = $GLOBALS['**stdout'];
	$strOutput .= ($bolNewLine) ? "\n" : "";
	fwrite($stdout, $strOutput);
	
	return $strOutput;
}

//------------------------------------------------------------------------//
// RoundCurrency
//------------------------------------------------------------------------//
/**
 * RoundCurrency()
 * 
 * Rounds a currency  
 * 
 * Rounds a currency to a specified number of places
 *
 * @param	float	$fltValue	The value to round
 * @param	int		$intPlaces	The number of places to round it to (default 4)
 *
 * @return	mix					float: the rounded number
 *								FALSE: if there is an error
 * 
 * @function
 */
function RoundCurrency($fltValue, $intPlaces = 4)
{
	if (!is_numeric($fltValue) || !is_int($intPlaces) || $intPlaces < 1)
	{
		// Bad parameter list
		return FALSE;
	}
	
	$fltMultiple = (float)("1".str_repeat("0", $intPlaces));
	return round($fltValue * $fltMultiple) / $fltMultiple;
}

//------------------------------------------------------------------------//
// Json
//------------------------------------------------------------------------//
/**
 * Json()
 *
 * Returns the singleton Json object
 *
 * Returns the singleton Json object
 * Note that this will return a new Json object if one has not yet been
 * created.  If one has been created, it will return a reference to it.
 *
 * @return	Json object
 *
 * @function
 * 
 */
function Json()
{
	$objJson = Singleton::Instance('Services_JSON');
	return $objJson;
}

//------------------------------------------------------------------------//
// AjaxRecieve INCOMPLETE
//------------------------------------------------------------------------//
/**
 * AjaxRecieve()
 * 
 * Function to act as a reciever for AJAX data.  
 * 
 * Function to act as a reciever for AJAX data. Converts to and from JSON format.
 *
 * @return	str				
 *
 * @brokenreturn
 * @comments
 * 
 * @function
 */
function AjaxRecieve()
{
	//$json = new Services_JSON();
	// get the JSON object and decode it into an object
	$input = file_get_contents('php://input', 1000000);
	//$input = $json->decode($input);
	$input = Json()->decode($input);
	
	// expected to return an array of data if a connection was made
	// or false if not
	return $input;
}

//------------------------------------------------------------------------//
// AjaxReply
//------------------------------------------------------------------------//
/**
 * AjaxReply()
 * 
 * Send data via AJAX.
 * 
 * Send data via AJAX.
 *
 * @param	array	$arrReply				The array of data to send
 *
 * @return	void 
 *
 * @function
 */
function AjaxReply($arrReply)
{
	echo Json()->encode($arrReply);
	//$json = new Services_JSON();
	//echo $json->encode($arrReply);
}



//------------------------------------------------------------------------//
// VixenRequire()
//------------------------------------------------------------------------//
/**
 * VixenRequire()
 * 
 * require_once's a viXen file.
 * 
 * require_once's a viXen file, relative to the vixen base path.
 *
 * @param	string	$strFilename			The viXen-relative path to include
 *
 * @function
 */
function VixenRequire($strFilename)
{
	// Make sure we have a base path to work from
	if (!$GLOBALS['**strVixenBasePath'])
	{
		GetVixenBase();
	}
	
	require_once($GLOBALS['**strVixenBasePath'].$strFilename);
	return TRUE;
}


//------------------------------------------------------------------------//
// GetVixenBase()
//------------------------------------------------------------------------//
/**
 * GetVixenBase()
 * 
 * Finds the viXen base directory
 * 
 * Finds the viXen base directory.  Throws an exception if it can't resolve path
 *
 * @return		string			Full viXen base path
 *
 * @function
 */
function GetVixenBase()
{
	// Determin base dir
	if (!$GLOBALS['**strVixenBasePath'])
	{
		if (defined(VIXEN_BASE_PATH))
		{
			$GLOBALS['**strVixenBasePath'] = VIXEN_BASE_PATH;
		}
		else
		{
			// Interpret current dir
			$arrPath = explode('/', getcwd());
			$strVixenRoot	= "/";
			$strCurrent		= "";
			foreach ($arrPath as $strDir)
			{
				$strCurrent .= "$strDir/";
				if ($strDir === "vixen")
				{
					$strVixenRoot = $strCurrent;
				}
			}
			
			// Set path
			if ($strVixenRoot !== '/')
			{
				$GLOBALS['**strVixenBasePath'] = $strVixenRoot;
			}
			else
			{
				throw new Exception("Cannot find viXen base path");
			}
		}
	}
	return $GLOBALS['**strVixenBasePath'];
}



//------------------------------------------------------------------------//
// LoadFramework
//------------------------------------------------------------------------//
/**
 * LoadFramework()
 * 
 * Load the framework.
 * 
 * Load the framework.
 *
 * @param	str	$strFrameworkDir			The directory of the framework (default: NULL)
 *
 * @function
 */
function LoadFramework($strFrameworkDir=NULL)
{
	// Get viXen base dir
	if (!$strFrameworkDir)
	{
		$strFrameworkDir = GetVixenBase();
		$strFrameworkDir .= 'framework/';
	}
	
	// load framework
	require_once($strFrameworkDir."framework.php");
	require_once($strFrameworkDir."functions.php");
	require_once($strFrameworkDir."definitions.php");
	require_once($strFrameworkDir."config.php");
	require_once($strFrameworkDir."database_define.php");
	require_once($strFrameworkDir."db_access.php");
	require_once($strFrameworkDir."report.php");
	require_once($strFrameworkDir."error.php");
	require_once($strFrameworkDir."exception_vixen.php");

	
	// PEAR Packages
	require_once("Console/Getopt.php");
	require_once("Spreadsheet/Excel/Writer.php");
	require_once("Mail.php");
	require_once("Mail/mime.php");
	
	// create framework instance
	$GLOBALS['fwkFramework'] = new Framework();
	return $GLOBALS['fwkFramework'];
}

//------------------------------------------------------------------------//
// LoadApplication 
//------------------------------------------------------------------------//
/**
 * LoadApplication()
 * 
 * Load the application.
 * 
 * Load the application.
 *
 * @param	string	$strApplication		The directory of the application (default: NULL)
 *
 * @return	array						Configuration array from application's config.php
 * @function
 */
function LoadApplication($strApplication=NULL)
{
	// Has the framework been loaded?
	if (!$GLOBALS['fwkFramework'])
	{
		LoadFramework();
	}
	
	// application specified
	$strApplicationDir = $GLOBALS['**strVixenBasePath'];
	if ($strApplication)
	{
		// Load from different dir
		$strApplicationDir .= $strApplication;
	}
	else
	{
		// Load from this dir
		require_once("require.php");
		require_once("application.php");
		require_once("definitions.php");
		require_once("config.php");
		return $arrConfig;
	}
	
	// set application dir
	$strApplicationDir = $strApplication."/";
	
	// require application
	VixenRequire($strApplicationDir."require.php");
	VixenRequire($strApplicationDir."application.php");
	VixenRequire($strApplicationDir."definitions.php");
	VixenRequire($strApplicationDir."config.php");
	return $arrConfig;
}

//------------------------------------------------------------------------//
// CentreText 
//------------------------------------------------------------------------//
/**
 * CentreText()
 * 
 * Calculate starting column position to centre a line of text
 * 
 * Calculate starting column position to centre a line of text
 *
 * @param	str	$strText		The text to be input
 * @param 	int	$intWidth		The width of the text
 *
 * @return 	int					Starting column position
 *
 * @function
 */
function CentreText($strText, $intWidth)
{
	return floor(($intWidth / 2) - (strlen($strText) / 2));
}

//------------------------------------------------------------------------//
// MaskCreditCard
//------------------------------------------------------------------------//
/**
 * MaskCreditCard()
 *
 * Masks the credit card number for safe output
 *
 * Masks the credit card number for safe output
 *
 * @param	string	$strCardNumber	The credit card number to mask
 * @return	string
 *
 * @method
 * @see	<MethodName()||typePropertyName>
 */
function MaskCreditCard($strCardNumber)
{
	$intLen = strlen($strCardNumber);
	$strFilteredCC = substr($strCardNumber, 0, 4);
	for ($i = 1; $i <= ($intLen - 8); $i++)
	{
		$strFilteredCC .= "*";
	}
	$strFilteredCC .= substr($strCardNumber, -4, 4);
	return $strFilteredCC;
}

//------------------------------------------------------------------------//
// SecsToHMS
//------------------------------------------------------------------------//
/**
 * SecsToHMS()
 *
 * Converts an integer time to HHH:MM:SS
 *
 * Converts an integer time to HHH:MM:SS
 *
 * @param	integer	$intSeconds		The time to convert
 * @return	string
 *
 * @method
 */
function SecsToHMS($intSeconds)
{
	$intHours	= floor($intSeconds / 3600);
	$intMins	= floor(($intSeconds % 3600) / 60);
	$intSecs	= ($intSeconds % 3600) % 60;
	$strTime	= sprintf("%03d:%02d:%02d", $intHours, $intMins, $intSecs);
	return $strTime;
}

//------------------------------------------------------------------------//
// CorrectPartialAccountNum
//------------------------------------------------------------------------//
/**
 * CorrectPartialAccountNum()
 *
 * Corrects a partial account number to the proper format 
 *
 * Corrects a partial account number by prefixing '1' and enough
 * '0s' to make it 10 digits long if it is not allready 10 digits long
 *
 * @param	integer	$intPartialAccNum		The account number to convert.
 *											Assume this is less than or equal to 
 *											10 digits in length.
 *
 * @return	integer 						the corrected account number or
 *											FALSE if the partial account number
 *											could not be changed
 *
 * @function
 */
function CorrectPartialAccountNum($intPartialAccNum)
{	
	// check that $intPartialAccNum can be converted to a 10 digit account number
	if (!is_numeric($intPartialAccNum))
	{
		// the argument is not numeric so return false
		return FALSE;
	}
	
	// check if $intPartialAccNum is already 10 digits long
	if (strlen($intPartialAccNum) == 10)
	{
		// return the account number, unchanged
		return $intPartialAccNum;
	}
	
	// if the partial account number is longer than 10 digits, return false
	if (strlen($intPartialAccNum) > 10)
    {
		return FALSE;
	}
	
	// The partial number is less than 10 digits long so add 1000000000 to it
	return $intPartialAccNum + 1000000000;
}

//------------------------------------------------------------------------//
// CorrectShortAccountNum
//------------------------------------------------------------------------//
/**
 * CorrectShortAccountNum()
 *
 * Corrects an account number that is short on '0s' to the proper 10 digit format 
 *
 * This function is used to correct an account number that starts with
 * '10' but may not have enough '0s' in it to make it 10 digits long.
 * It is expected that the passed account number begins with at least '10'
 * For example: 1000439 will become 1000000439
 * 
 *
 * @param	integer	$intShortAccNum		The account number to convert.
 *										Assume this is less than or equal to 
 *										10 digits in length.
 *
 * @return	integer 					the corrected account number or
 *										FALSE if the short account number
 *										could not be fixed
 *
 * @function
 */
function CorrectShortAccountNum($intShortAccNum)
{	
	// check that $intShortAccNum can be converted to a 10 digit account number
	if (!is_numeric($intShortAccNum))
	{
		// the argument is not numeric so return false
		return FALSE;
	}

	// check that $intShortAccNum is no longer than 10 digits
	if (strlen($intPartialAccNum) > 10)
    {
echo "[strlength > 10]";
		// the arguement is an invalid account number
		return FALSE;
	}

	// If $intShortAccNum is exactly 10 characters long, then one can assume it
	// is already in the correct format.  This is to account for the case when
	// the account number is has no zeros between the leading "1" and the other
	// digits of the number.
	if (strlen($intPartialAccNum) == 10)
	{
echo "[strlength == 10]";
		// the account number must already be in a correct format
		return $intPartialAccNum;
	}

	// check that $intShortAccNum begins with "1"
	if (substr($intShortAccNum, 0, 2) != 10)
	{
echo "[accNum does not begin with 10]";	
		// $intShortAccNum does not begin with a '10' so return FALSE
		return FALSE;
	}

	// this line corrects the number of leading zeros in the account number
	// and makes sure that it starts with a leading "1"
	$strCorrectedAccNum = str_pad(substr($intShortAccNum, 1), 10, "1000000000", STR_PAD_LEFT);
	
	return $strCorrectedAccNum;
}

//------------------------------------------------------------------------//
// AccountExists
//------------------------------------------------------------------------//
/**
 * AccountExists()
 *
 * Checks if an account exists and is not archived.
 *
 * When passed an account number, this function checks if an account
 * associated with the number exists in the database butis not archived
 * in the database.
 *
 * @param	integer	$intAccNum	The account number to check.  Note that
 *								this function accepts partial account numbers
 *								and account numbers that are less than 10 digits
 *                              long.
 *
 * @return	integer 			The account number assuming it was found and 
 *								is not archived.  Otherwise it returns FALSE.
 *
 * @function
 */
function AccountExists($intAccNum)
{
	$strArchived = ACCOUNT_ACTIVE.", ".ACCOUNT_CLOSED.", ".ACCOUNT_DEBT_COLLECTION;
	$selAccount = new StatementSelect("Account", "Id", "Id = <Id> AND Archived IN ($strArchived)");
	
	// check for partial account number first
	if (strlen($intAccNum) < 10)
	{
		$intTemp = CorrectPartialAccountNum($intAccNum);
		if ((intTemp) && ($selAccount->Execute(Array('Id' => $intTemp))))
		{
			return $intTemp;
		}
		
		$intTemp = CorrectShortAccountNum($intAccNum);
		if (($intTemp) && ($selAccount->Execute(Array('Id' => $intTemp))))
		{
			return $intTemp;
		}
		
		return FALSE;
	}
	
	if ($selAccount->Execute(Array('Id' => $intAccNum)))
	{
		return $intAccNum;
	}
	
	return FALSE;
}

//------------------------------------------------------------------------//
// AddGST
//------------------------------------------------------------------------//
/**
 * AddGST()
 * 
 * Adds GST to the input amount
 * 
 * Adds GST to the input amount
 * 
 * @param	flt		$fltAmount			The input amount
 * 
 * @return	flt
 * 
 * @method
 */
function AddGST($fltAmount)
{
	if (!(float)$fltAmount)
	{
		return 0;
	}
	return (float)($fltAmount * ((TAX_RATE_GST / 100) + 1));
}

//------------------------------------------------------------------------//
// RemoveGST
//------------------------------------------------------------------------//
/**
 * RemoveGST()
 * 
 * Removes GST from the input amount
 * 
 * Removes GST from the input amount
 * 
 * @param	flt		$fltAmount			The input amount
 * 
 * @return	flt
 * 
 * @method
 */
function RemoveGST($fltAmount)
{
	if (!(float)$fltAmount)
	{
		return 0;
	}
	return (float)($fltAmount / ((TAX_RATE_GST / 100) + 1));
}

//------------------------------------------------------------------------//
// ClearScreen()
//------------------------------------------------------------------------//
/**
 * ClearScreen()
 *
 * Emulates a "clear" or "cls" shell command
 *
 * Emulates a "clear" or "cls" shell command
 *
 * @param	boolean	$bolReturn	optional	Returns the string value of screen clear
 * 											instead of outputting it (defaults to FALSE)
 *
 * @function
 */
 function ClearScreen($bolReturn = FALSE)
 {
 	if ($bolReturn)
 	{
 		return chr(27)."[H".chr(27)."[2J";
 	}
 	echo chr(27)."[H".chr(27)."[2J";
 }


//------------------------------------------------------------------------//
// ParseArguments
//------------------------------------------------------------------------//
/**
 * ParseArguments()
 *
 * Parses command line arguments and puts the data in a meaningful array
 *
 * Parses command line arguments and puts the data in a meaningful array
 * 
 * @param	array 	$arrConfig	Configuration for parsing the arguments
 *
 * @return	array 				Array of arguments and values
 *
 * @function
 */
function ParseArguments($arrConfig)
{
	// Use Console_Getopt to parse arguments
	$argGetOpt	= new Console_Getopt();
	$arrArgV	= $argGetOpt->readPHPArgv();
	
	// Get list of options
	$strAllowedOptions = "";
	foreach ($arrConfig['Option'] as $strName=>$arrDefinition)
	{
		$strAllowedOptions .= $arrDefinition['Switch'];
		$strAllowedOptions .= (isset($arrDefinition['Value']))			? ":" : "";	// Value optional
		$strAllowedOptions .= (isset($arrDefinition['MandatoryValue']))	? ":" : "";	// Value mandatory
	}
	
	// Parse arguments and check for error
	$arrArguments = $argGetOpt->getopt($arrArgV, $strAllowedOptions);
	if (PEAR::isError($arrArguments))
	{
		Debug("Fatal Error: Unsupported command line argument ('".$arrArguments->getMessage()."')");
		die;
	}
	
	// Check for -? switch
	if (in_array(Array('?', ''), $arrArguments[0]))
	{
		// FIXME: Remove this when HELP is implemented
		echo "\nHELP function currently unavailable\n\n";
		return FALSE;
		
		// Print the command line options
		// TODO
		return FALSE;
	}
	
	// Convert options to meaningful variables
	$arrReturn = Array();
	foreach ($arrConfig['Option'] as $strName=>$arrDefinition)
	{
		foreach ($arrArguments[0] as $arrArgument)
		{
			if ($arrDefinition['Switch'] == $arrArgument[0])
			{
				$arrReturn[$strName] = ($arrArgument[1]) ? $arrArgument[1] : TRUE;
			}
		}
	}
	
	// Any additional arguments
	$strCurrent = reset($arrArguments[1]);
	foreach ($arrConfig['Arguments'] as $strName=>$arrDefinition)
	{
		$arrReturn[$strName] = $strCurrent;
		if (!$strCurrent = next($strCurrent))
		{
			break;
		}
	}
	
	return $arrReturn;
}

//------------------------------------------------------------------------//
// IsAssociativeArray()
//------------------------------------------------------------------------//
/**
 * IsAssociativeArray()
 *
 * Determines if a passed array is associative or not
 *
 * Determines if a passed array is associative or not
 *
 * @param		array	$arrArray		Array to be checked
 * 
 * @return		boolean					true	: Associative array
 * 										false	: Indexed array
 *
 * @method
 * @see			<MethodName()||typePropertyName>
 */ 
function IsAssociativeArray($arrArray) 
{
	return (is_array($arrArray) && !is_numeric(implode(array_keys($arrArray))));
}


function Donkey()
{
?>


                          /\          /\
                         ( \\        // )
                          \ \\      // /
                           \_\\||||//_/ 
                            \/ _  _ \ 
                           \/|(O)(O)|
                          \/ |      |  
      ___________________\/  \      /
     //                //     |____|      
    //                ||     /      \
   //|                \|     \ 0  0 /
  // \       )         V    / \____/ 
 //   \     /        (     /
""     \   /_________|  |_/
       /  /\   /     |  ||
      /  / /  /      \  ||
      | |  | |        | ||
      | |  | |        | ||  
      |_|  |_|        |_||       
       \_\  \_\        \_\\

<?php
}

//------------------------------------------------------------------------//
// ArchiveAccounts()
//------------------------------------------------------------------------//
/**
 * ArchiveAccounts()
 *
 * Bulk Archives Accounts
 *
 * Bulk Archives Accounts
 *
 * @param		array	$arrArray		Array of Accounts to Archive
 * 
 * @return		integer					Number of Accounts affected
 *
 * @method
 */ 
function ArchiveAccounts($arrAccounts)
{
	if (!$arrAccounts)
	{
		return 0;
	}
	
	$strIn = implode(', ', $arrAccounts);
	$updAccounts = new StatementUpdate("Account", "Id IN ($strIn)", Array('Archived' => 1));
	return $updAccounts->Execute(Array('Archived' => 1), Array());
}




//------------------------------------------------------------------------//
// TransactionStart
//------------------------------------------------------------------------//
/**
 * TransactionStart()
 *
 * Starts a Transaction
 *
 * Starts a Transaction on the primary Database connection (when multiple connections
 * are supported)
 *
 * @return		boolean					TRUE	: Committed
 * 										FALSE	: Failed
 *
 * @method
 */ 
function TransactionStart()
{
	if (!$GLOBALS['dbaDatabase'])
	{
		// Can't start a new transaction if not connected
		return FALSE;
	}
	
	// Start Transaction
	return $GLOBALS['dbaDatabase']->TransactionStart();
}

//------------------------------------------------------------------------//
// TransactionRollback
//------------------------------------------------------------------------//
/**
 * TransactionRollback()
 *
 * Rolls back the current Transaction, then re-enables AutoCommit
 *
 * Rolls back the current Transaction, then re-enables AutoCommit
 *
 * @return		boolean					TRUE	: Rolled back
 * 										FALSE	: Failed
 *
 * @method
 */ 
function TransactionRollback()
{
	if (!$GLOBALS['dbaDatabase'])
	{
		// Can't start a new transaction if not connected
		return FALSE;
	}
	
	// Rollback Transaction
	return $GLOBALS['dbaDatabase']->TransactionRollback();
}

//------------------------------------------------------------------------//
// TransactionCommit
//------------------------------------------------------------------------//
/**
 * TransactionCommit()
 *
 * Commits the current Transaction, then re-enables AutoCommit
 *
 * Commits the current Transaction, then re-enables AutoCommit
 *
 * @return		boolean					TRUE	: Started
 * 										FALSE	: Failed
 *
 * @method
 */ 
function TransactionCommit()
{
	if (!$GLOBALS['dbaDatabase'])
	{
		// Can't start a new transaction if not connected
		return FALSE;
	}
	
	// Commit Transaction
	return $GLOBALS['dbaDatabase']->TransactionCommit();
}

//------------------------------------------------------------------------//
// SetDBConfig
//------------------------------------------------------------------------//
/**
 * SetDBConfig()
 *
 * Overrides the default connection config
 *
 * Overrides the default connection config.  MUST be run before an Application is 
 * created/loaded
 * 
 * @param		string	$strURL			optional	URL to connect to
 * @param		string	$strDatabase	optional	Database to connect to
 * @param		string	$strUser		optional	User to connect as
 * @param		string	$strPassword	optional	Password to connect with
 *
 * @return		boolean								TRUE	: Overridden
 * 													FALSE	: Failed
 *
 * @method
 */ 
function SetDBConfig($strURL=NULL, $strDatabase=NULL, $strUser=NULL, $strPassword=NULL)
{
	if ($GLOBALS['dbaDatabase'])
	{
		// Can't override if already connected
		return FALSE;
	}
	
	// Override
	$GLOBALS['**arrDatabase']['URL']		= ($strURL)			? $strURL		: $GLOBALS['**arrDatabase']['URL'];
	$GLOBALS['**arrDatabase']['Database']	= ($strDatabase)	? $strDatabase	: $GLOBALS['**arrDatabase']['Database'];
	$GLOBALS['**arrDatabase']['User']		= ($strUser)		? $strUser		: $GLOBALS['**arrDatabase']['User'];
	$GLOBALS['**arrDatabase']['Password']	= ($strPassword)	? $strPassword	: $GLOBALS['**arrDatabase']['Password'];
	
	return TRUE;
}




//------------------------------------------------------------------------//
// UnbilledServiceCDRTotal
//------------------------------------------------------------------------//
/**
 * UnbilledServiceCDRTotal()
 *
 * Calculates the Unbilled CDR Total for a Service
 *
 * Calculates the Unbilled CDR Total for a Service
 * 
 * @param		integer	$intService					Service to generate total for
 * @param		bool	$bolDontIncludeCreditCDRs	optional, Set to TRUE if you don't want to include Credit CDRs in the total
 * 
 * @return		float								Total excluding Tax
 *
 * @method
 */ 
function UnbilledServiceCDRTotal($intService, $bolDontIncludeCreditCDRs = FALSE)
{
	if ($bolDontIncludeCreditCDRs)
	{
		// Don't include credit CDRs in the calculation
		$strColumns		= "SUM(Charge) AS TotalCharged";
		$strWhereClause = "Service = <Service> AND (Status = ". CDR_RATED ." OR Status = ". CDR_TEMP_INVOICE .") AND Credit != 1";
	}
	else
	{
		// Include credit CDRs in the calculation
		$strColumns 	= "SUM(CASE WHEN Credit = 1 THEN 0 - Charge ELSE Charge END) AS TotalCharged";
		$strWhereClause = "Service = <Service> AND (Status = ". CDR_RATED ." OR Status = ". CDR_TEMP_INVOICE .")";
	}
	
	// Get CDR Total
	$selCDRTotal = new StatementSelect("CDR", $strColumns, $strWhereClause);
	$selCDRTotal->Execute(Array('Service' => $intService));
	$arrCDRTotal = $selCDRTotal->Fetch();
	
	return $arrCDRTotal['TotalCharged'];
}

//------------------------------------------------------------------------//
// UnbilledServiceChargeTotal
//------------------------------------------------------------------------//
/**
 * UnbilledServiceChargeTotal()
 *
 * Calculates the Unbilled Adjustment Total for a Service
 *
 * Calculates the Unbilled Adjustment Total for a Service.  Only includes Approved Adjustments
 * 
 * @param		integer	$intService					Service to generate total for
 * 
 * @return		float								Total excluding Tax
 *
 * @method
 */ 
function UnbilledServiceChargeTotal($intService)
{
	// Get Adjustment Total
	$selChargeTotal = new StatementSelect("Charge", "SUM(CASE WHEN Nature = 'CR' THEN 0 - Amount ELSE Amount END) AS TotalCharged", "Service = <Service> AND Status = ".CHARGE_APPROVED);
	$selChargeTotal->Execute(Array('Service' => $intService));
	$selChargeTotal = $selChargeTotal->Fetch();
	
	return $selChargeTotal['TotalCharged'];
}

//------------------------------------------------------------------------//
// UnbilledAccountCDRTotal
//------------------------------------------------------------------------//
/**
 * UnbilledAccountCDRTotal()
 *
 * Calculates the Unbilled CDR Total for an Account
 *
 * Calculates the Unbilled CDR Total for an Account.  Does not account for Adjustments
 * 
 * @param		integer	$intAccount					Account to generate total for
 * @param		bool	$bolDontIncludeCreditCDRs	optional, Set to TRUE if you don't want to include Credit CDRs in the total
 *
 * @return		float								Total excluding Tax
 *
 * @method
 */ 
function UnbilledAccountCDRTotal($intAccount, $bolDontIncludeCreditCDRs = FALSE)
{
	if ($bolDontIncludeCreditCDRs)
	{
		// Don't include credit CDRs in the calculation
		$strColumns		= "SUM(Charge) AS TotalCharged";
		$strWhereClause = "Account = <Account> AND (Status = ".CDR_RATED ." OR Status = ". CDR_TEMP_INVOICE .") AND Credit != 1";
	}
	else
	{
		// Include credit CDRs in the calculation
		$strColumns 	= "SUM(CASE WHEN Credit = 1 THEN 0 - Charge ELSE Charge END) AS TotalCharged";
		$strWhereClause = "Account = <Account> AND (Status = ".CDR_RATED ." OR Status = ". CDR_TEMP_INVOICE .")";
	}

	// Get CDR Total
	$selCDRTotal = new StatementSelect("CDR", $strColumns, $strWhereClause);
	$selCDRTotal->Execute(Array('Account' => $intAccount));
	$arrCDRTotal = $selCDRTotal->Fetch();
	
	return $arrCDRTotal['TotalCharged'];
}


//------------------------------------------------------------------------//
// GetCurrentPlan
//------------------------------------------------------------------------//
/**
 * GetCurrentPlan()
 *
 * Gets the current plan for a specified Service
 *
 * Gets the current plan for a specified Service
 * 
 * @param		integer	$intService					Service to find a plan for
 *
 * @return		integer								Plan Id
 *
 * @method
 */ 
function GetCurrentPlan($intService)
{
	// This should really be ordered by CreatedOn DESC not StartDatetime DESC
	//$selRatePlan = new StatementSelect("ServiceRatePlan", "RatePlan", "Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime", "StartDatetime DESC", 1);
	$selRatePlan = new StatementSelect("ServiceRatePlan", "RatePlan", "Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime", "CreatedOn DESC", 1);
	$selRatePlan->Execute(Array('Service' => $intService));
	$arrRatePlan = $selRatePlan->Fetch();
	return ($arrRatePlan) ? $arrRatePlan['RatePlan'] : FALSE;
}

//------------------------------------------------------------------------//
// InvoicePDFExists
//------------------------------------------------------------------------//
/**
 * InvoicePDFExists()
 *
 * Checks if the invoice pdf exists for the given month, year and account id
 *
 * Checks if the invoice pdf exists for the given month, year and account id
 * 
 * @param		integer	$intAccountId		the invoice's associated Account
 * @param		integer $intYear			numeric repressentation of the year relating to the invoice's pdf (4 digit year)
 * @param		integer	$intMonth			numeric repressentation of the month relating to the invoice's pdf
 *
 * @return		boolean						TRUE if the pdf was found, else 
 *
 * @method
 */ 
function InvoicePDFExists($intAccountId, $intYear, $intMonth)
{
	$strGlob = PATH_INVOICE_PDFS ."$intYear/$intMonth/{$intAccountId}_*.pdf";
	$arrPDFs = glob($strGlob);
	
	if (count($arrPDFs))
	{
		return TRUE;
	}
	return FALSE;
}

//------------------------------------------------------------------------//
// GetPdfFilename
//------------------------------------------------------------------------//
/**
 * GetPdfFilename()
 * 
 * Return the filename of a PDF invoice in a string
 * 
 * Return the filename of a PDF invoice in a string for the specified account, year, and month
 * 
 * @param	int		$intAccount			Account to find PDFs for
 * @param	int		$intYear			Year to match
 * @param	int		$intMonth			Month to match
 *
 * @return	mixed						string: filename of the PDF invoice
 * 										FALSE: there was an error
 * 
 * @function
 */
function GetPdfFilename($intAccount, $intYear, $intMonth)
{
	$arrReturn = Array();
	
	// GLOB for account filename
	$arrInvoices = glob(PATH_INVOICE_PDFS . $intYear."/".$intMonth."/".$intAccount."_*.pdf");
	
	if (count($arrInvoices) == 0 || $arrInvoices === FALSE)
	{
		// Either glob had an error, or the filename doesn't exist
		return FALSE;
	}
	
	//grab the filename
	$strFilename = explode("/", $arrInvoices[0]);
	$strFilename = $strFilename[(count($strFilename)-1)];
	
	return $strFilename;
}


 
 
//------------------------------------------------------------------------//
// FindFNNOwner
//------------------------------------------------------------------------//
/**
 * FindFNNOwner()
 *
 * Finds the owner of a given FNN for a given date and time
 *
 * Finds the owner of a given FNN for a given date and time
 * 
 * @param	string	$strFNN				FNN to find owner for
 * @param	string	$strDatetime		Date to find owner on
 *
 * @return	bool					
 *
 * @method
 */
 function FindFNNOwner($strFNN, $strDate)
 {
	// Passthrough to Framework::FindFNNOwner()
 	return $GLOBALS['fwkFramework']->FindFNNOwner($strFNN, $strDate);
 }
 



 
//------------------------------------------------------------------------//
// WriteOffAccount
//------------------------------------------------------------------------//
/**
 * WriteOffAccount()
 *
 * Writes off all outstanding debt for a given Account
 *
 * Writes off all outstanding debt for a given Account
 * 
 * @param	integer		$intAccount				Account to write off
 *
 * @return	float								Value of written-off invoices					
 *
 * @method
 */
function WriteOffAccount($intAccount)
{
	// Find all Invoices to write off
	$fltTotal		= 0;
	$strStatus		= implode(', ', Array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_SETTLED, INVOICE_DISPUTED_SETTLED));
	$selInvoices	= new StatementSelect("Invoice", "*", "Account = <Account> AND Status IN ($strStatus)");
	if ($intInvoices	= $selInvoices->Execute(Array('Account' => $intAccount)))
	{
		// Write off each Invoice
		while ($arrInvoice = $selInvoices->Fetch())
		{
			$fltTotal			+= WriteOffInvoice($arrInvoice['Id'], FALSE);
			$intAccountGroup	= $arrInvoice['AccountGroup'];
		}
		
		// Add System Note
		$strContent	= "$intInvoices Invoices written off for the value of \${$fltTotal}";
		$GLOBALS['fwkFramework']->AddNote($strContent, 7, NULL, $intAccountGroup, $intAccount);
	}

	return $fltTotal;
}


//------------------------------------------------------------------------//
// WriteOffInvoice
//------------------------------------------------------------------------//
/**
 * WriteOffInvoice()
 *
 * Writes off all outstanding debt for a given Invoice
 *
 * Writes off all outstanding debt for a given Invoice
 * 
 * @param	integer		$intInvoice					Invoice to write off
 * @param	boolean		$bolAddNote		[optional]	Add a System Note about the write-off (default: TRUE)
 *
 * @return	float									Value of written-off invoice
 *
 * @method
 */
function WriteOffInvoice($intInvoice, $bolAddNote = TRUE)
{
	// Find Invoice
	$arrData = Array();
	$arrData['Id']			= $intInvoice;
	$arrData['Status']		= INVOICE_WRITTEN_OFF;
	$arrData['SettledOn']	= new MySQLFunction("CURDATE()");
	$selInvoice	= new StatementSelect("Invoice", "*", "Id = <Id>");
	$ubiInvoice	= new StatementUpdateById("Invoice", $arrData);
	$selInvoice->Execute($arrData);
	if ($arrInvoice	= $selInvoice->Fetch())
	{
		// Write off Invoice
		$ubiInvoice->Execute($arrData);
		
		// Add System Note
		if ($bolAddNote)
		{
			$strContent	= "1 Invoice written off for the value of \${$arrInvoice['Balance']}";
			$GLOBALS['fwkFramework']->AddNote($strContent, 7, NULL, $arrInvoice['AccountGroup'], $arrInvoice['Account']);
		}
		
		return $arrInvoice['Balance'];
	}
	else
	{
		return 0;
	}
}


//------------------------------------------------------------------------//
// SendEmail
//------------------------------------------------------------------------//
/**
 * SendEmail()
 *
 * Sends a simple email
 *
 * Sends a simple email
 * 
 * @param	string		$strAddresses				Comma-separated list of addresses to send to
 * @param	string		$strSubject					Subject for the email
 * @param	string		$strContent					Email content
 * @param	string		$strFrom		[optional]	Sent from
 *
 * @return	boolean									Pass/Fail
 *
 * @method
 */
function SendEmail($strAddresses, $strSubject, $strContent, $strFrom='rich@voiptelsystems.com.au')
{
	$arrHeaders = Array	(
							'From'		=> $strFrom,
							'Subject'	=> $strSubject
						);
	$mimMime = new Mail_mime("\n");
	$mimMime->setTXTBody($strContent);
	
	$strBody = $mimMime->get();
	$strHeaders = $mimMime->headers($arrHeaders);
	$emlMail =& Mail::factory('mail');
	
	// Send the email
	return (bool)$emlMail->send($strAddresses, $strHeaders, $strBody);
}


//------------------------------------------------------------------------//
// GetCCType
//------------------------------------------------------------------------//
/**
 * GetCCType()
 * 
 * Takes a Credit Card number and finds what bank it comes from
 * 
 * Takes a Credit Card number and finds what bank it comes from
 *
 * @param	mix		$mixNumber			The CC number to check
 * @param	boolean	$bolAsString		TRUE	: Returns a string (bank name)
 * 										FALSE	: Returns an integer (constant value)
 *
 * @return	mix							Bank name, Constant Value depending on $bolAsString, or FALSE on failure
 * 
 * @function
 * 
 */
function GetCCType($mixNumber, $bolAsString = FALSE)
{
	$intNumber = (int)str_replace (" ", "", $mixNumber);
	
	// Find Card Type
	switch ((int)substr($intNumber, 0, 2))
	{
		// VISA
		case 40:
		case 41:
		case 42:
		case 43:
		case 44:
		case 45:
		case 46:
		case 47:
		case 48:
		case 49:
			$strType	= "VISA";
			$intType	= CREDIT_CARD_VISA;
			break;
		
		// Mastercard
		case 51:
		case 52:
		case 53:
		case 54:
		case 55:
			$strType	= "MasterCard";
			$intType	= CREDIT_CARD_MASTERCARD;
			break;
		
		// Bankcard
		case 56:
			$strType	= "Bankcard";
			$intType	= CREDIT_CARD_BANKCARD;
			break;
		
		// AMEX
		case 34:
		case 37:
			$strType	= "American Express";
			$intType	= CREDIT_CARD_AMEX;
			break;
		
		// Diners
		case 30:
		case 36:
		case 38:
			$strType	= "Diners Club";
			$intType	= CREDIT_CARD_DINERS;
			break;
		
		default:
			return FALSE;
	}
	
	// Return requested value
	if ($bolAsString)
	{
		return $strType;
	}
	else
	{
		return $intType;
	}
}


//------------------------------------------------------------------------//
// AddCreditCardSurcharge
//------------------------------------------------------------------------//
/**
 * AddCreditCardSurcharge()
 *
 * Adds a surcharge to the given Account for the specified transaction
 *
 * Adds a surcharge to the given Account for the specified transaction
 * 
 * @param	integer		$intPayment				Comma-separated list of addresses to send to
 *
 * @return	boolean								Pass/Fail
 *
 * @method
 */
function AddCreditCardSurcharge($intPayment)
{
	// Statements
	$selAccount	= new StatementSelect("Account", "Id AS Account", "AccountGroup = <AccountGroup>", "(Archived != 1) DESC, Archived ASC, Account DESC", "1");
	$selPayment	= new StatementSelect("Payment", "*", "Id = <Payment>");
	$insCharge	= new StatementInsert("Charge");
	$selCCSRate	= new StatementSelect(	"Config",
										"Value",
										"Application = ".APPLICATION_PAYMENTS." AND Module = <Module> AND Name = 'Surcharge'");
	
	// Get Payment details
	if ($selPayment->Execute(Array('Payment' => $intPayment)))
	{
		$arrPayment = $selPayment->Fetch();
		
		// Find Credit Card Type and Rate
		$strType	= GetCCType($arrPayment['OriginId'], TRUE);
		if (!$selCCSRate->Execute(Array('Module' => $strType)))
		{
			// Cannot find Surcharge Rate
			return FALSE;
		}
		$arrCSSRate			= $selCCSRate->Fetch();
		$fltPC				= (float)$arrCSSRate['Value'];
		$strDate			= date("d/m/Y", strtotime($arrPayment['PaidOn']));
		$strPC				= round($fltPC * 100, 2);
		$fltPaymentAmount	= number_format($arrPayment['Amount'], 2, ".", "");
		$fltAmount			= RemoveGST(((float)$arrPayment['Amount'] / (1 + $fltPC)) * $fltPC);
		
		// Insert Charge
		$arrCharge	= Array();
		if (!$arrPayment['Account'])
		{
			// AccountGroup Payment
			$selAccount->Execute($arrPayment);
			$arrAccount				= $selAccount->Fetch();
			$arrCharge['Account']	= $arrAccount['Account'];
		}
		else
		{
			// Account Payment
			$arrCharge['Account']	= $arrPayment['Account'];
		}
		
		$arrCharge['AccountGroup']	= $arrPayment['AccountGroup'];
		$arrCharge['CreatedBy']		= $arrPayment['EnteredBy'];
		$arrCharge['CreatedOn']		= date("Y-m-d");
		$arrCharge['ChargeType']	= "CCS";
		$arrCharge['Description']	= "$strType Surcharge for Payment on {$strDate} (\${$fltPaymentAmount}) @ $strPC%";
		$arrCharge['ChargedOn']		= $arrPayment['PaidOn'];
		$arrCharge['Nature']		= 'DR';
		$arrCharge['Amount']		= $fltAmount;
		$arrCharge['Notes']			= '';
		$arrCharge['Status']		= CHARGE_APPROVED;
		$arrCharge['LinkType']		= CHARGE_LINK_PAYMENT;
		$arrCharge['LinkId']		= $intPayment;
		$mixResult = $insCharge->Execute($arrCharge);
		//Debug($arrCharge);
		return (bool)($mixResult !== FALSE);
	}
	else
	{
		// Can't find Payment
		return FALSE;
	}
}
	
//------------------------------------------------------------------------//
// IsInvoicing
//------------------------------------------------------------------------//
/**
 * IsInvoicing()
 *
 * Checks if the Invoicing process is currently running
 *
 * Checks if the Invoicing process is currently running
 * 
 * @return	boolean			Returns TRUE if the Invoicing process is currently underway, else returns FALSE
 *
 * @function
 */
function IsInvoicing()
{
	$selInvoiceTemp = new StatementSelect("InvoiceTemp", "Id", "", "", "1");
	$intRows = $selInvoiceTemp->Execute();
	
	// If there are records in the InvoiceTemp Table, then the invoicing process is occurring
	if ($intRows)
	{
		return TRUE;
	}
	
	return FALSE;
}

//------------------------------------------------------------------------//
// GetCurrentDateAndTimeForMySQL
//------------------------------------------------------------------------//
/**
 * GetCurrentDateAndTimeForMySQL()
 *
 * Retrieves the current date and time in the format that MySql expects datetime attributes to be in
 *
 * Retrieves the current date and time in the format that MySql expects datetime attributes to be in
 * This current time is taken from the database
 *
 * @return	string			current date and time as a string, properly formatted for MySql
 *							(YYYY-MM-DD HH:MM:SS)
 * @function
 */
function GetCurrentDateAndTimeForMySQL()
{
	// HACK HACK HACK!!!
	// StatementSelect doesn't work unless you specify a table name
	$selDatetime = new StatementSelect("Account", Array("CurrentTime" => "NOW()"));
	$selDatetime->Execute();
	$arrDatetime = $selDatetime->Fetch();

	return $arrDatetime['CurrentTime'];
}

//------------------------------------------------------------------------//
// GetCurrentDateForMySQL
//------------------------------------------------------------------------//
/**
 * GetCurrentDateForMySQL()
 *
 * Retrieves the current date in the format that MySql expects Date attributes to be in
 *
 * Retrieves the current date in the format that MySql expects Date attributes to be in
 *
 * @return	mix			current date as a string, properly formatted for MySql
 *						(YYYY-MM-DD)
 *
 * @function
 */
function GetCurrentDateForMySQL()
{
	return date("Y-m-d", strtotime(GetCurrentDateAndTimeForMySQL()));
}

//------------------------------------------------------------------------//
// GetCurrentTimeForMySQL
//------------------------------------------------------------------------//
/**
 * GetCurrentTimeForMySQL()
 *
 * Retrieves the current time in the format that MySql expects time attributes to be in
 *
 * Retrieves the current time in the format that MySql expects time attributes to be in
 *
 * @return	mix			current time as a string, properly formatted for MySql
 *						(HH:MM:SS)
 *
 * @function
 */
function GetCurrentTimeForMySQL()
{
	return date("H:i:s", strtotime(GetCurrentDateAndTimeForMySQL()));
}

//------------------------------------------------------------------------//
// GenerateLatePaymentNotices
//------------------------------------------------------------------------//
/**
 * GenerateLatePaymentNotices()
 *
 * Generates the appropriate Late Payment Notices
 *
 * Generates the appropriate Late Payment Notices
 *
 * @param	int		$intNoticeType	type of notice to be made. ie LETTER_TYPE_SUSPENSION
 * @param	string	$strBasePath	optional, path where the generated notices will be placed
 *									
 * @return	mixed					returns FALSE on failure 
 *									returns	Array['Successful']	= number of successfully generated notices of the NoticeType
 *											Array['Failed'] 	= number of notices that failed to generate, of the NoticeType
 * @function
 */
function GenerateLatePaymentNotices($intNoticeType, $strBasePath="./")
{
	$selPriorNotices = new StatementSelect("AccountLetterLog", "Id", "Invoice = <InvoiceId> AND LetterType = <NoticeType>", "", 1);

	// Append a backslash to the path, if it doesn't already end in one
	if (substr($strBasePath, -1) != "/")
	{
		$strBasePath .= "/";
	}

	// Set up NoticeType specific stuff here
	switch ($intNoticeType)
	{
		case LETTER_TYPE_OVERDUE:
		case LETTER_TYPE_SUSPENSION:
			$arrApplicableAccountStatuses = Array(ACCOUNT_ACTIVE, ACCOUNT_CLOSED);
			break;
		case LETTER_TYPE_FINAL_DEMAND:
			$arrApplicableAccountStatuses = Array(ACCOUNT_ACTIVE, ACCOUNT_CLOSED, ACCOUNT_SUSPENDED);
			break;
		default:
			// Unrecognised notice type
			return FALSE;
			break;
	}
	
	// Retrieve the list of CustomerGroups
	$selCustomerGroups = new StatementSelect("CustomerGroup", "Id, InternalName, ExternalName");
	$selCustomerGroups->Execute();
	$arrCustomerGroups = Array();
	while (($arrCustomerGroup = $selCustomerGroups->Fetch()) !== FALSE)
	{
		$arrCustomerGroups[$arrCustomerGroup['Id']] = $arrCustomerGroup;
	}
	
	// Find all Accounts that fit the requirements for Late Notice generation
	$arrColumns = Array(	'AccountId'				=> "Invoice.Account",
							'AccountGroup'			=> "Account.AccountGroup",
							'BusinessName'			=> "Account.BusinessName",
							'TradingName'			=> "Account.TradingName",
							'CustomerGroup'			=> "Account.CustomerGroup",
							'AccountStatus'			=> "Account.Archived",
							'LatePaymentAmnesty'	=> "Account.LatePaymentAmnesty", 
							'FirstName'				=> "Contact.FirstName",
							'LastName'				=> "Contact.LastName",
							'AddressLine1'			=> "Account.Address1",
							'AddressLine2'			=> "Account.Address2",
							'Suburb'				=> "Account.Suburb",
							'Postcode'				=> "Account.Postcode",
							'State'					=> "Account.State",
							'InvoiceId'				=> "MAX(CASE WHEN CURDATE() > Invoice.DueOn THEN Invoice.Id END)",
							'OutstandingNotOverdue'	=> "SUM(CASE WHEN CURDATE() <= Invoice.DueOn THEN Invoice.Balance END)",
							'Overdue'				=> "SUM(CASE WHEN CURDATE() > Invoice.DueOn THEN Invoice.Balance END)",
							'TotalOutstanding'		=> "SUM(Invoice.Balance)");
	
	
	$strTables	= "Invoice JOIN Account ON Invoice.Account = Account.Id JOIN Contact ON Account.PrimaryContact = Contact.Id";
	$strWhere	= "Account.DisableLateNotices = 0 AND Account.Archived IN (". implode(", ", $arrApplicableAccountStatuses) .")";
	$strOrderBy	= "Invoice.Account ASC";
	$strGroupBy	= "Invoice.Account HAVING Overdue > ". $GLOBALS['**arrCustomerConfig']['AccountNotice']['LateNoticeModule']['AcceptableOverdueBalance'];
	
	$selOverdue = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "", $strGroupBy);
	$mixResult = $selOverdue->Execute();

	if ($mixResult === FALSE)
	{
		// Failed to retrieve the data from the database
		return FALSE;
	}
	
	// Store a running total of how many were successfully generated, and how many failed, for each notice type
	$arrGeneratedNotices = Array("Successful" => 0, "Failed" => 0);
	$arrSummary = Array();
	
	// For each account retrieved, work out if a late payment notice really has to be made for it
	$arrAccounts	= $selOverdue->FetchAll();
	$strToday		= date("Y-m-d");
	$intToday		= strtotime($strToday);
	foreach ($arrAccounts as $arrAccount)
	{
		// Check if the account has a LatePayment amnesty period
		if (($arrAccount['LatePaymentAmnesty'] !== NULL) && ($intToday < strtotime($arrAccount['LatePaymentAmnesty'])))
		{
			// The account is within its LatePayment amnesty.  Don't produce Late Notices
			// (This is primarily to veto the production of Final Demand notices, as they are generated after the 
			// following bill is committed which sets DisableLateNotices to DisableLateNotices+1 for all accounts 
			// where DisableLateNotices < 0)
			continue; 
		}
		
		$bolSuccess = NULL;
		
		switch ($intNoticeType)
		{
			case LETTER_TYPE_OVERDUE:
				// If the account has a status of "Active" or "Closed", then they are eligible for recieving late notices
				// This condition is forced in the WHERE clause of the $selOverdue StatementSelect object
				$bolSuccess = BuildLatePaymentNotice(LETTER_TYPE_OVERDUE, $arrAccount, $strBasePath);
				break;
			
			case LETTER_TYPE_SUSPENSION:
				// Check if the Overdue Notice was built this month, and if so build the suspension notice
				$intNumRows = $selPriorNotices->Execute(Array("InvoiceId" => $arrAccount['InvoiceId'], "NoticeType" => LETTER_TYPE_OVERDUE));
				if ($intNumRows == 1)
				{
					// An "Overdue" notice has been sent for this invoice.  Build the Suspension notice
					$bolSuccess = BuildLatePaymentNotice(LETTER_TYPE_SUSPENSION, $arrAccount, $strBasePath);
				}
				break;
			
			case LETTER_TYPE_FINAL_DEMAND:
				// Check if the Suspension Notice was built this month, and if so build the final demand notice
				$intNumRows = $selPriorNotices->Execute(Array("InvoiceId" => $arrAccount['InvoiceId'], "NoticeType" => LETTER_TYPE_SUSPENSION));
				if ($intNumRows == 1)
				{
					// A "Suspension" notice has been sent for this invoice.  Build the Final Demand notice
					$bolSuccess = BuildLatePaymentNotice(LETTER_TYPE_FINAL_DEMAND, $arrAccount, $strBasePath);
				}
				break;
		}
		
		if ($bolSuccess !== NULL)
		{
			if ($bolSuccess == TRUE)
			{
				$arrGeneratedNotices['Successful'] += 1;
			}
			else 
			{
				$arrGeneratedNotices['Failed'] += 1;
			}
			
			$arrSummary[] = Array(	"AccountId"					=> $arrAccount['AccountId'], 
									"Outcome"					=> (($bolSuccess)? "Successful":"Failed"),
									"BusinessName"				=> $arrAccount['BusinessName'],
									"TradingName"				=> $arrAccount['TradingName'],
									"CustomerGroupInternalName"	=> $arrCustomerGroups[$arrAccount['CustomerGroup']]['InternalName'],
									"CustomerGroupExternalName"	=> $arrCustomerGroups[$arrAccount['CustomerGroup']]['ExternalName'],
									"OutstandingNotOverdue"		=> $arrAccount['OutstandingNotOverdue'],
									"Overdue"					=> $arrAccount['Overdue'],
									"TotalOutstanding"			=> $arrAccount['TotalOutstanding']);
		}
	}
	
	// Build the summary file
	$strFilename = 	str_replace(" ", "_", strtolower(GetConstantDescription($intNoticeType, "LetterType"))). 
					"_summary_". date("Y_m_d") .".csv";
	$ptrSummaryFile = fopen($strBasePath . $strFilename, 'wt');
	if ($ptrSummaryFile !== FALSE)
	{
		fputcsv($ptrSummaryFile, Array("Account Id", "Outcome", "Business Name", "Trading Name", "Customer Group (internal)", "Customer Group (external)", "Outstanding Not Overdue", "Overdue", "Total Outstanding"), ";");
		foreach ($arrSummary as $arrAccount)
		{
			fputcsv($ptrSummaryFile, $arrAccount, ";");
		}
		
		fclose($ptrSummaryFile);
	}
	
	// Record the summary file in the FileExport table
	//TODO! Fix up the Carrier, Status and FileType values so that they are meaningful
	$arrFileLog = Array(	'FileName'		=>	$strFilename,
							'Location'		=>	ltrim($strBasePath, "."),
							'Carrier'		=>	0,
							'ExportedOn'	=>	date("Y-m-d H:i:s"),
							'Status'		=>	0,
							'FileType'		=>	0,
							'SHA1'			=>	sha1($strFilename));

	$insFileExport = new StatementInsert("FileExport", $arrFileLog);
	$insFileExport->Execute($arrFileLog);
	
	return $arrGeneratedNotices;
}

//------------------------------------------------------------------------//
// UpdateDisableLateNoticesSetting
//------------------------------------------------------------------------//
/**
 * UpdateDisableLateNoticesSetting()
 *
 * Updates the Account.DisableLateNotices property
 *
 * For each account that has DisableLateNotices set to "Don't send late 
 * notices until next invoice", this will reset it to "Send Late Notices"
 * This function should be performed after a bill run is committed
 *
 * @return	mixed					int		:	number of accounts affected
 * 									FALSE	:	Update failed
 * @function
 */
function UpdateDisableLateNoticesSetting()
{
	// Having Late Notices disabled for more than 1 month but less than indefinite, is not currently handled
	$arrColumns = Array("DisableLateNotices" => "DisableLateNotices + 1");
	$updDisableLateNoticeSetting = new StatementUpdate("Account", "DisableLateNotices < 0", $arrColumns);
	
	return $updDisableLateNoticeSetting->Execute($arrColumns);
}

//------------------------------------------------------------------------//
// RecursiveMkdir
//------------------------------------------------------------------------//
/**
 * RecursiveMkdir()
 *
 * Performs the mkdir function recursively to allow construction of an entire directory path in one go
 *
 * Performs the mkdir function recursively to allow construction of an entire directory path in one go
 *
 * @param	string	$strPath		path to make ie "./This/is/a/path"
 * @param	integer	$intMode		permissions for the directories
 *									
 * @return	bool					TRUE on success, else FALSE
 *
 * @function
 */
function RecursiveMkdir($strPath, $intMode = 0777)
{
    // Remove leading / on absolute paths
    $strCumulativePath = "";
    if (substr($strPath, 0, 1) == '/')
    {
    	$strPath			= substr($strPath, 1);
    	$strCumulativePath	= '/';
    }

    $arrDirs = explode('/' , $strPath);
    foreach ($arrDirs as $strDir)
	{
        $strCumulativePath .= $strDir;
        if (!is_dir($strCumulativePath) && !mkdir($strCumulativePath, $intMode))
		{
            return FALSE;
        }
		
		$strCumulativePath .= '/';
    }
    return TRUE;
}

//------------------------------------------------------------------------//
// BuildLatePaymentNotice
//------------------------------------------------------------------------//
/**
 * BuildLatePaymentNotice()
 *
 * Generates the chosen Late Payment Notice for an Account
 *
 * Generates the chosen Late Payment Notice for an Account
 *
 * @param	integer	$intNoticeType	Type of notice to generate (LETTER_TYPE_OVERDUE | _SUSPENSION | _FINAL_DEMAND)
 * @param	array	$arrAccount		All Account, Contact and Invoice data required for the notice
 * @param	string	$strBasePath	optional, base path where the generated notices will be placed. Must end with a '/'
 *									
 * @return	bool					TRUE if the notice was successfully generated, else FALSE
 *
 * @function
 */
function BuildLatePaymentNotice($intNoticeType, $arrAccount, $strBasePath="./")
{
	//TODO! Modify this so that it builds actual pdfs, instead of just text files representing the pdfs
	
	// Static instances of the db access objects used to add records to the AccountNotice and FileExport tables
	// are used so that the same objects don't have to be built for each individual Late Payment Notice that gets
	// made in a run
	static $insNotice;
	static $insFileExport;
	
	// The key of this array is the CustomerGroup Id of the template
	static $arrLetterTemplates = Array();
	
	if (!isset($arrLetterTemplates[$intNoticeType]))
	{
		$arrLetterTemplates[$intNoticeType] = Array();
		// Cache the letter template details
		//TODO! you will also have to retrieve details from the LetterTemplateVar table, as soon as we work out
		// how the letter templates will work
		$strTables	= "CustomerGroup AS CG INNER JOIN LetterTemplate AS LT ON CG.Id = LT.CustomerGroup";
		$arrColumns	= Array("CustomerGroupId" => "CG.Id", "CustomerGroupInternalName" => "CG.InternalName", "CustomerGroupExternalName" => "CG.ExternalName", "Template" => "LT.Template", "TemplateId" => "LT.Id");
		$strWhere	= "LT.LetterType = <LetterType> AND LT.Id = (SELECT MAX(Id) FROM LetterTemplate WHERE LetterType = <LetterType> AND CustomerGroup = CG.Id)";
		$selLetterTemplates = new StatementSelect($strTables, $arrColumns, $strWhere);
		$selLetterTemplates->Execute(Array('LetterType' => $intNoticeType));
		
		// Load each CustomerGroup's Letter Template details into the $arrLetterTemplates array
		while (($arrLetterTemplate = $selLetterTemplates->Fetch()) !== FALSE)
		{
			$arrLetterTemplates[$intNoticeType][$arrLetterTemplate['CustomerGroupId']] = $arrLetterTemplate;
		}
	}
	
	// Check that LetterTemplate details were retrieved for the CustomerGroup, that this Account belongs to
	if (!isset($arrLetterTemplates[$intNoticeType][$arrAccount['CustomerGroup']]))
	{
		// LetterTemplate details have not been defined for this LetterType and CustomerGroup
		return FALSE;
	}
	
	// Directory structure = BasePath/CustomerGroup/NoticeType/YYYY/MM/DD/
	$strFullPath = 	$strBasePath . strtolower(str_replace(" ", "_", $arrLetterTemplates[$intNoticeType][$arrAccount['CustomerGroup']]['CustomerGroupInternalName'])) ."/". 
					str_replace(" ", "_", strtolower(GetConstantDescription($intNoticeType, "LetterType"))) ."/". date("Y/m/d");
	
	// Make the directory structure if it hasn't already been made
	if (!is_dir($strFullPath))
	{
		RecursiveMkdir($strFullPath);
	}
	
	// Create the filename
	$strFilename = $arrAccount['AccountId'] . ".txt";
	
	// Set up all values required of the notice, which have not been defined yet
	$strDateIssued								= date("d-m-Y");
	$strDueDateForAction						= date("d-F-Y", strtotime("+7 days"));
	$arrAccount['CustomerGroupInternalName']	= $arrLetterTemplates[$intNoticeType][$arrAccount['CustomerGroup']]['CustomerGroupInternalName'];
	$arrAccount['CustomerGroupExternalName']	= $arrLetterTemplates[$intNoticeType][$arrAccount['CustomerGroup']]['CustomerGroupExternalName'];
	$arrAccount['AccountStatus']				= GetConstantDescription($arrAccount['AccountStatus'], "Account");
	$arrAccount['NoticeTemplate']				= $arrLetterTemplates[$intNoticeType][$arrAccount['CustomerGroup']]['Template'];

	// Format the monetary values
	$arrAccount['OutstandingNotOverdue'] = number_format($arrAccount['OutstandingNotOverdue'], 2, ".", "");
	$arrAccount['Overdue'] = number_format($arrAccount['Overdue'], 2, ".", "");
	$arrAccount['TotalOutstanding'] = number_format($arrAccount['TotalOutstanding'], 2, ".", "");
	
	// Open the file in text mode
	$ptrNoticeFile = fopen($strFullPath ."/". $strFilename, 'wt');
	if ($ptrNoticeFile === FALSE)
	{
		// The file could not be opened
		return FALSE;
	}
	
	// Include NoticeType specific stuff here
	switch ($intNoticeType)
	{
		case LETTER_TYPE_OVERDUE:
			$strMessage =	"Our records indicate that your account for the amount of \${$arrAccount['Overdue']} remains unpaid.\n".
							"Please ensure payment is made by $strDueDateForAction to avoid any further recovery action and possible disruption to your services\n";
			break;
		case LETTER_TYPE_SUSPENSION:
			$strMessage =	"Further to our recent reminder letter, our records indicate that your account remains unpaid.\n".
							"\tTotal Amount Owing: \${$arrAccount['Overdue']}\n\n".
							"Please be advised that if we do not recieve payment by $strDueDateForAction your services will be suspended without further notice and we will commence appropriate collection action immediately.\n";
			break;
		case LETTER_TYPE_FINAL_DEMAND:
			$strMessage =	"We note that dispite numerous reminders to pay this outstanding amount, the account still remains in arrears in the amount of \${$arrAccount['Overdue']}\n".
							"Your service is due to be temporarily disconnected because of your failure to pay your accounts.\n".
							"Your current balance not outstanding is \${$arrAccount['OutstandingNotOverdue']}\n".
							"Total Amount Due: \${$arrAccount['TotalOutstanding']}\n\n".
							"If you would like to avoid the impending actions, we request that you contact this office within 7 days with a view to payment of the outstanding account.\n";
			break;
	}
	
	$strMessage .= "Date Issued: $strDateIssued\n";
	
	// The account's Suburb and State must be in all uppercase
	$arrAccount['Suburb'] = strtoupper($arrAccount['Suburb']);
	$arrAccount['State'] = strtoupper($arrAccount['State']);
	
	// Output the contents of $arrAccount
	foreach ($arrAccount as $strProperty=>$mixValue)
	{
		$strMessage .= "$strProperty: $mixValue\n";
	}
	
	fwrite($ptrNoticeFile, $strMessage);
	fclose($ptrNoticeFile);
	
	$strNow = date("Y-m-d H:i:s");
	// Log the Notice in the AccountLetterLog Table
	$arrLetterLog = Array(	'Account'		=> $arrAccount['AccountId'],
							'Invoice'		=> $arrAccount['InvoiceId'],
							'LetterType'	=> $intNoticeType,
							'CreatedOn'		=> $strNow);
	
	// Only define the StatementInsert object if it hasn't already been defined				
	if (!isset($insNotice))
	{
		$insNotice = new StatementInsert("AccountLetterLog", $arrLetterLog);
	}
	$insNotice->Execute($arrLetterLog);
	
	// Record the File in the FileExport table
	//TODO! Fix up the Carrier, Status and FileType values so that they are meaningful
	$arrFileLog = Array(	'FileName'		=>	$strFilename,
							'Location'		=>	ltrim($strFullPath, ".") . "/",
							'Carrier'		=>	0,
							'ExportedOn'	=>	$strNow,
							'Status'		=>	0,
							'FileType'		=>	0,
							'SHA1'			=>	sha1($strFilename));

	// Only define the StatementInsert object if it hasn't already been defined				
	if (!isset($insFileExport))
	{
		$insFileExport = new StatementInsert("FileExport", $arrFileLog);
	}
	$insFileExport->Execute($arrFileLog);
	
	// Create a system note for the account
	$strNote = 	"A ". GetConstantDescription($intNoticeType, "LetterType") . " has been generated for this account.\n".
				"Outstanding Overdue: \${$arrAccount['Overdue']}\n".
				"Outstanding Not Overdue: \${$arrAccount['OutstandingNotOverdue']}";
				
	$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE_TYPE, NULL, $arrAccount['AccountGroup'], $arrAccount['AccountId']);
	
	return TRUE;
}


//------------------------------------------------------------------------//
// SaveConstantGroup
//------------------------------------------------------------------------//
/**
 * SaveConstantGroup()
 *
 * Inserts a ConstantGroup into the ConfigConstant and ConfigConstantGroup tables of the database
 *
 * Inserts a ConstantGroup into the ConfigConstant and ConfigConstantGroup tables of the database
 *
 * @param	array	$arrConstGroup	constant group array.  This must be in the format of ConstantGroups
 * 									defined within $GLOBALS['*arrConstant'] array
 * @param	string	$strName		name of the constant group
 * @param	integer	$intDataType	the DatatType of the constants within the constant group.  This must be a constant
 * 									from the DATA_TYPE_ constant group
 * @param	string	$strDescription optional, description of the constant group.  Defaults to NULL
 *									
 * @return	mix						int  : Id of the ConstantGroup on success
 * 									bool : FALSE on failure
 *
 * @function
 */
function SaveConstantGroup($arrConstGroup, $strName, $intDataType, $strDescription=NULL)
{
	static $insConstGroup;
	
	// If the StatementInsert objects have not yet been created, create them now
	if (!isset($insConstGroup))
	{
		$insConstGroup	= new StatementInsert("ConfigConstantGroup");
	}
	
	// Set up the data for the ConfigConstantGroup record
	$arrConstGroupData = Array("Name" => $strName, "Description" => $strDescription, "Type" => $intDataType);
	
	TransactionStart();
	
	// Insert the ConfigConstantGroup record
	$mixConstGroupId = $insConstGroup->Execute($arrConstGroupData);
	if ($mixConstGroupId === FALSE)
	{
		TransactionRollback();
		return FALSE;
	}
	
	// Insert each constant of the ConstantGroup, into the ConfigConstant table
	foreach ($arrConstGroup as $mixValue=>$arrValue)
	{
		$mixResult = SaveConstant($arrValue['Constant'], $mixValue, $intDataType, $arrValue['Description'], $mixConstGroupId);
		if ($mixResult === FALSE)
		{
			TransactionRollback();
			return FALSE;
		}
	}
	
	TransactionCommit();
	return $mixConstGroupId;
}

//------------------------------------------------------------------------//
// SaveConstant
//------------------------------------------------------------------------//
/**
 * SaveConstant()
 *
 * Inserts a Constant into the ConfigConstant table of the database
 *
 * Inserts a Constant into the ConfigConstant table of the database
 *
 * @param	string	$strName			name of the constant (ie CONST_NAME)
 * @param	mix		$mixValue			value of the constant (either a string, int, float or bool)
 * @param	integer	$intDataType		optional, the DatatType of the constants within the constant group.  
 * 										This must be a constant from the DATA_TYPE_ constant group.  If
 * 										$intConstantGroupId is declared, then $intDataType is considered NULL.
 * 										Defaults to NULL
 * @param	string	$strDescription 	optional, description of the constant.  Defaults to NULL
 * @param	integer	$intConstantGroupId	optional, Id of the ConstantGroup that this constant belongs to.
 * 										Defaults to NULL
 *									
 * @return	mix							int  : Id of the Constant on success
 * 										bool : FALSE on failure
 *
 * @function
 */
function SaveConstant($strName, $mixValue, $intDataType=NULL, $strDescription=NULL, $intConstantGroupId=NULL)
{
	static $insConst;
	
	if (!isset($insConst))
	{
		$insConst = new StatementInsert("ConfigConstant");
	}
		
	if ($intConstantGroupId !== NULL)
	{
		// A constant group has been specified
		$intDataType = NULL; 
	}
	if ($intConstantGroupId === NULL && $intDataType === NULL)
	{
		// We cannot work out the data type for the constant.  Assume it is a string
		$intDataType = DATA_TYPE_STRING;
	}
	
	if ($intDataType == DATA_TYPE_BOOLEAN)
	{
		$mixValue = ($mixValue)? "1" : "0";
	}
	
	$arrConst = Array(	"ConstantGroup" => $intConstantGroupId, "Name" => $strName,
						"Description" => $strDescription, "Value" => "$mixValue",
						"Type" => $intDataType);
	
	$mixResult = $insConst->Execute($arrConst);
	return $mixResult;
}

// This also declares the constants retrieved from the database, and places any ConstantGroups
// into the $GLOBALS['*arrConstant'] array
// returns false, if it failed to create any of the constants
function BuildConstantsFromDB()
{
	$strTables	= "ConfigConstant AS CC LEFT JOIN ConfigConstantGroup AS CCG ON CC.ConstantGroup = CCG.Id";
	$arrColumns	= Array("Id"=>"CC.Id", 
						"Name" => "CC.Name", 
						"Value" => "CC.Value", 
						"ConstDesc" => "CC.Description",
						"Type" => "CASE WHEN CC.ConstantGroup IS NULL THEN CC.Type ELSE CCG.Type END",
						"ConstGroupName" => "CCG.Name");
	$strOrderBy	= "CC.ConstantGroup, CC.Id";
	$strWhere	= "TRUE"; 
	$selConstants = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
	
	$selConstants->Execute();
	$arrConstants = $selConstants->FetchAll();
	
	foreach ($arrConstants as $arrConstant)
	{
		// Check that the constant has not already been defined
		if (defined($arrConstant['Name']))
		{
			return FALSE;
		}

		// Type cast the constant's value to its data type
		switch ($arrConstant['Type'])
		{
			case DATA_TYPE_STRING:
				$mixValue = "{$arrConstant['Value']}"; 
				break;
			case DATA_TYPE_INTEGER:
				$mixValue = (integer)$arrConstant['Value'];
				break;
			case DATA_TYPE_FLOAT:
				$mixValue = (float)$arrConstant['Value'];
				break;
			case DATA_TYPE_BOOLEAN:
				$mixValue = (bool)$arrConstant['Value'];
				break;
			default:
				// Unknown data type
				return FALSE;
				break;
		}

		// Declare the constant
		define($arrConstant['Name'], $mixValue);
		
		// If the constant is part of a ConstantGroup, add it to the $GLOBALS['*arrConstant'] array
		if ($arrConstant['ConstGroupName'] !== NULL)
		{
			$GLOBALS['*arrConstant'][$arrConstant['ConstGroupName']][$mixValue]['Constant']		= $arrConstant['Name'];
			$GLOBALS['*arrConstant'][$arrConstant['ConstGroupName']][$mixValue]['Description']	= $arrConstant['ConstDesc'];
		}

		//Debug stuff
		/*
		if ($arrConstant['ConstGroupName'] !== NULL)
		{
			echo "\$GLOBALS['*arrConstant']['{$arrConstant['ConstGroupName']}'][{$mixValue}]['Constant'] &nbsp;&nbsp;&nbsp;&nbsp;= {$arrConstant['Name']}<br />\n";
			echo "\$GLOBALS['*arrConstant']['{$arrConstant['ConstGroupName']}'][{$mixValue}]['Description'] = {$arrConstant['Name']}<br />\n";
		}
		else
		{
			echo "define('{$arrConstant['Name']}', $mixValue)<br />";
		}
		*/
	}
}




//------------------------------------------------------------------------//
// GetCustomerName
//------------------------------------------------------------------------//
/**
 * GetCustomerName()
 *
 * Returns the customer (eg. TelcoBlue) name
 *
 * Returns the customer (eg. TelcoBlue) name
 *
 * @return	string		Customer Name (eg. "telcoblue")
 *
 * @function
 */
function GetCustomerName()
{
	return $GLOBALS['**arrCustomerConfig']['Customer'];
}





?>
