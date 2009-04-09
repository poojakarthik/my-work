<?php

/**
 * Version 162 of database update.
 * This version: -
 *
 *	1:	Add the permission Table
 *	2:	Populate the permission Table
 *
 *	3:	Add the permission_group Table
 *	4:	Populate the permission_group Table
 *
 *	5:	Add the person_group Table
 *	6:	Populate the person_group Table
 *
 *	7:	Add the person_group_permission Table
 *	8:	Populate the person_group_permission Table
 *
 *	9:	Add the person Table
 *
 *	10:	Add the person_employee Table
 *
 *	11:	Add the person_dealer Table
 *
 *	12:	Add the person_contact_account Table
 *
 *	13:	Add the person_contact_ticketing Table
 *
 *	14:	Add the person_person_group Table
 *
 *	15:	Add the person_permission Table
 *
 *	16:	Add the person_address Table
 *
 *	17:	Add the person_ticket Table
 *
 *	18:	Add the contact_method_type Table
 *	19:	Populate the contact_method_type Table
 *
 *	20:	Add the person_contact_method Table
 *
 *	21:	Add the account_person_association_type Table
 *	22:	Populate the account_person_association_type Table
 *
 *	23:	Add the account_person Table
 */

class Flex_Rollout_Version_000162 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the permission Table
		$strSQL = "	CREATE TABLE permission
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name of the Permission',
						description		VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Permission',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias of the Permission',
						
						CONSTRAINT	pk_permission_id					PRIMARY KEY	(id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the permission Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE permission;";

		// 2:	Populate the permission Table
		$strSQL = "	INSERT INTO permission (name, description, const_name) VALUES 
					('GOD'						, 'GOD Mode (access to all Flex functionality with additional debugging information)'	, 'PERMISSION_GOD'),
					('Super Admin'				, 'Super Administration (access to all Flex functionality)'								, 'PERMISSION_SUPER_ADMIN'),
					('Debug'					, 'Debug'																				, 'PERMISSION_DEBUG'),
					('General Admin'			, 'General Administration (access to advanced Flex functionality)'						, 'PERMISSION_GENERAL_ADMIN'),
					('Sales Admin'				, 'Sales Administration'																, 'PERMISSION_SALES_ADMIN'),
					('Knowledge Base Admin'		, 'Knowledge Base Administration'														, 'PERMISSION_KB_ADMIN'),
					('Knowledge Base Operator'	, 'Knowledge Base Operator'																, 'PERMISSION_KB_OPERATOR'),
					('Customer Group Admin'		, 'Customer Group Administration'														, 'PERMISSION_CUSTOMER_GROUP_ADMIN'),
					('Credit Management'		, 'Credit Management'																	, 'PERMISSION_CREDIT_MANAGEMENT'),
					('Rate Management'			, 'Rate Management'																		, 'PERMISSION_RATE_MANAGEMENT'),
					('Accounts'					, 'Accounts'																			, 'PERMISSION_ACCOUNTS'),
					('Sales'					, 'Sales'																				, 'PERMISSION_SALES'),
					('Enhanced Operator'		, 'Enhanced Operator'																	, 'PERMISSION_OPERATOR_ADVANCED'),
					('Operator'					, 'Operator'																			, 'PERMISSION_OPERATOR'),
					('Operator View'			, 'Operator (can only view data)'														, 'PERMISSION_OPERATOR_VIEW'),
					('Public'					, 'Public'																				, 'PERMISSION_PUBLIC'),
					('Ticketing Operator'		, 'Ticketing Operator'																	, 'PERMISSION_TICKETING_OPERATOR'),
					('Ticketing Admin'			, 'Ticketing Administration'															, 'PERMISSION_TICKETING_ADMIN');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the permission Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 3:	Add the permission_group Table
		$strSQL = "	CREATE TABLE permission_group
					(
						id					BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						group_permission_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Grouping Permission',
						child_permission_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Encompassed Permission',
						
						CONSTRAINT	pk_permission_group_id					PRIMARY KEY	(id),
						CONSTRAINT	fk_permission_group_group_permission_id	FOREIGN KEY	(group_permission_id)	REFERENCES permission(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_permission_group_child_permission_id	FOREIGN KEY	(child_permission_id)	REFERENCES permission(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the permission_group Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE permission_group;";

		// 4:	Populate the permission_group Table
		$strSQL = "	INSERT INTO permission_group (group_permission_id, child_permission_id) 
						SELECT (SELECT id FROM permission WHERE const_name = 'PERMISSION_GOD') AS group_permission_id, id AS child_permission_id
						FROM permission
						WHERE const_name NOT IN ('PERMISSION_GOD');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the GOD permission_group. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$strSQL = "	INSERT INTO permission_group (group_permission_id, child_permission_id) 
						SELECT (SELECT id FROM permission WHERE const_name = 'PERMISSION_SUPER_ADMIN') AS group_permission_id, id AS child_permission_id
						FROM permission
						WHERE const_name NOT IN ('PERMISSION_GOD', 'PERMISSION_DEBUG', 'PERMISSION_SUPER_ADMIN');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the SUPER ADMIN permission_group. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$strSQL = "	INSERT INTO permission_group (group_permission_id, child_permission_id) 
						SELECT (SELECT id FROM permission WHERE const_name = 'PERMISSION_OPERATOR_ADVANCED') AS group_permission_id, id AS child_permission_id
						FROM permission
						WHERE const_name IN ('PERMISSION_OPERATOR_VIEW', 'PERMISSION_ACCOUNTS', 'PERMISSION_OPERATOR', 'PERMISSION_PUBLIC', 'PERMISSION_SALES');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the OPERATOR ADVANCED permission_group. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$strSQL = "	INSERT INTO permission_group (group_permission_id, child_permission_id) 
						SELECT (SELECT id FROM permission WHERE const_name = 'PERMISSION_TICKETING_ADMIN') AS group_permission_id, id AS child_permission_id
						FROM permission
						WHERE const_name IN ('PERMISSION_TICKETING_OPERATOR');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the TICKETING ADMIN permission_group. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 5:	Add the person_group Table
		$strSQL = "	CREATE TABLE person_group
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name of the Person Group',
						description		VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Person Group',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias of the Person Group',
						
						CONSTRAINT	pk_person_group_id	PRIMARY KEY	(id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_group Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_group;";
		
		// 6:	Populate the person_group Table
		$strSQL = "	INSERT INTO person_group (name, description, const_name) VALUES
					('Employee'				, 'Employee'			, 'PERSON_GROUP_EMPLOYEE'),
					('Dealer'				, 'Dealer'				, 'PERSON_GROUP_DEALER'),
					('Account Contact'		, 'Account Contact'		, 'PERSON_GROUP_CONTACT_ACCOUNT'),
					('Ticketing Contact'	, 'Ticketing Contact'	, 'PERSON_GROUP_CONTACT_TICKETING');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the person_group Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 7:	Add the person_group_permission Table
		$strSQL = "	CREATE TABLE person_group_permission
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						person_group_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person Group',
						permission_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Permission',
						
						CONSTRAINT	pk_person_group_permission_id				PRIMARY KEY	(id),
						CONSTRAINT	fk_person_group_permission_person_group_id	FOREIGN KEY	(person_group_id)	REFERENCES person_group(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_group_permission_permission_id	FOREIGN KEY	(permission_id)		REFERENCES permission(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE account_type;";

		// 8:	Populate the person_group_permission Table
		$strSQL = "	INSERT INTO person_group_permission (person_group_id, permission_id) 
						SELECT (SELECT id FROM person_group WHERE const_name = 'PERSON_GROUP_EMPLOYEE') AS person_group_id, id AS permission_id
						FROM permission
						WHERE 1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the person_group_permission Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 9:	Add the person Table
		$strSQL = "	CREATE TABLE person
					(
						id								BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						salutation_id					BIGINT			UNSIGNED	NULL						COMMENT '(FK) Salutation (eg. Mr, Ms)',
						first_name						VARCHAR(256)				NOT NULL					COMMENT 'First Name',
						middle_names					VARCHAR(512)				NULL						COMMENT 'Middle Name(s)',
						last_name						VARCHAR(256)				NOT NULL					COMMENT 'Last Name/Surname',
						username						VARCHAR(64)					NULL						COMMENT 'System Username',
						password						CHAR(40)					NULL						COMMENT 'System Password (SHA1 Algorithm)',
						date_of_birth					DATE						NULL						COMMENT 'Date of Birth',
						is_telemarketing_blacklisted	TINYINT						NOT NULL	DEFAULT 0		COMMENT '1: Is Blacklisted; 0: Not Blacklisted',
						status_id						BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Active/Inactive Status',
						
						CONSTRAINT	pk_person_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_person_salutation_id	FOREIGN KEY	(salutation_id)	REFERENCES salutation(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT	fk_person_status_id		FOREIGN KEY	(status_id)		REFERENCES status(id)		ON UPDATE CASCADE	ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person;";

		// 10:	Add the person_employee Table
		$strSQL = "	CREATE TABLE person_employee
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						person_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Person this record defines Employee details for',
						employee_role_id		BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Role of the Employee'
						
						CONSTRAINT	pk_person_employee_id				PRIMARY KEY	(id),
						CONSTRAINT	fk_person_employee_person_id		FOREIGN KEY	(person_id)				REFERENCES person(id)			ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_employee_employee_role_id	FOREIGN KEY	(employee_role_id)		REFERENCES employee_role(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_employee Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_employee;";

		// 11:	Add the person_dealer Table
		$strSQL = "	CREATE TABLE person_dealer
					(
						id								BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						person_id						BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Person this record defines Dealer details for',
						manager_person_id				BIGINT			UNSIGNED	NULL									COMMENT '(FK) Up-line Manager for this Dealer',
						can_verify						TINYINT						NOT NULL	DEFAULT 0					COMMENT '1: Can verify sales other than those made by Dealers under their management; 0: Can only verify sales made by Dealers under their management',
						business_name					VARCHAR(256)				NULL									COMMENT 'Business Name',
						trading_name					VARCHAR(256)				NULL									COMMENT 'Trading Name',
						abn								CHAR(11)					NULL									COMMENT 'Australian Business Number',
						is_abn_registered				TINYINT						NULL									COMMENT '1: Yes; 0: No',
						bank_account_bsb				CHAR(6)						NULL									COMMENT 'Bank Account BSB',
						bank_account_number				VARCHAR(256)				NULL									COMMENT 'Bank Account Number',
						bank_account_name				VARCHAR(256)				NULL									COMMENT 'Bank Account Name',
						is_gst_registered				TINYINT						NOT NULL	DEFAULT 0					COMMENT '1: GST Registered; 0: Not GST Registered',
						termination_timestamp			TIMESTAMP					NULL									COMMENT 'Termination Timestamp',
						clawback_period					INT				UNSIGNED	NOT NULL	DEFAULT 0					COMMENT 'Clawback period for sales in hours',
						carrier_id						BIGINT			UNSIGNED	NULL									COMMENT '(FK) Sales Call Centre this Dealer belongs to',
						cascade_manager_restrictions	TINYINT						NOT NULL	DEFAULT 0					COMMENT '1: Cascade the Managers sale restrictions down to this Dealer; 0: Do not cascade',
						
						CONSTRAINT	pk_person_dealer_id							PRIMARY KEY	(id),
						CONSTRAINT	fk_person_dealer_person_id					FOREIGN KEY	(person_id)						REFERENCES person(id)			ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_dealer_manager_person_id			FOREIGN KEY	(manager_person_id)				REFERENCES person(id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT	fk_person_dealer_carrier_id					FOREIGN KEY	(carrier_id)					REFERENCES Carrier(Id)			ON UPDATE CASCADE	ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_dealer Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_dealer;";

		// 12:	Add the person_contact_account Table
		$strSQL = "	CREATE TABLE person_contact_account
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						person_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Person this record defines contact details for',
						position_title			VARCHAR(128)				NULL									COMMENT 'Job Position Title'
						
						CONSTRAINT	pk_person_contact_account_id				PRIMARY KEY	(id),
						CONSTRAINT	fk_person_contact_account_person_id			FOREIGN KEY	(person_id)				REFERENCES person(id)		ON UPDATE CASCADE	ON DELETE CASCADE,
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_contact_account Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_contact_account;";

		// 13:	Add the person_contact_ticketing Table
		$strSQL = "	CREATE TABLE person_contact_ticketing
					(
						id						BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						person_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Person this record defines contact details for',
						auto_reply_enabled		TINYINT						NULL		DEFAULT 1					COMMENT '1: Automatically reply to Email Tickets; 0: Do not automatically reply'
						
						CONSTRAINT	pk_person_contact_ticketing_id				PRIMARY KEY	(id),
						CONSTRAINT	fk_person_contact_ticketing_person_id		FOREIGN KEY	(person_id)				REFERENCES person(id)		ON UPDATE CASCADE	ON DELETE CASCADE,
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_contact_ticketing Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_contact_ticketing;";

		// 14:	Add the person_person_group Table
		$strSQL = "	CREATE TABLE person_person_group
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						person_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person',
						person_group_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person Group the Person belongs to',
						
						CONSTRAINT	pk_person_person_group_id				PRIMARY KEY	(id),
						CONSTRAINT	fk_person_person_group_person_id		FOREIGN KEY	(person_id)			REFERENCES person(id)		ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_person_group_person_group_id	FOREIGN KEY	(person_group_id)	REFERENCES person_group(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_person_group Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_person_group;";

		// 15:	Add the person_permission Table
		$strSQL = "	CREATE TABLE person_permission
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						person_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person',
						permission_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Permission the Person has',
						
						CONSTRAINT	pk_person_permission_id				PRIMARY KEY	(id),
						CONSTRAINT	fk_person_permission_person_id		FOREIGN KEY	(person_id)		REFERENCES person(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_permission_permission_id	FOREIGN KEY	(permission_id)	REFERENCES permission(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_permission Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_permission;";

		// 16:	Add the person_address Table
		$strSQL = "	CREATE TABLE person_address
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						person_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person',
						address_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Address',
						address_type_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Address Type (eg. Postal, Street)',
						
						CONSTRAINT	pk_person_address_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_person_address_person_id		FOREIGN KEY	(person_id)		REFERENCES person(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_address_address_id	FOREIGN KEY	(permission_id)	REFERENCES address(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_address Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_address;";

		// 17:	Add the person_ticket Table
		$strSQL = "	CREATE TABLE person_ticket
					(
						id									BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						person_id							BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person',
						ticketing_ticket_id					BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Ticket',
						
						CONSTRAINT	pk_person_ticket_id						PRIMARY KEY	(id),
						CONSTRAINT	fk_person_ticket_person_id				FOREIGN KEY	(person_id)				REFERENCES person(id)			ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_ticket_ticketing_ticket_id	FOREIGN KEY	(ticketing_ticket_id)	REFERENCES ticketing_ticket(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_ticket Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_ticket;";

		// 18:	Add the contact_method_type Table
		$strSQL = "	CREATE TABLE contact_method_type
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name of the Contact Method Type',
						description		VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Contact Method Type',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias of the Contact Method Type',
						
						CONSTRAINT	pk_contact_method_type_id	PRIMARY KEY	(id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the contact_method_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE contact_method_type;";
		
		// 19:	Populate the contact_method_type Table
		$strSQL = "	INSERT INTO contact_method_type (name, description, const_name) VALUES
					('Email'	, 'Email',	'CONTACT_METHOD_TYPE_EMAIL'),
					('Fax'		, 'Fax',	'CONTACT_METHOD_TYPE_FAX'),
					('Phone'	, 'Phone',	'CONTACT_METHOD_TYPE_PHONE'),
					('Mobile'	, 'Mobile',	'CONTACT_METHOD_TYPE_MOBILE');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the contact_method_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 20:	Add the person_contact_method Table
		$strSQL = "	CREATE TABLE person_contact_method
					(
						id							BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						person_id					BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person',
						contact_method_type_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Contact Method Type',
						details						VARCHAR(512)				NOT NULL					COMMENT 'Details of the Contact Method (eg. Phone Number, Email Address)',
						is_primary					TINYINT						NOT NULL	DEFAULT 0		COMMENT '1: Primary Contact Method; 0: Alternative Contact Method',
						is_verified					TINYINT						NOT NULL	DEFAULT 0		COMMENT '1: Verified; 0: Not Verified',
						
						CONSTRAINT	pk_person_contact_method_id						PRIMARY KEY	(id),
						CONSTRAINT	fk_person_contact_method_person_id				FOREIGN KEY	(person_id)					REFERENCES person(id)				ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_person_contact_method_contact_method_type_id	FOREIGN KEY	(contact_method_type_id)	REFERENCES contact_method_type(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the person_contact_method Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE person_contact_method;";

		// 21:	Add the account_person_association_type Table
		$strSQL = "	CREATE TABLE account_person_association_type
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name of the Association Type',
						description		VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Association Type',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias of the Association Type',
						
						CONSTRAINT	pk_account_person_association_type_id	PRIMARY KEY	(id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_person_association_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE account_person_association_type;";
		
		// 22:	Populate the account_person_association_type Table
		$strSQL = "	INSERT INTO account_person_association_type (name, description, const_name) VALUES
					('Primary'		, 'Primary',	'ACCOUNT_PERSON_ASSOCIATION_TYPE_PRIMARY'),
					('Billing'		, 'Billing',	'ACCOUNT_PERSON_ASSOCIATION_TYPE_BILLING'),
					('Technical'	, 'Technical',	'ACCOUNT_PERSON_ASSOCIATION_TYPE_TECHNICAL');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the account_person_association_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 23:	Add the account_person Table
		$strSQL = "	CREATE TABLE account_person
					(
						id									BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						account_id							BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Account',
						person_id							BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Person',
						account_person_association_type_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) How the Person is Associated with the Account',
						
						CONSTRAINT	pk_account_person_id									PRIMARY KEY	(id),
						CONSTRAINT	fk_account_person_account_id							FOREIGN KEY	(account_id)							REFERENCES account(id)							ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_account_person_person_id								FOREIGN KEY	(person_id)								REFERENCES person(id)							ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_account_person_account_person_association_type_id	FOREIGN KEY	(account_person_association_type_id)	REFERENCES account_person_association_type(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_person Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE account_person;";
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