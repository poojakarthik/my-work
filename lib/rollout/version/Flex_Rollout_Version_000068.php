<?php

/**
 * Version 68 of database update.
 * This version: -
 *	1:	Adds the account_status.can_invoice field
 *	2:	Populates the account_status.can_invoice field
 *	3:	Adds the service_status.can_invoice field
 *	4:	Populates the service_status.can_invoice field
 */

class Flex_Rollout_Version_000068 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the account_status.can_invoice field
		$strSQL = "ALTER TABLE account_status ADD can_invoice TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Account can be Invoiced; 0: Account cannot be Invoiced' AFTER name;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_status.can_invoice field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE account_status DROP can_invoice;";
		
		// 2:	Populates the account_status.can_invoice field
		$strSQL = "UPDATE account_status SET can_invoice = 1 WHERE name IN ('Active', 'Closed');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the account_status.can_invoice field. ' . $result->getMessage());
		}
		
		// 3:	Adds the service_status.can_invoice field
		$strSQL = "ALTER TABLE service_status ADD can_invoice TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: Service can be Invoiced; 0: Service cannot be Invoiced' AFTER name;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the service_status.can_invoice field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE service_status DROP can_invoice;";
		
		// 4:	Populates the service_status.can_invoice field
		$strSQL = "UPDATE service_status SET can_invoice = 1 WHERE name IN ('Active', 'Disconnected');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the service_status.can_invoice field. ' . $result->getMessage());
		}
		
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
