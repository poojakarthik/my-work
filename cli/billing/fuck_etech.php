<?php
//----------------------------------------------------------------------------//
// (c) copyright 2006 VOIPTEL Pty Ltd
//
// NOT FOR EXTERNAL DISTRIBUTION
//----------------------------------------------------------------------------//

//----------------------------------------------------------------------------//
// Execute a billing run
//----------------------------------------------------------------------------//

// load application
LoadApplication();

// load etech modules
require_once($strModuleDir."etech_reader.php");
require_once($strModuleDir."etech_biller.php");

$suxEtech = new EtechReader();
$etbEtech = new EtechBiller();

$stdout = fopen("php://stdout","w");

if (!function_exists("CliEcho"))
{
	function CliEcho($strOutput)
	{
		$stdout = $GLOBALS['stdout'];
		fwrite($stdout, $strOutput."\n");
	}
}
CliEcho("STARTING");

// set location of files
//$arrFilePath = glob("/home/etech_bills/2006/11/inv_telcoblue_*.txt");	// November
//$arrFilePath = glob("/home/etech_bills/2006/12/inv_telcoblue_*.txt");	// December
$arrFilePath = glob("/home/etech_bills/2007/inv_telcoblue_*.txt");	// January

$strCDRsWeCantMatchFilename = "/home/richdavis/Desktop/etech_cdrs_we_cant_match_minx_jan.txt";

$arrInvoices		= Array();
$arrCursor			= Array('|', '/', '-', '\\');

