<?php

// Load Framework
require_once(dirname(__FILE__).'/../../lib/classes/Flex.php');
Flex::load();

Log::getLog()->log("\n\n");

// Wait a while
$iStartTime		= time();
$iTime			= $iStartTime;
$iMaxSeconds	= 60;
$fLastRefresh	= 0;
$fRefreshRate	= 1;
while ($iTime < ($iStartTime + $iMaxSeconds))
{
	$intTime	= time();
	if (abs($fLastRefresh - $intTime) >= $fRefreshRate)
	{
		$fLastRefresh	= $intTime;
		Log::getLog()->log("\033[1A\033[K\033[1A");
		Log::getLog()->log((($iStartTime + $iMaxSeconds) - $iTime)." seconds remaining");
	}
}

Log::getLog()->log("\n\n");
exit(0);

?>