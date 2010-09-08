<?php

class JSON_Handler_Correspondence extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getDataset($bCountOnly=false, $iLimit=null, $iOffset=null, $oSort=null, $oFilter=null)
	{
		try
		{
			// TODO: Check permissions
			
			
			$aFilter	= get_object_vars($oFilter);
			$aSort		= get_object_vars($oSort);
			
			$iRecordCount	= Correspondence::searchFor(true, null, null, $aFilter, $aSort);
			if ($bCountOnly)
			{
				return	array(
							'Success'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= is_null($iLimit) ? 0 : $iLimit;
			$iOffset	= is_null($iOffset) ? 0 : $iOffset;
			$aItems		= Correspondence::searchFor(false, $iLimit, $iOffset, $aFilter, $aSort);
			$i			= 0;
			$aResults	= array();
			foreach ($aItems as $oItem)
			{
				$oLogic											= new Correspondence_Logic($oItem);
				$aItem											= $oLogic->toArray();
				$aItem['id']									= $oLogic->id;
				$aItem['customer_group_name']					= Customer_Group::getForId($aItem['customer_group_id'])->internal_name;
				$aItem['correspondence_delivery_method_name']	= Correspondence_Delivery_Method::getForId($aItem['correspondence_delivery_method_id'])->name;
				$aResults[$iOffset + $i]						= $aItem;
				$i++;
			}
			
			return	array(
						'Success'		=> true,
						'aRecords'		=> $aResults,
						'iRecordCount'	=> $iRecordCount
					);
		}
		catch (JSON_Handler_Correspondence_Exception $oException)
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
			// TODO: Check permissions
			$oCorrespondence	= new Correspondence_Logic(Correspondence::getForId($iId));
			$aAdditionalColumns	= $oCorrespondence->getAdditionalColumns();
			return	array(
						'bSuccess'	=> true,
						'aData'		=> $oCorrespondence->toArray(),
						'aAdditionalColumns'	=> $aAdditionalColumns
					);
		}
		catch (JSON_Handler_Correspondence_Exception $oException)
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

class JSON_Handler_Correspondence_Exception extends Exception
{
	// No changes
}

?>