<?php

/**
 * Version 185 of database update.
 * This version: -
 *	
 *	1:	Add the 1st Interim Invoice Report Email Notification
 *
 */

class Flex_Rollout_Version_000185 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the 1st Interim Invoice Report Email Notification
		$strSQL = "	INSERT INTO	email_notification
						(name, description, const_name, allow_customer_group_emails)
					VALUES
						('1st Interim Invoice Report'	, '1st Interim Invoice Report'	, 'EMAIL_NOTIFICATION_FIRST_INTERIM_INVOICE_REPORT', 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the 1st Interim Invoice Report Email Notification. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	email_notification
									WHERE		const_name = 'EMAIL_NOTIFICATION_FIRST_INTERIM_INVOICE_REPORT'
									LIMIT		1";
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