<?php
require_once("../../flex.require.php");

$intTotalLoops	= ($argv[1]) ? (int)$argv[1] : 10;

CliEcho("Starting! (PID: ".getmypid().")");
$intLoop	= 0;
while ($intLoop < $intTotalLoops)
{
	sleep(5);
	CliEcho("Loop #$intLoop");
	$intLoop++;
}
CliEcho("Complete!");
exit(1);
?>