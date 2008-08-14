<?php

/**
 * Version 26 (twenty-six) of database update.
 * This version: -
 *	1:	Add payment_terms.direct_debit_days and direct_debit_minimum Fields
 *	2:	Populate payment_terms.direct_debit_days and direct_debit_minimum Fields
 *	3:	Add Direct Debit record to automatic_invoice_action
 *	4:	Add
 */

class Flex_Rollout_Version_000026 extends Flex_Rollout_Version
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
		$this->rollbackSQL[] = "ALTER TABLE payment_terms DROP direct_debit_days, direct_debit_minimum;";
		
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
		// TODO
		
		//	5:	Populate resource_type_nature Table
		// TODO
		
		//	6:	Add resource_type Table to replace FileImport, FileExport, FileResource ConstantGroups
		// TODO
		
		//	7:	Populate resource_type Table
		// TODO
		
		//	8:	Add carrier_module_type Table
		// TODO
		
		//	9:	Populate carrier_module_type Table
		// TODO
		
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
			throw new Exception(__CLASS__ . ' Failed to add payment_terms.direct_debit_days and direct_debit_minimum Fields. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE payment_terms DROP direct_debit_days, direct_debit_minimum;";
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
