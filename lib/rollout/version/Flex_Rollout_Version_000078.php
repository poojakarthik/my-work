<?php

/**
 * Version 78 of database update.
 * This version: -
 *	1:	Creates and populates the automatic_invoice_action_config table
 *	2:	Re-structures the automatic_invoice_action table
 *  3:  Adds a customer_group_id column to the payment_terms table
 *  4:  Populates the payment terms table for each customer group
 */

class Flex_Rollout_Version_000078 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1a:	Create the account_history table
		$strSQL = "	CREATE TABLE automatic_invoice_action_config
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
					    automatic_invoice_action_id bigint(20) default NULL COMMENT 'FK to automatic_invoice_action table',
					    customer_group_id bigint(20) default NULL COMMENT 'FK to CustomerGroup table',
					    days_from_invoice smallint(5) default '0',
					    can_schedule tinyint(3) default '0' COMMENT 'Whether or not this action can be scheduled',
					    response_days smallint(5) default '7' COMMENT 'Number of days from event that an external response must be made in',
					    PRIMARY KEY  (id)
					) ENGINE = innodb COMMENT = 'Automatic invoice action configuration settings';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create automatic_invoice_action_config table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS automatic_invoice_action_config;";

		// 1b:	Populate it with the current configuration settings for each customer group
		$strSQL = "SELECT automatic_invoice_action.id, CustomerGroup.Id, days_from_invoice, can_schedule, response_days FROM automatic_invoice_action, CustomerGroup ORDER BY CustomerGroup.Id";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to read from automatic_invoice_action table. ' . $result->getMessage());
		}

		$values = $result->fetchAll();
		$first = true;
		$strSqlTemplate = "INSERT INTO automatic_invoice_action_config (automatic_invoice_action_id, customer_group_id, days_from_invoice, can_schedule, response_days) VALUES (xxx)";
		$strSqlRollBackTemplate = "UPDATE automatic_invoice_action SET days_from_invoice=ddd, can_schedule=sss, response_days=rrr WHERE id=iii";
		$rollbacks = array();
		foreach($values as $record)
		{
			if ($first === true)
			{
				$first = $record[1];
			}

			$xxx = implode(',', $record);
			$strSQL = str_replace('xxx', $xxx, $strSqlTemplate);
			$result = $dbAdmin->query($strSQL);
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . ' Failed to insert into automatic_invoice_action table (' . $xxx . '). ' . $result->getMessage());
			}

			if ($first === $record[1])
			{
				$record[1] = 'NULL';
				$xxx = implode(',', $record);
				$strSQL = str_replace('xxx', $xxx, $strSqlTemplate);
				$result = $dbAdmin->query($strSQL);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to insert into automatic_invoice_action table (' . $xxx . '). ' . $result->getMessage());
				}
				
				$rollbacks[] = str_replace(array('ddd', 'sss', 'rrr', 'iii'), array($record[2], $record[3], $record[4], $record[0]), $strSqlRollBackTemplate);
			}
		}
		
		$strSQL = " ALTER TABLE automatic_invoice_action DROP days_from_invoice, DROP can_schedule, DROP response_days";
		
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter automatic_invoice_action table. ' . $result->getMessage());
		}
		foreach ($rollbacks as $rollback)
		{
			$this->rollbackSQL[] = $rollback;
		}
		$this->rollbackSQL[] = "
				ALTER TABLE automatic_invoice_action 
				ADD days_from_invoice smallint(5) default '0', 
				ADD can_schedule tinyint(3) default '0' COMMENT 'Whether or not this action can be scheduled',
				ADD response_days smallint(5) default '7' COMMENT 'Number of days from event that an external response must be made in' 
			";


		
		$strSQL = "	ALTER TABLE payment_terms
					ADD customer_group_id bigint(20) default NULL COMMENT 'FK to CustomerGroup table'
			";

		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter payment_terms table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE payment_terms DROP customer_group_id";

		// 1b:	Populate it with the current configuration settings for each customer group
		$strSQL = "SELECT CustomerGroup.Id, invoice_day, payment_terms, minimum_balance_to_pursue, late_payment_fee, employee, created, direct_debit_days, direct_debit_minimum FROM payment_terms, CustomerGroup  WHERE payment_terms.id = (SELECT MAX(id) FROM payment_terms) ORDER BY CustomerGroup.Id";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to read from automatic_invoice_action table. ' . $result->getMessage());
		}

		$values = $result->fetchAll();
		$strSqlTemplate = "INSERT INTO payment_terms (customer_group_id, invoice_day, payment_terms, minimum_balance_to_pursue, late_payment_fee, employee, created, direct_debit_days, direct_debit_minimum) VALUES (xxx)";
		foreach($values as $record)
		{
			$xxx = "'" . implode("','", $record) . "'";
			$strSQL = str_replace('xxx', $xxx, $strSqlTemplate);
			$result = $dbAdmin->query($strSQL);
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . ' Failed to insert into payment_terms table (' . $xxx . '). ' . $result->getMessage());
			}
		}
		$this->rollbackSQL[] = "DELETE FROM payment_terms WHERE customer_group_id IS NOT NULL";

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
