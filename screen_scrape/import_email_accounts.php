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

// Init Statements
$arrColumns = Array();
$arrColumns['BillingMethod'] = BILLING_METHOD_EMAIL;
$ubiAccount = new StatementUpdateById("Account", $arrColumns);

// List of directories to parse (include trailing /)
$arrDirs = Array();
$arrDirs[]	= "/home/richdavis/invoice_temp/2007/01/1/Email/";
$arrDirs[]	= "/home/richdavis/invoice_temp/2007/01/2/Email/";
$arrDirs[]	= "/home/richdavis/invoice_temp/2007/01/4/Email/";

echo "<pre>\n\n[ IMPORTING EMAIL ACCOUNTS ]\n";

$intUpdated = 0;
foreach ($arrDirs as $strDir)
{	
	$arrFiles = glob($strDir."*.pdf");
	echo "\n\n* Browsing '$strDir'... ".count($arrFiles)." files to be parsed...\n\n";
	foreach ($arrFiles as $strFilename)
	{
		// Update the account
		$arrColumns['Id'] = (int)substr(basename($strFilename), 0, 10);
		
		echo "\t+ Updating Account #{$arrColumns['Id']}...\t\t\t";
		
		$mixResponse = $ubiAccount->Execute($arrColumns);
		if ($mixResponse === FALSE)
		{
			echo '\nError in $ubiAccount: '.$ubiAccount->Error()."\n\n";
			die;
		}
		elseif (!$mixResponse)
		{
			echo "[   SKIP   ]\n";
		}
		else
		{
			echo "[ UPDATED! ]\n";
			$intUpdated++;
		}
		ob_flush();
	}	
}
echo "\n COMPLETE! $intUpdated Accounts updated! \n\n";

?>