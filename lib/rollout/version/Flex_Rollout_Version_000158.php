<?php

/**
 * Version 158 of database update.
 * This version: -
 *
 *	1:	Add modified_by_user_id to the ticketing_ticket table (and foreign key constraint)
 *	2:	Add modified_by_user_id to the ticketing_ticket_history table (and foreign key constraint)
 */

class Flex_Rollout_Version_000158 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add modified_by_user_id to the ticketing_ticket table (and foreign key constraint)
		$strSQL =	"ALTER TABLE ticketing_ticket ".
					"ADD modified_by_user_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'FK into ticketing_user; the user who last modified the ticket' AFTER modified_datetime, ".
					"ADD CONSTRAINT fk_ticketing_ticket_modified_by_user_id_ticketing_user_id FOREIGN KEY (modified_by_user_id) REFERENCES ticketing_user(id) ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the modified_by_user_id to the ticketing_ticket table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE ticketing_ticket ". 
								"DROP FOREIGN KEY fk_ticketing_ticket_modified_by_user_id_ticketing_user_id, ".
								"DROP modified_by_user_id;";

		// 2:	Add modified_by_user_id to the ticketing_ticket_history table (and foreign key constraint)
		$strSQL =	"ALTER TABLE ticketing_ticket_history ".
					"ADD modified_by_user_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'FK into ticketing_user; the user who modified the ticket' AFTER modified_datetime, ".
					"ADD CONSTRAINT fk_ticketing_ticket_history_modified_by_user_id_ticketing_user FOREIGN KEY (modified_by_user_id) REFERENCES ticketing_user(id) ON UPDATE CASCADE ON DELETE RESTRICT;";

		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the modified_by_user_id to the ticketing_ticket_history table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE ticketing_ticket_history ". 
								"DROP FOREIGN KEY fk_ticketing_ticket_history_modified_by_user_id_ticketing_user, ".
								"DROP modified_by_user_id;";
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