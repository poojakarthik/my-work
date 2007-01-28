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
	// CleanDir
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
// Debug
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
			echo "\n<pre>\n";
			print_r($mixOutput);
			echo "\n</pre>\n";
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
// Backtrace
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
// TruncateName
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
// ReplaceAliases()
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
// GetCarrierName()
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
// isValidFNN()
//------------------------------------------------------------------------//
/**
 * isValidFNN()
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
function isValidFNN ($strFNN)
{
	return preg_match ("/^0\d{9}[i]?|13\d{4}|1[89]00\d{6}$/", $strFNN);
}

//------------------------------------------------------------------------//
// CleanFNN()
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
// ServiceType()
//------------------------------------------------------------------------//
/**
 * ServiceType()
 * 
 * Find the Service Type of an FNN
 * 
 * Find the Service Type of an FNN
 * 
 * @param	string		$strFNN				The FNN number to Clean
 *
 * @return	mixed					int		Service Type Constant
 *									FALSE	Service Type not found
 * 
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
// GetConstantName()
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
// GetConstantDescription()
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
// EvalReturn()
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

/**
 * HasPermission()
 * 
 * Check if a user has a specified permission
 * 
 * Check if a user has a specified permission
 * 
 * @param	int		$intUser			Current Permissions of the user
 * @param	int		$intPermission		Permissions to be checked for
 *
 * @return	bool						TRUE if the user has the permission
 * 
 * @method
 */
function HasPermission($intUser, $intPermission)
{
	// check for the permission (Bitwise OR)
	if ((int)$intUser && (int)$intUser == ((int)$intUser | (int)$intPermission))
	{
		// return TRUE
		return TRUE;
	}
	// return FALSE
	return FALSE;
}

// -------------------------------------------------------------------------- //
// PDF FUNCTIONS
// -------------------------------------------------------------------------- //

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
			$arrReturn[basename ($strYear)][basename ($strMonth)]	= basename($arrInvoices[0]);
		}
	}
	
	return $arrReturn;
}

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


// echo out a line
function EchoLine($strText)
{
	echo $strText;
	if (substr(-1, 1) != "\n")
	{
		echo "\n";
	}
}


//  LUHN formula
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

//  LUHN formula
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

// Check the expiry date of a credit card
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
?>
