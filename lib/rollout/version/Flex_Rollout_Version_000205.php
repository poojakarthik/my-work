<?php

/**
 * Version 205 (Two-Hundred-And-Five) of database update.
 * This version: -
 *	1:	Adds the data_report_employee table
 *	2:	Adds the data_report_operation_profile table
 */

class Flex_Rollout_Version_000205 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Add datareport_employee table
		$strSQL	= "CREATE TABLE data_report_employee
					(
					         id 			BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
					         data_report_id	BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) DataReport',
					         employee_id    BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Employee',
					        
					         CONSTRAINT	pk_data_report_employee_id				PRIMARY KEY	(id),
					         CONSTRAINT	fk_data_report_employee_data_report_id	FOREIGN KEY	(data_report_id)	REFERENCES	DataReport(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
					         CONSTRAINT	fk_data_report_employee_employee_id		FOREIGN KEY	(employee_id)		REFERENCES	Employee(Id)	ON UPDATE CASCADE	ON DELETE CASCADE
					) ENGINE=InnoDB COMMENT = 'Defines a relationship between a data_report and an employee';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create data_report_employee table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE data_report_employee;");
		
		// 3: Add datareport_operation_profile table
		$strSQL = "	CREATE TABLE data_report_operation_profile
					(
						id 						BIGINT 	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						data_report_id 			BIGINT 	UNSIGNED	NOT NULL 					COMMENT '(FK) DataReport',
						operation_profile_id 	INT 	UNSIGNED	NOT NULL 					COMMENT '(FK) operation_profile',
					
						CONSTRAINT data_report_operation_profile 							PRIMARY KEY (id),
						CONSTRAINT fk_data_report_operation_profile_data_report_id 			FOREIGN KEY (data_report_id) 		REFERENCES DataReport(Id) 			ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_data_report_operation_profile_operation_profile_id	FOREIGN KEY (operation_profile_id) 	REFERENCES operation_profile(id)	ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE = innodb COMMENT = 'Defines a relationship between a data_report and an operation_profile';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create data_report_operation_profile table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE data_report_operation_profile;");
	}
	
	function rollback()
	{
		// Setup a connection for each database
		$arrConnections = array();
		foreach ($GLOBALS['*arrConstant']['DatabaseConnection'] as $strDb=>$arrDetails)
		{
			$arrConnections[$strDb] = Data_Source::get($strDb);
		}
		
		// Pointer to the appropriate db connection
		$objDb = NULL;
		
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				// Get reference to appropriate database
				$objDb = &$arrConnections[$this->rollbackSQL[$l]['Database']];
				
				// Perform the SQL
				$objResult = $objDb->query($this->rollbackSQL[$l]['SQL']);
				
				if (PEAR::isError($objResult))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l]['SQL'] . '. ' . $objResult->getMessage());
				}
			}
		}
	}
}

?>
