<?php

/**
 * Version 160 of database update.
 * This version: -
 *
 *	1:	Update CDRCreditLink table so that it is in the new format
 *	2:	Create action_type_detail_requirement table
 *	3:	Populate action_type_detail_requirement table
 *	4:	Create action_type table
 *	5:	Create action table
 *	6:	Create account_action table
 *	7:	Create service_action table
 *	8:	Create contact_action table
 */

class Flex_Rollout_Version_000160 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Update CDRCreditLink table so that it is in the new format
		$strSQL = "RENAME TABLE CDRCreditLink TO cdr_credit_link;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename CDRCreditLink table to cdr_credit_link. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"RENAME TABLE cdr_credit_link TO CDRCreditLink;";
		
		$strSQL =	"ALTER TABLE cdr_credit_link " .
					"CHANGE Id id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "CHANGE CreditCDR credit_cdr_id BIGINT(20) UNSIGNED NOT NULL, ".
					"CHANGE DebitCDR debit_cdr_id BIGINT(20) UNSIGNED NOT NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename columns of the CDRCreditLink table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE cdr_credit_link " .
								"CHANGE id Id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    			"CHANGE credit_cdr_id CreditCDR BIGINT(20) UNSIGNED NOT NULL, ".
								"CHANGE debit_cdr_id DebitCDR BIGINT(20) UNSIGNED NOT NULL;";
		
		// 2:	Create action_type_detail_requirement table
		$strSQL = "	CREATE TABLE action_type_detail_requirement
					(
						id									SMALLINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name								VARCHAR(255)					NOT NULL					COMMENT 'Name of the Action Type Detail Requirement',
						description							VARCHAR(1024)					NOT NULL					COMMENT 'Description of the Action Type Detail Requirement',
						const_name							VARCHAR(512)					NULL						COMMENT 'Constant Alias of the Action Type Detail Requirement',
						
						CONSTRAINT	pk_action_type_detail_requirement_id	PRIMARY KEY (id)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the action_type_detail_requirement Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS action_type_detail_requirement;";
		
		// 3:	Populate action_type_detail_requirement table
		$strSQL = "	INSERT INTO action_type_detail_requirement (name, description, const_name) VALUES 
					('None'			, 'No Details'			, 'ACTION_TYPE_DETAIL_REQUIREMENT_NONE'),
					('Optional'		, 'Details Optional'	, 'ACTION_TYPE_DETAIL_REQUIREMENT_OPTIONAL'),  
					('Required'		, 'Details Required'	, 'ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the action_type_detail_requirement Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		// No rollback necessary

		// 4:	Create action_type table
		$strSQL = "	CREATE TABLE action_type
					(
						id									SMALLINT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name								VARCHAR(255)				NOT NULL					COMMENT 'Name of the Action Type',
						description							VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Action Type',
						const_name							VARCHAR(512)				NULL						COMMENT 'Constant Alias of the Action Type',
						action_type_detail_requirement_id	SMALLINT		UNSIGNED	NOT NULL					COMMENT '(FK) User Input Requirements',
						
						CONSTRAINT	pk_action_type_id						PRIMARY KEY (id),
						CONSTRAINT	fk_action_type_detail_requirement_id	FOREIGN KEY (action_type_detail_requirement_id)	REFERENCES action_type_detail_requirement(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the action_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS action_type;";
		
		// 5:	Create action table
		$strSQL = "	CREATE TABLE action
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						action_type_id			SMALLINT		UNSIGNED	NOT NULL								COMMENT '(FK) type of action',
						details					VARCHAR(32767)				NULL									COMMENT 'Additional Details for the Action',
						created_by_employee_id	BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Employee who performed the Action',
						created_on				TIMESTAMP					NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT 'Creation Timestamp',
						
						CONSTRAINT	pk_action_id						PRIMARY KEY (id),
						CONSTRAINT	fk_action_action_type_id			FOREIGN KEY (action_type_id)			REFERENCES action_type(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT	fk_action_created_by_employee_id	FOREIGN KEY (created_by_employee_id)	REFERENCES Employee(Id)		ON UPDATE CASCADE	ON DELETE RESTRICT
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the action Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS action;";

		// 6:	Create account_action table
		$strSQL = "	CREATE TABLE account_action
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						account_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Account',
						action_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) action',
						
						CONSTRAINT	pk_account_action_id			PRIMARY KEY (id),
						CONSTRAINT	fk_account_action_account_id	FOREIGN KEY (account_id)	REFERENCES Account(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_account_action_action_id		FOREIGN KEY (action_id)		REFERENCES action(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the account_action Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS account_action;";

		// 7:	Create service_action table
		$strSQL = "	CREATE TABLE service_action
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						service_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Service',
						action_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) action',
						
						CONSTRAINT	pk_service_action_id			PRIMARY KEY (id),
						CONSTRAINT	fk_service_action_service_id	FOREIGN KEY (service_id)	REFERENCES Service(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_service_action_action_id		FOREIGN KEY (action_id)		REFERENCES action(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the service_action Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS service_action;";

		// 8:	Create contact_action table
		$strSQL = "	CREATE TABLE contact_action
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						contact_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Contact',
						action_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) action',
						
						CONSTRAINT	pk_contact_action_id			PRIMARY KEY (id),
						CONSTRAINT	fk_contact_action_contact_id	FOREIGN KEY (contact_id)	REFERENCES Contact(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_contact_action_action_id		FOREIGN KEY (action_id)		REFERENCES action(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create the contact_action Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE IF EXISTS contact_action;";
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