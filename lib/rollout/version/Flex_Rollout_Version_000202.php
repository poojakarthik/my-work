<?php

/**
 * Version 202 of database update.
 * This version: -
 *	
 *	1:	Adds the dealer.sync_sale_constraints field  
 *
 */

class Flex_Rollout_Version_000202 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the dealer.sync_sale_constraints field
		$strSQL = "	ALTER TABLE	dealer
					ADD sync_sale_constraints TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'If TRUE AND up_line_id IS NOT NULL, then sale constraints should be kept in sync with those of up_line_id';";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the dealer.sync_sale_constraints Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "ALTER TABLE dealer DROP COLUMN sync_sale_constraints;";
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>