<?php

/**
 * Version 126 of database update.
 * This version: -
 *	1:	Add the 'Invoice Samples' Email Notification
 */

class Flex_Rollout_Version_000124 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the 'Invoice Samples' Email Notification
		$strSQL = "INSERT INTO email_notification (name, description, const_name, allow_customer_group_emails) VALUES " .
					"('Invoice Samples', 'Email listing Sample Accounts for the specified Invoice Run', 'EMAIL_NOTIFICATION_INVOICE_SAMPLES', 1);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the \'Invoice Samples\' Email Notification. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name = 'EMAIL_NOTIFICATION_INVOICE_SAMPLES';";
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