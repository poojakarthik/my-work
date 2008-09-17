<?php

// Framework
require_once("../../flex.require.php");

$strEffectiveDate	= '2008-09-01 00:00:00';
$intEffectiveDate	= strtotime($strEffectiveDate);
$strOutputPath		= "/home/rdavis/flex_unitel_agreed_services_".date('Ymd', $intEffectiveDate).".csv";
$arrReportPaths		= Array(
								CARRIER_UNITEL => '/home/rdavis/rsl058a20080901.txt',
								CARRIER_UNITEL_VOICETALK => '/home/rdavis/rsl321a20080901.txt'
							);

// Statements
$selService			= new StatementSelect("Service", "*", "Id = <Service>");
$selServiceDetails	= new StatementSelect("Service", "MIN(EarliestCDR) AS EarliestCDR, MAX(LatestCDR) AS LatestCDR", "FNN = <FNN> OR (FNN LIKE <FNNIndial> AND Indial100 = 1)");
$selLastResponse	= new StatementSelect(	"(ProvisioningResponse PR JOIN provisioning_type ON provisioning_type.id = PR.Type) JOIN Service ON Service.Id = PR.Service", 
											"PR.Description, PR.EffectiveDate, PR.ImportedOn", 
											"(PR.FNN = <FNN> OR (PR.FNN LIKE <FNNIndial> AND Indial100 = 1)) AND PR.Carrier = <Carrier> AND provisioning_type_nature = ".REQUEST_TYPE_NATURE_FULL_SERVICE,
											"ISNULL(PR.EffectiveDate) ASC, PR.EffectiveDate DESC, PR.ImportedOn DESC",
											"1");

$arrServiceTemplate	= Array(
								'Carrier'									=> NULL,
								'FNN'										=> NULL,
								'Basket 1'									=> NULL,
								'Basket 2'									=> NULL,
								'Basket 3'									=> NULL,
								'Basket 4'									=> NULL,
								'Basket 5'									=> NULL,
								'Basket 6'									=> NULL,
								'Flex Account'								=> NULL,
								'Flex Earliest CDR'							=> NULL,
								'Flex Latest CDR'							=> NULL,
								'Last Provisioning Response'				=> NULL,
								'Last Provisioning Response Effective Date'	=> NULL
							);

CliEcho("\n[ FLEX vs UNITEL AGREED BASKETS ]\n");

