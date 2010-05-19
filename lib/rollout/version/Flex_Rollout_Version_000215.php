<?php

/**
 * Version 215 of database update.
 * This version: -
 *
 *	1:	Add the charge_model Table
 *	2:	Populate the charge_model Table
 *
 *	3:	Add the Charge.charge_model_id Field
 *	4:	Populate the Charge.charge_model_id Field retroactively
 *	5:	Set the Charge.charge_model_id Field to NOT NULL
 *
 *	6:	Add the ChargeType.charge_model_id Field
 *	7:	Populate the ChargeType.charge_model_id Field retroactively
 *	8:	Set the ChargeType.charge_model_id Field to NOT NULL
 *
 *	9:	Add the Invoice.charge_total, .charge_tax, .adjustment_total and .adjustment_tax Fields
 *	10:	Populate the Invoice.charge_total and .charge_tax Fields retroactively
 *
 */

class Flex_Rollout_Version_000215 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the charge_model Table",
									'sAlterSQL'			=>	"	CREATE TABLE	charge_model
																(
																	id			INT				UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	name		VARCHAR(256)				NOT NULL					COMMENT 'Name',
																	description	VARCHAR(512)				NOT NULL					COMMENT 'Description',
																	const_name	VARCHAR(512)				NOT NULL					COMMENT 'Constant Alias',
																	system_name	VARCHAR(256)				NOT NULL					COMMENT 'System Alias',
																	
																	CONSTRAINT	pk_charge_model_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE	charge_model;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the charge_model Table",
									'sAlterSQL'			=>	"	INSERT INTO	charge_model
																	(name			, description	, const_name				, system_name)
																VALUES
																	('Charge'		, 'Charge'		, 'CHARGE_MODEL_CHARGE'		, 'CHARGE'),
																	('Adjustment'	, 'Adjustment'	, 'CHARGE_MODEL_ADJUSTMENT'	, 'ADJUSTMENT');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the Charge.charge_model_id Field",
									'sAlterSQL'			=>	"	ALTER TABLE	Charge
																ADD COLUMN		charge_model_id	INT	UNSIGNED	NOT NULL	COMMENT '(FK) Charge Model',
																ADD CONSTRAINT	fk_charge_charge_model_id	FOREIGN KEY	(charge_model_id)	REFERENCES	charge_model(id)	ON UPDATE CASCADE	ON DELETE RESTRICT;",
									'sRollbackSQL'		=>	"	ALTER TABLE	Charge
																DROP CONSTRAINT	fk_charge_charge_model_id,
																DROP COLUMN		charge_model_id;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the Charge.charge_model_id Field retroactively",
									'sAlterSQL'			=>	"	UPDATE	Charge
																SET		charge_model_id = (SELECT id FROM charge_model WHERE system_name = 'CHARGE')
																WHERE	1;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								/*array
								(
									'sDescription'		=>	"Set the Charge.charge_model_id Field to NOT NULL",
									'sAlterSQL'			=>	"	ALTER TABLE	Charge
																MODIFY COLUMN	charge_model_id	INT	UNSIGNED	NOT NULL	COMMENT '(FK) Charge Model';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/
								array
								(
									'sDescription'		=>	"Add the ChargeType.charge_model_id Field",
									'sAlterSQL'			=>	"	ALTER TABLE	ChargeType
																ADD COLUMN		charge_model_id	INT	UNSIGNED	NOT NULL	COMMENT '(FK) Charge Model',
																ADD CONSTRAINT	fk_charge_type_charge_model_id	FOREIGN KEY	(charge_model_id)	REFERENCES	charge_model(id)	ON UPDATE CASCADE	ON DELETE RESTRICT;",
									'sRollbackSQL'		=>	"	ALTER TABLE	ChargeType
																DROP CONSTRAINT	fk_charge_type_charge_model_id,
																DROP COLUMN		charge_model_id;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the ChargeType.charge_model_id Field retroactively",
									'sAlterSQL'			=>	"	UPDATE	ChargeType
																SET		charge_model_id = (SELECT id FROM charge_model WHERE system_name = 'CHARGE')
																WHERE	1;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								/*array
								(
									'sDescription'		=>	"Set the ChargeType.charge_model_id Field to NOT NULL",
									'sAlterSQL'			=>	"	ALTER TABLE	ChargeType
																MODIFY COLUMN	charge_model_id	INT	UNSIGNED	NOT NULL	COMMENT '(FK) Charge Model';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/
								array
								(
									'sDescription'		=>	"Add the Invoice.charge_total, .charge_tax, .adjustment_total and .adjustment_tax Fields",
									'sAlterSQL'			=>	"	ALTER TABLE	Invoice
																ADD COLUMN	charge_total		DECIMAL(13, 4)	NOT NULL			COMMENT 'New Charges Total',
																ADD COLUMN	charge_tax			DECIMAL(13, 4)	NOT NULL			COMMENT 'New Charges Tax',
																ADD COLUMN	adjustment_total	DECIMAL(13, 4)	NOT NULL			COMMENT 'Adjustment Total',
																ADD COLUMN	adjustment_tax		DECIMAL(13, 4)	NOT NULL			COMMENT 'Adjustment Tax';",
									'sRollbackSQL'		=>	"	ALTER TABLE	Invoice
																DROP COLUMN	charge_total,
																DROP COLUMN	charge_tax,
																DROP COLUMN	adjustment_total,
																DROP COLUMN	adjustment_tax;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the Invoice.charge_total and .charge_tax Fields retroactively",
									'sAlterSQL'			=>	"	UPDATE	Invoice
																SET		charge_total = Total,
																		charge_tax = Tax
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