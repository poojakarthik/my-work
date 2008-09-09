<?php

/**
 * Version 51 of database update.
 * This version: -
 *	1:	Fixes spelling mistakes in the provisioning_type table
 *	2:	Adds the RecurringCharge.in_advance field
 */

class Flex_Rollout_Version_000051 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Fixes spelling mistakes in the provisioning_type table
		$strSQL = "UPDATE provisioning_type SET name = 'Full Service Lost (Disconnected)', description = 'Full Service Lost (Disconnected)' WHERE id = 916;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to fix spelling mistake for id 916 in the provisioning_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "UPDATE provisioning_type SET name = 'Full Service Lost (Diconnected)', description = 'Full Service Lost (Diconnected)' WHERE id = 916;";
		
		$strSQL = "UPDATE provisioning_type SET name = 'Preselection Lost (Disconnected)', description = 'Preselection Lost (Disconnected)' WHERE id = 917;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to fix spelling mistake for id 917 in the provisioning_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "UPDATE provisioning_type SET name = 'Preselection Lost (Diconnected)', description = 'Preselection Lost (Diconnected)' WHERE id = 917;";
		
		// 2:	Adds the RecurringCharge.in_advance field
		$strSQL = "ALTER TABLE RecurringCharge ADD in_advance TINYINT(1) NOT NULL COMMENT '1: Charged in Advance; 0: Charged in Arrears' AFTER Continuable;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add the RecurringCharge.in_advance field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE RecurringCharge DROP in_advance;";
		
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
