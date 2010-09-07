<?php
/**
 * Resource_Type_File_Import_Provisioning
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Import_Provisioning
 */
abstract class Resource_Type_File_Import_Provisioning extends Resource_Type_File_Import
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_PROVISIONING_INPUT;
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>