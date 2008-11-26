<?php

/**
 * Version 102 of database update.
 * This version: -
 *	1:	Adds the CustomerGroup.cooling_off_period column
 */

class Flex_Rollout_Version_000102 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Adds the CustomerGroup.cooling_off_period column
		$strSQL = "ALTER TABLE CustomerGroup ADD cooling_off_period INT UNSIGNED DEFAULT NULL COMMENT 'Cooling off period for sales (in hours)';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the cooling_off_period column to the CustomerGroup table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE CustomerGroup DROP cooling_off_period;";
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
