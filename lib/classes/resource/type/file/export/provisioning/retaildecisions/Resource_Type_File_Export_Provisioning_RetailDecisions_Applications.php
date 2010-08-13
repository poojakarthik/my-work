<?php
/**
 * Resource_Type_File_Export_Provisioning_RetailDecisions_Applications
 *
 * Models a record of the resource_type table
 *
 * @class	Resource_Type_File_Export_Provisioning_RetailDecisions_Applications
 */
class Resource_Type_File_Export_Provisioning_RetailDecisions_Applications extends Resource_Type_File_Export_Provisioning
{
	protected	$_oFileExport;
	protected	$_aOutput			= array();
	
	const		NEW_LINE_DELIMITER	= "\n";
	const		FIELD_DELIMITER		= ',';
	const		FIELD_ENCAPSULATOR	= '';
	const		ESCAPE_CHARACTER	= '\\';
	
	public function addRecord()
	{
		// TODO
	}
	
	public function deliver()
	{
		// TODO
	}
}