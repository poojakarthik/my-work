<?php

/**
 * Version 60 of database update.
 * This version: -
 *	1:	Adds invoice_run_status Table
 *	2:	Populate the invoice_run_status Table
 *	3:	Adds invoice_run_status_id to the InvoiceRun table
 *	4:	Populate invoice_run_status_id Field
 */

class Flex_Rollout_Version_000060 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Create the invoice_run_status Table
		$strSQL = "CREATE TABLE invoice_run_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Invoice Run Status', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Invoice Run Status', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Invoice Run Status'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Invoice Run Status'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add invoice_run_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS invoice_run_status;";
		
		// 2:	Populate the invoice_run_status Table
		$strSQL = "INSERT INTO invoice_run_status (id, name, description, const_name) VALUES " .
				"	(NULL, 'Generating'	, 'Generating Invoices'	, 'INVOICE_RUN_STATUS_GENERATING'),
					(NULL, 'Temporary'	, 'Temporary'			, 'INVOICE_RUN_STATUS_TEMPORARY'),
					(NULL, 'Revoking'	, 'Revoking Invoices'	, 'INVOICE_RUN_STATUS_REVOKING'),
					(NULL, 'Revoked'	, 'Revoked'				, 'INVOICE_RUN_STATUS_REVOKED'),
					(NULL, 'Committing'	, 'Committing Invoices'	, 'INVOICE_RUN_STATUS_COMMITTING'),
					(NULL, 'Committed'	, 'Committed'			, 'INVOICE_RUN_STATUS_COMMITTED');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate invoice_run_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE invoice_run_status;";
		
		// 3:	Adds invoice_run_status_id to the InvoiceRun table
		$strSQL = "ALTER TABLE InvoiceRun ADD invoice_run_status_id BIGINT(20) NOT NULL COMMENT '(FK) Status of the Invoice Run';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter InvoiceRun table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE InvoiceRun DROP invoice_run_status_id;";
		
		// 4:	Populate invoice_run_status_id Field
		$strSQL	= "UPDATE InvoiceRun SET invoice_run_status_id = (SELECT id FROM invoice_run_status WHERE const_name = 'INVOICE_RUN_STATUS_COMMITTED');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate InvoiceRun.invoice_run_status_id. ' . $qryQuery->Error());
		}
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
