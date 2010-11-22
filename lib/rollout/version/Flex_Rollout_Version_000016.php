<?php

/**
 * Version 16 (sixteen) of database update.
 * This version: -
 *	1:	Increases the size of CarrierModuleConfig.Value to 4096
 */

class Flex_Rollout_Version_000016 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// Increases the size of CarrierModuleConfig.Value to 4096
		$strSQL = "ALTER TABLE CarrierModuleConfig MODIFY Value VARCHAR(4096) NOT NULL";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to change CarrierModuleConfig.Value to allow 4096 characters long. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE CarrierModuleConfig MODIFY Value VARCHAR(1024) NOT NULL";
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
