<?php

/**
 * Version 207 of database update.
 * This version: -
 *
 *	1:	Add the 'Rebill' Payment Method
 *	2:	Create the rebill_type Table
 *	3:	Create the rebill Table
 *	4:	Add the 'Motorpass' Rebill Type
 *	5:	Create the rebill_motorpass Table
 *
 *	6:	Create the customer_group_payment_method Table
 *	7:	Popuplate the customer_group_payment_method Table
 *
 *	8:	Create the customer_group_rebill_type Table
 *
 *	9:	Add the 'Rebill Payout' Payment Type
 *	10:	Add the Payment.surcharge_charge_id Field
 *	11:	Link up existing Payments to Surcharges
 *
 */

class Flex_Rollout_Version_000207 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the 'Rebill' Payment Method",
									'sAlterSQL'			=>	"	INSERT INTO	payment_method
																	(name, description, const_name)
																VALUES
																	('Rebill'	, 'Rebill'	, 'PAYMENT_METHOD_REBILL');",
									'sRollbackSQL'		=>	array
															(
																"	DELETE FROM payment_method WHERE const_name = 'PAYMENT_METHOD_REBILL'; ALTER TABLE payment_method AUTO_INCREMENT = 1;"
															),
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the rebill_type Table",
									'sAlterSQL'			=>	"	CREATE TABLE	rebill_type
																(
																	id					INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	name				BIGINT				NOT NULL					COMMENT 'Name of the Rebill Type',
																	description			BIGINT	UNSIGNED	NOT NULL					COMMENT 'Description of the Rebill Type',
																	const_name			BIGINT	UNSIGNED	NOT NULL					COMMENT 'Constant Alias of the Rebill Type',
																	system_name			BIGINT	UNSIGNED	NOT NULL					COMMENT 'System Name of the Rebill Type',
																	
																	CONSTRAINT	pk_rebill_type_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	rebill_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the rebill Table",
									'sAlterSQL'			=>	"	CREATE TABLE	rebill
																(
																	id					BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
																	account_id			BIGINT	UNSIGNED	NOT NULL								COMMENT '(FK) Account',
																	rebill_type_id		INTEGER	UNSIGNED	NOT NULL								COMMENT '(FK) Rebill Type',
																	created_employee_id	BIGINT	UNSIGNED	NOT NULL								COMMENT '(FK) Employee who created the Rebill definition',
																	created_timestamp	TIMESTAMP			NOT NULL	DEFAULT	CURRENT_TIMESTAMP	COMMENT 'Timestamp the Rebill definition was created',
																	
																	CONSTRAINT	pk_rebill_id					PRIMARY KEY	(id),
																	CONSTRAINT	fk_rebill_account_id			FOREIGN KEY	(account_id)			REFERENCES	Account(Id)		ON UPDATE CASCADE	ON DELETE CASCADE,
																	CONSTRAINT	fk_rebill_rebill_type_id		FOREIGN KEY	(rebill_type_id)		REFERENCES	rebill_type(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_rebill_applied_employee_id	FOREIGN	KEY	(applied_employee_id)	REFERENCES	Employee(Id)	ON UPDATE CASCADE	ON DELETE CASCADE
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	rebill;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the 'Motorpass' Rebill Type",
									'sAlterSQL'			=>	"	INSERT INTO	rebill_type
																	(name			, description		, const_name				, system_name)
																VALUES
																	('Motorpass'	, 'ReD Motorpass'	, 'REBILL_TYPE_MOTORPASS'	, 'MOTORPASS');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the customer_group_payment_method Table",
									'sAlterSQL'			=>	"	CREATE TABLE	customer_group_payment_method
																(
																	id					INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	customer_group_id	BIGINT				NOT NULL					COMMENT '(FK) Customer Group',
																	payment_method_id	BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Payment Method',
																	surcharge_percent	DECIMAL(4,4)		NULL						COMMENT 'Payment Method-level Surcharge for this Customer Group',
																	
																	CONSTRAINT	pk_customer_group_payment_method_id					PRIMARY KEY	(id),
																	CONSTRAINT	fk_customer_group_payment_method_customer_group_id	FOREIGN KEY (customer_group_id)	REFERENCES CustomerGroup(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
																	CONSTRAINT	fk_customer_group_payment_method_payment_method_id	FOREIGN KEY	(payment_method_id)	REFERENCES payment_method(id)	ON UPDATE CASCADE	ON DELETE CASCADE
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	customer_group_payment_method;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the customer_group_payment_method Table",
									'sAlterSQL'			=>	"	INSERT INTO	customer_group_payment_method
																	(customer_group_id,	payment_method_id)
																SELECT		cg.Id		AS customer_group_id,
																			pm.id		AS payment_method_id
																FROM		CustomerGroup cg,
																			payment_method pm
																WHERE		pm.const_name NOT IN ('PAYMENT_METHOD_REBILL');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the customer_group_rebill_type Table",
									'sAlterSQL'			=>	"	CREATE TABLE	customer_group_rebill_type
																(
																	id					INT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	customer_group_id	BIGINT				NOT NULL					COMMENT '(FK) Customer Group',
																	rebill_type_id		BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Payment Method',
																	surcharge_percent	DECIMAL(4,4)		NULL						COMMENT 'Rebill Type-level Surcharge for this Customer Group',
																	
																	CONSTRAINT	pk_customer_group_rebill_type_id				PRIMARY KEY	(id),
																	CONSTRAINT	fk_customer_group_rebill_type_customer_group_id	FOREIGN KEY (customer_group_id)	REFERENCES CustomerGroup(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
																	CONSTRAINT	fk_customer_group_rebill_type_rebill_type_id	FOREIGN KEY	(payment_method_id)	REFERENCES payment_method(id)	ON UPDATE CASCADE	ON DELETE CASCADE
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	customer_group_rebill_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the 'Rebill Payout' Payment Type",
									'sAlterSQL'			=>	"	INSERT INTO	payment_type
																	(name				, description		, const_name)
																VALUES
																	('Rebill Payout'	, 'Rebill Payout'	, 'PAYMENT_TYPE_REBILL_PAYOUT');",
									'sRollbackSQL'		=>	"	DELETE FROM	payment_type WHERE const_name = 'PAYMENT_TYPE_REBILL_PAYOUT';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the Payment.surcharge_charge_id Field",
									'sAlterSQL'			=>	"	ALTER TABLE		Payment
																ADD COLUMN		surcharge_charge_id	BIGINT	UNSIGNED	NULL	COMMENT '(FK) Surcharge Charge'
																ADD CONSTRAINT	fk_payment_surcharge_charge_id	FOREIGN KEY	(surcharge_charge_id)	REFERENCES	Charge(Id)	ON UPDATE CASCADE	ON DELETE SET NULL;",
									'sRollbackSQL'		=>	"	ALTER TABLE		Payment
																DROP CONSTRAINT	fk_payment_surcharge_charge_id,
																DROP COLUMN		surcharge_charge_id;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Link up existing Payments to Surcharges",
									'sAlterSQL'			=>	"	UPDATE	Payment p
																		JOIN Charge c ON (c.LinkType = 500 AND c.LinkId = p.Id)
																SET		p.surcharge_charge_id = c.Id
																WHERE	1;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								)
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