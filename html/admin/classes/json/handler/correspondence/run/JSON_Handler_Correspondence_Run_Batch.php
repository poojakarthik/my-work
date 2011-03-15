<?php

class JSON_Handler_Correspondence_Run_Batch extends JSON_Handler
{
	protected	$_JSONDebug	= '';

	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}

	public function getAll($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		try
		{
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Run_Exception('You do not have permission to view Correspdondence Runs.');
			}

			$iMinDate		= ($oFilter->batch_datetime->mFrom ? strtotime($oFilter->batch_datetime->mFrom) : null);
			$iMaxDate		= ($oFilter->batch_datetime->mTo ? strtotime($oFilter->batch_datetime->mTo) : null);

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
				$oLogic						= new Correspondence_Logic_Run_Batch($oBatch);
				$oStdBatch					= $oBatch->toStdClass();
				$aDispatchLogic				= $oLogic->getDispatchRecords();
				$oStdBatch->dispatch_data	= array();
				foreach ($aDispatchLogic as $oCorrespondenceRunDispatch)
				{
					$aData							= $oCorrespondenceRunDispatch->getFileInfo();
					$aData['correspondence_run_id']	= $oCorrespondenceRunDispatch->correspondence_run_id;
					$aData['correspondence_totals']	= $oCorrespondenceRunDispatch->getCorrespondenceTotalsPerDeliveryMethod();
					$oStdBatch->dispatch_data[]		= $aData;
				}
				
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
			$sMessage	= $oException->getMessage();
			return 	array(
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage
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
}

class JSON_Handler_Correspondence_Run_Batch_Exception extends Exception
{
	// No changes
}

?>