<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

// Ensure the File exists
$strFilename	= $argv[1];
if (!file_exists($strFilename))
{
	CliEcho("\nThe file '{$strFilename}' does not exist.  Please specify a valid path.\n");
	exit(1);
}

$resInputFile	= fopen($strFilename, 'r');
if ($resInputFile)
{
	try
	{
		DataAccess::getDataAccess->TransactionStart();
		
		$strDatetime	= Data_Source_Time::currentTimestamp();
		
		// Read through each line
		while (!feof($resInputFile))
		{
			$strFNN = trim(fgets($resInputFile));
			
			if (preg_match("/^0(23478)\d+$/", $strFNN))
			{
				CliEcho(" [+] Blacklisting '{$strFNN}'...");
				
				// Valid FNN, import
				$objBlacklistFNN	= new Telemarketing_FNN_Blacklist();
				$objBlacklistFNN->fnn									= $strFNN;
				$objBlacklistFNN->cached_on								= $strDatetime;
				$objBlacklistFNN->expired_on							= '9999-12-31 23:59:59';
				$objBlacklistFNN->telemarketing_fnn_blacklist_nature_id	= TELEMARKETING_FNN_BLACKLIST_NATURE_OPTOUT;
				$objBlacklistFNN->save();
			}
			else
			{
				CliEcho(" [!] '{$strFNN}' is not a valid FNN!");
			}
		}
		
		throw new Exception('TEST MODE');
		
		// All looks good -- commit
		DataAccess::getDataAccess->TransactionCommit();
	}
	catch (Exception $eException)
	{
		DataAccess::getDataAccess->TransactionRollback();
		throw $eException;
	}
}
else
{
	CliEcho("\nUnable to open the file '{$strFilename}' for reading.\n");
	exit(2);
}

?>