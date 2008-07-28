<?php

/**
 * Version 20 (twenty) of database update.
 * This version: -
 *	1:	Adds ServiceTotal.service_rate_plan Field
 *	2:	Adds service_type Table
 *	3:	Poulate service_type Table
 *	4:	Adds provisioning_request_type_nature Table
 *	5:	Populates provisioning_request_type_nature Table
 *	6:	Adds provisioning_request_type Table
 *	7:	Populates provisioning_request_type Table
 *	8:	Adds service_line_status Table
 *	9:	Populates service_line_status Table
 *	10:	Adds provisioning_request_line_status Table
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
			throw new Exception(__CLASS__ . ' Failed to add ServiceTotal.service_rate_plan. ' . $qryQuery->Error());
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
			throw new Exception(__CLASS__ . ' Failed to add service_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS service_type;";
		
		// 3:	Poulate service_type Table
		$arrServiceTypeSQL	= Array();
		foreach ($GLOBALS['*arrConstant']['ServiceType'] as $intId=>$arrConstant)
		{
			$arrServiceTypeSQL[]	= "({$intId}, '{$arrConstant['Description']}', '{$arrConstant['Description']}', '{$arrConstant['Constant']}')";
		}
		$strSQL = "INSERT INTO service_type (id, name, description, const_name) VALUES ".implode(', ', $arrServiceTypeSQL).";";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate service_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE service_type;";
		
		// 4:	Adds provisioning_request_type_nature Table
		$strSQL = "CREATE TABLE provisioning_request_type_nature " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Nature', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Nature', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Nature', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Nature', " .
						"service_type BIGINT(20) NOT NULL COMMENT 'Service Type that this Nature corresponds to'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add provisioning_request_type_nature Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS provisioning_request_type_nature;";
		
		// 5:	Populates provisioning_request_type_nature Table
		$strSQL = "INSERT INTO provisioning_request_type_nature (name, description, const_name, service_type) VALUES " .
					"('Full Service', 'Land Line: Full Service', 'REQUEST_TYPE_NATURE_FULL_SERVICE', ".SERVICE_TYPE_LAND_LINE."), " .
					"('Preselection', 'Land Line: Preselection', 'REQUEST_TYPE_NATURE_PRESELECTION', ".SERVICE_TYPE_LAND_LINE."), " .
					"('Mobile', 'Mobile', 'REQUEST_TYPE_NATURE_MOBILE', ".SERVICE_TYPE_MOBILE."), " .
					"('Inbound', 'Inbound', 'REQUEST_TYPE_NATURE_INBOUND', ".SERVICE_TYPE_INBOUND.")," .
					"('ADSL', 'ADSL', 'REQUEST_TYPE_NATURE_ADSL', ".SERVICE_TYPE_ADSL.");";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add provisioning_request_type_nature Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE provisioning_request_type_nature;";
		
 		// 6:	Adds provisioning_request_type Table
		$strSQL = "CREATE TABLE provisioning_request_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Request Type', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Nature', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Nature', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Nature', " .
						"provisioning_request_type_nature BIGINT(20) NOT NULL COMMENT 'Nature of this Request Type'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add provisioning_request_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS provisioning_request_type;";
		
		// 7:	Populates provisioning_request_type Table
		$arrRequestTypeSQL	= Array();
		foreach ($GLOBALS['*arrConstant']['Request'] as $intId=>$arrConstant)
		{
			$arrRequestTypeSQL[]	= "({$intId}, '{$arrConstant['Description']}', '{$arrConstant['Description']}', '{$arrConstant['Constant']}')";
		}
		$strSQL = "INSERT INTO provisioning_request_type (id, name, description, const_name) VALUES ".implode(', ', $arrRequestTypeSQL).";";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate provisioning_request_type Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE provisioning_request_type;";
		
		// 8:	Adds service_line_status Table
		$strSQL = "CREATE TABLE service_line_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Line Status', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Nature', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Nature', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Nature' " .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add service_line_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS service_line_status;";
		
		// 9:	Populates service_line_status Table
		$arrRequestTypeSQL	= Array();
		foreach ($GLOBALS['*arrConstant']['LineStatus'] as $intId=>$arrConstant)
		{
			$arrRequestTypeSQL[]	= "({$intId}, '{$arrConstant['Description']}', '{$arrConstant['Description']}', '{$arrConstant['Constant']}')";
		}
		$strSQL = "INSERT INTO service_line_status (id, name, description, const_name) VALUES ".implode(', ', $arrRequestTypeSQL).";";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate service_line_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE service_line_status;";
		
		// 10:	Adds provisioning_request_line_status Table
		$strSQL = "CREATE TABLE provisioning_request_line_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id', " .
						"current_line_status BIGINT(20) NOT NULL COMMENT 'Current Line Status', " .
						"provisioning_request_type BIGINT(20) NOT NULL COMMENT 'Request Type', " .
						"new_line_status BIGINT(20) NOT NULL COMMENT 'Resulting Line Status'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add provisioning_request_line_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS provisioning_request_line_status;";
		
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
