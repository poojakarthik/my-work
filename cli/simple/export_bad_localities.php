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

// Process each Address Table
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
	while ($arrAddress = $resSelect->fetchRow())
	{
		// Process Address
		$intPostcode	= (int)$arrAddress[ADDRESS_FIELD_POSTCODE];
		$strLocality	= trim(strtoupper($arrAddress[ADDRESS_FIELD_LOCALITY]));
		$strState		= (array_key_exists(ADDRESS_FIELD_STATE, $arrAddress)) ? trim(strtoupper($arrAddress[ADDRESS_FIELD_STATE])) : null;
		
		$arrAddressOutput	=	array
								(
									'Id'		=> $strPhysicalTableName.'.'.$arrDefinition[LOCAL_FIELD_ID].': '.(int)$arrAddress[LOCAL_FIELD_ID],
									'Locality'	=> $arrDefinition[ADDRESS_FIELD_LOCALITY].': '.$strLocality, 
									'State'		=> ($strState ? $arrDefinition[ADDRESS_FIELD_STATE].': '.$strState : ''), 
									'Postcode'	=> $arrDefinition[ADDRESS_FIELD_POSTCODE].': '.str_pad($intPostcode, 4, '0', STR_PAD_LEFT)
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
				if (VERBOSE_MODE || SINGLE_LINE_MODE)
				{
					$bolPerfectMatch	= true;
					$strLogBuffer		.= "\t\t[+] Perfect match found!\n";
				}
				continue 2;
			}
			else
			{
				// How close are the Locality names?
				$strInvalidLocality	= preg_replace("/[^A-Za-z0-9]/i", '', $strLocality);
				$strValidLocality	= preg_replace("/[^A-Za-z0-9]/i", '', $arrLocality[ADDRESS_FIELD_LOCALITY]);
				
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
				$strLogBuffer	.= "\t\t[*] Best Match: '".$arrLocalities[$intLocalityIndex][ADDRESS_FIELD_LOCALITY]."', ".str_pad($arrLocalities[$intLocalityIndex][ADDRESS_FIELD_POSTCODE], 4, '0', STR_PAD_LEFT)." (Score: {$intScore})\n";
			}
		}
		
		if (!$bolPerfectMatch && !count($arrLocalityMatches))
		{
			$strLogBuffer	.= "\t\t[!] No Match!\n";
		}
		
		if (!SILENT_MODE && (count($arrLocalityMatches) || VERBOSE_MODE || SINGLE_LINE_MODE))
		{
			Log::getLog()->log(trim($strLogBuffer));
		}
		
		fputcsv($resOutputFile, array_merge($arrAddressOutput, $arrLocalityMatches));
	}
}

fclose($resOutputFile);

exit(0);
?>