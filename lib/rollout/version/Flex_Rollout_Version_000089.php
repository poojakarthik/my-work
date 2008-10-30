<?php

/**
 * Version 89 of database update.
 * This version: -
 *	1:	Adds the contract_terms.payout_charge_type_id and exit_fee_charge_type_id fields
 *	2:	Adds ChargeTypes for Contract Payout and Exit Fees
 *	3:	Populates the contract_terms.payout_charge_type_id and exit_fee_charge_type_id fields
 */

class Flex_Rollout_Version_000089 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the contract_terms.payout_charge_type_id and exit_fee_charge_type_id fields
		$strSQL = "ALTER TABLE contract_terms " .
					"ADD payout_charge_type_id BIGINT(20) NULL COMMENT '(FK) The ChargeType for the Contract Payout Fee'," .
					"ADD exit_fee_charge_type_id BIGINT(20) NULL COMMENT '(FK) The ChargeType for the Contract Exit Fee';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contract_terms.payout_charge_type_id and exit_fee_charge_type_id fields. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE contract_terms " .
								"DROP payout_charge_type_id," .
								"DROP exit_fee_charge_type_id;";
		
		// 2:	Adds ChargeTypes for Contract Payout and Exit Fees
		$arrPayoutChargeType	= Array();
		$arrPayoutChargeType['ChargeType']		= $this->getUserResponse("Please enter the Charge Code for Contract Payout Fees (eg. CPF)?");
		$arrPayoutChargeType['Description']		= $this->getUserResponse("Please enter the Charge Description for Contract Payout Fees (eg. 'Contract Payout Fee')");
		$arrPayoutChargeType['Nature']			= 'DR';
		$arrPayoutChargeType['Fixed']			= 0;
		$arrPayoutChargeType['automatic_only']	= 0;
		$arrPayoutChargeType['Amount']			= 0.00;
		$arrPayoutChargeType['Archived']		= 0;
		
		$arrExitFeeChargeType	= Array();
		$arrExitFeeChargeType['ChargeType']		= $this->getUserResponse("Please enter the Charge Code for Contract Exit Fees (eg. CEF)?");
		$arrExitFeeChargeType['Description']	= $this->getUserResponse("Please enter the Charge Description for Contract Exit Fees (eg. 'Contract Exit Fee')");
		$arrExitFeeChargeType['Nature']			= 'DR';
		$arrExitFeeChargeType['Fixed']			= 0;
		$arrExitFeeChargeType['automatic_only']	= 0;
		$arrExitFeeChargeType['Amount']			= 0.00;
		$arrExitFeeChargeType['Archived']		= 0;
		$result = $dbAdmin->query("INSERT INTO ChargeType (ChargeType, Description, Nature, Fixed, automatic_only, Amount, Archived) VALUES " .
									"('{$arrPayoutChargeType['ChargeType']}', '{$arrPayoutChargeType['Description']}', '{$arrPayoutChargeType['Nature']}', {$arrPayoutChargeType['Fixed']}, {$arrPayoutChargeType['automatic_only']}, {$arrPayoutChargeType['Amount']}, {$arrPayoutChargeType['Archived']}), " .
									"('{$arrExitFeeChargeType['ChargeType']}', '{$arrExitFeeChargeType['Description']}', '{$arrExitFeeChargeType['Nature']}', {$arrExitFeeChargeType['Fixed']}, {$arrExitFeeChargeType['automatic_only']}, {$arrExitFeeChargeType['Amount']}, {$arrExitFeeChargeType['Archived']});");
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add ChargeTypes for Contract Payout and Exit Fees fields. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM ChargeType WHERE ChargeType = '{$arrPayoutChargeType['ChargeType']}' ORDER BY Id DESC LIMIT 1";
		$this->rollbackSQL[] = "DELETE FROM ChargeType WHERE ChargeType = '{$arrExitFeeChargeType['ChargeType']}' ORDER BY Id DESC LIMIT 1";
		
		// 3:	Populates the contract_terms.payout_charge_type_id and exit_fee_charge_type_id fields
		$strSQL = "UPDATE contract_terms SET " .
					"payout_charge_type_id = (SELECT Id FROM ChargeType WHERE ChargeType = '{$arrPayoutChargeType['ChargeType']}' ORDER BY Id DESC LIMIT 1), " .
					"exit_fee_charge_type_id = (SELECT Id FROM ChargeType WHERE ChargeType = '{$arrExitFeeChargeType['ChargeType']}' ORDER BY Id DESC LIMIT 1)";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the contract_terms.payout_charge_type_id and exit_fee_charge_type_id fields. ' . $result->getMessage());
		}
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
