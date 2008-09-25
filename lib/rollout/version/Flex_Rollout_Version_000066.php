<?php

/**
 * Version 66 of database update.
 * This version: -
 *	1:	Renames InvoiceRun.invoice_run_type to invoice_run_type_id
 */

class Flex_Rollout_Version_000066 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Renames InvoiceRun.invoice_run_type to invoice_run_type_id
		$strSQL = "ALTER TABLE InvoiceRun CHANGE invoice_run_type invoice_run_type_id BIGINT(20) UNSIGNED NOT NULL COMMENT '(FK) The type of InvoiceRun';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename InvoiceRun.invoice_run_type to invoice_run_type_id. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE InvoiceRun CHANGE invoice_run_type_id invoice_run_type BIGINT(20) UNSIGNED NOT NULL COMMENT '(FK) The type of InvoiceRun';";
		
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
