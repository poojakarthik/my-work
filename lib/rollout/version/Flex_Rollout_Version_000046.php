<?php

/**
 * Version 46 of database update.
 * This version: -
 *	1:	Alter credit_card_payment_history table
 *	2:	Add email_notification for payment confimations and direct debit setup
 */

class Flex_Rollout_Version_000046 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Alter credit_card_payment_config table
		$strSQL = "
			ALTER TABLE credit_card_payment_history 
			ADD txn_id VARCHAR(255) DEFAULT NULL COMMENT 'TXN Id issued by credit card payment service provider',
			ADD payment_id BIGINT(20) NOT NULL COMMENT 'FK to the Payment table'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter credit_card_payment_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE credit_card_payment_history DROP txn_id, DROP payment_id";

		//	2:	Add email_notification for payment confimations and direct debit setup
		$strSQL = "INSERT INTO email_notification (id, name, description, const_name, allow_customer_group_emails) VALUES " .
					"(NULL, 'Payment Confirmation', 'Email sent to customers to acknowledge payments and direct debit setup', 'EMAIL_NOTIFICATION_PAYMENT_CONFIRMATION', 1);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add email_notification for payment confimations and direct debit setup. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name = 'EMAIL_NOTIFICATION_PAYMENT_CONFIRMATION';";
	}

	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
