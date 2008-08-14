<?php

/**
 * Version 27 of database update.
 * This version: -
 *	1:	Increase size of fields used for storing paths in the ticketing_config table
 */

class Flex_Rollout_Version_000027 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Increase size of fields used for storing paths in the ticketing_config table
		$strSQL = "ALTER TABLE ticketing_config 
						MODIFY username varchar(255) DEFAULT NULL COMMENT 'Username to use when retrieving emails (or backup dir for XML files)',
						MODIFY password varchar(255) DEFAULT NULL COMMENT 'Password (encrypted) to use when retrieving emails (or dir for junk XML files)'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter ticketing_config table. ' . $qryQuery->Error());
		}
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
