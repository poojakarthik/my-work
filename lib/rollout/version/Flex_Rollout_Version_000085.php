<?php

/**
 * Version 85 of database update.
 * This version: -
 *	1:	Add the contract_breach_reason Table
 *	2:	Populate the contract_breach_reason Table
 *	3:	Add the ServiceRatePlan.contract_breach_reason_id field
 */

class Flex_Rollout_Version_000085 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the contract_breach_reason Table
		$strSQL = "CREATE TABLE contract_breach_reason " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Contract Breach Reason', " .
						"name VARCHAR(255) NOT NULL COMMENT 'Name of the Contract Breach Reason', " .
						"description VARCHAR(1024) NOT NULL COMMENT 'Description of the Contract Breach Reason'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Contract Breach Reason'" .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contract_breach_reason Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS contract_breach_reason;";
		
		// 2:	Populate the contract_breach_reason Table
		$strSQL = "INSERT INTO contract_breach_reason (name, description, const_name) VALUES " .
					"('Churned', 'Service Churned', 'CONTRACT_BREACH_REASON_CHURNED')," .
					"('Disconnected', 'Service Disconnected', 'CONTRACT_BREACH_REASON_DISCONNECTED')," .
					"('Upgrade', 'Plan Upgraded', 'CONTRACT_BREACH_REASON_UPGRADE')," .
					"('Downgrade', 'Plan Downgraded', 'CONTRACT_BREACH_REASON_DOWNGRADE')," .
					"('Crossgrade', 'Plan Crossgraded', 'CONTRACT_BREACH_REASON_CROSSGRADE')," .
					"('Moved', 'Service Moved to another Account', 'CONTRACT_BREACH_REASON_MOVED')," .
					"('Other', 'Other', 'CONTRACT_BREACH_REASON_OTHER');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the contract_breach_reason Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE contract_breach_reason;";
		
		// 3:	Add the ServiceRatePlan.contract_breach_reason_id and contract_breach_reason_description field
		$strSQL = "ALTER TABLE ServiceRatePlan " .
					"ADD contract_breach_reason_id BIGINT(20) NULL COMMENT '(FK) Reason why the Contract was breached' AFTER contract_status_id," .
					"ADD contract_breach_reason_description VARCHAR(512) NULL COMMENT 'Description of why the Contract was breached' AFTER contract_breach_reason_id;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ServiceRatePlan.contract_breach_reason_id and contract_breach_reason_description fields. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE ServiceRatePlan " .
								"DROP contract_breach_reason_id," .
								"DROP contract_breach_reason_description;";
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
