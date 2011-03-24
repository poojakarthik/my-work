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

	static public function preProcessFiles($mFileImport=null, $iMaximumRecords=null) {
		$oStopwatch	= new Stopwatch();
		$oStopwatch->start();

		// Get FileImport Records
		//--------------------------------------------------------------------//
		$aFileImports	= array();
		$iFileImportId	= ORM::extractId($mFileImport);
		if ($iFileImportId) {
			// Use the provided File Import Id
			Log::getLog()->log("Using provided File Import Id #{$iFileImportId}");
			$aFileImports[$iFileImportId]	= File_Import::getForId($iFileImportId);
		} else {
			// Get a full list of File Imports that need to be pre-processed
			// FIXME: This may be able to be abstracted into a File_Import static method
			$iMaximumRecords	= ($iMaximumRecords === null) ? null : max(1, (int)$iMaximumRecords);
			Log::getLog()->log("Retrieving all Payment Files pending Import".(($iMaximumRecords) ? " (maximum: {$iMaximumRecords})" : ''));
			$mFileImportsResult	= Query::run("
				SELECT	fi.*
				FROM	FileImport fi
						JOIN CarrierModule cm ON (
													fi.Status = ".FILE_COLLECTED."
													AND fi.FileType = cm.FileType
													AND cm.Type = ".self::CARRIER_MODULE_TYPE."
													AND cm.Carrier = fi.Carrier
													AND cm.Active = 1
												)
				WHERE	1
				LIMIT	<maximum-records>
			", array(
				'maximum-records'	=> $iMaximumRecords ? $iMaximumRecords : PHP_INT_MAX	// This is the only way to do LIMIT with no limit
			));
			
			while ($aFileImport = $mFileImportsResult->fetch_assoc()) {
				$aFileImports[$aFileImport['Id']]	= new File_Import($aFileImport);
			}
			Log::getLog()->log("Retrieved ".count($aFileImports)." Payment Import Files for pre-Processing in ".round($oStopwatch->split(), 4).'s');
		}

		// pre-Process each File
		//--------------------------------------------------------------------//
		$iProgress		= 0;
		$oStopwatch->start();
		Log::getLog()->log("Pre-Processing ".count($aFileImports)." Payment Import Files");
		foreach ($aFileImports as $iFileImportId=>$oFileImport) {
			$iProgress++;
			Log::getLog()->log("({$iProgress}/".count($aFileImports).") Importing {$oFileImport->Id}");
			Log::getLog()->log("\t[ ] Resource Type: ".GetConstantDescription($oFileImport->FileType, 'resource_type'));
			
			// Process each FileImport within a Transaction
			DataAccess::getDataAccess()->TransactionStart(false);
			try {
				// Get the Carrier Module
				$oCarrierModule	= reset(Carrier_Module::getForDefinition(self::CARRIER_MODULE_TYPE, $oFileImport->FileType, $oFileImport->Carrier));
				if (!$oCarrierModule) {
					throw new Exception("No Carrier Module found for definition [".GetConstantName('carrier_module_type', self::CARRIER_MODULE_TYPE)."|".GetConstantName('resource_type', $oFileImport->FileType)."|".GetConstantName('Carrier', $oFileImport->Carrier)."]");
				}

				// Initialise the Importer Class
				$sImporterClass	= $oCarrierModule->Module;
				Log::getLog()->log("\t[ ] Module: {$sImporterClass}");
				Flex::assert(
					is_subclass_of($sImporterClass, __CLASS__),
					"Carrier Module #{$oCarrierModule->Id}'s Class '{$sImporterClass}' does not inherit from ".__CLASS__,
					array(
						'oFileImport'		=> $oFileImport->toArray(),
						'oCarrierModule'	=> $oCarrierModule->toArray()
					),
					"Payment pre-Processing: Carrier Module with Invalid Class"
				);
				$oImporter		= new $sImporterClass($oCarrierModule, $oFileImport);

				// Get Records (optionally pre-processed) and insert them into the Database
				$aRecords	= $oImporter->getRecords();
				foreach ($aRecords as $mSequence=>$sRecord) {
					File_Import_Data::create(array(
						'file_import_id'				=> $iFileImportId,
						'data'							=> $sRecord,
						'sequence_no'					=> $mSequence,
						'file_import_data_status_id'	=> FILE_IMPORT_DATA_STATUS_IMPORTED
					))->save();
				}

				Log::getLog()->log("\t[+] Saved ".count($aRecords)." File Import Data Records");

				$oFileImport->Status		= FILE_IMPORTED;
				$oFileImport->NormalisedOn	= date('Y-m-d H:i:s');
			} catch (Exception_Database $oException) {
				// Rollback and pass through
				DataAccess::getDataAccess()->TransactionRollback(false);
				throw $oException;
			} catch (Exception $oException) {
				// Import Error -- Mark the File as Normalisation Failed
				Log::getLog()->log("\t[!] Import Error: ".$oException->getMessage());
				$oFileImport->Status	= FILE_IMPORT_FAILED;
			}

			// Save back to the Database
			$oFileImport->save();
			Log::getLog()->log("\t[~] {$oFileImport->Id} saved with Status ".GetConstantDescription($oFileImport->Status, 'FileStatus')." in ".round($oStopwatch->lap(), 2)."s");

			// Commit
			DataAccess::getDataAccess()->TransactionCommit(false);
		}

		// TODO: Report?  Probably no need
		Log::getLog()->log("Imported ".count($aFileImports)." Payment Import Files in ".round($oStopwatch->split(), 2)."s");
	}
	
	static public function processRecords($mFileImportData=null, $iMaximumRecords=null)
	{
		$oStopwatch	= new Stopwatch();
		$oStopwatch->start();

		// Get file_import_data Records
		//--------------------------------------------------------------------//
		$aFileImportDataRecords	= array();
		$iFileImportDataId		= ORM::extractId($mFileImportData);
		if ($iFileImportDataId) {
			// Use the provided File Import Data Id
			Log::getLog()->log("Using provided File Import Data Id #{$iFileImportDataId}");
			$aFileImportDataRecords[$iFileImportDataId]	= File_Import_Data::getForId($iFileImportDataId);
		}
		else
		{
			// Get a full list of File Import Data Records that need to be pre-processed
			$iMaximumRecords	= ($iMaximumRecords === null) ? null : max(1, (int)$iMaximumRecords);
			Log::getLog()->log("Retrieving all File Import Data Records from Payment Import Files pending Normalisation".(($iMaximumRecords) ? " (maximum: {$iMaximumRecords})" : ''));
			$mFileImportDataResult	= Query::run("
				SELECT	fid.*
				FROM	file_import_data fid
						JOIN FileImport fi ON (
							fid.file_import_id = fi.Id
							AND fi.Status = ".FILE_IMPORTED."
							AND fid.file_import_data_status_id = ".FILE_IMPORT_DATA_STATUS_IMPORTED."
						)
						JOIN CarrierModule cm ON (
							fi.FileType = cm.FileType
							AND cm.Type = ".self::CARRIER_MODULE_TYPE."
							AND cm.Carrier = fi.Carrier
							AND cm.Active = 1
						)
				WHERE	1
				LIMIT	<maximum-records>
			", array(
				'maximum-records'	=> $iMaximumRecords ? $iMaximumRecords : PHP_INT_MAX	// This is the only way to do LIMIT with no limit
			));
			
			while ($aFileImportData = $mFileImportDataResult->fetch_assoc()) {
				$aFileImportDataRecords[$aFileImportData['id']]	= new File_Import_Data($aFileImportData);
			}
			Log::getLog()->log("Retrieved ".count($aFileImportDataRecords)." Payment Import Data Records for Processing ".round($oStopwatch->split(), 2)."s");
		}
		
		// Process each Record
		//--------------------------------------------------------------------//
		$iProgress		= 0;
		$oStopwatch->start();
		$aFileImports	= array();
		Log::getLog()->log("Normalising ".count($aFileImportDataRecords)." Payment Import Records");
		foreach ($aFileImportDataRecords as $iFileImportDataId=>$oFileImportData) {
			$iProgress++;
			Log::getLog()->log("({$iProgress}/".count($aFileImportDataRecords).") Processing {$oFileImportData->file_import_id}:{$iFileImportDataId}");
			
			// Ensure we're reusing the same File instances when appropriate
			if (!isset($aFileImports[$oFileImportData->file_import_id]))
			{
				$oFileImport	= File_Import::getForId($oFileImportData->file_import_id);
				
				// Get the Carrier Module
				$oCarrierModule	= reset(Carrier_Module::getForDefinition(self::CARRIER_MODULE_TYPE, $oFileImport->FileType, $oFileImport->Carrier));
				
				// Initialise the Importer Class
				$sImporterClass	= $oCarrierModule->Module;
				Flex::assert(
					is_subclass_of($sImporterClass, __CLASS__),
					"Carrier Module #{$oCarrierModule->Id}'s Class '{$sImporterClass}' does not inherit from ".__CLASS__,
					array(
						'oFileImportData'	=> $oFileImportData->toArray(),
						'oCarrierModule'	=> $oCarrierModule->toArray()
					),
					"Payment Processing: Carrier Module with Invalid Class"
				);
				$oImporter		= new $sImporterClass($oCarrierModule, $oFileImport);
				
				$aFileImports[$oFileImportData->file_import_id]	= array(
					'oFileImport'		=> $oFileImport,
					'oCarrierModule'	=> $oCarrierModule,
					'oImporter'			=> $oImporter
				);
			}
			
			Log::getLog()->log("\t[ ] Module: {$aFileImports[$oFileImportData->file_import_id]['oCarrierModule']->Module}");
			
			// Normalise each Record within a Transaction
			//----------------------------------------------------------------//
			DataAccess::getDataAccess()->TransactionStart(false);
			$sDBDatetime	= DataAccess::getDataAccess()->getNow();
			try {
				$oRecordStopwatch	= new Stopwatch(true);
				
				// Process the Record
				//------------------------------------------------------------//
				$aData				= $aFileImports[$oFileImportData->file_import_id]['oImporter']->processRecord($oFileImportData->data);
				$oPaymentResponse	= (isset($aData['oPaymentResponse'])) ? $aData['oPaymentResponse'] : null;
				$aTransactionData	= (isset($aData['aTransactionData'])) ? $aData['aTransactionData'] : array();
				Log::getLog()->log("\t[+] Processed in ".$oRecordStopwatch->lap(4).'s');

				// Save resulting data
				//------------------------------------------------------------//
				if ($oPaymentResponse instanceof Payment_Response) {

					// Validate the Payment Response
					if (!Account::getForId($oPaymentResponse->account_id)) {
						throw new Exception("Invalid Account: '{$oPaymentResponse->account_id}'");
					}
					Constant_Group::getConstantGroup('payment_type')->getConstantName($oPaymentResponse->payment_type_id);
					Constant_Group::getConstantGroup('payment_response_type')->getConstantName($oPaymentResponse->payment_response_type_id);
					if ($oPaymentResponse->payment_response_type_id	=== PAYMENT_RESPONSE_TYPE_REJECTION) {
						Payment_Reversal_Reason::getForId($oPaymentResponse->payment_reversal_reason_id);
					}

					// Save the Payment Response
					$oPaymentResponse->account_group_id				= Account::getForId($oPaymentResponse->account_id)->AccountGroup;
					$oPaymentResponse->file_import_data_id			= $iFileImportDataId;
					$oPaymentResponse->created_datetime				= $sDBDatetime;
					$oPaymentResponse->payment_response_status_id	= PAYMENT_RESPONSE_STATUS_PROCESSED;
					$oPaymentResponse->payment_reversal_type_id		= ($oPaymentResponse->payment_reversal_reason_id === null) ? null : Payment_Reversal_Reason::getForId($oPaymentResponse->payment_reversal_reason_id)->payment_reversal_type_id;

					//Log::getLog()->log(print_r($oPaymentResponse->toArray(), true));

					$oPaymentResponse->save();
					Log::getLog()->log("\t[+] Saved Payment_Response #{$oORMObject->id} in ".$oRecordStopwatch->lap(4).'s');

					// Save the Transaction Data
					foreach ($aTransactionData as $oTransactionData) {
						$oTransactionData->payment_response_id	= $oPaymentResponse->id;
						$oTransactionData->save();
					}
					Log::getLog()->log("\t[+] Saved Transaction Data #{$oORMObject->id} in ".$oRecordStopwatch->lap(4).'s');

					// Mark the Data as Processed
					$oFileImportData->file_import_data_status_id	= FILE_IMPORT_DATA_STATUS_NORMALISED;

					// Action the Payment Response
					$sPaymentLink	= ($oPaymentResponse->payment_id) ? 'pre-existing' : 'new';
					$oPaymentResponse->action();
					Log::getLog()->log("\t[+] Actioned Payment_Response #{$oPaymentResponse->id} against {$sPaymentLink} Payment #{$oPaymentResponse->payment_id} in ".$oRecordStopwatch->lap(4).'s');
				} else {
					// No Data -- Mark as Ignored
					$oFileImportData->file_import_data_status_id	= FILE_IMPORT_DATA_STATUS_IGNORED;
				}
			} catch (Exception_Database $oException) {
				// Database Error -- Transaction Rollback
				DataAccess::getDataAccess()->TransactionRollback(false);
				throw $oException;
			} catch (Exception $oException) {
				// Error Normalising the Record
				Log::getLog()->log("\t[!] Normalisation Error: ".$oException->getMessage());
				
				$oFileImportData->file_import_data_status_id	= FILE_IMPORT_DATA_STATUS_NORMALISATION_FAILED;
				$oFileImportData->reason						= $oException->getMessage();
			}
			
			// Update the Data Record
			$oFileImportData->save();
			Log::getLog()->log("\t[~] {$oFileImportData->file_import_id}:{$iFileImportDataId} saved with Status ".GetConstantDescription($oFileImportData->file_import_data_status_id, 'file_import_data_status')." in ".$oRecordStopwatch->split(4)."s");
			
			// Commit
			DataAccess::getDataAccess()->TransactionCommit(false);
		}
		
		// TODO: Report?  Probably no need
		Log::getLog()->log("Processed ".count($aFileImportDataRecords)." Payment Import Records in ".round($oStopwatch->split(), 2)."s");
	}

	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType) {
		if ($iCustomerGroup !== null) {
			throw new Exception(GetConstantName(self::CARRIER_MODULE_TYPE, 'carrier_module_type')." Carrier Modules cannot be Customer Group specific");
		}
		parent::createCarrierModule($iCarrier, null, $sClassName, $iResourceType, self::CARRIER_MODULE_TYPE);
	}
}
?>