<?php

/**
 * Version 162 of database update.
 * This version: -
 *
 *	1:	Add the address_locality Table
 *
 *	2:	Add the address_type Table
 *	3:	Populate the address_type Table
 *
 *	4:	Add the address Table
 *
 *	5:	Add the account_type Table
 *	6:	Populate the account_type Table
 *
 *	7:	Add the account Table
 *
 *	8:	Add the account_address Table
 */

class Flex_Rollout_Version_000162 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the address_locality Table
		$strSQL = "	CREATE TABLE address_locality
					(
						id			BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(256)				NOT NULL					COMMENT 'Name of the Locality',
						postcode	INT				UNSIGNED	NOT NULL					COMMENT 'Postcode of the Locality',
						state_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) State of the Locality',
						
						CONSTRAINT	pk_address_locality_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_address_locality_state_id	FOREIGN KEY	(state_id)	REFERENCES	state(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the address_locality Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE address_locality;";

		// 2:	Add the address_type Table
		$strSQL = "	CREATE TABLE address_type
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name			VARCHAR(256)				NOT NULL					COMMENT 'Name of the Address Type',
						description		VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Address Type',
						const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias of the Address Type',
						
						CONSTRAINT	pk_address_type_id	PRIMARY KEY	(id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the address_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE address_type;";

		// 3:	Populate the address_type Table
		$strSQL = "	INSERT INTO address_type (name, description, const_name) VALUES 
					('Street'	, 'Street Address'	, 'ADDRESS_TYPE_STREET'),
					('Postal'	, 'Postal Address'	, 'ADDRESS_TYPE_POSTAL');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the address_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 4:	Add the address Table
		$strSQL = "	CREATE TABLE address
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						address_line_1	VARCHAR(512)				NOT NULL					COMMENT 'Address Line 1',
						address_line_2	VARCHAR(512)				NULL						COMMENT 'Address Line 2',
						address_locality_id		BIGINT			UNSIGNED	NOT NULL			COMMENT '(FK) Locality',
						
						CONSTRAINT	pk_address_id					PRIMARY KEY	(id),
						CONSTRAINT	fk_address_address_locality_id	FOREIGN KEY	(address_locality_id)	REFERENCES address_locality(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the address Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE address;";

		// 5:	Add the account_type Table
		$strSQL = "	CREATE TABLE account_type
					(
						id			BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						name		VARCHAR(256)				NOT NULL					COMMENT 'Name of the Account Type',
						description	VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Account Type',
						const_name	VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias of the Account Type',
						
						CONSTRAINT	pk_account_type_id			PRIMARY KEY	(id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE account_type;";

		// 6:	Populate the account_type Table
		$strSQL = "	INSERT INTO account_type (name, description, const_name) VALUES 
					('Residential'	, 'Residential'	, 'ACCOUNT_TYPE_RESIDENTIAL'),
					('Business'		, 'Business'	, 'ACCOUNT_TYPE_BUSINESS');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the account_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 7:	Add the account Table
		$strSQL = "	CREATE TABLE account
					(
						id								BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						customer_group_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Customer Group this Account belongs to',
						account_type_id					BIGINT			UNSIGNED	NULL									COMMENT '(FK) Type of Account (eg. Residential, Business)',
						account_name					VARCHAR(256)				NOT NULL								COMMENT 'Account Name (End User or Business Name)',
						trading_name					VARCHAR(256)				NULL									COMMENT 'Business Trading Name',
						abn								CHAR(11)					NULL									COMMENT 'Australian Business Number',
						acn								CHAR(9)						NULL									COMMENT 'Australian Company Number',
						payment_term					INT				UNSIGNED	NOT NULL								COMMENT 'Number of days the Account has to pay a bill',
						created_by_person_id			BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Person who created the Account',
						created_on_timestamp			TIMESTAMP					NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT 'Creation Timestamp',
						waive_payment_method_fee		TINYINT						NOT NULL	DEFAULT 0					COMMENT '1: PMF is waived; 0: PMF is charged',
						late_payment_fee_amnesty		TIMESTAMP					NULL									COMMENT 'No LPFs will be applied until this date',
						late_notice_amnesty				TIMESTAMP					NULL									COMMENT 'No Late Notices will be sent until this date',
						sample_until					TIMESTAMP					NULL									COMMENT 'Include on the Invoice Sample list until this date',
						credit_control_status_id		BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Credit Control Status',
						automatic_barring_status_id		BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Automatic Barring Status',
						automatic_barring_timestamp		TIMESTAMP					NULL									COMMENT 'Automatic Barring Status Timestamp',
						tio_reference_number			VARCHAR(150)				NULL									COMMENT 'TIO Reference Number',
						is_vip							TINYINT						NOT NULL	DEFAULT 0					COMMENT '1: VIP Account; 0: Standard Account',
						delivery_method_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Delivery Method',
						payment_method_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Payment Method',
						direct_debit_id					BIGINT			UNSIGNED	NULL									COMMENT '(FK) Current Direct Debit details',
						is_telemarketing_blacklisted	TINYINT						NOT NULL	DEFAULT 0					COMMENT '1: Is Blacklisted; 0: Not Blacklisted',
						dealer_person_id				BIGINT			UNSIGNED	NULL									COMMENT '(FK) Dealer who sold this Account',
						account_status_id				BIGINT			UNSIGNED	NOT NULL								COMMENT '(FK) Status of the Account',
						
						CONSTRAINT	pk_account_id							PRIMARY KEY	(id),
						CONSTRAINT	fk_account_customer_group_id			FOREIGN KEY	(customer_group_id)				REFERENCES	CustomerGroup(Id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_account_type_id				FOREIGN KEY	(account_type_id)				REFERENCES	account_type(id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_created_by_person_id			FOREIGN KEY	(created_by_person_id)			REFERENCES	person(id)						ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_credit_control_status_id		FOREIGN KEY	(credit_control_status_id)		REFERENCES	credit_control_status(id)		ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_automatic_barring_status_id	FOREIGN KEY	(automatic_barring_status_id)	REFERENCES	automatic_barring_status(id)	ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_delivery_method_id			FOREIGN KEY	(delivery_method_id)			REFERENCES	delivery_method(id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_payment_method_id			FOREIGN KEY	(payment_method_id)				REFERENCES	payment_method(id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_direct_debit_id				FOREIGN KEY	(direct_debit_id)				REFERENCES	direct_debit(id)				ON UPDATE CASCADE ON DELETE SET NULL,
						CONSTRAINT	fk_account_dealer_person_id				FOREIGN KEY	(dealer_person_id)				REFERENCES	person(id)						ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_account_account_status_id			FOREIGN KEY	(account_status_id)				REFERENCES	account_status(id)				ON UPDATE CASCADE ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE account;";

		// 8:	Add the account_address Table
		$strSQL = "	CREATE TABLE account_address
					(
						id				BIGINT			UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						account_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Account',
						address_id		BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Address',
						address_type_id	BIGINT			UNSIGNED	NOT NULL					COMMENT '(FK) Address Type (eg. Postal, Street)',
						
						CONSTRAINT	pk_account_address_id			PRIMARY KEY	(id),
						CONSTRAINT	fk_account_address_account_id	FOREIGN KEY	(account_id)	REFERENCES account(id)	ON UPDATE CASCADE	ON DELETE CASCADE,
						CONSTRAINT	fk_account_address_address_id	FOREIGN KEY	(permission_id)	REFERENCES address(id)	ON UPDATE CASCADE	ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_address Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE account_address;";
		
		// 9:	Change account_history to accomodate Account changes
		$strSQL =	"ALTER TABLE account_history " .
					"ADD 	person_id					BIGINT		UNSIGNED	NOT NULL	COMMENT '(FK) Person who modified the Account', " .
					"" .
					"CHANGE	billing_method			delivery_method_id			BIGINT	UNSIGNED	NOT NULL	COMMENT '(FK) Delivery Method', " .
					"CHANGE	late_payment_amnesty	late_notice_amnesty			TIMESTAMP			NULL		COMMENT 'No Late Notices will be sent until this date', " .
					"CHANGE disable_ddr				waive_payment_method_fee	TINYINT				NOT NULL	COMMENT '1: PMF is waived; 0: PMF is charged', " .
					"";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the account_address Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE account_address;";
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