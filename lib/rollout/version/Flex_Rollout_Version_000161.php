<?php

/**
 * Version 161 of database update.
 * This version: -
 *
 *	1:	Add previous_balance AND total_balance columns to the InvoiceRun table
 *	2:	For all InvoiceRuns that have BalanceData, populate the previous_balance and total_balance columns
 *	3:	Remove the BalanceData column from the InvoiceRun table
 */

class Flex_Rollout_Version_000161 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1: Add previous_balance AND total_balance columns to the InvoiceRun table
		$strSQL = 	"ALTER TABLE InvoiceRun ".
					"ADD previous_balance	DECIMAL(13, 4) NULL DEFAULT NULL COMMENT 'Total oustanding balance of the customer group''s previous invoice run, at the time of this invoice run' AFTER invoice_run_status_id, ".
					"ADD total_balance		DECIMAL(13, 4) NULL DEFAULT NULL COMMENT 'Total outstanding balance of all of the customer group''s previous invoice runs, at the time of this invoice run' AFTER previous_balance;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add previous_balance AND total_balance columns to the InvoiceRun table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE InvoiceRun ".
								"DROP previous_balance, ".
								"DROP total_balance;";
		
		
		// 2: For all InvoiceRuns that have BalanceData, populate the previous_balance and total_balance columns
		// Retrieve all the balance data, so that it can be used for the rollback for when we drop the BalanceData field
		$strSQL = "SELECT Id, BalanceData FROM InvoiceRun;";
		$arrInvoiceRunRecords = $dbAdmin->queryAll($strSQL, array('integer', 'text'), MDB2_FETCHMODE_ASSOC);
		if (PEAR::isError($arrInvoiceRunRecords))
		{
			throw new Exception(__CLASS__ . ' Failed to retreive BalanceData info from the InvoiceRun table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		foreach ($arrInvoiceRunRecords as $arrInvoiceRunRecord)
		{
			$arrBalanceData = unserialize($arrInvoiceRunRecord['BalanceData']);

			if (is_array($arrBalanceData))
			{
				// This InvoiceRun has Balance Data (I don't have to number format these values because they are stored in the serialised array as strings)
				$strPreviousBalance = (array_key_exists('PreviousBalance', $arrBalanceData) && is_numeric($arrBalanceData['PreviousBalance']))? $arrBalanceData['PreviousBalance'] : "NULL";
				$strTotalBalance = (array_key_exists('TotalBalance', $arrBalanceData) && is_numeric($arrBalanceData['TotalBalance']))? $arrBalanceData['TotalBalance'] : "NULL";
				
				
				$strUpdateSQL = "UPDATE InvoiceRun ".
								"SET previous_balance = $strPreviousBalance, ".
								"total_balance = $strTotalBalance ".
								"WHERE Id = {$arrInvoiceRunRecord['Id']};";

				$result = $dbAdmin->query($strUpdateSQL);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . " Failed to set previous_balance and total_balance for InvoiceRun {$arrInvoiceRunRecord['Id']}. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ") Query - $strUpdateSQL");
				}
			}
		}

		// 3: Remove the BalanceData column from the InvoiceRun table
		$strSQL = "ALTER TABLE InvoiceRun DROP BalanceData;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the BalanceData column from the InvoiceRun table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		// Build the Rollback script to repopulate the InvoiceRun.BalanceData field
		// $this->rollbackSQL is a Last-In-First-Out stack, so we have to define the row updates before we add the actual column that is being updated
		foreach ($arrInvoiceRunRecords as $arrInvoiceRunRecord)
		{
			$strBalanceData = ($arrInvoiceRunRecord['BalanceData'] !== NULL)? $dbAdmin->quote($arrInvoiceRunRecord['BalanceData']): "NULL";
			$this->rollbackSQL[] = "UPDATE InvoiceRun SET BalanceData = $strBalanceData WHERE Id = {$arrInvoiceRunRecord['Id']};";
		}
		
		// Add the command to add the BalanceData column (The rollbackSQL array is used as a LIFO stack)
		$this->rollbackSQL[] = "ALTER TABLE InvoiceRun ADD BalanceData VARCHAR(32767) DEFAULT NULL AFTER BillTax;";
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