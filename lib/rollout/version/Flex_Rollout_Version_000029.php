<?php

/**
 * Version 29 (twenty-nine) of database update.
 * This version: -
 *	1:	Add payment_terms.direct_debit_days and direct_debit_minimum Fields
 *	2:	Populate payment_terms.direct_debit_days and direct_debit_minimum Fields
 *	3:	Add Direct Debit record to automatic_invoice_action
 *	4:	Add resource_type_nature Table
 *	5:	Populate resource_type_nature Table
 *	6:	Add resource_type Table to replace FileImport, FileExport, FileResource ConstantGroups
 *	7:	Populate resource_type Table
 *	8:	Add carrier_module_type Table
 *	9:	Populate carrier_module_type Table
 *	10:	Remove all ConfigConstants in the ModuleType ConfigConstantGroup
 *	11:	Remove the ModuleType ConfigConstantGroup
 *	12:	Add CarrierModule.customer_group Field
 *	13:	Add Direct Debit Report details to email_notification
 */

class Flex_Rollout_Version_000029 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add payment_terms.direct_debit_days and direct_debit_minimum Fields
		$strSQL = "ALTER TABLE payment_terms " .
					"ADD direct_debit_days SMALLINT(6) NOT NULL COMMENT 'Number of days after invoicing that Direct Debits will be applied'," .
					"ADD direct_debit_minimum DECIMAL(4, 2) NOT NULL COMMENT 'Minimum Debt in order to be Direct Debited';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add payment_terms.direct_debit_days and direct_debit_minimum Fields. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE payment_terms DROP direct_debit_days, DROP direct_debit_minimum;";
		
		// 	2:	Populate payment_terms.direct_debit_days and direct_debit_minimum Fields
		$strSQL = "UPDATE payment_terms SET " .
					"direct_debit_days		= 15, " .
					"direct_debit_minimum	= 5.00;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate payment_terms.direct_debit_days and direct_debit_minimum Fields. ' . $qryQuery->Error());
		}
		
		//	3:	Add Direct Debit record to automatic_invoice_action
		$strSQL = "INSERT INTO automatic_invoice_action (id, name, description, const_name, days_from_invoice, can_schedule, response_days) VALUES " .
					"(NULL, 'Direct Debit', 'Direct Debit applied', 'AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT', 16, 1, 0);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add Direct Debit record to automatic_invoice_action. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM automatic_invoice_action WHERE const_name = 'AUTOMATIC_INVOICE_ACTION_DIRECT_DEBIT';";
		
		//	4:	Add resource_type_nature Table
		$strSQL = "CREATE TABLE resource_type_nature " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Resource Type Nature', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Resource Type Nature', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Resource Type Nature', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Resource Type Nature'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create resource_type_nature Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE resource_type_nature;";
		
		//	5:	Populate resource_type_nature Table
		$strSQL = "INSERT INTO resource_type_nature (id, name, description, const_name) VALUES " .
					"(NULL, 'Import File'		, 'Import File'		, 'RESOURCE_TYPE_NATURE_IMPORT_FILE'), " .
					"(NULL, 'Export File'		, 'Export File'		, 'RESOURCE_TYPE_NATURE_EXPORT_FILE'), " .
					"(NULL, 'File Repository'	, 'File Repository'	, 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY'), " .
					"(NULL, 'SOAP'				, 'SOAP Interface'	, 'RESOURCE_TYPE_NATURE_SOAP'); ";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate resource_type_nature Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE resource_type_nature;";
		
		//	6:	Add resource_type Table to replace FileImport, FileExport, FileResource ConstantGroups
		$strSQL = "CREATE TABLE resource_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Resource Type', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Resource Type', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Resource Type', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Resource Type', " .
						"resource_type_nature BIGINT(20) NOT NULL COMMENT 'Nature of this Resource Type'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create resource_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE resource_type;";
		
		//	7:	Populate resource_type Table
		$strSQL = "INSERT INTO resource_type (id, name, description, const_name, resource_type_nature) VALUES
					(1000	, 'Unitel Preselection File'			, 'Unitel Preselection File'			, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_UNITEL_PRESELECTION'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					(1001	, 'Unitel Daily Order File'				, 'Unitel Daily Order File'				, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_UNITEL_DAILY_ORDER'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					
					(1100	, 'AAPT EOE Request File'				, 'AAPT EOE Request File'				, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_EOE'						, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					
					(1200	, 'Optus Preselection File'				, 'Optus Preselection File'				, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_PRESELECTION'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					(1201	, 'Optus Barring File'					, 'Optus Barring File'					, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_BAR'					, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					(1202	, 'Optus Suspension File'				, 'Optus Suspension File'				, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_SUSPEND'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					(1203	, 'Optus Restoration File'				, 'Optus Restoration File'				, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_RESTORE'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					(1204	, 'Optus Preselection Reversal File'	, 'Optus Preselection Reversal File'	, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_PRESELECTION_REVERSAL'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					(1205	, 'Optus Deactivation File'				, 'Optus Deactivation File'				, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_OPTUS_DEACTIVATION'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					
					(1300	, 'SecurePay Credit Card Debit File'	, 'SecurePay Credit Card Debit File'	, 'RESOURCE_TYPE_FILE_EXPORT_SECUREPAY_CREDIT_CARD_FILE'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					(1301	, 'SecurePay Bank Transfer Debit File'	, 'SecurePay Bank Transfer Debit File'	, 'RESOURCE_TYPE_FILE_EXPORT_SECUREPAY_BANK_TRANSFER_FILE'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE')), 
					
					(5000	, 'Unitel Daily Order Report'			, 'Unitel Daily Order Report'			, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_ORDER'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(5001	, 'Unitel Daily Status Changes Report'	, 'Unitel Daily Status Changes Report'	, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(5002	, 'Unitel Agreed Baskets Report'		, 'Unitel Agreed Baskets Report'		, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_BASKETS'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(5003	, 'Unitel Preselection Report'			, 'Unitel Preselection Report'			, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_PRESELECTION'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(5004	, 'Unitel Line Status Report'			, 'Unitel Line Status Report'			, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_LINE_STATUS'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(5100	, 'Optus PPR Report'					, 'Optus PPR Report'					, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_OPTUS_PPR'					, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(5200	, 'AAPT EOE Return File'				, 'AAPT EOE Return File'				, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_EOE_RETURN'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(5201	, 'AAPT Line Status Database File'		, 'AAPT Line Status Database File'		, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LSD'						, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(5202	, 'AAPT Rejections Report'				, 'AAPT Rejections Report'				, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_REJECT'					, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(5203	, 'AAPT Loss Report'					, 'AAPT Loss Report'					, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_LOSS'					, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(4000	, 'Unitel Land Line CDR File'			, 'Unitel Land Line CDR File'			, 'RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD'						, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(4001	, 'Unitel Mobile CDR File'				, 'Unitel Mobile CDR File'				, 'RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE'							, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					(4002	, 'Unitel Service & Equipment CDR File'	, 'Unitel Service & Equipment CDR File'	, 'RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_S_AND_E'						, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(4100	, 'Optus Standard CDR File'				, 'Optus Standard CDR File'				, 'RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD'						, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(4200	, 'AAPT Standard CDR File'				, 'AAPT Standard CDR File'				, 'RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD'							, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(4300	, 'iSeek ADSL1 Usage CDR File'			, 'iSeek ADSL1 Usage CDR File'			, 'RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_ADSL1'							, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(4400	, 'M2 Standard CDR File'				, 'M2 Standard CDR File'				, 'RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD'							, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(3000	, 'Westpac BPay File'					, 'Westpac BPay File'					, 'RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BPAY_WESTPAC'						, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(3100	, 'BillExpress Standard Payments File'	, 'BillExpress Standard Payments File'	, 'RESOURCE_TYPE_FILE_IMPORT_PAYMENT_BILLEXPRESS_STANDARD'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(3200	, 'SecurePay Standard Payments File'	, 'SecurePay Standard Payments File'	, 'RESOURCE_TYPE_FILE_IMPORT_PAYMENT_SECUREPAY_STANDARD'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')), 
					
					(10000	, 'FTP File Server'						, 'FTP File Server'						, 'RESOURCE_TYPE_FILE_RESOURCE_FTP'										, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY')), 
					(10001	, 'SSH2 File Server'					, 'SSH2 File Server'					, 'RESOURCE_TYPE_FILE_RESOURCE_SSH2'									, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY')), 
					(10002	, 'AAPT XML File Resource'				, 'AAPT XML File Resource'				, 'RESOURCE_TYPE_FILE_RESOURCE_AAPT'									, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY')), 
					(10003	, 'Optus XML File Resource'				, 'Optus XML File Resource'				, 'RESOURCE_TYPE_FILE_RESOURCE_OPTUS'									, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY')), 
					(10004	, 'Local Path'							, 'Local Path'							, 'RESOURCE_TYPE_FILE_RESOURCE_LOCAL'									, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY'));";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate resource_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE resource_type;";
		
		//	8:	Add carrier_module_type Table
		$strSQL = "CREATE TABLE carrier_module_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Carrier Module Type', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Carrier Module Type', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Carrier Module Type', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Carrier Module Type'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create carrier_module_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE carrier_module_type;";
		
		//	9:	Populate carrier_module_type Table
		$strSQL = "INSERT INTO carrier_module_type (id, name, description, const_name) VALUES
					(500, 'Provisioning (Incoming)'	, 'Provisioning (Incoming)'	, 'MODULE_TYPE_PROVISIONING_INPUT'),
					(501, 'Provisioning (Outgoing)'	, 'Provisioning (Outgoing)'	, 'MODULE_TYPE_PROVISIONING_OUTPUT'),
					(502, 'Collection'				, 'Collection'				, 'MODULE_TYPE_COLLECTION'),
					(503, 'CDR Normalisation'		, 'CDR Normalisation'		, 'MODULE_TYPE_NORMALISATION_CDR'),
					(504, 'Payment Normalisation'	, 'Payment Normalisation'	, 'MODULE_TYPE_NORMALISATION_PAYMENT'), 
					(505, 'Direct Debit Requests'	, 'Direct Debit Requests'	, 'MODULE_TYPE_PAYMENT_DIRECT_DEBIT');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate carrier_module_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE carrier_module_type;";
		
		//	10:	Remove all ConfigConstants in the ModuleType ConfigConstantGroup
		$strSQL = "DELETE FROM ConfigConstant WHERE ConstantGroup = (SELECT Id FROM ConfigConstantGroup WHERE Name = 'ModuleType');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the ModuleType ConfigConstantGroup. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "INSERT INTO `ConfigConstant` (`Id`, `ConstantGroup`, `Name`, `Description`, `Value`, `Type`, `Editable`, `Deletable`) VALUES
								(NULL, (SELECT Id FROM ConfigConstantGroup WHERE Name = 'ModuleType'), 'MODULE_TYPE_PROVISIONING_INPUT', 'Provisioning (Incoming)', '500', 2, 0, 0),
								(NULL, (SELECT Id FROM ConfigConstantGroup WHERE Name = 'ModuleType'), 'MODULE_TYPE_PROVISIONING_OUTPUT', 'Provisioning (Outgoing)', '501', 2, 0, 0),
								(NULL, (SELECT Id FROM ConfigConstantGroup WHERE Name = 'ModuleType'), 'MODULE_TYPE_COLLECTION', 'Collection', '502', 2, 0, 0),
								(NULL, (SELECT Id FROM ConfigConstantGroup WHERE Name = 'ModuleType'), 'MODULE_TYPE_NORMALISATION_CDR', 'CDR Normalisation', '503', 2, 0, 0),
								(NULL, (SELECT Id FROM ConfigConstantGroup WHERE Name = 'ModuleType'), 'MODULE_TYPE_NORMALISATION_PAYMENT', 'Payment', '504', 2, 0, 0);";
		
		//	11:	Remove the ModuleType ConfigConstantGroup
		$strSQL = "DELETE FROM ConfigConstantGroup WHERE Name = 'ModuleType';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the ModuleType ConfigConstantGroup. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "INSERT INTO ConfigConstantGroup (Id, Name, Description, Type, Special, Extendable) VALUES (NULL, 'ModuleType', 'The various types of modules for Flex (eg. Provisioning Input/Output, Normalisation, Collection)', 2, 1, 1);";
		
		//	12:	Add CarrierModule.customer_group Field
		$strSQL = "ALTER TABLE CarrierModule " .
					"ADD customer_group BIGINT(20) NULL COMMENT 'The Customer Group that this Module is associated with.  NULL: All CustomerGroups';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add CarrierModule.customer_group and Field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE CarrierModule DROP customer_group;";
		
		//	13:	Add Direct Debit Report details to email_notification
		$strSQL = "INSERT INTO email_notification (id, name, description, const_name, allow_customer_group_emails) VALUES " .
					"(NULL, 'Direct Debit Report', 'Email listing Accounts that are being Direct Debited with output files attached', 'EMAIL_NOTIFICATION_DIRECT_DEBIT_REPORT', 0);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add Direct Debit Report details to email_notification. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM email_notification WHERE const_name = 'EMAIL_NOTIFICATION_DIRECT_DEBIT_REPORT';";
		
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
