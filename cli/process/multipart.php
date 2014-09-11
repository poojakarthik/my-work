<?php

// Include Framework
require_once("../../flex.require.php");

// Include Config to Run
if (!$argv[1])
{
	CliEcho("Please specify a config file");
	exit(1);
}
if (!include_once($argv[1]))
{
	CliEcho("Could not find config file '{$argv[1]}'!");
	exit(1);
}

// Check for Commandline Options specified as '--CommandLineOption=Value'
$arrCommandLineOptions	= Array();
foreach ($argv as $intIndex=>$strOption)
{
	if ($intIndex > 1 && substr($strOption, 0, 2) === '--')
	{
		// This is a valid option, so parse
		$arrOption	= explode('=', ltrim($strOption, '-'));
		$strAlias	= $arrOption[0];
		array_shift($arrOption);
		$arrCommandLineOptions[$strAlias]	= implode('=', $arrOption);
	}
}

// Make sure we've satisfied all of our required parameters
$arrMissing	= Array();
foreach ($arrConfig as $strName=>$arrProperties)
{
	// Get the list of parameters for this script
	$arrAliases	= Array();
	if (preg_match_all("/<([\d\w]+)>/misU", $arrProperties['Command'], $arrAliases, PREG_SET_ORDER))
	{
		// Ensure this parameter has been provided
		foreach ($arrAliases as $arrAlias)
		{
			if (!array_key_exists($arrAlias[1], $arrCommandLineOptions))
			{
				// This parameter is missing
				$arrMissing[]	= $arrAlias[1];
			}
		}
	}
}

if (count($arrMissing))
{
	CliEcho("Unable to execute, as the following parameters are missing:\n");
	foreach ($arrMissing as $strParameter)
	{
		CliEcho("\t * '$strParameter'");
	}
	CliEcho();
	exit(1);
}

// Init Log File
$strLogDir		= FILES_BASE_PATH."logs/multipart/";
$strFileName	= basename(trim($argv[1]), '.cfg.php')."_".date("Ymdhis");
@mkdir($strLogDir, 0777, TRUE);
$resLogFile		= @fopen($strLogDir.$strFileName, 'w');

// Run Scripts
$intStartTime	= time();
CliLog($resLogFile, "Starting Multipart Script '{$argv[1]}' @ ".date("Y-m-d H:i:s", $intStartTime));
CliLog($resLogFile);
foreach ($arrConfig as $strName=>$arrProperties)
{
	CliLog($resLogFile, "Starting SubScript: $strName @ ".date("Y-m-d H:i:s"));
	
	// Pass through any commandline options
	$strCommand	= $arrProperties['Command'];
	foreach ($arrCommandLineOptions as $strAlias=>$strValue)
	{
		$strCommand	= str_replace("<$strAlias>", $strValue, $strCommand);
	}
	
	// Execute Child Script
	$strWorkingDirectory	= getcwd();
	chdir($arrProperties['Directory']);
	$ptrProcess	= popen($strCommand, 'r');
	$arrBlank	= Array();
	stream_set_blocking($ptrProcess, 0);
	while (!feof($ptrProcess))
	{
		$arrProcess	= Array($ptrProcess);
		if (stream_select($arrProcess, $arrBlank, $arrBlank, 0, 500000))
		{
			// Check for output every 0.5s
			CliLog($resLogFile, stream_get_contents($ptrProcess), FALSE);
		}
	}
	$intReturnCode = pclose($ptrProcess);
	
	chdir($strWorkingDirectory);
	
	CliLog($resLogFile, "Finished SubScript: $strName @ ".date("Y-m-d H:i:s"));
	
	if ($intReturnCode > 0)
	{
		// Child Process returned an error code
		if ($arrProperties['ChildDie'])
		{
			// This child has died, so stop the whole script, and pass the error code on
			CliLog($resLogFile, "\n\nERROR: Child Script '$strName' died with error code '$intReturnCode'\n\n");
			exit($intReturnCode);
		}
	}
}

$intEndTime		= time();
CliLog($resLogFile, "\nFinished Multipart Script '{$argv[1]}' @ ".date("Y-m-d H:i:s", $intEndTime)." (".($intEndTime-$intStartTime)."s)");
exit(0);

// CliLog
function CliLog($resLogFile, $strMessage = '', $bolNewLine = TRUE)
{
	CliEcho($strMessage, $bolNewLine);
	
	if ($bolNewLine)
	{
		$strMessage	.= "\n";
	}
	return fwrite($resLogFile, $strMessage);
}
?>