<?php

// require stuff
require_once('../../flex.require.php');
require_once('require.php');

echo "<pre>";

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
	if ($arrNormalisationModule[$arrCDR['FileType']])
	{
		// normalise CDR
		$mixReturn = $arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->Normalise($arrCDR);
		
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
