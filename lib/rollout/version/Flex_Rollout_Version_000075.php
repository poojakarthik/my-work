<?php

/**
 * Version 75 of database update.
 * This version: -
 *	1:	Adds the service_total_service Table
 */

class Flex_Rollout_Version_000075 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the service_total_service Table
		$strSQL = "CREATE TABLE service_total_service " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the ServiceTotal-Service relationship', " .
						"service_total_id BIGINT(20) UNSIGNED NOT NULL COMMENT '(FK) Service Total', " .
						"service_id BIGINT(20) UNSIGNED NOT NULL COMMENT '(FK) Service'" .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the service_total_serivce Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS service_total_service;";
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
