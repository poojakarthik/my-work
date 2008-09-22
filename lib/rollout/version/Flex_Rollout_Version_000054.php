<?php

/**
 * Version 54 (Fifty-Four) of database update.
 * This version: -
 *	1:	Removes contact table and changes default value of email field to null. 
 *	2:  Removes known duplicate emails.
 */

class Flex_Rollout_Version_000053 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		$strSQL = "ALTER TABLE `Contact` DROP `UserName`;
ALTER TABLE `Contact` CHANGE `Email` `Email` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;
UPDATE `Contact` SET Email='' WHERE Email='noemail@telcoblue.com.au';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add scalable, minimum_services and maximum_services fields to RatePlan table. ' . $qryQuery->Error());
		}
		
		$this->rollbackSQL[] = "ALTER TABLE `Contact` ADD `UserName` VARCHAR( 31 ) NOT NULL;
ALTER TABLE `Contact` CHANGE `Email` `Email` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;";
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
