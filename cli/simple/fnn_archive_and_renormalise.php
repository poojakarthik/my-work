<?php

// Framework
require_once("../../flex.require.php");

// Load CSV File of FNNs
$strCSVFile	= "/home/rdavis/fnns_to_archive_and_renormalise.csv";
$resFile	= fopen($strCSVFile, 'r');

CliEcho("\n PARSING: '{$strCSVFile}'\n");

// Parse file for FNNs
$arrCols						= Array();
$arrCols['Status']				= SERVICE_ARCHIVED;
$ubiService						= new StatementUpdateById("Service", $arrCols);

$arrCDR							= Array();
$arrCDR['Status']				= CDR_NORMALISED;
$arrCDR['Service']				= NULL;
$arrCDR['Account']				= NULL;
$arrCDR['AccountGroup']			= NULL;
$updCDRs	= new StatementUpdate("CDR", "FNN = <FNN>", $arrCDR);

$selFNNDisconnectedInstances	= new StatementSelect("Service", "Id", "Status = ".SERVICE_DISCONNECTED." AND FNN = <FNN> AND Account = <Account>");
$intFNNCount	= 0;
$intCDRCount	= 0;
while ($arrRow	= fgetcsv($resFile))
{
	$arrFNN		= Array('FNN' => str_pad(trim($arrRow[0]), 10, 0, STR_PAD_LEFT));
	CliEcho(" * Updating {$arrFNN['FNN']}...\t\t\t", FALSE);
	
	// Find Disconnected Instances of this FNNs
	$arrOwner			= FindFNNOwner($arrFNN['FNN'], date('Y-m-d'));
	$arrOwner['FNN']	= $arrFNN['FNN'];
	if ($selFNNDisconnectedInstances->Execute($arrOwner))
	{
		while ($arrService = $selFNNDisconnectedInstances->Fetch())
		{
			// Archive this Service
			$arrService['Status']	= SERVICE_ARCHIVED;
			if (!$ubiService->Execute($arrService))
			{
				// Error
				CliEcho("[ FAILED ]\n\t -- Unable to update Service {$arrService['Id']}: ".$ubiService->Error());
				die;
			}
		}
		
		// Re-own all of these CDRs to be on the current Service, and re-rate
		$arrCDR	= $arrOwner;
		if (is_array($arrCDR))
		{
			$arrCDR['Status']	= CDR_NORMALISED;
			$intCDRsUpdated	= $updCDRs->Execute($arrCDR, Array('FNN' => $arrFNN['FNN'], 'Account' => $arrCDR['Account']));
			//$intCDRsUpdated	= TRUE;
			if ($intCDRsUpdated === FALSE)
			{
				// Error
				CliEcho("[ FAILED ]\n\t -- Unable to update Service {$arrService['Id']}: ".$ubiService->Error());
				die;
			}
			else
			{
				CliEcho("{$intCDRsUpdated} CDRs Updated");
				$intCDRCount	+= $intCDRsUpdated;
			}
		}
		else
		{
			CliEcho("No current owner for {$arrFNN['FNN']}");
		}
	}
	else
	{
		CliEcho("[  SKIP  ]\n\t -- No Disconnected Services found for {$arrFNN['FNN']}: ".$ubiService->Error());
	}
	$intFNNCount++;
}

CliEcho("\nUpdated a total of {$intCDRCount} CDRs for {$intFNNCount} FNNs\n");
?>