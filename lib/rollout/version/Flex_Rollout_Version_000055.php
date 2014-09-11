<?php

/**
 * Version 55 of database update.
 * This version: -
 *	1:	Introduces Payment Advice category to ticketing system
 */

class Flex_Rollout_Version_000055 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		// The live db will have had this value hacked in already. Remove it first so that the rollout does not fail!
		$strSQL = "DELETE FROM ticketing_category WHERE id IN (19)";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to remove existing entry from ticketing_category table. ' . $qryQuery->Error());
		}

		// 1:	Increase size of fields used for storing paths in the ticketing_config table
		$strSQL = "INSERT INTO ticketing_category (id, name, description, const_name) VALUES
					(19, 'Payment Advice', 'Payment advice', 'TICKETING_CATEGORY_PAYMENT_ADVICE')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to insert into ticketing_category table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM ticketing_category WHERE id IN (19)";
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