// Open the output file
CliEcho(" * Opening Output File '{$strOutputPath}'...");
$resOutputFile	= fopen($strOutputPath, 'w');
if ($resOutputFile)
{
	// Create the Header Row
	fwrite($resOutputFile, '"'.implode('","', array_keys($arrServiceTemplate)).'"'."\n");
	
	// Parse each input ABR
	$arrService	= NULL;
	foreach ($arrReportPaths as $intCarrier=>$strPath)
	{
		// Load the file
		CliEcho(" * Opening Input File '{$strPath}'...");
		$resInputFile	= fopen($strPath, 'r');
		if ($resInputFile)
		{
			// Parse each line
			while ($strLine = fgets($resInputFile))
			{
				// Ignore Headers and Footers
				if (in_array(substr($strLine, 0, 1), Array('H', 'T')))
				{
					continue;
				}
				
				// Split the line
				$arrLine		= SplitABRLine($strLine);
				$arrLine['FNN']	= trim($arrLine['FNN']);
				
				// Is this the first Service in the Report?
				if ($arrService === NULL)
				{
					CliEcho("\t + First Service '{$arrLine['FNN']}'...", FALSE);
					$arrService	= $arrServiceTemplate;
				}
				// Is this a new FNN?
				elseif ($arrService['FNN'] != $arrLine['FNN'])
				{
					// Get Flex Service Details
					$arrServiceOwner	= FindFNNOwner($arrService['FNN'], $strEffectiveDate);
					if ($selService->Execute($arrServiceOwner) === FALSE)
					{
						throw new Exception($selService->Error());
					}
					$arrServiceDetails	= $selService->Fetch();
					if ($selServiceDetails->Execute(Array('FNN' => $arrService['FNN'], 'FNNIndial' => substr($arrService['FNN'], 0, -2).'__')) === FALSE)
					{
						throw new Exception($selServiceDetails->Error());
					}
					$arrServiceDetails	= array_merge($arrServiceDetails, $selServiceDetails->Fetch());
					if ($selLastResponse->Execute(Array('Carrier' => $intCarrier, 'FNN' => $arrService['FNN'], 'FNNIndial' => substr($arrService['FNN'], 0, -2).'__')) === FALSE)
					{
						throw new Exception($selLastResponse->Error());
					}
					$arrServiceDetails	= array_merge($arrServiceDetails, $selLastResponse->Fetch());
					
					// Finalise Output Row
					$arrService['Carrier']										= GetConstantDescription('Carrier', $intCarrier);
					$arrService['Flex Account']									= $arrServiceOwner['Account'];
					$arrService['Flex Earliest CDR']							= $arrServiceDetails['EarliestCDR'];
					$arrService['Flex Latest CDR']								= $arrServiceDetails['LatestCDR'];
					$arrService['Last Provisioning Response']					= $arrServiceDetails['Description'];
					$arrService['Last Provisioning Response Effective Date']	= date('d/m/Y', strtotime(($arrServiceDetails['EffectiveDate']) ? $arrServiceDetails['EffectiveDate'] : $arrServiceDetails['ImportedOn']));
					
					// Add the current Service to the Report
					fwrite($resOutputFile, '"'.implode('","', $arrService).'"'."\n");
					
					// Clean up the Current Service
					$arrService	= $arrServiceTemplate;
					CliEcho("\n\t + New Service '{$arrLine['FNN']}'...", FALSE);
				}
				
				// Add this basket to the current Service
				$arrService['FNN']	= $arrLine['FNN'];
				
				$strEffectiveDate	= date("d/m/Y", strtotime($arrLine['EffectiveDate']));
				switch ($arrLine['LastChange'])
				{
					case 'S':
						$strLastChange	= "New Service";
						break;
					case 'G':
						$strLastChange	= "Gain by Reversal";
						break;
					case 'A':
						$strLastChange	= "Actioned Order (Unitel)";
						break;
					case 'C':
						$strLastChange	= "Completed Order (Telstra)";
						break;
					default:
						$strLastChange	= $arrLine['LastChange'];
				}
				$arrService['Basket '.$arrLine['Basket']]	= $strLastChange." on ".$strEffectiveDate;
				CliEcho(" {$arrLine['Basket']}", FALSE);
			}
			
			// Close the input file
			fclose($resInputFile);
			CliEcho();
		}
	}
}

// Close the Output File and exit
fclose($resOutputFile);
exit(0);



// SplitABRLine
function SplitABRLine($strLine)
{
	static	$arrDefinition;
	if (!isset($arrDefinition))
	{
		$arrDefinition	= Array(
									'RecordType'	=> Array(
																'Start'		=> 0,
																'Length'	=> 1,
																'Type'		=> 'string'
															),
									'Sequence'		=> Array(
																'Start'		=> 1,
																'Length'	=> 8,
																'Type'		=> 'integer'
															),
									'FNN'			=> Array(
																'Start'		=> 9,
																'Length'	=> 29,
																'Type'		=> 'string'
															),
									'Basket'		=> Array(
																'Start'		=> 28,
																'Length'	=> 3,
																'Type'		=> 'integer'
															),
									'LastChange'	=> Array(
																'Start'		=> 41,
																'Length'	=> 1,
																'Type'		=> 'string'
															),
									'EffectiveDate'	=> Array(
																'Start'		=> 42,
																'Length'	=> 8,
																'Type'		=> 'string'
															)
								);
	}
	
	// Split the line
	$arrSplit	= Array();
	foreach ($arrDefinition as $strField=>$arrFieldDefinition)
	{
		$arrSplit[$strField]	= substr($strLine, $arrFieldDefinition['Start'], $arrFieldDefinition['Length']);
		CliEcho($arrSplit[$strField]);
		settype($arrSplit[$strField], $arrFieldDefinition['Type']);
	}
	
	Debug($strLine);
	Debug($arrSplit);
	die;
	return $arrSplit;
}


// GetPreviousServiceInstances
function GetPreviousServiceInstances($arrService)
{
	static	$selPreviousInstance;
	$selPreviousInstance	= (isset($selPreviousInstance)) ? $selPreviousInstance : new StatementSelect("Service", "*", "CreatedOn < <CreatedOn> AND <FNN> ");
	
	// If this isn't the earliest Instance
	if ($arrService['NatureOfCreation'] !== SERVICE_CREATION_NEW)
	{
		// Get the previous Instance
		
		$arrService['LastInstance']	= GetPreviousServiceInstances();
		return $arrService;
	}
	else
	{
		return FALSE;
	}
}
?>