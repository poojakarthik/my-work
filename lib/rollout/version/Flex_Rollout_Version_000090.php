<?php

/**
 * Version 90 of database update.
 * This version: -
 *	1:	Adds the ServiceRatePlan.contract_breach_fees_reason Field
 */

class Flex_Rollout_Version_000090 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the ServiceRatePlan.contract_breach_fees_reason Field
		$strSQL = "ALTER TABLE ServiceRatePlan " .
					"ADD contract_breach_fees_reason VARCHAR(512) NULL COMMENT 'Reason for approving/waiving the Contract Fees' AFTER contract_breach_fees_employee_id;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ServiceRatePlan.contract_breach_fees_reason Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE ServiceRatePlan " .
								"DROP contract_breach_fees_reason;";
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
