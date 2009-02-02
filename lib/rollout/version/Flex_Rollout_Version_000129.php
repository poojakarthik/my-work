<?php

/**
 * Version 129 of database update.
 * This version: -
 *	1:	Inserts records into email_notification table for EMAIL_NOTIFICATION_SALE_IMPORT_REPORT and EMAIL_NOTIFICATION_SALE_AUTOMATIC_PROVISIONING_REPORT
 */

class Flex_Rollout_Version_000129 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Insert records into email_notification table for EMAIL_NOTIFICATION_SALE_IMPORT_REPORT and EMAIL_NOTIFICATION_SALE_AUTOMATIC_PROVISIONING_REPORT
		$strSQL = "	INSERT INTO email_notification (name, description, const_name, allow_customer_group_emails)
					VALUES 
					('Sale Import Report', 'Report on the outcome of the Sale Import batch process', 'EMAIL_NOTIFICATION_SALE_IMPORT_REPORT', 0),
					('Sale Automatic Provisioning Report', 'Report on the outcome of the Sale Automatic Provisioning batch process', 'EMAIL_NOTIFICATION_SALE_AUTOMATIC_PROVISIONING_REPORT', 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to insert records into email_notification table for EMAIL_NOTIFICATION_SALE_IMPORT_REPORT and EMAIL_NOTIFICATION_SALE_AUTOMATIC_PROVISIONING_REPORT - " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name IN ('EMAIL_NOTIFICATION_SALE_IMPORT_REPORT', 'EMAIL_NOTIFICATION_SALE_AUTOMATIC_PROVISIONING_REPORT');";
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