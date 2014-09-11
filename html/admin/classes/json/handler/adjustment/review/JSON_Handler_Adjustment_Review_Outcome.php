<?php

class JSON_Handler_Adjustment_Review_Outcome extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Adjustment_Review_Outcome::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Adjustment_Review_Outcome::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			
			foreach ($aData as $aRecord)
			{
				$aResults[$i] = $aRecord;
				$i++;
			}
			
			return	array(
						'bSuccess'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (Exception $oEx)
		{
			$sMessage	= $bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getForAdjustmentReviewOutcomeType($iAdjustmentReviewOutcomeTypeId, $bActiveOnly=false)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aRecords 	= Adjustment_Review_Outcome::getForAdjustmentReviewOutcomeType($iAdjustmentReviewOutcomeTypeId);
			$aOutcomes	= array();
			foreach ($aRecords as $oRecord)
			{
				if (!$bActiveOnly || $oRecord->isActive())
				{
					$aOutcomes[$oRecord->id] = $oRecord->toStdClass();
				}
			}
			return array('bSuccess' => true, 'aOutcomes' => $aOutcomes);
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function createDeclined($oDetails)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Validation
			$aErrors = array();
			if ($oDetails->name === '')
			{
				$aErrors[] = "No Name supplied.";
			}
			else if (strlen($oDetails->name) > 256)
			{
				$aErrors[] = "Name must be less than 256 characters long.";
			}
			
			if ($oDetails->description === '')
			{
				$aErrors[] = "No Description supplied.";
			}
			else if (strlen($oDetails->description) > 256)
			{
				$aErrors[] = "Description must be less than 256 characters long.";
			}
			
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => true, 'aErrors' => $aErrors);
			}
			
			// Save the review outcome
			$oAdjustmentReviewOutcome 										= new Adjustment_Review_Outcome(get_object_vars($oDetails));
			$oAdjustmentReviewOutcome->status_id 							= STATUS_ACTIVE;
			$oAdjustmentReviewOutcome->adjustment_review_outcome_type_id	= ADJUSTMENT_REVIEW_OUTCOME_TYPE_DECLINED;
			$oAdjustmentReviewOutcome->save();
			
			return array('bSuccess' => true, 'iOutcomeId' => $oAdjustmentReviewOutcome->id);
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function setStatus($iAdjustmentId, $iStatusId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Update the status
			$oAdjustmentReviewOutcome 				= Adjustment_Review_Outcome::getForId($iAdjustmentId);
			$oAdjustmentReviewOutcome->status_id 	= $iStatusId;
			$oAdjustmentReviewOutcome->save();
			
			return array('bSuccess' => true);
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
}

class JSON_Handler_Adjustment_Review_Outcome_Exception extends Exception
{
	// No changes
}

?>