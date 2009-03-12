<?php

/**
 * Version 159 of database update.
 * This version: -
 *
 *	1:	Add the payment_method Table
 *	2:	Populate the payment_method Table
 *
 *	3:	Add the direct_debit_type Table
 *	4:	Populate the direct_debit_type Table
 *
 *	5:	Add the direct_debit Table
 *
 *	6:	Add the direct_debit_credit_card Table
 *
 *	7:	Add the direct_debit_bank_account Table
 */

class Flex_Rollout_Version_000159 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the payment_method Table
		$strSQL = "	CREATE TABLE payment_method
					(
						id						BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						name					VARCHAR(255)				NOT NULL								COMMENT 'Name of the Payment Method',
						description				VARCHAR(1024)				NOT NULL								COMMENT 'Description of the Payment Method',
						const_name				VARCHAR(512)				NOT NULL								COMMENT 'Constant Alias of the Payment Method',
						
						CONSTRAINT	pk_payment_method_id	PRIMARY KEY (id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the payment_method Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE payment_method;";

		// 2:	Populate the payment_method Table
		$strSQL = "	INSERT INTO payment_method (name, description, const_name) VALUES 
					('Account'		, 'Account Billing'	, 'PAYMENT_METHOD_ACCOUNT'), 
					('Direct Debit'	, 'Direct Debit'	, 'PAYMENT_METHOD_DIRECT_DEBIT');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the payment_method Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 3:	Add the direct_debit_type Table
		$strSQL = "	CREATE TABLE direct_debit_type
					(
						id						BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						name					VARCHAR(255)				NOT NULL								COMMENT 'Name of the Direct Debit Type',
						description				VARCHAR(1024)				NOT NULL								COMMENT 'Description of the Direct Debit Type',
						const_name				VARCHAR(512)				NOT NULL								COMMENT 'Constant Alias of the Direct Debit Type',
						
						CONSTRAINT	pk_direct_debit_type_id	PRIMARY KEY (id)
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the direct_debit_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE direct_debit_type;";

		// 4:	Populate the direct_debit_type Table
		$strSQL = "	INSERT INTO direct_debit_type (name, description, const_name) VALUES 
					('Account'		, 'Account Billing'	, 'PAYMENT_METHOD_ACCOUNT'), 
					('Direct Debit'	, 'Direct Debit'	, 'PAYMENT_METHOD_DIRECT_DEBIT');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the direct_debit_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 5:	Add the direct_debit Table
		$strSQL = "	CREATE TABLE direct_debit
					(
						id						BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						account_id				BIGINT(20)	UNSIGNED	NOT NULL								COMMENT '(FK) Account this Direct Debit belongs to',
						direct_debit_type_id	BIGINT(20)	UNSIGNED	NOT NULL								COMMENT '(FK) Direct Debit Type (eg. Credit Card, Bank Account)',
						created_employee_id		BIGINT(20)	UNSIGNED	NOT NULL								COMMENT '(FK) Employee who created this Direct Debit',
						created_on				TIMESTAMP				NOT NULL	DEFAULT	CURRENT_TIMESTAMP	COMMENT 'Creation Timestamp',
						dealer_id				BIGINT(20)	UNSIGNED	NULL									COMMENT '(FK) Dealer who obtained the Direct Debit details',
						modified_employee_id	BIGINT(20)	UNSIGNED	NOT NULL								COMMENT '(FK) Employee who last modified this Direct Debit',
						modified_on				TIMESTAMP				NOT NULL	DEFAULT	CURRENT_TIMESTAMP	COMMENT 'Last Modification Timestamp',
						status_id				BIGINT(20)	UNSIGNED	NOT NULL								COMMENT '(FK) Active/Inactive Status of this Direct Debit',
						
						CONSTRAINT	pk_direct_debit_id						PRIMARY KEY (id),
						CONSTRAINT	fk_direct_debit_account_id				FOREIGN KEY (account_id)			REFERENCES Account(Id)				ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT	fk_direct_debit_direct_debit_type_id	FOREIGN KEY (direct_debit_type_id)	REFERENCES direct_debit_type(id)	ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_direct_debit_created_employee_id		FOREIGN KEY (created_employee_id)	REFERENCES Employee(Id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_direct_debit_dealer_id				FOREIGN KEY (dealer_id)				REFERENCES dealer(id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_direct_debit_modified_employee_id	FOREIGN KEY (modified_employee_id)	REFERENCES Employee(Id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_direct_debit_status_id				FOREIGN KEY	(status_id)				REFERENCES status(id)				ON UPDATE CASCADE ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the direct_debit Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE direct_debit;";

		// 6:	Add the direct_debit_credit_card Table
		$strSQL = "	CREATE TABLE direct_debit_credit_card
					(
						id						BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						direct_debit_id			BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) Direct Debit record that this details',
						credit_card_type_id		BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) Credit Card Type',
						card_name				VARCHAR(512)				NOT NULL								COMMENT 'Customer Name on the Credit Card',
						card_number				VARCHAR(24)					NOT NULL								COMMENT 'Credit Card Number',
						expiry_month			TINYINT(2)		UNSIGNED	NOT NULL								COMMENT 'Month in which the Credit Card expires',
						expiry_year				TINYINT(4)		UNSIGNED	NOT NULL								COMMENT 'Year in which the Credit Card expires',
						cvv						VARCHAR(8)					NOT NULL								COMMENT 'Card Verification Value for the Credit Card',
						
						CONSTRAINT	pk_direct_debit_credit_card_id					PRIMARY KEY (id),
						CONSTRAINT	fk_direct_debit_credit_card_direct_debit_id		FOREIGN KEY (direct_debit_id)		REFERENCES direct_debit(id)			ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT	fk_direct_debit_credit_card_credit_card_type_id	FOREIGN KEY (credit_card_type_id)	REFERENCES credit_card_type(id)		ON UPDATE CASCADE ON DELETE RESTRICT
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the direct_debit_credit_card Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE direct_debit_credit_card;";

		// 7:	Add the direct_debit_bank_account Table
		$strSQL = "	CREATE TABLE direct_debit_bank_account
					(
						id						BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
						direct_debit_id			BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) Direct Debit record that this details',
						bank_name				VARCHAR(512)				NOT NULL								COMMENT 'Name of the Bank which holds the Account',
						bank_bsb				CHAR(6)						NOT NULL								COMMENT 'Bank/State/Branch Number',
						account_number			VARCHAR(24)					NOT NULL								COMMENT 'Bank Account Number',
						account_name			VARCHAR(512)				NOT NULL								COMMENT 'Name on the Bank Account',
						
						CONSTRAINT	pk_direct_debit_bank_account_id						PRIMARY KEY (id),
						CONSTRAINT	fk_direct_debit_bank_account_direct_debit_id		FOREIGN KEY (direct_debit_id)		REFERENCES direct_debit(id)			ON UPDATE CASCADE ON DELETE CASCADE
					)
					ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the direct_debit_bank_account Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE direct_debit_bank_account;";
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