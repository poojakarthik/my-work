<?php

class JSON_Handler_Carrier extends JSON_Handler
{
	const	DESTINATION_UNKNOWN	= -1;
	
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
	public function getCarriers($sCarrierTypeConstant=null)
	{
		$bIsGod	= Employee::getForId(Flex::getUserId())->isGod();
		
		$iCarrierType	= @constant($sCarrierTypeConstant);	// Will return NULL if not defined
		
		try
		{
			$aORMs		= Carrier::getAll();
			$aCarriers	= array();
			foreach ($aORMs as $iCarrierId=>$oCarrier)
			{
				if (!$iCarrierType || $iCarrierType === $oCarrier->carrier_type)
				{
					$aCarriers[]	= $oCarrier->toStdClass();
				}
			}
			
			return	array(
						'bSuccess'	=> true,
						'Success'	=> true,
						'aRecords'	=> $aCarriers,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
		catch (Exception $oException)
		{
			$sMessage	= ($bIsGod ? $oException->getMessage() : 'An error occured accessing the database, please contact YBS for assistance.');
			return	array(
						'bSuccess'	=> false,
						'Success'	=> false,
						'sMessage'	=> $sMessage,
						'Message'	=> $sMessage,
						'sDebug'	=> $bIsGod ? $this->_JSONDebug : ''
					);
		}
	}
	
	public function getRatePlanCarriers()
	{
		$bUserIsGod = Employee::getForId(Flex::getUserId())->isGod();
		try
		{
			$aORMs		= Carrier::getAllAssociatedWithRatePlan();
			$aCarriers	= array();
			foreach ($aORMs as $iCarrierId => $oCarrier)
			{
				$aCarriers[$oCarrier->Id] = $oCarrier->toStdClass();
			}
			
			return array('bSuccess' => true, 'aRecords' => $aCarriers);
		}
		catch (Exception $oException)
		{
			$sMessage = ($bUserIsGod ? $oException->getMessage() : 'An error occured accessing the database, please contact YBS for assistance.');
			return	array(
						'bSuccess'	=> false,
						'sMessage'	=> $sMessage,
						'sDebug'	=> $bUserIsGod ? $this->_JSONDebug : ''
					);
		}
	}
}

?>