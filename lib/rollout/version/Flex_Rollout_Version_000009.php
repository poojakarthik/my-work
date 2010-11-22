<?php

/**
 * Version 9 (nine) of database update.
 * This version: -
 *	1:	Adds the "Pending Activation" record to the account_status table
 *	2:	Adds the "const_name" record to the account_status table
 *	3:	Adds the "const_name" record to the provisioning_type table
 *	4:	Sets appropriate values for the const_name field, for each record in the account_status table
 */

class Flex_Rollout_Version_000009 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Add "Pending Activation" record to the account_status table
		$strSQL = "	INSERT INTO `account_status` ( `id` , `name` , `can_bar` , `send_late_notice` , `description` )
					VALUES ('5', 'Pending Activation', '0', '0', 'Pending Activation');
		";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to insert the "Pending Activation" record into the account_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM account_status WHERE id = 5";

		// Add the const_name field to the account_status table
		$strSQL = "	ALTER TABLE `account_status`
					ADD `const_name` VARCHAR( 255 ) NOT NULL COMMENT 'The constant name' AFTER `description`;
		";
		
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add the const_name field to the account_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `account_status` DROP `const_name`";

		// Add the const_name field to the provisioning_type table
		$strSQL = "	ALTER TABLE `provisioning_type`
					ADD `const_name` VARCHAR( 255 ) NOT NULL COMMENT 'The constant name' AFTER `description`;
		";
		
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add the const_name field to the provisioning_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE `provisioning_type` DROP `const_name`";

		$arrConstants = array(	"0"	=> array("const_name" => "ACCOUNT_STATUS_ACTIVE",				"description" => "Active"),
								"1"	=> array("const_name" => "ACCOUNT_STATUS_ARCHIVED",				"description" => "Archived"),
								"2"	=> array("const_name" => "ACCOUNT_STATUS_CLOSED",				"description" => "Closed"),
								"3"	=> array("const_name" => "ACCOUNT_STATUS_DEBT_COLLECTION",		"description" => "Debt Collection"),
								"4"	=> array("const_name" => "ACCOUNT_STATUS_SUSPENDED",			"description" => "Suspended"),
								"5"	=> array("const_name" => "ACCOUNT_STATUS_PENDING_ACTIVATION",	"description" => "Pending Activation")
							);

		// The rollback can be defined before the queries
		$this->rollbackSQL[] = "UPDATE account_status SET const_name = ''";
		foreach ($arrConstants as $intId=>$arrConstant)
		{
			$strUpdate = "UPDATE account_status SET const_name = '{$arrConstant['const_name']}', description = '{$arrConstant['description']}' WHERE id = $intId;";
			if (!$qryQuery->Execute($strUpdate))
			{
				throw new Exception_Database(__CLASS__ . " Failed to set the const_name ({$arrConstant['const_name']}) and the description '{$arrConstant['description']}' for the account_status record with id = $intId. " . $qryQuery->Error());
			}
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
					throw new Exception_Database(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
