<?php

/**
 * Version 70 of database update.
 * This version: -
 *	1:	Adds the account_status.deliver_invoice field
 *	2:	Populates the account_status.deliver_invoice field
 *	3:	Change ACCOUNT_STATUS_DEBT_COLLECTION.can_invoice to 1
 */

class Flex_Rollout_Version_000070 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the account_status.deliver_invoice field
		$strSQL = "ALTER TABLE account_status ADD deliver_invoice TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Invoices can be delivered; 0: Invoices are never delivered' AFTER can_invoice;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_status.deliver_invoice field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE account_status DROP deliver_invoice;";
		
		// 2:	Populates the account_status.deliver_invoice field
		$strSQL = "UPDATE account_status SET deliver_invoice = 1 WHERE name IN ('Active', 'Closed');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the account_status.deliver_invoice field. ' . $result->getMessage());
		}
		
		// 3:	Change ACCOUNT_STATUS_DEBT_COLLECTION.can_invoice to 1
		$strSQL = "UPDATE account_status SET can_invoice = 1 WHERE const_name = 'ACCOUNT_STATUS_DEBT_COLLECTION';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to change ACCOUNT_STATUS_DEBT_COLLECTION.can_invoice to 1. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "UPDATE account_status SET can_invoice = 0 WHERE const_name = 'ACCOUNT_STATUS_DEBT_COLLECTION';";
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
