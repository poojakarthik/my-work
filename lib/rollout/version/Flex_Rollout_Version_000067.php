<?php

/**
 * Version 67 of database update.
 * This version: -
 *	1:	Adds the InvoiceRun.invoice_run_schedule_id field
 */

class Flex_Rollout_Version_000067 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the InvoiceRun.invoice_run_schedule_id field
		$strSQL = "ALTER TABLE InvoiceRun ADD invoice_run_schedule_id BIGINT(20) UNSIGNED NULL COMMENT '(FK) The Scheduled Invoice Run (eg. Bronze Samples)' AFTER invoice_run_type_id;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the InvoiceRun.invoice_run_schedule_id field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE InvoiceRun DROP invoice_run_schedule_id;";
		
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
