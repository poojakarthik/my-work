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
 * @param	string	$strOutput				The string to write to stdout
 * @param	boolean	$bolNewLine	optional	Whether to automatically add a new line character
 *
 * @return	void
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
		if (defined(VIXEN_BASE_DIR))
		{
			$GLOBALS['**strVixenBasePath'] = VIXEN_BASE_DIR;
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
 * 
 * @return		float								Total excluding Tax
 *
 * @method
 */ 
function UnbilledServiceCDRTotal($intService)
{
	// Get CDR Total
	$selCDRTotal = new StatementSelect("CDR", "SUM(CASE WHEN Credit = 1 THEN 0 - Charge ELSE Charge END) AS TotalCharged", "Service = <Service> AND (Status = ".CDR_RATED ." OR Status = ". CDR_TEMP_INVOICE .")");
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
 *
 * @return		float								Total excluding Tax
 *
 * @method
 */ 
function UnbilledAccountCDRTotal($intAccount)
{
	// Get CDR Total
	$selCDRTotal = new StatementSelect("CDR", "SUM(CASE WHEN Credit = 1 THEN 0 - Charge ELSE Charge END) AS TotalCharged", "Account = <Account> AND (Status = ".CDR_RATED ." OR Status = ". CDR_TEMP_INVOICE .")");
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
	$selRatePlan = new StatementSelect("ServiceRatePlan", "RatePlan", "Service = <Service> AND NOW() BETWEEN StartDatetime AND EndDatetime", "StartDatetime DESC", 1);
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
	$strGlob = "/home/vixen_invoices/$intYear/$intMonth/{$intAccountId}_*.pdf";
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
	$arrInvoices = glob("/home/vixen_invoices/".$intYear."/".$intMonth."/".$intAccount."_*.pdf");
	
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
	$selFindOwner 			= new StatementSelect("Service", "AccountGroup, Account, Id AS Service", "FNN = <fnn> AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
	$selFindOwnerIndial100	= new StatementSelect("Service", "AccountGroup, Account, Id AS Service", "(FNN LIKE <fnn>) AND (Indial100 = TRUE)AND (CAST(<date> AS DATE) BETWEEN CreatedOn AND ClosedOn OR ISNULL(ClosedOn))", "CreatedOn DESC, Account DESC", "1");
	
 	$intResult = $selFindOwner->Execute(Array("fnn" => $strFNN, "date" => $strDate));
 	if ($arrResult = $selFindOwner->Fetch())
 	{
 		return $arrResult;
 	}
 	else
 	{
 		$arrParams['fnn']	= substr($strFNN, 0, -2) . "__";
 		$arrParams['date']	= $strDate;
 		$intResult = $selFindOwnerIndial100->Execute($arrParams);
 		if(($arrResult = $selFindOwnerIndial100->Fetch()))
 		{
 			return $arrResult;
 		}
 	}
 	
 	return FALSE;
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
	$selInvoice->Execute($arrData);
	if ($arrInvoice	= $selInvoice->Fetch())
	{
		// Write off Invoice
		$ubiInvoice	= new StatementUpdateById("Invoice");
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
?>