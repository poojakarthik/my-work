<?php

/**
 * Version 118 of database update.
 * This version: -
 *	1:	Add the clawback_period attribute to the dealer table
 */

class Flex_Rollout_Version_000119 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Add the clawback_period attribute to the dealer table
		$strSQL = "ALTER TABLE dealer ADD clawback_period INT NOT NULL DEFAULT 0 COMMENT 'clawback period for sales (in hours)' AFTER created_on;";
		
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add dealer.clawback_period Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE dealer DROP clawback_period;";
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