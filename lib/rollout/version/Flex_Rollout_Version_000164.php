<?php

/**
 * Version 164 of database update.
 * This version: -
 *
 *	1:	Rename the action_type.system_only property to action_type.is_automatic_only and add is_system and active_status_id to this table
 *	2:	Set values for these fields for all the action_type records
 *	3:	Add NOT NULL constraints that would have preiously failed
 */
class Flex_Rollout_Version_000164 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1: Rename the action_type.system_only property to action_type.is_automatic_only and add is_system and status_id to this table
		$strSQL = "	ALTER TABLE action_type
					CHANGE			system_only is_automatic_only TINYINT UNSIGNED NOT NULL COMMENT '0 = anyone can manually log this action, 1 = only the system can log this action',
					ADD COLUMN		is_system TINYINT UNSIGNED NULL COMMENT '1 = the system specifically uses this action type, 0 = it doesn''t' AFTER is_automatic_only,
					ADD COLUMN		active_status_id SMALLINT UNSIGNED NULL COMMENT 'FK into active_status table' AFTER is_system,
					ADD CONSTRAINT	fk_action_type_active_status_id FOREIGN KEY (active_status_id) REFERENCES active_status(id) ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to modify the structure of the action_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE action_type
									CHANGE is_automatic_only system_only TINYINT UNSIGNED NOT NULL COMMENT '0 = anyone can manually log this action, 1 = only the system can log this action',
									DROP FOREIGN KEY fk_action_type_active_status_id,
									DROP COLUMN active_status_id,
									DROP COLUMN is_system;";

		// 2: Set values for these fields for all the action_type records
		// Set the active_status_id to ACTIVE_STATUS_ACTIVE for every record that is already in the database
		// set the is_system flag to 1 for the "Payment Made" action type, because that is the only system one so far
		$cgActiveStatus			= Constant_Group::loadFromTable($dbAdmin, "active_status", false, false, true);
		$intActiveStatusActive	= $cgActiveStatus->getValue('ACTIVE_STATUS_ACTIVE');
		$strSQL = "	UPDATE action_type
					SET active_status_id = $intActiveStatusActive,
					is_system = CASE WHEN name = 'Payment Made' THEN 1 ELSE 0 END;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to update the active_status_id and is_system fields for the records of the action_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// We don't need to roll this back, as these columns will be deleted

		// 3: Add NOT NULL constraints that would have preiously failed
		$strSQL = "	ALTER TABLE action_type
					CHANGE	is_system is_system TINYINT UNSIGNED NOT NULL COMMENT '1 = the system specifically uses this action type, 0 = it doesn''t',
					CHANGE	active_status_id active_status_id SMALLINT UNSIGNED NOT NULL COMMENT 'FK into active_status table';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to modify the structure of the action_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// We don't need to roll this back, as these columns will be deleted
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