<?php

/**
 * Version 91 of database update.
 * This version: -
 *	1:	Adds indexes to the ServiceTotal Table
 */

class Flex_Rollout_Version_000091 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds indexes to the ServiceTotal Table
		$strSQL = "CREATE INDEX service_rate_plan ON ServiceTotal (service_rate_plan);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ServiceTotal.service_rate_plan Index. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP INDEX service_rate_plan ON ServiceTotal;";
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
