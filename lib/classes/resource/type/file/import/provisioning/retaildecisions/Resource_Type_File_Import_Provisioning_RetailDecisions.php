<?php
/**
 * Resource_Type_File_Import_Provisioning_RetailDecisions
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Import_Provisioning_RetailDecisions
 */
abstract class Resource_Type_File_Import_Provisioning_RetailDecisions extends Resource_Type_File_Import
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_MOTORPASS_PROVISIONING_EXPORT;
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>