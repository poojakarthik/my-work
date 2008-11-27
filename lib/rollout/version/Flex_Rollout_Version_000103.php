<?php

/**
 * Version 103 of database update.
 * This version: -
 *	1:	Adds the RatePlan.included_data Field
 */

class Flex_Rollout_Version_000103 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the RatePlan.included_data Field
		$strSQL = "ALTER TABLE RatePlan " .
					"ADD included_data INT NOT NULL DEFAULT 0 COMMENT 'Included Data Allowance (in KB)';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the RatePlan.included_data Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE RatePlan DROP included_data;";
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
