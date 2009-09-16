<?php

/**
 * Version 186 of database update.
 * This version: -
 *	
 *	1:	Add the charge_type_visibility Table
 *	2:	Populate the charge_type_visibility Table
 *	3:	Add the ChargeType.charge_type_visibility_id Field
 *	4:	Set the default value for ChargeType.charge_type_visibility_id to VISIBLE
 *	5:	Alter ChargeType.charge_type_visibility_id so that it cannot be NULL
 *
 */

class Flex_Rollout_Version_000186 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the charge_type_visibility Table
		$strSQL = "	CREATE TABLE	charge_type_visibility
					(
						id				INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name',
						description		VARCHAR(1024)				NULL						COMMENT 'Description',
						system_name		VARCHAR(256)				NOT NULL					COMMENT 'System Name',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias',
						
						CONSTRAINT	pk_operation_id				PRIMARY KEY (id),
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the charge_type_visibility Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	charge_type_visibility;";
		
		//	2:	Populate the charge_type_visibility Table
		$strSQL = "	INSERT INTO	charge_type_visibility
						(name, description, system_name, const_name)
					VALUES
						('Visible'			, 'Visible to all Users'				, 'VISIBLE'			, 'CHARGE_TYPE_VISIBILITY_VISIBLE'),
						('Hidden'			, 'Hidden from all Users'				, 'HIDDEN'			, 'CHARGE_TYPE_VISIBILITY_HIDDEN'),
						('Credit Control'	, 'Visible to Credit Controllers only'	, 'CREDIT_CONTROL'	, 'CHARGE_TYPE_VISIBILITY_CREDIT_CONTROL');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the charge_type_visibility Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		//	3:	Add the ChargeType.charge_type_visibility_id Field
		$strSQL = "	ALTER TABLE	ChargeType
					ADD COLUMN		charge_type_visibility_id	INTEGER	UNSIGNED	NULL	COMMENT '(FK) ChargeType Visiblity Mode',
					ADD CONSTRAINT	fk_charge_type_charge_type_visibility_id	FOREIGN KEY (charge_type_visibility_id)	REFERENCES charge_type_visibility(id)	ON UPDATE CASCADE	ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ChargeType.charge_type_visibility_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	ChargeType
									DROP CONSTRAINT	fk_charge_type_charge_type_visibility_id,
									DROP COLUMN		charge_type_visibility_id;";
		
		//	4:	Set the default value for ChargeType.charge_type_visibility_id to VISIBLE
		$strSQL = "	UPDATE	ChargeType
					SET		charge_type_visibility_id	= (SELECT id FROM charge_type_visibility WHERE system_name = 'VISIBLE' LIMIT 1)
					WHERE	charge_type_visibility_id IS NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to set the default value for ChargeType.charge_type_visibility_id to VISIBLE. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		//	5:	Alter ChargeType.charge_type_visibility_id so that it cannot be NULL
		$strSQL = "	ALTER TABLE	ChargeType
					MODIFY COLUMN		charge_type_visibility_id	INTEGER	UNSIGNED	NOT NULL	COMMENT '(FK) ChargeType Visiblity Mode';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to alter ChargeType.charge_type_visibility_id so that it cannot be NULL. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
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