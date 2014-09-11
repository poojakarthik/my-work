
<?php
require_once("../../flex.require.php");

$arrFiles = Array('surname.csv', 'male.csv', 'female.csv');

foreach ($arrFiles as $strFile)
{
	// Open file
	$ptrFile = fopen($strFile, 'r');
	
	// Read file
	$arrData = Array();	
	while (!feof($ptrFile))
	{
		$strLine	= fgets($ptrFile);
		$strLine	= substr($strLine, 0, stripos($strLine, ' '));
		$arrData[]	= trim($strLine);
	}
	
	// Reopen file for writing
	fclose($ptrFile);
	unlink($strFile);
	$ptrFile = fopen($strFile, 'w');
	fwrite($ptrFile, implode("\n", $arrData));
	fclose($ptrFile);
	chmod($strFile, 0777);
}
?>