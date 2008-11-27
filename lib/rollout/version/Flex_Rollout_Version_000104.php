<?php

/**
 * Version 104 of database update.
 * This version: -
 *	1:	Fixes the cont_name values in the credit_control_status table (they currently include spaces in the constant names)
 */

class Flex_Rollout_Version_000104 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Fix the cont_name values in the credit_control_status table (they currently include spaces in the constant names)
		$strSQL = 	"UPDATE credit_control_status ".
					"SET const_name = REPLACE(const_name, ' ', '_');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to fix the const_name values of the credit_control_status table, so that they don\'t include spaces. ' . $result->getMessage());
		}
		// No explicit rollback sql is required
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
