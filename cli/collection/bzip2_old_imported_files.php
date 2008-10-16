<?php

// Framework
require_once("../../flex.require.php");

$selFileImport	= new StatementSelect("FileImport", "*", "compression_algorithm_id = 1");
$ubiFileImport	= new StatementUpdateById("FileImport");

// Get all FileImports
$mixTotal	= $selFileImport->Execute();
$intCount	= 0;
if ($mixTotal === FALSE)
{
	throw new Exception($selFileImport->Error());
}
else
{
	while ($arrFileImport = $selFileImport->Fetch())
	{
		$intCount++;
		CliEcho("+ ({$intCount}/{$mixTotal}) Archiving '{$arrFileImport['Location']}'...", FALSE);
		
		// If the file exists, then compress it
		if (file_exists($arrFileImport['Location']))
		{
			// Compress the Imported File using the BZ2 algorithm
			if (file_put_contents("compress.bzip2://{$arrFileImport['Location']}.bz2", file_get_contents($arrFileImport['Location'])))
			{
				// Success, remove the uncompressed file
				unlink($arrFileImport['Location']);
				
				$arrFileImport['Location']					.= '.bz2';
				$arrFileImport['compression_algorithm_id']	= COMPRESSION_ALGORITHM_BZIP2;
				
				// Save back to the DB
				if ($ubiFileImport->Execute($arrFileImport) === FALSE)
				{
					throw new Exception($ubiFileImport->Error());
				}
				CliEcho(" SUCCESS");
			}
			else
			{
				// Failure, keep the old file, and continue as if nothing went wrong
				CliEcho(" FAILED");
			}
		}
		else
		{
			CliEcho(" SKIPPED");
		}
	}
}

?>