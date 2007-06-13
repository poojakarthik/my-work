<?php
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
function IsValidFNN ($strFNN)
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
	$arrYears = glob("/home/vixen_invoices/*", GLOB_ONLYDIR);
	
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
	$arrInvoices = glob("/home/vixen_invoices/".$intYear."/".$intMonth."/".$intAccount."_*.pdf");
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
			$arrPrefixes	= Array ();
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
	
	return true;
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
 * @param	str	$strOutput	The string to write to stdout
 *
 * @return	void
 * 
 * @function
 */
function CliEcho($strOutput)
{
	if (!$GLOBALS['**stdout'])
	{
		$GLOBALS['**stdout'] = fopen("php://stdout","w"); 
	}
	$stdout = $GLOBALS['**stdout'];
	fwrite($stdout, $strOutput."\n");
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
	$json = new Services_JSON();
	// get the JSON object and decode it into an object
	$input = file_get_contents('php://input', 1000000);
	$input = $json->decode($input);
	
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
	$json = new Services_JSON();
	echo $json->encode($arrReply);
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
	if (is_null($strFrameworkDir))
	{
		if (defined(VIXEN_BASE_DIR))
		{
			rtrim(VIXEN_BASE_DIR, '/').'/framework/';
		}
		else
		{
			$strFrameworkDir = "../framework/";
		}
	}
	else
	{
		$strFrameworkDir = rtrim($strFrameworkDir, '/').'/';
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
 * @param	str	$strApplication		The directory of the application (default: NULL)
 *
 * @incomplete
 * @function
 */
function LoadApplication($strApplication=NULL)
{
	$strApplicationDir = '';
	
	// no application specified
	if (!$strApplication)
	{
		// load from current dir
		$strApplicationDir = '';
		require_once("require.php");
		return TRUE;
	}
	
	// set the base dir
	if (defined(VIXEN_BASE_DIR))
	{
		$strApplicationDir = rtrim(VIXEN_BASE_DIR, '/').'/';
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
		$strApplicationDir = $strVixenRoot;
	}
	
	// set application dir
	//$strApplicationDir .= "application_".strtolower(trim($strApplication, '/')).'/';
	$strApplicationDir .= $strApplication."/";
	
	//Debug($strApplicationDir);
	
	// require application
	require_once($strApplicationDir."require.php");
	
	return TRUE;
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
	$selAccount = new StatementSelect("Account", "Id", "Id = <Id> AND Archived != 1");
	
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


?>
