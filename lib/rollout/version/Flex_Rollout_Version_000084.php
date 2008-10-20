<?php

/**
 * Version 84 of database update.
 * This version: -
 *	1:	Drop the contract_exit_nature Table
 *	2:	Add the contract_status Table
 *	3:	Populate the contract_status Table
 *	4:	Drop the ServiceRatePlan.contract_exit_nature_id field
 *	5:	Add the ServiceRatePlan.contract_status_id field
 */

class Flex_Rollout_Version_000084 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Drop the contract_exit_nature table
		$strSQL = "DROP TABLE IF EXISTS contract_exit_nature;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the contract_exit_nature Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "CREATE TABLE contract_exit_nature " .
								"(" .
									"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Contract Exit Nature', " .
									"name VARCHAR(255) NOT NULL COMMENT 'Name of the Contract Exit Nature', " .
									"description VARCHAR(1024) NOT NULL COMMENT 'Description of the Contract Exit Nature'," .
									"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Contract Exit Nature'" .
								") ENGINE = innodb;";
		
		// 2:	Add the contract_status table
		$strSQL = "CREATE TABLE contract_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Contract Status', " .
						"name VARCHAR(255) NOT NULL COMMENT 'Name of the Contract Status', " .
						"description VARCHAR(1024) NOT NULL COMMENT 'Description of the Contract Status'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Contract Status'" .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contract_status Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS contract_status;";
		
		// 3:	Populate the contract_status table
		$strSQL = "INSERT INTO contract_status (name, description, const_name) VALUES " .
					"('Active', 'Contract Active', 'CONTRACT_STATUS_ACTIVE')," .
					"('Expired', 'Contract Expired', 'CONTRACT_STATUS_EXPIRED'), " .
					"('Breached', 'Contract Breached', 'CONTRACT_STATUS_BREACHED');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the contract_status Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE contract_status;";
		
		// 4:	Drop the ServiceRatePlan.contract_exit_nature_id field
		$strSQL = "ALTER TABLE ServiceRatePlan DROP contract_exit_nature_id;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the ServiceRatePlan.contract_exit_nature_id field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE ServiceRatePlan ADD contract_exit_nature_id BIGINT(20) NULL COMMENT '(FK) The nature of the End of Contract' AFTER contract_effective_end_datetime";
		
		// 5:	Add the ServiceRatePlan.contract_status_id field
		$strSQL = "ALTER TABLE ServiceRatePlan ADD contract_status_id BIGINT(20) NULL COMMENT '(FK) The Status of this Contract' AFTER contract_effective_end_datetime;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ServiceRatePlan.contract_status_id field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE ServiceRatePlan DROP contract_status_id;";
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
