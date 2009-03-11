<?php

/**
 * Version 156 of database update.
 * This version: -
 *
 *	1:	Add the Credit Control Status Change Email Notification
 */

class Flex_Rollout_Version_000156 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the Credit Control Status Change Email Notification
		$strSQL =	"INSERT INTO email_notification (name, description, const_name, allow_customer_group_emails) VALUES " .
					"('Credit Control Status Change', 'Notification email for when an Account\\'s Credit Control Status is changed', 'EMAIL_NOTIFICATION_CREDIT_CONTROL_STATUS_CHANGE', 1);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Credit Control Status Change Email Notification. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DELETE FROM email_notification " .
								"WHERE const_name IN ('EMAIL_NOTIFICATION_CREDIT_CONTROL_STATUS_CHANGE');";
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