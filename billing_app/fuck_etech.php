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
		
		/*$intCount++;
		if ($intCount > 10000)
		{
			break;
		}*/
		
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
							//CliEcho("CDR NOT FOUND for : ".$arrLine['FNN']);
							/*print_r($arrLine);
							Die;*/
						}
						else
						{						
							if (abs($mixReturn['Difference']) > 0.05)
							{
								CliEcho($arrLine['FNN']." (".$mixReturn['Id'].") =\t ".$mixReturn['Difference']."\t\t\t".GetConstantDescription($mixReturn['Status'], 'CDR'));
							}
			
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
							
							// Insert into DB
							$arrLine['VixenCDR'] = $mixReturn['Id'];
							$etbEtech->InsertEtechCDR($arrLine);
						}
						break;
					
					case 'ServiceTypeTotal':
						// Ignore
						//CliEcho("ServiceTypeTotal");
						break;
						
					case 'ServiceTotal':
						//CliEcho("ServiceTotal");
						// Ignore
						break;
						
					case 'Invoice':
						//CliEcho("Invoice");
						// Ignore
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
