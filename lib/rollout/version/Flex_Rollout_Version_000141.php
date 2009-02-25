<?php

/**
 * Version 141 of database update.
 * This version: -
 *	1:	Add SMS Automatic Invoice Actions
 */

class Flex_Rollout_Version_000141 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add SMS Automatic Invoice Actions
		$strSQL =	"INSERT INTO automatic_invoice_action (name, description, const_name) VALUES " .
					"('Friendly Reminder SMS'		, 'Automatic Friendly Reminder SMS Sent'	, 'AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_SMS'), " .
					"('Barring Notification SMS'	, 'Automatic Barring Notification SMS Sent'	, 'AUTOMATIC_INVOICE_ACTION_BARRING_NOTIFICATION_SMS');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add SMS Automatic Invoice Actions. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM automatic_invoice_action WHERE const_name IN ('AUTOMATIC_INVOICE_ACTION_FRIENDLY_REMINDER_SMS', 'AUTOMATIC_INVOICE_ACTION_BARRING_NOTIFICATION_SMS');";		
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