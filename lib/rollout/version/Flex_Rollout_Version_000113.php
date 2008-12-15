<?php

/**
 * Version 113 of database update.
 * This version: -
 *	1:	Add resource_type.file_name_regex Field
 */

class Flex_Rollout_Version_000113 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add resource_type.file_name_regex Field
		$strSQL = "ALTER TABLE resource_type ADD file_name_regex VARCHAR(1024) NULL COMMENT 'File Name Validation Perl RegEx';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add resource_type.file_name_regex Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE resource_type DROP file_name_regex;";
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