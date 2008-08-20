<?php

/**
 * Version 32 (thirty-two) of database update.
 * This version: -
 *	1:	Create the invoice_run_type Table
 *	2:	Populate the invoice_run_type Table
 *	3:	Modify InvoiceRun table to allow NULLs in some fields, and add the invoice_run_type Field
 *	4:	Populate the InvoiceRun.invoice_run_type Field
 *	5:	Remove the InvoiceTemp Table (Temp Invoices will now be in the Invoice table)
 *	6:	Add invoice_run_id Field to all tables (except InvoiceRun) which currently have a VARCHAR() InvoiceRun field and populate
 *	7:	Add invoice_run_schedule Table
 *	8:	Populate invoice_run_schedule Table
 *	9:	Remove Sample Invoice Run details from the payment_terms table
 */

class Flex_Rollout_Version_000032 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Create the invoice_run_type Table
		$strSQL = "CREATE TABLE invoice_run_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Invoice Run Type', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Invoice Run Type', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Invoice Run Type'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Invoice Run Type'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add invoice_run_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS invoice_run_type;";
		
		// 2:	Populate the invoice_run_type Table
		$strSQL = "INSERT INTO invoice_run_type (id, name, description, const_name) VALUES " .
				"	(NULL, 'Live Run'			, 'Live Run'			, 'INVOICE_RUN_TYPE_LIVE'),
					(NULL, 'Internal Samples'	, 'Internal Samples'	, 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES'),
					(NULL, 'Samples'			, 'Samples'				, 'INVOICE_RUN_TYPE_SAMPLES');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate invoice_run_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE invoice_run_type;";
		
		// 3:	Modify InvoiceRun table to allow NULLs in some fields, and add the invoice_run_type Field
		$strSQL = "ALTER TABLE InvoiceRun " .
					"MODIFY InvoiceCount INT(11) NULL, " .
					"MODIFY BillCost DECIMAL(13, 4) NULL, " .
					"MODIFY BillRated DECIMAL(13, 4) NULL, " .
					"MODIFY BillInvoiced DECIMAL(13, 4) NULL, " .
					"MODIFY BillTax DECIMAL(13, 4) NULL, " .
					"MODIFY BalanceData VARCHAR(32767) NULL, " .
					"ADD invoice_run_type BIGINT(20) UNSIGNED NOT NULL;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter InvoiceRun table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE InvoiceRun " .
								"MODIFY InvoiceCount INT(11) NOT NULL, " .
								"MODIFY BillCost DECIMAL(13, 4) NOT NULL, " .
								"MODIFY BillRated DECIMAL(13, 4) NOT NULL, " .
								"MODIFY BillInvoiced DECIMAL(13, 4) NOT NULL, " .
								"MODIFY BillTax DECIMAL(13, 4) NOT NULL, " .
								"MODIFY BalanceData VARCHAR(32767) NOT NULL, " .
								"DROP invoice_run_type;";
		
		// 4:	Populate the InvoiceRun.invoice_run_type Field
		$strSQL = "UPDATE InvoiceRun SET invoice_run_type = (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_LIVE')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to update provisioning_type.provisioning_type_nature Field. ' . $qryQuery->Error());
		}
		
		// 5:	Remove the InvoiceTemp Table (Temp Invoices will now be in the Invoice table)
		$strSQL = "DROP TABLE InvoiceTemp;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to update provisioning_type.provisioning_type_nature Field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "CREATE TABLE IF NOT EXISTS `InvoiceTemp` (
								  `Id` bigint(20) unsigned NOT NULL auto_increment,
								  `AccountGroup` bigint(20) unsigned NOT NULL,
								  `Account` bigint(20) unsigned NOT NULL,
								  `CreatedOn` date NOT NULL,
								  `DueOn` date NOT NULL,
								  `SettledOn` date default NULL,
								  `Credits` decimal(13,4) NOT NULL,
								  `Debits` decimal(13,4) NOT NULL,
								  `Total` decimal(13,4) NOT NULL,
								  `Tax` decimal(13,4) NOT NULL,
								  `TotalOwing` decimal(13,4) NOT NULL,
								  `Balance` decimal(13,4) NOT NULL,
								  `Disputed` decimal(13,4) NOT NULL,
								  `AccountBalance` decimal(13,4) NOT NULL,
								  `DeliveryMethod` int(10) unsigned NOT NULL,
								  `Status` int(10) unsigned NOT NULL,
								  `InvoiceRun` varchar(32) default NULL,
								  PRIMARY KEY  (`Id`),
								  KEY `AccountGroup` (`AccountGroup`),
								  KEY `Account` (`Account`),
								  KEY `Status` (`Status`),
								  KEY `InvoiceRun` (`InvoiceRun`)
								) ENGINE=InnoDB;";
		
		//	6:	Add invoice_run_id Field to all tables (except InvoiceRun) which currently have a VARCHAR() InvoiceRun field
		$strInvoiceRunTable	= "{$GLOBALS['**arrDatabase']['flex']['Database']}.InvoiceRun";
		$arrExclude			= Array('InvoiceRun');
		$arrUpdateDBs		= Array();
		$arrUpdateDBs[]		= $GLOBALS['**arrDatabase']['cdr']['Database'];
		$arrUpdateDBs[]		= $GLOBALS['**arrDatabase']['flex']['Database'];
		
		// Determine list of tables to update
		$arrUpdateTables	= Array();
		foreach ($arrUpdateDBs as $strDB)
		{
			$resResult	= $qryQuery->Execute("SHOW FULL TABLES FROM {$strDB} WHERE Table_type = 'BASE TABLE';");
			while ($arrRow = $resResult->fetch_row())
			{
				// Ensure that this table and is not supposed to be skipped
				if (!in_array($arrRow, $arrExclude))
				{
					// Does this table has an InvoiceRun VARCHAR(32) field?
					$resColList	= $qryQuery->Execute("SHOW COLUMNS FROM {$arrRow[0]} WHERE Field = 'InvoiceRun' AND Type = 'varchar(32)'");
					if ($resColList->num_rows)
					{
						// Add this table to list of tables to update
						$arrUpdateTables[]	= "{$strDB}.{$arrRow[0]}";
					}
				}
			}
		}
		
		// Update each table
		foreach ($arrUpdateTables as $strTable)
		{
			$strSQL = "ALTER TABLE {$strTable} ADD invoice_run_id BIGINT(20) UNSIGNED NULL;";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . ' Failed to add invoice_run_id to '.$strTable.' table. ' . $qryQuery->Error());
			}
			$this->rollbackSQL[] = "ALTER TABLE {$strTable} DROP invoice_run_id;";
			
			$strSQL = "UPDATE {$strTable} SET invoice_run_id = (SELECT Id FROM InvoiceRun WHERE InvoiceRun = {$strTable}.InvoiceRun);";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . ' Failed to populate invoice_run_id in '.$strTable.' table. ' . $qryQuery->Error());
			}
		}
		
		//	7:	Add invoice_run_schedule Table
		$strSQL = "CREATE TABLE invoice_run_schedule " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Scheduled Invoice Run', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Scheduled Invoice Run', " .
						"invoice_day_offset INT(11) NOT NULL COMMENT 'Offset in days from the Billing Date that this will run', " .
						"invoice_run_type BIGINT(20) NOT NULL COMMENT 'The Type of Invoice Run';" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add invoice_run_schedule Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS invoice_run_schedule;";
		
		//	8:	Populate invoice_run_schedule Table
		$strSQL = "INSERT INTO invoice_run_schedule (id, description, invoice_day_offset, invoice_run_type) VALUES " .
				"	(NULL, 'Gold (Live) Run'			, 0		, (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_LIVE')),
					(NULL, 'Gold Internal Samples'		, -1	, (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES')),
					(NULL, 'Silver Samples'				, -3	, (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_SAMPLES')),
					(NULL, 'Silver Internal Samples'	, -4	, (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES')),
					(NULL, 'Bronze Samples'				, -7	, (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_SAMPLES')),
					(NULL, 'Bronze Internal Samples'	, -10	, (SELECT id FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_INTERNAL_SAMPLES'));";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate invoice_run_schedule Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE invoice_run_schedule;";
		
		//	9:	Remove Sample Invoice Run details from the payment_terms table
		$strSQL = "ALTER TABLE payment_terms " .
					"DROP samples_internal_initial_days, " .
					"DROP samples_internal_final_days, " .
					"DROP samples_bronze_days, " .
					"DROP samples_silver_days;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to remove Sample Invoice Run details from the payment_terms table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE payment_terms " .
									"ADD samples_internal_initial_days SMALLINT(6) NOT NULL COMMENT 'Offset in days from the Billing Date that the Initial YBS Internal Samples are run',
									ADD samples_internal_final_days SMALLINT(6) NOT NULL COMMENT 'Offset in days from the Billing Date that the Final YBS Internal Samples are run',
									ADD samples_bronze_days SMALLINT(6) NOT NULL COMMENT 'Offset in days from the Billing Date that the Bronze Samples are run',
									ADD samples_silver_days SMALLINT(6) NOT NULL COMMENT 'Offset in days from the Billing Date that the Silver Samples are run';";
		
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
