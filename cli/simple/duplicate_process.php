<?php

// Load Framework
require_once(dirname(__FILE__).'/../../lib/classes/Flex.php');
Flex::load();

echo "\n\n";

// Wait a while
$intStartTime	= time();
$intTime		= $intStartTime;
$intMaxSeconds	= 60;
while ($intTime < ($intStartTime + $intMaxSeconds))
{
	$intTime	= time();
	Log::getLog()->log("\033[K\033[2A");
	Log::getLog()->log((($intStartTime + $intMaxSeconds) - $intTime)." seconds remaining");
}

echo "\n\n";
exit(0);

?>