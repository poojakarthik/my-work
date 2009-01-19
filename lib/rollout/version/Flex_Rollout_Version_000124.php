<?php

/**
 * Version 124 of database update.
 * This version: -
 *	1:	Add the 'Destination Not Found' Destination for IDD
 *	2:	Add the destination_context Table
 *	3:	Populate the destination_context Table
 */

class Flex_Rollout_Version_000124 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the 'Destination Not Found' Destination for IDD
		$strSQL = "INSERT INTO Destination (Code, Description, Context) VALUES (100, 'Unknown Destination', 1);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the \'Destination Not Found\' Destination for IDD. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM Destination WHERE Code = 100;";
		
		// 2:	Add the destination_context Table
		$strSQL = "CREATE TABLE destination_context 
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id', 
						name VARCHAR(255) NOT NULL COMMENT 'Name of the Destination Context', 
						description VARCHAR(1024) NOT NULL COMMENT 'Description of the Destination Context', 
						const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Destination Context', 
						fallback_destination_id BIGINT(20) NULL COMMENT '(FK) Destination Id to use when no valid Destination can be found', 
						
						CONSTRAINT pk_destination_context PRIMARY KEY (id),
						CONSTRAINT fk_destination_context_fallback_destination_id FOREIGN KEY (fallback_destination_id) REFERENCES Destination(Id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the destination_context Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE destination_context;";
		
		// 3:	Populate the destination_context Table
		$strSQL = "INSERT INTO destination_context (id, name, description, const_name, fallback_destination_id) VALUES " .
					"(1	, 'IDD'	, 'International Direct Dial'	, 'DESTINATION_CONTEXT_IDD'						, (SELECT Id FROM Destination WHERE Code = 100)), " .
					"(2	, 'S&E'	, 'Service & Equipment'			, 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'	, (SELECT Id FROM Destination WHERE Code = 80001));";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the destination_context Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE destination_context;";
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