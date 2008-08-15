<?php

/**
 * Version 12 (twelve) of database update.
 * This version: -
 *	1:	Alters carrier_provisioning_support table, again
 *	2:	Populates carrier_provisioning_support table
 */

class Flex_Rollout_Version_000012 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Alter the active_status Table to make it work with new constant definition framework
		$strSQL = " ALTER TABLE active_status
						ADD name varchar(255) NOT NULL COMMENT 'The name of the status' AFTER id,
						ADD const_name varchar(255) NOT NULL COMMENT 'The constant name'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add name and const_name columns to active_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE active_status DROP name, DROP const_name";
		$strSQL = "UPDATE active_status SET name = description, const_name = UCASE(CONCAT('active_status_', REPLACE(description, ' ', '_')))";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate name and const_name columns to active_status table. ' . $qryQuery->Error());
		}

		// Alter the credit_control_status Table to make it work with new constant definition framework
		$strSQL = " ALTER TABLE credit_control_status
						ADD const_name varchar(255) NOT NULL COMMENT 'The constant name'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add const_name columns to credit_control_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE credit_control_status DROP const_name";
		$strSQL = "UPDATE credit_control_status SET const_name = UCASE(CONCAT('credit_control_status_', REPLACE(name, ' ', '_')))";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate name and const_name columns to credit_control_status table. ' . $qryQuery->Error());
		}

		// Alter the automatic_invoice_action Table to make it work with new constant definition framework
		$strSQL = " ALTER TABLE automatic_invoice_action
						ADD const_name varchar(255) NOT NULL COMMENT 'The constant name'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add const_name columns to automatic_invoice_action table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE automatic_invoice_action DROP const_name";
		
		$strSQL = "UPDATE automatic_invoice_action SET const_name = REPLACE(UCASE(CONCAT('automatic_invoice_action_', name)), ' ', '_')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate name and const_name columns to automatic_invoice_action table. ' . $qryQuery->Error());
		}

		$strSQL = "UPDATE automatic_invoice_action SET const_name = UCASE(CONCAT('automatic_invoice_action_', REPLACE(name, ' ', '_')))";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate name and const_name columns to automatic_invoice_action table. ' . $qryQuery->Error());
		}

		// Alter the automatic_barring_status Table to make it work with new constant definition framework
		$strSQL = " ALTER TABLE automatic_barring_status
						ADD const_name varchar(255) NOT NULL COMMENT 'The constant name'";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add const_name columns to automatic_barring_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE automatic_barring_status DROP const_name";
		$strSQL = "UPDATE automatic_barring_status SET const_name = UCASE(CONCAT('automatic_barring_status_', REPLACE(name, ' ', '_')))";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate name and const_name columns to automatic_barring_status table. ' . $qryQuery->Error());
		}

		// Alter the payment_terms Table ()
		$strSQL = "
			ALTER TABLE payment_terms 
				ADD late_payment_fee DECIMAL(4,2) NOT NULL DEFAULT 17.27 COMMENT 'The late payment fee charged, excluding GST' AFTER minimum_balance_to_pursue  
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to alter payment_terms table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE payment_terms DROP late_payment_fee";
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
