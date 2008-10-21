<?php

// Framework
require_once("../../flex.require.php");

$strAccountFilePath	= $argv[1];
for ($i = 2; $i  < $argc; $i++)
{
	$intFileImportId	= (int)$argv[$i];
	if ($intFileImportId)
	{
		$arrFileImportIds[]	= $intFileImportId;
	}
}

// Validate Parameters
if (!is_file($strAccountFilePath))
{
	throw new Exception("'{$strAccountFilePath}' is not a vaild file path!");
}
if (!count($arrFileImportIds))
{
	throw new Exception("No valid FileImport Ids specified!");
}

// Init Statements
$strFileImportIds	= implode(', ', $arrFileImportIds);
$selPayments		= new StatementSelect("Payment", "Id", "Account = <Account> AND Amount = <Amount> AND File IN ({$strFileImportIds})");

// Open Accounts file
$arrLines	= file_get_contents($strAccountFilePath);
if (!$arrLines)
{
	throw new Exception("Unable to open file '{$strAccountFilePath}'!");
}

// Start Transaction
DataAccess::getDataAccess()->TransactionStart();

try
{
	// For each Line in the file
	foreach ($arrLines as $strLine)
	{
		// Parse the line
		$arrLine	= split(',', $strLine);
		$intAccount	= (int)$arrLine[0];
		$fltAmount	= ((float)$arrLine[1] / 100);
		
		CliEcho(" * Account: {$intAccount}; Amount: \${$fltAmount}...");
		
		// Get list of payments for this Account, for this amount, from this file
		$mixResult	= $selPayments->Execute(Array('Account'=>$intAccount, 'Amount'=>$fltAmount));
		if ($mixResult === FALSE)
		{
			throw new Exception($selPayments->Error());
		}
		else
		{
			CliEcho("\t > Found {$mixResult} payments!");
			
			while ($arrPayment = $selPayments->Fetch())
			{
				// Reverse the Payment
				/*if ($GLOBALS['fwkFramework']->ReversePayment($arrPayment['Id'], 0))
				{
					CliEcho("\t + Reversed Payment #{$arrPayment['Id']}");
				}
				else
				{
					throw new Exception("Error Reversing Payment #{$arrPayment['Id']}");
				}*/
			}
		}
		
		// DEBUG: Test Mode
		throw new Exception("TEST MODE!");
	}
}
catch (Exception $eException)
{
	// Revoke Transaction and re-throw
	DataAccess::getDataAccess()->TransactionRollback();
	throw $eException;
}

// Commit Transaction
DataAccess::getDataAccess()->TransactionRollback();
?>