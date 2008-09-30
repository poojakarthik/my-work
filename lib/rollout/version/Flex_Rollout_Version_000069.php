<?php

/**
 * Version 69 (Sixty-Nine (the funny number)) of database update.
 * This version: - Doesn't do anything anymore.  What a waste
 */

class Flex_Rollout_Version_000069 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
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
