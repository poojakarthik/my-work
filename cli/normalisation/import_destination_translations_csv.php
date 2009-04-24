<?php

// Flex Framework
require_once("../../lib/classes/Flex.php");
Flex::load();

$arrImportColumns	=	array
						(
							'carrier_code'					=> 0,
							'carrier_description'			=> 1,
							'flex_code:flex_description'	=> 2,
							'can_import'					=> 3
						);

try
{
	$dsFlex	= Data_Source::get();
	$dsFlex->setFetchMode(MDB2_FETCHMODE_ASSOC);
	
	$dsFlex->beginTransaction();
	
	// Import
	$mixCarrier	= $argv[1];
	$strPath	= $argv[2];
	
	// Ensure Carrier is valid
	$resCarrierByConstant	= $dsFlex->query("SELECT * FROM Carrier WHERE const_name = ".$dsFlex->quote($mixCarrier, 'text')." LIMIT 1");
	if (PEAR::isError($resCarrierByConstant))
	{
		throw new Exception($resCarrierByConstant->getMessage()."\n\n".$resCarrierByConstant->getUserInfo());
	}
	elseif ($resCarrierByConstant->numRows())
	{
		$arrCarrier	= $resCarrierByConstant->fetchRow();
	}
	else
	{
		$resCarrierById	= $dsFlex->query("SELECT * FROM Carrier WHERE Id = ".$dsFlex->quote($mixCarrier, 'integer')." LIMIT 1");if (PEAR::isError($resCarrierByConstant))
		{
			throw new Exception($resCarrierById->getMessage()."\n\n".$resCarrierById->getUserInfo());
		}
		elseif ($resCarrierById->numRows())
		{
			$arrCarrier	= $resCarrierById->fetchRow();
		}
		else
		{
			throw new Exception("Parameter 1 ({$mixCarrier}) is not a valid Carrier Id or Constant Name");
		}
	}
	
	if (!file_exists($strPath))
	{
		throw new Exception("Parameter 2, Path {$strPath} does not exist");
	}
	elseif (!($resInputFile = @fopen($strPath, 'r')))
	{
		throw new Exception("Unable to open Path {$strPath} for reading: ".error_get_last());
	}
	
	$strErrorTail	= " in File {$strPath}";
	
	$intLine	= 0;
	
	$arrErrors	= array();
	
	// Parse Header
	$intLine++;
	if (!$arrHeader = fgetcsv($resInputFile))
	{
		throw new Exception("Unable to parse File Header {$strErrorTail}");
	}
	foreach ($arrImportColumns as $strAlias=>$intColumn)
	{
		try
		{
			if (!array_key_exists($intColumn, $arrHeader))
			{
				throw new Exception("Header column at Index {$intColumn} does not exist {$strErrorTail}");
			}
			elseif ($arrHeader[$intColumn] !== $strAlias)
			{
				throw new Exception("Header column at Index {$intColumn} expected '{$strAlias}'; found '{$arrHeader[$intColumn]}' {$strErrorTail}");
			}
			else
			{
				//echo "\t[+] Header Match ('{$strAlias}' === '{$arrHeader[$intColumn]}')\n";
			}
		}
		catch (Exception $eException)
		{
			$arrErrors[]	= $eException->getMessage();
		}
	}
	
	// Parse Data
	$intSuccess	= 0;
	while ($arrData = fgetcsv($resInputFile))
	{
		$intLine++;
		
		$arrFlexDestination	= explode(':', $arrData[$arrImportColumns['flex_code:flex_description']]);
		$intFlexCode		= (int)$arrFlexDestination[0];
		$intCanImport		= (int)$arrData[$arrImportColumns['carrier_code']];
		
		if (!$intFlexCode)
		{
			Log::getLog()->log("Skipping '{$arrData[$arrImportColumns['carrier_description']]}'({$arrData[$arrImportColumns['carrier_code']]}) (No Flex Code Specified)");
			continue;
		}
		
		try
		{
			$arrDestinationTranslation	= array(
													'code'			=> $intFlexCode,
													'carrier_id'	=> (int)$arrCarrier['Id'],
													'carrier_code'	=> trim($arrData[$arrImportColumns['carrier_code']]),
													'description'	=> trim($arrData[$arrImportColumns['carrier_description']])
												);
			
			// Ensure this is not a duplicate
			$resDuplicate	= $dsFlex->query("SELECT * FROM cdr_call_type_translation WHERE carrier_id = ".$dsFlex->quote($intCarrier, 'integer')." AND carrier_code = ".$dsFlex->quote($arrDestinationTranslation['carrier_code'], 'text')." LIMIT 1");
			if (PEAR::isError($resDuplicate))
			{
				throw new Exception($resDuplicate->getMessage()."\n\n".$resDuplicate->getUserInfo());
			}
			elseif ($resDuplicate->numRows())
			{
				$arrDuplicate	= $resDuplicate->fetchRow();
				
				if ($intCanImport)
				{
					throw new Exception("Destination Translation for Carrier {$arrDestinationTranslation['carrier_id']}/Code {$arrDestinationTranslation['carrier_code']} already exists with Id {$arrDuplicate['id']}");
				}
				else
				{
					Log::getLog()->log("Skipping '{$arrData[$arrImportColumns['carrier_description']]}'({$arrData[$arrImportColumns['carrier_code']]}) (Already in Flex)");
				}
			}
			elseif (!$intCanImport)
			{
				throw new Exception("Destination Translation for Carrier {$arrDestinationTranslation['carrier_id']}/Code {$arrDestinationTranslation['carrier_code']} should already exist, but does not");
			}
			
			if ($intCanImport)
			{
				// Insert into the DB
				$strInsertSQL	= "	INSERT INTO	cdr_call_type_translation
										(code, carrier_id, carrier_code, description)
									VALUES
										(
											".$dsFlex->quote($arrDestinationTranslation['code']			, 'integer').", 
											".$dsFlex->quote($arrDestinationTranslation['carrier_id']	, 'integer').", 
											".$dsFlex->quote($arrDestinationTranslation['carrier_code']	, 'text').", 
											".$dsFlex->quote($arrDestinationTranslation['description']	, 'text')."
										);";
				$resInsert	= $dsFlex->exec($strInsertSQL);
				if (PEAR::isError($resInsert))
				{
					throw new Exception($resInsert->getMessage()."\n\n".$resInsert->getUserInfo());
				}
				$intSuccess++;
			}
		}
		catch (Exception $eException)
		{
			$arrErrors[]	= $eException->getMessage()." @ Line {$intLine}";
		}
	}
	
	if (count($arrErrors))
	{
		throw new Exception(count($arrErrors)." Fatal Errors were encountered {$strErrorTail}.  No data has been imported into Flex.\n\n".implode("\n", $arrErrors)."\n");
	}
	
	throw new Exception("TEST MODE");
	
	// Everything looks good, so Commit
	$dsFlex->commit();
}
catch (Exception $eException)
{
	// Rollback & Die ungracefully
	$dsFlex->rollback();
	throw $eException;
}

?>