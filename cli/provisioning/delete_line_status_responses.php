<?php

// Framework
require_once("../../flex.require.php");

// Statements
$selLineStatusFiles	= new StatementSelect("FileImport", "*", "FileType = 5004");
$qryDelete			= new Query();

CliEcho("\n[ DELETING LINE STATUS RESPONSES ]\n");

// Select all LineStatus files
$intCount		= 0;
$intTimeStart	= time();
if (($intTotal = $selLineStatusFiles->Execute()) !== FALSE)
{
	while ($arrFileImport = $selLineStatusFiles->Fetch())
	{
		$intCount++;
		$intSplit	= time() - $intTimeStart;
		CliEcho(" + ($intCount/$intTotal @ {$intSplit}s) #{$arrFileImport['Id']} -- '{$arrFileImport['FileName']}'\t\t\t", FALSE);
		
		// Delete Responses from this File
		if ($qryDelete->Execute("DELETE FROM ProvisioningResponse WHERE FileImport = {$arrFileImport['Id']}") === FALSE)
		{
			CliEcho("[ FAILED ]");
			CliEcho("\t -- ERROR: DB Error in \$qryDelete: ".$qryDelete->Error());'' .
			exit(2);
		}
		else
		{
			CliEcho("[   OK   ]");
		}
	}
}
else
{
	CliEcho("ERROR: DB Error in \$selLineStatusFiles: ".$selLineStatusFiles->Error());
	exit(1);
}

CliEcho();
exit(0);
?>