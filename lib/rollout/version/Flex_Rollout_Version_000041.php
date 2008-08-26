<?php

/**
 * Version 41 of database update.
 * This version: -
 *	1:	update to enable last login statistics.
 */

class Flex_Rollout_Version_000041 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "ALTER TABLE `Contact` ADD `LastLogin` VARCHAR( 20 ) NULL COMMENT 'Unix time stamp of when the user last authenticated.';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `Contact` DROP `LastLogin`";
	}

	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
