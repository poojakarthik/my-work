<?php
/**
 * Resource_Type_File_Export_Payment
 *
 * @class	Resource_Type_File_Export_Payment
 */
abstract class Resource_Type_File_Export_Payment extends Resource_Type_File_Export
{
	const	CARRIER_MODULE_TYPE	= MODULE_TYPE_PAYMENT_DIRECT_DEBIT;
	
	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."payment/{$iCarrier}/{$sClass}/";
	}
	
	static public function createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType=self::CARRIER_MODULE_TYPE)
	{
		parent::createCarrierModule($iCarrier, $sClassName, $iResourceType, $iCarrierModuleType);
	}
}
?>