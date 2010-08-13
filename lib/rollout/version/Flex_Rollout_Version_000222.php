<?php

/**
 * Version 221 of database update.
 * This version: -
 *
 *	1:	Add table motorpass_business_structure
 *	2:	Populate table motorpass_business_structure  
 *	3:	Add table motorpass_promotion_code
 *	4:	Add table motorpass_address
 *	5:	Add table motorpass_contact
 *	6:	Add table motorpass_card_type
 *	7:	
 *	8:	
 *	9:	
 *	10:
 *	11:	
 *	11:	
 *	12:	
 *	13:	
 *	14:		
 */

class Flex_Rollout_Version_000221 extends Flex_Rollout_Version
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
																	CONSTRAINT pk_motorpass_business_structure_id	PRIMARY KEY (id) )
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_business_structure;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate table motorpass_business_structure",
									'sAlterSQL'			=>	"	INSERT INTO motorpass_business_structure (name, description, system_name, const_name)
																VALUES	('Unlisted Pty Ltd',			'Unlisted Pty Ltd', 			'UNLISTED_PTY_LTD', 		'MOTORPASS_BUSINESS_STRUCTURE_UNLISTED_PTY_LTD'),
																		('Listed Ltd Co', 				'Listed Ltd Co', 				'LISTED_LTD_CO', 			'MOTORPASS_BUSINESS_STRUCTURE_LISTED_LTD_CO'),
																		('Trust', 						'Trust', 						'TRUST', 					'MOTORPASS_BUSINESS_STRUCTURE_TRUST'),
																		('Partnership', 				'Partnership', 					'PARTNERSHIP', 				'MOTORPASS_BUSINESS_STRUCTURE_PARTNERSHIP'),
																		('Sole Trader', 				'Sole Trader', 					'SOLE_TRADER', 				'MOTORPASS_BUSINESS_STRUCTURE_SOLE_TRADER'),
																		('Govt Department', 			'Govt Department', 				'GOVT_DEPARTMENT', 			'MOTORPASS_BUSINESS_STRUCTURE_GOVT_DEPARTMENT'),
																		('Subsidiary of Foreign Co.', 	'Subsidiary of Foreign Co.', 	'SUBSIDIARY_OF_FOREIGN_CO', 'MOTORPASS_BUSINESS_STRUCTURE_SUBSIDIARY_OF_FOREIGN_CO'),
																		('Association', 				'Association', 					'ASSOCIATION', 				'MOTORPASS_BUSINESS_STRUCTURE_ASSOCIATION'),
																		('Trustee', 					'Trustee', 						'TRUSTEE', 					'MOTORPASS_BUSINESS_STRUCTURE_TRUSTEE'),
																		('Trading Subsidiary', 			'Trading Subsidiary', 			'TRADING_SUBSIDIARY', 		'MOTORPASS_BUSINESS_STRUCTURE_TRADING_SUBSIDIARY'),
																		('Non Profit Organisation', 	'Non Profit Organisation', 		'NON_PROFIT_ORGANISATION', 	'MOTORPASS_BUSINESS_STRUCTURE_NON_PROFIT_ORGANISATION'),
																		('Incorporated Body', 			'Incorporated Body', 			'INCORPORATED_BODY', 		'MOTORPASS_BUSINESS_STRUCTURE_INCORPORATED_BODY'),
																		('Other', 						'Other', 						'OTHER', 					'MOTORPASS_BUSINESS_STRUCTURE_OTHER');",
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
																	CONSTRAINT pk_motorpass_promotion_code_id PRIMARY KEY (id) )
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
																    	ON UPDATE CASCADE)
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
																  	drivers_license VARCHAR(20) NULL ,
																  	position VARCHAR(45) NOT NULL ,
																  	landline_number VARCHAR(25) NOT NULL ,
																  	modified DATETIME DEFAULT CURRENT_TIMESTAMP,
																  	modified_employee_id BIGINT UNSIGNED NOT NULL ,
																  	CONSTRAINT pk_motorpass_contact_id PRIMARY KEY (id) ,
																  	CONSTRAINT fk_motorpass_contact_contact_title_id
																   		FOREIGN KEY (contact_title_id )
																    	REFERENCES contact_title (id )
																    	ON DELETE RESTRICT
																    	ON UPDATE CASCADE)
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
																  	CONSTRAINT pk_motorpass_card_type_id PRIMARY KEY (id) )
																;",
									'sRollbackSQL'		=>	"	DROP TABLE motorpass_card_type;",
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