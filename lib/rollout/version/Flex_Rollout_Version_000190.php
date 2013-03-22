<?php

/**
 * Version 190 of database update.
 * This version: -
 *	
 *	1:	Add the ticketing_customer_group_email.archived_on_datetime Field
 *	2:	Make ticketing_customer_group_email.customer_group_id, .email & .name not nullable
 *
 */

class Flex_Rollout_Version_000190 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the ticketing_customer_group_email.archived_on_datetime Field
		$strSQL = "	ALTER TABLE ticketing_customer_group_email
					ADD COLUMN archived_on_datetime DATETIME NULL COMMENT 'Time at which this record was archived';";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ticketing_customer_group_email.archived_on_datetime Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE ticketing_customer_group_email
									DROP COLUMN archived_on_datetime;";

		//	2:	Make ticketing_customer_group_email.customer_group_id, .email & .name not nullable
		$strSQL = "	ALTER TABLE		ticketing_customer_group_email
					CHANGE COLUMN	customer_group_id customer_group_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to the CustomerGroup table',
					CHANGE COLUMN	email email VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Email address for accepted emails',
					CHANGE COLUMN	name name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Email name for outbound emails';";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to make ticketing_customer_group_email.customer_group_id, .email & .name not nullable. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE		ticketing_customer_group_email
									CHANGE COLUMN	customer_group_id customer_group_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'FK to the CustomerGroup table',
									CHANGE COLUMN	email email VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Email address for accepted emails',
									CHANGE COLUMN	name name VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'Email name for outbound emails';";
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