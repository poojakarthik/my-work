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
	echo "\\033[K";
	echo (($intStartTime + $intMaxSeconds) - $intTime)." seconds remaining";
}

echo "\n\n";
exit(0);

?>