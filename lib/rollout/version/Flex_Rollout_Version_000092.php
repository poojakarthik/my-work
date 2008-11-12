<?php

/**
 * Version 92 (Ninety-Two) of database update.
 * This version: -
 *	1:	Adds the dealer_status table
 *	2:	Populates the dealer_status table
 *	3:	Adds country table
 *	4:	Populates the country table
 *	5:	Adds state table
 *	6:	Populates the state table
 *	7:	Adds contact_title table
 *	8:	Populates the contact_title table
 *	9:	Modifies the dealer table so that it matches that of the sales database's dealer table
 *	10:	Creates the dealer_customer_group table
 *	11: Creates the dealer_rate_plan table
 *	12: Creates the sale_type table
 *	13: Populates the sale_type table
 *	14: Creates the dealer_sale_type table
 *	15: defines the System dealer, in the dealer table (points to system employee)
 */

class Flex_Rollout_Version_000092 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1: Add dealer_status table
		$strSQL = "	CREATE TABLE dealer_status
					(
						id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Unique Id for this record',
						name VARCHAR(255) NOT NULL COMMENT 'Unique name for the dealer status',
						description VARCHAR(255) NOT NULL COMMENT 'Description of the dealer status',
					
						CONSTRAINT pk_dealer_status PRIMARY KEY (id),
						CONSTRAINT un_dealer_status_name UNIQUE (name)
					) ENGINE = innodb COMMENT = 'Defines dealer statuses';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create dealer_status table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE dealer_status;");
		
		
		// 2: Populate the dealer_status table
		$strSQL = "	INSERT INTO dealer_status (id, name, description)
					VALUES
					(1, 'Active', 'Active'),
					(2, 'Inactive', 'Inactive');";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to populate dealer_status table - ". $objResult->getMessage());
		}
		// No rollback is required
		
		// 3: Add country table
		$strSQL = "	CREATE TABLE country
					(
						id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Unique Id for this record',
						name VARCHAR(255) NOT NULL COMMENT 'Unique name of the country',
						description VARCHAR(255) NOT NULL COMMENT 'Description of the country',
						code VARCHAR(255) NOT NULL COMMENT 'Abbreviation of the country''s name',
					
						CONSTRAINT pk_country PRIMARY KEY (id),
						CONSTRAINT un_country_name UNIQUE (name)
					) ENGINE = innodb COMMENT = 'Defines various countries';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create country table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE country;");
		
		
		// 4: Populate the country table
		$strSQL = "	INSERT INTO country (id, name, description, code)
					VALUES
					(1, 'Australia', 'Australia', 'AUS'),
					(2, 'India', 'India', 'IND');";

		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to populate country table - ". $objResult->getMessage());
		}
		// No rollback is required

		// 5: Add state table
		$strSQL = "	CREATE TABLE state
					(
						id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Unique Id for this record',
						name VARCHAR(255) NOT NULL COMMENT 'Name of the state',
						description VARCHAR(255) NOT NULL COMMENT 'Description of the state',
						country_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into country table, defining the country that the state belongs to',
						code VARCHAR(255) NOT NULL COMMENT 'Abbreviation of the state''s name', 
					
						CONSTRAINT pk_state PRIMARY KEY (id),
						CONSTRAINT un_country_id_name UNIQUE (country_id, name),
						CONSTRAINT fk_state_country_id FOREIGN KEY (country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE = innodb COMMENT = 'Geographical States';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create state table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE state;");
		
		// 6: Populate the state table
		$strSQL = "	INSERT INTO state (id, name, description, country_id, code)
					VALUES
					(1, 'Australian Capital Territory', 'Australian Capital Territory', 1, 'ACT'),
					(2, 'New South Wales', 'New South Wales', 1, 'NSW'),
					(3, 'Northern Territory', 'Northern Territory', 1, 'NT'),
					(4, 'Queensland', 'Queensland', 1, 'QLD'),
					(5, 'South Australia', 'South Australia', 1, 'SA'),
					(6, 'Tasmania', 'Tasmania', 1, 'TAS'),
					(7, 'Victoria', 'Victoria', 1, 'VIC'),
					(8, 'Western Australia', 'Western Australia', 1, 'WA');";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to populate state table - ". $objResult->getMessage());
		}
		// No rollback is required

		// 7: Add contact_title table
		$strSQL = "	CREATE TABLE contact_title
					(
						id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Unique Id for this record',
						name VARCHAR(255) NOT NULL COMMENT 'Unique title',
						description VARCHAR(255) NOT NULL COMMENT 'Description',
					
						CONSTRAINT pk_contact_title PRIMARY KEY (id),
						CONSTRAINT un_contact_title_name UNIQUE (name)
					) ENGINE = innodb COMMENT = 'Defines contact titles';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create contact_title table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE state;");
		
		// 8: Populate the contact_title table
		$strSQL = "	INSERT INTO contact_title (id, name, description)
					VALUES
					(1, 'Dr', 'Doctor'),
					(2, 'Mr', 'Mister'),
					(3, 'Mrs', 'Missus'),
					(4, 'Mstr', 'Master'),
					(5, 'Miss', 'Miss'),
					(6, 'Ms', 'Ms'),
					(7, 'Esq', 'Esquire'),
					(8, 'Prof', 'Professor');";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to populate contact_title table - ". $objResult->getMessage());
		}
		// No rollback is required

		// Delete the old dealer table (The structure has changed substantially)
		$strSQL = "DROP TABLE IF EXISTS dealer";
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to drop dealer table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "	CREATE TABLE dealer
															(
																id bigint(20) unsigned NOT NULL auto_increment,
																upline_id bigint(20) unsigned NOT NULL default '0',
																first_name varchar(255) character set utf8 NOT NULL,
																last_name varchar(255) character set utf8 NOT NULL,
																title varchar(4) character set utf8 NOT NULL,
																business_name varchar(255) character set utf8 NOT NULL,
																trading_name varchar(255) character set utf8 NOT NULL,
																abn char(11) character set utf8 NOT NULL,
																abn_registered tinyint(1) unsigned NOT NULL,
																address_1 varchar(255) character set utf8 NOT NULL,
																address_2 varchar(255) character set utf8 NOT NULL,
																suburb varchar(255) character set utf8 NOT NULL,
																state char(3) character set utf8 NOT NULL,
																post_code varchar(10) character set utf8 NOT NULL,
																postal_address1 varchar(255) character set utf8 NOT NULL,
																postal_address2 varchar(255) character set utf8 NOT NULL,
																postal_suburb varchar(255) character set utf8 NOT NULL,
																postal_state char(3) character set utf8 NOT NULL,
																postal_postcode varchar(10) character set utf8 NOT NULL,
																phone varchar(25) character set utf8 NOT NULL,
																mobile varchar(25) character set utf8 NOT NULL,
																fax varchar(25) character set utf8 NOT NULL,
																email varchar(255) character set utf8 NOT NULL,
																commission_scale bigint(20) unsigned NOT NULL,
																royalty_scale bigint(20) unsigned NOT NULL,
																bsb char(9) character set utf8 NOT NULL,
																account_number char(9) character set utf8 NOT NULL,
																account_name varchar(255) character set utf8 NOT NULL,
																gst_registered tinyint(1) unsigned NOT NULL,
																termination_date date NOT NULL,
																PRIMARY KEY  (id),
																KEY upline_id (upline_id,commission_scale,royalty_scale)
															) ENGINE=InnoDB  DEFAULT CHARSET=latin1;"
									);
		

		// 9: Create new dealer table
		$strSQL = "	CREATE TABLE dealer
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
						up_line_id BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'FK into the dealer table, defining the direct ''up line'' manager of the dealer',
						username VARCHAR(255) NOT NULL,
						password VARCHAR(255) NOT NULL,
						can_verify TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 = dealer can verify sales other than those made by dealers under their management; 0 = dealer can only verify sales made by dealers under their management',
						first_name VARCHAR(255) NOT NULL,
						last_name VARCHAR(255) NOT NULL,
						title_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into the contact_title table, defining the title/salutation used by the dealer',
						business_name VARCHAR(255) NULL,
						trading_name VARCHAR(255) NULL,
						abn CHARACTER(11) NULL,
						abn_registered TINYINT(1) UNSIGNED NULL,
						address_line_1 VARCHAR(255) NULL,
						address_line_2 VARCHAR(255) NULL,
						suburb VARCHAR(255) NULL,
						state_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into the state table, defining the state in which the dealer is primarily located',
						country_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into the country table, defining the country in which the dealer is primarily located',
						postcode VARCHAR(255) NULL,
						postal_address_line_1 VARCHAR(255) NULL,
						postal_address_line_2 VARCHAR(255) NULL,
						postal_suburb VARCHAR(255) NULL,
						postal_state_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into the state table, defining the state used by the postal address of the dealer',
						postal_country_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into the country table, defining the country used by the postal address of the dealer',
						postal_postcode VARCHAR(255) NULL,
						phone VARCHAR(255) NULL,
						mobile VARCHAR(255) NULL,
						fax VARCHAR(255) NULL,
						email VARCHAR(255) NULL,
						commission_scale BIGINT(20) UNSIGNED NULL,
						royalty_scale BIGINT(20) UNSIGNED NULL,
						bank_account_bsb CHARACTER(6) NULL,
						bank_account_number VARCHAR(255) NULL,
						bank_account_name VARCHAR(255) NULL,
						gst_registered TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 = YES, 0 = NO',
						termination_date DATE NULL,
						dealer_status_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into the dealer_status table, defininng the current status of the dealer',
						created_on TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time at which the dealer record was created',
						employee_id BIGINT(20) UNSIGNED NULL COMMENT 'FK into Employee table',
					
						CONSTRAINT pk_dealer PRIMARY KEY (id),
						CONSTRAINT fk_dealer_title_id_contact_title_id FOREIGN KEY (title_id) REFERENCES contact_title(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_dealer_state_id FOREIGN KEY (state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_dealer_country_id FOREIGN KEY (country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_dealer_postal_state_id_state_id FOREIGN KEY (postal_state_id) REFERENCES state(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_dealer_postal_country_id_country_id FOREIGN KEY (postal_country_id) REFERENCES country(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_dealer_dealer_status_id FOREIGN KEY (dealer_status_id) REFERENCES dealer_status(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_dealer_up_line_id_dealer_id FOREIGN KEY (up_line_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_dealer_employee_id_Employee_Id FOREIGN KEY (employee_id) REFERENCES Employee(Id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE = innodb COMMENT = 'Defines dealers';
					";
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create dealer table - ". $objResult->getMessage());
		}
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE dealer;");
										
 		
 		// 10: Create the dealer_customer_group table
		$strSQL = "	CREATE TABLE dealer_customer_group
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
						dealer_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into dealer table',
						customer_group_id BIGINT(20) NOT NULL COMMENT 'FK into CustomerGroup table',
					
						CONSTRAINT pk_dealer_customer_group PRIMARY KEY (id),
						CONSTRAINT un_dealer_customer_group_dealer_id_customer_group_id UNIQUE (dealer_id, customer_group_id),
						CONSTRAINT fk_dealer_customer_group_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_dealer_customer_group_customer_group_id FOREIGN KEY (customer_group_id) REFERENCES CustomerGroup(Id) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE = innodb COMMENT = 'dealer - customer group relationships';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create dealer_customer_group table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE dealer_customer_group;");
 
 		// 11: Create the dealer_rate_plan table
		$strSQL = "	CREATE TABLE dealer_rate_plan
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
						dealer_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into dealer table',
						rate_plan_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into RatePlan table',
					
						CONSTRAINT pk_dealer_rate_plan PRIMARY KEY (id),
						CONSTRAINT un_dealer_rate_plan_dealer_id_rate_plan_id UNIQUE (dealer_id, rate_plan_id),
						CONSTRAINT fk_dealer_rate_plan_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_dealer_rate_plan_rate_plan_id FOREIGN KEY (rate_plan_id) REFERENCES RatePlan(Id) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE = innodb COMMENT = 'dealer - rate plan relationships';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create dealer_rate_plan table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE dealer_rate_plan;");

		// 12: Create the sale_type table
		$strSQL = "	CREATE TABLE sale_type
					(
						id BIGINT(20) UNSIGNED NOT NULL COMMENT 'Unique Id for this record',
						name VARCHAR(255) NOT NULL COMMENT 'Unique name for the sale type',
						description VARCHAR(255) NOT NULL COMMENT 'Description of the sale type',
					
						CONSTRAINT pk_sale_type PRIMARY KEY (id),
						CONSTRAINT un_sale_type_name UNIQUE (name)
					) ENGINE = innodb COMMENT = 'Defines sale types';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create sale_type table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE sale_type;");

		// 13: Populate the sale_type table
		$strSQL = "	INSERT INTO sale_type (id, name, description)
					VALUES
					(1, 'New Customer', 'New Customer'),
					(2, 'Existing Customer', 'Existing Customer'),
					(3, 'Win Back', 'Win Back');";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to populate sale_type table - ". $objResult->getMessage());
		}
		// No rollback is required

 		// 14: Create the dealer_sale_type table
		$strSQL = "	CREATE TABLE dealer_sale_type
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for this record',
						dealer_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into dealer table',
						sale_type_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into sale_type table',
					
						CONSTRAINT pk_dealer_sale_type PRIMARY KEY (id),
						CONSTRAINT un_dealer_sale_type_dealer_id_sale_type_id UNIQUE (dealer_id, sale_type_id),
						CONSTRAINT fk_dealer_sale_type_dealer_id FOREIGN KEY (dealer_id) REFERENCES dealer(id) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_dealer_sale_type_sale_type_id FOREIGN KEY (sale_type_id) REFERENCES sale_type(id) ON UPDATE CASCADE ON DELETE CASCADE
					) ENGINE = innodb COMMENT = 'dealer - sale type relationships';";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to create dealer_sale_type table - ". $objResult->getMessage());
		}
		
		$this->rollbackSQL[] = array(	"Database"	=> FLEX_DATABASE_CONNECTION_ADMIN,
										"SQL"		=> "DROP TABLE dealer_sale_type;");

		// 15: define the System dealer, in the dealer table (points to system employee)
		$strSQL = "	INSERT INTO dealer (id, first_name, last_name, username, password, can_verify, dealer_status_id, employee_id)
					VALUES
					(1, 'System', '', 'system', '', 1, 1, 0);";
		
		$objResult = $dbAdmin->query($strSQL);
		if (PEAR::isError($objResult))
		{
			throw new Exception(__CLASS__ . " Failed to add the System dealer to the dealer table - ". $objResult->getMessage());
		}
		// No rollback is required

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
