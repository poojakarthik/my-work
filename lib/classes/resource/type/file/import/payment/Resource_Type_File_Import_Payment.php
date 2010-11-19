<?php
/**
 * Resource_Type_File_Import_Payment
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Import_Payment
 */
abstract class Resource_Type_File_Import_Payment extends Resource_Type_File_Import
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_NORMALISATION_PAYMENT;
	
	static public function preProcessFiles($mFileImport=null, $iMaximumRecords=null)
	{
		$iFileImportId	= ORM::extractId($mFileImport);
		$aFileImports	= array();
		if ($iFileImportId)
		{
			// Use the provided File Import Id
			Log::getLog()->log("Using provided File Import Id #{$iFileImportId}");
			$aFileImports[$iFileImportId]	= File_Import::getForId($iFileImportId);
		}
		else
		{
			// Get a full list of File Imports that need to be pre-processed
			$iMaximumRecords	= ($iMaximumRecords === null) ? null : max(1, (int)$iMaximumRecords);
			Log::getLog()->log("Retrieving all Payment Files pending Import".(($iMaximumRecords) ? " (maximum: {$iMaximumRecords}" : ''));
			$sFullListSQL	= "	SELECT	fi.*
								FROM	FileImport fi
										JOIN CarrierModule cm ON (
																	fi.Status = ".FILE_IMPORTED."
																	AND fi.FileType = cm.FileType
																	AND cm.Type = ".self::CARRIER_MODULE_TYPE."
																	AND cm.Carrier = fi.Carrier
																	AND cm.Active = 1
																)
								WHERE	1
								".(($iMaximumRecords) ? "LIMIT	{$iMaximumRecords}" : '');
			$oFullListQuery	= new Query();
			if (false === ($mFullListResult = $oFullListQuery->Execute($sFullListSQL)))
			{
				throw new Exception($oFullListQuery->Error());
			}
			while ($aFileImport = $mFullListResult->fetch_assoc())
			{
				$aFileImports[$aFileImport['Id']]	= new File_Import($aFileImport);
			}
			Log::getLog()->log("Retrieved ".count($aFileImports)." Payment Import Files for Importing");
		}
		
		// Pre-Process each File
		// TODO: Encase each File in a Transaction for DB failure
		$iProgress		= 0;
		$oStopwatch		= new Stopwatch();
		$oStopwatch->start();
		Log::getLog()->log("Importing ".count($aFileImports)." Payment Import Files");
		foreach ($aFileImports as $iFileImportId=>$oFileImport)
		{
			$iProgress++;
			Log::getLog()->log("({$iProgress}/".count($aFileImports).") Importing {$oFileImport->Id}");
			Log::getLog()->log("\t[ ] Resource Type: ".GetConstantDescription($oFileImport->FileType, 'resource_type'));
			
			try
			{
				// Get the Carrier Module
				$oCarrierModule	= Carrier_Module::getForDefinition(self::CARRIER_MODULE_TYPE, $oFileImport->FileType, $oFileImport->Carrier);
				
				// Initialise the Importer Class
				$sImporterClass	= $oCarrierModule->Module;
				$oImporter		= new $sImporterClass($oCarrierModule, $oFileImport);
				
				Log::getLog()->log("\t[ ] Module: {$sImporterClass}");
				
				// Get Records (optionally pre-processed) and insert them into the Database
				$aRecords	= $oImporter->getRecords();
				$iSequence	= 0;
				foreach ($aRecords as $sRecord)
				{
					$iSequence++;
					$oFileImportData					= new File_Import_Data();
					$oFileImportData->file_import_id	= $iFileImportId;
					$oFileImportData->data				= $sRecord;
					$oFileImportData->sequence			= $iSequence;
					$oFileImportData->save();
				}
				
				Log::getLog()->log("\t[+] Saved ".count($aRecords)." File Import Data Records");
				
				$oFileImport->Status		= FILE_NORMALISED;	// Not REALLY "normalised", but it doesn't really matter
				$oFileImport->NormalisedOn	= date('Y-m-d H:i:s');
			}
			catch (Exception $oException)
			{
				// Mark the File as Normalisation Failed
				Log::getLog()->log("\t[!] Import Error: ".$oException->getMessage());
				
				$oFileImport->Status	= FILE_NORMALISE_FAILED;
			}
			
			// Save back to the Database
			$oFileImport->save();
			Log::getLog()->log("\t[~] {$oFileImport->Id} saved with Status ".GetConstantDescription($oFileImport->Status, 'FileStatus')." in ".round($oStopwatch->lap(), 2)."s");
		}
		
		// TODO: Report?  Probably no need
		Log::getLog()->log("Imported ".count($aFileImports)." Payment Import Files in ".round($oStopwatch->split(), 2)."s");
	}
	
	static public function processRecords($mFileImportData=null, $iMaximumRecords=null)
	{
		$iFileImportDataId		= ORM::extractId($mFileImportData);
		$aFileImportDataRecords	= array();
		if ($iFileImportDataId)
		{
			// Use the provided File Import Data Id
			Log::getLog()->log("Using provided File Import Data Id #{$iFileImportDataId}");
			$aFileImportDataRecords[$iFileImportDataId]	= File_Import_Data::getForId($iFileImportDataId);
		}
		else
		{
			// Get a full list of File Import Data Records that need to be pre-processed
			$iMaximumRecords	= ($iMaximumRecords === null) ? null : max(1, (int)$iMaximumRecords);
			Log::getLog()->log("Retrieving all File Import Data Records from Payment Import Files pending Normalisation".(($iMaximumRecords) ? " (maximum: {$iMaximumRecords}" : ''));
			$sFullListSQL	= "	SELECT	fid.*
								FROM	file_import_data fid
										JOIN FileImport fi ON (fid.file_import_id = fi.Id)
										JOIN CarrierModule cm ON (
																	fi.Status = ".FILE_NORMALISED."
																	AND fi.FileType = cm.FileType
																	AND cm.Type = ".self::CARRIER_MODULE_TYPE."
																	AND cm.Carrier = fi.Carrier
																	AND cm.Active = 1
																)
								WHERE	1
								".(($iMaximumRecords) ? "LIMIT	{$iMaximumRecords}" : '');
			$oFullListQuery	= new Query();
			if (false === ($mFullListResult = $oFullListQuery->Execute($sFullListSQL)))
			{
				throw new Exception($oFullListQuery->Error());
			}
			while ($aFileImportData = $mFullListResult->fetch_assoc())
			{
				$aFileImportDataRecords[$aFileImportData['id']]	= new File_Import_Data($aFileImportData);
			}
			Log::getLog()->log("Retrieved ".count($aFileImportDataRecords)." Payment Import Data Records for Normalisation");
		}
		
		// Process each Record
		// TODO: Encase each Record in a Transaction for DB failure
		$iProgress		= 0;
		$oStopwatch		= new Stopwatch();
		$oStopwatch->start();
		$aFileImports	= array();
		Log::getLog()->log("Normalising ".count($aFileImportDataRecords)." Payment Import Records");
		foreach ($aFileImportDataRecords as $iFileImportDataId=>$oFileImportData)
		{
			$iProgress++;
			Log::getLog()->log("({$iProgress}/".count($aFileImportDataRecords).") Normalising {$oFileImportData->file_import_id}:{$iFileImportDataId}");
			
			// Ensure we're reusing the same File instances when appropriate
			if (!isset($aFileImports[$oFileImportData->file_import_id]))
			{
				// Get the Carrier Module
				$oCarrierModule	= Carrier_Module::getForDefinition(self::CARRIER_MODULE_TYPE, $oFileImport->FileType, $oFileImport->Carrier);
				
				// Initialise the Importer Class
				$sImporterClass	= $oCarrierModule->Module;
				$oImporter		= new $sImporterClass($oCarrierModule, $oFileImport);
				
				$aFileImports[$oFileImportData->file_import_id]	= array(
					'oFileImport'		=> File_Import::getForId($oFileImportData->file_import_id),
					'oCarrierModule'	=> $oCarrierModule,
					'oImporter'			=> $oImporter
				);
			}
			
			Log::getLog()->log("\t[ ] Module: {$aFileImports[$oFileImportData->file_import_id]['oCarrierModule']['Module']}");
			
			// Normalise
			try
			{
				$aORMObjects	= $oImporter->processRecord($oFileImportData->data);
				if (is_array($aORMObjects))
				{
					// ORM Objects returned -- save them to the DB
					foreach ($aORMObjects as $oORMObject)
					{
						if ($oORMObject instanceof ORM)
						{
							$oORMObject->save();
							Log::getLog()->log("\t[+] Saved ".get_class($oORMObject)." #{$oORMObject->id}");
						}
					}
					$oFileImportData->file_import_data_status_id	= FILE_IMPORT_DATA_STATUS_PROCESSED;
				}
				else
				{
					// No Data -- Mark as Ignored
					$oFileImportData->file_import_data_status_id	= FILE_IMPORT_DATA_STATUS_IGNORED;
				}
			}
			catch (Exception $oException)
			{
				// Error Normalising the Record
				Log::getLog()->log("\t[!] Normalisation Error: ".$oException->getMessage());
				
				$oFileImportData->file_import_data_status_id	= FILE_IMPORT_DATA_STATUS_NORMALISATION_FAILED;
			}
			
			// Update the Data Record
			$oFileImportData->save();
			Log::getLog()->log("\t[~] {$oFileImportData->file_import_id}:{$iFileImportDataId} saved with Status ".GetConstantDescription($oFileImportData->file_import_data_status_id, 'file_import_data_status')." in ".round($oStopwatch->lap(), 2)."s");
		}
		
		// TODO: Report?  Probably no need
		Log::getLog()->log("Normalised ".count($aFileImportDataRecords)." Payment Import Records in ".round($oStopwatch->split(), 2)."s");
	}
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>