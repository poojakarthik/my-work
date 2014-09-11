<?php
// Load the Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

CliEcho("\n\n[ FIXING M2 CDR FUCKUPS ]\n");

// Open the Import File
$strImportPath	= '/home/rdavis/fix_m2_dec08.csv';
$resImportFile	= fopen($strImportPath, 'r');
if (!$resImportFile)
{
	throw new Exception("Unable to open File at '{$strImportPath}'");
}

// Start a new Transaction
DataAccess::getDataAccess()->TransactionStart();

$strProcessingDatetime	= date("Y-m-d H:i:s");
try
{
	// Parse each line
	$intLine	= 0;
	while ($arrLine = fgetcsv($resImportFile))
	{
		$intLine++;
		if (count($arrLine) != 4)
		{
			throw new Exception("Parsing Error! -- Line {$intLine} has an incorrect number of columns");
		}
		
		$intAccount			= (int)$arrLine[0];
		$fltOldTotal		= (float)$arrLine[1];
		$fltNewTotal		= (float)$arrLine[2];
		$fltAmountIncGST	= (float)$arrLine[3];
		$fltAmount			= ($fltAmountIncGST / 11) * 10;
		
		$fltAmountIncGST	= round(abs($fltAmountIncGST), 2);
		
		// Get additional details
		$objAccount	= new Account(array('Id'=>$intAccount), false, true);
		
		CliEcho("\t+ Account {$intAccount}...", false);
		
		// Add Charge
		$objCharge	= new Charge();
		$objCharge->AccountGroup		= $objAccount->AccountGroup;
		$objCharge->Account				= $objAccount->Id;
		$objCharge->CreatedBy			= 0;										// System User
		$objCharge->CreatedOn			= $strProcessingDatetime;
		$objCharge->ApprovedBy			= 0;										// System User
		$objCharge->ChargedOn			= $strProcessingDatetime;
		$objCharge->Status				= CHARGE_APPROVED;
		$objCharge->global_tax_exempt	= 0;
		$objCharge->Amount				= round(abs($fltAmount), 2);
		if ($fltAmount > 0)
		{
			// Debit
			$objCharge->Nature			= 'DR';
			$objCharge->ChargeType		= 'UCHRG';
			$objCharge->Description		= 'Undercharged from December 2008';
			$objCharge->Notes			= "Account {$intAccount} has been debited \${$fltAmountIncGST} due to an M2 CDR issue affecting the December 2008 Invoice";
		}
		else
		{
			// Credit
			$objCharge->Nature			= 'CR';
			$objCharge->ChargeType		= 'OCHRG';
			$objCharge->Description		= 'Overcharged from December 2008';
			$objCharge->Notes			= "Account {$intAccount} has been credited \${$fltAmountIncGST} due to an M2 CDR issue affecting the December 2008 Invoice";
		}
		$objCharge->save();
		
		CliEcho("{$objCharge->Nature} for \${$fltAmountIncGST}");
		
		
		// Add Note
		$objNote	= new Note();
		$objNote->Note			= $objCharge->Notes;
		$objNote->AccountGroup	= $objAccount->AccountGroup;
		$objNote->Account		= $objAccount->Id;
		$objNote->Employee		= 0;											// System User
		$objNote->Datetime		= $strProcessingDatetime;
		$objNote->NoteType		= 7;
		$objNote->save();
	}
	
	// TEST MODE
	throw new Exception("TEST MODE");
}
catch (Exception $eException)
{
	// Rollback the Transaction
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}

// Commit the Transaction
DataAccess::getDataAccess()->TransactionRollback();
//DataAccess::getDataAccess()->TransactionCommit();
?>