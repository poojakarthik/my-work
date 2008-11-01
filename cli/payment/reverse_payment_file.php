<?php

// Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

// Parse Parameters
$intFileImportId	= (int)$argv[1];
if ($intFileImportId < 1 || $intFileImportId === NULL)
{
	CliEcho("{$intFileImportId} is not a valid FileImport Id!");
	exit(1);
}

// Start a Transaction
DataAccess::getDataAccess->TransactionStart();

try
{
	// Get list of Payments from the given FileImport Id
	$selPaymentsByFile	 = new StatementSelect("Payment", "*", "File = <File> AND Status IN (101, 103, 150)");
	if ($selPaymentsByFile->Execute(Array('File'=>$intFileImportId)) === FALSE)
	{
		throw new Exception($selPaymentsByFile->Error());
	}
	
	// Reverse each payment
	while ($arrPayment = $selPaymentsByFile->Fetch())
	{
		CliEcho(" + Reversing Payment #{$arrPayment['Id']} (Account: $arrPayment['Account'])");
		
		// Reverse this Payment
		$GLOBALS['fwkFramework']->ReversePayment($arrPayment['Id'], 0);
	}
	
	throw new Exception('TEST MODE');
	
	// All looks good, commit the Transaction
	DataAccess::getDataAccess->TransactionCommit();
}
catch (Exception $eException)
{
	DataAccess::getDataAccess->TransactionRollback();
	throw $eException;
}

?>