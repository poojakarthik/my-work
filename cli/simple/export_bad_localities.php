<?php

// Flex Framework
require_once('../../lib/classes/Flex.php');
Flex::load();

define('LOCALITY_DATABASE_PATH'	, "/home/rdavis/locality.csv");
define('CONVERSION_OUTPUT_PATH'	, "/home/rdavis/locality_errors.csv");

define('ADDRESS_FIELD_LOCALITY'	, 'Locality');
define('ADDRESS_FIELD_POSTCODE'	, 'Postcode');
define('ADDRESS_FIELD_STATE'	, 'State');
define('ADDRESS_FIELD_COUNTRY'	, 'Country');

define('LOCAL_FIELD_ID'			, 'Id');

define('LEVENSHTEIN_DIVISOR'	, 3);

define('CLOSE_MATCH_LIMIT'		, 99);

define('VERBOSE_MODE'			, true);
define('SINGLE_LINE_MODE'		, true);
define('SILENT_MODE'			, false);

$arrAddressTables	=	array
						(
							'Account'							=>	array
																	(
																		LOCAL_FIELD_ID			=> 'Id',
																		ADDRESS_FIELD_LOCALITY	=> 'Suburb',
																		ADDRESS_FIELD_POSTCODE	=> 'Postcode',
																		ADDRESS_FIELD_STATE		=> 'State'
																	),
							'dealer'							=>	array
																	(
																		LOCAL_FIELD_ID			=> 'id',
																		ADDRESS_FIELD_LOCALITY	=> 'suburb',
																		ADDRESS_FIELD_POSTCODE	=> 'postcode'
																	),
							'ServiceAddress'					=>	array
																	(
																		LOCAL_FIELD_ID			=> 'Id',
																		ADDRESS_FIELD_LOCALITY	=> 'ServiceLocality',
																		ADDRESS_FIELD_POSTCODE	=> 'ServicePostcode',
																		ADDRESS_FIELD_STATE		=> 'ServiceState'
																	),
							'ServiceAddress ServiceAddressBill'	=>	array
																	(
																		LOCAL_FIELD_ID			=> 'Id',
																		ADDRESS_FIELD_LOCALITY	=> 'BillLocality',
																		ADDRESS_FIELD_POSTCODE	=> 'BillPostcode'
																	)
						);

$dbConnection	= Data_Source::get();
$dbConnection->setFetchMode(MDB2_FETCHMODE_ASSOC);

if (!SILENT_MODE)
{
	Log::getLog()->log(" [*] Loading Locality CSV Database...\n");
}

// Load the Locality CSV Database
if (!$resLocalityFile = @fopen(LOCALITY_DATABASE_PATH, 'r'))
{
	throw new Exception(print_r(error_get_last(), true));
}

$arrHeader	= fgetcsv($resLocalityFile);

$arrLocalities	= array();
while ($arrLocalityRaw = fgetcsv($resLocalityFile))
{
	if (count($arrLocalityRaw) >= 3)
	{
		$arrLocalities[]	=	array
								(
									ADDRESS_FIELD_POSTCODE	=> (int)$arrLocalityRaw[0],
									ADDRESS_FIELD_LOCALITY	=> trim(strtoupper($arrLocalityRaw[1])),
									ADDRESS_FIELD_STATE		=> trim(strtoupper($arrLocalityRaw[2]))
								);
	}
}

fclose($resLocalityFile);

if (!$resOutputFile = @fopen(CONVERSION_OUTPUT_PATH, 'w'))
{
	throw new Exception(print_r(error_get_last(), true));
}

fputcsv($resOutputFile, array('Flex Reference', 'Current Locality', 'Current Postcode', 'Current State', 'Correct Locality', 'Correct Postcode', 'Correct State'));

