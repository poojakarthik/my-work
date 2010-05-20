<?php

// require stuff
require_once('../../flex.require.php');
require_once('../../lib/classes/Flex.php');
Flex::load();

$arrConfig	= LoadApplication();
$appNormalisation	= new ApplicationNormalise($arrConfig);

// show cdr
$intCDR = (int)$_REQUEST['id'];
if ($argc > 1)
{
	$intCDR	= (int)$argv[1];
}
if ($intCDR)
{
	// get CDR
	$selCDR = new StatementSelect("CDR JOIN FileImport ON CDR.File = FileImport.Id", "CDR.*, FileImport.FileType AS FileType", "CDR.Id = <Id>");
	if (!$selCDR->Execute(Array('Id' => $intCDR)))
	{
		CliEcho("Invalid CDR record requested.  Please double-check the Id ($intCDR).\n");
		die;
	}
	$arrCDR = $selCDR->Fetch();
	
	// Check for a Normalisation Module
	if ($appNormalisation->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']])
	{
		// normalise CDR
		$mixReturn		= $appNormalisation->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->Normalise($arrCDR);
		
		// debug CDR
		$arrDebugCDR	= $appNormalisation->_arrNormalisationModule[$arrCDR['Carrier']][$arrCDR['FileType']]->DebugCDR();
	}
	else
	{
		CliEcho("No Normalisation Module found for this CDR.\n");
		die;
	}
	
	// display CDR
	Debug($arrDebugCDR);
}
else
{
	CliEcho("No CDR record requested.\n");
}
die;

?>
