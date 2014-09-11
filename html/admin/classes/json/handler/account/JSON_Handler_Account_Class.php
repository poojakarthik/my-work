<?php

class JSON_Handler_Account_Class extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Account_Class::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Account_Class::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
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
	
	public function getForId($iAccountClassId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oAccountClass = Account_Class::getForId($iAccountClassId);
			return array('bSuccess' => true, 'oAccountClass' => ($oAccountClass ? $oAccountClass->toStdClass() : null));
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function getAll($bActiveOnly=false, $aMustIncludeIds=null)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aMustIncludeIds	= ($aMustIncludeIds === null ? array() : $aMustIncludeIds);
			$aRecords 			= Account_Class::getAll();
			$aClasses			= array();
			foreach ($aRecords as $oRecord)
			{
				if (!$bActiveOnly || $oRecord->isActive() || in_array($oRecord->id, $aMustIncludeIds))
				{
					$aClasses[$oRecord->id] = $oRecord->toStdClass();
				}
			}
			return array('bSuccess' => true, 'aClasses' => $aClasses);
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function createClass($oDetails)
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
			
			if ($oDetails->collection_scenario_id === '')
			{
				$aErrors[] = "No Scenario supplied.";
			}
			else if (!Collection_Scenario::getForId($oDetails->collection_scenario_id))
			{
				$aErrors[] = "Invalid Scenario supplied.";
			}
			
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => true, 'aErrors' => $aErrors);
			}
			
			// Save the review outcome
			$oAccountClass 				= new Account_Class(get_object_vars($oDetails));
			$oAccountClass->status_id	= STATUS_ACTIVE;
			$oAccountClass->save();
			
			return array('bSuccess' => true, 'iClassId' => $oAccountClass->id);
		}
		catch (Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> ($bUserIsGod ? $oEx->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.')
					);
		}
	}
	
	public function setStatus($iAccountClassId, $iStatusId)
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Check if (going inactive) the account class is being used and which customer groups are using it
			if ($iStatusId == STATUS_INACTIVE)
			{
				$aCustomerGroups = Customer_Group::getForDefaultAccountClassId($iAccountClassId);
				if (count($aCustomerGroups) > 0)
				{
					$aArrays = array();
					foreach ($aCustomerGroups as $oCustomerGroup)
					{
						$aArrays[$oCustomerGroup->Id] = $oCustomerGroup->toArray();
					}
					return array('bSuccess' => false, 'aCustomerGroups' => $aArrays);
				}
			}
			
			// Update the status
			$oAccountClass 				= Account_Class::getForId($iAccountClassId);
			$oAccountClass->status_id 	= $iStatusId;
			$oAccountClass->save();
			
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

class JSON_Handler_Account_Class_Exception extends Exception
{
	// No changes
}

?>