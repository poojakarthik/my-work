<?php

// require stuff
require_once("../../lib/classes/Flex.php");
Flex::load();

require_once('require.php');

echo "<pre>";

$qryQuery	= new Query();

// show cdr
$intCDR = (int)$_REQUEST['id'];
if ($argc > 1)
{
	$intCDR	= (int)$argv[1];
}
if ($intCDR)
{
	// Create an instance of each Normalisation module
	CliEcho(" * NORMALISATION MODULES");
 	$selCarrierModules	= new StatementSelect("CarrierModule", "*", "Type = <Type> AND Active = 1");
	$selCarrierModules->Execute(Array('Type' => MODULE_TYPE_NORMALISATION_CDR));
	while ($arrModule = $selCarrierModules->Fetch())
	{
		$arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']]	= new $arrModule['Module']($arrModule['Carrier']);
		CliEcho("\t + ".GetConstantDescription($arrModule['Carrier'], 'Carrier')." : ".$arrNormalisationModule[$arrModule['Carrier']][$arrModule['FileType']]->strDescription);
	}
	CliEcho();
	
	// get CDR
	$selCDR = new StatementSelect("CDR JOIN FileImport ON CDR.File = FileImport.Id", "CDR.*, FileImport.FileType AS FileType", "CDR.Id = <Id>");
	if (!$selCDR->Execute(Array('Id' => $intCDR)))
	{
		echo "Invalid CDR record requested.  Please double-check the Id ($intCDR).\n";
		die;
	}
	$arrCDR = $selCDR->Fetch();
	
	// Check for a Normalisation Module
	if ($arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']])
	{
		// normalise CDR
		$arrCDR	= $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->Normalise($arrCDR);

		// Check for duplicates
		$arrMatchCDR	= Array();
		foreach ($arrCDR as $strField=>$mixValue)
		{
			if ($mixValue === NULL)
			{
				$mixValue	= 'NULL';
			}
			elseif (is_string($mixValue))
			{
				$mixValue	= "'".str_replace("'", '\\\'', $mixValue)."'";
			}
			$arrMatchCDR[$strField]	= $mixValue;
		}
		$strFindDuplicateSQL	= "SELECT Id, CASE WHEN CarrierRef <=> {$arrMatchCDR['CarrierRef']} THEN ".CDR_DUPLICATE." ELSE ".CDR_RECHARGE." END AS Status 
									FROM CDR 
									WHERE Id != {$arrMatchCDR['Id']} AND 
									FNN = {$arrMatchCDR['FNN']} AND 
									Source <=> {$arrMatchCDR['Source']} AND 
									Destination <=> {$arrMatchCDR['Destination']} AND 
									StartDatetime <=> {$arrMatchCDR['StartDatetime']} AND 
									EndDatetime <=> {$arrMatchCDR['EndDatetime']} AND 
									Units = {$arrMatchCDR['Units']} AND 
									Cost = {$arrMatchCDR['Cost']} AND 
									RecordType = {$arrMatchCDR['RecordType']} AND 
									RecordType NOT IN (10, 15, 33, 21) AND 
									Credit = {$arrMatchCDR['Credit']} AND 
									Description <=> {$arrMatchCDR['Description']} AND 
									Status NOT IN (".CDR_DUPLICATE.", ".CDR_RECHARGE.")
									ORDER BY Id DESC
									LIMIT 1";
		$mixResult = $qryQuery->Execute($strFindDuplicateSQL);
		if ($mixResult === FALSE)
		{
			throw new Exception($qryQuery->Error()."\n\n{$strFindDuplicateSQL}");
		}
		elseif ($arrDuplicateCDR = $mixResult->fetch_assoc())
		{
			$strMatchString			= GetConstantDescription($arrDuplicateCDR['Status'], 'CDR');
			CliEcho("!!! CDR #{$arrCDR['Id']} is a {$strMatchString} of #{$arrDuplicateCDR['Id']}");
			$arrCDR['Status']		= $arrDuplicateCDR['Status'];
		}
		
		// debug CDR
		$arrDebugCDR = $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->DebugCDR();
	}
	else
	{
		echo "No Normalisation Module found for this CDR.\n";
		die;
	}
	
	// display CDR
	Debug($arrDebugCDR);
}
else
{
	echo "No CDR record requested.\n";
}
die;

?>
