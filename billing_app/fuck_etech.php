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

// set location of file
$strFilePath = "/home/vixen/etech_bills/2006/12/inv_telcoblue_20070105_1167948643.txt";

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
	$intCount++;
	if ($intCount > 1000)
	{
		break;
	}
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
						CliEcho("CDR NOT FOUND for : ".$arrLine['FNN']);
						print_r($arrLine);
						Die;
					}
					else
					{
						CliEcho($arrLine['FNN']." = ".$mixReturn);
					}
					break;
				
				case 'ServiceTypeTotal':
					// Ignore
					break;
					
				case 'ServiceTotal':
					// Ignore
					break;
					
				case 'Invoice':
					// Ignore
					break;
				
				case 'Other':
					// Ignore
					break;
					
				default:
					CliEcho("Unknown Table ({$arrLine['_Table']}) at Line {$arrLine['_LineNo']}");
					break;
			}
			break;
			
		// match line
		default:
			CliEcho("Unknown Type at Line {$arrLine['_LineNo']}");
			break;
	}
}

CliEcho("\n\nDone");
fclose($stdout);
Die();

?>
