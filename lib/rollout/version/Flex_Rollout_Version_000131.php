<?php

/**
 * Version 131 of database update.
 * This version: -
 *	1:	Add the account_status.allow_customer_login Field
 */

class Flex_Rollout_Version_000131 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the account_status.allow_customer_login Field
		$strSQL = "ALTER TABLE account_status " .
					"ADD allow_customer_login TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'The Date on which the Billing Period starts' AFTER send_late_notice;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_status.allow_customer_login Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "ALTER TABLE account_status " .
								"DROP allow_customer_login;";
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