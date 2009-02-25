<?php

/**
 * Version 144 of database update.
 * This version: -
 *	1:	Add the product_type_nature Table
 *	2:	Populate the product_type_nature Table
 *	
 *	3:	Add the product_type Table
 *	4:	Populate the product_type Table
 *	
 *	5:	Add the product_status Table
 *	6:	Populate the product_status Table
 *	
 *	7:	Add the product_sale_priority Table
 *	8:	Populate the product_sale_priority Table
 *	
 *	9:	Add the product Table
 *	
 *	10:	Add the RatePlan.product_id Field
 */

class Flex_Rollout_Version_000144 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the product_type_nature Table
		$strSQL = "	CREATE TABLE product_type_nature
					(
						id			BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Indentifier',
						name		VARCHAR(255)				NOT NULL					COMMENT 'Name of the Product Type Nature',
						description	VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Product Type Nature',
						const_name	VARCHAR(512)				NOT NULL					COMMENT 'Constant Name of the Product Type Nature',
						
						CONSTRAINT	pk_product_type_nature_id	PRIMARY KEY	(id)
					);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the product_type_nature Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE product_type_nature ";
		
		// 2:	Populate the product_type_nature Table
		$strSQL = "	INSERT INTO product_type_nature (name, description, const_name) VALUES 
					('Service'	, 'Service'		, 'PRODUCT_TYPE_NATURE_SERVICE'), 
					('Hardware'	, 'Hardware'	, 'PRODUCT_TYPE_NATURE_HARDWARE');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the product_type_nature Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE product_type_nature ";
		
		// 3:	Add the product_type Table
		$strSQL = "	CREATE TABLE product_type
					(
						id						BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Indentifier',
						name					VARCHAR(255)				NOT NULL					COMMENT 'Name of the Product Type',
						description				VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Product Type',
						const_name				VARCHAR(512)				NOT NULL					COMMENT 'Constant Name of the Product Type',
						product_type_nature_id	BIGINT(20)		UNSIGNED	NOT NULL					COMMENT '(FK) Nature of this Product Type',
						
						CONSTRAINT	pk_product_type__id						PRIMARY KEY	(id),
						CONSTRAINT	fk_product_type_product_type_nature_id	FOREIGN KEY	(product_type_nature_id)	REFERENCES product_type_nature(id)	ON UPDATE CASCADE ON DELETE RESTRICT
					);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the product_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE product_type ";
		
		// 4:	Populate the product_type Table
		$strSQL = "	INSERT INTO product_type (name, description, const_name, product_type_nature_id) VALUES 
					('Landline'	, 'Landline'			,	'PRODUCT_TYPE_LANDLINE'	, (SELECT id FROM product_type_nature WHERE const_name = 'PRODUCT_TYPE_NATURE_SERVICE' LIMIT 1)), 
					('ADSL'		, 'ADSL'				,	'PRODUCT_TYPE_ADSL'		, (SELECT id FROM product_type_nature WHERE const_name = 'PRODUCT_TYPE_NATURE_SERVICE' LIMIT 1)), 
					('Wireless'	, 'Wireless Broadband'	,	'PRODUCT_TYPE_WIRELESS'	, (SELECT id FROM product_type_nature WHERE const_name = 'PRODUCT_TYPE_NATURE_SERVICE' LIMIT 1)), 
					('Mobile'	, 'Mobile'				,	'PRODUCT_TYPE_MOBILE'	, (SELECT id FROM product_type_nature WHERE const_name = 'PRODUCT_TYPE_NATURE_SERVICE' LIMIT 1)), 
					('Inbound'	, 'Inbound'				,	'PRODUCT_TYPE_INBOUND'	, (SELECT id FROM product_type_nature WHERE const_name = 'PRODUCT_TYPE_NATURE_SERVICE' LIMIT 1));";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the product_type Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE product_type ";
		
		// 5:	Add the product_status Table
		$strSQL = "	CREATE TABLE product_status
					(
						id			BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Indentifier',
						name		VARCHAR(255)				NOT NULL					COMMENT 'Name of the Product Status',
						description	VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Product Status',
						const_name	VARCHAR(512)				NOT NULL					COMMENT 'Constant Name of the Product Status',
						
						CONSTRAINT	pk_product_status_id	PRIMARY KEY	(id)
					);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the product_status Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE product_status ";
		
		// 6:	Populate the product_status Table
		$strSQL = "	INSERT INTO product_status (name, description, const_name) VALUES 
					('Draft'	, 'Draft'		, 'PRODUCT_STATUS_DRAFT'), 
					('Active'	, 'Active'		, 'PRODUCT_STATUS_ACTIVE'),
					('Inactive'	, 'Inactive'	, 'PRODUCT_STATUS_INACTIVE');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the product_status Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE product_status ";
		
		// 7:	Add the product_sale_priority Table
		$strSQL = "	CREATE TABLE product_sale_priority
					(
						id			BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Indentifier',
						name		VARCHAR(255)				NOT NULL					COMMENT 'Name of the Product Priority',
						description	VARCHAR(1024)				NOT NULL					COMMENT 'Description of the Product Priority',
						const_name	VARCHAR(512)				NOT NULL					COMMENT 'Constant Name of the Product Priority',
						
						CONSTRAINT	pk_product_priority_id	PRIMARY KEY	(id)
					);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the product_sale_priority Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE product_sale_priority ";
		
		// 8:	Populate the product_sale_priority Table
		$strSQL = "	INSERT INTO product_sale_priority (name, description, const_name) VALUES 
					('Active'	, 'Actively Sold'		, 'PRODUCT_SALE_PRIORITY_ACTIVE'), 
					('Passive'	, 'Passively Sold'		, 'PRODUCT_SALE_PRIORITY_PASSIVE');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the product_sale_priority Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE product_sale_priority ";
		
		// 9:	Add the product Table
		$strSQL = "	CREATE TABLE product
					(
						id							BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Indentifier',
						name						VARCHAR(255)				NOT NULL								COMMENT 'Name of the Product',
						description					VARCHAR(1024)				NULL									COMMENT 'Description of the Product',
						product_type_id				BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) Type of the Product',
						customer_group_id			BIGINT(20)					NULL									COMMENT '(FK) Customer Group this Product belongs to',
						employee_id					BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) The Employee who created the Product',
						created_on					TIMESTAMP					NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT 'Creation timestamp for the Product',
						product_sale_priority_id	BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) How actively the Product is being sold',
						product_status_id			BIGINT(20)		UNSIGNED	NOT NULL								COMMENT '(FK) The Status of the Product',
						
						CONSTRAINT	pk_product_id						PRIMARY KEY	(id),
						CONSTRAINT	fk_product_product_type_id			FOREIGN KEY	(product_type_id)			REFERENCES product_type(id)				ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_product_customer_group_id		FOREIGN KEY	(customer_group_id)			REFERENCES CustomerGroup(Id)			ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_product_employee_id				FOREIGN KEY	(employee_id)				REFERENCES Employee(Id)					ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_product_product_sale_priority_id	FOREIGN KEY	(product_sale_priority_id)	REFERENCES product_sale_priority(id)	ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT	fk_product_product_status_id		FOREIGN KEY	(product_status_id)			REFERENCES product_status(id)			ON UPDATE CASCADE ON DELETE RESTRICT
					);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the product Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DROP TABLE product ";
		
		// 10:	Add the RatePlan.product_id Field
		$strSQL = "	ALTER TABLE RatePlan
					ADD product_id		BIGINT(20)		UNSIGNED	NULL				COMMENT '(FK) Product that this defines',
					
					ADD CONSTRAINT	fk_rate_plan_product_id	FOREIGN KEY (product_id)	REFERENCES product(id)	ON UPDATE CASCADE ON DELETE SET NULL;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the RatePlan.product_id Field. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE RatePlan " .
								"DROP FOREIGN KEY fk_rate_plan_product_id, " .
								"DROP product_id;";
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