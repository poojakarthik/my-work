<?php

/**
 * Version 140 of database update.
 * This version: -
 *	1:	Adds the FLEX_MODULE_TICKETING module into the flex_module table (defaults to being turned off)
 */

class Flex_Rollout_Version_000140 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the FLEX_MODULE_TICKETING module into the flex_module table (defaults to being turned off)
		$strSQL = "	INSERT INTO flex_module (id, name, description, const_name, active)
					VALUES
					(5, 'Ticketing System', 'Ticketing System Module', 'FLEX_MODULE_TICKETING', 0);";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to insert the FLEX_MODULE_TICKETING record into the flex_module table (id: 5). ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM flex_module WHERE const_name = 'FLEX_MODULE_TICKETING';";
		
	}
	
	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>
