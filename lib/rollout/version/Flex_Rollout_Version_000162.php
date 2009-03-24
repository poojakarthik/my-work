<?php

/**
 * Version 162 of database update.
 * This version: -
 *
 *	1:	Create action_association_type table
 *	2:	Populate the action_association_type table
 *	3:	Create action_type_action_association_type table
 *	4:	Add performed_by_employee_id column to the action table
 *	5:	Add system_only column to the action_type table and remove the const_name column and add a uniqueness constraint on action_type.name
 *	6:	Populate the action_type table
 *	7:	Populate the action_type_action_association_type table
 */

class Flex_Rollout_Version_000162 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1: Create action_association_type table
		$strSQL = "	CREATE TABLE action_association_type
					(
						id			SMALLINT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(255)				NOT NULL					COMMENT 'Name of the Action Association Type',
						description	VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Action Association Type',
						const_name	VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias of the Action Association Type',
						
						CONSTRAINT	pk_action_association_type	PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the action_association_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS action_association_type;";
		
		// 2: Populate the action_association_type table
		$strSQL = "	INSERT INTO action_association_type (name, description, const_name)
					VALUES
					('Account', 'Account', 'ACTION_ASSOCIATION_TYPE_ACCOUNT'),
					('Service', 'Service', 'ACTION_ASSOCIATION_TYPE_SERVICE'),
					('Contact', 'Contact', 'ACTION_ASSOCIATION_TYPE_CONTACT');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the action_association_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// We don't need to roll this back, as the table will be deleted
		
		// 3: Create action_type_action_association_type table
		$strSQL = "	CREATE TABLE action_type_action_association_type
					(
						id									BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						action_type_id						SMALLINT		UNSIGNED	NOT NULL					COMMENT 'FK into action_type table',
						action_association_type_id			SMALLINT		UNSIGNED	NOT NULL					COMMENT 'FK into action_association_type table',
						
						CONSTRAINT	pk_action_type_action_association_type			PRIMARY KEY	(id),
						CONSTRAINT	un_action_type_id_action_association_type_id	UNIQUE KEY	(action_type_id, action_association_type_id),
					
						CONSTRAINT	fk_action_type_action_association_type_action_type_id		FOREIGN KEY (action_type_id)				REFERENCES action_type(id)				ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT	fk_action_type_action_association_type_association_type_id	FOREIGN KEY (action_association_type_id)	REFERENCES action_association_type(id)	ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the action_type_action_association_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS action_type_action_association_type;";
		
		// 4: Add performed_by_employee_id column to the action table
		$strSQL = "	ALTER TABLE action
					ADD	COLUMN		performed_by_employee_id						BIGINT UNSIGNED NOT NULL COMMENT '(FK) Employee who performed the Action' AFTER details,
					CHANGE			created_by_employee_id created_by_employee_id	BIGINT UNSIGNED NOT NULL COMMENT '(FK) Employee who logged the Action',
					ADD CONSTRAINT	fk_action_performed_by_employee_id				FOREIGN KEY (performed_by_employee_id) REFERENCES Employee(Id) ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add performed_by_employee_id column to action Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE action
									DROP FOREIGN KEY fk_action_performed_by_employee_id,
									DROP COLUMN performed_by_employee_id;";

		// 5: Add system_only column to the action_type table and remove the const_name column and add a uniqueness constraint on action_type.name
		$strSQL = "	ALTER TABLE action_type
					ADD COLUMN		system_only TINYINT UNSIGNED NOT NULL COMMENT '0 = anyone can log this action, 1 = only the system can log this action' AFTER action_type_detail_requirement_id,
					DROP COLUMN		const_name,
					ADD CONSTRAINT	un_action_type_name UNIQUE KEY (name);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to modify the structure of the action_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE action_type
									DROP KEY un_action_type_name,
									DROP COLUMN system_only,
									ADD COLUMN const_name VARCHAR(512) NULL COMMENT 'Constant Alias of the Action Type' AFTER description;";

		// 6: Populate the action_type table
		// First retrieve the action_type_detail_requirement constants
		$cgActionTypeDetailRequirement = Constant_Group::loadFromTable($dbAdmin, "action_type_detail_requirement", false, false, true);
		$intDetailNoneId		= $cgActionTypeDetailRequirement->getValue('ACTION_TYPE_DETAIL_REQUIREMENT_NONE');
		$intDetailOptionalId	= $cgActionTypeDetailRequirement->getValue('ACTION_TYPE_DETAIL_REQUIREMENT_OPTIONAL');
		$intDetailRequiredId	= $cgActionTypeDetailRequirement->getValue('ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED');

		$strSQL = "	INSERT INTO action_type (name, description, action_type_detail_requirement_id, system_only)
					VALUES
					('Manual Bar', 'Manual Bar', $intDetailOptionalId, 0),
					('Manual Unbar', 'Manual Unbar', $intDetailOptionalId, 0),
					('Manual TDC', 'Manual TDC', $intDetailOptionalId, 0),
					('Manual UnTDC', 'Manual UnTDC', $intDetailOptionalId, 0),
					('Left Message to Call', 'Left Message to Call', $intDetailOptionalId, 0),
					('Sent to Debt Collection', 'Sent to Debt Collection', $intDetailRequiredId, 0),
					('Payment Made', 'Payment Made', $intDetailOptionalId, 1),
					('Payment Advice', 'Payment Advice', $intDetailRequiredId, 0),
					('Logged Fault', 'Logged Fault', $intDetailRequiredId, 0),
					('Closed Fault', 'Closed Fault', $intDetailOptionalId, 0),
					('Checked Fault', 'Checked Fault', $intDetailOptionalId, 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the action_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "ALTER TABLE action_type AUTO_INCREMENT = 1;";
		$this->rollbackSQL[] = "DELETE FROM action_type WHERE TRUE;";
		
		// 7: Populate the action_type_action_association_type table
		// First retrieve the action_association_type constants
		$cgActionAssocationType	= Constant_Group::loadFromTable($dbAdmin, "action_association_type", false, false, true);
		$intAATAccountId		= $cgActionAssocationType->getValue('ACTION_ASSOCIATION_TYPE_ACCOUNT');
		$intAATServiceId		= $cgActionAssocationType->getValue('ACTION_ASSOCIATION_TYPE_SERVICE');
		$intAATContactId		= $cgActionAssocationType->getValue('ACTION_ASSOCIATION_TYPE_CONTACT');
		
		// All action types can be associated with an account
		$strSQL = "	INSERT INTO action_type_action_association_type (action_type_id, action_association_type_id)
					SELECT id, $intAATAccountId
					FROM action_type
					WHERE TRUE;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the action_type_action_association_type Table for account constraints. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback necessary
		
		// Action types that can be associated with a service
		$strSQL = "	INSERT INTO action_type_action_association_type (action_type_id, action_association_type_id)
					SELECT id, $intAATServiceId
					FROM action_type
					WHERE name IN ('Manual Bar', 'Manual Unbar', 'Manual TDC', 'Manual UnTDC', 'Logged Fault', 'Closed Fault', 'Checked Fault');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the action_type_action_association_type Table for service constraints. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback necessary
		
		// Action types that can be associated with a contact
		$strSQL = "	INSERT INTO action_type_action_association_type (action_type_id, action_association_type_id)
					SELECT id, $intAATContactId
					FROM action_type
					WHERE name IN ('Left Message to Call');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the action_type_action_association_type Table for contact constraints. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback necessary
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>