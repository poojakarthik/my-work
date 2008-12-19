<?php

/**
 * Version 117 of database update.
 * This version: -
 *	1:	Add resource_type.file_name_regex Field
 */

class Flex_Rollout_Version_000117 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add resource_type.file_name_regex Field
		$strSQL = "ALTER TABLE telemarketing_fnn_proposed MODIFY id BIGINT(20) NOT NULL UNSIGNED AUTO_INCREMENT COMMENT 'Unique Identifier';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add resource_type.file_name_regex Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE telemarketing_fnn_proposed MODIFY id BIGINT(20) NOT NULL UNSIGNED COMMENT 'Unique Identifier';";
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