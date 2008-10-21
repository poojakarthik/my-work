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
$arrPaymentStatuses	= Array(PAYMENT_REVERSED);
$strFileImportIds	= implode(', ', $arrFileImportIds);
$selPayments		= new StatementSelect("Payment", "*", "Account = <Account> AND Amount = <Amount> AND File IN ({$strFileImportIds})");
$ubiPayment			= new StatementUpdateById("Payment", Array('Status'=>NULL));
$qryQuery			= new Query();

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
					// Unreverse the Payment
					$arrPayment['Status']	= PAYMENT_IMPORTED;
					if ($ubiPayment->Execute($arrPayment))
					{
						if ($qryQuery->Execute("DELETE FROM Note WHERE Account = {$arrPayment['Account']} AND NoteType = 7 AND Note LIKE 'Administrators Reversed a Payment made on __/10/2008 for %'"))
						{
							CliEcho("\t + Unreversed Payment #{$arrPayment['Id']} (Paid: {$arrPayment['PaidOn']}; File: {$arrPayment['File']})");
						}
						elseif ($qryQuery->Error())
						{
							throw new Exception($qryQuery->Error());
						}
						else
						{
							throw new Exception("\t ! No Notes updated for Payment #{$arrPayment['Id']}!");
						}
					}
					else
					{
						throw new Exception("Error Unreversing Payment #{$arrPayment['Id']}");
					}
				}
				else
				{
					CliEcho("\t ! Cannot Unreverse Payment #{$arrPayment['Id']} with Status '".GetConstantDescription($arrPayment['Status'], 'PaymentStatus')."'");
				}
			}
		}
	}
	
	// DEBUG: Test Mode
	//throw new Exception("TEST MODE!");
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