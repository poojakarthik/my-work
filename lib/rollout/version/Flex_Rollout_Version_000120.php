<?php

/**
 * Version 120 of database update.
 * This version: -
 *	1:	Add the telemarketing_fnn_blacklist.file_import_id field
 */

class Flex_Rollout_Version_000120 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the telemarketing_fnn_blacklist.file_import_id field
		$strSQL = "ALTER TABLE telemarketing_fnn_blacklist " .
					"ADD file_import_id BIGINT(20) UNSIGNED NULL COMMENT '(FK) The File that this was imported from'," .
					"ADD CONSTRAINT fk_telemarketing_fnn_blacklist_file_import_id FOREIGN KEY (file_import_id) REFERENCES FileImport(Id) ON UPDATE CASCADE ON DELETE CASCADE;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the telemarketing_fnn_blacklist.file_import_id field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "ALTER TABLE telemarketing_fnn_blacklist " .
								"DROP FOREIGN KEY fk_telemarketing_fnn_blacklist_file_import_id, " .
								"DROP file_import_id;";
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