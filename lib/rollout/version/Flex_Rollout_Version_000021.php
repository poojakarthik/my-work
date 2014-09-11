<?php

/**
 * Version 21 (twenty-one) of database update.
 * This version: -
 *	1:	Adds the created_on property to the CreditCard table (of type TIMESTAMP)
 *	2:	Adds the created_on property to the DirectDebit table (of type TIMESTAMP)
 */

class Flex_Rollout_Version_000021 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1. Add the "created_on" field to the CreditCard table
		$strSQL = "	ALTER TABLE `CreditCard` 
					ADD `created_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp at which the credit card details were entered into flex' AFTER `Archived`;
		";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add the "created_on" field to the CreditCard table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `CreditCard` DROP `created_on`";


		// 2. Add the "created_on" field to the DirectDebit table
		$strSQL = "	ALTER TABLE `DirectDebit` 
					ADD `created_on` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'The timestamp at which the direct debit account details were entered into flex' AFTER `Archived`;
		";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add the "created_on" field to the DirectDebit table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `DirectDebit` DROP `created_on`";

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
					throw new Exception_Database(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
