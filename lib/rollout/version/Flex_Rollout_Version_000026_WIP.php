<?php

/**
 * Version 26 (twenty-six) of database update.
 * This version: -
 *	1:	Add payment_terms.direct_debit_days and direct_debit_minimum Fields
 *	2:	Populate payment_terms.direct_debit_days and direct_debit_minimum Fields
 *	3:	Add Direct Debit record to automatic_invoice_action
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
