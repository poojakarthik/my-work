<?php

// Framework
require_once("../../flex.require.php");

// Conversion Array
//					New Status				Old Status
$arrConvertStatus	= Array();
$arrConvertStatus[FILE_IMPORTED]	[]	= PROVFILE_COMPLETE;
$arrConvertStatus[FILE_IMPORTED]	[]	= CDRFILE_IMPORTED;
$arrConvertStatus[FILE_NORMALISED]	[]	= CDRFILE_NORMALISED;

// Statements
$arrCols				= Array();
$arrCols['Status']		= NULL;
$updFileImportStatus	= new StatementUpdate("FileImport", "Status = <OldStatus>", $arrCols);

// Convert each status
foreach ($arrConvertStatus as $intNewStatus=>$arrOldStatuses)
{
	foreach ($arrOldStatuses as $intOldStatus)
	{
		if ($updFileImportStatus->Execute(Array('NewStatus' => $intNewStatus), Array('OldStatus' => $intOldStatus)) === FALSE)
		{
			throw new Exception($updFileImportStatus->Error());
		}
		else
		{
			
		}
	}
}
?>