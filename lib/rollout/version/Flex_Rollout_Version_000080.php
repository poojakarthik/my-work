<?php

/**
 * Version 80 of database update.
 * This version: -
 *	1:	Correct tax_type.name Type from BIGINT(20) to VARCHAR(255)
 */

class Flex_Rollout_Version_000080 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the service_total_service Table
		$strSQL = "ALTER TABLE tax_type MODIFY name VARCHAR(255) NOT NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to correct tax_type.name Type from BIGINT(20) to VARCHAR(255). ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE tax_type MODIFY name BIGINT(20) NOT NULL;";
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
