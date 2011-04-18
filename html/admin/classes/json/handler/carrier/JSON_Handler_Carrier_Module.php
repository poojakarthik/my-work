<?php

class JSON_Handler_Carrier_Module extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAll($bActiveOnly=false)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aModules 		= Carrier_Module::getAll();
			$aStdModules	= array();
			foreach ($aModules as $oModule)
			{
				if (!$bActiveOnly || $oModule->isActive())
				{
					$oStdClass = $oModule->toStdClass();
					if ($oModule->customer_group)
					{
						$oStdClass->customer_group_name = Customer_Group::getForId($oModule->customer_group)->internal_name;
					}
					else
					{
						$oStdClass->customer_group_name = null;
					}
					$oStdClass->carrier_name				= Carrier::getForId($oModule->Carrier)->Name;
					$oStdClass->carrier_module_type_name	= Carrier_Module_Type::getForId($oModule->Type)->name;
					$aStdModules[$oModule->id] 				= $oStdClass;
				}
			}
			return array('bSuccess' => true, 'aModules' => $aStdModules);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try {
			$iRecordCount = Carrier_Module::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly) {
				return array('iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Carrier_Module::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			
			foreach ($aData as $aRecord) {
				$aResults[$i] = $aRecord;
				$i++;
			}
			
			return array(
				'aRecords'		=> $aResults,
				'iRecordCount'	=> $iRecordCount
			);
		} catch (Exception $oEx) {
			return self::_buildExceptionResponse($oEx);
		}
	}
	
	public function setModuleActive($iModuleId, $bActive)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oModule			= Carrier_Module::getForId($iModuleId);
			$oModule->Active	= ($bActive ? 1 : 0);
			$oModule->save();
			return	array(
						'bSuccess'	=> true
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getEditingDetailsForId($iModuleId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oModule 	= Carrier_Module::getForId($iModuleId);
			$oStdClass	= $oModule->toStdClass();
			return	array(
						'bSuccess'			=> true,
						'oCarrierModule'	=> $oStdClass
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getConfigForId($iModuleId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aConfig 	= Carrier_Module_Config::getForCarrierModule($iModuleId);
			$aStdConfig	= array();
			foreach ($aConfig as $iId => $oConfig)
			{
				$aStdConfig[$iId] = $oConfig->toStdClass();
			}
			
			return	array(
						'bSuccess'	=> true,
						'aConfig'	=> $aStdConfig
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getJSONFromSerialisedConfigValue($sSerialisedValue)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return	array(
						'bSuccess'	=> true,
						'mValue'	=> unserialize($sSerialisedValue)
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getSerialisedConfigJSONValue($mValue)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			return	array(
						'bSuccess'			=> true,
						'sSerialisedValue'	=> serialize($mValue)
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getConfigForModuleName($sModule)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$mConfig = null;
			if (class_exists($sModule) && method_exists($sModule, 'defineCarrierModuleConfig'))
			{
				$mConfig = call_user_func(array($sModule, 'defineCarrierModuleConfig'));
			}
			return	array(
						'bSuccess'	=> true,
						'aConfig'	=> $mConfig
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function saveModule($oDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Validate details
			$aErrors = array();
			
			if ($oDetails->carrier_id == '')
			{
				$aErrors[] = 'No Carrier was supplied.';
			}
			
			if ($oDetails->carrier_module_type_id == '')
			{
				$aErrors[] = 'No Type was Supplied.';
			}
			
			if ($oDetails->resource_type_id == '')
			{
				$aErrors[] = 'No File Type was Supplied.';
			}
			
			if ($oDetails->module == '')
			{
				$aErrors[] = 'No Module name was supplied.';
			}
			
			if ($oDetails->frequency_type == '')
			{
				$aErrors[] = 'No Frequency Type (i.e. Second/Minute/Hour/Day) was supplied.';
			}
			
			if ($oDetails->frequency == '')
			{
				$aErrors[] = 'No Frequency amount was supplied.';
			}
			
			if ($oDetails->earliest_delivery == '')
			{
				$aErrors[] = 'No Earliest Delivery time was supplied.';
			}
			
			if (count($aErrors) > 0)
			{
				return 	array(
							'bSuccess'	=> false,
							'aErrors'	=> $aErrors
						);
			}
			
			// Start DB transaction
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception("Failed to start db transaction.");
			}
			
			try
			{
				if ($oDetails->id)
				{
					// Editing
					$oCarrierModule 		= Carrier_Module::getForId($oDetails->id);
					$oCarrierModule->Active	= ($oDetails->active ? 1 : 0);
				}
				else
				{
					// New (start out disabled)
					$oCarrierModule 					= new Carrier_Module();
					$oCarrierModule->Carrier 			= $oDetails->carrier_id;
					$oCarrierModule->customer_group		= $oDetails->customer_group_id;
					$oCarrierModule->Type				= $oDetails->carrier_module_type_id;
					$oCarrierModule->Module				= $oDetails->module;
					$oCarrierModule->FileType			= $oDetails->resource_type_id;
					$oCarrierModule->LastSentOn			= '0000-00-00 00:00:00';
					$oCarrierModule->Active				= 0;
					
					$iToday 							= strtotime(date('Y-m-d 00:00:00'));
					$iEarliestDelivery					= strtotime(date("Y-m-d {$oDetails->earliest_delivery}"));
					$oCarrierModule->EarliestDelivery	= $iEarliestDelivery - $iToday;
				}
				
				$oCarrierModule->description		= $oDetails->description;
				$oCarrierModule->FrequencyType		= $oDetails->frequency_type;
				$oCarrierModule->Frequency			= $oDetails->frequency;
				$oCarrierModule->save();
				
				// Config
				foreach ($oDetails->config as $oRecord)
				{
					if ($oRecord->Id && ($oDetails->id !== null))
					{
						// Editing an existing module
						$oCarrierModuleConfig = Carrier_Module_Config::getForId($oRecord->Id);
					}
					else
					{
						// Either creating a new module or cloning one
						$oCarrierModuleConfig 					= new Carrier_Module_Config();
						$oCarrierModuleConfig->CarrierModule	= $oCarrierModule->Id;
						$oCarrierModuleConfig->Name				= $oRecord->Name;
						$oCarrierModuleConfig->Type				= (!!$oRecord->Type ? $oRecord->Type : DATA_TYPE_STRING);
						$oCarrierModuleConfig->Description		= $oRecord->Description; 
					}
					
					if (in_array($oCarrierModuleConfig->Type, array(DATA_TYPE_SERIALISED, DATA_TYPE_ARRAY)))
					{
						// Serialised or array type, unescape the value
						$oRecord->Value = unserialize($oRecord->Value);
					}
					
					$oCarrierModuleConfig->Value = $oRecord->Value;
					$oCarrierModuleConfig->save();
				}
			}
			catch (Exception $oEx)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception("Failed to rollback db transaction.");
				}
				
				throw $oEx;
			}
			
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception("Failed to commit db transaction.");
			}
			
			return	array(
						'bSuccess'	=> true
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function validateModuleName($sModule)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			if (!class_exists($sModule))
			{
				throw new JSON_Handler_Carrier_Module_Exception("Invalid class name");
			}
			
			if (!method_exists($sModule, 'defineCarrierModuleConfig'))
			{
				throw new JSON_Handler_Carrier_Module_Exception("Module cannot be used to create a Carrier Module via the Flex interface");
			}
			
			$iCarrierModuleTypeId 	= constant("{$sModule}::CARRIER_MODULE_TYPE");
			$iResourceTypeId 		= constant("{$sModule}::RESOURCE_TYPE");
			
			return	array(
						'bSuccess'				=> true,
						'iCarrierModuleTypeId'	=> $iCarrierModuleTypeId,
						'iResourceTypeId'		=> $iResourceTypeId
					);
		}
		catch (JSON_Handler_Carrier_Module_Exception $oEx)
		{
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $oEx->getMessage()
					);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
}

class JSON_Handler_Carrier_Module_Exception extends Exception
{
	// No changes
}

?>