<?php

/**
 * Version 122 of database update.
 * This version: -
 *	1:	Updates resource_type References to Indial Call Centre/Salescom
 *	2:	Updates the Indial Call Centre Carrier to Salescom Australia
 */

class Flex_Rollout_Version_000122 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Updates resource_type References to Indial Call Centre/Salescom
		$strSQL = "UPDATE resource_type SET name = 'Salescom Proposed Dialling List', description = 'Salescom Proposed Dialling List', const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_SALESCOM_PROPOSED_DIALLING_LIST' WHERE const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_PROPOSED_DIALLING_LIST';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_PROPOSED_DIALLING_LIST. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE resource_type SET name = 'INDIAN CC Proposed Dialling List', description = 'INDIAN CC Proposed Dialling List', const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_PROPOSED_DIALLING_LIST' WHERE const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_SALESCOM_PROPOSED_DIALLING_LIST';";
		
		$strSQL = "UPDATE resource_type SET name = 'Salescom Permitted Dialling List', description = 'Salescom Permitted Dialling List', const_name = 'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_SALESCOM_PERMITTED_DIALLING_LIST' WHERE const_name = 'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_INDIAN_PERMITTED_DIALLING_LIST';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_INDIAN_PERMITTED_DIALLING_LIST. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE resource_type SET name = 'INDIAN CC Permitted Dialling List', description = 'INDIAN CC Permitted Dialling List', const_name = 'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_INDIAN_PERMITTED_DIALLING_LIST' WHERE const_name = 'RESOURCE_TYPE_FILE_EXPORT_TELEMARKETING_SALESCOM_PERMITTED_DIALLING_LIST';";
		
		$strSQL = "UPDATE resource_type SET name = 'Salescom Dialler Report', description = 'Salescom Dialler Report', const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_SALESCOM_DIALLER_REPORT' WHERE const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_DIALLER_REPORT';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_DIALLER_REPORT. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE resource_type SET name = 'INDIAN CC Dialler Report', description = 'INDIAN CC Dialler Report', const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_INDIAN_DIALLER_REPORT' WHERE const_name = 'RESOURCE_TYPE_FILE_IMPORT_TELEMARKETING_SALESCOM_DIALLER_REPORT';";
		
		// 2:	Updates the Indial Call Centre Carrier to Salescom Australia
		$strSQL = "UPDATE Carrier SET Name = 'Salescom', description = 'Salescom Australia', const_name = 'CARRIER_SALESCOM' WHERE const_name = 'CARRIER_INDIAL_CALL_CENTRE';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update CARRIER_INDIAL_CALL_CENTRE. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE Carrier SET Name = 'Indian Call Centre', description = 'Indian Call Centre', const_name = 'CARRIER_INDIAL_CALL_CENTRE' WHERE const_name = 'CARRIER_SALESCOM';";
	}
	
	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>