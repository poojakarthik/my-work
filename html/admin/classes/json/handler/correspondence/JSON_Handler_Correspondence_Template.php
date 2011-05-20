<?php

class JSON_Handler_Correspondence_Template extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAll($bActiveOnly=false)
	{
		try
		{
			$aTemplates	= Correspondence_Template::getAll();
			$aResults	= array();
			foreach ($aTemplates as $oTemplate)
			{
				if (!$bActiveOnly || $oTemplate->isActive())
				{
					$aResults[$oTemplate->id]	= $oTemplate->toStdClass();
				}
			}
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"bSuccess"	=> true,
						"aResults"	=> $aResults 
					);
		}
		catch (JSON_Handler_Correspondence_Template_Exception $oException)
		{
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function getAllWithNonSystemSources()
	{
		try
		{
			// Proper admin required
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Template_Exception('You do not have permission to view Correspdondence Templates.');
			}
			
			// BEGIN: Get templates
			// TODO: Replace this with a call to an ORM/Logic object, this was done to enable development of the interface
			$oQuery		= new Query();
			$aResults	= array();
			$mResult	= $oQuery->Execute("SELECT	ct.id as correspondence_template_id
											FROM	correspondence_template ct
											JOIN	correspondence_source cs ON ct.correspondence_source_id = cs.id
											JOIN	correspondence_source_type cst ON cs.correspondence_source_type_id = cst.id
											WHERE	cst.system_name NOT IN ('SYSTEM', 'SQL_ACCOUNTS')
											AND		ct.status_id = ".STATUS_ACTIVE.";");
			if ($mResult === false)
			{
				throw new Exception_Database("Failed to get correspondence templates. ".$oQuery->Error());
			}
			
			while ($aRow = $mResult->fetch_assoc())
			{
				$iTemplateId			= $aRow['correspondence_template_id'];
				$aResults[$iTemplateId]	= Correspondence_Template::getForId($iTemplateId)->toStdClass();
			}
			// END: Get templates
			
			// If no exceptions were thrown, then everything worked
			return 	array(
						"bSuccess"					=> true,
						"aCorrespondenceTemplates"	=> $aResults 
					);
		}
		catch (JSON_Handler_Correspondence_Template_Exception $oException)
		{
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function getCorrespondenceSourceType($iTemplateId)
	{
		try
		{
			// Proper admin required
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Template_Exception('You do not have permission to view Correspdondence Templates.');
			}
			
			$oTemplate		= Correspondence_Template::getForId($iTemplateId);
			$oSource		= Correspondence_Source::getForId($oTemplate->correspondence_source_id);
			
			// TODO: Replace with an ORM way of doing this (done like this because ORM re-factor in progress)
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute("SELECT	*
											FROM	correspondence_source_type
											WHERE	id = {$oSource->correspondence_source_type_id}");
			if ($mResult === false)
			{
				throw new Exception_Database("Failed to get correspondence source type for id '{$oSource->correspondence_source_type_id}'. ".$oQuery->Error());
			}
			$aSourceType	= $mResult->fetch_assoc();
			
			return 	array(
						"bSuccess"					=> true,
						"oCorrespondenceSourceType"	=> $aSourceType
					);
		}
		catch (JSON_Handler_Correspondence_Template_Exception $oException)
		{
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function getAdditionalColumns($iId)
	{
		try
		{
			// Proper admin required
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Template_Exception('You do not have permission to view Correspdondence Templates.');
			}
			
			return 	array(
						"bSuccess"				=> true,
						"aAdditionalColumns"	=> Correspondence_Logic_Template::getForId($iId)->getAdditionalColumnSet()
					);
		}
		catch (JSON_Handler_Correspondence_Template_Exception $oException)
		{
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $oException->getMessage()
					);
		}
		catch (Exception $e)
		{
			$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
			return 	array(
						"bSuccess"	=> false,
						"sMessage"	=> $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.'
					);
		}
	}
	
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null) {
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try {
			$iRecordCount = Correspondence_Template::searchFor(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly) {
				return array('iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Correspondence_Template::searchFor(false, $iLimit, $iOffset, $oSort, $oFilter);
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
	
	public function getSelectableSourceTypes()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aTypes 	= Correspondence_Source_Type::getAll();
			$aStdTypes	= array();
			foreach ($aTypes as $oType)
			{
				if ($oType->isUserSelectable())
				{
					$aStdTypes[$oType->id] = $oType->toStdClass();
				}
			}
			return array('bSuccess' => true, 'aSourceTypes' => $aStdTypes);
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
	
	public function getCorrespondenceTemplateCarrierModules()
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aModules 		= Correspondence_Template_Carrier_Module::getAll();
			$aStdModules	= array();
			foreach ($aModules as $oModule)
			{
				$oStdClass		= $oModule->toStdClass();
				$oCarrierModule	= Carrier_Module::getForId($oModule->carrier_module_id);
				if ($oCarrierModule->customer_group)
				{
					$oStdClass->customer_group_name = Customer_Group::getForId($oCarrierModule->customer_group)->internal_name;
				}
				else
				{
					$oStdClass->customer_group_name = null;
				}
				$oStdClass->carrier_name				= Carrier::getForId($oCarrierModule->Carrier)->Name;
				$oStdClass->carrier_module_type_name	= Carrier_Module_Type::getForId($oCarrierModule->Type)->name;
				$aStdModules[$oModule->id] 				= $oStdClass;
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
	
	public function saveTemplate($oDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aErrors = array();
			
			if ($oDetails->name === '')
			{
				$aErrors[] = "Name was not supplied";
			}
			else if (strlen($oDetails->description) > 255)
			{
				$aErrors[] = "Name cannot be more than 255 characters.";
			}
			
			if ($oDetails->description === '')
			{
				$aErrors[] = "Description was not supplied.";
			}
			else if (strlen($oDetails->description) > 510)
			{
				$aErrors[] = "Description cannot be more than 510 characters.";
			}
			
			if ($oDetails->correspondence_source_type_id === '')
			{
				$aErrors[] = "Source Type was not supplied.";
			}
			else
			{
				switch ($oDetails->correspondence_source_type_id)
				{
					case CORRESPONDENCE_SOURCE_TYPE_SQL:
						if ($oDetails->correspondence_source_details->sql_syntax == '')
						{
							$aErrors[] = "SQL Syntax must be supplied for the chosen Source Type";
						}
						break;
				}
			}
			
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => false, 'aErrors' => $aErrors);
			}
			
			$oDataAccess = DataAccess::getDataAccess();
			if ($oDataAccess->TransactionStart() === false)
			{
				throw new Exception("Failed to start transaction");
			}
			
			try
			{
				// Create source
				$oSource 								= new Correspondence_Source();
				$oSource->correspondence_source_type_id	= $oDetails->correspondence_source_type_id;
				$oSource->save();
				
				// Create source details
				switch ($oSource->correspondence_source_type_id)
				{
					case CORRESPONDENCE_SOURCE_TYPE_SQL:
						$oSourceSQL 							= new Correspondence_Source_Sql();
						$oSourceSQL->correspondence_source_id	= $oSource->id;
						$oSourceSQL->sql_syntax					= $oDetails->correspondence_source_details->sql_syntax;
						$oSourceSQL->save();
						break;
				}
				
				// Create template
				$oTemplate 								= new Correspondence_Template();
				$oTemplate->name 						= $oDetails->name;
				$oTemplate->description					= $oDetails->description;
				$oTemplate->created_employee_id			= Flex::getUserId();
				$oTemplate->created_timestamp			= DataAccess::getDataAccess()->getNow();
				$oTemplate->status_id					= STATUS_ACTIVE;
				$oTemplate->correspondence_source_id	= $oSource->id;
				$oTemplate->save();
				
				// Create columns
				foreach ($oDetails->columns as $oColumnDetails)
				{
					$oCorrespondenceTemplateColumn 								= new Correspondence_Template_Column(get_object_vars($oColumnDetails));
					$oCorrespondenceTemplateColumn->correspondence_template_id	= $oTemplate->id;
					$oCorrespondenceTemplateColumn->save();
				}
				
				// Create correspondence template carrier module config
				foreach ($oDetails->template_carrier_modules as $iCorrespondenceDeliveryMethodId => $iCorrespondenceTemplateCarrierModuleId)
				{
					$oConfig 											= new Correspondence_Template_Correspondence_Template_Carrier_Module();
					$oConfig->correspondence_template_id 				= $oTemplate->id;
					$oConfig->correspondence_template_carrier_module_id	= $iCorrespondenceTemplateCarrierModuleId;
					$oConfig->correspondence_delivery_method_id 		= $iCorrespondenceDeliveryMethodId;
					$oConfig->save();
				}
			}
			catch (Exception $oException)
			{
				if ($oDataAccess->TransactionRollback() === false)
				{
					throw new Exception("Failed to rollback transaction");
				}
				
				throw $oException;
			}
			
			if ($oDataAccess->TransactionCommit() === false)
			{
				throw new Exception("Failed to commit transaction");
			}
			
			return array('bSuccess' => true);
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
	
	public function getEditingDetailsForId($iTemplateId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oTemplate		= Correspondence_Template::getForId($iTemplateId);
			$oStdTemplate	= $oTemplate->toStdClass();
			
			// Get source information
			$oStdTemplate->correspondence_source = Correspondence_Source::getForId($oTemplate->correspondence_source_id)->toStdClass();
			switch ($oStdTemplate->correspondence_source->correspondence_source_type_id)
			{
				case CORRESPONDENCE_SOURCE_TYPE_SQL:
					$oSourceDetails = Correspondence_Source_Sql::getForCorrespondenceSourceId($oTemplate->correspondence_source_id)->toStdClass();
					break;
			}
			$oStdTemplate->correspondence_source->details = $oSourceDetails;
			
			// Get template carrier module config
			$aCarrierModuleConfig		= Correspondence_Template_Correspondence_Template_Carrier_Module::getForTemplateId($iTemplateId);
			$aTemplateCarrierModules	= array();
			foreach ($aCarrierModuleConfig as $oModuleConfig)
			{
				$aTemplateCarrierModules[$oModuleConfig->correspondence_delivery_method_id] = $oModuleConfig->correspondence_template_carrier_module_id;
			}
			
			// Get columns
			$aColumns 		= Correspondence_Template_Column::getForTemplateId($iTemplateId);
			$aStdColumns	= array();
			foreach ($aColumns as $oColumn)
			{
				$aStdColumns[] = $oColumn->toStdClass();
			}
			
			return	array(
						'bSuccess' 					=> true, 
						'oTemplate'					=> $oStdTemplate,
						'aColumns' 					=> $aStdColumns,
						'aTemplateCarrierModules'	=> $aTemplateCarrierModules
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
	
	public function setStatus($iTemplateId, $iStatusId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$oTemplate				= Correspondence_Template::getForId($iTemplateId);
			$oTemplate->status_id	= $iStatusId;
			$oTemplate->save();
			
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
	
	public function createCorrespondenceTemplateCarrierModule($oDetails)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aErrors = array();
			
			if ($oDetails->template_code === '')
			{
				$aErrors[] = "Template Code was not supplied";
			}
			else if (strlen($oDetails->template_code) > 45)
			{
				$aErrors[] = "Template Code cannot be more than 45 characters.";
			}
			
			if ($oDetails->carrier_module_id === '')
			{
				$aErrors[] = "Carrier Module was not supplied.";
			}
			else if (Correspondence_Template_Carrier_Module::getForCarrierModuleAndTemplateCode($oDetails->carrier_module_id, $oDetails->template_code) !== null)
			{
				$aErrors[] = "There is already a Correspondence Template Carrier Module with the given details.";
			}
			
			if (count($aErrors) > 0)
			{
				return array('bSuccess' => false, 'aErrors' => $aErrors);
			}
			
			// Create record
			$oRecord 					= new Correspondence_Template_Carrier_Module();
			$oRecord->carrier_module_id	= $oDetails->carrier_module_id;
			$oRecord->template_code		= $oDetails->template_code;
			$oRecord->save();
			
			return array('bSuccess' => true, 'iCorrespondenceTemplateCarrierModule' => $oRecord->id);
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

class JSON_Handler_Correspondence_Template_Exception extends Exception
{
	// No changes
}

?>