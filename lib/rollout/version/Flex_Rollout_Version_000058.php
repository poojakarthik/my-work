<?php

/**
 * Version 58 of database update.
 * This version: -
 *	1:	Create the invoice_run_type Table
 *	2:	Populate the invoice_run_type Table
 *	3:	Modify InvoiceRun table to allow NULLs in some fields, and add the invoice_run_type and customer_group_id Fields
 *	4:	Populate the InvoiceRun.invoice_run_type Field
 *	5:	Remove the InvoiceTemp Table (Temp Invoices will now be in the Invoice table)
 *	6:	Add invoice_run_schedule Table
 *	7:	Remove Sample Invoice Run details from the payment_terms table
 *	8:	Add ChargeType.automatic_only Field (means it can't be applied by employees)
 *	9:	Add 'PCAD', 'PCAR', 'PCR' ChargeTypes
 *	10:	Add Charge.charge_type_id Field
 *	11:	Add tax_type Table
 *	12:	Populate tax_type Table
 *	13:	Add RecordType.global_tax_exempt
 *	14:	Add Charge.global_tax_exempt
 */

class Flex_Rollout_Version_000058 extends Flex_Rollout_Version
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
		
		// 3:	Modify InvoiceRun table to allow NULLs in some fields, and add the invoice_run_type and customer_group_id Fields
		$strSQL = "ALTER TABLE InvoiceRun " .
					"MODIFY InvoiceCount INT(11) NULL, " .
					"MODIFY BillCost DECIMAL(13, 4) NULL, " .
					"MODIFY BillRated DECIMAL(13, 4) NULL, " .
					"MODIFY BillInvoiced DECIMAL(13, 4) NULL, " .
					"MODIFY BillTax DECIMAL(13, 4) NULL, " .
					"MODIFY BalanceData VARCHAR(32767) NULL, " .
					"ADD invoice_run_type BIGINT(20) UNSIGNED NOT NULL COMMENT '(FK) The type of InvoiceRun' AFTER BillingDate, " .
					"ADD customer_group_id BIGINT(20) UNSIGNED NULL COMMENT '(FK) CustomerGroup this InvoiceRun applies to' AFTER Id;";
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
								"DROP invoice_run_type, " .
								"DROP customer_group_id;";
		
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
		
		//	6:	Add invoice_run_schedule Table
		$strSQL = "CREATE TABLE invoice_run_schedule " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Scheduled Invoice Run', " .
						"customer_group_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'CusotmerGroup this InvoiceRun applies to', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Scheduled Invoice Run', " .
						"invoice_day_offset INT(11) NOT NULL COMMENT 'Offset in days from the Billing Date that this will run', " .
						"invoice_run_type_id BIGINT(20) NOT NULL COMMENT 'The Type of Invoice Run';" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add invoice_run_schedule Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS invoice_run_schedule;";
		
		//	7:	Remove Sample Invoice Run details from the payment_terms table
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
		
		//	8:	Add ChargeType.automatic_only Field (means it can't be applied by employees)
		$strSQL = "ALTER TABLE ChargeType ADD automatic_only SMALLINT(1) NOT NULL DEFAULT 0 COMMENT '1: This can only be automatically added by Flex; 0: This can be manually added by an Employee' AFTER Fixed;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add automatic_only to the ChargeType table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE ChargeType DROP automatic_only;";
		
		//	9:	Add 'PCAD', 'PCAR', 'PCR' ChargeTypes
		$strSQL = "INSERT INTO ChargeType (ChargeType, Description, Nature, Fixed, automatic_only, Amount, Archived) VALUES " .
					"('PCAD', 'Plan Charge in Advance', 'DR', 0, 1, 0.0, 0), " .
					"('PCAR', 'Plan Charge in Arrears', 'DR', 0, 1, 0.0, 0), " .
					"('PCR', 'Plan Credit in Arrears', 'CR', 0, 1, 0.0, 0);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add \'PCAD\', \'PCAR\', \'PCR\' ChargeTypes ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM ChargeType WHERE ChargeType IN ('PCAD', 'PCAR', 'PCR');";
		
		//	10:	Add Charge.charge_type_id Field
		$strSQL = "ALTER TABLE Charge ADD charge_type_id BIGINT(20) NULL COMMENT '(FK) The ChargeType.Id that this implements' AFTER ChargeType;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add charge_type_id to the Charge table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Charge DROP charge_type_id;";
		
		//	11:	Add tax_type Table
		$strSQL = "CREATE TABLE tax_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Tax Rate', " .
						"name BIGINT(20) UNSIGNED NOT NULL COMMENT 'Name of the Tax', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Tax', " .
						"rate_percentage DECIMAL(13, 4) NOT NULL COMMENT 'The Tax Rate Percentage (eg. 0.10 for 10%)', " .
						"global TINYINT(1) NOT NULL COMMENT '1: This Tax Rate is applied to everything except exempted charges (should only be one of these); 0: This tax is only applied to specific charges', " .
						"start_datetime DATETIME NOT NULL COMMENT 'The date this tax becomes effective', " .
						"end_datetime DATETIME NOT NULL COMMENT 'The date this tax expires';" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add tax_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS tax_type;";
		
		//	12:	Populate tax_type Table
		if ($this->getUserResponseYesNo("Is there a Global Tax that should be applied to most charges (eg. GST/VAT)?"))
		{
			// Get details for the Global Tax
			$strTaxName			= $this->getUserResponse("Please enter the name of the Global Tax (eg. GST):");
			$strTaxDescription	= $this->getUserResponse("Please enter a description for the Global Tax (eg. Goods & Services Tax):");
			$fltTaxRatePercent	= $this->getUserResponseDecimal("Please enter the tax rate percentage (eg 0.1 for 10%)");
			
			$strSQL = "INSERT INTO tax_type (name, description, rate_percentage, global, start_datetime, end_datetime) VALUES " .
						"('{$strTaxName}', '{$strTaxDescription}', {$fltTaxRatePercent}, 1, '0000-00-00 00:00:00', '9999-12-31 23:59:59');";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . ' Failed to add Global Tax Rate ' . $qryQuery->Error());
			}
			$this->rollbackSQL[] = "TRUNCATE TABLE tax_type;";
		}
		
		//	13:	Add and populate RecordType.global_tax_exempt
		$strSQL = "ALTER TABLE RecordType ADD global_tax_exempt TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: This RecordType WILL NOT have the Global Tax Rate applied; 0: This RecordType WILL have the Global Tax Rate applied';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add global_tax_exempt to the RecordType table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE RecordType DROP global_tax_exempt;";
		$strSQL = "UPDATE RecordType SET global_tax_exempt = 1 WHERE Code IN ('Roaming', 'OSNetworkAirtime');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate global_tax_exempt to the RecordType table. ' . $qryQuery->Error());
		}
		
		//	14:	Add Charge.global_tax_exempt
		$strSQL = "ALTER TABLE Charge ADD global_tax_exempt TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1: This Charge WILL NOT have the Global Tax Rate applied; 0: This Charge WILL have the Global Tax Rate applied';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add global_tax_exempt to the Charge table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Charge DROP global_tax_exempt;";
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
