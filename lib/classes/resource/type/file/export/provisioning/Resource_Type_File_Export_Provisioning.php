<?php
/**
 * Resource_Type_File_Export_Provisioning
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_Provisioning
 */
abstract class Resource_Type_File_Export_Provisioning extends Resource_Type_File_Export
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_PROVISIONING_OUTPUT;
	
	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."provisioning/{$iCarrier}/{$sClass}/";
	}
	
	static public function createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE) {
		parent::createCarrierModule($iCarrier, $iCustomerGroup, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>