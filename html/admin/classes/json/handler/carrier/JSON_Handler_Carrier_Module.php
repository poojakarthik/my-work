<?php

class JSON_Handler_Carrier_Module extends JSON_Handler
{
	protected	$_JSONDebug	= '';
	
	public function __construct()
	{
		// Send Log output to a debug string
		Log::registerLog('JSON_Handler_Debug', Log::LOG_TYPE_STRING, $this->_JSONDebug);
		Log::setDefaultLog('JSON_Handler_Debug');
	}
	
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
}

class JSON_Handler_Carrier_Module_Exception extends Exception
{
	// No changes
}

?>