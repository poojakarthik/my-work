<?php
require_once("../../flex.require.php");

CliEcho("Starting! (PID: ".getmypid().")");
$intLoop	= 0;
while ($intLoop < 12)
{
	sleep(5);
	CliEcho("Loop #$intLoop");
	$intLoop++;
}
CliEcho("Complete!");
exit(1);
?>