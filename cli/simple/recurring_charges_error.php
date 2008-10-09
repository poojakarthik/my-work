<?php

// Framework
require_once("../../flex.require.php");

// Statements
$ubiRecurringCharge	= new StatementSelect("RecurringCharge");

// Load the file
$strPath	= $argv[1];
if (!is_file($strPath))
{
	throw new Exception("File {$strPath} does not exist!");
}
$resInputFile	= fopen($strPath, 'r');


DataAccess::getDataAccess()->TransactionStart();
try
{
	// Parse each line
	$strCurrentDate	= NULL;
	while ($strLine = fgets($resInputFile))
	{
		// Is this the date line?
		if (stripos($strLine, "Charges Report for ") === 0)
		{
			// Get the Date
			$strCurrentDate	= substr($strLine, 19, 10);
			CliEcho("File Date: {$strCurrentDate}");
		}
		
		// Is this a valid "Generating Charge" line?
		if (stripos($strLine, " + Generating charge for #") === 0)
		{
			// Do we have a File Date yet?
			if (!$strCurrentDate)
			{
				throw new Exception("Recurring Charge line found before Date definition!");
			}
			
			// Undo last Recurring Charge Operation
			$strRecurringChargeId	= substr($strLine, stripos($strLine, '#'), strlen($strLine)  - 1 - stripos($strLine, '...'));
			$intRecurringChargeId	= (int)$strRecurringChargeId;
			
			CliEcho(" + Undoing Recurring Charge #{$intRecurringChargeId} ({$strRecurringChargeId})...");
			/*
			if ($ubiRecurringCharge->Execute() === FALSE)
			{
				throw new Exception($ubiRecurringCharge->Error());
			}*/
		}
	}
		
	// DEBUG
	DataAccess::getDataAccess()->TransactionRollback();
	//DataAccess::getDataAccess()->TransactionCommit();
}
catch (Exception $eException)
{
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}
?>