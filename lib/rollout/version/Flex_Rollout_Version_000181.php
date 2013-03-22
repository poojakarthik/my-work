<?php

/**
 * Version 181 of database update.
 * This version: -
 *	
 *	1:	Remove the RecurringCharge.Archived column
 *
 *	This is in its own rollout script to guarantee that RecurringCharge.recurring_charge_status_id has been successfully set
 *	because the rollback relies on that field to appropriately set RecurringCharge.Archived 
 */

class Flex_Rollout_Version_000181 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Remove the RecurringCharge.Archived column
		$strSQL = "ALTER TABLE RecurringCharge DROP COLUMN Archived;";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 1 - Remove the RecurringCharge.Archived column. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		
		// Rollback the value for the Archived column after it has been recreated
		// Note that when there are serveral steps in a rollback, they have to be defined in reverse order to how they should actually be run
		/*	Seeing as though the field defaults to 0, we only need to set it for when it should be 1 (Archived)
			It should only be set to Archived if the recurring adjustment has been CANCELLED OR (it is flagged as COMPLETED AND is continuable)
		*/
		$cgRecurringChargeStatus			= Constant_Group::loadFromTable($dbAdmin, "recurring_charge_status", false, false, true);
		$intRecurringChargeStatusCancelled	= $cgRecurringChargeStatus->getValue('RECURRING_CHARGE_STATUS_CANCELLED');
		$intRecurringChargeStatusCompleted	= $cgRecurringChargeStatus->getValue('RECURRING_CHARGE_STATUS_COMPLETED');

		$this->rollbackSQL[] =	"	UPDATE RecurringCharge
									SET Archived = 1
									WHERE recurring_charge_status_id = $intRecurringChargeStatusCancelled
									OR (recurring_charge_status_id = $intRecurringChargeStatusCompleted AND Continuable = 1);";
		
		// This adds the Archived column back
		$this->rollbackSQL[] =	"ALTER TABLE RecurringCharge ADD COLUMN Archived TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 = Is Archived; 0 = IS Not Archived' AFTER TotalRecursions;";

	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>