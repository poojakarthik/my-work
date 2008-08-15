<?php

/**
 * Version 28 of database update.
 * This version: -
 *	1:	Introduces Sales and Winbacks categories to ticketing system
 */

class Flex_Rollout_Version_000028 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Increase size of fields used for storing paths in the ticketing_config table
		$strSQL = "INSERT INTO ticketing_category (id, name, description, const_name) VALUES
						(11, 'Sales', 'Sales', 'TICKETING_CATEGORY_SALES'),
						(12, 'Winbacks', 'Winbacks', 'TICKETING_CATEGORY_WINBACKS')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to insert into ticketing_category table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM ticketing_category WHERE id in (11, 12)";
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
