<?php

require_once("../../flex.require.php");

$arrProcessType		= Array();
$arrDependencies	= Array();
//----------------------------------------------------------------------------//
// SETUP
//----------------------------------------------------------------------------//

// Friendly Name (no spaces)
$arrProcessType['Name']				= "Billing";

// Command to execute
$arrProcessType['Command']			= "php process_billing.php";

// Command's Working Directory
$arrProcessType['WorkingDirectory']	= FLEX_BASE_PATH."cli/process/";

// Whether to save the process's Output
$arrProcessType['Debug']			= TRUE;
//$arrProcessType['Debug']			= FALSE;

// Dependencies
// ProcessName	: The Friendly Name of the Process which blocks this one
// WaitMode		: 0 - Do not wait; -1 - Wait indefinitely; 1+ Wait for X seconds
// AlertEmail	: (NULL) Email address to alert if this process fails

$arrDependencies['Payments']			['WaitMode']	= -1;
$arrDependencies['Payments']			['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['Collection']			['WaitMode']	= -1;
$arrDependencies['Collection']			['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['ProvisioningExport']	['WaitMode']	= -1;
$arrDependencies['ProvisioningExport']	['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['ProvisioningImport']	['WaitMode']	= -1;
$arrDependencies['ProvisioningImport']	['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['Normalise10000']		['WaitMode']	= -1;
$arrDependencies['Normalise10000']		['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['ImportSingle']		['WaitMode']	= -1;
$arrDependencies['ImportSingle']		['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['FullNormalisation']	['WaitMode']	= -1;
$arrDependencies['FullNormalisation']	['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['RateNew']				['WaitMode']	= -1;
$arrDependencies['RateNew']				['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['RateAll']				['WaitMode']	= -1;
$arrDependencies['RateAll']				['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['RecurringCharges']	['WaitMode']	= -1;
$arrDependencies['RecurringCharges']	['AlertEmail']	= 'rdavis@ybs.net.au';

$arrDependencies['PayNegativeBalances']	['WaitMode']	= -1;
$arrDependencies['PayNegativeBalances']	['AlertEmail']	= 'rdavis@ybs.net.au';



//----------------------------------------------------------------------------//
// CODE
//----------------------------------------------------------------------------//
$selProcessType	= new StatementSelect("ProcessType", "Id", "Name = <Name>");
$insProcessType	= new StatementInsert("ProcessType");
$insPriority	= new StatementInsert("ProcessPriority");

// Add ProcessType
CliEcho("Adding ProcessType '{$arrProcessType['Name']}'...\t\t\t", FALSE);
if ($selProcessType->Execute(Array('Name' => $arrProcessType['Name'])))
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
		if (!$selProcessType->Execute(Array('Name' => $strDependency)))
		{
			CliEcho("[ FAILED ]");
			CliEcho("\t\t- Could not find ProcessType '$strDependency'");
			continue;
		}
		
		$arrRunning	= $selProcessType->Fetch();
		
		$arrPriority = Array();
		$arrPriority['ProcessWaiting']	= $intProcessType;
		$arrPriority['ProcessRunning']	= $arrRunning['Id'];
		$arrPriority['WaitMode']		= $arrDependency['WaitMode'];
		$arrPriority['AlertEmail']		= $arrDependency['AlertEmail'];
		if ($insPriority->Execute($arrPriority) === FALSE)
		{
			CliEcho("[ FAILED ]");
			CliEcho("\t\t- Could not link to ProcessType '$strDependency'");
			continue;
		}
		CliEcho("[   OK   ]");
	}
}

?>