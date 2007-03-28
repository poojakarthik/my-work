<?php

// load framework
$strFrameworkDir = "../framework/";
require_once($strFrameworkDir."framework.php");
require_once($strFrameworkDir."functions.php");
require_once($strFrameworkDir."definitions.php");
require_once($strFrameworkDir."config.php");
require_once($strFrameworkDir."database_define.php");
require_once($strFrameworkDir."db_access.php");
require_once($strFrameworkDir."report.php");
require_once($strFrameworkDir."error.php");
require_once($strFrameworkDir."exception_vixen.php");

// create framework instance
$GLOBALS['fwkFramework'] = new Framework();
$framework = $GLOBALS['fwkFramework'];

$selDirectDebit	= new StatementSelect("DirectDebit", "Id, AccountGroup", "Archived = 0", "Id DESC");
$selCreditCard	= new StatementSelect("CreditCard", "Id, AccountGroup", "Archived = 0", "Id DESC");
$selAccount		= new StatementSelect("Account", "Id, CreditCard, DirectDebit, BillingType", "AccountGroup = <AccountGroup>");
$arrColumns = Array();
$arrColumns['CreditCard']		= NULL;
$arrColumns['DirectDebit']		= NULL;
$arrColumns['BillingMethod']	= NULL;
$ubiAccount	= new StatementUpdateById("Account", $arrColumns);

ob_start();
echo "\n\n[ LINKING DIRECT DEBIT ]\n\n";
ob_flush();

// Grab the Direct Debit details
$intTotal = $selDirectDebit->Execute();
$intPassed = 0;
$intIgnored = 0;
while ($arrDD = $selDirectDebit->Fetch())
{
	ob_flush();
	echo " + Attempting to link DD #{$arrDD['Id']} to Account...\t\t\t";
	
	// Find the Account
	if (!$selAccount->Execute(Array('AccountGroup' => $arrDD['AccountGroup'])))
	{
		echo "[ FAILED ]\n";
		continue;
	}
	$arrAccount = $selAccount->Fetch();
	
	// Make sure this is a DD Account
	if ($arrAccount['BillingType'] == 3)
	{
		// Update the Account
		$arrAccount['DirectDebit']	= $arrDD['Id'];
		$arrAccount['BillingType']	= BILLING_TYPE_DIRECT_DEBIT;
		$ubiAccount->Execute($arrAccount);
		
		echo "[   OK   ]\n"; 
		$intPassed++;
	}
	else
	{
		// Not DD
		echo "[ IGNORE ]\n";
		$intIgnored++;
	}
}

echo "\nUpdated $intPassed of $intTotal Accounts, $intIgnored ignored.\n";
ob_flush();

echo "\n\n[ LINKING CREDIT CARD ]\n\n";
ob_flush();

// Grab the Direct Debit details
$intTotal = $selCreditCard->Execute();
$intPassed = 0;
$intIgnored = 0;
while ($arrCC = $selCreditCard->Fetch())
{
	ob_flush();
	echo " + Attempting to link CC #{$arrCC['Id']} to Account...\t\t\t";
	
	// Find the Account
	if (!$selAccount->Execute(Array('AccountGroup' => $arrCC['AccountGroup'])))
	{
		echo "[ FAILED ]\n";
		continue;
	}
	$arrAccount = $selAccount->Fetch();
	
	// Make sure this is a CC Account
	if ($arrAccount['CreditCard'] !== NULL)
	{
		// Update the Account
		$arrAccount['BillingType']	= BILLING_TYPE_CREDIT_CARD;
		$ubiAccount->Execute($arrAccount);
		
		echo "[   OK   ]\n"; 
		$intPassed++;
	}
	else
	{
		// Not CC
		echo "[ IGNORE ]\n";
		$intIgnored++;
	}
}
echo "\nUpdated $intPassed of $intTotal Accounts, $intIgnored ignored.\n";
ob_flush();



?>