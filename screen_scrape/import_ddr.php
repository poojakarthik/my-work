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
	$selCheckExists	= new StatementSelect("DirectDebit", "Id", "AccountGroup = <AccountGroup> AND Archived = 0");
	$insDirectDebit	= new StatementInsert("DirectDebit");
	
	// Set up paths, etc
	$arrPaths = Array();
	$arrPaths[]	= "sae00.csv";
	//$arrPaths[]	= "sae0009.csv";
	
	$arrInsertData = Array();
	$ptrFile = NULL;
	
	// Import data from the text file...
	foreach ($arrPaths as $strPath)
	{
		// open the file
		echo str_pad("Opening and parsing '$strPath'...", 70, " ", STR_PAD_RIGHT);
		if (!$ptrFile = fopen($strPath, "r"))
		{
			echo "[ FAILED ]\n";
			continue;
		}
		
		// parse each line
		while ($strLine = trim(fgets($ptrFile)))
		{
			// Split the line, and copy fields to our grand array
			$arrSplit		= explode(',', $strLine);
			$arrSplit[4]	= explode('-', $arrSplit[4]);
			
			$arrData['AccountGroup']	= (int)$arrSplit[4][2];
			$arrData['BankName']		= "Unknown";
			$arrData['BSB']				= $arrSplit[0];
			$arrData['AccountNumber']	= $arrSplit[1];
			$arrData['AccountName']		= $arrSplit[2];
			$arrData['Archived']		= 0;
			
			if ($mixResponse = $selCheckExists->Execute($arrData))
			{
				// Entry for this account already exists
				continue;
			}
			elseif ($mixResponse === FALSE)
			{
				Debug($selCheckExists->Error());
				die;
			}
			
			// add to our insert array
			$arrInsertData[] = $arrData;
		}
		
		echo "[   OK   ]\n";
	}
	
	echo "\n".str_pad("Inserting Data...", 70, " ", STR_PAD_RIGHT);
	
	foreach ($arrInsertData as $arrData)
	{
		if ($insDirectDebit->Execute($arrData) === FALSE)
		{
			Debug($insDirectDebit->Error());
			die;
		}
	}
	
	echo "[   OK   ]\n\n";
	die;	
?>