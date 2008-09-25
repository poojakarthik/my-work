<?php

/**
 * Version 57 of database update.
 * This version: -
 *	1:	Add invoice_run_id to all Flex tables with InvoiceRun columns (except InvoiceRun table)
 *	2:	Populates invoice_run_id column just added to all Flex tables with InvoiceRun columns (except InvoiceRun table) 
 *	3:	Drops InvoiceRun column from all Flex tables with InvoiceRun columns (except InvoiceRun table)
 */

class Flex_Rollout_Version_000057 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		


		// Drop keys and composite indicies that use InvoiceRun

		self::outputMessage("Dropping ServiceTotal.RecordType KEY ...");

		$strSQL = "ALTER TABLE ServiceTotal DROP KEY RecordType";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to drop RecordType KEY from ServiceTotal table. " . $qryQuery->Error());
		}

		$this->rollbackSQL[] = "ALTER TABLE ServiceTotal ADD UNIQUE KEY RecordType (Service, InvoiceRun)";

		self::outputMessage(" done.\n");



		self::outputMessage("Dropping ServiceTypeTotal.RecordType KEY ...");

		$strSQL = "ALTER TABLE ServiceTypeTotal DROP KEY RecordType";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to drop RecordType KEY from ServiceTypeTotal table. " . $qryQuery->Error());
		}

		$this->rollbackSQL[] = "ALTER TABLE ServiceTypeTotal ADD UNIQUE KEY RecordType (Service, InvoiceRun, RecordType, FNN)";

		self::outputMessage(" done.\n");


		
		self::outputMessage("Dropping CDR.Service_3 INDEX ...");

		$strSQL = "ALTER TABLE CDR DROP INDEX Service_3";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to drop Service_3 INDEX from CDR table. " . $qryQuery->Error());
		}

		$this->rollbackSQL[] = "ALTER TABLE CDR ADD INDEX Service_3 (Service, InvoiceRun, RecordType)";

		self::outputMessage(" done.\n");


		
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

			self::outputMessage("Adding $strTable.invoice_run_id column ...");

			$strSQL = "ALTER TABLE $strTable ADD invoice_run_id BIGINT(20) NULL COMMENT 'FK to InvoiceRun table' AFTER InvoiceRun;"; 
	
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to add columns to $strTable table. " . $qryQuery->Error());
			}
			$this->rollbackSQL[] = "ALTER TABLE $strTable DROP invoice_run_id;"; 

			self::outputMessage(" done.\n");



			// 2:	Populate new column

			self::outputMessage("Populating $strTable.invoice_run_id column ...");

			$strSQL = "UPDATE $strTable SET invoice_run_id = (SELECT Id FROM InvoiceRun WHERE InvoiceRun.InvoiceRun = $strTable.InvoiceRun);"; 

			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to populate invoice_run_id column of $strTable table. " . $qryQuery->Error());
			}

			self::outputMessage(" done.\n");
		}

		foreach ($arrTables as $strTable)
		{
			// Drop InvoiceRun indicies
			
			self::outputMessage("Dropping $strTable.InvoiceRun index ...");
			
			$strSQL = "ALTER TABLE $strTable DROP INDEX InvoiceRun";

			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to drop InvoiceRun INDEX from $strTable table. " . $qryQuery->Error());
			}

			$this->rollbackSQL[] = "ALTER TABLE $strTable ADD INDEX InvoiceRun (InvoiceRun)";
			
			self::outputMessage(" done.\n");			



			// 3:	Drop InvoiceRun column

			self::outputMessage("Dropping $strTable.InvoiceRun column ...");

			$strSQL = "ALTER TABLE $strTable DROP InvoiceRun;"; 
	
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to drop InvoiceRun column from $strTable table. " . $qryQuery->Error());
			}

			$this->rollbackSQL[] = "UPDATE $strTable SET InvoiceRun = (SELECT InvoiceRun FROM InvoiceRun WHERE InvoiceRun.Id = $strTable.invoice_run_id);"; 
			$this->rollbackSQL[] = "ALTER TABLE $strTable ADD InvoiceRun varchar(32) default NULL COMMENT 'FK to InvoiceRun table' AFTER invoice_run_id;"; 

			self::outputMessage(" done.\n");



			// 4:	Add the invoice_run_id index

			self::outputMessage("Adding $strTable.invoice_run_id index ...");

			$strSQL = "ALTER TABLE $strTable ADD INDEX invoice_run_id (invoice_run_id)";

			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . " Failed to add invoice_run_id INDEX to $strTable table. " . $qryQuery->Error());
			}

			$this->rollbackSQL[] = "ALTER TABLE $strTable DROP INDEX invoice_run_id";

			self::outputMessage(" done.\n");
		}


		// Add keys

		self::outputMessage("Adding new ServiceTypeTotal.RecordType KEY ...");

		$strSQL = "ALTER TABLE ServiceTypeTotal ADD UNIQUE KEY RecordType (Service, invoice_run_id, RecordType, FNN)";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to add RecordType KEY to ServiceTypeTotal table. " . $qryQuery->Error());
		}

		$this->rollbackSQL[] = "ALTER TABLE ServiceTypeTotal DROP KEY RecordType";

		self::outputMessage(" done.\n");


		
		self::outputMessage("Adding new ServiceTotal.RecordType KEY ...");

		$strSQL = "ALTER TABLE ServiceTotal ADD UNIQUE KEY RecordType (Service, invoice_run_id)";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to add RecordType KEY to ServiceTotal table. " . $qryQuery->Error());
		}

		$this->rollbackSQL[] = "ALTER TABLE ServiceTotal DROP KEY RecordType";

		self::outputMessage(" done.\n");


		
		self::outputMessage("Adding new CDR.Service_3 INDEX ...");

		$strSQL = "ALTER TABLE CDR ADD INDEX Service_3 (Service, invoice_run_id, RecordType)";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . " Failed to add Service_3 INDEX to CDR table. " . $qryQuery->Error());
		}

		$this->rollbackSQL[] = "ALTER TABLE CDR DROP INDEX Service_3";

		self::outputMessage(" done.\n");

	}

	function rollback()
	{
		self::outputMessage("Rolling back " . __CLASS__ . " ...\n");
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				self::outputMessage("... " . $this->rollbackSQL[$l] . "\n");
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}



