<?php

require_once("../../flex.require.php");

$arrProcessTypes		= Array();
$arrDependencies	= Array();
//----------------------------------------------------------------------------//
// SETUP
//----------------------------------------------------------------------------//

// Friendly Name (no spaces)
$arrProcessTypes[]	= "Payments";
$arrProcessTypes[]	= "Collection";
$arrProcessTypes[]	= "ProvisioningExport";
$arrProcessTypes[]	= "ProvisioningImport";
$arrProcessTypes[]	= "DataReports";
$arrProcessTypes[]	= "Normalise10000";
$arrProcessTypes[]	= "ImportSingle";
$arrProcessTypes[]	= "FullNormalisation";
$arrProcessTypes[]	= "RateNew";
$arrProcessTypes[]	= "RateAll";
$arrProcessTypes[]	= "RecurringCharges";
$arrProcessTypes[]	= "PayNegativeBalances";

// Dependencies
// ProcessName	: The Friendly Name of the Process which blocks this one
// WaitMode		: 0 - Do not wait; -1 - Wait indefinitely; 1+ Wait for X seconds
// AlertEmail	: (NULL) Email address to alert if this process fails

$arrDependencies['Billing']			['WaitMode']	= 0;
$arrDependencies['Billing']			['AlertEmail']	= 'rdavis@ybs.net.au';



//----------------------------------------------------------------------------//
// CODE
//----------------------------------------------------------------------------//
$selProcessType	= new StatementSelect("ProcessType", "*", "Name = <Name>");
$insProcessType	= new StatementInsert("ProcessType");
$insPriority	= new StatementInsert("ProcessPriority");

foreach($arrProcessTypes as $strName)
{
	// Find ProcessType
	CliEcho("Finding ProcessType '$strName'...\t\t\t", FALSE);
	if (!$selProcessType->Execute(Array('Name' => $strName)))
	{
		// Name doesn't exists
		CliEcho("[ FAILED ]");
		CliEcho("\t- '$strName' doesn't exist!");
		die;
	}
	CliEcho("[   OK   ]\n");
	
	$arrProcessType	= $selProcessType->Fetch();
	
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
			$arrPriority['ProcessWaiting']	= $arrProcessType['Id'];
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
}
?>
