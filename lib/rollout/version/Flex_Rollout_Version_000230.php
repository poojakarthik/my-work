<?php

/**
 * Version 230 of database update - email_queue tables
 */

class Flex_Rollout_Version_000230 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table charge_type_system",
									'sAlterSQL'			=>	"	CREATE TABLE charge_type_system
																(
																	id				INT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\", 
																	name			VARCHAR(256)	NOT NULL					COMMENT \"Name of the System Charge Type\", 
																	description		VARCHAR(512)	NOT NULL					COMMENT \"Description of the System Charge Type\", 
																	const_name		VARCHAR(256)	NOT NULL					COMMENT \"Constant Alias of the System Charge Type\", 
																	system_name		VARCHAR(256)	NOT NULL					COMMENT \"System Name of the System Charge Type\", 
																	PRIMARY KEY	(id)
																) ENGINE=InnoDB, COMMENT=\"A System Charge Type\";",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS charge_type_system;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add system charge type for rerating adjustments (RERATE)",
									'sAlterSQL'			=>	"	INSERT INTO charge_type_system (name, description, const_name, system_name) 
																VALUES	('Rerate', 'Rerate Adjustment', 'CHARGE_TYPE_SYSTEM_RERATE', 'RERATE');",
									'sRollbackSQL'		=>	"	TRUNCATE charge_type_system;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table charge_type_system_config",
									'sAlterSQL'			=>	"	CREATE TABLE charge_type_system_config
																(
																	id						INT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
																	charge_type_system_id	INT UNSIGNED	NOT NULL					COMMENT \"(FK) charge_type_system, the System Charge Type being configured\",
																	charge_type_id			BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) ChargeType, the Charge Type which the System Charge Type represents\", 
																	start_datetime			DATETIME		NOT NULL					COMMENT \"When the System Charge Type configuration is valid from\",
																	end_datetime			DATETIME		NOT NULL					COMMENT \"When the System Charge Type configuration is valid to\",
																	PRIMARY KEY (id),
																	CONSTRAINT fk_charge_type_system_config_charge_type_system_id	FOREIGN KEY (charge_type_system_id)	REFERENCES charge_type_system (id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT fk_charge_type_system_config_charge_type_id			FOREIGN KEY (charge_type_id)		REFERENCES ChargeType (Id)			ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB, COMMENT=\"A configuration of a System Charge Type\";",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS charge_type_system_config;",
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