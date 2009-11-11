<?php

// Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

$sProcessName	= 'process-locking-test';

// Check if I'm already running
Log::getLog()->log("Process '{$sProcessName}' is ".((Flex_Process::factory($sProcessName)->isLocked()) ? '' : 'not ')." locked");

// Attempt to lock myself
Log::getLog()->log("Attempting to lock process '{$sProcessName}'...", false);
Log::getLog()->log((Flex_Process::factory($sProcessName)->lock()) ? ' Success!' : 'Failed!');

// Check if I'm running again
Log::getLog()->log("Process '{$sProcessName}' is ".((Flex_Process::factory($sProcessName)->isLocked()) ? '' : 'not ')." locked");

// Check if Payments are running
Log::getLog()->log("Process '".Flex_Process::PROCESS_BILLING_GENERATE."' is ".((Flex_Process::factory(Flex_Process::PROCESS_BILLING_GENERATE)->isLocked()) ? '' : 'not ')." locked");

Log::getLog()->log("Waiting to end for ", false);
$iSecondsToWait	= 15;
$iSecondsWaited	= 0;
while ($iSecondsWaited < $iSecondsToWait)
{
	sleep(1);
}

// Exit peacefully
exit(0);

?>