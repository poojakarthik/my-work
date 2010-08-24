<?php
abstract class Resource_Type_File_Deliver extends Resource_Type_Base
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_FILE_DELIVER;
	
	abstract public function connect();
	
	abstract public function disconnect();
	
	abstract public function deliver($sLocalPath);
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, self::CARRIER_MODULE_TYPE);
	}
}
?>