<?php

/**
 * Version 24 of database update.
 * This version: -
 *	1:	Alter the ticketing_contact_account table to use bigints for ids (had originally been set as tinyints in error)
 */

class Flex_Rollout_Version_000024 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Alter the ticketing_contact_account table to use bigints for ids (had originally been set as tinyints in error)
		$strSQL = "ALTER TABLE ticketing_contact_account MODIFY ticketing_contact_id bigint(20) NOT NULL COMMENT 'FK to ticketing_contact table',
														 MODIFY account_id bigint(20) NOT NULL COMMENT 'FK to Account table'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to modify ticketing_contact_account. ' . $qryQuery->Error());
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
					throw new Exception_Database(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
