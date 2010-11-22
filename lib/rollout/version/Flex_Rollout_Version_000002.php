<?php

/**
 * Version 2 (two) of database update.
 * This version: -
 *	1:	Modifies the Service table to make CreatedOn and ClosedOn DateTimes.  They are currently Dates.
 *	2:	Updates the ClosedOn value of all Service records where ClosedOn is not null, and the time component == "00:00:00"
 *		It sets the time component to "23:59:59"
 *	3:	Adds the following properties to the Service table:
 * 		NatureOfCreation, NatureOfClosure, LastOwner, NextOwner
 *	4:	Adds the default_rate_plan table
 */

class Flex_Rollout_Version_000002 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Make changes to the Service Table
		$strSQL = "
			ALTER TABLE `Service`
			CHANGE `CreatedOn` `CreatedOn` DATETIME NOT NULL,
			CHANGE `ClosedOn` `ClosedOn` DATETIME NULL DEFAULT NULL,
			ADD `NatureOfCreation` INT( 10 ) UNSIGNED NULL DEFAULT NULL COMMENT 'Identifies the reason why this Service Record was created' AFTER `CreatedBy`,
			ADD `NatureOfClosure` INT( 10 ) UNSIGNED NULL DEFAULT NULL COMMENT 'Identifies the reason why this Service Record was closed' AFTER `ClosedBy`,
			ADD `LastOwner` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL COMMENT 'Identifies the Account which last owned this FNN' AFTER `ForceInvoiceRender` ,
			ADD `NextOwner` BIGINT( 20 ) UNSIGNED NULL DEFAULT NULL COMMENT 'Identifies the Account which next owned this FNN' AFTER `LastOwner` ;
		";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to alter Service table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Service DROP NatureOfCreation, DROP NatureOfClosure, DROP LastOwner, DROP NextOwner";

		// Update the ClosedOn values in the Service Table 
		$strSQL = "
			UPDATE Service
			SET ClosedOn = ADDTIME(ClosedOn, '23:59:59')
			WHERE ClosedOn IS NOT NULL AND TIME(ClosedOn) = '00:00:00'
		";
		
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . " Failed to update the ClosedOn value of the Service table, for services with time component of ClosedOn == \"00:00:00\". " . $qryQuery->Error());
		}

		// Add the default_rate_plan table
		$strSQL = "
			CREATE TABLE `default_rate_plan` (
			`id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`customer_group` BIGINT( 20 ) UNSIGNED NOT NULL ,
			`service_type` INT( 10 ) UNSIGNED NOT NULL ,
			`rate_plan` BIGINT( 20 ) UNSIGNED NOT NULL
			) ENGINE = innodb;
		";
		
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create the default_rate_plan table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE default_rate_plan";
		
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
