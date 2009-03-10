<?php

/**
 * Version 154 of database update.
 * This version: -
 *
 *	1:	Drop the ConfigConstant table
 *	2:	Drop the ConfigConstantGroup table
 */

class Flex_Rollout_Version_000154 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Drop the ConfigConstant table
		$strSQL =	"DROP TABLE IF EXISTS ConfigConstant;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the ConfigConstant Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 2:	Drop the ConfigConstantGroup table
		$strSQL =	"DROP TABLE IF EXISTS ConfigConstantGroup;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the ConfigConstantGroup Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
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