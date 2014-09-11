<?php

// load framework
LoadFramework();

// Read command line arguments as Account list
$bolFirst = TRUE;
$arrAccounts	= Array();
foreach ($argv as $strArgument)
{
	if ($bolFirst)
	{
		$bolFirst = FALSE;
		continue;
	}
	$arrAccounts[] = (int)trim($strArgument);
}

if (!$arrAccounts)
{
	echo "\nERROR: No Accounts to Generate for!\n\n";
	die;
}

//Debug($arrAccounts);
//die;

// Array of 00-99
$arrRange	= range(0, 99);

// Statements
$selServices	= new StatementSelect("Service", "Id, FNN", "Indial100 = 1 AND Account = <Account>");
$insExtension	= new StatementInsert("ServiceExtension");

echo "\n\n";

// Foreach account
foreach ($arrAccounts as $intAccount)
{
	echo " + Creating Extensions for Account $intAccount...\n";
	ob_flush();
	
	$selServices->Execute(Array('Account' => $intAccount));
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