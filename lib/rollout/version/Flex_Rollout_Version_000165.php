<?php

/**
 * Version 165 of database update.
 * This version: -
 *
 *	1:	Add an index on the action.created_on property (as most queries of this table will be constrained using a data range for action.created_on)
 */
class Flex_Rollout_Version_000165 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1: Add an index on the action.created_on property (as most queries of this table will be constrained using a data range for action.created_on)
		$strSQL = "ALTER TABLE action ADD INDEX in_action_created_on (created_on);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to modify the structure of the action_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE action DROP INDEX in_action_created_on;";
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