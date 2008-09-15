<?php

/**
 * Version 53 (Fifty-Three) of database update.
 * This version: -
 *	1:	Adds scalable, minimum_services and maximum_services fields to RatePlan table
 */

class Flex_Rollout_Version_000053 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		//	1:	Add scalable, minimum_services and maximum_services fields to RatePlan table
		$strSQL = "	ALTER TABLE RatePlan 
					ADD scalable TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Boolean flag to enable/disable Scalable Shared Plans',
					ADD minimum_services INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'The minimum number of Services that should be on this Plan (only for Reporting purposes atm)',
					ADD maximum_services INT(11) UNSIGNED NULL DEFAULT NULL COMMENT 'The maximum number of Services before Scaling is introduced';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add scalable, minimum_services and maximum_services fields to RatePlan table. ' . $qryQuery->Error());
		}
		
		$this->rollbackSQL[] = "ALTER TABLE `RatePlan`
								DROP `scalable`,
								DROP `minimum_services`,
								DROP `maximum_services`;";
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
