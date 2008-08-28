<?php

/**
 * Version 43 (forty-three) of database update.
 * This version: -
 *	1:	Add billing_charge_module.customer_group_id Field
 */

class Flex_Rollout_Version_000043 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add payment_terms.direct_debit_days and direct_debit_minimum Fields
		$strSQL = "ALTER TABLE billing_charge_module ADD customer_group_id BIGINT(20) NULL COMMENT 'Customer Group that this Module applies to (NULL = ALL)';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add billing_charge_module.customer_group_id Field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE billing_charge_module DROP customer_group_id;";
		
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
