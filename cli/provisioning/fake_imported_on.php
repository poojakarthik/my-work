<?php

require_once("../../flex.require.php");


$selResponses = new StatementSelect("ProvisioningResponse PR JOIN FileImport FI ON PR.FileImport = FI.Id",
									"PR.*, FI.FileName, FI.FileType");

$arrCols = Array();
$arrCols['ImportedOn']	= NULL;
$ubiResponse = new StatementUpdateById("ProvisioningResponse", $arrCols);


// Get Responses
$selResponses->Execute();
while ($arrResponse = $selResponses->Fetch())
{
	// Get File Date
	$strDate = NULL;
	switch ($arrResponse['FileType'])
	{
		case PRV_UNITEL_DAILY_STATUS_RPT:
			$strDate = substr($arrResponse['FileName'], 7, 4) . "-" . substr($arrResponse['FileName'], 11, 2) . "-" . substr($arrResponse['FileName'], 13, 2);
			break;
		
		default:
			Debug("Can't parse '{$arrResponse['FileName']}'!");
	}
	
	// Update ImportedOn
	if ($strDate)
	{
		$arrImportedOn = explode(' ', $arrResponse['ImportedOn']);
		$arrResponse['ImportedOn'] = $strDate . " " . $arrImportedOn[1];
		$ubiResponse->Execute($arrResponse);
	}
}


?>