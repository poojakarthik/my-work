<?php

/**
 * Version 48 (forty-eight) of database update.
 * This version: -
 *	1:	Add FileImport.archived_on and archive_location Fields
 */

class Flex_Rollout_Version_000048 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add FileImport.archived_on and archive_location Fields
		$strSQL = "ALTER TABLE FileImport " .
					" ADD archived_on DATETIME NULL COMMENT 'The Date that this file was automatically archived' AFTER NormalisedOn, " .
					" ADD archive_location VARCHAR(1024) NULL COMMENT 'The Archive in which this file has been stored' AFTER Location;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add FileImport.archived_on and archive_location Fields. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE FileImport DROP archived_on, DROP archive_location;";
		
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
