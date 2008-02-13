<?php

require_once("../../flex.require.php");

$arrProcessType		= Array();
$arrDependencies	= Array();
//----------------------------------------------------------------------------//
// SETUP
//----------------------------------------------------------------------------//

// Friendly Name (no spaces)
$arrProcessType['Name']				= "Test1";

// Command to execute
$arrProcessType['Command']			= "php test_loop";

// Command's Working Directory
$arrProcessType['WorkingDirectory']	= "/data/www/rich.yellowbilling.com.au/cli/process/";

// Whether to save the process's Output
$arrProcessType['Debug']			= TRUE;
//$arrProcessType['Debug']			= FALSE;

// Dependencies
// ProcessName	: The Friendly Name of the Process which blocks this one
// WaitMode		: 0 - Do not wait; -1 - Wait indefinitely; 1+ Wait for X seconds
// AlertEmail	: (NULL) Email address to alert if this process fails

//$arrDependencies['Test']			['WaitMode']
//$arrDependencies['Test']			['AlertEmail']


//----------------------------------------------------------------------------//
// CODE
//----------------------------------------------------------------------------//
$selProcessType	= new StatementSelect("ProcessType", "Id", "Name = <Name>");
$insProcessType	= new StatementInsert("ProcessType");
$insPriority	= new StatementInsert("ProcessPriority");

// Add ProcessType
CliEcho("Adding ProcessType '{$arrProcessType['Name']}'...\t\t\t", FALSE);
if ($selProcessType->Execute(Array('Name' => $strDependency)))
{
	// Name already exists
	CliEcho("[ FAILED ]");
	CliEcho("\t- '{$arrProcessType['Name']}' already exists!");
	die;
}

if (($intProcessType = $insProcessType->Execute($arrProcessType)) === FALSE)
{
	CliEcho("[ FAILED ]");
	CliEcho("\t- ".$insProcessType->Error());
	die;
}
CliEcho("[   OK   ]\n");

// Add Dependencies
if (count($arrDependencies))
{
	CliEcho("Adding Dependencies...");
	foreach ($arrDependencies as $strDependency=>$arrDependency)
	{
		CliEcho("\t Linking to '$strDependency'...\t", FALSE);
		if (!$selDependency->Execute(Array('Name' => $strDependency)))
		{
			CliEcho("[ FAILED ]");
			CliEcho("\t\t- Could not link to ProcessType '$strDependency'");
			continue;
		}
		CliEcho("[   OK   ]");
	}
}

?>