// Process each Address Table
$intTotalCount		= 0;
$intTotalPerfect	= 0;
foreach ($arrAddressTables as $strTable=>$arrDefinition)
{
	$arrTableName			= explode(' ', trim($strTable));
	$strPhysicalTableName	= reset($arrTableName);
	$strFriendlyTableName	= end($arrTableName);
	
	// Parse Definition
	$arrColumns	= array();
	foreach ($arrDefinition as $strNormalisedName=>$strColumnName)
	{
		$arrColumns[]	= "{$strColumnName} AS {$strNormalisedName}";
	}
	
	$strSelectSQL	= "SELECT ".implode(', ', $arrColumns)." FROM {$strTable} WHERE 1;";
	$resSelect		= $dbConnection->query($strSelectSQL);
	if (PEAR::isError($resSelect))
	{
		throw new Exception($resSelect->getMessage()."\n".$resSelect->getUserInfo());
	}
	$intCount	= 0;
	$intPerfect	= 0;
	while ($arrAddress = $resSelect->fetchRow())
	{
		$intCount++;
		
		// Process Address
		$intPostcode	= (int)$arrAddress[ADDRESS_FIELD_POSTCODE];
		$strLocality	= trim(strtoupper($arrAddress[ADDRESS_FIELD_LOCALITY]));
		$strState		= (array_key_exists(ADDRESS_FIELD_STATE, $arrAddress)) ? trim(strtoupper($arrAddress[ADDRESS_FIELD_STATE])) : null;
		
		$arrAddressOutput	=	array
								(
									'Id'				=> $strPhysicalTableName.'.'.$arrDefinition[LOCAL_FIELD_ID].': '.(int)$arrAddress[LOCAL_FIELD_ID],
									'Current Locality '	=> $strLocality,
									'Current Postcode '	=> str_pad($intPostcode, 4, '0', STR_PAD_LEFT),
									'Current State '	=> $strState
								);
		
		$strLogBuffer	= "\t[+] {$strFriendlyTableName} #".$arrAddress[LOCAL_FIELD_ID]."\t: '{$strLocality}'   ".($strState ? $strState : 'UNK')."   ".str_pad($intPostcode, 4, '0', STR_PAD_LEFT)."\n";
		
		$arrLocalityMatches	= array();
		
		$bolPerfectMatch	= false;
		foreach ($arrLocalities as $intLocalityIndex=>$arrLocality)
		{
			
			$bolPostcodeMatch	= ($intPostcode === $arrLocality[ADDRESS_FIELD_POSTCODE]);
			$bolLocalityMatch	= ($strLocality === $arrLocality[ADDRESS_FIELD_LOCALITY]);
			$bolStateMatch		= ($strState === null || $strState === $arrLocality[ADDRESS_FIELD_STATE]);
			
			if ($bolPostcodeMatch && $bolLocalityMatch && $bolStateMatch)
			{
				// Perfect Match
				$intPerfect++;
				if (VERBOSE_MODE || SINGLE_LINE_MODE)
				{
					$bolPerfectMatch	= true;
					$strLogBuffer		.= "\t\t[+] Perfect match found!\n";
				}
				break;
			}
			else
			{
				$strInvalidLocality	= preg_replace("/[^A-Z0-9]/i", '', $strLocality);
				$strValidLocality	= preg_replace("/[^A-Z0-9]/i", '', $arrLocality[ADDRESS_FIELD_LOCALITY]);
				
				// How close are the Locality names?
				$intDifference		= levenshtein($strInvalidLocality, $strValidLocality);
				$intMaxDifferences	= ceil(strlen($strInvalidLocality) / LEVENSHTEIN_DIVISOR);
				
				// Is it a close Locality match AND the same Postcode?
				if ($intDifference < $intMaxDifferences && $bolPostcodeMatch)
				{
					// Same Postcode & close Locality
					$intScore	= 0 - ($intMaxDifferences + 1 - $intDifference);
					$arrLocalityMatches[$intLocalityIndex]	= $intScore;
					
					if (VERBOSE_MODE)
					{
						$strLogBuffer	.= "\t\t[-] Close Locality & Postcode Match: '".$arrLocality[ADDRESS_FIELD_LOCALITY]."', ".str_pad($arrLocality[ADDRESS_FIELD_POSTCODE], 4, '0', STR_PAD_LEFT)." (Score: {$intScore})\n";
					}
				}
				elseif ($bolPostcodeMatch)
				{
					// Same Postcode
					$arrLocalityMatches[$intLocalityIndex]	= -1;
					
					if (VERBOSE_MODE)
					{
						$strLogBuffer	.= "\t\t[-] Postcode Match:  '".$arrLocality[ADDRESS_FIELD_LOCALITY]."', ".str_pad($arrLocality[ADDRESS_FIELD_POSTCODE], 4, '0', STR_PAD_LEFT)."\n";
					}
				}
				elseif ($intDifference < $intMaxDifferences)
				{
					// Add to our close-match array
					$arrLocalityMatches[$intLocalityIndex]	= $intDifference;
					
					if (VERBOSE_MODE)
					{
						$strLogBuffer	.= "\t\t[-] Close Locality Match: '".$arrLocality[ADDRESS_FIELD_LOCALITY]."', ".str_pad($arrLocality[ADDRESS_FIELD_POSTCODE], 4, '0', STR_PAD_LEFT)." (Difference: {$intDifference})\n";
					}
				}
			}
		}
		
		if (!$bolPerfectMatch)
		{
			if (count($arrLocalityMatches))
			{
				// Order the Matches by Closeness
				asort($arrLocalityMatches);
				while (count($arrLocalityMatches) > CLOSE_MATCH_LIMIT)
				{
					array_pop($arrLocalityMatches);
				}
				
				if (VERBOSE_MODE || SINGLE_LINE_MODE)
				{
					$intScore			= reset($arrLocalityMatches);
					$intLocalityIndex	= key($arrLocalityMatches);
					$strLogBuffer	.= "\t\t[*] Best Match: '".$arrLocalities[$intLocalityIndex][ADDRESS_FIELD_LOCALITY]."'   ".$arrLocalities[$intLocalityIndex][ADDRESS_FIELD_STATE]."   ".str_pad($arrLocalities[$intLocalityIndex][ADDRESS_FIELD_POSTCODE], 4, '0', STR_PAD_LEFT)." (Score: {$intScore})\n";
				}
			}
			else
			{
				$strLogBuffer	.= "\t\t[!] No Match!\n";
			}
			
			foreach ($arrLocalityMatches as $intLocalityIndex=>$intScore)
			{
				$arrAddressOutput[]	= $arrLocalities[$intLocalityIndex][ADDRESS_FIELD_LOCALITY];
				$arrAddressOutput[]	= str_pad($arrLocalities[$intLocalityIndex][ADDRESS_FIELD_POSTCODE], 4, '0', STR_PAD_LEFT);
				$arrAddressOutput[]	= $arrLocalities[$intLocalityIndex][ADDRESS_FIELD_STATE];
			}
			fputcsv($resOutputFile, $arrAddressOutput);
		}
		
		if (!SILENT_MODE && (count($arrLocalityMatches) || VERBOSE_MODE || SINGLE_LINE_MODE))
		{
			Log::getLog()->log(trim($strLogBuffer));
		}
	}
	
	$intTotalCount		+= $intCount;
	$intTotalPerfect	+= $intPerfect;
	SendEmail('rdavis@ybs.net.au', "Locality Totals for '{$strFriendlyTableName}'", "Table\t: {$strFriendlyTableName}\nTotal\t: {$intCount}\nPerfect\t: {$intPerfect}");
}

	SendEmail('rdavis@ybs.net.au', "Locality Grand Totals", "Total\t: {$intTotalCount}\nPerfect\t: {$intTotalPerfect}");

fclose($resOutputFile);

exit(0);
?>