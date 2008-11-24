<?php


/**
 * Version 100 of database update.
 * This version: -
 *	1:	Add new record to email notification table
 */

class Flex_Rollout_Version_000100 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Add new record to email notification table
		$strSQL = "INSERT INTO email_notification (id, name, description, const_name, allow_customer_group_emails)
				   VALUES (NULL , 'Voice mail files', 'Files containing voice mail message', 'EMAIL_NOTIFICATION_VOICE_MAIL', '0');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to insert EMAIL_NOTIFICATION_VOICE_MAIL: ' . $result->getMessage());
		}
		
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name = 'EMAIL_NOTIFICATION_VOICE_MAIL';";

		$strSQL = "SELECT id FROM email_notification WHERE const_name = 'EMAIL_NOTIFICATION_VOICE_MAIL';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to find id for EMAIL_NOTIFICATION_VOICE_MAIL: ' . $result->getMessage());
		}
		$id = $result->fetchOne();
		
		$strSQL = "INSERT INTO email_notification_address (id, email_notification_id, email_address_usage_id, email_address, customer_group_id)
				   VALUES (NULL, $id, 4, 'voice_message@yellowbilling.com.au', NULL), (NULL, $id, 2, 'ybs-admin@yellowbilling.com.au', NULL);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to insert sender email notification address for EMAIL_NOTIFICATION_VOICE_MAIL: ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification_address WHERE email_notification_id = $id;";
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
