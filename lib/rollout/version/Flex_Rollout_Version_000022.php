<?php

/**
 * Version 22 (twenty-two) of database update.
 * This version: -
 * 	1:	Add provisioning_request_status Table
 *	2:	Populate provisioning_request_status
 *	3:	Make service_line_status_update.current_line_status NULLable
 *	4:	Add service_line_status_update.provisioning_request_status Field
 *	5:	Populate service_line_status_update Table
 *	6:	Add provisioning_response_status Table
 *	7:	Populate provisioning_response_status Table
 */

class Flex_Rollout_Version_000022 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add provisioning_request_status Table
		$strSQL = "CREATE TABLE provisioning_request_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Request Status', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Request Status', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Request Status'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Request Status'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create provisioning_request_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE provisioning_request_status;";
		
		// 2:	Populate provisioning_request_status
		$strSQL = "INSERT INTO provisioning_request_status (id, name, description, const_name) VALUES 
					(300, 'Awaiting Dispatch'		, 'Awaiting Dispatch'				, 'REQUEST_STATUS_WAITING'), 
					(301, 'Pending'					, 'Pending'							, 'REQUEST_STATUS_PENDING'), 
					(302, 'Rejected by Carrier'		, 'Rejected by Carrier'				, 'REQUEST_STATUS_REJECTED'), 
					(303, 'Completed'				, 'Completed'						, 'REQUEST_STATUS_COMPLETED'), 
					(304, 'Cancelled'				, 'Cancelled'						, 'REQUEST_STATUS_CANCELLED'), 
					(305, 'Duplicated'				, 'Duplicated (Ignored)'			, 'REQUEST_STATUS_DUPLICATE'), 
					(306, 'Exporting'				, 'Currently Exporting'				, 'REQUEST_STATUS_EXPORTING'), 
					(307, 'Delivered'				, 'Awaiting Carrier Response'		, 'REQUEST_STATUS_DELIVERED'), 
					(308, 'Not Supported by Flex'	, 'Request Not Supported by Flex'	, 'REQUEST_STATUS_NO_MODULE'), 
					(309, 'Rejected by Flex'		, 'Rejected by Flex'				, 'REQUEST_STATUS_REJECTED_FLEX');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate provisioning_request_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE provisioning_request_status;";
		
		// 3:	Make service_line_status_update.current_line_status NULLable
		$strSQL = "ALTER TABLE service_line_status_update MODIFY current_line_status BIGINT(20) NULL ;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to make service_line_status_update.current_line_status NULLable. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE service_line_status_update MODIFY current_line_status BIGINT(20) NOT NULL ;";
		
		// 4:	Add service_line_status_update.provisioning_request_status Field
		$strSQL = "ALTER TABLE service_line_status_update ADD provisioning_request_status BIGINT(20) NULL ;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add service_line_status_update.provisioning_request_status Field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE service_line_status_update DROP provisioning_request_status;";
		
		// 5:	Populate service_line_status_update Table
		$strSQL = "INSERT INTO service_line_status_update (id, current_line_status, provisioning_type, provisioning_request_status, new_line_status) VALUES 
					(NULL,	NULL,	900,	303,	501),
					(NULL,	NULL,	900,	302,	505),
					(NULL,	NULL,	900,	301,	500),
					(NULL,	501,	900,	303,	501),
					(NULL,	505,	900,	303,	505),
					(NULL,	NULL,	901,	303,	501),
					(NULL,	NULL,	901,	302,	505),
					(NULL,	NULL,	901,	301,	500),
					(NULL,	501,	901,	303,	501),
					(NULL,	505,	901,	303,	505),
					(NULL,	NULL,	902,	303,	503),
					(NULL,	NULL,	903,	303,	501),
					(NULL,	NULL,	906,	303,	507),
					(NULL,	NULL,	907,	303,	507),
					(NULL,	NULL,	910,	303,	506),
					(NULL,	501,	910,	303,	501),
					(NULL,	NULL,	911,	303,	506),
					(NULL,	501,	911,	303,	501),
					(NULL,	NULL,	913,	303,	501),
					(NULL,	NULL,	913,	302,	505),
					(NULL,	NULL,	913,	301,	500),
					(NULL,	501,	913,	303,	501),
					(NULL,	505,	913,	303,	505),
					(NULL,	NULL,	914,	303,	507),
					(NULL,	NULL,	915,	303,	506),
					(NULL,	NULL,	916,	303,	506),
					(NULL,	501,	916,	303,	501),
					(NULL,	NULL,	917,	303,	506),
					(NULL,	501,	917,	303,	501);";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate service_line_status_update Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE service_line_status_update;";
		
		// 6:	Add provisioning_response_status Table
		$strSQL = "CREATE TABLE provisioning_response_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for the Response Status', " .
						"name VARCHAR(256) NOT NULL COMMENT 'Name for the Response Status', " .
						"description VARCHAR(512) NOT NULL COMMENT 'Description for the Response Status'," .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Response Status'" .
					") ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create provisioning_response_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE provisioning_response_status;";
		
		// 7:	Populate provisioning_response_status Table
		$strSQL = "INSERT INTO provisioning_response_status (id, name, description, const_name) VALUES 
					(400, 'Unable to Normalise'		, 'Unable to Normalise'				, 'RESPONSE_STATUS_CANT_NORMALISE'), 
					(401, 'Unable to Find Owner'	, 'Unable to Find Owner'			, 'RESPONSE_STATUS_BAD_OWNER'), 
					(402, 'Imported'				, 'Successfully Imported'			, 'RESPONSE_STATUS_IMPORTED'), 
					(403, 'Redundant'				, 'Redundant'						, 'RESPONSE_STATUS_REDUNDANT'), 
					(404, 'Duplicate'				, 'Duplicate'						, 'RESPONSE_STATUS_DUPLICATE');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate provisioning_response_status Table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE provisioning_response_status;";
		
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
