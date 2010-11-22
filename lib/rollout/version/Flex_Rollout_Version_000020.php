<?php

/**
 * Version 20 (twenty) of database update.
 * This version: -
 *	1:	Adds ServiceTotal.service_rate_plan Field
 *	2:	Adds service_type Table
 *	3:	Poulate service_type Table
 *	4:	Adds provisioning_type_nature Table
 *	5:	Populates provisioning_type_nature Table
 *	6:	Adds provisioning_type.provisioning_type_nature Field
 *	7:	Sets provisioning_type.provisioning_type_nature for existing records
 *	8:	Finishes populating provisioning_type Table
 *	9:	Adds service_line_status Table
 *	10:	Populates service_line_status Table
 *	11:	Adds service_line_status_update Table
 */

class Flex_Rollout_Version_000020 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds ServiceTotal.service_rate_plan Field
		$strSQL = "ALTER TABLE ServiceTotal ADD service_rate_plan BIGINT(20) UNSIGNED NULL AFTER RatePlan";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add ServiceTotal.service_rate_plan. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE ServiceTotal DROP service_rate_plan";
		
		// 2:	Adds service_type Table
		$strSQL = "CREATE TABLE service_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Service Type', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Service Type', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Service Type'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Service Type'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add service_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS service_type;";
		
		// 3:	Poulate service_type Table
		$strSQL = "INSERT INTO service_type (id, name, description, const_name) VALUES " .
				"	(100, 'ADSL'		, 'ADSL'					, 'SERVICE_TYPE_ADSL'),
					(101, 'Mobile'		, 'Mobile'					, 'SERVICE_TYPE_MOBILE'),
					(102, 'Land Line'	, 'Land Line'				, 'SERVICE_TYPE_LAND_LINE'),
					(103, 'Inbound'		, 'Inbound 13/1300/1800'	, 'SERVICE_TYPE_INBOUND'),
					(104, 'Dialup'		, 'Dialup Internet'			, 'SERVICE_TYPE_DIALUP');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate service_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE service_type;";
		
		// 4:	Adds provisioning_type_nature Table
		$strSQL = "CREATE TABLE provisioning_type_nature " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Nature', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Nature', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Nature', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Nature', " .
						"service_type BIGINT(20) NOT NULL COMMENT 'Service Type that this Nature corresponds to'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add provisioning_type_nature Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS provisioning_type_nature;";
		
		// 5:	Populates provisioning_type_nature Table
		$strSQL = "INSERT INTO provisioning_type_nature (name, description, const_name, service_type) VALUES " .
					"('Full Service', 'Land Line: Full Service', 'REQUEST_TYPE_NATURE_FULL_SERVICE', (SELECT id FROM service_type WHERE const_name = 'SERVICE_TYPE_LAND_LINE')), " .
					"('Preselection', 'Land Line: Preselection', 'REQUEST_TYPE_NATURE_PRESELECTION', (SELECT id FROM service_type WHERE const_name = 'SERVICE_TYPE_LAND_LINE')), " .
					"('Mobile', 'Mobile', 'REQUEST_TYPE_NATURE_MOBILE', (SELECT id FROM service_type WHERE const_name = 'SERVICE_TYPE_MOBILE')), " .
					"('Inbound', 'Inbound', 'REQUEST_TYPE_NATURE_INBOUND', (SELECT id FROM service_type WHERE const_name = 'SERVICE_TYPE_INBOUND'))," .
					"('ADSL', 'ADSL', 'REQUEST_TYPE_NATURE_ADSL', (SELECT id FROM service_type WHERE const_name = 'SERVICE_TYPE_ADSL'));";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add provisioning_type_nature Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE provisioning_type_nature;";
		
		// 6:	Adds provisioning_type.provisioning_type_nature Field
		$strSQL = "ALTER TABLE provisioning_type ADD provisioning_type_nature BIGINT(20) UNSIGNED NULL AFTER outbound";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add provisioning_type.provisioning_type_nature. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE provisioning_type DROP provisioning_type_nature";
		
		// 7:	Sets provisioning_type.provisioning_type_nature for existing records
		$strSQL = "UPDATE provisioning_type SET " .
					"provisioning_type_nature = " .
					"(CASE " .
					"	WHEN name IN ('Full Service', 'Full Service Reverse') THEN (SELECT id FROM provisioning_type_nature WHERE name = 'Full Service') " .
					"	WHEN name IN ('Pre-Selection', 'Bar', 'Unbar', 'Activation', 'Deactivation', 'Pre-Selection Reverse', 'Virtual Pre-Selection', 'Virtual Pre-Selection Reverse') THEN (SELECT id FROM provisioning_type_nature WHERE name = 'Preselection') " .
					"END);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to update provisioning_type.provisioning_type_nature Field. ' . $qryQuery->Error());
		}
		
		// 8:	Finishes populating provisioning_type Table
		$strSQL = "INSERT INTO provisioning_type (id, name, inbound, outbound, description, const_name, provisioning_type_nature) VALUES " .
				"	(908, 'Temporary Disconnection'				, 0, 0, 'Temporary Disconnection'			, 'PROVISIONING_TYPE_DISCONNECT_TEMPORARY'			, (SELECT id FROM provisioning_type_nature WHERE name = 'Full Service')),
					(909, 'Temporary Disconnection Reversal'	, 0, 0, 'Temporary Disconnection Reversal'	, 'PROVISIONING_TYPE_RECONNECT_TEMPORARY'				, (SELECT id FROM provisioning_type_nature WHERE name = 'Full Service')),
					(910, 'Full Service Lost (Churned)'			, 1, 0, 'Full Service Lost (Churned)'		, 'PROVISIONING_TYPE_LOSS_FULL'						, (SELECT id FROM provisioning_type_nature WHERE name = 'Full Service')),
					(911, 'Preselection Lost (Churned)'			, 1, 0, 'Preselection Lost (Churned)'		, 'PROVISIONING_TYPE_LOSS_PRESELECT'					, (SELECT id FROM provisioning_type_nature WHERE name = 'Preselection')),
					(912, 'Address Change'						, 1, 0, 'Address Change'					, 'PROVISIONING_TYPE_CHANGE_ADDRESS'					, (SELECT id FROM provisioning_type_nature WHERE name = 'Full Service')),
					(915, 'Virtual Preselection Lost'			, 1, 0, 'Virtual Preselection Lost'			, 'PROVISIONING_TYPE_LOSS_VIRTUAL_PRESELECTION'		, (SELECT id FROM provisioning_type_nature WHERE name = 'Preselection')),
					(916, 'Full Service Lost (Diconnected)'		, 1, 0, 'Full Service Lost (Diconnected)'	, 'PROVISIONING_TYPE_DISCONNECT_FULL'					, (SELECT id FROM provisioning_type_nature WHERE name = 'Full Service')),
					(917, 'Preselection Lost (Diconnected)'		, 1, 0, 'Preselection Lost (Diconnected)'	, 'PROVISIONING_TYPE_DISCONNECT_PRESELECT'			, (SELECT id FROM provisioning_type_nature WHERE name = 'Preselection'));";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate provisioning_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM provisioning_type WHERE id IN (908, 909, 910, 911, 912, 915, 916, 917);";
		
		// 9:	Adds service_line_status Table
		$strSQL = "CREATE TABLE service_line_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Line Status', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Nature', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Nature', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Nature' " .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add service_line_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS service_line_status;";
		
		// 10:	Populates service_line_status Table
		$strSQL = "INSERT INTO service_line_status (id, name, description, const_name) VALUES " .
				"	(500, 'Pending'						, 'Pending Connection'			, 'SERVICE_LINE_PENDING'),
					(501, 'Active'						, 'Active'						, 'SERVICE_LINE_ACTIVE'),
					(502, 'Disconnected'				, 'Disconnected'				, 'SERVICE_LINE_DISCONNECTED'),
					(503, 'Barred'						, 'Barred'						, 'SERVICE_LINE_BARRED'),
					(504, 'Temporarily Disconnected'	, 'Temporarily Disconnected'	, 'SERVICE_LINE_TEMPORARY_DISCONNECT'),
					(505, 'Rejected'					, 'Churn Request Rejected'		, 'SERVICE_LINE_REJECTED'),
					(506, 'Churned'						, 'Churned Away'				, 'SERVICE_LINE_CHURNED'),
					(507, 'Reversed'					, 'Churn Reversed'				, 'SERVICE_LINE_REVERSED');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to populate service_line_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE service_line_status;";
		
		// 11:	Adds service_line_status_update Table
		$strSQL = "CREATE TABLE service_line_status_update " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id', " .
						"current_line_status BIGINT(20) NOT NULL COMMENT 'Current Line Status', " .
						"provisioning_type BIGINT(20) NOT NULL COMMENT 'Request Type', " .
						"new_line_status BIGINT(20) NOT NULL COMMENT 'Resulting Line Status'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception_Database(__CLASS__ . ' Failed to add service_line_status_update Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS service_line_status_update;";
		
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
