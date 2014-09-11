<?php

/**
 * Version 182 of database update.
 * This version: -
 *	
 *	1:	Add action types 'Adjustment Requested', 'Adjustment Request Outcome', 'Recurring Adjustment Requested', 'Recurring Adjustment Request Outcome'
 *
 *	Note that this rollout script shouldn't have any table structure changes in it, because it relies on the MySQL transaction being able to properly rollback
 *	the changes made.  The only possible issue is that the deprecated action types from stage 1.1 will not be reverted to their original state, but that's
 *	no big deal, as they should be deprecated regardless.
 *
 *	2: Add email_notification 'Recurring Charge Report' to the email_notification table
 *
 */

class Flex_Rollout_Version_000182 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		$strCurrentTimestamp = Data_Source_Time::currentTimestamp($dbAdmin);
		
		$cgActiveStatus				= Constant_Group::loadFromTable($dbAdmin, "active_status", false, false, true);
		$intActiveStatusActive		= $cgActiveStatus->getValue('ACTIVE_STATUS_ACTIVE');
		$intActiveStatusInactive	= $cgActiveStatus->getValue('ACTIVE_STATUS_INACTIVE');
		
		
		$cgActionTypeDetailRequirement			= Constant_Group::loadFromTable($dbAdmin, "action_type_detail_requirement", false, false, true);
		$intActionTypeDetailRequirementRequired	= $cgActionTypeDetailRequirement->getValue('ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED');
		
		//	1:		Add action types 'Adjustment Requested', 'Adjustment Request Outcome', 'Recurring Adjustment Requested', 'Recurring Adjustment Request Outcome'
		//	1.1:	Deprecate any existing user-created action types that are like these new system only action types
		$strSQL = "	UPDATE action_type
					SET name = CONCAT(SUBSTRING(name, 1, 150), ' - DEPRECATED on $strCurrentTimestamp for possible conflict with system action type'),
					active_status_id = $intActiveStatusInactive
					WHERE name LIKE '%Adjustment Requested%' OR name LIKE '%Adjustment Request Outcome%';";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 1.1 - Deprecate any existing user-created action types that are like these new system only action types. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// Let the MySQL transaction roll this step back.  It's no biggie if it doesn't


		//	1.2:	Insert action_type records for 'Adjustment Requested', 'Adjustment Request Outcome', 'Recurring Adjustment Requested', 'Recurring Adjustment Request Outcome' action types
		$strSQL = "	INSERT INTO action_type (name, description, action_type_detail_requirement_id, is_automatic_only, is_system, active_status_id)
					VALUES
					('Adjustment Requested', 'Adjustment Requested', $intActionTypeDetailRequirementRequired, 1, 1, $intActiveStatusActive),
					('Adjustment Request Outcome', 'Adjustment Request Outcome', $intActionTypeDetailRequirementRequired, 1, 1, $intActiveStatusActive),
					('Recurring Adjustment Requested', 'Recurring Adjustment Requested', $intActionTypeDetailRequirementRequired, 1, 1, $intActiveStatusActive),
					('Recurring Adjustment Request Outcome', 'Recurring Adjustment Request Outcome', $intActionTypeDetailRequirementRequired, 1, 1, $intActiveStatusActive);";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 1.2 - Insert action_type records for 'Adjustment Requested', 'Adjustment Request Outcome', 'Recurring Adjustment Requested', 'Recurring Adjustment Request Outcome' action types. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// Let the MySQL transaction roll this step back

		//	1.3:	Define the action_type_action_association_type constraints for the ActionTypes (they can be associated with accounts and services)
		$strSQL = "	INSERT INTO action_type_action_association_type (action_type_id, action_association_type_id)
					SELECT action_type.id, action_association_type.id
					FROM action_type, action_association_type
					WHERE action_type.name IN ('Adjustment Requested', 'Adjustment Request Outcome', 'Recurring Adjustment Requested', 'Recurring Adjustment Request Outcome')
					AND action_association_type.const_name IN ('ACTION_ASSOCIATION_TYPE_ACCOUNT', 'ACTION_ASSOCIATION_TYPE_SERVICE');";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 1.3 - Define the action_type_action_association_type constraints for the ActionTypes (they can be associated with accounts and services). " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback is required


		//	2:	Add email_notification 'Recurring Charge Report' to the email_notification table
		$strSQL = "	INSERT INTO email_notification (name, description, const_name, allow_customer_group_emails)
					VALUES ('Recurring Charge Report', 'Report generated by the Recurring Charge batch process', 'EMAIL_NOTIFICATION_RECURRING_CHARGE_REPORT', 0);";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed stage 2: - Add email_notification 'Recurring Charge Report' to the email_notification table. " . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback is required

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