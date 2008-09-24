<?php

/**
 * Version 59 of database update.
 * This version: -
 *	1:	Add invoice_run_id to all Flex tables with InvoiceRun columns (except InvoiceRun table)
 *	2:	Populates invoice_run_id column just added to all Flex tables with InvoiceRun columns (except InvoiceRun table) 
 *	3:	Drops InvoiceRun column from all Flex tables with InvoiceRun columns (except InvoiceRun table)
 */

class Flex_Rollout_Version_000059 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		$arrTables = array(
			'Charge',
			'Invoice',
			'InvoiceOutput',
			'InvoiceOutputArchive',
			'InvoicePayment',
			'InvoiceTemp',
			'ServiceTotal',
			'ServiceTypeTotal',
			'CDR',
		);

		foreach ($arrTables as $strTable)
		{
			// 1:	Add new column
			$strSQL = "ALTER TABLE $strTable ADD invoice_run_id BIGINT(20) NULL COMMENT 'FK to InvoiceRun table' AFTER InvoiceRun;"; 
	
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to add columns to $strTable table. " . $qryQuery->Error());
			}
			$this->rollbackSQL[] = "ALTER TABLE $strTable DROP invoice_run_id;"; 

			// 2:	Populate new column
			$strSQL = "UPDATE $strTable SET invoice_run_id = (SELECT Id FROM InvoiceRun WHERE InvoiceRun.InvoiceRun = $strTable.InvoiceRun);"; 

		}

		foreach ($arrTables as $strTable)
		{
			// 3:	Drop InvoiceRun column
			$strSQL = "ALTER TABLE $strTable DROP InvoiceRun;"; 
	
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to drop InvoiceRun column from $strTable table. " . $qryQuery->Error());
			}

			$this->rollbackSQL[] = "UPDATE $strTable SET InvoiceRun = (SELECT InvoiceRun FROM InvoiceRun WHERE InvoiceRun.Id = $strTable.invoice_run_id);"; 
			$this->rollbackSQL[] = "ALTER TABLE $strTable ADD InvoiceRun varchar(32) default NULL COMMENT 'FK to InvoiceRun table' AFTER invoice_run_id;"; 
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



