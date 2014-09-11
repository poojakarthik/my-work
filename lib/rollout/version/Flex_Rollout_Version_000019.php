<?php

/**
 * Version 19 of database update.
 * This version: -
 *	1:	Add missing ticketing user permission
 */

class Flex_Rollout_Version_000019 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		$strSQL = "INSERT INTO ticketing_user_permission (id, name, description, const_name) VALUES
				(0, 'None', 'Not a ticketing system user', 'TICKETING_USER_PERMISSION_NONE')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . " Failed to populate ticketing_user_permission table. " . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM ticketing_user_permission WHERE id = 0;";
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
