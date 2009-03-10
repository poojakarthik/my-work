<?php

/**
 * Version 152 of database update.
 * This version: -
 *
 *	1:	Add the delivery_method Table
 *	2:	Populate the delivery_method Table
 *	3:	Add the customer_group_delivery_method Table
 *	4:	Populate the customer_group_delivery_method Table
 */

class Flex_Rollout_Version_000152 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the delivery_method Table
		$strSQL =	"CREATE TABLE delivery_method " .
					"(" .
					"	id				BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INREMENT	COMMENT 'Unique Identifier', " .
					"	name			VARCHAR(255)				NOT NULL					COMMENT 'Name of the Delivery Method', " .
					"	description		VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Delivery Method', " .
					"	const_name		VARCHAR(512)				NOT NULL					COMMENT 'Constant Name for this Delivery Method', " .
					"	account_setting	TINYINT(1)					NOT NULL					COMMENT '1: Can be used as an Account setting; 0: System-only setting', " .
					"	" .
					"	CONSTRAINT	pk_delivery_method_id	PRIMARY KEY (id)" .
					") ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the delivery_method Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE delivery_method;";

		// 2:	Populate the delivery_method Table
		$strSQL =	"INSERT INTO delivery_method (id, name, description, const_name, account_setting) VALUES " .
					"(0	, 'Post'		, 'Post'			, 'DELIVERY_METHOD_POST'		, 1), " .
					"(1	, 'Email'		, 'Email'			, 'DELIVERY_METHOD_EMAIL'		, 1), " .
					"(2	, 'Withheld'	, 'Do Not Send'		, 'DELIVERY_METHOD_DO_NOT_SEND'	, 0), " .
					"(3	, 'Email Sent'	, 'Email (Sent)'	, 'DELIVERY_METHOD_EMAIL_SENT'	, 0)";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the delivery_method Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"TRUNCATE TABLE delivery_method;";

		// 3:	Add the customer_group_delivery_method Table
		$strSQL =	"CREATE TABLE customer_group_delivery_method " .
					"(" .
					"	id						BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier', " .
					"	customer_group_id		BIGINT(20)					NOT NULL								COMMENT '(FK) Customer Group', " .
					"	delivery_method_id		BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) Delivery Method', " .
					"	minimum_invoice_value	DECIMAL(13, 4)				NOT NULL								COMMENT 'Minimum Invoice value for this Delivery Method to apply', " .
					"	employee_id				BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) Employee who defined this setting', " .
					"	created_on				TIMESTAMP					NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT 'Creation Timestamp', " .
					"	" .
					"	CONSTRAINT	pk_customer_group_delivery_method_id					PRIMARY KEY (id), " .
					"	CONSTRAINT	fk_customer_group_delivery_method_customer_group_id		FOREIGN KEY (customer_group_id)		REFERENCES CustomerGroup(Id)	ON UPDATE CASCADE ON DELETE CASCADE, " .
					"	CONSTRAINT	fk_customer_group_delivery_method_delivery_method_id	FOREIGN KEY (delivery_method_id)	REFERENCES delivery_method(id)	ON UPDATE CASCADE ON DELETE RESTRICT, " .
					"	CONSTRAINT	fk_customer_group_delivery_method_employee_id			FOREIGN KEY (employee_id)			REFERENCES Employee(Id)			ON UPDATE CASCADE ON DELETE RESTRICT " .
					") ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the customer_group_delivery_method Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DROP TABLE customer_group_delivery_method;";

		// 4:	Populate the customer_group_delivery_method Table
		$arrCustomerGroups	= Customer_Group::getAll();
		$arrDeliveryMethods	= Delivery_Method::getAll();
		foreach ($arrCustomerGroups as $objCustomerGroup)
		{
			foreach ($arrDeliveryMethods as $objDeliveryMethod)
			{
				$strSQL =	"INSERT INTO customer_group_delivery_method (customer_group_id, delivery_method_id, employee_id) VALUES " .
							"({$objCustomerGroup->id}, {$objDeliveryMethod->id}, ".Employee::SYSTEM_EMPLOYEE_ID.")";
				$result = $dbAdmin->query($strSQL);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to populate the customer_group_delivery_method Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
				}
				$this->rollbackSQL[] =	"TRUNCATE TABLE customer_group_delivery_method;";
			}
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