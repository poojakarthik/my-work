<?php
	
	//----------------------------------------------------------------------------//
	// (c) copyright 2007 VOIPTEL Pty Ltd
	//
	// NOT FOR EXTERNAL DISTRIBUTION
	//----------------------------------------------------------------------------//

	//----------------------------------------------------------------------------//
	// cdrcheck
	//----------------------------------------------------------------------------//
	/**
	 * cdrcheck
	 *
	 * Check for CDRs
	 *
	 * Checks for complete collection of CDR files at the end of the month
	 *
	 * @file		cdrcheck.php
	 * @language	PHP
	 * @package		billing_app
	 * @author		Nathan Abussi and Joel Dawkins
	 * @version		7.05
	 * @copyright	2007 VOIPTEL Pty Ltd
	 * @license		NOT FOR EXTERNAL DISTRIBUTION
	 *
	 */
	 
	// call application
	require_once("../framework/require.php");
	
	//------------------------------------------------------------------------//
	// AAPT
	//------------------------------------------------------------------------//
	/**
	 * AAPT()
	 *
	 * Checks for AAPT records
	 *
	 * Checks for AAPT records
	 * 
	 * @function
	 */
	
	// Checking for AAPT records
	function AAPT()
	{
		echo "Checking AAPT records...";
		ob_flush();
		$arrLettertoMonth = range('A', 'L');
		$intCurrentDaysInMonth = (int)date("t", time());
		$strDate = date("Y-m-01 00:00:00", time());
		
		// Query
		$selAAPT = new StatementSelect ('FileImport', '*', "Id NOT IN (SELECT File FROM CDR WHERE Status IN (199, 198)) AND Carrier = 3 AND FileType = 4 AND ImportedOn > '$strDate'");
		$selAAPT->Execute();
		$arrAAPT = $selAAPT->FetchAll();
		
		$arrAAPTFiles = Array();
		// Checking each entry in the result list
		foreach ($arrAAPT as $arrEntry)
		{
			// Get the date from the end of the file name
			$arrFileDate = explode('.', $arrEntry['FileName']);
			$strFileDate = $arrFileDate[1];
			
			// turn this "date" into a month and day
			$intmonth = array_search($strFileDate[0], $arrLettertoMonth) + 1;
			$intday = substr($strFileDate, -2);
			
			// If the file is for this current month, add to temp array
			if ($intmonth == (int)date("m"))
			{
				$arrAAPTFiles[] = $arrEntry['FileName'];
			}
		}
		$intAAPTCount = count($arrAAPTFiles);
		
		// Check and output
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
	
	//------------------------------------------------------------------------//
	// Optus
	//------------------------------------------------------------------------//
	/**
	 * Optus()
	 *
	 * Checks for Optus records
	 *
	 * Checks for Optus records
	 * 
	 * @function
	 */
	 
	// Checking for Optus records
	function Optus()
	{
		echo "Checking Optus records...";
		ob_flush();
		$arrLettertoMonth = range('A', 'L');
		$intCurrentDaysInMonth = (int)date("t", time());
		$strDate = date("Y-m-01 00:00:00", time());
	
		// Query
		$selOptus = new StatementSelect ('FileImport', '*', "Id NOT IN (SELECT File FROM CDR WHERE Status IN (199, 198)) AND Carrier = 2 AND FileType = 3 AND ImportedOn > '$strDate'");
		$selOptus->Execute();
		$arrOptus = $selOptus->FetchAll();
		
		$arrOptusFiles = Array();
		// Checking each entry in the result list
		foreach ($arrOptus as $arrEntry)
		{
			// Get the date string from the filename
			$arrFileDate = explode('_', $arrEntry['FileName']);
			$strFileDate = $arrFileDate[3];
			
			// turn it into a month and day
			$intMonth = substr($strFileDate, 4, 2);
			$intDay = substr($strFileDate, -2);
			
			// If the file is for this current month, add to temp array
			if ($intMonth == (int)date("m"))
			{
				$arrOptusFiles[] = $arrFileDate[3];
			}
		}
		// Remove duplicates
		$arrOptusFiles = array_unique($arrOptusFiles);
		
		// Check and output 
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
	
	//------------------------------------------------------------------------//
	// Unitel
	//------------------------------------------------------------------------//
	/**
	 * Unitel()
	 *
	 * Checks for Unitel records
	 *
	 * Checks for Unitel records
	 * 
	 * @function
	 */
	
	// Checking for Unitel records
	function Unitel()
	{
		echo "Checking Unitel records...\n";
		ob_flush();
		
		$arrLettertoMonth = range('A', 'L');
		$intCurrentDaysInMonth = (int)date("t", time());
		$strDate = date("Y-m-01 00:00:00", time());
		
		// Query
		$selUnitel = new StatementSelect ('FileImport', '*', "Id NOT IN (SELECT File FROM CDR WHERE Status IN (199, 198)) AND Carrier = 1 AND FileType BETWEEN 1 AND 2 AND ImportedOn > '$strDate'");
		$selUnitel->Execute();
		$arrUnitel = $selUnitel->FetchAll();
		
		// Find the monthly service and equipment file
		$bolFound = FALSE;
		echo "\tS&E monthly...";
		// Check for files with "Onnet" inside.. these are monthlys
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
		
		// Find bi-monthly s&e
		$intBiMon = 0;
		echo "\tS&E bi-monthly...";
		// Check for files with "Offnet" inside.. these are bi-monthlys
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
		
		// Check Landline & Mobile
		$intLandline = 0;
		$intMobile = 0;
		
		foreach ($arrUnitel as $arrEntry)
		{
			// Files have a filename 12 chars long, and a Landline is type 1
			if (strlen($arrEntry['FileName']) == 12 AND $arrEntry['FileType'] == 1)
			{
				$intLandline++;
			}
			// Files have a filename 12 chars long, and a Mobile is type 2
			elseif (strlen($arrEntry['FileName']) == 12 AND $arrEntry['FileType'] == 2)
			{
				$intMobile++;
			}
		}
		echo "\tLandline...";
		// Check and output
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
