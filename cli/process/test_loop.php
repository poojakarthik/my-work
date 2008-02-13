<?php
require_once("../../flex.require.php");

CliEcho("Starting! (PID: ".getmypid().")");
$intLoop	= 12;
while (true)
{
	sleep(5);
	CliEcho("Loop #$intLoop");
}
CliEcho("Complete!");
die;
?>