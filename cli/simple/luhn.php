<?php

// Load the Framework
require_once("../../lib/framework/require.php");

$intNumber	= (int)$argv[1];
$intDigit	= MakeLuhn($intNumber);

CliEcho("Provided Number\t: $intNumber");
CliEcho("Correct Digit\t: $intDigit");

?>