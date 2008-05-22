<?php

// Load File
$ptrInputFile	= fopen("serialised.input", 'r');
$strLine		= fgets($ptrInputFile);

if ($strLine)
{
	// Unserialise the Array
	$arrArray	= unserialize(trim($strLine));
}
fclose($ptrInputFile);

print_r($arrArray);

//------------------//
// MAKE CHANGES HERE
//------------------//

//------------------//

print_r($arrArray);

file_put_contents("serialised.output", serialize($arrArray));
$ptrOututFile	= fopen("serialised.output", 'w');

?>