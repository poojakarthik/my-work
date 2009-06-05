<?php

/**
 * Version 177 of database update.
 * This version: -
 *	
 *	1:	Add the operation Table
 *	2:	Add the operation_prerequisite Table
 *	3:	Add the operation_profile Table
 *	4:	Add the operation_profile_children Table
 *	5:	Add the operation_profile_operation Table
 *	6:	Add the employee_operation Table
 *	7:	Add the employee_operation_profile Table
 *	8:	Add the employee_operation_log Table
 *	9:	Add the employee_operation_log_account Table
 *	10:	Add the employee_operation_log_service Table
 *	11:	Add the Employee.is_god Field
 *	12:	Populate the Employee.is_god Field
 *
 */

class Flex_Rollout_Version_000177 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the operation Table
		$strSQL = "	CREATE TABLE	operation
					(
						id				INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name',
						description		VARCHAR(1024)				NULL						COMMENT 'Description',
						system_name		VARCHAR(256)				NOT NULL					COMMENT 'System Name',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias',
						is_assignable	TINYINT						NOT NULL	DEFAULT 1		COMMENT '1: Can be assigned in Flex; 0: Cannot be assigned in Flex',
						flex_module_id	BIGINT						NULL						COMMENT '(FK) Flex Module that contains this Operation',
						status_id		BIGINT						NOT NULL					COMMENT '(FK) Status',
						
						CONSTRAINT	pk_operation_id				PRIMARY KEY (id),
						CONSTRAINT	fk_operation_flex_module_id	FOREIGN KEY (flex_module_id)	REFERENCES flex_module(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_operation_status_id		FOREIGN KEY (status_id)			REFERENCES status(id)		ON UPDATE CASCADE	ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the operation Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	operation;";
		
		//	2:	Add the operation_prerequisite Table
		$strSQL = "	CREATE TABLE	operation_prerequisite
					(
						id							INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						operation_id				INTEGER			UNSIGNED	NOT NULL					COMMENT '(FK) Dependent Operation',
						prerequisite_operation_id	INTEGER			UNSIGNED	NOT NULL					COMMENT '(FK) Prerequisite Operation',
						
						CONSTRAINT	pk_operation_prerequisite_id						PRIMARY KEY (id),
						CONSTRAINT	fk_operation_prerequisite_operation_id				FOREIGN KEY (operation_id)				REFERENCES operation(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_operation_prerequisite_prerequisite_operation_id	FOREIGN KEY (prerequisite_operation_id)	REFERENCES operation(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the operation_prerequisite Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	operation_prerequisite;";
		
		//	3:	Add the operation_profile Table
		$strSQL = "	CREATE TABLE	operation_profile
					(
						id				INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name',
						description		VARCHAR(1024)				NULL						COMMENT 'Description',
						status_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Status',
						
						CONSTRAINT	pk_operation_profile_id				PRIMARY KEY (id),
						CONSTRAINT	fk_operation_profile_status_id		FOREIGN KEY (status_id)	REFERENCES status(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the operation_profile Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	operation_profile;";
		
		//	4:	Add the operation_profile_children Table
		$strSQL = "	CREATE TABLE	operation_profile_children
					(
						id							INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						parent_operation_profile_id	INTEGER			UNSIGNED	NOT NULL					COMMENT '(FK) Parent Operation Profile',
						child_operation_profile_id	INTEGER			UNSIGNED	NOT NULL					COMMENT '(FK) Child Operation Profile',
						
						CONSTRAINT	pk_operation_profile_children_id							PRIMARY KEY (id),
						CONSTRAINT	fk_operation_profile_children_parent_operation_profile_id	FOREIGN KEY (parent_operation_profile_id)	REFERENCES operation_profile(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_operation_profile_children_child_operation_profile_id	FOREIGN KEY (child_operation_profile_id)	REFERENCES operation_profile(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the operation_profile_children Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	operation_profile_children;";
		
		//	5:	Add the operation_profile_operation Table
		$strSQL = "	CREATE TABLE	operation_profile_operation
					(
						id							INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						operation_profile_id		INTEGER			UNSIGNED	NOT NULL					COMMENT '(FK) Operation Profile',
						operation_id				INTEGER			UNSIGNED	NOT NULL					COMMENT '(FK) Operation',
						
						CONSTRAINT	pk_operation_profile_operation_id					PRIMARY KEY (id),
						CONSTRAINT	fk_operation_profile_operation_operation_profile_id	FOREIGN KEY (operation_profile_id)	REFERENCES operation_profile(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_operation_profile_operation_operation_id			FOREIGN KEY (operation_id)			REFERENCES operation(id)			ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the operation_profile_operation Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	operation_profile_operation;";
		
		//	6:	Add the employee_operation Table
		$strSQL = "	CREATE TABLE	employee_operation
					(
						id							INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT					COMMENT 'Unique Identifier',
						employee_id					BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Employee',
						operation_id				INTEGER			UNSIGNED	NOT NULL									COMMENT '(FK) Permitted Operation',
						start_datetime				DATETIME					NOT NULL	DEFAULT '0000-00-00 00:00:00'	COMMENT 'Effective Start Datetime for this permission',
						end_datetime				DATETIME					NOT NULL	DEFAULT '9999-12-31 23:59:59'	COMMENT 'Effective End Datetime for this permission',
						assigned_timestamp			TIMESTAMP					NOT NULL	DEFAULT	CURRENT_TIMESTAMP		COMMENT 'Timestamp when the permission was assigned',
						assigned_employee_id		BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Employee who assigned this permission',
						
						CONSTRAINT	pk_employee_operation_id					PRIMARY KEY (id),
						CONSTRAINT	fk_employee_operation_employee_id			FOREIGN KEY (employee_id)			REFERENCES Employee(Id)		ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_employee_operation_operation_id			FOREIGN KEY (operation_id)			REFERENCES operation(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_employee_operation_assigned_employee_id	FOREIGN KEY (assigned_employee_id)	REFERENCES Employee(Id)		ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the employee_operation Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	employee_operation;";
		
		//	7:	Add the employee_operation_profile Table
		$strSQL = "	CREATE TABLE	employee_operation_profile
					(
						id							INTEGER			UNSIGNED	NOT NULL	AUTO_INCREMENT					COMMENT 'Unique Identifier',
						employee_id					BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Employee',
						operation_profile_id		INTEGER			UNSIGNED	NOT NULL									COMMENT '(FK) Permitted Operation Profile',
						start_datetime				DATETIME					NOT NULL	DEFAULT '0000-00-00 00:00:00'	COMMENT 'Effective Start Datetime for this permission',
						end_datetime				DATETIME					NOT NULL	DEFAULT '9999-12-31 23:59:59'	COMMENT 'Effective End Datetime for this permission',
						assigned_timestamp			TIMESTAMP					NOT NULL	DEFAULT	CURRENT_TIMESTAMP		COMMENT 'Timestamp when the permission was assigned',
						assigned_employee_id		BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Employee who assigned this permission',
						
						CONSTRAINT	pk_employee_operation_profile_id					PRIMARY KEY (id),
						CONSTRAINT	fk_employee_operation_profile_employee_id			FOREIGN KEY (employee_id)			REFERENCES Employee(Id)				ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_employee_operation_profile_operation_profile_id	FOREIGN KEY (operation_profile_id)	REFERENCES operation_profile(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_employee_operation_profile_assigned_employee_id	FOREIGN KEY (assigned_employee_id)	REFERENCES Employee(Id)				ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the employee_operation_profile Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	employee_operation_profile;";
		
		//	8:	Add the employee_operation_log Table
		$strSQL = "	CREATE TABLE	employee_operation_log
					(
						id							BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT					COMMENT 'Unique Identifier',
						employee_id					BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Employee',
						operation_id				INTEGER			UNSIGNED	NOT NULL									COMMENT '(FK) Operation',
						was_authorised				TINYINT						NOT NULL									COMMENT '1: Authorised; 0: Restricted',
						operation_timestamp			TIMESTAMP					NOT NULL	DEFAULT	CURRENT_TIMESTAMP		COMMENT 'Timestamp of the Operation',
						description					VARCHAR(1024)				NULL										COMMENT 'Description'
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the employee_operation_log Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	employee_operation_log;";
		
		//	9:	Add the employee_operation_log_account Table
		$strSQL = "	CREATE TABLE	employee_operation_log_account
					(
						id							BIGINT			UNSIGNED	NOT NULL		AUTO_INCREMENT				COMMENT 'Unique Identifier',
						employee_operation_log_id	BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Employee Operation Log',
						account_id					BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Account',
						
						CONSTRAINT	pk_employee_operation_log_account_id						PRIMARY KEY (id),
						CONSTRAINT	fk_employee_operation_log_account_employee_operation_log_id	FOREIGN KEY (employee_operation_log_id)	REFERENCES employee_operation_log(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_employee_operation_log_account_employee_account_id		FOREIGN KEY (account_id)				REFERENCES Account(Id)					ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the employee_operation_log_account Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	employee_operation_log_account;";
		
		//	10:	Add the employee_operation_log_service Table
		$strSQL = "	CREATE TABLE	employee_operation_log_service
					(
						id							BIGINT			UNSIGNED	NOT NULL		AUTO_INCREMENT				COMMENT 'Unique Identifier',
						employee_operation_log_id	BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Employee Operation Log',
						service_id					BIGINT			UNSIGNED	NOT NULL									COMMENT '(FK) Service',
						
						CONSTRAINT	pk_employee_operation_log_service_id						PRIMARY KEY (id),
						CONSTRAINT	fk_employee_operation_log_service_employee_operation_log_id	FOREIGN KEY (employee_operation_log_id)	REFERENCES employee_operation_log(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_employee_operation_log_service_employee_service_id		FOREIGN KEY (account_id)				REFERENCES Service(Id)					ON UPDATE CASCADE	ON DELETE CASCADE)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the employee_operation_log_service Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DROP TABLE	employee_operation_log_service;";
		
		//	11:	Add the Employee.is_god Field
		$strSQL = "	ALTER TABLE	Employee
						ADD	is_god	TINYINT	NOT NULL	DEFAULT 0	COMMENT '1: GOD Employee (trumps permissions); 0: General Employee';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Employee.is_god Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	Employee
										DROP	is_god;";
		
		//	12:	Populate the Employee.is_god Field
		$strSQL = "	UPDATE	Employee
					SET		is_god = 1
					WHERE	Privileges = 140737488355327;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the Employee.is_god Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
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