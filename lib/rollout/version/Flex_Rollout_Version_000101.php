<?php

/**
 * Version 101 of database update.
 * This version: -
 *	1:	Adds the CustomerGroup.invoice_cdr_credits Field
 */

class Flex_Rollout_Version_000101 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the CustomerGroup.invoice_cdr_credits Field
		$strSQL = "ALTER TABLE CustomerGroup " .
					"ADD invoice_cdr_credits TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: CDR Credits are Invoiced; 0: CDR Credits are Suppressed';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the CustomerGroup.invoice_cdr_credits Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE CustomerGroup DROP invoice_cdr_credits;";
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
