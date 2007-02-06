<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Reprint invoices for a defined list of accounts
//----------------------------------------------------------------------------//
 
 echo "<pre>";

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


// Select all accounts
$selAccounts = new StatementSelect("Account", "Id", "Archived = 0");
if ($selAccounts->Execute() === FALSE)
{
	Debug('$selAccounts died in the ass');
	die;
}
$arrAccounts = $selAccounts->FetchAll();

// loop through the accounts
$fltGrandTotal = 0.0;
foreach ($arrAccounts as $arrAccount)
{
	$fltGrandTotal += (float)$this->GetInvoiceTotal($arrAccount['Id']);
}

Debug("Grand Total: $fltGrandTotal (ex. GST)");
die;
?>
