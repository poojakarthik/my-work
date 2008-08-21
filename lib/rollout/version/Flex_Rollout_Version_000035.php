<?php

/**
 * Version 35 of database update.
 * This version: -
 *	1:	Introduces Change of Lessee, Partner Support, DNCR and Sales Complaints categories to ticketing system
 */

class Flex_Rollout_Version_000035 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);

		// The live db will have had this value hacked in already. Remove it first so that the rollout does not fail!
		$strSQL = "DELETE FROM ticketing_category WHERE id IN (14,15,16,17)";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to remove existing entry from ticketing_category table. ' . $qryQuery->Error());
		}

		// 1:	Increase size of fields used for storing paths in the ticketing_config table
		$strSQL = "INSERT INTO ticketing_category (id, name, description, const_name) VALUES
					(14, 'Change of Lessee', 'Change of lessee', 'TICKETING_CATEGORY_CHANGE_OF_LESSEE'),
					(15, 'Partner Support', 'Partner support', 'TICKETING_CATEGORY_PARTNER_SUPPORT'),
					(16, 'DNCR', 'DNCR', 'TICKETING_CATEGORY_DNCR'),
					(17, 'Sales Complaints', 'Sales complaints', 'TICKETING_CATEGORY_SALES_COMPLAINTS')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to insert into ticketing_category table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM ticketing_category WHERE id IN (14,15,16,17)";
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
