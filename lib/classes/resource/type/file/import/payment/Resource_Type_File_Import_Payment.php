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
	
	static public function preProcessFiles($mFileImport=null)
	{
		$iFileImportId	= ORM::extractId($mFileImport);
		$aFileImports	= array();
		if ($iFileImportId)
		{
			// Use the provided File Import Id
			$aFileImports[$iFileImportId]	= File_Import::getForId($iFileImportId);
		}
		else
		{
			// Get a full list of File Import Ids that need to be pre-processed
			$sFullListSQL	= "	SELECT	fi.*
								FROM	FileImport fi
										JOIN CarrierModule cm ON (
																	fi.Status = ".FILE_IMPORTED."
																	AND fi.FileType = cm.FileType
																	AND cm.Type = ".self::CARRIER_MODULE_TYPE."
																	AND cm.Carrier = fi.Carrier
																	AND cm.Active = 1
																)
								WHERE	1";
			$oFullListQuery	= new Query();
			if (false === ($mFullListResult = $oFullListQuery->Execute($sFullListSQL)))
			{
				throw new Exception($oFullListQuery->Error());
			}
			while ($aFileImport = $mFullListResult->fetch_assoc())
			{
				$aFileImports[$aFileImport['Id']]	= new File_Import($aFileImport);
			}
		}
		
		// Pre-Process each File
		foreach ($aFileImports as $iFileImportId=>$oFileImport)
		{
			try
			{
				// Get the Carrier Module
				$oCarrierModule	= Carrier_Module::getForDefinition(self::CARRIER_MODULE_TYPE, $oFileImport->FileType, $oFileImport->Carrier);
				
				// Initialise the Importer Class
				$sImporterClass	= $oCarrierModule->Module;
				$oImporter		= new $sImporterClass($oCarrierModule, $oFileImport);
				
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
				
				// Save back to the Database
				$oFileImport->Status		= FILE_NORMALISED;	// Not REALLY "normalised", but it doesn't really matter
				$oFileImport->NormalisedOn	= date('Y-m-d H:i:s');
				$oFileImport->save();
			}
			catch (Exception $oException)
			{
				// TODO: Mark the File as Normalisation Failed or something...
				throw $oException;
			}
		}
	}
	
	static public function processFiles()
	{
		
	}
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>