foreach($arrFilePath AS $strFilePath)
{
	// open file
	CliEcho("\nOpening $strFilePath...");
	if (!$suxEtech->OpenFile($strFilePath))
	{
		CliEcho("Failed to open file $strFilePath");
		die;
	}
	
	// read file
	CliEcho("Parsing file...");
	$intCount = 0;
	$intCDRs = 0;
	$intMatches = 0;
	$intInvoiceNo = NULL;
	while($arrLine = $suxEtech->FetchNext())
	{
		$arrLine['_File']	= $strFilePath;
		
		// check line type
		switch($arrLine['_LineType'])
		{
			case 'ERROR':
				CliEcho("Error at Line {$arrLine['_LineNo']}");
				break;
				
			case 'DATA':
				// check table type
				switch ($arrLine['_Table'])
				{
					case 'CDR':/*
						$arrCDR = $etbEtech->FindCDR($arrLine);
						if ($arrCDR === FALSE)
						{
							// no match found
							$arrLine['Status']	= CDR_ETECH_NO_MATCH;
						}
						else
						{
							$arrLine['VixenCDR'] = $arrCDR['Id'];
							
							// Determine status
							if ($arrCDR['Id'] && $arrCDR['Difference'] === (float)0.0)
							{
								$arrLine['Status']	= CDR_ETECH_PERFECT_MATCH;
							}
							elseif ($arrCDR['Id'])
							{
								$arrLine['Status']	= CDR_ETECH_IMPERFECT_MATCH;
							}
							else
							{
								$arrCDR['Status']	= CDR_ETECH_NO_MATCH;
								$arrLine['VixenCDR'] = 0;
							}
						}
						/*
						// Insert etech CDR into DB
						if (!$etbEtech->InsertEtechCDR($arrLine))
						{
							CliEcho("CDR Insert Failed : {$arrLine['_File']} - {$arrLine['_LineNo']}");
							break;
						}
						
						// update our CDR
						if ($arrCDR['Id'] && $arrCDR['Status'] == CDR_NORMALISED)
						{
							$arrUpdateCDR['Id'] 		= $arrCDR['Id'];
							$arrUpdateCDR['Status'] 	= CDR_ETECH_INVOICED;
							$arrUpdateCDR['Charge'] 	= $arrLine['Charge'];
							if (!$etbEtech->UpdateCDR($arrUpdateCDR))
							{
								CliEcho("CDR Update Failed : {$arrCDR['Id']}");
							}
						}
						*/
						
						if (($strCursor = next($arrCursor)) === FALSE)
						{
							$strCursor = reset($arrCursor);
						}
						echo "\033[1D$strCursor";
						ob_flush();
						
						// Insert Etech CDR into CDREtech
						$arrLine['Status']	 = CDR_ETECH_NO_MATCH;
						if (!($intId = $etbEtech->InsertEtechCDR($arrLine)))
						{
							CliEcho("CDR Insert Failed : {$arrLine['_File']} - {$arrLine['_LineNo']}");
							break;
						}
						/*
						// Try to match to a viXen CDR
						if (($arrCDR = $etbEtech->FindCDR($arrLine)) === FALSE)
						{
							// Could not match
							$arrCDRsNotInVixen[$intInvoiceNo] = $intId;
						}
						else
						{
							// Update CDREtech to reflect match
							$arrLine['Id']			= $intId;
							$arrLine['VixenCDR']	= $arrCDR['Id'];
							$arrLine['Status']		= CDR_ETECH_PERFECT_MATCH;
							$etbEtech->UpdateEtechCDR($arrLine);
							
							// Update viXen CDR
							$arrUpdateCDR['Id'] 		= $arrCDR['Id'];
							$arrUpdateCDR['Status'] 	= CDR_ETECH_INVOICED;
							$arrUpdateCDR['Charge'] 	= $arrLine['Charge'];
							if (!$etbEtech->UpdateCDR($arrUpdateCDR))
							{
								//CliEcho("CDR Update Failed : {$arrCDR['Id']}");
							}
						}
						*/
						break;
					
					case 'ServiceTypeTotal':/*
						/*if(!$etbEtech->AddServiceTypeTotal($arrLine))
						{
							CliEcho("ServiceTypeTotal Failed : {$arrLine['FNN']} - {$arrLine['_Status']['CreatedOn']}");
						}*/
					
						/*if($arrLine['RecordType'] == 17)
						{
							$intLocal =  $etbEtech->UpdateLocalCDRs($arrLine, $arrLine['_Status']['CreatedOn']);
							if(!$intLocal)
							{
								CliEcho("UpdateLocal Failed : {$arrLine['FNN']} - {$arrLine['_Status']['CreatedOn']}");
							}
						}*/
						break;
						
					case 'ServiceTotal':/*
						if (!$etbEtech->AddServiceTotal($arrLine))
						{
							CliEcho("ServiceTotal Failed : {$arrLine['FNN']} - {$arrLine['_Status']['CreatedOn']}");
						}*/
						break;
						
					case 'Invoice':
						/*if (!$etbEtech->UpdateInvoice($arrLine))
						{
							if (!$etbEtech->AddInvoice($arrLine))
							{
								CliEcho("Could not add invoice : {$arrLine['Id']}");
							}
						}
						
						if (!$etbEtech->UpdateTotalOwing($arrLine, '45dfe46ae67cd'))
						{
							CliEcho("Could not update account : {$arrLine['Account']}");
						}
						else
						{
							CliEcho("Updated account : {$arrLine['Account']}");
						}*/
						
						// Add to Invoice array
						$arrInvoices[] = $arrLine['Id'];
						/*
						if (($strCursor = next($arrCursor)) === FALSE)
						{
							$strCursor = reset($arrCursor);
						}*/
						
						$intInvoiceNo = $arrLine['Id'];
						
						$intInvCount = count($arrInvoices);
						$strEcho = "Parsed $intInvCount Invoices... *";
						$intLen = strlen($strEcho);
						echo "\033[".$intLen."D".$strEcho;
						ob_flush();
						
						break;
					
					case 'Other':
						// Ignore
						break;
						
					default:
						CliEcho("Unknown Table ({$arrLine['_Table']}) at Line {$arrLine['_LineNo']}");
						print_r($arrLine);
						die;
						break;
				}
				break;
				
			// match line
			default:
				CliEcho("Unknown Type at Line {$arrLine['_LineNo']}");
				break;
		}
	}
}
/*
// Match Invoices
$arrFailedInvoices	= $etbEtech->MatchInvoices($arrInvoices);
$intFailedInvoices	= count($arrFailedInvoices);
$intTotalInvoices	= count($arrInvoices);
$strInvoices = implode(', ', $arrInvoices);
$strInvoiceRun = $etbEtech->FindInvoiceRun($arrInvoices[0]);
$selMissingInvoices = new StatementSelect("Invoice", "Id, Account", "Id NOT IN ($strInvoices) AND InvoiceRun = '$strInvoiceRun' AND Total != 0");
$selMissingInvoices->Execute();
$arrMissingAccounts = $selMissingInvoices->FetchAll();
CliEcho("\n * $intTotalInvoices with PDFs, $intFailedInvoices without PDFs, ".count($arrMissingAccounts)." without PDFs but non-zero Total+Tax");

$strFileName = "/home/richdavis/Desktop/january_accounts_missing_pdfs.txt";
if (file_exists($strFileName))
{
	unlink($strFileName);
}
echo " * Writing Accounts with Missing Invoices to '$strFileName'...  ";
$ptrFile = fopen($strFileName, "w");
foreach ($arrMissingAccounts as $arrAccount)
{
	if (($strCursor = next($arrCursor)) === FALSE)
	{
		$strCursor = reset($arrCursor);
	}
	echo "\033[1D$strCursor";
	fwrite($ptrFile, $arrAccount['Account']."\n");
}
echo "\n\n";
*/





// Update Local Call CDRs (impossible to match them)
echo "\n[ Updating Local Call CDRs ]\n\n";
$arrUpdateCols = Array();
$arrUpdateCols['Status'] = CDR_ETECH_INVOICED;
$strLocalRecordTypes = "17, 18"; // FIXME????
$upd190DayCDRs = new StatementUpdate("CDR", "(StartDatetime > SUBDATE('2007-05-01 00:00:00', INTERVAL 190 DAY) OR RecordType IN ($strLocalRecordTypes)) AND Status = 250", $arrUpdateCols);
$intCount = $sel190DayCDRs->Execute($arrUpdateCols, Array());
echo " * $intCount Local Call CDRs Updated!\n\n";
echo " * Writing Accounts with Missing Invoices to '$strFileName'...  ";

// Write list of CDRs we couldnt match to a file
$ptrFile = fopen($strCDRsWeCantMatchFilename, "w");
foreach ($arrCDRsNotInVixen as $intAccount=>$intCDR)
{
	if (($strCursor = next($arrCursor)) === FALSE)
	{
		$strCursor = reset($arrCursor);
	}
	echo "\033[1D$strCursor";
	fwrite($ptrFile, "Invoice: $intAccount; CDREtech.Id: $intCDR\n");
}
echo "\n\n";


CliEcho("\n\nDone");
fclose($stdout);
die;

?>
