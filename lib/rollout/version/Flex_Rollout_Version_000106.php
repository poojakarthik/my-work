<?php

/**
 * Version 106 of database update.
 * This version: -
 *	1:	Creates the dealer_config table
 *	2:	Inserts a default record to the dealer_config table
 */

class Flex_Rollout_Version_000106 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Creates the dealer_config table
		$strSQL = "	CREATE TABLE dealer_config
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
						default_employee_manager_dealer_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into dealer table; default manager for employee dealers',
											
						CONSTRAINT pk_dealer_config PRIMARY KEY (id),
						CONSTRAINT fk_dealer_config_default_employee_manager_dealer_id_dealer_id FOREIGN KEY (default_employee_manager_dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE = innodb COMMENT = 'dealer configuration';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the dealer_config table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS dealer_config;";
		
		// 2: Insert a default record to the dealer_config table
		$strSQL = "INSERT INTO dealer_config (default_employee_manager_dealer_id) VALUES (NULL);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to insert a default record into the dealer_config table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM dealer_config WHERE TRUE;";
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
