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

$arrCDR						= Array();
$arrCDR['Status']				= CDR_READY;
$updCDRs	= new StatementUpdate("CDR", "FNN = <FNN>", $arrCDR);

$selFNNDisconnectedInstances	= new StatementSelect("Service", "Id", "Status = ".SERVICE_DISCONNECTED." AND FNN = <FNN>");
$intFNNCount	= 0;
$intCDRCount	= 0;
while ($arrRow	= fgetcsv($resFile))
{
	$arrFNN	= Array('FNN' => str_pad(trim($arrRow[0]), 10, 0, STR_PAD_LEFT));
	CliEcho(" * Updating {$arrFNN['FNN']}...\t\t\t", FALSE);
	
	// Find Disconnected Instances of this FNNs
	if ($selFNNDisconnectedInstances->Execute($arrFNN))
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
		
		// Set all CDRs for this FNN to be ReNormalised
		$intCDRsUpdated	= $updCDRs->Execute($arrCDR, $arrFNN);
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
	$intFNNCount++;
}

CliEcho("\nUpdated a total of {$intCDRCount} CDRs for {$intFNNCount} FNNs\n");
?>