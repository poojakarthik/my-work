<?php

class JSON_Handler_Barring extends JSON_Handler implements JSON_Handler_Loggable
{
	public function getAuthorisationLedgerDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Service_Barring_Level::getAuthorisationLedger(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit		= ($iLimit === null ? 0 : $iLimit);
			$iOffset	= ($iOffset === null ? 0 : $iOffset);
			$aData	 	= Service_Barring_Level::getAuthorisationLedger(false, $iLimit, $iOffset, $oSort, $oFilter);
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
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getActionLedgerDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$iRecordCount = Service_Barring_Level::getActionLedger(true, $iLimit, $iOffset, $oSort, $oFilter);
			if ($bCountOnly)
			{
				return array('bSuccess' => true, 'iRecordCount' => $iRecordCount);
			}
			
			$iLimit 	= ($iLimit === 0 ? null : $iLimit);
			$aTypes 	= Service_Barring_Level::getActionLedger(false, $iLimit, $iOffset, $oSort, $oFilter);
			$aResults	= array();
			$i			= $iOffset;
			foreach ($aTypes as $aRecord)
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
		catch (Exception $e)
		{
			$sMessage	= $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return 	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage
					);
		}
	}
	
	public function getServicesForAccountAuthorisation($iAccountId, $iBarringLevelId)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			// Get the unauthorised service_barring_level records for the account & barring level
			$oQuery 	= new Query();
			$mResult	= $oQuery->Execute("SELECT	sbl.id
											FROM	service_barring_level sbl
											JOIN	Service s ON (
											        	s.Id = sbl.service_id
											        	AND s.Account = {$iAccountId}
											        )
											WHERE	barring_level_id = {$iBarringLevelId}
											AND     authorised_datetime IS NULL;");
			if ($mResult === false)
			{
				throw new Exception("Failed to get service_barring_level records for account {$iAccountId}. ".$oQuery->Error());
			}
			
			$aServiceBarringLevelIds = array();
			while ($aRow = $mResult->fetch_assoc())
			{
				$aServiceBarringLevelIds[] = $aRow['id'];
			}
			
			// Build the list of services
			if ($aServiceBarringLevelIds == null || count($aServiceBarringLevelIds) == 0)
			{
				// Get all barrable services for the account
				$oQuery = new Query();
				$mResult = $oQuery->Execute("	SELECT 		Id, FNN
												FROM		Service
												INNER JOIN 	(
																SELECT MAX(Service.Id) serviceId
																FROM Service
																WHERE
																(
																	Service.ClosedOn IS NULL
																	OR NOW() < Service.ClosedOn
																)
																AND Service.CreatedOn < NOW()
																AND Service.FNN IN (SELECT FNN FROM Service WHERE Account = $iAccountId)
																GROUP BY Service.FNN
															) CurrentService ON (
																Service.Account = $iAccountId
																AND Service.Id = CurrentService.serviceId
																AND Service.Status IN (".SERVICE_ACTIVE.", ".SERVICE_DISCONNECTED.", ".SERVICE_ARCHIVED.")
															)
												ORDER BY	Service.FNN ASC;");
				if ($mResult === false)
				{
					throw new Exception("Failed to get barrable services for account.".$oQuery->Error());
				}
				
				$aServices = array();
				while ($aRow = $mResult->fetch_assoc())
				{
					$aServices[$aRow['Id']] = 	array(
													'id' 						=> $aRow['Id'],
													'fnn'						=> $aRow['FNN'],
													'service_barring_level_id'	=> null
												);
				}
			}
			else
			{
				// Get all services linked from the service_barring_level records
				$oQuery	= new Query();
				$sQuery	= "	SELECT 	s.Id AS id, s.FNN AS fnn, sbl.id AS service_barring_level_id
							FROM	service_barring_level sbl
							JOIN	Service s ON (s.Id = sbl.service_id)
							WHERE	sbl.id IN (".implode(', ', $aServiceBarringLevelIds).");";
				$mResult = $oQuery->Execute($sQuery);
				if ($mResult === false)
				{
					throw new Exception("Failed to get services from service_barring_level ids.".$oQuery->Error());
				}
				
				$aServices = array();
				while ($aRow = $mResult->fetch_assoc())
				{
					$aServices[$aRow['id']] = $aRow;
				}
			}
			
			foreach ($aServices as $iServiceId => $aService)
			{
				// Check and add auto_barrable flag to any services that can be
				$aServices[$iServiceId]['auto_barrable'] = Logic_Service::canServiceBeAutomaticallyBarred($iServiceId, $iBarringLevelId);
			}
			
			return array('bSuccess' => true, 'aServices' => $aServices);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function authoriseAccount($iAccountId, $iAccountBarringLevelId, $iBarringLevelId, $aServiceBarringLevelIdsByServiceId, $aServiceIdsToAction)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			return array('bSuccess' => false, 'sMessage' => 'Failed to start db Transaction. Please contact YBS.');
		}
		
		try
		{
			// Authorise each service_barring_level (create for the service if there is no record, link to the account_barring_level if there is one)
			$aServiceBarringLevelsByServiceId = array();
			foreach ($aServiceBarringLevelIdsByServiceId as $iServiceId => $iServiceBarringLevelId)
			{
				if ($iServiceBarringLevelId === null)
				{
					// Create service_barring_level record
					$oServiceBarringLevel 						= new Service_Barring_Level();
					$oServiceBarringLevel->service_id 			= $iServiceId;
					$oServiceBarringLevel->barring_level_id		= $iBarringLevelId;
					$oServiceBarringLevel->created_datetime		= DataAccess::getDataAccess()->getNow();
					$oServiceBarringLevel->created_employee_id	= Flex::getUserId();
					if ($iAccountBarringLevelId !== null)
					{
						$oServiceBarringLevel->account_barring_level_id = $iAccountBarringLevelId;
					}
					$oServiceBarringLevel->save();
				}
				else
				{
					$oServiceBarringLevel = Service_Barring_Level::getForId($iServiceBarringLevelId);
				}
				
				// Authorise the record
				$oServiceBarringLevel->authorise();
				
				// Auto provision if possible
				if (Logic_Service::canServiceBeAutomaticallyBarred($oServiceBarringLevel->service_id, $oServiceBarringLevel->barring_level_id))
				{
					// ... it is possible action & create provisioning request
					$oServiceBarringLevel->action();
					switch ($oServiceBarringLevel->barring_level_id)
					{
						case BARRING_LEVEL_UNRESTRICTED:
							$iProvisioningTypeId = PROVISIONING_TYPE_UNBAR;
							break;
						case BARRING_LEVEL_BARRED:
							$iProvisioningTypeId = PROVISIONING_TYPE_BAR;
							break;
						case BARRING_LEVEL_TEMPORARY_DISCONNECTION:
							$iProvisioningTypeId = PROVISIONING_TYPE_DISCONNECT_TEMPORARY;
							break;
					}
					
					Logic_Service::createProvisioningRequest($oServiceBarringLevel->service_id, $iProvisioningTypeId);
				}
				
				// Cache for actioning stage
				$aServiceBarringLevelsByServiceId[$iServiceId] = $oServiceBarringLevel;
			}
			
			// Authorise account_barring_level (if exists)
			if ($iAccountBarringLevelId !== null)
			{
				Account_Barring_Level::getForId($iAccountBarringLevelId)->authorise();
			}
			
			// Action the service_barring_level records for the given aServiceIdsToAction
			foreach ($aServiceIdsToAction as $iServiceId)
			{
				$oServiceBarringLevel = $aServiceBarringLevelsByServiceId[$iServiceId];
				$oServiceBarringLevel->action();
			}
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new Exception("Successful but failed to commit db transaction.");
			}
			
			return array('bSuccess' => true);
		}
		catch (JSON_Handler_Barring_Exception $oEx)
		{
			return array('bSuccess' => false, 'sMessage' => $oEx->getMessage());
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			if (!$oDataAccess->TransactionRollback())
			{
				$sMessage .= " Failed to rollback db transaction.";
			}
			
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function actionServiceBarringLevels($aServiceBarringLevelIds)
	{
		$bUserIsGod		= Employee::getForId(Flex::getUserId())->isGod();
		$oDataAccess	= DataAccess::getDataAccess();
		if (!$oDataAccess->TransactionStart())
		{
			return array('bSuccess' => false, 'sMessage' => 'Failed to start db Transaction. Please contact YBS.');
		}
		
		try
		{
			foreach ($aServiceBarringLevelIds as $iServiceBarringLevelId)
			{
				Service_Barring_Level::getForId($iServiceBarringLevelId)->action();
			}
			
			if (!$oDataAccess->TransactionCommit())
			{
				throw new Exception("Successful but failed to commit db transaction.");
			}
			
			return array('bSuccess' => true);
		}
		catch (Exception $e)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			if (!$oDataAccess->TransactionRollback())
			{
				$sMessage .= " Failed to rollback db transaction.";
			}
			
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
	
	public function generateActionLedgerFile($oSort, $oFilter, $sFileType)
	{
		$bUserIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aColumns =	array(
							'account_id' 				=> 'Account|int',
							'account_name'				=> 'Business Name',
							'customer_group_name'		=> 'Customer Group',
							'service_fnn' 				=> 'Service|fnn',
							'service_type_name'		 	=> 'Service Type',
							'carrier_name' 				=> 'Carrier',
							'barring_level_name' 		=> 'Target Barring Level',
							'authorised_datetime' 		=> 'Authorised On',
							'authorised_employee_name'	=> 'Authorised By',
							'created_datetime' 			=> 'Created On',
							'created_employee_name'		=> 'Created By'
						);
			$aRecords = Service_Barring_Level::getActionLedger(false, null, null, $oSort, $oFilter);

			// Build list of lines for the file
			$aLines	= array();
			foreach ($aRecords as $aRecord)
			{
				$aLine = array();
				foreach ($aColumns as $sField => $sTitle)
				{
					$aLine[$sTitle] = $aRecord[$sField];
				}
				$aLines[] = $aLine;
			}
			
			switch ($sFileType)
			{
				case 'CSV':
					$sFileExtension = 'csv';
					$sMIME			= 'text/csv';
					break;
				case 'Excel2007':
					$sFileExtension = 'xlsx';
					$sMIME			= 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
					break;
			}
			
			$sFilename	= "barring_action_ledger_".date('YmdHis').".{$sFileExtension}";
			$sFilePath	= FILES_BASE_PATH."/temp/{$sFilename}";
			
			$oSpreadsheet = new Logic_Spreadsheet(array_keys($aLines[0]), $aLines, $sFileType);
            $oSpreadsheet->saveAs($sFilePath, $sFileType);
			
			return array('bSuccess' => true, 'sFilename' => $sFilename, 'sMIME' => $sMIME);
		}
		catch (Exception $oException)
		{
			$sMessage = $bUserIsGod ? $e->getMessage() : 'There was an error getting the accessing the database. Please contact YBS for assistance.';
			return array('bSuccess' => false, 'sMessage' => $sMessage);
		}
	}
}

class JSON_Handler_Barring_Exception extends Exception
{
	// No changes
}

?>