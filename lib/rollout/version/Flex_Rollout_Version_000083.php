<?php

/**
 * Version 83 of database update.
 * This version: -
 *	1:	Add the contract_exit_nature Table
 *	2:	Populate the contract_exit_nature Table
 *	3:	Add the contract_scheduled_end_datetime, contract_effective_end_datetime, contract_exit_nature_id fields to the ServiceRatePlan table
 *	4:	Add the contract_exit_fee, contract_payout_percentage fields to the RatePlan table
 */

class Flex_Rollout_Version_000083 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the contract_exit_nature table
		$strSQL = "CREATE TABLE contract_exit_nature " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Contract Exit Nature', " .
						"name VARCHAR(255) NOT NULL COMMENT 'Name of the Contract Exit Nature', " .
						"description VARCHAR(1024) NOT NULL COMMENT 'Description of the Contract Exit Nature'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Contract Exit Nature'" .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contract_exit_nature Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS contract_exit_nature;";
		
		// 2:	Populate the contract_exit_nature table
		$strSQL = "INSERT INTO contract_exit_nature (name, description, const_name) VALUES " .
					"('Expired', 'Contract Expired', 'CONTRACT_EXIT_NATURE_EXPIRED'), " .
					"('Breached', 'Contract Breached', 'CONTRACT_EXIT_NATURE_BREACHED');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the contract_exit_nature Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE contract_exit_nature;";
		
		// 3:	Add the contract_scheduled_end_datetime, contract_effective_end_datetime, contract_exit_nature_id fields to the ServiceRatePlan table
		$strSQL = "ALTER TABLE ServiceRatePlan " .
					"ADD contract_scheduled_end_datetime DATETIME NULL COMMENT 'Scheduled Contract End Date' AFTER EndDatetime," .
					"ADD contract_effective_end_datetime DATETIME NULL COMMENT 'Scheduled Contract End Date' AFTER contract_scheduled_end_datetime," .
					"ADD contract_exit_nature_id BIGINT(20) NULL COMMENT '(FK) The nature of the End of Contract' AFTER contract_effective_end_datetime;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contract_scheduled_end_datetime, contract_effective_end_datetime, contract_exit_nature_id fields to the ServiceRatePlan table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE ServiceRatePlan " .
								"DROP contract_scheduled_end_datetime, " .
								"DROP contract_effective_end_datetime, " .
								"DROP contract_exit_nature_id;";
		
		// 4:	Add the contract_exit_fee, contract_payout_percentage fields to the RatePlan table
		$strSQL = "ALTER TABLE RatePlan " .
					"ADD contract_exit_fee DECIMAL(13, 4) NOT NULL DEFAULT 0 COMMENT 'Contract Exit Fee' AFTER ContractTerm," .
					"ADD contract_payout_percentage DECIMAL(13, 4) NOT NULL DEFAULT 0 COMMENT 'Contract Payout Percentage' AFTER contract_exit_fee;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contract_exit_fee, contract_payout_percentage fields to the RatePlan table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE RatePlan " .
								"DROP contract_scheduled_end_datetime, " .
								"DROP contract_exit_nature_id;";
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
