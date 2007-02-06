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

$hlpHelper = new VixenHelper();


// Select all accounts
$selAccounts = new StatementSelect("Account", "Id", "Archived = 0");
if (($intCount = $selAccounts->Execute()) === FALSE)
{
	Debug('$selAccounts died in the ass');
	die;
}
$arrAccounts = $selAccounts->FetchAll();

// loop through the accounts
$fltGrandTotal = 0.0;
$i = 0;
$fltLastTime = 0.0;
foreach ($arrAccounts as $arrAccount)
{
	$i++;
	$fltLastTime = microtime(TRUE);
	
	echo "+ ($i of $intCount) Working Account #".$arrAccount['Id']."...\t\t\t";
	if (($mixResult = $hlpHelper->GetInvoiceTotal($arrAccount['Id'])) === FALSE)
	{
		echo "FAILED!\n";
		continue;
	}
	$fltGrandTotal += (float)$mixResult;
	$intTimeLapse = microtime() - $intLastTime;
	$intLastTime = microtime();
	echo '$'.$fltGrandTotal." ($fltTimeLapse secs)\n";
}

Debug("Grand Total: $fltGrandTotal (ex. GST)");
die;
?>
