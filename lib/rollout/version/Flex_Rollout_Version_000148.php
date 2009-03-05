<?php

/**
 * Version 148 of database update.
 * This version: -
 *
 *	1:	Change the CREDIT_CONTROL_STATUS_SENDING_TO_AUSTRAL constant to CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION
 *	2:	Change the CREDIT_CONTROL_STATUS_WITH_AUSTRAL constant to CREDIT_CONTROL_STATUS_WITH_DEBT_COLLECTION
 *	3:	Remove the auto_increment property from credit_control_status.id because we don't need it, and can cause integrety issues with rollbacks in this script
 *	4:	Add the CREDIT_CONTROL_STATUS_WIN_BACK and CREDIT_CONTROL_STATUS_PAYMENT_PLAN to the credit_control_status table, giving them the same rules as CREDIT_CONTROL_STATUS_EXTENSION
 *	5:	Change all references of 'Austral' to 'Debt Collection', in the customer_status table
 */

class Flex_Rollout_Version_000148 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Change the CREDIT_CONTROL_STATUS_SENDING_TO_AUSTRAL constant to CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION
		$strSQL = "UPDATE credit_control_status
SET name = 'Sending to Debt Collection',
const_name = 'CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION'
WHERE const_name = 'CREDIT_CONTROL_STATUS_SENDING_TO_AUSTRAL';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to change the CREDIT_CONTROL_STATUS_SENDING_TO_AUSTRAL constant to CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ") Query: $strSQL");
		}
		$this->rollbackSQL[] =	"UPDATE credit_control_status
SET name = 'Sending to Austral',
const_name = 'CREDIT_CONTROL_STATUS_SENDING_TO_AUSTRAL'
WHERE const_name = 'CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION';";

		// 2:	Change the CREDIT_CONTROL_STATUS_WITH_AUSTRAL constant to CREDIT_CONTROL_STATUS_WITH_DEBT_COLLECTION
		$strSQL = "UPDATE credit_control_status
SET name = 'With Debt Collection',
const_name = 'CREDIT_CONTROL_STATUS_WITH_DEBT_COLLECTION'
WHERE const_name = 'CREDIT_CONTROL_STATUS_WITH_AUSTRAL';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to change the CREDIT_CONTROL_STATUS_WITH_AUSTRAL constant to CREDIT_CONTROL_STATUS_WITH_DEBT_COLLECTION " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ") Query: $strSQL");
		}
		$this->rollbackSQL[] =	"UPDATE credit_control_status
SET name = 'With Austral',
const_name = 'CREDIT_CONTROL_STATUS_WITH_AUSTRAL'
WHERE const_name = 'CREDIT_CONTROL_STATUS_WITH_DEBT_COLLECTION';";

		// 3:	Remove the auto_increment property from credit_control_status.id because we don't need it
		$strSQL = "ALTER TABLE credit_control_status CHANGE id id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Id for the status';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to remove the auto_increment property from credit_control_status.id " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ") Query: $strSQL");
		}
		$this->rollbackSQL[] =	"ALTER TABLE credit_control_status CHANGE id id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Id for the status';";

		// 4:	Add the CREDIT_CONTROL_STATUS_WIN_BACK and CREDIT_CONTROL_STATUS_PAYMENT_PLAN to the credit_control_status table, giving them the same rules as CREDIT_CONTROL_STATUS_EXTENSION
		$strSQL = "INSERT INTO credit_control_status (id, name, description, const_name, can_bar, send_late_notice)
VALUES
(5, 'Win Back', 'Do not bar.', 'CREDIT_CONTROL_STATUS_WIN_BACK', 0, 1),
(6, 'Payment Plan', 'Do not bar.', 'CREDIT_CONTROL_STATUS_PAYMENT_PLAN', 0, 1);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to add the CREDIT_CONTROL_STATUS_WIN_BACK and CREDIT_CONTROL_STATUS_PAYMENT_PLAN to the credit_control_status table " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ") Query: $strSQL");
		}
		$this->rollbackSQL[] =	"DELETE FROM credit_control_status WHERE id IN (5, 6);";
		
		
		// 5:	Change all references of 'Austral' to 'Debt Collection', in the customer_status table
		// For customer_status H
		$strSQL = "UPDATE customer_status
SET description = 'Account has gone to Debt Collection',
test = 'AccountWithDebtCollection'
WHERE name = 'H';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to change 'Austral' references to 'Debt Collection' in the customer_status table " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ") Query: $strSQL");
		}
		$this->rollbackSQL[] =	"UPDATE customer_status
SET description = 'Account has gone to Austral',
test = 'AccountWithAustral'
WHERE name = 'H';";
		
		// For customer_status G
		$strSQL = "UPDATE customer_status
SET description = 'Account is ready for Debt Collection but has not gone there yet',
test = 'AccountReadyForDebtCollection'
WHERE name = 'G';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to change 'Austral' references to 'Debt Collection' in the customer_status table " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ") Query: $strSQL");
		}
		$this->rollbackSQL[] =	"UPDATE customer_status
SET description = 'Account is ready for Austral but has not gone there yet',
test = 'AccountReadyForAustral'
WHERE name = 'G';";
		
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