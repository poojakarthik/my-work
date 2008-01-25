<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2007 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//

	// call application
	require_once("../../flex.require.php");
	
	function AAPT()
	{
		echo "Checking AAPT records...";
		ob_flush();
		$arrLettertoMonth = range('A', 'L');
		$intCurrentDaysInMonth = (int)date("t", time());
		$strDate = date("Y-m-01 00:00:00", time());
	
		$selAAPT = new StatementSelect ('FileImport', '*', "Id NOT IN (SELECT File FROM CDR WHERE Status IN (199, 198)) AND Carrier = 3 AND FileType = 4 AND ImportedOn > '$strDate'");
		$selAAPT->Execute();
		$arrAAPT = $selAAPT->FetchAll();
		
		$arrAAPTFiles = Array();
		foreach ($arrAAPT as $arrEntry)
		{
			$arrFileDate = explode('.', $arrEntry['FileName']);
			$strFileDate = $arrFileDate[1];
			$intmonth = array_search($strFileDate[0], $arrLettertoMonth) + 1;
			$intday = substr($strFileDate, -2);
			if ($intmonth == (int)date("m"))
			{
				$arrAAPTFiles[] = $arrEntry['FileName'];
			}
		}
		$intAAPTCount = count($arrAAPTFiles);
		if ($intAAPTCount != $intCurrentDaysInMonth)
		{
			echo "\t\t\t\t[ FAILED ]\n";
			ob_flush();
			echo "\tReason: There are not enough files from AAPT ($intAAPTCount of $intCurrentDaysInMonth). \n";
			ob_flush();
		}
		else
		{
			echo "\t\t\t\t[   OK   ]\n";
			ob_flush();
		}
		ob_flush();		
	}
	
	function Optus()
	{
		echo "Checking Optus records...";
		ob_flush();
		$arrLettertoMonth = range('A', 'L');
		$intCurrentDaysInMonth = (int)date("t", time());
		$strDate = date("Y-m-01 00:00:00", time());
	
		$selOptus = new StatementSelect ('FileImport', '*', "Id NOT IN (SELECT File FROM CDR WHERE Status IN (199, 198)) AND Carrier = 2 AND FileType = 3 AND ImportedOn > '$strDate'");
		$selOptus->Execute();
		$arrOptus = $selOptus->FetchAll();
		
		$arrOptusFiles = Array();
		foreach ($arrOptus as $arrEntry)
		{
			$arrFileDate = explode('_', $arrEntry['FileName']);
			$strFileDate = $arrFileDate[3];
			$intMonth = substr($strFileDate, 4, 2);
			$intDay = substr($strFileDate, -2);
			
			if ($intMonth == (int)date("m"))
			{
				$arrOptusFiles[] = $arrFileDate[3];
			}
		}
		$arrOptusFiles = array_unique($arrOptusFiles);
		
		$intOptusCount = count($arrOptusFiles);
		if ($intOptusCount != $intCurrentDaysInMonth)
		{
			echo "\t\t\t\t[ FAILED ]\n";
			echo "\tReason: There are not enough files from Optus ($intOptusCount of $intCurrentDaysInMonth). \n";
		}
		else
		{
			echo "\t\t\t\t[   OK   ]\n";
		}
		ob_flush();	
	}
	

	function Unitel()
	{
		echo "Checking Unitel records...\n";
		ob_flush();
		
		$arrLettertoMonth = range('A', 'L');
		$intCurrentDaysInMonth = (int)date("t", time());
		$strDate = date("Y-m-01 00:00:00", time());
	
		$selUnitel = new StatementSelect ('FileImport', '*', "Id NOT IN (SELECT File FROM CDR WHERE Status IN (199, 198)) AND Carrier = 1 AND FileType BETWEEN 1 AND 2 AND ImportedOn > '$strDate'");
		$selUnitel->Execute();
		$arrUnitel = $selUnitel->FetchAll();
		
		// Find the monthly service and equiptment file
		$bolFound = FALSE;
		echo "\tS&E monthly...";
		foreach ($arrUnitel as $arrEntry)
		{
			if (substr($arrEntry['FileName'], 3, 2) == 'On')
			{
				$bolFound = TRUE;
				echo "\t\t\t\t\t[   OK   ]\n";
			}
		}
		if (!$bolFound)
		{
			echo "\t\t\t\t\t[ FAILED ]\n";
		}	
		ob_flush();
		
		//Find bi-monthly s&e
		$intBiMon = 0;
		echo "\tS&E bi-monthly...";
		foreach ($arrUnitel as $arrEntry)
		{
			if (substr($arrEntry['FileName'], 3, 2) == 'Of')
			{
				$intBiMon++;
			}
		}
		if ($intBiMon == 2)
		{
			echo "\t\t\t\t[   OK   ]\n";
		}
		else
		{
			echo "\t\t\t\t[ FAILED ]\n";
			echo "\t\tReason: There are not enough bi-monthly S&E files ($intBiMon of 2). \n";
		}			
		ob_flush();
		
		//Check Landline & Mobile
		$intLandline = 0;
		$intMobile = 0;
		
		foreach ($arrUnitel as $arrEntry)
		{
			if (strlen($arrEntry['FileName']) == 12 AND $arrEntry['FileType'] == 2)
			{
				$intLandline++;
			}
			elseif (strlen($arrEntry['FileName']) == 12 AND $arrEntry['FileType'] == 1)
			{
				$intMobile++;
			}
		}
		echo "\tLandline...";
		if ($intLandline != $intCurrentDaysInMonth)
		{
			echo "\t\t\t\t\t[ FAILED ]\n";
			echo "\t\tReason: There are not enough Unitel landline files ($intLandline of $intCurrentDaysInMonth). \n";
		}
		else
		{
			echo "\t\t\t\t\t[   OK   ]\n";
		}
		ob_flush();
		
		echo "\tMobile...";
		if ($intMobile != $intCurrentDaysInMonth)
		{
			echo "\t\t\t\t\t[ FAILED ]\n";
			echo "\t\tReason: There are not enough Unitel mobile files ($intMobile of $intCurrentDaysInMonth). \n";
		}
		else
		{
			echo "\t\t\t\t\t[   OK   ]\n";
		}
		ob_flush();	
	}

	// Main execution
	AAPT();
	Optus();
	Unitel();
	echo "Done.\n";
?>
