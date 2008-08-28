<?php

/**
 * Version 44 (Forty-Four) of database update.
 * This version: -
 *	1:	Adds Account.tio_reference_number field
 */

class Flex_Rollout_Version_000044 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add Account.tio_reference_number field
		$strSQL = "ALTER TABLE `Account` ADD `tio_reference_number` VARCHAR(150) NULL COMMENT 'reference number when dealing with the T.I.O.' AFTER `automatic_barring_datetime`;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add Account.tio_reference_number field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Account DROP tio_reference_number;";
		
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
