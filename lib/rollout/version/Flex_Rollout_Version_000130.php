<?php

/**
 * Version 130 of database update.
 * This version: -
 *	1:	Add the Invoice.billing_period_start_datetime and billing_period_end_datetime Fields
 *	2:	Populate the Invoice.billing_period_start_datetime and billing_period_end_datetime Fields
 *	3:	Add the InvoiceRun.billing_period_start_datetime and billing_period_end_datetime Fields
 *	4:	Populate the InvoiceRun.billing_period_start_datetime and billing_period_end_datetime Fields
 */

class Flex_Rollout_Version_000130 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the Invoice.billing_period_start_datetime and billing_period_end_datetime Fields
		$strSQL = "ALTER TABLE Invoice " .
					"ADD billing_period_start_datetime DATETIME NOT NULL COMMENT 'The Date on which the Billing Period starts' AFTER CreatedOn, " .
					"ADD billing_period_end_datetime DATETIME NOT NULL COMMENT 'The Date on which the Billing Period ends' AFTER billing_period_start_datetime;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Invoice.billing_period_start_datetime and billing_period_end_datetime Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "ALTER TABLE Invoice " .
								"DROP billing_period_start_datetime, " .
								"DROP billing_period_end_datetime;";
		
		// 2:	Populate the Invoice.billing_period_start_datetime and billing_period_end_datetime Fields
		$strSQL = "UPDATE Invoice SET billing_period_start_datetime = SUBDATE(CAST(CreatedOn AS DATETIME), INTERVAL 1 MONTH), billing_period_end_datetime = SUBDATE(CAST(CreatedOn AS DATETIME), INTERVAL 1 SECOND)";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the Invoice.billing_period_start_datetime and billing_period_end_datetime Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE Invoice SET billing_period_start_datetime = '0000-00-00 00:00:00', billing_period_end_datetime = '0000-00-00 00:00:00';";
		
		// 3:	Add the InvoiceRun.billing_period_start_datetime and billing_period_end_datetime Fields
		$strSQL = "ALTER TABLE InvoiceRun " .
					"ADD billing_period_start_datetime DATETIME NOT NULL COMMENT 'The Date on which the Billing Period starts' AFTER BillingDate, " .
					"ADD billing_period_end_datetime DATETIME NOT NULL COMMENT 'The Date on which the Billing Period ends' AFTER billing_period_start_datetime;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the InvoiceRun.billing_period_start_datetime and billing_period_end_datetime Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "ALTER TABLE InvoiceRun " .
								"DROP billing_period_start_datetime, " .
								"DROP billing_period_end_datetime;";
		
		// 4:	Populate the InvoiceRun.billing_period_start_datetime and billing_period_end_datetime Fields
		$strSQL = "UPDATE InvoiceRun SET billing_period_start_datetime = SUBDATE(CAST(BillingDate AS DATETIME), INTERVAL 1 MONTH), billing_period_end_datetime = SUBDATE(CAST(BillingDate AS DATETIME), INTERVAL 1 SECOND)";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the InvoiceRun.billing_period_start_datetime and billing_period_end_datetime Fields. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "UPDATE InvoiceRun SET billing_period_start_datetime = '0000-00-00 00:00:00', billing_period_end_datetime = '0000-00-00 00:00:00';";
		
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