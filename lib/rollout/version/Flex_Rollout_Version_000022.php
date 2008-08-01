<?php

/**
 * Version 22 (twenty-two) of database update.
 * This version: -
 *	1:	Make service_line_status_update.current_line_status NULLable
 */

class Flex_Rollout_Version_000022 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Make service_line_status_update.current_line_status NULLable
		$strSQL = "ALTER TABLE service_line_status_update MODIFY current_line_status BIGINT(20) NULL ;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to make service_line_status_update.current_line_status NULLable. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE service_line_status_update MODIFY current_line_status BIGINT(20) NOT NULL ;";
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
