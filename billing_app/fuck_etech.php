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
require_once('application_loader.php');

// load etech modules
require_once($strModuleDir."etech_reader.php");
require_once($strModuleDir."etech_biller.php");

$suxEtech = new EtechReader();
$etbEtech = new EtechBiller();

$stdout = fopen("php://stdout","w"); 
function CliEcho($strOutput)
{
	$stdout = $GLOBALS['stdout'];
	fwrite($stdout, $strOutput."\n");
}
CliEcho("STARTING");

// set location of files
$arrFilePath = glob("/home/vixen/etech_bills/2006/12/inv_telcoblue_*.txt");

foreach($arrFilePath AS $strFilePath)
{
	// open file
	CliEcho("Opening $strFilePath...");
	if (!$suxEtech->OpenFile($strFilePath))
	{
		CliEcho("Failed to open file $strFilePath");
		die;
	}
	
	// read file
	CliEcho("Parsing file...");
	$intCount = 0;
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
					case 'CDR':
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
							$arrUpdateCDR['Status'] 	= CDR_INVOICED;
							$arrUpdateCDR['Charge'] 	= $arrLine['Charge'];
							$arrUpdateCDR['Invoice'] 	= $arrLine['Invoice'];
							if (!$etbEtech->UpdateCDR($arrUpdateCDR))
							{
								CliEcho("CDR Update Failed : {$arrCDR['Id']}");
							}
						}
						
						break;
					
					case 'ServiceTypeTotal':
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
						
					case 'ServiceTotal':
						if (!$etbEtech->AddServiceTotal($arrLine))
						{
							CliEcho("ServiceTotal Failed : {$arrLine['FNN']} - {$arrLine['_Status']['CreatedOn']}");
						}
						break;
						
					case 'Invoice':
						if (!$etbEtech->UpdateInvoice($arrLine))
						{
							if (!$etbEtech->AddInvoice($arrLine))
							{
								CliEcho("Could not add invoice : {$arrLine['Id']}");
							}
						}
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

CliEcho("\n\nDone");
fclose($stdout);
die;

?>
