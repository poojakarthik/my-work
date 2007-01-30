#!/usr/bin/php
<?php
require_once('application_loader.php');

$arrStatus = $appMonitor->GetStatusCountCDR();
if (is_array($arrStatus))
{
	foreach($arrStatus AS $intStatus=>$intCount)
	{
		$strStatus 	= GetConstantName($intStatus, 'CDR');
		echo "$intStatus : $strStatus : $intCount\n";
	}
}
else
{
	echo "NO CDRs FOUND\n";
}
?>
