<?php

/**
 * Version 156 of database update.
 * This version: -
 *
 *	1:	Add the RatePlan.commissionable_value, created_employee_id, modified_employee_id, modified_on Table
 */

class Flex_Rollout_Version_000156 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the RatePlan.commissionable_value, created_employee_id, modified_employee_id, modified_on Table
		$strSQL =	"ALTER TABLE RatePlan " .
					"ADD commissionable_value	DECIMAL(13, 4)				NOT NULL								COMMENT 'Commission Paid on this Plan', " .
					"ADD created_employee_id	BIGINT(20)		UNSIGNED	NOT NULL	DEFAULT 0					COMMENT '(FK) Employee who created this Plan', " .
					"ADD modified_employee_id	BIGINT(20)		UNSIGNED	NOT NULL	DEFAULT 0					COMMENT '(FK) Employee who last modified this Plan', " .
					"ADD modified_on			TIMESTAMP					NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT '(FK) Employee who created this Plan', " .
					" " .
					"ADD CONSTRAINT fk_rate_plan_created_employee_id	FOREIGN KEY (created_employee_id)	REFERENCES Employee(Id)	ON UPDATE CASCADE ON DELETE RESTRICT, " .
					"ADD CONSTRAINT fk_rate_plan_modified_employee_id	FOREIGN KEY (modified_employee_id)	REFERENCES Employee(Id)	ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the new RatePlan fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE RatePlan " .
								"DROP FOREIGN KEY fk_rate_plan_modified_employee_id, " .
								"DROP FOREIGN KEY fk_rate_plan_created_employee_id, " .
								"DROP commissionable_value, " .
								"DROP created_employee_id, " .
								"DROP modified_employee_id, " .
								"DROP modified_on;";
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