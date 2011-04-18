<?php

class JSON_Handler_Adjustment_Type extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Adjustment_Type::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Adjustment_Type::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
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
	
	public function getAll($bActiveOnly=false)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			$aRecords 	= Adjustment_Type::getAll();
			$aTypes		= array();
			foreach ($aRecords as $oRecord)
			{
				if (!$bActiveOnly || ($oRecord->isActive()))
				{
					$oStdClass							= $oRecord->toStdClass();
					$oStdClass->transaction_nature_code	= Transaction_Nature::getForId($oRecord->transaction_nature_id)->code;
					$aTypes[$oRecord->id] 				= $oStdClass;
				}
			}
			return array('bSuccess' => true, 'aAdjustmentTypes' => $aTypes);
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getSystemAdjustmentType($iSystemAdjustmentTypeId)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			$oAdjustmentType = Adjustment_Type_System_Config::getAdjustmentTypeForSystemAdjustmentType($iSystemAdjustmentTypeId);
			return array('bSuccess' => true, 'oAdjustmentType' => $oAdjustmentType->toStdClass());
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function archiveAdjustmentType($iAdjustmentTypeId)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			Adjustment_Type::getForId($iAdjustmentTypeId)->archive();
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
	
	public function createAdjustmentType($oDetails)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		try
		{
			$aErrors = array();
			
			if ($oDetails->code === '')
			{
				$aErrors[] = 'No Code was supplied';
			}
			
			if ($oDetails->description === '')
			{
				$aErrors[] = 'No Description was supplied';
			}
			
			if ($oDetails->amount === '')
			{
				$aErrors[] = 'No Amount was supplied';
			}
			else if (!is_numeric($oDetails->amount))
			{
				$aErrors[] = 'Invalid Amount was supplied';
			}
						
			if ($oDetails->transaction_nature_id === null)
			{
				$aErrors[] = 'No Nature was supplied';
			}
			
			if ($oDetails->adjustment_type_invoice_visibility_id === null)
			{
				$aErrors[] = 'No Visibility was supplied';
			}
			
			if ($oDetails->is_amount_fixed === null)
			{
				$aErrors[] = 'No Fixation was supplied';
			}
			
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => false, 'aErrors' => $aErrors);
			}
			
			$oAdjustmentType = new Adjustment_Type(get_object_vars($oDetails));
			$oAdjustmentType->save();
			
			return array('bSuccess' => true, 'iAdjustmentTypeId' => $oAdjustmentType->id);
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

class JSON_Handler_Adjustment_Type_Exception extends Exception
{
	// No changes
}

?>