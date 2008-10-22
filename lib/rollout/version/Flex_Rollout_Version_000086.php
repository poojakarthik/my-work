<?php

/**
 * Version 86 of database update.
 * This version: -
 *	1:	Add the contract_terms Table
 *	2:	Populate the contract_terms Table
 *	3:	Add the ServiceRatePlan.contract_payout_percentage, contract_payout_charge_id, exit_fee_charge_id, contract_breach_fees_charged_on, contract_breach_fees_employee_id fields
 */

class Flex_Rollout_Version_000086 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the contract_terms Table
		$strSQL = "CREATE TABLE contract_terms " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for this version of the Contract Terms', " .
						"created_by BIGINT(20) NOT NULL COMMENT '(FK) Employee who created this version', " .
						"created_on DATETIME NOT NULL COMMENT 'Date this version was created', " .
						"contract_payout_minimum_invoices INT(10) NOT NULL COMMENT 'Minimum number of invoices for the contract before Contract Payouts are charged'," .
						"exit_fee_minimum_invoices INT(10) NOT NULL COMMENT 'Minimum number of invoices for the contract before Exit Fees are charged'" .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contract_terms Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS contract_terms;";
		
		// 2:	Populate the contract_terms Table
		$intContractPayoutOffset 			= $this->getUserResponseInteger("How many Invoices must a Contracted Plan appear on before it will be charged a Contract Payout?");
		$intExitFeeOffset 					= $this->getUserResponseInteger("How many Invoices must a Contracted Plan appear on before it will be charged a Exit Fee?");
		
		$fltDefaultContractPayoutPercentage	= ($fltDefaultContractPayoutPercentage) ? $fltDefaultContractPayoutPercentage : NULL;
		$strSQL = "
			INSERT INTO contract_terms (created_by, created_on, contract_payout_minimum_invoices, exit_fee_minimum_invoices)
				VALUES (0, NOW(), $intContractPayoutOffset, $intExitFeeOffset)";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the contract_terms Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE contract_terms;";
		
		// 3:	Add the ServiceRatePlan.contract_payout_percentage, contract_payout_charge_id, exit_fee_charge_id, contract_breach_fees_charged_on, contract_breach_fees_employee_id fields
		$strSQL = "ALTER TABLE ServiceRatePlan " .
					"ADD contract_payout_percentage DECIMAL(13,4) NULL COMMENT 'Actual Contract Payout Percentage' AFTER contract_breach_reason_description," .
					"ADD contract_payout_charge_id BIGINT(20) NULL COMMENT '(FK) Charge which corresponds to the Contract Payout' AFTER contract_payout_percentage," .
					"ADD exit_fee_charge_id BIGINT(20) NULL COMMENT '(FK) Charge which corresponds to the Exit Fee' AFTER contract_payout_charge_id," .
					"ADD contract_breach_fees_charged_on DATETIME NULL COMMENT 'Date and time the Contract Breach Fees were applied' AFTER exit_fee_charge_id," .
					"ADD contract_breach_fees_employee_id BIGINT(20) NULL COMMENT '(FK) Employee who charges the Contract Breach Fees' AFTER contract_breach_fees_charged_on;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to Add the ServiceRatePlan.contract_payout_percentage, contract_payout_charge_id, exit_fee_charge_id, contract_breach_fees_charged_on, contract_breach_fees_employee_id fields. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE ServiceRatePlan " .
								"DROP contract_payout_percentage," .
								"DROP contract_payout_charge_id," .
								"DROP exit_fee_charge_id," .
								"DROP contract_breach_fees_charged_on," .
								"DROP contract_breach_fees_employee_id;";
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
