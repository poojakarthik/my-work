<?php

/**
 * Version 45 (Forty-Five) of database update.
 * This version: -
 *	1:	Adds support for titles with more then 4 chars.
 */

class Flex_Rollout_Version_000045 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		$strSQL = "ALTER TABLE `Contact` CHANGE `Title` `Title` VARCHAR( 255 );";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to ALTER TABLE ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `Contact` CHANGE `Title` `Title` CHAR( 4 );";
		
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
