<?php

/**
 * Version 69 (Sixty-Nine (the funny number)) of database update.
 * This version: -
 *	1:	Creates the account_history table
 *	2:	Populates it with the current state of the Account table
 */

class Flex_Rollout_Version_000069 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Create the account_history table
		$strSQL = "	CREATE TABLE account_history
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for this record',
						change_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time at which the change in the account record was made',
						employee_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'reference to the employee who changed the state of the account record',
						account_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'foreign key into Account table',

						billing_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'defines how the invoice is paid by the customer',
						credit_card_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'defines the (direct debit) credit card details',
						direct_debit_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'defines the (direct debit) bank account details',
						billing_method INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'defines how the invoice is sent to the customer',
						disable_ddr TINYINT(1) NULL DEFAULT NULL COMMENT 'boolean flagging the disabling of the admin fee. 0 = charge lpf, 1 = don''t charge lpf',
						late_payment_amnesty DATE DEFAULT NULL COMMENT 'If this is set, no late payment notices are generated until after this date',
						tio_reference_number VARCHAR(150) DEFAULT NULL COMMENT 'reference number when dealing with the T.I.O.',

						INDEX account_id (account_id),
						INDEX employee_id (employee_id),
						FOREIGN KEY (account_id) REFERENCES Account(Id) ON DELETE CASCADE ON UPDATE CASCADE,
						FOREIGN KEY (employee_id) REFERENCES Employee(Id) ON DELETE NO_ACTION ON UPDATE CASCADE
					) ENGINE = innodb COMMENT = 'history of account table';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create account_history table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS account_history;";

		// 2:	Populate it with the current state of the Account table
		$strSQL = "	INSERT INTO account_history (change_timestamp, account_id, billing_type, credit_card_id, direct_debit_id, billing_method, disable_ddr, late_payment_amnesty, tio_reference_number)
					SELECT NOW(), Id, BillingType, CreditCard, DirectDebit, BillingMethod, DisableDDR, LatePaymentAmnesty, tio_reference_number
					FROM Account;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to perform initial population of the account_history table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM account_history;";
		
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
