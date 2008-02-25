<?php

// Include Framework
require_once("../../flex.require.php");

// Include Config to Run
if (!$argv[1])
{
	CliEcho("Please specify a config file");
	die;
}
if (!include_once($argv[1]))
{
	CliEcho("Could not find config file '{$argv[1]}'!");
	die;
}

$intStartTime	= time();
CliEcho("Starting Multipart Script '{$argv[1]}' @ ".date("Y-m-d H:i:s", $intStartTime));
CliEcho('');
// Run Scripts
foreach ($arrConfig as $strName=>$arrProperties)
{
	CliEcho("Starting SubScript: $strName @ ".date("Y-m-d H:i:s"));
	
	$strWorkingDirectory	= getcwd();
	chdir($arrProperties['WorkingDirectory']);
	$ptrProcess	= popen($arrProperties['Command'], 'r');
	$arrBlank	= Array();
	stream_set_blocking($ptrProcess, 0);
	while (!feof($ptrProcess))
	{
		$arrProcess	= Array($ptrProcess);
		if (stream_select($arrProcess, $arrBlank, $arrBlank, 0, 500000))
		{
			// Check for output every 0.5s
			CliEcho(stream_get_contents($ptrProcess), FALSE);
		}
	}
	pclose($ptrProcess);
	chdir($strWorkingDirectory);
	
	CliEcho("Finished SubScript: $strName @ ".date("Y-m-d H:i:s"));
}

$intEndTime		= time();
CliEcho("\nFinished Multipart Script '{$argv[1]}' @ ".date("Y-m-d H:i:s", $intEndTime)." (".($intEndTime-$intStartTime)."s)");


?>