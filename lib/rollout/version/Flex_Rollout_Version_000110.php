<?php

/**
 * Version 110 of database update.
 * This version: -
 *	1:	Add new Payment File Types
 */

class Flex_Rollout_Version_000110 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add new Payment File Types
		$strSQL = "INSERT INTO resource_type (name, description, const_name, resource_type_nature) VALUES
					('Australian Direct Entry File', 'Australian Direct Entry File', 'RESOURCE_TYPE_FILE_EXPORT_DIRECT_DEBIT_AUSTRALIAN_DIRECT_ENTRY_FILE', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')),
					('Australian Direct Entry Report', 'Australian Direct Entry Report', 'RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE'))";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add new Payment File Types. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM resource_type WHERE const_name IN ('RESOURCE_TYPE_FILE_EXPORT_DIRECT_DEBIT_AUSTRALIAN_DIRECT_ENTRY_FILE', 'RESOURCE_TYPE_FILE_IMPORT_PAYMENT_AUSTRALIAN_DIRECT_ENTRY_REPORT');";
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