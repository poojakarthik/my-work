<?php

/**
 * Version 168 of database update.
 * This version: -
 *
 *	1:	Create records in the email_notification table for EMAIL_NOTIFICATION_ALERT and EMAIL_NOTIFICATION_PAYMENT_ALERT
 */

class Flex_Rollout_Version_000168 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1: Create records in the email_notification table for EMAIL_NOTIFICATION_ALERT and EMAIL_NOTIFICATION_PAYMENT_ALERT
		$strSQL = "	INSERT INTO email_notification (name, description, const_name, allow_customer_group_emails)
					VALUES
					('Alert', 'Generic system alert', 'EMAIL_NOTIFICATION_ALERT', 0),
					('Payment Alert', 'Payment related alert', 'EMAIL_NOTIFICATION_PAYMENT_ALERT', 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add EMAIL_NOTIFICATION_ALERT and EMAIL_NOTIFICATION_PAYMENT_ALERT records to the email_notification Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name = 'EMAIL_NOTIFICATION_ALERT' OR const_name = 'EMAIL_NOTIFICATION_PAYMENT_ALERT';";
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