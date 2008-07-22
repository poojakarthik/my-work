<?php

require_once("../../../../../flex.require.php");

// CDR
$arrStructure[FILE_IMPORT_CDR_OPTUS_STANDARD]				['Regex']			= "/^tap_isk4_\d{14}_\d{8}_\d{6}_a_s\.dat$/i";
$arrStructure[FILE_IMPORT_CDR_OPTUS_STANDARD]				['Uniqueness']		= "FileName = <FileName> AND SHA1 = <SHA1>";
$arrStructure[FILE_IMPORT_CDR_OPTUS_STANDARD]				['DownloadUnique']	= TRUE;
$arrStructure[FILE_IMPORT_CDR_OPTUS_STANDARD]				['Paths']			[]	= '/home/telcoblue/Incoming/cdr/iseek/';

// Config
$arrModuleConfig['FileDefine']		['Value']		= $arrStructure;

// Output
$strOutputFile	= basename(__FILE__, '.cfg.php').'.serialised';
@unlink($strOutputFile);

if (file_put_contents($strOutputFile, serialize($arrStructure)))
{
	echo "\nSerialised Data successfully dumped to '$strOutputFile'.\n\n";
}
else
{
	echo "\nUnable to dump serialised data to '$strOutputFile'.\n\n";
}

?>