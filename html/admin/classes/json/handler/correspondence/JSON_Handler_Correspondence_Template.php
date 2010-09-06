<?php

class JSON_Handler_Correspondence_Template extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getAllWithNonSystemSources()
	{
		try
		{
			// TODO: Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR)))
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
											WHERE	cst.system_name <> 'SYSTEM';");
			if ($mResult === false)
			{
				throw new Exception("Failed to get correspondence templates. ".$oQuery->Error());
			}
			
			while ($aRow = $mResult->fetch_assoc())
			{
				$iTemplateId			= $aRow['correspondence_template_id'];
				$aResults[$iTemplateId]	= Correspondence_Template_ORM::getForId($iTemplateId)->toStdClass();
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
			// TODO: Check permissions
			if (!AuthenticatedUser()->UserHasPerm(array(PERMISSION_OPERATOR)))
			{
				throw new JSON_Handler_Correspondence_Template_Exception('You do not have permission to view Correspdondence Templates.');
			}
			
			$oTemplate		= Correspondence_Template_ORM::getForId($iTemplateId);
			$oSource		= Correspondence_Source_ORM::getForId($oTemplate->correspondence_source_id);
			
			// TODO: Replace with an ORM way of doing this (done like this because ORM re-factor in progress)
			$oQuery		= new Query();
			$mResult	= $oQuery->Execute("SELECT	*
											FROM	correspondence_source_type
											WHERE	id = {$oSource->correspondence_source_type_id}");
			if ($mResult === false)
			{
				throw new Exception("Failed to get correspondence source type for id '{$oSource->correspondence_source_type_id}'. ".$oQuery->Error());
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
}

class JSON_Handler_Correspondence_Template_Exception extends Exception
{
	// No changes
}

?>