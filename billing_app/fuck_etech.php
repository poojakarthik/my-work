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
						$mixReturn = $etbEtech->FindCDR($arrLine);
						if ($mixReturn === FALSE)
						{
							// no match found
							$arrLine['Status']	= CDR_ETECH_NO_MATCH;
						}
						else
						{
							// Determine status
							if ($mixReturn['Difference'] === (float)0.0)
							{
								$arrLine['Status']	= CDR_ETECH_PERFECT_MATCH;
							}
							elseif ($mixReturn['Id'])
							{
								$arrLine['Status']	= CDR_ETECH_IMPERFECT_MATCH;
							}
							else
							{
								$arrLine['Status']	= CDR_ETECH_NO_MATCH;
							}
							
							$arrLine['VixenCDR'] = $mixReturn['Id'];
						}
						
						// Insert into DB
						if (!$etbEtech->InsertEtechCDR($arrLine))
						{
							CliEcho("CDR Insert Failed : {$arrLine['_File']} - {$arrLine['_LineNo']}");
						}
						break;
					
					case 'ServiceTypeTotal':
						if(!$etbEtech->AddServiceTypeTotal($arrLine))
						{
							CliEcho("ServiceTypeTotal Failed : {$arrLine['FNN']}");
						}
						break;
						
					case 'ServiceTotal':
						if (!$etbEtech->AddServiceTypeTotal($arrLine))
						{
							CliEcho("ServiceTotal Failed : {$arrLine['FNN']}");
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
						//CliEcho("Other");
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
