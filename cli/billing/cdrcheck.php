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
 * @author		Rich 'Waste' Davis
 * @version		7.06
 * @copyright	2007 VOIPTEL Pty Ltd
 * @license		NOT FOR EXTERNAL DISTRIBUTION
 *
 */
 
// call application
require_once("../../flex.require.php");
$arrConfig = LoadApplication();

$bolVerbose = FALSE;
if (trim($argv[1] == '-v'))
{
	$bolVerbose = TRUE;
}


if (date("d") == '01')
{
	$strStartOfMonth	= date("Y-m-01", strtotime("-1 month"));
	$intDay				= (int)date("d", strtotime("-1 day"));
}
else
{
	$strStartOfMonth	= date("Y-m-01");
	$intDay				= (int)date("d");
}

// Init Statements
$selFiles		= new StatementSelect("FileImport", "*", "Carrier = <Carrier> AND FileType = <FileType> AND Status = 207 AND ImportedOn >= '$strStartOfMonth'", "FileName");
$selCDRCheck	= new StatementSelect("CDR", "Id", "Status IN (198, 150) AND Credit = 0 AND File = <File>", NULL, 1);

$GLOBALS['*strEmailContents']	= "";
CLIEchoString("\n[ VALIDATING CDR FILES ]\n\n");

// Loop through the file types
foreach ($arrConfig['CDRCheck'] as $arrCDRCheck)
{
	CLIEchoString(" * {$arrCDRCheck['Label']} Files...", 70);
	
	// Get some matches
	if ($selFiles->Execute($arrCDRCheck) === FALSE)
	{
		CLIEchoString("[ FAILED ]\n\t- Error in StatementSelect");
		//Debug($selFiles->Error());
		continue;
	}
	
	// Filter results to match filename regex & make sure they havent been invoiced at all
	$arrMatches = Array();
	while ($arrResult = $selFiles->Fetch())
	{
		// Match Regex
		if (preg_match($arrCDRCheck['FileNameRegex'], $arrResult['FileName']))
		{
			// Are there any CDRs?
			if ($selCDRCheck->Execute(Array('File' => $arrResult['Id'])))
			{
				$arrMatches[] = $arrResult;
			}
		}
	}
	
	
	$intMinCount		= 0;
	$intPreferredCount	= 0;
	switch ($arrCDRCheck['RecurringFreqType'])
	{
		case BILLING_FREQ_DAY:
			$intMinCount		= $intDay * $arrCDRCheck['MinCountPerFreq'];
			$intPreferredCount	= $intDay * $arrCDRCheck['PrefCountPerFreq'];
			break;
			
		case BILLING_FREQ_MONTH:
			foreach ($arrCDRCheck['ExpectedByPref'] as $intExpectedDay)
			{
				if ($intDay >= $intExpectedDay)
				{
					$intPreferredCount++;
				}
			}
			foreach ($arrCDRCheck['ExpectedByMin'] as $intExpectedDay)
			{
				if ($intDay >= $intExpectedDay)
				{
					$intMinCount++;
				}
			}
			break;
	}
	
	// Check matches
	if (count($arrMatches) == $intPreferredCount)
	{
		// Perfect number of matches
		CLIEchoString("[  PASS  ]\n\n");
	}
	else
	{
		if ($intMinCount > count($arrMatches))
		{
			// Not enough matches
			CLIEchoString("[ FAILED ]\n");
			
		}
		elseif (count($arrMatches) > $intPreferredCount)
		{
			// Too many matches
			CLIEchoString("[  WARN ]\n");
		}
		else
		{
			// Between minimum and preferred matches
			CLIEchoString("[  WARN  ]\n");
		}
	
		CLIEchoString("\t- Minimum: $intMinCount;\tPreferred: $intPreferredCount;\tFound: ".count($arrMatches)."\n\n");
		
		// Print out matches if in verbose mode
		if ($bolVerbose)
		{
			foreach ($arrMatches as $arrFile)
			{
				CLIEchoString("\t* {$arrFile['FileName']}\t({$arrFile['ImportedOn']})\n");
			}
		}
		
		CLIEchoString("\n");
	}
}

CLIEchoString("Emailing results...", 70, FALSE);

// Email results to flame & rich
$arrHeaders = Array();
$arrHeaders['From']		= 'billing@ybs.net.au';
$arrHeaders['Subject']	= "CDR File Check for ".date("Y-m-d");
$mimMime = new Mail_mime("\n");
$mimMime->setTXTBody($GLOBALS['*strEmailContents']);
$strBody = $mimMime->get();
$strHeaders = $mimMime->headers($arrHeaders);
$emlMail =& Mail::factory('mail');

// Send the email
$strEmailAddresses = "rdavis@ybs.net.au";
if (!$emlMail->send('rdavis@ybs.net.au', $strHeaders, $strBody))
{
	CLIEchoString("[ FAILED ]\n\n", NULL, FALSE);
}
else
{
	CLIEchoString("[   OK   ]\n\n", NULL, FALSE);
}

die;
	
	
// CLIEchoString
function CLIEchoString($strOutput, $intPadLength = NULL, $bolAddToEmail = TRUE)
{
	if ($intPadLength > 0)
	{
		$strOutput = str_pad($strOutput, $intPadLength);
	}
	echo $strOutput;
	ob_flush();
	
	if ($bolAddToEmail)
	{
		$GLOBALS['*strEmailContents'] .= $strOutput;
	}
}
?>
