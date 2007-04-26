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


// Accounts to generate for
$arrAccounts	= Array();
$arrAccounts[]	= Array('Account' => 1000156611);
$arrAccounts[]	= Array('Account' => 1000160843);
$arrAccounts[]	= Array('Account' => 1000157789);

// Array of 00-99
$arrRange	= range(0, 99);

// Statements
$selServices	= new StatementSelect("Service", "Id, FNN", "Indial100 = 1 AND Account = <Account>");
$insExtension	= new StatementInsert("ServiceExtension");

echo "\n\n";

// Foreach account
foreach ($arrAccounts as $arrAccount)
{
	echo " + Creating Extensions for Account {$arrAccount['Account']}...\n";
	ob_flush();
	
	$selServices->Execute($arrAccount);
	$arrServices = $selServices->FetchAll();
	
	// Foreach service
	foreach ($arrServices as $arrService)
	{
		echo "\t+ Creating Extensions for Service {$arrService['FNN']}...\n";
		// Foreach extension
		foreach (range(0, 99) as $intExtension)
		{
			$strExtension = substr($arrService['FNN'], 0, -2).str_pad($intExtension, 2, '0', STR_PAD_LEFT);
			echo "\t\t + $strExtension\t\t\t";
			
			// Insert an entry
			$arrData = Array();
			$arrData['Service']		= $arrService['Id'];
			$arrData['Name']		= $strExtension;
			$arrData['RangeStart']	= $intExtension;
			$arrData['RangeEnd']	= $intExtension;
			if (!$insExtension->Execute($arrData))
			{
				echo "[ FAILED ]\n";
			}
			else
			{
				echo "[   OK   ]\n";
			}
			ob_flush();
		}
	}
}


?>