<?php

/**
 * Version 128 of database update.
 * This version: -
 *	1:	Updates the customer_status 'D' record so that it tests for the friendly reminder instead of the overdue notice
 */

class Flex_Rollout_Version_000128 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Update the customer_status 'D' record so that it tests for the friendly reminder instead of the overdue notice
		$strSQL = "UPDATE customer_status
					SET description = 'Friendly Reminder has been sent', test = 'AccountSentFriendlyReminder'
					WHERE name = 'D';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to update the customer_status 'D' record, to test for FriendlyReminder instead of OverdueNotcie" . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE customer_status
								SET description = 'Overdue Notice has been sent', test = 'AccountSentOverdueNotice'
								WHERE name = 'D';";
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