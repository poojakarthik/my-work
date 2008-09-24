<?php

/**
 * Version 60 (Sixty) of database update.
 * This version: -
 *	1:	Adds employee_message table
 */

class Flex_Rollout_Version_000060 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add employee_message table
		$strSQL = "CREATE TABLE employee_message
					(
						id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY COMMENT 'unique id for this message',
						created_on DATETIME NOT NULL COMMENT 'timestamp for when this record was created',
						effective_on DATETIME NOT NULL COMMENT 'time at which this message will come into effect',
						message LONGTEXT NOT NULL COMMENT 'the message'
					) ENGINE = innodb COMMENT = 'Messages for employees';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create employee_message table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS employee_message;";
		
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
