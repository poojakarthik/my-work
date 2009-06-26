<?php

// Load Framework
require_once(dirname(__FILE__).'/../../lib/classes/Flex.php');
Flex::load();

Log::getLog()->log("\n\n");

// Wait a while
$intStartTime	= time();
$intTime		= $intStartTime;
$intMaxSeconds	= 60;
while ($intTime < ($intStartTime + $intMaxSeconds))
{
	$intTime	= time();
	Log::getLog()->log("\033[1A\033[K\033[1A");
	Log::getLog()->log((($intStartTime + $intMaxSeconds) - $intTime)." seconds remaining");
}

Log::getLog()->log("\n\n");
exit(0);

?>