<?php

// Framework
require_once('../../../lib/classes/Flex.php');
Flex::load();

$iCarrier			= (int)$argv[1];
$sImportFilename	= $argv[2];

// Load Carrier Details
if (!($oCarrier = Carrier::getForId($iCarrier)))
{
	throw new Exception("Unable to load Carrier with Id '{$iCarrier}'");
}

// Open CSV file
if (!file_exists($sImportFilename) || !is_readable($sImportFilename))
{
	throw new Exception("Unable to open file '{$sImportFilename}' for reading");
}

$oCSVFile	= new File_CSV();
$oCSVFile->setColumns(array('carrier-code', 'carrier-description', 'flex-code'));
$oCSVFile->importFile($sImportFilename, true);

$iTotal		= 0;
$iImported	= 0;
$iIgnored	= 0;

if (!DataAccess::getDataAccess()->TransactionStart())
{
	throw new Exception("Unable to start a Transaction");
}

try
{
	$aInsertColumns		=	array
							(
								'code'			=> null,
								'carrier_id'	=> $oCarrier->Id,
								'carrier_code'	=> null,
								'description'	=> null
							);
	$oInsertTranslation	= new StatementInsert("cdr_call_type_translation", $aInsertColumns);
	$oMatchTranslation	= new StatementSelect("cdr_call_type_translation", "*", "carrier_id = <carrier_id> AND carrier_code = <carrier_code>", null, 1);
	
	// Import each row
	foreach ($oCSVFile as $aRow)
	{
		$iTotal++;
		
		if ((int)$aRow['flex-code'])
		{
			// Import
			$aInsertColumns['code']			= trim($aRow['flex-code']);
			$aInsertColumns['carrier_code']	= trim($aRow['carrier-code']);
			$aInsertColumns['description']	= trim($aRow['carrier-description']);
			
			// Verify that there isn't already a translation for this Carrier/CarrierCode pair
			if ($oMatchTranslation->Execute($aInsertColumns))
			{
				throw new Exception("Unable to check [".implode('.', $aInsertColumns)."]! (".$oInsertTranslation->Error().")");
			}
			elseif ($aMatchedTranslation = $oMatchTranslation->Fetch())
			{
				if ($aMatchedTranslation['description'] !== $aRow['carrier-description'])
				{
					throw new Exception("Non-matching Translation already exists for [".implode('.', $aInsertColumns)."]! ([".implode('.', $aMatchedTranslation)."])");
				}
				else
				{
					// Ignore -- this exact translation already exists in the DB
					$iIgnored++;
					continue;
				}
			}
			
			// Insert Translation
			if ($oInsertTranslation->Execute($aInsertColumns) === false)
			{
				throw new Exception("Unable to insert [".implode('.', $aInsertColumns)."]! (".$oInsertTranslation->Error().")");
			}
			$iImported++;
		}
		else
		{
			// Ignore
			$iIgnored++;
		}
	}
	
	//throw new Exception("TEST MODE");
	
	// Commit
	if (!DataAccess::getDataAccess()->TransactionCommit())
	{
		throw new Exception("Unable to commit Transaction");
	}
}
catch (Exception $oException)
{
	// Rollback
	if (!DataAccess::getDataAccess()->TransactionRollback())
	{
		throw new Exception("Unable to rollback Transaction");
	}
	
	// Re-throw
	throw $oException;
}

Log::getLog()->log("[~] Imported: {$iImported}");
Log::getLog()->log("[~] Ignored: {$iIgnored}");
Log::getLog()->log("[~] Total Parsed: {$iTotal}");

exit(0);

?>