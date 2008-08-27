<?php

/**
 * Version 42 of database update.
 * This version: -
 *	1:	update to enable last login statistics.
 */

class Flex_Rollout_Version_000042 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "ALTER TABLE `Contact` ADD `CurrentLogin` VARCHAR( 20 ) NULL ;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `Contact` DROP `CurrentLogin` ";
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
