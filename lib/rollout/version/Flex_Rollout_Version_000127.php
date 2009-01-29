<?php

/**
 * Version 127 of database update.
 * This version: -
 *	1:	Add the 'Invoice Samples Internal' Email Notification
 */

class Flex_Rollout_Version_000127 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the 'Invoice Samples Internal' Email Notification
		$strSQL = "INSERT INTO email_notification (name, description, const_name, allow_customer_group_emails) VALUES " .
					"('Invoice Samples Internal', 'Email listing Sample Accounts for the specified Internal Invoice Run', 'EMAIL_NOTIFICATION_INVOICE_SAMPLES_INTERNAL', 1);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the \'Invoice Samples Internal\' Email Notification. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name = 'EMAIL_NOTIFICATION_INVOICE_SAMPLES_INTERNAL';";
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