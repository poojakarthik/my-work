<?php

/**
 * Version 183 of database update.
 * This version: -
 *	
 *	1:	Add the External Ticketing User Permission
 *
 */

class Flex_Rollout_Version_000183 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the External Ticketing User Permission
		$strSQL = "	INSERT INTO	ticketing_user_permission
						(id, name, description, const_name)
					VALUES
						(3	, 'External User'	, 'Ticketing system external user'	, 'TICKETING_USER_PERMISSION_USER_EXTERNAL');";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the External Ticketing User Permission. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	ticketing_user_permission
									WHERE		const_name = 'TICKETING_USER_PERMISSION_USER_EXTERNAL'
									LIMIT		1";
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