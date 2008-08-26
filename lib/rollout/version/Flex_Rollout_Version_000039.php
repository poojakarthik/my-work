<?php

/**
 * Version 39 (thirty-nine) of database update.
 * This version: -
 *	1:	Creates data_type table
 *	2:	Populates data_type table
 *	3:	Creates billing_charge_module table
 *	4:	Creates billing_charge_module_config table
 */

class Flex_Rollout_Version_000039 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Create the data_type Table
		$strSQL = "CREATE TABLE data_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Data Type', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Data Type', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Data Type'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Data Type'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add data_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS data_type;";
		
		// 2:	Populate the data_type Table
		$strSQL = "INSERT INTO data_type (id, name, description, const_name) VALUES 
						(1, 'String'		, 'String'		, 'DATA_TYPE_STRING'),
						(2, 'Integer'		, 'Integer'		, 'DATA_TYPE_INTEGER'),
						(3, 'Float'			, 'Float'		, 'DATA_TYPE_FLOAT'),
						(4, 'Boolean'		, 'Boolean'		, 'DATA_TYPE_BOOLEAN'),
						(5, 'Serialised'	, 'Serialised'	, 'DATA_TYPE_SERIALISED'),
						(6, 'Array'			, 'String'		, 'DATA_TYPE_ARRAY');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate data_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE data_type;";
		
		//	3:	Creates billing_charge_module table
		$strSQL = "CREATE TABLE billing_charge_module " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Billing Charge Module Instance', " .
						"class VARCHAR(1024) NOT NULL COMMENT 'Module Class Name'," .
						"description VARCHAR(1024) NULL COMMENT 'Optional Description for the Instance'," .
						"active_status_id BIGINT(20) NOT NULL DEFAULT 1 COMMENT 'FK to active_status table'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create billing_charge_module Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS billing_charge_module;";
		
		//	4:	Creates billing_charge_module_config table
		$strSQL = "CREATE TABLE billing_charge_module_config " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Billing Charge Module Config Field', " .
						"billing_charge_module_id BIGINT(20) NOT NULL COMMENT 'FK to billing_charge_module table'," .
						"name VARCHAR(256) NULL COMMENT 'Name of the field'," .
						"data_type_id BIGINT(20) NULL COMMENT 'FK to data_type table'," .
						"description VARCHAR(1024) NULL COMMENT 'Description for the field'," .
						"value VARCHAR(4096) NOT NULL DEFAULT 1 COMMENT 'Value of the field'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create billing_charge_module_config Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS billing_charge_module_config;";
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
