<?php
/**
 * Resource_Type_File_Export_Provisioning
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_Provisioning
 */
abstract class Resource_Type_File_Export_Provisioning
{
	public static function getExportPath($iCarrier, $sClass)
	{
		return parent::getExportPath()."provisioning/{$iCarrier}/{$sClass}/";
	}
}
?>