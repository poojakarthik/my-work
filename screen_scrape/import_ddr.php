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
	$selCheckEFTExists	= new StatementSelect("DirectDebit", "Id", "AccountGroup = <AccountGroup> AND Archived = 0");
	$selCheckCCExists	= new StatementSelect("CreditCard", "Id", "AccountGroup = <AccountGroup> AND Archived = 0");
	$insDirectDebit	= new StatementInsert("DirectDebit");
	$insCreditCard	= new StatementInsert("CreditCard");
	
	// Set up paths, etc
	$strEFTPath	= "sae00.csv";
	$strCCPath	= "sae0009.csv";
	
	$arrInsertData = Array();
	$ptrFile = NULL;
	
	// Import data from the EFT CSV file...
	// open the file
	echo str_pad("Opening and parsing '$strPath'...", 70, " ", STR_PAD_RIGHT);
	if (!$ptrFile = fopen($strEFTPath, "r"))
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
		
		if ($mixResponse = $selCheckEFTExists->Execute($arrData))
		{
			// Entry for this account already exists
			continue;
		}
		elseif ($mixResponse === FALSE)
		{
			Debug($selCheckEFTExists->Error());
			die;
		}
		
		// add to our insert array
		$arrInsertData[] = $arrData;
	}
	
	echo "[   OK   ]\n";
	
	
	// Import data from the CC CSV file...
	// open the file
	echo str_pad("Opening and parsing '$strPath'...", 70, " ", STR_PAD_RIGHT);
	if (!$ptrFile = fopen($strCCPath, "r"))
	{
		echo "[ FAILED ]\n";
		continue;
	}
	
	// parse each line
	while ($strLine = trim(fgets($ptrFile)))
	{
		// Split the line, and copy fields to our grand array
		$arrSplit		= explode(',', $strLine);
		$arrSplit[3]	= explode('-', $arrSplit[3]);
		$arrSplit[1]	= explode('/', $arrSplit[1]);
		
		$arrData['AccountGroup']	= (int)$arrSplit[3][2];
		$arrData['CardType']		= GetCCType($arrSplit[0]);
		$arrData['Name']			= "Unknown";
		$arrData['CardNumber']		= $arrSplit[0];
		$arrData['ExpMonth']		= $arrSplit[1][0];
		$arrData['ExpYear']			= "20".$arrSplit[1][1];
		$arrData['CVV']				= "000";
		$arrData['Archived']		= 0;
		
		if ($mixResponse = $selCheckCCExists->Execute($arrData))
		{
			// Entry for this account already exists
			continue;
		}
		elseif ($mixResponse === FALSE)
		{
			Debug($selCheckCCExists->Error());
			die;
		}
		
		// add to our insert array
		$arrInsertData[] = $arrData;
	}
	
	echo "[   OK   ]\n";

	
	echo "\n".str_pad("Inserting Data...", 70, " ", STR_PAD_RIGHT);
	
	foreach ($arrInsertData as $arrData)
	{
		if ($insCreditCard->Execute($arrData) === FALSE)
		{
			Debug($insCreditCard->Error());
			die;
		}
	}
	
	echo "[   OK   ]\n\n";
	die;
	
	
	// Gets the CreditCard type
	function GetCCType($strCCNumber)
	{
		switch (strlen($strCCNumber))
		{
			case 13:
				return CREDIT_CARD_VISA;
			case 14:
				return CREDIT_CARD_DINERS;
			case 15:
				if (substr($strCCNumber, 0, 1) == '2')
				{
					return CREDIT_CARD_DINERS;
				}
				elseif (substr($strCCNumber, 0, 1) == '3')
				{
					return CREDIT_CARD_AMEX;
				}
			case 16:
				return CREDIT_CARD_MASTERCARD;
		}
		return CREDIT_CARD_VISA;
	}
?>