<?php

/**
 * Version 125 of database update.
 * This version: -
 *	1:	Add new Invoice Run Types INVOICE_RUN_TYPE_INTERIM and INVOICE_RUN_TYPE_FINAL
 */

class Flex_Rollout_Version_000125 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add new Invoice Run Types INVOICE_RUN_TYPE_INTERIM and INVOICE_RUN_TYPE_FINAL
		$strSQL = "INSERT INTO invoice_run_type (name, description, const_name) VALUES " .
					"('Interim Invoice'	, 'Interim Invoice'	, 'INVOICE_RUN_TYPE_INTERIM'), " .
					"('Final Invoice'	, 'Final Invoice'	, 'INVOICE_RUN_TYPE_FINAL');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add new Invoice Run Types INVOICE_RUN_TYPE_INTERIM and INVOICE_RUN_TYPE_FINAL. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM invoice_run_type WHERE const_name IN ('INVOICE_RUN_TYPE_INTERIM', 'INVOICE_RUN_TYPE_FINAL');";
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