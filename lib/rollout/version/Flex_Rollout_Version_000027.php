<?php

/**
 * Version x of database update.
 * This version: -
 *	1:	adds employee_id field to CreditCard table
 *	2:	adds employee_id field to DirectDebit table
 */

class Flex_Rollout_Version_000027 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add employee_id field to CreditCard table
		$strSQL = "ALTER TABLE CreditCard ADD employee_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into Employee table. Id of the employee who entered the CreditCard details' AFTER created_on";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add employee_id field to the CreditCard table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE CreditCard DROP employee_id";
		
		// 1:	Add employee_id field to DirectDebit table
		$strSQL = "ALTER TABLE DirectDebit ADD employee_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into Employee table. Id of the employee who entered the DirectDebit details' AFTER created_on";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add employee_id field to the DirectDebit table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE DirectDebit DROP employee_id";
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
