<?php

/**
 * Version 222 of database update.
 * This version: -
 *
 *	1:	Add table motorpass_business_structure
 *	2:	Populate table motorpass_business_structure
 *	3:	Add table motorpass_promotion_code
 *	4:	Add table motorpass_address
 *	5:	Add table motorpass_contact
 *	6:	Add table motorpass_card_type
 *	7:  Populate table motorpass_card_type
 *	8:  Add table motorpass_card
 *	9:  Add table motorpass_account
 *	10: Add table motorpass_trade_reference
 *
 */

class Flex_Rollout_Version_000222 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table motorpass_business_structure",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_business_structure (
																	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																	name VARCHAR(128) NOT NULL ,
																	description VARCHAR(128) NOT NULL ,
																	system_name VARCHAR(128) NOT NULL ,
																	const_name VARCHAR(128) NOT NULL ,
																	code_numeric INT NOT NULL,
																	CONSTRAINT pk_motorpass_business_structure_id	PRIMARY KEY (id) ) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_business_structure;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate table motorpass_business_structure",
									'sAlterSQL'			=>	"	INSERT INTO motorpass_business_structure (name, description, system_name, const_name, code_numeric)
																VALUES	('Unlisted Pty Ltd',			'Unlisted Pty Ltd', 			'UNLISTED_PTY_LTD', 		'MOTORPASS_BUSINESS_STRUCTURE_UNLISTED_PTY_LTD'			, 1),
																		('Listed Ltd Co', 				'Listed Ltd Co', 				'LISTED_LTD_CO', 			'MOTORPASS_BUSINESS_STRUCTURE_LISTED_LTD_CO'			, 2),
																		('Trust', 						'Trust', 						'TRUST', 					'MOTORPASS_BUSINESS_STRUCTURE_TRUST'					, 5),
																		('Partnership', 				'Partnership', 					'PARTNERSHIP', 				'MOTORPASS_BUSINESS_STRUCTURE_PARTNERSHIP'				, 6),
																		('Sole Trader', 				'Sole Trader', 					'SOLE_TRADER', 				'MOTORPASS_BUSINESS_STRUCTURE_SOLE_TRADER'				, 7),
																		('Govt Department', 			'Govt Department', 				'GOVT_DEPARTMENT', 			'MOTORPASS_BUSINESS_STRUCTURE_GOVT_DEPARTMENT'			, 8),
																		('Subsidiary of Foreign Co.', 	'Subsidiary of Foreign Co.', 	'SUBSIDIARY_OF_FOREIGN_CO', 'MOTORPASS_BUSINESS_STRUCTURE_SUBSIDIARY_OF_FOREIGN_CO'	, 9),
																		('Association', 				'Association', 					'ASSOCIATION', 				'MOTORPASS_BUSINESS_STRUCTURE_ASSOCIATION'				, 10),
																		('Trustee', 					'Trustee', 						'TRUSTEE', 					'MOTORPASS_BUSINESS_STRUCTURE_TRUSTEE'					, 11),
																		('Trading Subsidiary', 			'Trading Subsidiary', 			'TRADING_SUBSIDIARY', 		'MOTORPASS_BUSINESS_STRUCTURE_TRADING_SUBSIDIARY'		, 12),
																		('Non Profit Organisation', 	'Non Profit Organisation', 		'NON_PROFIT_ORGANISATION', 	'MOTORPASS_BUSINESS_STRUCTURE_NON_PROFIT_ORGANISATION'	, 13),
																		('Incorporated Body', 			'Incorporated Body', 			'INCORPORATED_BODY', 		'MOTORPASS_BUSINESS_STRUCTURE_INCORPORATED_BODY'		, 14),
																		('Other', 						'Other', 						'OTHER', 					'MOTORPASS_BUSINESS_STRUCTURE_OTHER'					, 15);",
									'sRollbackSQL'		=>	"	TRUNCATE motorpass_business_structure;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table motorpass_promotion_code",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_promotion_code (
																	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																	name VARCHAR(128) NOT NULL ,
																 	description VARCHAR(128) NOT NULL ,
																	status_id BIGINT UNSIGNED DEFAULT 1 NOT NULL,
																	CONSTRAINT	pk_motorpass_promotion_code_id
																				PRIMARY KEY (id),
																	CONSTRAINT	fk_motorpass_promotion_code_status_id
																   				FOREIGN KEY (status_id)
																    			REFERENCES status (id)
																    			ON DELETE RESTRICT
																    			ON UPDATE CASCADE
																) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_promotion_code;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table motorpass_address",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_address (
																	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																	line_1 VARCHAR(45) NOT NULL,
																	line_2 VARCHAR(45) NULL,
																  	suburb VARCHAR(45) NOT NULL,
																  	state_id BIGINT UNSIGNED NOT NULL  ,
																  	postcode VARCHAR(4) NOT NULL  ,
																  	CONSTRAINT pk_motorpass_address_id PRIMARY KEY (id) ,
																 	CONSTRAINT fk_motorpass_address_state_id
																    	FOREIGN KEY (state_id )
																    	REFERENCES state (id )
																   		ON DELETE RESTRICT
																    	ON UPDATE CASCADE) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_address;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table motorpass_contact",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_contact (
																	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																  	contact_title_id BIGINT UNSIGNED NULL ,
																  	first_name VARCHAR(45) NOT NULL ,
																  	last_name VARCHAR(45) NOT NULL ,
																  	dob  DATE NOT NULL ,
																  	drivers_licence VARCHAR(20) NULL ,
																  	position VARCHAR(45) NOT NULL ,
																  	landline_number VARCHAR(25) NOT NULL ,
																  	modified TIMESTAMP DEFAULT NOW(),
																  	modified_employee_id BIGINT UNSIGNED NOT NULL ,
																  	CONSTRAINT pk_motorpass_contact_id PRIMARY KEY (id) ,
																  	CONSTRAINT fk_motorpass_contact_contact_title_id
																   		FOREIGN KEY (contact_title_id )
																    	REFERENCES contact_title (id )
																    	ON DELETE RESTRICT
																    	ON UPDATE CASCADE) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_contact;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table motorpass_card_type",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_card_type (
																  	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																  	name VARCHAR(128) NOT NULL ,
																  	description VARCHAR(128) NOT NULL ,
																  	system_name VARCHAR(128) NOT NULL ,
																  	const_name VARCHAR(128) NOT NULL ,
																  	CONSTRAINT pk_motorpass_card_type_id PRIMARY KEY (id) ) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_card_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate table motorpass_card_type",
									'sAlterSQL'			=>	"	INSERT INTO motorpass_card_type (name, description, system_name, const_name)
																VALUES	('All Products', 			'All Products', 		'ALL_PRODUCTS',			'MOTORPASS_CARD_TYPE_ALL_PRODUCTS'),
																		('Fuel Only', 				'Fuel Only', 			'FUEL_ONLY'	,			'MOTORPASS_CARD_TYPE_FUEL_ONLY'),
																		('Fuel And Oil Only', 		'Fuel And Oil Only', 	'FUEL_AND_OIL_ONLY',		'MOTORPASS_CARD_TYPE_FUEL_AND_OIL_ONLY'),
																		('All Vehicle Expenses', 	'All Vehicle Expenses',	'ALL_VEHICLE_EXPENSES',	'MOTORPASS_CARD_TYPE_ALL_VEHICLE_EXPENSES'),
																		('Other', 					'Other', 				'OTHER'	,				'MOTORPASS_CARD_TYPE_OTHER')
																;",
									'sRollbackSQL'		=>	"	TRUNCATE TABLE motorpass_card_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table motorpass_card",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_card (
																  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																  holder_contact_title_id BIGINT UNSIGNED NULL ,
																  holder_first_name VARCHAR(100) NULL ,
																  holder_last_name VARCHAR(100) NULL ,
																  shared INT NOT NULL ,
																  vehicle_model VARCHAR(45) NULL,
																  vehicle_rego VARCHAR(10) NULL ,
																  vehicle_make VARCHAR(45) NULL ,
																  motorpass_card_type_id BIGINT UNSIGNED NOT NULL ,
																  card_type_description VARCHAR(128) NOT NULL ,
																  card_expiry_date DATE NOT NULL,
																  modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
																  modified_employee_id BIGINT UNSIGNED NOT NULL ,
																  CONSTRAINT pk_motorpass_card_id PRIMARY KEY (id) ,
																  CONSTRAINT fk_motorpass_card_motorpass_card_type_id
																    FOREIGN KEY (motorpass_card_type_id )
																    REFERENCES motorpass_card_type (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE,
																  CONSTRAINT fk_card_holder_contact_title_id
																    FOREIGN KEY (holder_contact_title_id )
																    REFERENCES contact_title (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_card;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table motorpass_account",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_account (
																  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																  account_number INT(9) UNSIGNED NOT NULL,
																  account_name VARCHAR(256) NULL,
																  motorpass_promotion_code_id BIGINT UNSIGNED NOT NULL ,
																  business_commencement_date DATE NOT NULL ,
																  motorpass_business_structure_id BIGINT UNSIGNED NOT NULL ,
																  business_structure_description VARCHAR(128) NOT NULL ,
																  email_address VARCHAR(100) NULL ,
  																  email_invoice INTEGER NOT NULL ,
																  street_address_id BIGINT UNSIGNED NOT NULL ,
																  postal_address_id BIGINT UNSIGNED NULL ,
																  motorpass_contact_id BIGINT UNSIGNED NOT NULL ,
																  motorpass_card_id BIGINT UNSIGNED NOT NULL ,
																  modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
																  modified_employee_id BIGINT UNSIGNED NOT NULL ,
																  CONSTRAINT pk_motorpass_account_id PRIMARY KEY (id) ,
																  CONSTRAINT fk_motorpass_account_motorpass_promotion_code_id
																    FOREIGN KEY (motorpass_promotion_code_id )
																    REFERENCES motorpass_promotion_code (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE,
																  CONSTRAINT fk_motorpass_account_motorpass_business_structure_id
																    FOREIGN KEY (motorpass_business_structure_id )
																    REFERENCES motorpass_business_structure (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE,
																  CONSTRAINT fk_motorpass_account_street_address_id
																    FOREIGN KEY (street_address_id )
																    REFERENCES motorpass_address (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE,
																  CONSTRAINT fk_motorpass_account_postal_address_id
																   FOREIGN KEY (postal_address_id )
																    REFERENCES motorpass_address (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE,
																  CONSTRAINT fk_motorpass_account_motorpass_contact_id
																    FOREIGN KEY (motorpass_contact_id )
																    REFERENCES motorpass_contact (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE,
																  CONSTRAINT fk_motorpass_account_motorpass_card_id
																    FOREIGN KEY (motorpass_card_id )
																    REFERENCES motorpass_card (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_account;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table motorpass_trade_reference",
									'sAlterSQL'			=>	"	CREATE  TABLE motorpass_trade_reference (
																  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																  motorpass_account_id BIGINT UNSIGNED NOT NULL ,
																  company_name VARCHAR(100) NOT NULL ,
																  contact_person VARCHAR(100) NOT NULL ,
																  phone_number VARCHAR(25) NOT NULL ,
																  status_id BIGINT UNSIGNED DEFAULT 1 NOT NULL,
																  created TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
																  created_employee_id BIGINT UNSIGNED NOT NULL ,
																  CONSTRAINT pk_motorpass_trade_reference_id PRIMARY KEY (id),
																    CONSTRAINT fk_motorpass_trade_reference_motorpass_sale_id
																    FOREIGN KEY (motorpass_account_id )
																    REFERENCES motorpass_account (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE,
																    CONSTRAINT fk_motorpass_trade_reference_status_id
																   FOREIGN KEY (status_id )
																    REFERENCES status (id )
																    ON DELETE RESTRICT
																    ON UPDATE CASCADE  ) ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_trade_reference;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),

							array
								(
									'sDescription'		=>	"alter table rebill_motorpass",
									'sAlterSQL'			=>	"ALTER TABLE		rebill_motorpass
															ADD COLUMN		motorpass_account_id 	BIGINT UNSIGNED ;	",
									'sRollbackSQL'		=>	"	ALTER TABLE		rebill_motorpass
															DROP COLUMN		motorpass_account_id ;	",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
							);

		// Perform Batch Rollout
		$iRolloutVersionNumber	= self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation)
		{
			$iStepNumber++;

			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");

			// Attempt to apply changes
			$oResult	= Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (PEAR::isError($oResult))
			{
				throw new Exception(__CLASS__ . " Failed to {$aOperation['sDescription']}. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
			}

			// Append to Rollback Scripts (if one or more are provided)
			if (array_key_exists('sRollbackSQL', $aOperation))
			{
				$aRollbackSQL	= (is_array($aOperation['sRollbackSQL'])) ? $aOperation['sRollbackSQL'] : array($aOperation['sRollbackSQL']);

				foreach ($aRollbackSQL as $sRollbackQuery)
				{
					if (trim($sRollbackQuery))
					{
						$this->rollbackSQL[] =	$sRollbackQuery;
					}
				}
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