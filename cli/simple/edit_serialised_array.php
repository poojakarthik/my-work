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

Debug($arrArray);

//------------------//
// MAKE CHANGES HERE
//------------------//

//------------------//

Debug($arrArray);

file_put_contents("serialised.output", serialise($arrArray));
$ptrOututFile	= fopen("serialised.output", 'w');

?>