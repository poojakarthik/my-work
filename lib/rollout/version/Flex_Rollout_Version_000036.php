<?php

/**
 * Version 36 (thirty-six) of database update.
 * This version: -
 *	1:	Creates flex_module table
 */

class Flex_Rollout_Version_000036 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		//	1:	Creates flex_module table
		$strSQL = "CREATE TABLE flex_module " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Flex Module', " .
						"name VARCHAR(1024) NOT NULL COMMENT 'Name of the Flex Module'," .
						"status_id BIGINT(20) NOT NULL DEFAULT 1 COMMENT 'FK to active_status table'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create flex_module Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE flex_module;";
		
	}
	
	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
