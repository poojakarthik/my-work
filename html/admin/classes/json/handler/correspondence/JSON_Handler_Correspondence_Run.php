<?php

class JSON_Handler_Correspondence_Run extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	public function scheduleRunFromSQLTemplate($iCorrespondenceTemplateId, $sScheduleDateTime, $bProcessNow)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Run_Exception('You do not have permission to create Correspdondence Runs.');
			}
			
			// Validate input before proceeding
			$aErrors	= array();

			// Delivery date time
			$iDeliveryDateTime	= null;
			if (is_null($sScheduleDateTime))
			{
				// Missing
				$aErrors[]	= 'No delivery date time supplied.';
			}
			else
			{
				// Given, validate the date string (should be Y-m-d H:i:s)
				$iDeliveryDateTime	= strtotime($sScheduleDateTime);
				if ($iDeliveryDateTime === false)
				{
					// Invalid date string
					$aErrors[]	= "Invalid delivery date time supplied ('".$sScheduleDateTime."').";
				}
			}

			// Correspondence_Template id
			$oTemplateORM	= null;
			if (is_null($iCorrespondenceTemplateId))
			{
				// Missing
				$aErrors[]	= 'No Correspondence Template Id supplied.';
			}
			else
			{
				try
				{
					// Try and load it
					$oTemplateORM	= Correspondence_Template::getForId($iCorrespondenceTemplateId);

					// All good
					$iCorrespondenceTemplateId	= (int)$iCorrespondenceTemplateId;
				}
				catch (Exception $oEx)
				{
					// Invalid
					$aErrors[]	= "Invalid Correspondence Template Id supplied (".($iCorrespondenceTemplateId == '' ? 'Not supplied' : "'{$iCorrespondenceTemplateId}'").")";
				}
			}

			// Process now
			if (is_null($bProcessNow))
			{
				// Missing
				$aErrors[]	= "Please specify whether to process the SQL template now or at time of delivery.";
			}

			if (count($aErrors) > 0)
			{
				// Validation errors, return
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors,
					 		'sMessage'	=> 'There were errors in the form information.'
						);
			}

			$oTemplate	= Correspondence_Logic_Template::getForId($iCorrespondenceTemplateId);
			try
			{
				$oTemplate->createRun(false, date('Y-m-d H:i:s', $iDeliveryDateTime), $bProcessNow);
			} 
			catch (Correspondence_DataValidation_Exception $oEx)
			{
				// Invalid CSV file, build an error message
				$oEx->sFileName	= basename($oEx->sFileName);
				return 	array(
							'bSuccess'		=> false,
							'oException'	=> $oEx
						);
			}

			// If no exceptions were thrown, then everything worked
			return 	array(
						'bSuccess'	=> true
					);
		}
		catch (JSON_Handler_Correspondence_Run_Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		try
		{
			$aFilter	= get_object_vars($oFilter);
			$aSort		= get_object_vars($oSort);
			
			// Manipulate filter if status is filtered
			if (isset($aFilter['status']))
			{
				switch ($aFilter['status'])
				{
					case 'SUBMITTED':
						// a.k.a Having not yet been processed
						$aFilter['processed_datetime']	= 'NULL';
						break;
					case 'PROCESSED':
						// a.k.a Having been processd but not delivered
						$aFilter['processed_datetime']	= 'NOT NULL';
						$aFilter['delivered_datetime']	= 'NULL';
						break;
					case 'PROCESSING_FAILED':
						// a.k.a Having an error
						$aFilter['correspondence_run_error_id']	= 'NOT NULL';
						break;
					case 'DISPATCHED':
						// a.k.a Having been delivered
						$aFilter['delivered_datetime']	= 'NOT NULL';
						break;
				}
				unset($aFilter['status']);
			}
			
			$iRecordCount	= Correspondence_Run::getLedgerInformation(true, null, null, $aFilter, $aSort);
			if ($bCountOnly)
			{
				return	array(
							'Success'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= is_null($iLimit) ? 0 : $iLimit;
			$iOffset	= is_null($iOffset) ? 0 : $iOffset;
			$aRuns		= Correspondence_Run::getLedgerInformation(false, $iLimit, $iOffset, $aFilter, $aSort);
			$i			= 0;
			$aResults	= array();
			foreach ($aRuns as $aRun)
			{
				// Nullify end of time delivered datetime
				if ($aRun['delivered_datetime'] == Data_Source_Time::END_OF_TIME)
				{
					$aRun['delivered_datetime']	= null;
				}
				
				// Get the source of the run data
				$oSource	= Correspondence_Source::getForId($aRun['correspondence_template_source_id']);
				switch ($oSource->correspondence_source_type_id)
				{
					case CORRESPONDENCE_SOURCE_TYPE_SQL:
						$aRun['source'] = 'SQL';
						break;
						
					case CORRESPONDENCE_SOURCE_TYPE_SYSTEM:
						$aRun['source'] = 'System';
						break;
						
					case CORRESPONDENCE_SOURCE_TYPE_CSV:
						$aRun['source']	= 'CSV';
						break;
					
					default:
						$aRun['source']	= null;
				}
				
				$aResults[$iOffset + $i]	= $aRun;
				$i++;
			}
			
			return	array(
						'Success'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (JSON_Handler_Correspondence_Run_Exception $oException)
		{
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $oException->getMessage(),
						'Message'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage
					);
		}
	}
	
	public function getAllBatches($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Run_Exception('You do not have permission to view Correspdondence Runs.');
			}
			
			$iMinDate	= ($oFilter->batch_datetime->mFrom ? strtotime($oFilter->batch_datetime->mFrom) : null);
			$iMaxDate	= ($oFilter->batch_datetime->mTo ? strtotime($oFilter->batch_datetime->mTo) : null);
			
			$iRecordCount	= Correspondence_Run_Batch::getForBatchDateTime(true, null, null, $iMinDate, $iMaxDate);
			if ($bCountOnly)
			{
				return	array(
							'Success'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= is_null($iLimit) ? 0 : $iLimit;
			$iOffset	= is_null($iOffset) ? 0 : $iOffset;
			$aBatches	= Correspondence_Run_Batch::getForBatchDateTime(false, $iLimit, $iOffset, $iMinDate, $iMaxDate);
			$i			= 0;
			$aResults	= array();
			foreach ($aBatches as $oBatch)
			{
				// Add the correspondence runs for the batch to the std class object
				$oStdBatch	= $oBatch->toStdClass();
				$oStdBatch->aCorrespondenceRuns	= Correspondence_Logic_Run::getForBatchId($oBatch->id, true);
				
				// Cache for the result
				$aResults[$iOffset + $i]	= $oStdBatch;
				$i++;
			}
			
			return	array(
						'Success'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (JSON_Handler_Correspondence_Run_Exception $oException)
		{
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function getForId($iId)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Run_Exception('You do not have permission to view Correspdondence Runs.');
			}
			
			$oRun	= Correspondence_Logic_Run::getForId($iId, true);
			$aRun	= $oRun->toArray();
			
			// Get the source of the run data
			$oTemplate	= Correspondence_Template::getForId($oRun->correspondence_template_id);
			$oSource	= Correspondence_Source::getForId($oTemplate->correspondence_source_id);
			switch ($oSource->correspondence_source_type_id)
			{
				case CORRESPONDENCE_SOURCE_TYPE_SQL:
					$aRun['source'] = 'SQL';
					break;
					
				case CORRESPONDENCE_SOURCE_TYPE_SYSTEM:
					$aRun['source'] = 'System';
					break;
					
				case CORRESPONDENCE_SOURCE_TYPE_CSV:
					$aRun['source']	= 'CSV';
					break;
				
				default:
					$aRun['source']	= null;
			}
			
			// Get the file import/export names
			$oFileImport	= File_Import::getForId($oRun->file_import_id);
			if ($oFileImport)
			{
				$aRun['import_file_name']	= $oFileImport->FileName;
			}
			$oFileExport	= File_Export::getForId($oRun->data_file_export_id);
			if ($oFileExport)
			{
				$aRun['export_file_name']	= $oFileExport->FileName;
			}
			
			return	array(
						'bSuccess' 				=> true, 
						'oCorrespondenceRun' 	=> $aRun
					);
		}
		catch (JSON_Handler_Correspondence_Run_Exception $oException)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
}

class JSON_Handler_Correspondence_Run_Exception extends Exception
{
	// No changes
}

?>