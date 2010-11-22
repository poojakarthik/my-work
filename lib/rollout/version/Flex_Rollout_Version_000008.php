<?php

/**
 * Version 8 (eight) of database update.
 * This version: -
 *	1:	Creates provisioning_type table
 *	2:	Creates carrier_module_provisioning_support table
 *	3:	Creates active_status table
 */

class Flex_Rollout_Version_000008 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);

		// Create the provisioning_type Table
		$strSQL = "
			CREATE TABLE provisioning_type (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
				name VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Name of provisioning type',
				inbound TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Whether or not inbound messaging is supported',
				outbound TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Whether or not outbound messaging is supported',
				description VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Description of provisioning type',
				PRIMARY KEY ( id )
			) ENGINE = InnoDB 
		";

		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create provisioning_type table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE provisioning_type";

		// Create the carrier_module_provisioning_support Table
		$strSQL = "
			CREATE TABLE carrier_module_provisioning_support (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
			carrier_module_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to CarrierModule table',
			provisioning_type_id BIGINT UNSIGNED NOT NULL COMMENT 'FK to provisioning_type table',
			status_id SMALLINT UNSIGNED NOT NULL COMMENT 'FK to active_status table',
			PRIMARY KEY ( id )
			) ENGINE = InnoDB COMMENT = 'Stores status of provisioning_type for CarrierModule'
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create carrier_module_provisioning_support table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE carrier_module_provisioning_support";

		// Add the active_status table
		$strSQL = "
			 CREATE TABLE active_status (
				id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT ,
				active TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Flag - 1 for active or 0 for inactive',
				description VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'Description of active status',
				PRIMARY KEY ( id )
			) ENGINE = InnoDB COMMENT = 'Active statuses' 
		";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to create the active_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE active_status";

		// Add the active_status table
		$strSQL = "INSERT INTO active_status (active, description) VALUES (0, 'Inactive'), (1, 'Active')";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to insert default data into the active_status table. ' . $qryQuery->Error());
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
