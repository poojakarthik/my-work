<?php

/**
 * Version 33 (thirty-three) of database update.
 * This version: -
 *	1:	Adds Mobile to 13/1300 RecordType 
 */

class Flex_Rollout_Version_000033 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		$arrUpdateDBs[]		= $GLOBALS['**arrDatabase']['cdr']['Database'];
		$arrUpdateDBs[]		= $GLOBALS['**arrDatabase']['flex']['Database'];
		
		// Adds Mobile to 13/1300 RecordType
		foreach ($arrUpdateDBs as $strUpdateDB)
		{
			$strSQL = " INSERT INTO {$strUpdateDB}.RecordType
							(Code, Name, Description, ServiceType, Context, Required, Itemised, GroupId, DisplayType) " .
							"VALUES ('OneThree', 'Mobile to 1300', 'Mobile to 13/1300', 101, 0, 1, 1, 0, 3)";
			if (!$qryQuery->Execute($strSQL))
			{
				throw new Exception(__CLASS__ . ' Failed to add Mobile to 13/1300 in '.$strUpdateDB.'. ' . $qryQuery->Error());
			}
			$strSQL 		= " UPDATE {$strUpdateDB}.RecordType
								SET GroupId = Id WHERE Code = 'OneThree' AND ServiceType = 101";
			if (!($intInsertId = $qryQuery->Execute($strSQL)))
			{
				throw new Exception(__CLASS__ . ' Failed to Set Mobile to 13/1300\'s GroupId in '.$strUpdateDB.'. ' . $qryQuery->Error());
			}
			$this->rollbackSQL[] = "DELETE FROM {$strUpdateDB}.RecordType WHERE Code = 'OneThree' AND ServiceType = 101;";
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
