<?php

/**
 * Version 34 of database update.
 * This version: -
 *	1:	Alter credit_card_payment_config table
 */

class Flex_Rollout_Version_000034 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Alter credit_card_payment_config table
		$strSQL = "
			ALTER TABLE credit_card_payment_config 
			MODIFY confirmation_text mediumtext NOT NULL COMMENT 'Message displayed to confirm payment',
			MODIFY direct_debit_text mediumtext NOT NULL COMMENT 'Message displayed to confirm direct debit setup',
			ADD confirmation_email mediumtext NOT NULL COMMENT 'Body of email sent to confirm payment',
			ADD direct_debit_email mediumtext NOT NULL COMMENT 'Body of email sent to confirm direct debit setup',
			ADD direct_debit_disclaimer mediumtext NOT NULL COMMENT 'Terms and conditions displayed to user when opting for direct debit' ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter credit_card_payment_config table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE credit_card_payment_config DROP confirmation_email, DROP direct_debit_email, DROP direct_debit_disclaimer";
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
