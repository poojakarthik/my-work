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
			// Require proper admin priviledges when the account has not been limited (i.e. is from a system wide search)
			if (!isset($oFilter->account_id) && !AuthenticatedUser()->UserHasPerm(array(PERMISSION_PROPER_ADMIN)))
			{
				throw new JSON_Handler_Correspondence_Exception('You do not have permission to view Correspdondence.');
			}
			
			$aFilter	= get_object_vars($oFilter);
			$aSort		= get_object_vars($oSort);
			
			$iRecordCount	= Correspondence::getLedgerInformation(true, null, null, $aFilter, $aSort);
			if ($bCountOnly)
			{
				return	array(
							'Success'		=> true,
							'iRecordCount'	=> $iRecordCount
						);
			}
			
			$iLimit		= is_null($iLimit) ? null : $iLimit;
			$iOffset	= is_null($iOffset) ? 0 : $iOffset;
			$aItems		= Correspondence::getLedgerInformation(false, $iLimit, $iOffset, $aFilter, $aSort);
			$i			= 0;
			$aResults	= array();
			foreach ($aItems as $aItem)
			{
				// Add Additional columns to the result set
				$oCorrespondenceLogic	= new Correspondence_Logic(Correspondence::getForId($aItem['id']));
				$aColumns	= $oCorrespondenceLogic->getAdditionalColumns();
				$aLogic		= $oCorrespondenceLogic->toArray();
				foreach ($aColumns as $sColumn)
				{
					if (!isset($aItem[$sColumn]))
					{
						$aItem[$sColumn]	= $aLogic[$sColumn];
					}
				}
				
				$aResults[$iOffset + $i]	= $aItem;
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
	
	public function getForId($iId)
	{
		try
		{
			// TODO: Check permissions
			$oCorrespondence		= new Correspondence_Logic(Correspondence::getForId($iId));
			$aAdditionalColumns		= $oCorrespondence->getAdditionalColumns();
			$aCorrespondence		= $oCorrespondence->toArray();
			$aCorrespondence['id']	= $iId;
			return	array(
						'bSuccess'				=> true,
						'aData'					=> $aCorrespondence,
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