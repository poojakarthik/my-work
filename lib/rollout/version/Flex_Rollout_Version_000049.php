<?php

/**
 * Version 49 (forty-nine) of database update.
 * This version: -
 *	1:	Sets the DisplayType of Mobile to 13/1300 Numbers to Call
 *	2:	Make the Mobile OC&C RecordType non-compulsory while it is not being used
 */

class Flex_Rollout_Version_000049 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		
		$arrUpdateDBs[]		= $GLOBALS['**arrDatabase']['cdr']['Database'];
		$arrUpdateDBs[]		= $GLOBALS['**arrDatabase']['flex']['Database'];
		
		foreach ($arrUpdateDBs as $strUpdateDB)
		{
			//	1:	Sets the DisplayType of Mobile to 13/1300 Numbers to Call
			$strSQL = "UPDATE {$strUpdateDB}.RecordType SET DisplayType = 1 WHERE Code = 'OneThree' AND ServiceType = 101;";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . ' Failed to Set the DisplayType of Mobile to 13/1300 Numbers to Call in '.$strUpdateDB.'. ' . $qryQuery->Error());
			}
			$this->rollbackSQL[]	= "UPDATE {$strUpdateDB}.RecordType SET DisplayType = 3 WHERE Code = 'OneThree' AND ServiceType = 101;";
			
			//	2:	Make the Mobile OC&C RecordType non-compulsory while it is not being used
			$strSQL = "UPDATE {$strUpdateDB}.RecordType SET Required = 0 WHERE Code = 'OC&C' AND ServiceType = 101;";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . ' Failed to Make the Mobile OC&C RecordType non-compulsory in '.$strUpdateDB.'. ' . $qryQuery->Error());
			}
			$this->rollbackSQL[]	= "UPDATE {$strUpdateDB}.RecordType SET Required = 1 WHERE Code = 'OC&C' AND ServiceType = 101;";
		}
		
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
