<?php

/**
 * Version 30 (thirty) of database update.
 * This version: -
 * 	1:	Back up flex_*_cdr.RecordType to flex_*_cdr.RecordType_bk
 *	2:	Copy flex_*.RecordType to flex_*_cdr.RecordType
 *	3:	Remove flex_*_cdr.RecordType_bk Backup Table (at commit)
 */

class Flex_Rollout_Version_000030 extends Flex_Rollout_Version
{
	private $rollbackSQL	= array();
	private	$commitSQL		= array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		$strCDRDB	= $GLOBALS['**arrDatabase']['cdr']['Database'];
		$strFlexDB	= $GLOBALS['**arrDatabase']['flex']['Database'];
		
		//	1:	Back up flex_*_cdr.RecordType to flex_*_cdr.RecordType_bk
		$strSQL = "DROP TABLE IF EXISTS {$strCDRDB}.RecordType_bk; \n" .
					"CREATE TABLE {$strCDRDB}.RecordType_bk LIKE {$strCDRDB}.RecordType; \n" .
					"INSERT INTO {$strCDRDB}.RecordType_bk (SELECT * FROM {$strCDRDB}.RecordType);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to Back up flex_*_cdr.RecordType to flex_*_cdr.RecordType_bk. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS {$strCDRDB}.RecordType_bk;";
		
		//	2:	Copy flex_*.RecordType to flex_*_cdr.RecordType
		$strSQL = "DROP TABLE IF EXISTS {$strCDRDB}.RecordType;\n" .
					"CREATE TABLE {$strCDRDB}.RecordType LIKE {$strFlexDB}.RecordType;\n" .
					"INSERT INTO {$strCDRDB}.RecordType (SELECT * FROM {$strFlexDB}.RecordType);\n";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to Copy flex_*.RecordType to flex_*_cdr.RecordType. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS {$strCDRDB}.RecordType;\n" .
								"CREATE TABLE {$strCDRDB}.RecordType LIKE {$strCDRDB}.RecordType_bk;\n" .
								"INSERT INTO {$strCDRDB}.RecordType (SELECT * FROM {$strCDRDB}.RecordType_bk);\n";
				
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
	
	function commit()
	{
		$strCDRDB	= $GLOBALS['**arrDatabase']['cdr']['Database'];
		$strFlexDB	= $GLOBALS['**arrDatabase']['flex']['Database'];
		
		//	3:	Remove flex_*_cdr.RecordType_bk Backup Table (at commit)
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		if (!$qryQuery->Execute("DROP TABLE IF EXISTS {$strCDRDB}.RecordType_bk"))
		{
			throw new Exception(__CLASS__ . ' Failed to remove flex_*_cdr.RecordType_bk Backup Table: ' . $qryQuery->Error());
		}
	}
}

?>
