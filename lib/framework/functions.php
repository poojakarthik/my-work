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
	return GetConstantDescription($intCarrier, 'Carrier');
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
	else if (isset($GLOBALS['*arrConstant'][strtolower($strType)][$intCode]['Constant']))
	{
		return $GLOBALS['*arrConstant'][strtolower($strType)][$intCode]['Constant'];
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
	else if (isset($GLOBALS['*arrConstant'][strtolower($strType)][$intCode]['Description']))
	{
		return $GLOBALS['*arrConstant'][strtolower($strType)][$intCode]['Description'];
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

function PageUsesModule($strUsedModules, $strModule)
{
	$strUsedModules = str_replace(' ', '0', rtrim($strUsedModules, ' 0'));
	if ($strUsedModules)
	{
		$strModule = str_replace(' ', '0', $strModule);
		$or = rtrim($strUsedModules | $strModule, ' 0');
		if ($strUsedModules == $or)
		{
			return TRUE;
		}
	}
	return FALSE;
}

// -------------------------------------------------------------------------- //
// PDF FUNCTIONS
// -------------------------------------------------------------------------- //

//------------------------------------------------------------------------//
// ListPDF INCOMPLETE
// - Not used?
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

	// Get the XML invoices...
	// GLOB for xml/year directories
	$arrYears = glob(PATH_INVOICE_PDFS .DIRECTORY_SEPARATOR."/xml/*", GLOB_ONLYDIR);

	foreach($arrYears as $strYear)
	{
		// GLOB for month directories
		$arrMonths = glob("$strYear/*", GLOB_ONLYDIR);

		foreach($arrMonths as $strMonth)
		{
			// GLOB for account filename
			$arrInvoices = glob("$strMonth/".$intAccount."_*.xml");
			if (count($arrInvoices))
			{
				$arrReturn[basename ($strYear)][basename ($strMonth)]	= basename($arrInvoices[0]);
			}
		}
	}

	// GLOB for pdf/year directories - i.e. pdfs generated prior to the introduction of pdf generation by flex
	$arrYears = glob(PATH_INVOICE_PDFS .DIRECTORY_SEPARATOR."/pdf/*", GLOB_ONLYDIR);

	foreach($arrYears as $strYear)
	{
		// GLOB for month directories
		$arrMonths = glob("$strYear/*", GLOB_ONLYDIR);

		foreach($arrMonths as $strMonth)
		{
			if (!(array_key_exists(basename ($strYear), $arrReturn) && array_key_exists(basename ($strMonth), $arrReturn[basename ($strYear)])))
			{
				// GLOB for account filename
				$arrInvoices = glob("$strMonth/".$intAccount."_*.pdf");
				if (count($arrInvoices))
				{
					$arrReturn[basename ($strYear)][basename ($strMonth)]	= basename($arrInvoices[0]);
				}
			}
		}
	}

	// GLOB for year directories - This is old school! Should be removed once updates are complete!!
	$arrYears = glob(PATH_INVOICE_PDFS ."*", GLOB_ONLYDIR);
	
	foreach($arrYears as $strYear)
	{
		// GLOB for month directories
		$arrMonths = glob("$strYear/*", GLOB_ONLYDIR);

		foreach($arrMonths as $strMonth)
		{
			if (!(array_key_exists(basename ($strYear), $arrReturn) && array_key_exists(basename ($strMonth), $arrReturn[basename ($strYear)])))
			{
				// GLOB for account filename
				$arrInvoices = glob("$strMonth/".$intAccount."_*.pdf");
				if (count($arrInvoices))
				{
					$arrReturn[basename ($strYear)][basename ($strMonth)]	= basename($arrInvoices[0]);
				}
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
	$arrModel = Flex_Data_Model::get($strTable);
	if ($arrModel === NULL || !is_array($arrModel['Column']))
	{
		return FALSE;
	}
	
	$strReturn = $strSeparator; // Id
	foreach($arrModel['Column'] AS $strKey => $arrValue)
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
	$arrModel = Flex_Data_Model::get($strTable);
	if ($arrModel === NULL || !is_array($arrModel['Column']))
	{
		return FALSE;
	}
	
	$strReturn = "Id".$strSeparator;
	foreach($arrModel['Column'] AS $strKey => $arrValue)
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
 * Check the format of a phone number
 * 
 * Check the format of a phone number
 *
 * @param	str	$strPhoneNumber	The phone number to check
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
// UnmaskShortDate
//------------------------------------------------------------------------//
/**
 * MaskShortDate()
 * 
 * Convert short date in user friendly format (dd/mm/yyyy) to standard format (yyyy-mm-dd)
 * 
 * Convert short date in user friendly format (dd/mm/yyyy) to standard format (yyyy-mm-dd)
 *
 * @param	str	$strShortDate	The short date to unmask
 *
 * @return	str The unmasked short date or the original string if invalid
 * 
 * @function
 */
function UnmaskShortDate ($strShortDate)
{
	$arrDateParts = array();
	if (preg_match ("/^(0?[1-9]|[12][0-9]|3[01])\/(0?[1-9]|1[0-2])\/(\d\d\d\d)$/", $strShortDate, $arrDateParts))
	{
		$day = str_pad($arrDateParts[1], 2, "0", STR_PAD_LEFT);
		$month = str_pad($arrDateParts[2], 2, "0", STR_PAD_LEFT);
		$strShortDate = $arrDateParts[3] . "-" . $month . "-" . $day;
	}
	return $strShortDate;
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
 * @param	string	$strOutput		[optional]	The string to write to stdout; default: ''
 * @param	boolean	$bolNewLine		[optional]	Whether to automatically add a new line character; default: TRUE
 *
 * @return	string								The string written to stdout
 * 
 * @function
 */
function CliEcho($strOutput='', $bolNewLine=TRUE)
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
	return JSON_Services::instance();
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
	// get the JSON object and decode it into an object
	$input = file_get_contents('php://input');

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
	// Determine base dir
	if (!array_key_exists('**strVixenBasePath', $GLOBALS) || !$GLOBALS['**strVixenBasePath'])
	{
		if (defined('FLEX_BASE_PATH'))
		{
			$GLOBALS['**strVixenBasePath'] = FLEX_BASE_PATH;
		}
		else
		{
			// DIE
			echo "FLEX_BASE_PATH is not defined!\n";
			die;
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
function LoadFramework($strFrameworkDir=NULL, $bolBasicsOnly=FALSE, $loadDbConstants=TRUE)
{
	// Get viXen base dir
	if (!$strFrameworkDir)
	{
		$strFrameworkDir = GetVixenBase();
		$strFrameworkDir .= 'lib/framework/';
	}
	
	// Load the viXen/Flex Global Config File (defining database and path constants)
	require_once($strFrameworkDir."config.php");

	// Load framework
	if (!$bolBasicsOnly)
	{
		require_once($strFrameworkDir."framework.php");
	}

	require_once($strFrameworkDir."functions.php");
	if (file_exists($strFrameworkDir."database_constants.php"))
	{
		require_once($strFrameworkDir."database_constants.php");
	}
	require_once($strFrameworkDir."definitions.php");

	if (!$bolBasicsOnly)
	{
		require_once($strFrameworkDir."db_access.php");
	}
	
	
	// Retrieve all constants stored in the database
	// Note that this will not override constants that have already been defined
	if ($loadDbConstants)
	{
		BuildConstantsFromDB();
	}
	
	// Load viXen/Flex customer config file
	$strPath = dirname(dirname(dirname(__FILE__))) . "/customer.cfg.php";
	if (!@include_once($strPath))
	{
		echo "\nFATAL ERROR: Unable to find Flex customer configuration file at location '$strPath'\n\n";
		die;
	}

	if (!$bolBasicsOnly)
	{
		require_once($strFrameworkDir."report.php");
		require_once($strFrameworkDir."error.php");
		require_once($strFrameworkDir."exception_vixen.php");
	
		// PEAR Packages
		require_once("Console/Getopt.php");
		require_once("Spreadsheet/Excel/Writer.php");
		require_once("Mail.php");
		require_once("Mail/mime.php");
	}

	// Create framework instance
	$GLOBALS['fwkFramework'] = new Framework($bolBasicsOnly);
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
		$arrConfig = NULL;
		require_once("require.php");
		// There is no application.php in this dir! This will load the next application.php in the include path.
		// Marked with "" . "..." to prevent IDE giving warning
		require_once("" . "application.php");
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
		//return FALSE;
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
 * Starts a Transaction
 *
 * @param	string		$strConnectionType	optional, defaults to FLEX_DATABASE_CONNECTION_DEFAULT.  The specific database connection
 * @return	boolean					TRUE	: Committed
 * 									FALSE	: Failed
 *
 * @method
 */ 
function TransactionStart($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
{
	if (!DataAccess::connected($strConnectionType))
	{
		// Can't start a new transaction if not connected
		return FALSE;
	}
	
	// Start Transaction
	return DataAccess::getDataAccess($strConnectionType)->TransactionStart();
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
 * @param	string		$strConnectionType	optional, defaults to FLEX_DATABASE_CONNECTION_DEFAULT.  The specific database connection
 * @return	boolean					TRUE	: Rolled back
 * 									FALSE	: Failed
 *
 * @method
 */ 
function TransactionRollback($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
{
	if (!DataAccess::connected($strConnectionType))
	{
		// Can't start a new transaction if not connected
		return FALSE;
	}
	
	// Rollback Transaction
	return DataAccess::getDataAccess($strConnectionType)->TransactionRollback();
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
 * @param	string		$strConnectionType	optional, defaults to FLEX_DATABASE_CONNECTION_DEFAULT.  The specific database connection
 * @return	boolean					TRUE	: Started
 * 									FALSE	: Failed
 *
 * @method
 */ 
function TransactionCommit($strConnectionType=FLEX_DATABASE_CONNECTION_DEFAULT)
{
	if (!DataAccess::connected($strConnectionType))
	{
		// Can't start a new transaction if not connected
		return FALSE;
	}
	
	// Commit Transaction
	return DataAccess::getDataAccess($strConnectionType)->TransactionCommit();
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
function SetDBConfig($strURL=NULL, $strDatabase=NULL, $strUser=NULL, $strPassword=NULL, $strDatabaseConnection=FLEX_DATABASE_CONNECTION_DEFAULT)
{
	if (DataAccess::connected())
	{
		// Can't override if already connected
		return FALSE;
	}
	
	// Override
	$GLOBALS['**arrDatabase'][$strDatabaseConnection]['URL']		= ($strURL)			? $strURL		: $GLOBALS['**arrDatabase'][$strDatabaseConnection]['URL'];
	$GLOBALS['**arrDatabase'][$strDatabaseConnection]['Database']	= ($strDatabase)	? $strDatabase	: $GLOBALS['**arrDatabase'][$strDatabaseConnection]['Database'];
	$GLOBALS['**arrDatabase'][$strDatabaseConnection]['User']		= ($strUser)		? $strUser		: $GLOBALS['**arrDatabase'][$strDatabaseConnection]['User'];
	$GLOBALS['**arrDatabase'][$strDatabaseConnection]['Password']	= ($strPassword)	? $strPassword	: $GLOBALS['**arrDatabase'][$strDatabaseConnection]['Password'];
	
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
// ListPDFSamples
//------------------------------------------------------------------------//
/**
 * ListPDFSamples()
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
function ListPDFSamples($intAccountId)
{
	$strGlob = PATH_INVOICE_PDFS ."xml/*-*/{$intAccountId}.xml";
	$arrPDFs = glob($strGlob);
	$arrInvoiceRuns = array();
	if ($arrPDFs && count($arrPDFs))
	{
		$strPaths = str_replace("\\", "/", implode("!", $arrPDFs)) . "!";
		preg_match_all("/xml\/([^\-]+\-([^\/]+))\/[^_]+\.xml/U", $strPaths, $arrInvoiceRuns);
		if (count($arrInvoiceRuns))
		{
			$arrInvoiceRuns = array_map('ucwords', array_map('strtolower', array_combine($arrInvoiceRuns[1], $arrInvoiceRuns[2])));
		}
	}
	krsort($arrInvoiceRuns);
	return $arrInvoiceRuns;
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
function InvoicePDFExists($intAccountId, $intYear, $intMonth, $intInvoiceId, $mxdInvoiceRun)
{
	$strGlob = PATH_INVOICE_PDFS ."xml/$mxdInvoiceRun/{$intAccountId}.xml";
	$arrPDFs = glob($strGlob);
	if ($arrPDFs && count($arrPDFs))
	{
		return $arrPDFs[0];
	}

	if ($intInvoiceId)
	{
		$strGlob = PATH_INVOICE_PDFS ."xml/$mxdInvoiceRun/{$intAccountId}_{$intInvoiceId}.xml";
		$arrPDFs = glob($strGlob);
		if ($arrPDFs && count($arrPDFs))
		{
			return $arrPDFs[0];
		}
	}

	$strGlob = PATH_INVOICE_PDFS ."xml/$mxdInvoiceRun/{$intAccountId}.xml.bz2";
	$arrPDFs = glob($strGlob);
	if ($arrPDFs && count($arrPDFs))
	{
		return $arrPDFs[0];
	}

	if ($intInvoiceId)
	{
		$strGlob = PATH_INVOICE_PDFS ."xml/$mxdInvoiceRun/{$intAccountId}_{$intInvoiceId}.xml.bz2";
		$arrPDFs = glob($strGlob);
		if ($arrPDFs && count($arrPDFs))
		{
			return $arrPDFs[0];
		}
	}

	if ($intInvoiceId)
	{
		$intMonth = intVal($intMonth);
		$strGlob = PATH_INVOICE_PDFS ."pdf/$intYear/$intMonth/{$intAccountId}_{$intInvoiceId}.pdf";
		$arrPDFs = glob($strGlob);
		if ($arrPDFs && count($arrPDFs))
		{
			return $arrPDFs[0];
		}
	}

	if (is_int($mxdInvoiceRun))
	{
		// We must have just searched using an invoice run id.
		// Historically we have stored against the invoice run name (InvoiceRun.InvoiceRun),
		// so we should have a go searching with this before we give up.
		$selInvoiceRun = new StatementSelect('InvoiceRun', 'InvoiceRun', 'Id=<Id>');
		if ($mxdOutcome	= $selInvoiceRun->Execute(Array('Id' => $mxdInvoiceRun)))
		{
			$arrInvoiceRun = $selInvoiceRun->Fetch();
			$strInvoiceRun = strval($arrInvoiceRun['InvoiceRun']);
			return InvoicePDFExists($intAccountId, $intYear, $intMonth, $intInvoiceId, $strInvoiceRun);
		}		
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
 * @param	int		$intInvoiceId		Id of invoice
 * @param	int		$intInvoiceRunId	Invoice run of invoice
 *
 * @return	mixed						string: filename of the PDF invoice
 * 										FALSE: there was an error
 * 
 * @function
 */
function GetPdfFilename($intAccount, $intYear, $intMonth, $intInvoiceId, $intInvoiceRunId)
{
	$mxdInvoicePath = InvoicePDFExists($intAccount, $intYear, $intMonth, $intInvoiceId, intval($intInvoiceRunId));
	if (!$mxdInvoicePath)
	{
		return FALSE;
	}
	else
	{
		$fileName = preg_replace("/(.xml|.xml.bz2)$/", ".pdf", basename($mxdInvoicePath));
		return $fileName;
	}
}

//------------------------------------------------------------------------//
// GetPDFContent
//------------------------------------------------------------------------//
/**
 * GetPDFContent()
 * 
 * Return the contents of a PDF invoice in a string
 * 
 * Return the contents of a PDF invoice in a string for the specified account, year, and month
 * 
 * @param	obj	$objInvoice		Invoice to get PDF document for
 * @param	int	$intTargetMedia	Target media (if generated on the fly) if not default for file
 *
 * @return	mixed						string: contents of the PDF invoice
 * 										FALSE: there was an error
 * 
 * @function
 */
function GetPDFContent($intAccount, $intYear, $intMonth, $intInvoiceId, $intInvoiceRunId, $intTargetMedia=0)
{
	$mxdInvoicePath = InvoicePDFExists($intAccount, $intYear, $intMonth, $intInvoiceId, intval($intInvoiceRunId));

	if (!$mxdInvoicePath)
	{
		return FALSE;
	}
	else
	{
		$ext = substr($mxdInvoicePath, strrpos($mxdInvoicePath, '.'));

		switch ($ext)
		{
			case '.bz2':
				// Load the xml from the bz2 file
				$xml = '';
				$bz = bzopen($mxdInvoicePath, 'r');
				$line = TRUE;
				while (!feof($bz) && $line)
				{
					$line = bzread($bz, 8192);
					$xml .= $line;
				}
				bzclose($bz);

			case '.xml':
				// Load the xml from the xml file
				if ($ext == '.xml')
				{
					$xml = file_get_contents($mxdInvoicePath);
				}

				// Get the document properties from the file
				$parts = array();
				preg_match_all("/(?:\<(DocumentType|CustomerGroup|CreationDate|DeliveryMethod)\>([^\<]*)\<)/", $xml, $parts);

				// Check that we have a full set
				if (count($parts) != 3 || count($parts[1]) != 4 || count($parts[2]) != 4)
				{
					throw new Exception("Unable to identify document properties.");
				}

				// Create a [name=>value,...] arrray...
				$docProps = array();
				for($i = 0; $i < 4; $i++)
				{
					$docProps[$parts[1][$i]] = $parts[2][$i]; 
				}

				// If no target media has been specified, get the default media type for the file
				if (!$intTargetMedia)
				{
					$targetMedia = $docProps["DeliveryMethod"];
					switch($targetMedia)
					{
						case 'DELIVERY_METHOD_EMAIL':
						case 'DELIVERY_METHOD_EMAIL_SENT':
						case 'DELIVERY_METHOD_DO_NOT_SEND':
							$intTargetMedia = DOCUMENT_TEMPLATE_MEDIA_TYPE_EMAIL;
							break;
						case 'DELIVERY_METHOD_POST':
						case 'DELIVERY_METHOD_PRINT':
							$intTargetMedia = DOCUMENT_TEMPLATE_MEDIA_TYPE_PRINT;
							break;
						default:
							return FALSE;
					}
				}

				// Take the effective date to be the document Creation Date
				$effectiveDate = $docProps["CreationDate"];

				// Take the customer group from the file - this should be the same as the one for the invoice
				$custGroupId = constant($docProps["CustomerGroup"]);

				VixenRequire('lib/pdf/Flex_Pdf.php');

				try
				{
				// Generate the pdf document on the fly
				$pdfTemplate = new Flex_Pdf_Template(
								$custGroupId, 
								$effectiveDate, 
								DOCUMENT_TEMPLATE_TYPE_INVOICE, 
								$xml, 
								$intTargetMedia, 
								TRUE);

				$pdfDocument = $pdfTemplate->createDocument();

				$pdf = $pdfDocument->render();
				}
				catch (Exception $e)
				{
					throw $e;
				}

				break;

			case '.pdf':
				$pdf = file_get_contents($mxdInvoicePath);
				break;

			default:
				$pdf = FALSE;
		}
		return $pdf;
	}
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
 * @param	boolean	$bolDateOnly		Date comparison only (instead of Datetime)
 *
 * @return	bool					
 *
 * @method
 */
function FindFNNOwner($strFNN, $strDatetime, $bolDateOnly=FALSE)
{
	// Passthrough to Framework::FindFNNOwner()
	return $GLOBALS['fwkFramework']->FindFNNOwner($strFNN, $strDatetime, $bolDateOnly);
}

//------------------------------------------------------------------------//
// IsFNNInUse
//------------------------------------------------------------------------//
/**
 * IsFNNInUse()
 *
 * Checks if an FNN is/has-been in use, or is scheduled to be used in the future, since the given date
 *
 * Checks if an FNN is/has-been in use, or is scheduled to be used in the future, since the given date
 *
 * @param	string	$strFNN					The FNN to check
 * @param	bool	$bolIsIndial			TRUE If the FNN to check is an Indial100
 * @param	string	$strDate				The date to check from
 *
 * @return	mixed							TRUE if the FNN is/has been in use since $strDate, or is scheduled to be used
 * 											beyond this date
 * 											FALSE if the FNN isn't in use and is not scheduled to be used
 * 											String if there is an error				
 */
function IsFNNInUse($strFNN, $bolIsIndial, $strDate)
{
	// Passthrough to Framework::IsFNNInUse()
	return $GLOBALS['fwkFramework']->IsFNNInUse($strFNN, $bolIsIndial, $strDate);
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
function SendEmail($strAddresses, $strSubject, $strContent, $strFrom='auto@yellowbilling.com.au', $bolHTML = FALSE)
{
	$arrHeaders = Array	(
							'From'		=> $strFrom,
							'Reply-To'	=> $strFrom,
							'Subject'	=> $strSubject
						);
	$mimMime = new Mail_mime("\n");
	
	if ($bolHTML)
	{
		$mimMime->setTXTBody($strContent);
	}
	else
	{
		$mimMime->setHTMLBody($strContent);
	}
	
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
	
	// Find Card Type
	switch ((int)substr(trim($mixNumber), 0, 2))
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
			$arrAccount					= $selAccount->Fetch();
			$arrCharge['Account']		= $arrAccount['Account'];
		}
		else
		{
			// Account Payment
			$arrCharge['Account']		= $arrPayment['Account'];
		}
		
		$arrCharge['AccountGroup']		= $arrPayment['AccountGroup'];
		$arrCharge['CreatedBy']			= $arrPayment['EnteredBy'];
		$arrCharge['CreatedOn']			= date("Y-m-d");
		$arrCharge['ChargeType']		= "CCS";
		$arrCharge['Description']		= "$strType Surcharge for Payment on {$strDate} (\${$fltPaymentAmount}) @ $strPC%";
		$arrCharge['ChargedOn']			= $arrPayment['PaidOn'];
		$arrCharge['Nature']			= 'DR';
		$arrCharge['Amount']			= $fltAmount;
		$arrCharge['Notes']				= '';
		$arrCharge['global_tax_exempt']	= '';
		$arrCharge['Status']			= CHARGE_APPROVED;
		$arrCharge['LinkType']			= CHARGE_LINK_PAYMENT;
		$arrCharge['LinkId']			= $intPayment;
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
	$selInvoiceTemp	= new StatementSelect("Invoice", "Id", "Status = ".INVOICE_TEMP, "", "1");
	$intRows		= $selInvoiceTemp->Execute();
	
	// If there are records in the InvoiceTemp Table, then the invoicing process is occurring
	return (bool)$intRows;
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
	// StatementSelect doesn't work unless you specify a table name
	$selDatetime = new StatementSelect("Account", Array("CurrentTime" => "NOW()"));
	$selDatetime->Execute();
	$arrDatetime = $selDatetime->Fetch();

	return $arrDatetime['CurrentTime'];
}

//------------------------------------------------------------------------//
// GetCurrentISODateTime
//------------------------------------------------------------------------//
/**
 * GetCurrentISODateTime()
 *
 * Retrieves the current date and time in the ISO Datetime format
 *
 * Retrieves the current date and time in the ISO Datetime format
 * By default this value is cached, for subsequent calls
 * 
 * @param	bool	$bolForceRefresh	optional, defaults to FALSE.  If set to TRUE
 * 										then the "current" Time is retrieved from the
 * 										database's server.  If set to FALSE then the
 * 										cached "current" time is retrieved.
 * 
 * @param	bool	$bolUpdateCache		optional, defaults to FALSE.  If set to TRUE
 * 										then the cached value is updated.
 * 										If $bolForceRefresh == FALSE then $bolUpdateCache
 * 										is ignored
 *
 * @return	string			"current" date and time as an ISO Datetime string (YYYY-MM-DD HH:MM:SS)
 * @function
 */
function GetCurrentISODateTime($bolForceRefresh=FALSE, $bolUpdateCache=FALSE)
{
	if ($bolForceRefresh)
	{
		// Retrieve a fresh value for "Current" time
		$strTime = GetCurrentDateAndTimeForMySQL();
	}
	else
	{
		// Retrieve the cached "Current" time
		if (!isset($GLOBALS['CurrentISODateTime']))
		{
			// The "Current" timestamp isn't cached yet, do it now
			$GLOBALS['CurrentISODateTime'] = GetCurrentDateAndTimeForMySQL();
		}
		
		$strTime = $GLOBALS['CurrentISODateTime'];
	}
	
	if ($bolUpdateCache)
	{
		$GLOBALS['CurrentISODateTime'] = $strTime;
	}
	
	return $strTime;
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
	$strDatetime	= GetCurrentDateAndTimeForMySQL();
	$arrTimeParts	= explode(" ", $strDatetime);
	return $arrTimeParts[0];
}

//------------------------------------------------------------------------//
// GetCurrentISODate
//------------------------------------------------------------------------//
/**
 * GetCurrentISODate()
 *
 * Retrieves the current date and time in the ISO Date format
 *
 * Retrieves the current date and time in the ISO Date format
 * By default this value is cached, for subsequent calls
 * 
 * @param	bool	$bolForceRefresh	optional, defaults to FALSE.  If set to TRUE
 * 										then the "current" Date is retrieved from the
 * 										database's server.  If set to FALSE then the
 * 										cached "current" time is retrieved.
 * 
 * @param	bool	$bolUpdateCache		optional.  Only applicable when $bolForceRefresh == TRUE.
 * 										Defaults to FALSE.  If set to TRUE
 * 										then the cached value is updated
 *
 * @return	string			"current" date as an ISO Date string (YYYY-MM-DD)
 * @function
 */
function GetCurrentISODate($bolForceRefresh=FALSE, $bolUpdateCache=FALSE)
{
	$strDatetime	= GetCurrentISODateTime($bolForceRefresh, $bolUpdateCache);
	$arrTimeParts	= explode(" ", $strDatetime);
	return $arrTimeParts[0];
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


function EnsureLatestInvoiceRunEventsAreDefined()
{
	$arrColumns = array(
		'invoice_run_id' 	=> 'last_invoice_run.invoice_run_id',
		'customer_group_id' => 'last_invoice_run.customer_group_id',
		'processed' 		=> 'existing.invoice_run_id',
	);

	$strTables = "
		(SELECT customer_group_id, MAX(InvoiceRun.Id) invoice_run_id FROM InvoiceRun, invoice_run_status WHERE InvoiceRun.invoice_run_status_id = invoice_run_status.id AND invoice_run_status.const_name = 'INVOICE_RUN_STATUS_COMMITTED' GROUP BY customer_group_id) last_invoice_run
		LEFT OUTER JOIN (SELECT DISTINCT(invoice_run_id) FROM automatic_invoice_run_event) existing
		ON last_invoice_run.invoice_run_id = existing.invoice_run_id
		HAVING processed IS NULL
	";

	$selInvoiceRun = new StatementSelect($strTables, $arrColumns, "");

	$mxdReturn = $selInvoiceRun->Execute();
	if ($mxdReturn === FALSE)
	{
		throw new Exception("Failed to find latest invoice run: " . $selInvoiceRun->Error());
	}

	$invoiceRuns = $selInvoiceRun->FetchAll();

	if (!count($invoiceRuns))
	{
		// No invoice run to process
		return;
	}

	foreach ($invoiceRuns as $invoiceRun)
	{
		$invoiceRunId = $invoiceRun['invoice_run_id'];
		$customerGroupId = $invoiceRun['customer_group_id'];
	
		// Load up the automatic invoice actions
		$strTables = 'automatic_invoice_action_config';
		$arrColumns = array(
			'automatic_invoice_action_id' => 'automatic_invoice_action_id'
		);
		$strWhere = 'can_schedule = 1 AND customer_group_id' . ($customerGroupId ? " = $customerGroupId" : " IS NULL");
		$selInvoiceActions = new StatementSelect($strTables, $arrColumns, $strWhere);

		$mxdReturn = $selInvoiceActions->Execute();
		if ($mxdReturn === FALSE)
		{
			throw new Exception("Failed to load the automatic invoice actions: " . $selInvoiceActions->Error());
		}
		$arrColumns = array('automatic_invoice_action_id' => 0, 'invoice_run_id' => $invoiceRunId);
		$insEvent  = new StatementInsert('automatic_invoice_run_event', $arrColumns);
		while($invoiceAction = $selInvoiceActions->Fetch())
		{
			$arrColumns['automatic_invoice_action_id'] = $invoiceAction['automatic_invoice_action_id'];
			$mxdReturn = $insEvent->Execute($arrColumns);
			if ($mxdReturn === FALSE)
			{
				throw new Exception("Failed to create invoice run ($invoiceRunId) event ($invoiceAction): " . $insEvent->Error());
			}
		}
	}
}

function ListAutomaticUnbarringAccounts($intEffectiveTime)
{
	if (!$intEffectiveTime)
	{
		$intEffectiveTime = time();
	}
	$strEffectiveDate = date("'Y-m-d'", $intEffectiveTime);

	$strApplicableAccountStatuses = implode(", ", array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED, ACCOUNT_STATUS_SUSPENDED));

	$arrColumns = array(
							'invoice_run_id'			=> "MAX(CASE WHEN $strEffectiveDate <= Invoice.DueOn THEN '' ELSE Invoice.invoice_run_id END)",
							'AccountId'				=> "Invoice.Account",
							'AccountGroupId'		=> "Account.AccountGroup",
							'CustomerGroupId'		=> "Account.CustomerGroup",
							'CustomerGroupName'		=> "CustomerGroup.ExternalName",
							'Overdue'				=> "SUM(CASE WHEN $strEffectiveDate > Invoice.DueOn THEN Invoice.Balance END)",
							'minBalanceToPursue'	=> "payment_terms.minimum_balance_to_pursue",
	);

	$strTables = "
			 Invoice
		JOIN Account 
		  ON Invoice.Account = Account.Id
		 AND Account.Archived IN ($strApplicableAccountStatuses) 
		 AND Account.automatic_barring_status = " . AUTOMATIC_BARRING_STATUS_BARRED . " 
		JOIN Service 
		  ON Account.Id = Service.Account
		JOIN CustomerGroup
		  ON CustomerGroup.Id = Account.CustomerGroup
		JOIN payment_terms 
		  ON payment_terms.customer_group_id = Account.CustomerGroup
	";

	$strWhere	= "";

	$strGroupBy	= "Invoice.Account HAVING Overdue < minBalanceToPursue";
	$strOrderBy	= "Invoice.Account ASC";

	/*
	// DEBUG: Output the query that gets run
	$select = array();
	foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
	echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nGROUP BY $strGroupBy\nORDER BY $strOrderBy\n\n";
	//*/

	$selUnbarrable = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "", $strGroupBy);
	$mxdReturn = $selUnbarrable->Execute();
	return $mxdReturn === FALSE ? $mxdReturn : $selUnbarrable->FetchAll();
}


function ListStaggeredAutomaticBarringAccounts($intEffectiveTime, $arrInvoiceRunIds)
{
	if (!$intEffectiveTime)
	{
		$intEffectiveTime = time();
	}
	
	
	$db = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
	$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

	// If we don't know the customer group id ($intCustomerGroupId===FALSE) than we need to find it for the given invoice_run_id
	$strSQL = "SELECT distinct(customer_group_id) FROM InvoiceRun WHERE Id IN (" . implode(',', $arrInvoiceRunIds) . ") AND customer_group_id IS NOT NULL";
	if (PEAR::isError($result = $db->query($strSQL)))
	{
		throw new Exception("Failed to find customer group ids for invoice runs: \n$strSQL\n" . $result->getMessage());
	}
	$arrCustomerGroupIds = $result->fetchCol(0);
	
	$strCustomerGroupRestriction = count($arrCustomerGroupIds) ? "OR customer_group_id IN (" . implode(",", $arrCustomerGroupIds) . ")" : '';

	$time = time();
	
	// Create a temporary table for the results
	$tmpTableName = "tmp_staggered_barring_accounts_$time";

	$strSQL = "DROP TABLE IF EXISTS $tmpTableName;";
	if (PEAR::isError($result = $dbAdmin->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}
	

	$strSQL = "
			CREATE TABLE $tmpTableName
			(
				id bigint(20) UNSIGNED NOT NULL auto_increment,
				invoice_run_id bigint(20) unsigned NOT NULL,
				AccountId bigint(20) unsigned NOT NULL,
				AccountGroupId bigint(20) unsigned NOT NULL,
				CustomerGroupId bigint(20) unsigned NOT NULL,
				CustomerGroupName VARCHAR(255) DEFAULT '',
				Overdue decimal(13,4) NOT NULL,
				minBalanceToPursue decimal(13,4) NOT NULL,
				PRIMARY KEY (id)
			) ENGINE=InnoDB AUTO_INCREMENT=0;
			";
	if (PEAR::isError($result = $dbAdmin->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}

	// Create a temporary ranking table for the results
	$tmpRankTableName = "tmp_staggered_account_ranks_$time";

	$strSQL = "DROP TABLE IF EXISTS $tmpRankTableName;";
	if (PEAR::isError($result = $dbAdmin->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}

	$strSQL = "
			CREATE TABLE $tmpRankTableName
			(
				id bigint(20) UNSIGNED NOT NULL auto_increment,
				account_id bigint(20) unsigned NOT NULL,
				ranking decimal(13,4) NOT NULL,
				PRIMARY KEY (id)
			) ENGINE=InnoDB AUTO_INCREMENT=0;
			";
	if (PEAR::isError($result = $dbAdmin->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}


	// Load the details into a temporary table
	$strEffectiveDate = date("'Y-m-d'", $intEffectiveTime);

	$strApplicableAccountStatuses = implode(", ", array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED, ACCOUNT_STATUS_SUSPENDED));
	$strApplicableInvoiceStatuses = implode(", ", array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_PRINT));

	$arrColumns = array(
							'invoice_run_id'			=> "MAX(CASE WHEN $strEffectiveDate <= Invoice.DueOn THEN '' ELSE Invoice.invoice_run_id END)",
							'AccountId'				=> "Invoice.Account",
							'AccountGroupId'		=> "Account.AccountGroup",
							'CustomerGroupId'		=> "Account.CustomerGroup",
							'CustomerGroupName'		=> "CustomerGroup.ExternalName",
							'Overdue'				=> "SUM(CASE WHEN $strEffectiveDate > Invoice.DueOn THEN Invoice.Balance END)",
							'minBalanceToPursue'	=> "payment_terms.minimum_balance_to_pursue",
	);

	$strTables	= "
			 Invoice 
		JOIN Account 
		  ON Invoice.Account = Account.Id
		 AND Account.Archived IN ($strApplicableAccountStatuses) 
		 AND NOT Account.automatic_barring_status = " . AUTOMATIC_BARRING_STATUS_BARRED . " 
		 AND Account.BillingType = " . BILLING_TYPE_ACCOUNT . "
		 AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
		JOIN credit_control_status 
		  ON Account.credit_control_status = credit_control_status.id
		 AND credit_control_status.can_bar = 1
		JOIN account_status 
		  ON Account.Archived = account_status.id
		 AND account_status.can_bar = 1
		JOIN CustomerGroup 
		  ON Account.CustomerGroup = CustomerGroup.Id
		JOIN payment_terms
		  ON payment_terms.customer_group_id = Account.CustomerGroup";

	$strWhere	= "Account.Id IN (
		SELECT DISTINCT(Account.Id) 
		FROM InvoiceRun 
		JOIN Invoice
		  ON InvoiceRun.Id IN (" . implode(', ', $arrInvoiceRunIds) . ")
		 AND Invoice.Status IN ($strApplicableInvoiceStatuses) 
		 AND InvoiceRun.Id = Invoice.invoice_run_id
		JOIN Account 
		  ON Account.Id = Invoice.Account
		 AND Account.Archived IN ($strApplicableAccountStatuses) 
		 AND Account.BillingType = " . BILLING_TYPE_ACCOUNT . "
		 AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
		 AND NOT Account.automatic_barring_status = " . AUTOMATIC_BARRING_STATUS_BARRED . " 
		JOIN credit_control_status 
		  ON Account.credit_control_status = credit_control_status.id
		 AND credit_control_status.can_bar = 1
		JOIN account_status 
		  ON Account.Archived = account_status.id
		 AND account_status.can_bar = 1
	)";

	$strGroupBy	= "Invoice.Account HAVING Overdue >= minBalanceToPursue AND invoice_run_id IN (" . implode(', ', $arrInvoiceRunIds) . ")";
	$strOrderBy	= "Invoice.Account ASC";

	$select = array();
	foreach($arrColumns as $alias => $column) $select[] = "$column AS \"$alias\"";
	$tmpCols = implode(', ', array_keys($arrColumns));
	$strSQL = "INSERT INTO $tmpTableName ($tmpCols) SELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nWHERE $strWhere\nGROUP BY $strGroupBy\nORDER BY $strOrderBy";
	if (PEAR::isError($result = $db->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}

	// Apply the ranking to the table for each account
	$strSQL = "
			INSERT INTO $tmpRankTableName (account_id, ranking)
			SELECT account_id, CASE WHEN SUM(VIP) < 0 THEN SUM(VIP) WHEN COUNT(late) = 1 THEN 0 WHEN SUM(late) <= 0 THEN 0 ELSE ((SUM(late) / (COUNT(late) - 1))/86400) END AS \"ranking\"
			FROM
			(
			
				SELECT InvoicePayment.Account AS \"account_id\", 0 AS \"VIP\", InvoicePayment.invoice_run_id AS \"invoice_run_id\", UNIX_TIMESTAMP(MAX(Payment.PaidOn)) - UNIX_TIMESTAMP(Invoice.CreatedOn) AS \"late\" 
				FROM 
					Payment, 
					InvoicePayment, 
					Invoice, 
					$tmpTableName,
						(
						SELECT id AS \"invoice_run_id\"
						FROM InvoiceRun
						WHERE customer_group_id IS NULL $strCustomerGroupRestriction
						ORDER BY id DESC
						LIMIT 1, 6
					) PreviousSixInvoiceRuns
				WHERE Payment.Id = InvoicePayment.Payment
				    AND InvoicePayment.invoice_run_id = PreviousSixInvoiceRuns.invoice_run_id
				    AND Invoice.Account = $tmpTableName.AccountId 
				    AND Invoice.Account = InvoicePayment.Account
				    AND Invoice.invoice_run_id = InvoicePayment.invoice_run_id
				    AND Invoice.Total > 0.10
				    AND Invoice.Balance <= 0.10
				GROUP BY InvoicePayment.Account, InvoicePayment.invoice_run_id, Invoice.CreatedOn
			
			UNION
				SELECT Invoice.Account AS \"account_id\", 0 AS \"VIP\", Invoice.invoice_run_id AS \"invoice_run_id\", UNIX_TIMESTAMP() - UNIX_TIMESTAMP(Invoice.CreatedOn) AS \"late\"
				FROM 
					Invoice, $tmpTableName,
					(
						SELECT id AS \"invoice_run_id\"
						FROM InvoiceRun
						WHERE customer_group_id IS NULL $strCustomerGroupRestriction
						ORDER BY id DESC
						LIMIT 1, 6
					) PreviousSixInvoiceRuns
				WHERE Invoice.Account = $tmpTableName.AccountId 
				    AND Invoice.Balance > 0.10
				    AND Invoice.Total > 0.10
				    AND Invoice.invoice_run_id = PreviousSixInvoiceRuns.invoice_run_id
			
			UNION	
				SELECT Account.Id AS \"account_id\", CASE WHEN SUM(vip) > 0 THEN -2 WHEN SUM(Invoice.Id) > 0 THEN 0 ELSE -1 END AS \"VIP\", 0 AS \"invoice_run_id\", 0 AS \"late\" 
				FROM Account INNER JOIN $tmpTableName ON Account.Id = $tmpTableName.AccountId LEFT OUTER JOIN Invoice ON Account.Id = Invoice.Account
				GROUP BY Account.Id
			
			) as AccountRankings
			GROUP BY account_id
	";
	if (PEAR::isError($result = $db->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}

	// Load the details from the tmp tables in reverse rank order (worst first)
	$strSQL = "SELECT $tmpCols, ranking FROM $tmpTableName, $tmpRankTableName WHERE $tmpTableName.AccountId = $tmpRankTableName.account_id ORDER BY ranking DESC";
	if (PEAR::isError($result = $db->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}
	
	$results = $result->fetchAll(MDB2_FETCHMODE_ASSOC);

	// Drop the temp tables
	$strSQL = "DROP TABLE $tmpTableName;";
	if (PEAR::isError($result = $dbAdmin->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}
	
	$strSQL = "DROP TABLE $tmpRankTableName;";
	if (PEAR::isError($result = $dbAdmin->query($strSQL)))
	{
		throw new Exception($result->getMessage());
	}

	// Return the results
	return $results;
}

function ListAutomaticBarringAccounts($intEffectiveTime, $action=AUTOMATIC_INVOICE_ACTION_BARRING)
{
	if (!$intEffectiveTime)
	{
		$intEffectiveTime = time();
	}

	// First, we need to find which invoice runs are involved (if any)
	$arrInvoiceRuns = ListInvoiceRunsForAutomaticInvoiceActionAndDate($action, $intEffectiveTime);
	if (!count($arrInvoiceRuns))
	{
		// No invoice runs, so no accounts
		return array();
	}
	$strInvoiceRunIds = implode(', ', $arrInvoiceRuns);

	$strEffectiveDate = date("'Y-m-d'", $intEffectiveTime);

	$strApplicableAccountStatuses = implode(", ", array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED, ACCOUNT_STATUS_SUSPENDED));
	$strApplicableInvoiceStatuses = implode(", ", array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_PRINT));

	$arrColumns = array(
							'invoice_run_id'			=> "MAX(CASE WHEN $strEffectiveDate <= Invoice.DueOn THEN '' ELSE Invoice.invoice_run_id END)",
							'AccountId'				=> "Invoice.Account",
							'AccountGroupId'		=> "Account.AccountGroup",
							'CustomerGroupId'		=> "Account.CustomerGroup",
							'CustomerGroupName'		=> "CustomerGroup.ExternalName",
							'Overdue'				=> "SUM(CASE WHEN $strEffectiveDate > Invoice.DueOn THEN Invoice.Balance END)",
							'minBalanceToPursue'	=> "payment_terms.minimum_balance_to_pursue",
	);

	$strTables	= "
			 Invoice 
		JOIN Account 
		  ON Invoice.Account = Account.Id
		 AND Account.Archived IN ($strApplicableAccountStatuses) 
		 AND NOT Account.automatic_barring_status = " . AUTOMATIC_BARRING_STATUS_BARRED . " 
		 AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
		JOIN credit_control_status 
		  ON Account.credit_control_status = credit_control_status.id
		 AND credit_control_status.can_bar = 1
		JOIN account_status 
		  ON Account.Archived = account_status.id
		 AND account_status.can_bar = 1
		JOIN CustomerGroup 
		  ON Account.CustomerGroup = CustomerGroup.Id
		JOIN payment_terms
		  ON payment_terms.customer_group_id = Account.CustomerGroup";

	$strWhere	= "Account.Id IN (
		SELECT DISTINCT(Account.Id) 
		FROM InvoiceRun 
		JOIN Invoice
		  ON InvoiceRun.Id IN ($strInvoiceRunIds)
		 AND Invoice.Status IN ($strApplicableInvoiceStatuses) 
		 AND InvoiceRun.Id = Invoice.invoice_run_id
		JOIN Account 
		  ON Account.Id = Invoice.Account
		 AND Account.Archived IN ($strApplicableAccountStatuses) 
		 AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
		 AND NOT Account.automatic_barring_status = " . AUTOMATIC_BARRING_STATUS_BARRED . " 
		JOIN credit_control_status 
		  ON Account.credit_control_status = credit_control_status.id
		 AND credit_control_status.can_bar = 1
		JOIN account_status 
		  ON Account.Archived = account_status.id
		 AND account_status.can_bar = 1
	)";

	$strGroupBy	= "Invoice.Account HAVING Overdue >= minBalanceToPursue";
	$strOrderBy	= "Invoice.Account ASC";

	/*
	// DEBUG: Output the query that gets run
	$select = array();
	foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
	echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nWHERE $strWhere\nGROUP BY $strGroupBy\nORDER BY $strOrderBy\n\n";
	//*/

	$selBarrable = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "", $strGroupBy);
	$mxdReturn = $selBarrable->Execute();
	return $mxdReturn === FALSE ? FALSE : $selBarrable->FetchAll();
}

function ListInvoiceRunsForAutomaticInvoiceActionAndDate($intAutomaticInvoiceActionType, $intEffectiveDate)
{
	$strEffectiveDate = date("'Y-m-d H:i:s'", $intEffectiveDate);
	$arrColumns = array(
		"invoice_run_id" => "automatic_invoice_run_event.invoice_run_id",
		"unsatisfied" => "unsatisfied_dependencies.nr"
	);

	$strTables = "
		automatic_invoice_run_event
		LEFT OUTER JOIN 
		(
			SELECT invoice_run_id, count(*) nr
			  FROM automatic_invoice_run_event aire
			 WHERE actioned_datetime IS NULL
			   AND automatic_invoice_action_id IN 
				(
					SELECT prerequisite_automatic_invoice_action_id 
					  FROM automatic_invoice_action_dependency 
					 WHERE dependent_automatic_invoice_action_id = $intAutomaticInvoiceActionType
				)
			GROUP BY invoice_run_id
		) unsatisfied_dependencies
		ON automatic_invoice_action_id = $intAutomaticInvoiceActionType
		AND unsatisfied_dependencies.invoice_run_id = automatic_invoice_run_event.invoice_run_id
	";

	$strWhere = "
			actioned_datetime IS NULL
		AND scheduled_datetime IS NOT NULL
		AND scheduled_datetime <= $strEffectiveDate
		AND automatic_invoice_action_id = $intAutomaticInvoiceActionType
	";

	$strGroupBy = " automatic_invoice_run_event.invoice_run_id HAVING (unsatisfied IS NULL OR unsatisfied = 0)";

	/*
	// DEBUG: Output the query that gets run
	$select = array();
	foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
	echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nGROUP BY $strGroupBy\n\n";
	//*/

	$selInvoiceRuns = new StatementSelect($strTables, $arrColumns, $strWhere, '', '', $strGroupBy);
	$mxdReturn = $selInvoiceRuns->Execute();
	if ($mxdReturn === FALSE)
	{
		throw new Exception('Failed to find relevant invoice runs: ' . $selInvoiceRuns->Error());
	}
	$arrInvoiceRuns = $selInvoiceRuns->FetchAll();
	foreach($arrInvoiceRuns as $i => $invoiceRun)
	{
		$arrInvoiceRuns[$i] = $invoiceRun['invoice_run_id'];
	}
	return $arrInvoiceRuns;
}

function ListLatePaymentAccounts($intAutomaticInvoiceActionType, $intEffectiveDate)
{
	$strEffectiveDate = date("'Y-m-d'", $intEffectiveDate);

	// Set up NoticeType specific stuff here
	$arrApplicableAccountStatuses = array();
	$arrApplicableInvoiceStatuses = array();

	// First, we need to find which invoice runs are involved (if any)
	$arrInvoiceRuns = ListInvoiceRunsForAutomaticInvoiceActionAndDate($intAutomaticInvoiceActionType, $intEffectiveDate);
	if (!count($arrInvoiceRuns))
	{
		// No invoice runs, so no accounts
		return array();
	}
	$strInvoiceRunIds = implode(', ', $arrInvoiceRuns);

	switch ($intAutomaticInvoiceActionType)
	{
		case AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_LIST:
		case AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER:
		case AUTOMATIC_INVOICE_ACTION_LATE_FEES:
		case AUTOMATIC_INVOICE_ACTION_LATE_FEES_LIST:
		case AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE:
		case AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE_LIST:
			$strAccountBillingType = "AND Account.BillingType = " . BILLING_TYPE_ACCOUNT;
			$arrApplicableAccountStatuses = array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED);
			$arrApplicableInvoiceStatuses = array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_PRINT);
			break;
		case AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE:
		case AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE_LIST:
			$arrApplicableAccountStatuses = array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED);
			$arrApplicableInvoiceStatuses = array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_PRINT);
			break;
		case AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND:
		case AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND_LIST:
			$arrApplicableAccountStatuses = array(ACCOUNT_STATUS_ACTIVE, ACCOUNT_STATUS_CLOSED, ACCOUNT_STATUS_SUSPENDED);
			$arrApplicableInvoiceStatuses = array(INVOICE_COMMITTED, INVOICE_DISPUTED, INVOICE_PRINT);
			break;
		default:
			// Unrecognised notice type
			return FALSE;
			break;
	}
	$arrApplicableAccountStatuses = implode(", ", $arrApplicableAccountStatuses);
	$strApplicableInvoiceStatuses = implode(", ", $arrApplicableInvoiceStatuses);

	// Find all Accounts that fit the requirements for Late Notice generation
	$arrColumns = Array(	'invoice_run_id'			=> "MAX(CASE WHEN $strEffectiveDate <= Invoice.DueOn THEN 0 ELSE Invoice.invoice_run_id END)",
							'AccountId'				=> "Invoice.Account",
							'AccountGroup'			=> "Account.AccountGroup",
							'BusinessName'			=> "Account.BusinessName",
							'TradingName'			=> "Account.TradingName",
							'CustomerGroup'			=> "Account.CustomerGroup",
							'AccountStatus'			=> "Account.Archived",
							'automatic_invoice_action'=> "Account.last_automatic_invoice_action",
							'LatePaymentAmnesty'	=> "Account.LatePaymentAmnesty",
							'DeliveryMethod'		=> "Account.BillingMethod",
							'FirstName'				=> "Contact.FirstName",
							'LastName'				=> "Contact.LastName",
							'Email'					=> "Contact.Email",
							'EmailFrom'				=> "CustomerGroup.OutboundEmail",
							'CustomerGroupName'		=> "CustomerGroup.ExternalName",
							'Title'					=> "Contact.Title",
							'AddressLine1'			=> "Account.Address1",
							'AddressLine2'			=> "Account.Address2",
							'Suburb'				=> "UPPER(Account.Suburb)",
							'Postcode'				=> "Account.Postcode",
							'State'					=> "Account.State",
							'DisableLatePayment'	=> "Account.DisableLatePayment",
							'InvoiceId'				=> "MAX(CASE WHEN $strEffectiveDate > Invoice.DueOn THEN Invoice.Id END)",
							'CreatedOn'				=> "MAX(CASE WHEN $strEffectiveDate > Invoice.DueOn THEN Invoice.CreatedOn END)",
							'OutstandingNotOverdue'	=> "SUM(CASE WHEN $strEffectiveDate <= Invoice.DueOn THEN Invoice.Balance END)",
							'Overdue'				=> "SUM(CASE WHEN $strEffectiveDate > Invoice.DueOn THEN Invoice.Balance END)",
							'TotalOutstanding'		=> "SUM(Invoice.Balance)",
							'minBalanceToPursue'	=> "payment_terms.minimum_balance_to_pursue");

	$strTables	= "Invoice 
		JOIN Account 
		  ON Invoice.Account = Account.Id 
		 AND Invoice.Status IN ($strApplicableInvoiceStatuses)
		 AND Account.Archived IN ($arrApplicableAccountStatuses) 
		 AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
		JOIN credit_control_status 
		  ON Account.credit_control_status = credit_control_status.id
		 AND credit_control_status.send_late_notice = 1
		JOIN account_status 
		  ON Account.Archived = account_status.id
		 AND account_status.send_late_notice = 1
		JOIN Contact 
		  ON Account.PrimaryContact = Contact.Id 
		JOIN CustomerGroup 
		  ON Account.CustomerGroup = CustomerGroup.Id
		JOIN payment_terms
		  ON payment_terms.customer_group_id = Account.CustomerGroup";

	$strWhere	= "Account.Id IN (
		SELECT DISTINCT(Account.Id) 
		FROM InvoiceRun 
		JOIN Invoice
		  ON InvoiceRun.Id IN ($strInvoiceRunIds)
	     AND Invoice.Status IN ($strApplicableInvoiceStatuses) 
		 AND InvoiceRun.Id = Invoice.invoice_run_id
		JOIN Account 
		  ON Account.Id = Invoice.Account
	         AND Account.Archived IN ($arrApplicableAccountStatuses) $strAccountBillingType
	         AND (Account.LatePaymentAmnesty IS NULL OR Account.LatePaymentAmnesty < $strEffectiveDate)
		JOIN credit_control_status 
		  ON Account.credit_control_status = credit_control_status.id
		 AND credit_control_status.send_late_notice = 1
		JOIN account_status 
		  ON Account.Archived = account_status.id
		 AND account_status.send_late_notice = 1
	)";

	$strOrderBy	= "Invoice.Account ASC";
	$strGroupBy	= "Invoice.Account HAVING Overdue >= minBalanceToPursue";

	/*
	// DEBUG: Output the query that gets run
	$select = array();
	foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
	echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nWHERE $strWhere\nGROUP BY $strGroupBy\nORDER BY $strOrderBy\n\n";
	//*/

	$selOverdue = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy, "", $strGroupBy);
	$mxdReturn = $selOverdue->Execute();
	if ($mxdReturn !== FALSE)
	{
		$mxdReturn = $selOverdue->FetchAll();
	}
	return $mxdReturn;
}


function GetPaymentTerms($customerGroupId)
{
	static $paymentTerms;
	if (!isset($paymentTerms))
	{
		$paymentTerms = array();
	}
	if (!array_key_exists($customerGroupId, $paymentTerms))
	{
		// Need to load the payment terms from the payment_terms table
		$arrColumns = array(
			'invoice_day' 				=> 'invoice_day',
			'payment_terms' 			=> 'payment_terms',
			'minimum_balance_to_pursue' => 'minimum_balance_to_pursue',
			'late_payment_fee' 			=> 'late_payment_fee',
		);

		$strWhere = 'id IN (SELECT MAX(id) FROM payment_terms WHERE customer_group_id ' . (intval($customerGroupId) ? (' = ' . intval($customerGroupId)) : 'IS NULL') . ")";

		$selSelect = new StatementSelect('payment_terms', $arrColumns, $strWhere);
		$mxdResult = $selSelect->Execute();

		if ($mxdResult === FALSE)
		{
			throw new Exception('Failed to load payment terms.');
		}

		$payementTermsX = $selSelect->FetchAll();
		if (!count($payementTermsX))
		{
			throw new Exception('Payment terms have not been configurred.');
		}

		$paymentTerms[$customerGroupId] = $payementTermsX[0];

		$strTables = 'automatic_invoice_action aa, automatic_invoice_action_config aac';
		$strWhere = 'aa.id = aac.automatic_invoice_action_id AND aac.customer_group_id ' . (intval($customerGroupId) ? (' = ' . intval($customerGroupId)) : 'IS NULL') . ' AND NOT aa.id = ' . AUTOMATIC_INVOICE_ACTION_NONE;
		$arrColumns = array(
			'id' => 'aa.id',
			'days_from_invoice' => 'days_from_invoice',
		);
		$selSelect = new StatementSelect($strTables, $arrColumns, $strWhere);
		$mxdResult = $selSelect->Execute(array('CustomerGroupId' => $customerGroupId));
		if ($mxdResult === FALSE)
		{
			throw new Exception('Failed to load payment terms.');
		}
		$arrAutomaticInvoiceActions = $selSelect->FetchAll();
		foreach ($arrAutomaticInvoiceActions as $arrAutomaticInvoiceAction)
		{
			$paymentTerms[$customerGroupId][$arrAutomaticInvoiceAction['id']] = $arrAutomaticInvoiceAction['days_from_invoice'];
		}
	}
	return $paymentTerms[$customerGroupId];
}


function CreateDefaultPaymentTerms($customerGroupId)
{
	TransactionStart();
	
	// Create the default payment terms
	$arrPaymentTerms = array(
		'customer_group_id' => $customerGroupId,
		'invoice_day' => '1',
		'payment_terms' => '14',
		'minimum_balance_to_pursue' => '0.01',
		'late_payment_fee' => '0.00',
		'created' => date('Y-m-d H:i:s'),
		'direct_debit_days' => 15,
		'direct_debit_minimum' => '0.01',
	);
	$insPaymentTerms = new StatementInsert("payment_terms", $arrPaymentTerms);
	if (($id = $insPaymentTerms->Execute($arrPaymentTerms)) === FALSE)
	{
		TransactionRollback();
		throw new Exception('Failed to create default payment terms for customer group ' . $customerGroupId . ': ' . $insPaymentTerms->Error());
	}

	// Create the default automatic_invoice_action_config entries
	
	// Load up the automatic invoice action configs for the NULL customer_group_id
	$selAutoInvActions = new StatementSelect('automatic_invoice_action_config', 
							array('automatic_invoice_action_id', 'days_from_invoice', 'can_schedule', 'response_days'), 
							'customer_group_id IS NULL');
	if (($result = $selAutoInvActions->Execute()) === FALSE)
	{
		TransactionRollback();
		throw new Exception('Failed to load default automatic invoice action configurations: ' . $selAutoInvActions->Error());
	}
	$automaticInvoiceActions = $selAutoInvActions->FetchAll();
	
	$insAutoInvAction = NULL;
	foreach ($automaticInvoiceActions as $automaticInvoiceAction)
	{
		$automaticInvoiceAction['customer_group_id'] = $customerGroupId;
		if (!$insAutoInvAction)
		{
			$insAutoInvAction = new StatementInsert('automatic_invoice_action_config', $automaticInvoiceAction);
		}
		if (($result = $insAutoInvAction->Execute($automaticInvoiceAction)) === FALSE)
		{
			TransactionRollback();
			throw new Exception('Failed to create default automatic invoice action configuration ' . $automaticInvoiceAction['automatic_invoice_action_id'] . ' for customer group ' . $customerGroupId . ': ' . $selAutoInvActions->Error());
		}
	}

	TransactionCommit();
	
	return $id;
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
 * @param	int		$intAutomaticInvoiceActionType	type of action to produce a notice for. eg AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE
 * @param	string	$strBasePath	optional, path where the generated notices will be placed
 *									
 * @return	mixed					returns FALSE on failure 
 *									returns	Array['Successful']	= number of successfully generated notices of the NoticeType
 *											Array['Failed'] 	= number of notices that failed to generate, of the NoticeType
 * @function
 */
function GenerateLatePaymentNotices($intAutomaticInvoiceActionType, $intEffectiveDate=0, $strBasePath=FILES_BASE_PATH)
{
	$selPriorNotices = new StatementSelect("AccountLetterLog", "Id", "Invoice = <InvoiceId> AND LetterType = <NoticeType>", "", 1);

	// Append a backslash to the path, if it doesn't already end in one
	if (substr($strBasePath, -1) != "/")
	{
		$strBasePath .= "/";
	}

	// Retrieve the list of CustomerGroups
	$selCustomerGroups = new StatementSelect("CustomerGroup", "Id, InternalName, ExternalName");
	$selCustomerGroups->Execute();
	$arrCustomerGroups = Array();
	while (($arrCustomerGroup = $selCustomerGroups->Fetch()) !== FALSE)
	{
		$arrCustomerGroups[$arrCustomerGroup['Id']] = $arrCustomerGroup;
	}

	$intEffectiveDate = $intEffectiveDate ? $intEffectiveDate : time();

	// Find all Accounts that fit the requirements for Late Notice generation
	$arrAccounts = ListLatePaymentAccounts($intAutomaticInvoiceActionType, $intEffectiveDate);

	if ($arrAccounts === FALSE)
	{
		// Failed to retrieve the data from the database
		return FALSE;
	}
	
	// Store a running total of how many were successfully generated, and how many failed, for each notice type
	$arrGeneratedNotices = Array("Successful" => 0, "Failed" => 0, "Details" => array());
	$arrSummary = Array();

	VixenRequire('lib/dom/Flex_Dom_Document.php');
	$dom = new Flex_Dom_Document();

	// For each account retrieved, build the late payment notice for it
	foreach ($arrAccounts as $arrAccount)
	{
		$mxdSuccess = NULL;

		switch ($intAutomaticInvoiceActionType)
		{
			case AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER:
				// If the account has a status of "Active" or "Closed", then they are eligible for recieving late notices
				// This condition is forced in the WHERE clause of the $selOverdue StatementSelect object
				$intNoticeType = DOCUMENT_TEMPLATE_TYPE_FRIENDLY_REMINDER;
				$mxdSuccess = BuildLatePaymentNotice($intNoticeType, $arrAccount, $strBasePath, $intEffectiveDate, $intAutomaticInvoiceActionType);
				break;

			case AUTOMATIC_INVOICE_ACTION_OVERDUE_NOTICE:
				// If the account has a status of "Active" or "Closed", then they are eligible for recieving late notices
				// This condition is forced in the WHERE clause of the $selOverdue StatementSelect object
				$intNoticeType = DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE;
				$mxdSuccess = BuildLatePaymentNotice($intNoticeType, $arrAccount, $strBasePath, $intEffectiveDate, $intAutomaticInvoiceActionType);
				break;

			case AUTOMATIC_INVOICE_ACTION_SUSPENSION_NOTICE:
				// Check if the Overdue Notice was built this month, and if so build the suspension notice
				$intNumRows = $selPriorNotices->Execute(Array("InvoiceId" => $arrAccount['InvoiceId'], "NoticeType" => DOCUMENT_TEMPLATE_TYPE_OVERDUE_NOTICE));
				$intNoticeType = DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE;
				if ($intNumRows == 1)
				{
					// An "Overdue" notice has been sent for this invoice.  Build the Suspension notice
					$mxdSuccess = BuildLatePaymentNotice($intNoticeType, $arrAccount, $strBasePath, $intEffectiveDate, $intAutomaticInvoiceActionType);
				}
				break;

			case AUTOMATIC_INVOICE_ACTION_FINAL_DEMAND:
				// Check if the Suspension Notice was built this month, and if so build the final demand notice
				$intNumRows = $selPriorNotices->Execute(Array("InvoiceId" => $arrAccount['InvoiceId'], "NoticeType" => DOCUMENT_TEMPLATE_TYPE_SUSPENSION_NOTICE));
				$intNoticeType = DOCUMENT_TEMPLATE_TYPE_FINAL_DEMAND;
				if ($intNumRows == 1)
				{
					// A "Suspension" notice has been sent for this invoice.  Build the Final Demand notice
					$mxdSuccess = BuildLatePaymentNotice($intNoticeType, $arrAccount, $strBasePath, $intEffectiveDate, $intAutomaticInvoiceActionType);
				}
				break;
		}

		if ($mxdSuccess !== NULL)
		{
			if ($mxdSuccess !== FALSE)
			{
				$arrGeneratedNotices['Successful'] += 1;
				$i = count($arrGeneratedNotices['Details']);
				$arrGeneratedNotices['Details'][$i]['Account'] = $arrAccount;
				$arrGeneratedNotices['Details'][$i]['XMLFilePath'] = $mxdSuccess;
				$bolSuccess = TRUE;
			}
			else 
			{
				$arrGeneratedNotices['Failed'] += 1;
				$bolSuccess = FALSE;
			}

			$arrSummary[] = Array(	"AccountId"					=> $arrAccount['AccountId'], 
									"Outcome"					=> ($bolSuccess ? "Successful":"Failed"),
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
	$strFilename = 	str_replace(" ", "_", strtolower(GetConstantDescription($intNoticeType, "DocumentTemplateType"))). 
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

function GetAutomaticInvoiceActionResponseTime($intActionId, $intCustomerGroupId)
{
	static $cache;
	if (!isset($cache))
	{
		$cache = array();
	}
	if (!array_key_exists($intActionId, $cache))
	{
		$arrColumns = array('response_days' => 'response_days');
		$strTables = "automatic_invoice_action_config";
		$strWhere = "automatic_invoice_action_id = $intActionId AND " . ($intCustomerGroupId ? "customer_group_id = $intCustomerGroupId" : " IS NULL");
		$selDays = new StatementSelect($strTables, $arrColumns, $strWhere);
		$mxdReturn = $selDays->Execute();
		$result = FALSE;
		if ($mxdReturn !== FALSE)
		{
			$days = $selDays->Fetch();
			if ($days)
			{
				$result = $days['response_days'];
			}
		}
		$cache[$intActionId] = $result;
	}
	return $cache[$intActionId];
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
 * @param	integer	$intNoticeType	Type of notice to generate DOCUMENT_TEMPLATE_TYPE_[OVERDUE_NOTICE|SUSPENSION_NOTICE|FINAL_DEMAND|FRIENDLY_REMINDER]
 * @param	array	$arrAccount		All Account, Contact and Invoice data required for the notice
 * @param	string	$strBasePath	optional, base path where the generated notices will be placed. Must end with a '/'
 *									
 * @return	bool					TRUE if the notice was successfully generated, else FALSE
 *
 * @function
 */
function BuildLatePaymentNotice($intNoticeType, $arrAccount, $strBasePath=FILES_BASE_PATH, $intEffectiveDate, $intAutomaticInvoiceActionType)
{
	// Static instances of the db access objects used to add records to the AccountNotice and FileExport tables
	// are used so that the same objects don't have to be built for each individual Late Payment Notice that gets
	// made in a run
	static $insNotice;
	static $insFileExport;

	// Directory structure = BasePath/CustomerGroup/NoticeType/YYYY/MM/DD/
	$strFullPath = 	$strBasePath . str_replace(" ", "_", strtolower(GetConstantDescription($intNoticeType, "DocumentTemplateType"))) . "/xml/" . date("Ymd");
	
	// Make the directory structure if it hasn't already been made
	if (!is_dir($strFullPath))
	{
		RecursiveMkdir($strFullPath);
	}

	// Create the filename
	$strFilename = $arrAccount['AccountId'] . ".xml";

	// Build XML for the data...
	VixenRequire('lib/dom/Flex_Dom_Document.php');
	$dom = new Flex_Dom_Document();

	// Set up all values required of the notice, which have not been defined yet
	$dom->Document->DocumentType->setValue(GetConstantName($intNoticeType, 'DocumentTemplateType'));

	$responseDays = GetAutomaticInvoiceActionResponseTime($intAutomaticInvoiceActionType, $arrAccount['CustomerGroup']);
	$actionDate = ($responseDays * 24 * 60 * 60) + $intEffectiveDate;

	// Always issue on the scheduled date!
	$dom->Document->DateIssued = date("d M Y", $intEffectiveDate);

	switch($arrAccount['DeliveryMethod'])
	{
		case DELIVERY_METHOD_POST:
			$strDeliveryMethod='DELIVERY_METHOD_POST';
			break;
		case DELIVERY_METHOD_EMAIL:
		case DELIVERY_METHOD_EMAIL_SENT:
			$strDeliveryMethod='DELIVERY_METHOD_EMAIL';
			break;
		case DELIVERY_METHOD_DO_NOT_SEND:
		default;
			$strDeliveryMethod='DELIVERY_METHOD_DO_NOT_SEND';
			break;
	}

	$dom->Document->CustomerGroup->setValue(GetConstantName($arrAccount['CustomerGroup'], 'CustomerGroup'));
	$dom->Document->CreationDate->setValue(date("Y-m-d H:i:s"));
	$dom->Document->DeliveryMethod->setValue($strDeliveryMethod);

	$dom->Document->Currency->Symbol->Location = 'Prefix';
	$dom->Document->Currency->Symbol->setValue('$');
	$dom->Document->Currency->Negative->Location = 'Suffix';
	$dom->Document->Currency->Negative->setValue('CR');

	$dom->Document->Account->Id = $arrAccount['AccountId'];
	$dom->Document->Account->Name = $arrAccount['BusinessName'];
	$dom->Document->Account->CustomerGroup = GetConstantName($arrAccount['CustomerGroup'], 'CustomerGroup');
	$dom->Document->Account->Email->setValue($arrAccount['Email']);
	$dom->Document->Account->Addressee->setValue($arrAccount['BusinessName']);
	$dom->Document->Account->AddressLine1;
	if (trim($arrAccount['AddressLine1']))
	{
		$dom->Document->Account->AddressLine1->setValue(trim($arrAccount['AddressLine1']));
	}
	$dom->Document->Account->AddressLine2;
	if (trim($arrAccount['AddressLine2']))
	{
		$dom->Document->Account->AddressLine2->setValue(trim($arrAccount['AddressLine2']));
	}
	$dom->Document->Account->Suburb->setValue(strtoupper($arrAccount['Suburb']));
	$dom->Document->Account->Postcode->setValue($arrAccount['Postcode']);
	$dom->Document->Account->State->setValue(strtoupper($arrAccount['State']));

	$dom->Document->PrimaryContact->FirstName->setValue($arrAccount['FirstName']);
	$dom->Document->PrimaryContact->LastName->setValue($arrAccount['LastName']);
	$dom->Document->PrimaryContact->Title->setValue($arrAccount['Title']);
	$dom->Document->PrimaryContact->FullName->setValue(
		($arrAccount['Title'] ? $arrAccount['Title'] . ' ' : '') .
		($arrAccount['FirstName'] ? $arrAccount['FirstName'] . ' ' : '') .
		$arrAccount['LastName']);

	$dom->Document->Payment->BPay->CustomerReference->setValue($arrAccount['AccountId'] . MakeLuhn($arrAccount['AccountId']));
	$dom->Document->Payment->BillExpress->CustomerReference->setValue($arrAccount['AccountId'] . MakeLuhn($arrAccount['AccountId']));

	$dom->Document->Outstanding->Overdue->setValue(number_format($arrAccount['Overdue'], 2, ".", ""));
	$dom->Document->Outstanding->NotOverdue->setValue(number_format($arrAccount['OutstandingNotOverdue'], 2, ".", ""));
	$dom->Document->Outstanding->Total->setValue(number_format($arrAccount['TotalOutstanding'], 2, ".", ""));
	$dom->Document->Outstanding->CurrentInvoiceId->setValue($arrAccount['InvoiceId']);

	$dom->Document->Outstanding->ActionDate->setValue(date("d M Y", $actionDate));

	$strXML = $dom->saveXML();

	$return = $strFullPath ."/". $strFilename;

	// Open the file in text mode
	$ptrNoticeFile = fopen($return, 'wt');

	if ($ptrNoticeFile === FALSE)
	{
		// The file could not be opened
		return FALSE;
	}

	fwrite($ptrNoticeFile, $strXML);
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
							'SHA1'			=>	sha1($strXML));

	// Only define the StatementInsert object if it hasn't already been defined				
	if (!isset($insFileExport))
	{
		$insFileExport = new StatementInsert("FileExport", $arrFileLog);
	}
	$insFileExport->Execute($arrFileLog);

	// Create a system note for the account
	$strNote = 	GetConstantDescription($intNoticeType, "DocumentTemplateType") . " has been generated\n".
				"Outstanding Overdue: \$" . number_format($arrAccount['Overdue'], 2, '.', '') . "\n".
				"Outstanding Not Overdue: \$" . number_format($arrAccount['OutstandingNotOverdue'], 2, '.', '');

	$GLOBALS['fwkFramework']->AddNote($strNote, SYSTEM_NOTE_TYPE, NULL, $arrAccount['AccountGroup'], $arrAccount['AccountId']);

	return $return;
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

//------------------------------------------------------------------------//
// BuildConstantsFromDB
//------------------------------------------------------------------------//
/**
 * BuildConstantsFromDB()
 *
 * Declares all constants and ConstantGroups stored in the database, so long as the constants have not already been defined
 *
 * Declares all constants and ConstantGroups stored in the database, so long as the constants have not already been defined
 * If a constant declared in the database, has aleady been declared in the php process, 
 * then it will not be changed.  All ConstantGroups defined in the database are also loaded into the 
 * $GLOBALS['*arrConstant'][ConstantGroupName] structure
 *
 * @param	boolean		$bolExceptionOnError			optional, if TRUE then an exception is thrown if 
 * 														it cannot resolve the data type of the constant
 * 														or if it is declaring a constant in a ConstantGroup
 * 														with a value that is already used by another constant
 * 														within the constant group. Defaults to FALSE
 * @param	booleab		$bolExceptionOnRedefinition		optional, if TRUE then an exception is thrown if
 * 														a constant in the database has already been defined
 * 														in the php global namespace. Defaults to FALSE
 *
 * @return	boolean								TRUE on success, else FALSE
 *
 * @function
 */
function BuildConstantsFromDB($bolExceptionOnError=FALSE, $bolExceptionOnRedefinition=FALSE)
{
	$strTables	= "ConfigConstant AS CC INNER JOIN ConfigConstantGroup AS CCG ON CC.ConstantGroup = CCG.Id";
	$arrColumns	= Array("Id"				=> "CC.Id", 
						"Name"				=> "CC.Name", 
						"Value"				=> "CC.Value", 
						"ConstDesc"			=> "CC.Description",
						"Type"				=> "CASE WHEN CCG.Special THEN CCG.Type ELSE CC.Type END",
						"ConstGroupName"	=> "CCG.Name",
						"Special" 			=> "CCG.Special");
	$strOrderBy	= "CC.ConstantGroup, CC.Id";
	$strWhere	= "TRUE"; 
	$selConstants = new StatementSelect($strTables, $arrColumns, $strWhere, $strOrderBy);
	
	$selConstants->Execute();
	$arrConstants = $selConstants->FetchAll();
	
	foreach ($arrConstants as $arrConstant)
	{
		// Check if the constant has already been defined
		if (defined($arrConstant['Name']))
		{
			// The constant has already been defined.  
			if ($bolExceptionOnRedefinition)
			{
				// Throw an exception
				$strMsg = "Error: Attempting to declare constant: {$arrConstant['Name']} with value: {$arrConstant['Value']}, but this has already been declared within the php global namespace with value: ". constant($arrConstant['Name']);
				throw new Exception($strMsg);
			}
			
			// Move on to the next constant
			continue;
		}

		// Type cast the constant's value to its data type
		if ($arrConstant['Value'] === NULL)
		{
			// Don't bother type casting it if it is equal to NULL
			$mixValue = NULL;
		}
		else
		{
			switch ($arrConstant['Type'])
			{
				case 1: //DATA_TYPE_STRING:
					$mixValue = (string)$arrConstant['Value']; 
					break;
				case 2: //DATA_TYPE_INTEGER:
					$mixValue = (integer)$arrConstant['Value'];
					break;
				case 3: //DATA_TYPE_FLOAT:
					$mixValue = (float)$arrConstant['Value'];
					break;
				case 4: //DATA_TYPE_BOOLEAN:
					$mixValue = (bool)$arrConstant['Value'];
					break;
				default:
					// Unknown data type
					if ($bolExceptionOnError)
					{
						// Throw an exception
						$strMsg = "Error: Constant: {$arrConstant['Name']} with value: {$arrConstant['Value']}, has unknown datatype {$arrConstant['Type']}";
						throw new Exception($strMsg);
					}
					
					return FALSE;
					break;
			}
		}

		// If the constant is part of a special ConstantGroup then add it to the $GLOBALS['*arrConstant'] array
		if ($arrConstant['Special'])
		{
			// Check that the value is not already in use by another constant within the ConstantGroup
			if (isset($GLOBALS['*arrConstant'][$arrConstant['ConstGroupName']][$mixValue]))
			{
				// This value is already being used
				if ($bolExceptionOnRedefinition)
				{
					// Throw an exception
					$strMsg = 	"Error: ConstantGroup: {$arrConstant['ConstGroupName']} already has constant ". 
								$GLOBALS['*arrConstant'][$arrConstant['ConstGroupName']][$mixValue]['Constant'] .
								" set to $mixValue so it cannot also contain the constant {$arrConstant['Name']} which ".
								"is also set to $mixValue";
					throw new Exception($strMsg);
				}
				continue;
			}
			
			$GLOBALS['*arrConstant'][$arrConstant['ConstGroupName']][$mixValue]['Constant']		= $arrConstant['Name'];
			$GLOBALS['*arrConstant'][$arrConstant['ConstGroupName']][$mixValue]['Description']	= $arrConstant['ConstDesc'];
		}

		// Declare the constant
		define($arrConstant['Name'], $mixValue);

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
	
	//HACK! HACK! HACK! HACK! HACK! HACK! HACK! HACK!
	// Load in the CustomerGroup constants from the CustomerGroup table.
	// These constants are now only used by the backend.  The frontend always refers to the database
	// when dealing with customer groups
	// This block of code can be removed when the backend no longer relies on them
	$selCustomerGroup = new StatementSelect("CustomerGroup", "Id, InternalName", "TRUE", "Id");
	$selCustomerGroup->Execute();
	$arrCustomerGroups = $selCustomerGroup->FetchAll();
	foreach ($arrCustomerGroups as $arrCustomerGroup)
	{
		// Build the constant name
		$strConstant		= "CUSTOMER_GROUP_" . strtoupper(str_replace(" ", "_", $arrCustomerGroup['InternalName']));
		$strDescription		= $arrCustomerGroup['InternalName'];
		$intCustomerGroup	= $arrCustomerGroup['Id'];
		
		define($strConstant, $intCustomerGroup);
		$GLOBALS['*arrConstant']['CustomerGroup'][$intCustomerGroup]['Constant']	= $strConstant;
		$GLOBALS['*arrConstant']['CustomerGroup'][$intCustomerGroup]['Description']	= $strDescription;
	}
	//HACK! HACK! HACK! HACK! HACK! HACK! HACK! HACK!
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


//------------------------------------------------------------------------//
// TruncateTime()
//------------------------------------------------------------------------//
/**
 * TruncateTime()
 *
 * Truncates a Unix Timestamp to a specified degree of accuracy
 *
 * Truncates a Unix Timestamp to a specified degree of accuracy.
 * 
 * @param	integer	$intTime					The timestamp to truncate
 * @param	string	$strAccuracy				Where to truncate the timestamp.  Accepts 'y', 'm', 'd', 'h', 'i', or 's'.
 * @param	string	$strRound					'floor': Rounded Down; 'ceil': Rounded Up
 *
 * @return	integer								Truncated Timestamp
 *
 * @function
 */
function TruncateTime($intTime, $strAccuracy, $strRound)
{
	// Set up default values
	$arrParts		= Array();
	if ($strRound == 'ceil')
	{
		$arrParts['Y']	= 2037;
		$arrParts['m']	= 12;
		$arrParts['d']	= 31;
		$arrParts['H']	= 23;
		$arrParts['i']	= 59;
		$arrParts['s']	= 59;
	}
	else
	{
		$arrParts['Y']	= 1970;
		$arrParts['m']	= 1;
		$arrParts['d']	= 1;
		$arrParts['H']	= 0;
		$arrParts['i']	= 0;
		$arrParts['s']	= 0;
	}
	
	// Truncate time
	$bolTruncated	= FALSE;
	foreach ($arrParts as $strPart=>$intValue)
	{
		// If we're already truncated
		if ($bolTruncated)
		{
			// Use default
			continue;
		}
		elseif (strtolower($strPart) === strtolower($strAccuracy))
		{
			// Truncate from here onwards
			$bolTruncated	= TRUE;
		}
		
		// Set passed value
		$arrParts[$strPart]	= (int)date($strPart, $intTime);
	}
	
	return mktime($arrParts['H'], $arrParts['i'], $arrParts['s'], $arrParts['m'], $arrParts['d'], $arrParts['Y']);
}



//------------------------------------------------------------------------//
// FlexCast()
//------------------------------------------------------------------------//
/**
 * FlexCast()
 *
 * Casts a variable to a type defined by Flex DataType constants
 *
 * Casts a variable to a type defined by Flex DataType constants
 * 
 * @param	mixed	$mixVariable				The variable to cast
 * @param	integer	$intDataType				The Flex DataType constant to cast to
 *
 * @return	mixed								Cast variable
 *
 * @function
 */
function FlexCast($mixVariable, $intDataType)
{
	switch ($intDataType)
	{
		case DATA_TYPE_INTEGER:
			return (int)$mixVariable;
			
		case DATA_TYPE_FLOAT:
			return (float)$mixVariable;
			
		case DATA_TYPE_BOOLEAN:
			return (bool)$mixVariable;
			
		case DATA_TYPE_STRING:
			return (string)$mixVariable;
			
		case DATA_TYPE_SERIALISED:
			return unserialize($mixVariable);
		
		default:
			// If we don't recognise the type, return in its original value
			return $mixVariable;
	}
}

//------------------------------------------------------------------------//
// EscapeXML()
//------------------------------------------------------------------------//
/**
 * EscapeXML()
 *
 * Escapes a string for use in XML
 *
 * Escapes a string for use in XML
 * 
 * @param	string	$strText					The string to escape
 * @param	boolean	$bolAttribute				TRUE	: This string is for use in an Attribute (only escape quotes)
 * 												FALSE	: This string is for general XML use (escape everything)
 *
 * @return	string								Escaped string
 *
 * @function
 */
function EscapeXML($strText, $bolAttribute = FALSE)
{
	if (!$bolAttribute)
	{
		$strText	= str_replace('&', '&amp;', $strText);
		$strText	= str_replace('<', '&lt;', $strText);
		$strText	= str_replace('>', '&gt;', $strText);
	}
	
	$strText	= str_replace('"', '&quot;', $strText);
	$strText	= str_replace("'", '&apos;', $strText);
	
	return $strText;
}

//------------------------------------------------------------------------//
// Encrypt($strKey, $strDecryptedString)
//------------------------------------------------------------------------//
/**
 * Encrypt($strKey, $strDecryptedString)
 *
 * Encrypts a string
 *
 * Encrypts a string
 * 
 * @param	boolean	$strDecryptedString	The string to encrypt
 *
 * @return	string						Encrypted string base 64 encoded
 *
 * @function
 */
function Encrypt($strDecryptedString)
{
	if (!array_key_exists('**arrCustomerConfig', $GLOBALS) || !array_key_exists('Key', $GLOBALS['**arrCustomerConfig']))
	{
		throw new Exception("Encryption key has not been configurred in customer configuration.");
	}
	$strKey = $GLOBALS['**arrCustomerConfig']['Key'];
	if ($strDecryptedString === '' || $strDecryptedString === NULL) return '';
	$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CFB, '');
	$iv = substr(sha1($strKey), 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB));
	$ks = mcrypt_enc_get_key_size($cipher);
	$key = substr($strKey.sha1($strKey), 0, $ks);
	mcrypt_generic_init($cipher, $key, $iv);
	$strEncryptedString = mcrypt_generic($cipher, $strDecryptedString);
	mcrypt_generic_deinit($cipher);
	mcrypt_module_close($cipher);
	return base64_encode($strEncryptedString);
}

//------------------------------------------------------------------------//
// Decrypt($strKey, $strBase64EncodedEncryptedString)
//------------------------------------------------------------------------//
/**
 * Decrypt($strKey, $strBase64EncodedEncryptedString)
 *
 * Decrypts a string
 *
 * Decrypts a string
 * 
 * @param	boolean	$strBase64EncodedEncryptedString	The encrypted string, base 64 encoded
 * 														(as returned by Encrypt() function)
 *
 * @return	string										Decrypted string
 *
 * @function
 */
function Decrypt($strBase64EncodedEncryptedString)
{
	if (!array_key_exists('**arrCustomerConfig', $GLOBALS) || !array_key_exists('Key', $GLOBALS['**arrCustomerConfig']))
	{
		throw new Exception("Encryption key has not been configurred in customer configuration.");
	}
	$strKey = $GLOBALS['**arrCustomerConfig']['Key'];
	if ($strBase64EncodedEncryptedString === '' || $strBase64EncodedEncryptedString === NULL) return '';
	$strEncryptedString = base64_decode($strBase64EncodedEncryptedString);
	$cipher = mcrypt_module_open(MCRYPT_RIJNDAEL_256, '', MCRYPT_MODE_CFB, '');
	$iv = substr(sha1($strKey), 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB));
	$ks = mcrypt_enc_get_key_size($cipher);
	$key = substr($strKey.sha1($strKey), 0, $ks);
	mcrypt_generic_init($cipher, $key, $iv);
	$strDecryptedString = mdecrypt_generic($cipher, $strEncryptedString);
	mcrypt_generic_deinit($cipher);
	mcrypt_module_close($cipher);
	return $strDecryptedString;
}

//------------------------------------------------------------------------//
// Decrypt($strKey, $strBase64EncodedEncryptedString)
//------------------------------------------------------------------------//
/**
 * DecryptAndStripSpaces($strKey, $strBase64EncodedEncryptedString)
 *
 * Decrypts a string and strips spaces from it
 *
 * Decrypts a string and strips spaces from it. Used by the "Credit Card Payments Report"
 * 
 * @param	boolean	$strBase64EncodedEncryptedString	The encrypted string, base 64 encoded
 * 														(as returned by Encrypt() function and 
 * 														stored in the database)
 *
 * @return	string										Decrypted string with spaces removed
 *
 * @function
 */
function DecryptAndStripSpaces($strBase64EncodedEncryptedString)
{
	return str_replace(' ', '', Decrypt($strBase64EncodedEncryptedString));
}

	
//------------------------------------------------------------------------//
// UnpackArchive
//------------------------------------------------------------------------//
/**
 * UnpackArchive()
 *
 * Unpacks an Archive
 *
 * Unpacks an Archive to a given location.  Accepted Force Types are 'zip', 'tar', 'tar.bz2'
 * 
 * @param	string	$strSourcePath						Full path to the Source Archive
 * @param	string	$strDestinationPath		[optional]	Full path to where the Archive should be extracted. (Default: NULL - Current Working Directory)
 * @param	boolean	$bolJunkPaths			[optional]	TRUE: Do not recreate Archive directory structure. (Default: FALSE)
 * @param	string	$strPassword			[optional]	Archive password. (Default: NULL)
 * @param	string	$strType				[optional]	Archive is of this type. (Default: NULL)
 *
 * @return	mixed										Array: full paths to the files extracted; string: Error Message
 *
 * @method
 */
function UnpackArchive($strSourcePath, $strDestinationPath = NULL, $bolJunkPaths = FALSE, $strPassword = NULL, $strType = NULL)
{
	$arrHandledTypes			= Array();
	
	// ZIP types
	$arrHandledTypes['zip']		= 'zip';
	
	// TAR types
	$arrHandledTypes['tar']		= 'tar';
	$arrHandledTypes['tar.bz2']	= 'tar';
	$arrHandledTypes['tbz']		= 'tar';
	$arrHandledTypes['tbz2']	= 'tar';
	$arrHandledTypes['tb2']		= 'tar';
	$arrHandledTypes['tar.gz']	= 'tar';
	$arrHandledTypes['tgz']		= 'tar';
	
	// Source and Destination manipulation
	$strBasename	= basename($strSourcePath);
	$strDirname		= dirname($strSourcePath);
	if (!file_exists($strSourcePath))
	{
		// Source file does not exist
		return "Unable to locate Source file '$strSourcePath'";
	}
	if (!(file_exists($strDestinationPath) && is_dir($strDestinationPath)))
	{
		if (!@mkdir($strDestinationPath, 0644, TRUE))
		{
			// Unable to create the Destination Path
			return "Unable to create Destination path '$strDestinationPath'";
		}
	}
	
	// Get the type
	$strExtension	= '';
	if ($strType === NULL)
	{
		foreach ($arrHandledTypes as $strHandledExtension=>$strBaseType)
		{
			if ($strHandledExtension === strtolower(substr($strBasename, strripos($strBasename, $strHandledExtension))))
			{
				$strType		= $strBaseType;
				$strExtension	= $strHandledExtension;
				break;
			}
		}
	}
	
	// Unpack
	$arrOutput	= Array();
	$intReturn	= NULL;
	$strCommand	= NULL;
	$arrFiles	= Array($strSourcePath);
	switch (strtolower($strType))
	{
		case 'zip':
			$strCommand		= "unzip ";
			$strCommand		.= ($bolJunkPaths) ? '-j ' : '';
			$strCommand		.= ($strPassword !== NULL) ? "-P {$strPassword} " : '';
			$strCommand		.= "$strSourcePath ";
			$strCommand		.= ($strDestinationPath !== NULL) ? "-d $strDestinationPath" : "-d $strDirname";
			
			$strLastLine	= exec($strCommand, $arrOutput, $intReturn);
			
			if ($intReturn > 0)
			{
				// An error occurred
				return "Unable to unzip archive '$strSourcePath'";
			}
			
			// Get list of files extracted
			$arrFiles	= Array();
			foreach ($arrOutput as $strLine)
			{
				if (stripos($strLine, 'Archive: ') === FALSE)
				{
					$arrLine	= explode(': ', $strLine, 2);
					if (is_file($arrLine[1]))
					{
						$arrFiles[]	= $arrLine[1];
					}
				}
			}
			break;
			
		case 'tar':
			$strCommand		= "tar";
			$strCommand		.= ($bolJunkPaths) ? " --transform='s,\/?(\w+\/)*,,x'" : '';
			$strCommand		.= " -xv ";
			$strCommand		.= (in_array(strtolower($strHandledExtension), Array('tar.bz2', 'tbz', 'tbz2', 'tb2'))) ? '--bzip2 ' : '';
			$strCommand		.= (in_array(strtolower($strHandledExtension), Array('tar.gz', 'tgz'))) ? '--gzip ' : '';
			$strCommand		.= "-f $strSourcePath";
			$strCommand		.= ($strDestinationPath !== NULL) ? " -C $strDestinationPath" : '';
			$strCommand		.= ($bolJunkPaths) ? " --show-transformed-names" : '';
			
			$strLastLine	= exec($strCommand, $arrOutput, $intReturn);
			
			if ($intReturn > 0)
			{
				// An error occurred
				return "Unable to untar file '$strSourcePath'";
			}
			
			// Get list of files extracted
			$arrFiles		= $arrOutput;
			foreach ($arrFiles as &$strFile)
			{
				if ($strDestinationPath !== NULL)
				{
					$strFile	= rtrim($strDestinationPath, '/').'/'.$strFile;
				}
				else
				{
					$strFile	= getcwd().'/'.$strFile;
				}
				
				if (!is_file($strFile))
				{
					unset($strFile);
				}
			}
			break;
			
		default:
			return Array('Files' => Array($strSourcePath));
	}
	
	//Debug("Command\t: '$strCommand'");
	//Debug("Last Line\t: '$strLastLine'");
	return Array('Files' => $arrFiles, 'Processed' => TRUE);
}

function BarAccount($intAccountId, $intAccountGroup, $bolAutomatic=FALSE, $invoiceRun=NULL)
{
	// Throw exception if fails

	// Bar the account

	$arrAccountServices = ListServicesAndCarriersForAccount($intAccountId);

	$arrAutomaticallyBarrableCarriers = ListAutomaticallyBarrableCarriers();

	$arrUnbarrableAccountServices = array();
	$arrBarrableAccountServices = array();

	$bolBarred = FALSE;

	$bolBarredFNNs = array();

	foreach($arrAccountServices as $intServiceId => $arrDetails)
	{
		if ($arrDetails['CarrierId'] !== NULL && array_search($arrDetails['CarrierId'], $arrAutomaticallyBarrableCarriers) !== FALSE)
		{
			// Write the record to bar the service
			$arrColumns = array(
				'AccountGroup' 		=> $intAccountGroup,
				'Account'			=> $intAccountId,
				'Service'			=> $intServiceId,
				'FNN'				=> $arrDetails['FNN'],
				'Employee'			=> USER_ID,
				'Carrier'			=> $arrDetails['CarrierId'],
				'Type'				=> PROVISIONING_TYPE_BAR,
				'RequestedOn'		=> new MySQLFunction('NOW()'),
				'AuthorisationDate'	=> new MySQLFunction('NOW()'),
				'Status'			=> REQUEST_STATUS_WAITING,
			);
			$insProvisioningRequest = new StatementInsert('ProvisioningRequest', $arrColumns, FALSE);
			$mxdResult = $insProvisioningRequest->Execute($arrColumns);
			if ($mxdResult === FALSE)
			{
				throw new Exception('Failed to create provisioning request for barring service ' . $arrDetails['FNN'] . '(' . $intServiceId . '): ' . $insProvisioningRequest->Error());
			}

			// Add a note to the service
			$GLOBALS['fwkFramework']->AddNote('Service automatically barred.', SYSTEM_NOTE_TYPE, USER_ID, $intAccountGroup, NULL, $intServiceId);

			$bolBarred = TRUE;
			$bolBarredFNNs[] = $arrDetails['FNN'];
			$arrBarrableAccountServices[$intServiceId] = $arrDetails;
		}
		else
		{
			$arrUnbarrableAccountServices[$intServiceId] = $arrDetails;
		}
	}

	// If automatic, change auto_barring_status for the account
	if ($bolAutomatic && $bolBarred)
	{
		$strReason = 'Automatically barred the following services: ' . implode(', ', $bolBarredFNNs) . '. ';
		ChangeAccountAutomaticBarringStatus($intAccountId, $intAccountGroup, AUTOMATIC_BARRING_STATUS_BARRED, $strReason);
		ChangeAccountAutomaticInvoiceAction($intAccountId, NULL, AUTOMATIC_INVOICE_ACTION_BARRING, $strReason, NULL, $invoiceRun);
	}

	$outcome = array('BARRED' => $arrBarrableAccountServices, 'NOT_BARRED' => $arrUnbarrableAccountServices);

	// Return a list of services that could and could not be barred
	return $outcome;
}

function UnbarAccount($intAccountId, $intAccountGroup, $bolAutomatic=FALSE, $invoiceRun=NULL)
{
	// Throw exception if fails

	// Bar the account

	$arrAccountServices = ListServicesAndCarriersForAccount($intAccountId);

	$arrAutomaticallyUnbarrableCarriers = ListAutomaticallyUnbarrableCarriers();

	$arrNonUnbarrableAccountServices = array();
	$arrUnbarrableAccountServices = array();

	$bolUnbarred = FALSE;
	$bolManualUnbars = FALSE;

	$bolUnbarredFNNs = array();

	foreach($arrAccountServices as $intServiceId => $arrDetails)
	{
		if ($arrDetails['CarrierId'] !== NULL && array_search($arrDetails['CarrierId'], $arrAutomaticallyUnbarrableCarriers) !== FALSE)
		{
			// Write the record to unbar the service
			$arrColumns = array(
				'AccountGroup' 		=> $intAccountGroup,
				'Account'			=> $intAccountId,
				'Service'			=> $intServiceId,
				'FNN'				=> $arrDetails['FNN'],
				'Employee'			=> USER_ID,
				'Carrier'			=> $arrDetails['CarrierId'],
				'Type'				=> PROVISIONING_TYPE_UNBAR,
				'RequestedOn'		=> new MySQLFunction('NOW()'),
				'AuthorisationDate'	=> new MySQLFunction('NOW()'),
				'Status'			=> REQUEST_STATUS_WAITING,
			);
			$insProvisioningRequest = new StatementInsert('ProvisioningRequest', $arrColumns, FALSE);
			$mxdResult = $insProvisioningRequest->Execute($arrColumns);
			if ($mxdResult === FALSE)
			{
				throw new Exception('Failed to create provisioning request for unbarring service ' . $arrDetails['FNN'] . '(' . $intServiceId . '): ' . $insProvisioningRequest->Error());
			}

			// Add a note to the service
			$GLOBALS['fwkFramework']->AddNote('Service automatically unbarred.', SYSTEM_NOTE_TYPE, USER_ID, $intAccountGroup, NULL, $intServiceId);

			$bolUnbarred = TRUE;
			$bolUnbarredFNNs[] = $arrDetails['FNN'];
			$arrUnbarrableAccountServices[$intServiceId] = $arrDetails;
		}
		else
		{
			$arrNonUnbarrableAccountServices[$intServiceId] = $arrDetails;
			$bolManualUnbars = TRUE;
		}
	}

	// If automatic, change auto_barring_status for the account
	// Note: We do this regardless of whether any services were unbarred automatically, manuall or even if there were no services
	if ($bolAutomatic)
	{
		$strReason = 'Automatically unbarred the following services: ' . implode(', ', $bolUnbarredFNNs) . '. ';
		ChangeAccountAutomaticBarringStatus($intAccountId, $intAccountGroup, AUTOMATIC_BARRING_STATUS_UNBARRED, $strReason);
		ChangeAccountAutomaticInvoiceAction($intAccountId, NULL, AUTOMATIC_INVOICE_ACTION_UNBARRING, $strReason, NULL, $invoiceRun);
	}

	$outcome = array('UNBARRED' => $arrUnbarrableAccountServices, 'NOT_UNBARRED' => $arrNonUnbarrableAccountServices);

	// Return a list of services that could and could not be unbarred
	return $outcome;
}

function ChangeAccountAutomaticBarringStatus($intAccount, $intAccountGroup, $intTo, $strReason)
{
	$error = '';

	$strDate = date('Y-m-d H:i:s');
	
	// Need to find out the current status of the account
	$selQuery = new StatementSelect('Account', array('automatic_barring_status' => 'automatic_barring_status'), 'Id=<Id>');
	if (!$outcome = $selQuery->Execute(array('Id' => $intAccount)))
	{
		throw new Exception('Failed to retreive current automatic barring status for account $intAccount. ' .  $qryQuery->Error());
	}
	$arrFrom =  $selQuery->Fetch();
	$intFrom = intval($arrFrom['automatic_barring_status']);

	$qryQuery = new Query();
	$strSQL = 'UPDATE Account SET automatic_barring_status = ' . $intTo . ', automatic_barring_datetime = \'' . $strDate . '\' WHERE Id = ' . $intAccount;
	if (!$outcome = $qryQuery->Execute($strSQL))
	{
		$message = ' Failed to update Account ' . $intAccount . ' automatic_barring_status from ' . $intFrom . ' to ' . $intTo . '. '. $qryQuery->Error();
		throw new Exception($message);
	}

	// and creating a corresponding automatic_barring_status_history entry.
	$qryQuery = new Query();
	$strSQL = 'INSERT INTO automatic_barring_status_history (account, from_status, to_status, reason, change_datetime) ' .
			' VALUES (' .
			$intAccount . ', ' .
			$intFrom . ', ' .
			$intTo .', ' .
			'\'' . $qryQuery->EscapeString($strReason) . '\', ' .
			'\'' . $strDate . '\'' .
			')';
	if (!$outcome = $qryQuery->Execute($strSQL))
	{
		$message = ' Failed to create automatic_barring_status_history entry for ' . $intAccount . ' change from ' . $intFrom . ' to ' . $intTo . '. '. $qryQuery->Error();
		throw new Exception($message);
	}

	// Add a note to the account
	$GLOBALS['fwkFramework']->AddNote($strReason, SYSTEM_NOTE_TYPE, USER_ID, $intAccountGroup, $intAccount);

	return TRUE;
}

function ChangeAccountAutomaticInvoiceAction($intAccount, $intFrom, $intTo, $strReason, $strDateTime=NULL, $mxdInvoiceRun=NULL)
{
	$error = '';

	if ($strDateTime == NULL)
	{
		$strDateTime = date("Y-m-d H:i:s");
	}

	if ($intFrom === NULL)
	{
		// Need to find the current status for the account (do this as part of the other queries)
		$intFrom = "(SELECT last_automatic_invoice_action FROM Account WHERE Id = $intAccount)";
	}

	// If mxdInvoiceRun is onot an int, we should assume that it is the InvoiceRun string value of an InvoiceRun record
	if (!is_int($mxdInvoiceRun))
	{
		$invoiceRunId = "(SELECT Id FROM InvoiceRun WHERE InvoiceRun = '$mxdInvoiceRun')";
	}
	// Else we can assume that an invoice run id has been passed
	else
	{
		$invoiceRunId = $mxdInvoiceRun;
	}

	// Creating an automatic_invoice_action_history entry.
	$qryQuery = new Query();
	$strReason = $qryQuery->EscapeString($strReason);
	$strSQL = "INSERT INTO automatic_invoice_action_history (account, from_action, to_action, reason, change_datetime, invoice_run_id)
				VALUES ($intAccount, $intFrom, $intTo, '$strReason', '$strDateTime', $invoiceRunId)";
//echo "\n\n$strSQL\n\n";
	if (!$outcome = $qryQuery->Execute($strSQL))
	{
		return ' Failed to create automatic_invoice_action_history entry for ' . $intAccount . ' change to ' . $intTo . '. '. $qryQuery->Error();
	}

	$qryQuery = new Query();
	$strSQL = "UPDATE Account SET last_automatic_invoice_action = $intTo, last_automatic_invoice_action_datetime = '$strDateTime' WHERE Id = $intAccount";
	if (!$outcome = $qryQuery->Execute($strSQL))
	{
		return ' Failed to update Account ' . $intAccount . ' last_automatic_invoice_action to ' . $intTo . '. '. $qryQuery->Error();
	}

	return TRUE;
}



function ListServicesAndCarriersForAccount($accountId)
{
	$arrServices = ListAccountServices($accountId);

	// Build an array of service ids
	$arrServiceIds = array();
	$arrServicesById = array();
	foreach($arrServices as $arrService)
	{
		$arrServiceIds[] = $arrService['Id'];
		$arrServicesById[$arrService['Id']] = $arrService;
		$arrServicesById[$arrService['Id']]['CarrierId'] = NULL;
		$arrServicesById[$arrService['Id']]['CarrierName'] = NULL;
	}

	// Retreive the carriers for the services
	$arrCarriersForServices = ListCarriersForServices($arrServiceIds);

	foreach($arrCarriersForServices as $serviceId => $carrierDetails)
	{
		$arrServicesById[$serviceId]['CarrierId'] = $carrierDetails['CarrierId'];
		$arrServicesById[$serviceId]['CarrierName'] = $carrierDetails['CarrierName'];
	}

	return $arrServicesById;
}

function ListAccountServices($accountId)
{
	$strServiceStatuses = implode(', ', array(SERVICE_ACTIVE, SERVICE_DISCONNECTED, SERVICE_ARCHIVED));
	$arrColumns = array('Id' => 'Service.Id', 'FNN' => 'Service.FNN');
	$strTables = "Service
  INNER JOIN (
    SELECT MAX(Service.Id) serviceId
      FROM Service
     WHERE 
     (
       Service.ClosedOn IS NULL
       OR NOW() < Service.ClosedOn
     )
     AND Service.CreatedOn < NOW()
     AND Service.FNN IN (SELECT FNN FROM Service WHERE Account = $accountId)
     GROUP BY Service.FNN
   ) CurrentService
   ON Service.Account = $accountId
   AND Service.Id = CurrentService.serviceId
   AND Service.Status IN ($strServiceStatuses)";

	$strOrderBy = "Service.FNN ASC";

	/*
	// DEBUG: Output the query that gets run
	$select = array();
	foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
	echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\nORDER BY $strOrderBy\n\n";
	//*/


	$selServices = new StatementSelect($strTables, $arrColumns, "", $strOrderBy);
	$mxdReturn = $selServices->Execute();
	if ($mxdReturn === FALSE)
	{
		throw new Exception("Failed to list services for account $accountId: " . $qryQuery->Error());
	}
	return $selServices->FetchAll();
}

/*
 * Returns an array of carrier details indexed by Service Id
 * [ServiceId] = array('ServiceId'=>$serviceId, 'CarrierId'=>$carrierId, 'CarrierName'=>$carrierName);
 */
function ListCarriersForServices($arrServiceIds)
{
	$carriers = array();
	if (count($arrServiceIds))
	{
		// We also want the carriers for these services
		$strServiceIds = implode(', ', $arrServiceIds);
		$arrColumns = array('ServiceId' => 'Services.Id', 'CarrierId' => ' Carrier.Id', 'CarrierName' => 'Carrier.Name');
		$strTables = "
				   (SELECT Service.Id FROM Service WHERE Service.Id IN ($strServiceIds)) Services
				   LEFT JOIN (SELECT MAX(Id) Id, ServiceRatePlan.Service Service
					  FROM ServiceRatePlan 
					 WHERE ServiceRatePlan.Service IN ($strServiceIds)
					   AND ServiceRatePlan.Active = 1
					   AND ServiceRatePlan.StartDateTime < ServiceRatePlan.EndDateTime
					   AND NOW() BETWEEN ServiceRatePlan.StartDateTime AND ServiceRatePlan.EndDateTime
					 GROUP BY ServiceRatePlan.Service
					) ServiceRatePlans
					ON ServiceRatePlans.Service = Services.Id
					LEFT JOIN ServiceRatePlan
					  ON ServiceRatePlan.Id = ServiceRatePlans.Id
					LEFT JOIN RatePlan
					  ON ServiceRatePlan.RatePlan = RatePlan.Id
					LEFT JOIN Carrier
					  ON RatePlan.CarrierPreselection = Carrier.Id
		";

		/*
		// DEBUG: Output the query that gets run
		$select = array();
		foreach($arrColumns as $alias => $column) $select[] = "$column '$alias'";
		echo "\n\nSELECT " . implode(",\n       ", $select) . "\nFROM $strTables\n\n";
		//*/


		$selCarriers = new StatementSelect($strTables, $arrColumns);
		$mxdReturn = $selCarriers->Execute();
		if ($mxdReturn === FALSE)
		{
			throw new Exception("Failed to list carriers for services $strServiceIds: " . $qryQuery->Error());
		}
		$arrCarriers = $selCarriers->FetchAll();
		foreach($arrCarriers as $carrier)
		{
			$carriers[$carrier['ServiceId']] = $carrier;
		}
	}
	return $carriers;
}

function ListAutomaticallyBarrableCarriers()
{
	return ListAutomatableCarriers(PROVISIONING_TYPE_BAR, FALSE, TRUE);
}

function ListAutomaticallyUnbarrableCarriers()
{
	return ListAutomatableCarriers(PROVISIONING_TYPE_UNBAR, FALSE, TRUE);
}

function ListAutomatableCarriers($intProvisioningTypeConstant, $bolInbound=FALSE, $bolOutbound=FALSE)
{
	// The result of this query is highly unlikely to change, so cache the result statically
	static $carriers;
	if (!isset($carriers))
	{
		$carriers = array();
	}
	$key = $intProvisioningTypeConstant . '|' . $bolInbound . '|' . $bolOutbound;
	if (!array_key_exists($key, $carriers))
	{
		$strInbound = $bolInbound ? ' AND provisioning_type.inbound = 1' : '';
		$strOutbound = $bolInbound ? ' AND provisioning_type.outbound = 1' : '';
		$arrColumns = array("Id" => "DISTINCT(Carrier.Id)");
		$strTables = "
			Carrier
			  JOIN carrier_provisioning_support
			    ON carrier_provisioning_support.carrier_id = Carrier.Id
			  JOIN active_status
			    ON carrier_provisioning_support.status_id = active_status.id
			   AND active_status.active = 1
			  JOIN provisioning_type
			    ON provisioning_type.id = $intProvisioningTypeConstant
			   AND provisioning_type.id = carrier_provisioning_support.provisioning_type_id	";
	
		$selCarriers = new StatementSelect($strTables, $arrColumns);
		$mxdResult = $selCarriers->Execute();
		if ($mxdResult === FALSE)
		{
			throw new Exception("Failed to load automatable carriers for provisioning type $intProvisioningTypeConstant (inbound: $bolInbound, outbound: $bolOutbound): " . $qryQuery->Error());
		}
		$arrResults = $selCarriers->FetchAll();
		foreach($arrResults as $arrCarrier)
		{
			$carriers[$key][] = intval($arrCarrier['Id']);
		}
	}
	return $carriers[$key];
}


//------------------------------------------------------------------------//
// FlexModuleActive
//------------------------------------------------------------------------//
/**
 * FlexModuleActive()
 * 
 * Determines whether a Flex Module is activated or not
 * 
 * Determines whether a Flex Module is activated or not.  If the module doesn't exist
 * in the database, it returns NULL (and should be treated as inactive)
 *
 * @param	string	$strModuleName		The name of the Module to Check
 *
 * @return	mixed						TRUE: Active; FALSE: Inactive; NULL: Not present
 * 
 * @function
 * 
 */
function FlexModuleActive($strModuleName)
{
	// Init Statement
	static	$selFlexModule;
	$selFlexModule	= (isset($selFlexModule)) ? $selFlexModule : new StatementSelect("flex_module", "status_id", "name = <Name>");
	
	// Check Module Status
	if ($selFlexModule->Execute(Array('Name' => $strModuleName)) === FALSE)
	{
		// DB Error
		throw new Exception("DB ERROR: ".$selFlexModule->Error());
	}
	elseif ($arrModule = $selFlexModule->Fetch())
	{
		// Module Exists - is it active?
		if ($arrModule['status_id'] === ACTIVE_STATUS_ACTIVE)
		{
			// Active
			return TRUE;
		}
		else
		{
			// Inactive
			return FALSE;
		}
	}
	else
	{
		// Module doesn't exist
		return NULL;
	}
}

?>
