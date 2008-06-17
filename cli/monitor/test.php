#!/usr/bin/php
<?php
require_once('application_loader.php');

$arrStatus = $appMonitor->CountCDRStatus();
if (is_array($arrStatus))
{
	foreach($arrStatus AS $intStatus=>$intCount)
	{
		$strStatus 	= GetConstantDescription($intStatus, 'CDR');
		echo "$intStatus : $strStatus : $intCount\n";
	}
}
else
{
	echo "NO CDRs FOUND\n";
}
?>
