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

// Init
$arrPaymentStatuses	= Array(PAYMENT_WAITING, PAYMENT_PAYING, PAYMENT_FINISHED);
$strFileImportIds	= implode(', ', $arrFileImportIds);
$selPayments		= new StatementSelect("Payment", "*", "Account = <Account> AND Amount = <Amount> AND File IN ({$strFileImportIds})");

// Open Accounts file
$strContents	= str_replace("\r\n", "\n", file_get_contents($strAccountFilePath));
$arrLines		= explode("\n", $strContents);
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
		if (!trim($strLine))
		{
			// Blank line
			continue;
		}
		
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
			
			if ($mixResult > 2 || $mixResult < 1)
			{
				throw new Exception("Too many/few payments!");
			}
			
			while ($arrPayment = $selPayments->Fetch())
			{
				if (in_array($arrPayment['Status'], $arrPaymentStatuses))
				{
					// Reverse the Payment
					if ($GLOBALS['fwkFramework']->ReversePayment($arrPayment['Id'], 0))
					{
						CliEcho("\t + Reversed Payment #{$arrPayment['Id']} (Paid: {$arrPayment['PaidOn']}; File: {$arrPayment['File']})");
					}
					else
					{
						throw new Exception("Error Reversing Payment #{$arrPayment['Id']}");
					}
				}
				else
				{
					CliEcho("\t ! Cannot Reverse Payment #{$arrPayment['Id']} with Status '".GetConstantDescription($arrPayment['Status'], 'PaymentStatus')."'");
				}
			}
		}
	}
	
	// DEBUG: Test Mode
	throw new Exception("TEST MODE!");
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