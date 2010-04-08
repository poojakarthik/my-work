<?php

/**
 * Version 204 of database update.
 * This version: -
 *	
 *	1:	Flex: Create the usage_modifier Table
 *	2:	Flex: Populate the usage_modifier Table
 *	3:	Flex: Create the cdr_usage_modifier Table
 *	4:	Flex: Create the rate_usage_modifier Table
 *	5:	Flex: Create the cdr_usage_modifier_translation Table
 *
 *	6:	CDR: Create the cdr_usage_modifier Table
 *
 */

class Flex_Rollout_Version_000204 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								// MYSQL: flex_*
								array
								(
									'sDescription'		=>	"Create the usage_modifier Table",
									'sAlterSQL'			=>	"	CREATE TABLE	usage_modifier
																(
																	id			INTEGER	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	name		VARCHAR(128)		NOT NULL					COMMENT 'Name of the Usage Modifier',
																	description	VARCHAR(512)		NOT NULL					COMMENT 'Description of the Usage Modifier',
																	system_name	VARCHAR(128)		NOT NULL					COMMENT 'Code-friendly alias for the Usage Modifier',
																	
																	CONSTRAINT	pk_usage_modifier_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	usage_modifier",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the usage_modifier Table",
									'sAlterSQL'			=>	"	INSERT INTO	usage_modifier
																	(name		, description			, system_name)
																VALUES
																	('yes Time'	, 'Optus yes Time'	, 'YES_TIME'),
																	('On-Net'	, 'On-Net'			, 'ON_NET'),
																	('Off-Net'	, 'Off-Net'			, 'OFF_NET');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the cdr_usage_modifier Table",
									'sAlterSQL'			=>	"	CREATE TABLE	cdr_usage_modifier
																(
																	id					BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	cdr_id				BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) CDR',
																	usage_modifier_id	INT		UNSIGNED	NOT NULL					COMMENT '(FK) Usage Modifier Id',
																	
																	CONSTRAINT	pk_cdr_usage_modifier_id				PRIMARY KEY	(id),
																	CONSTRAINT	fk_cdr_usage_modifier_cdr_id			FOREIGN KEY	(cdr_id)			REFERENCES CDR(Id)				ON UPDATE	CASCADE	ON DELETE CASCADE,
																	CONSTRAINT	fk_cdr_usage_modifier_usage_modifier_id	FOREIGN KEY	(usage_modifier_id)	REFERENCES usage_modifier(id)	ON UPDATE	CASCADE	ON DELETE RESTRICT,
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	cdr_usage_modifier;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the rate_usage_modifier Table",
									'sAlterSQL'			=>	"	CREATE TABLE	rate_usage_modifier
																(
																	id					BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	rate_id				BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Rate',
																	usage_modifier_id	INT		UNSIGNED	NOT NULL					COMMENT '(FK) Usage Modifier Id',
																	
																	CONSTRAINT	pk_rate_usage_modifier_id					PRIMARY KEY	(id),
																	CONSTRAINT	fk_rate_usage_modifier_rate_id				FOREIGN KEY	(rate_id)			REFERENCES Rate(Id)				ON UPDATE	CASCADE	ON DELETE CASCADE,
																	CONSTRAINT	fk_rate_usage_modifier_usage_modifier_id	FOREIGN KEY	(usage_modifier_id)	REFERENCES usage_modifier(id)	ON UPDATE	CASCADE	ON DELETE RESTRICT,
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	rate_usage_modifier;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the cdr_usage_modifier_translation Table",
									'sAlterSQL'			=>	"	CREATE TABLE	cdr_usage_modifier_translation
																(
																	id								INTEGER	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	carrier_translation_context_id	BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Carrier Translation Context',
																	carrier_code					VARCHAR(1024)		NOT NULL					COMMENT 'Carrier Code to translate',
																	carrier_description				VARCHAR(1024)		NOT NULL					COMMENT 'Description of the Carrier Code',
																	usage_modifier_id				INT		UNSIGNED	NOT NULL					COMMENT '(FK) Usage Modifier',
																	
																	CONSTRAINT	pk_cdr_usage_modifier_translation_id								PRIMARY KEY	(id),
																	CONSTRAINT	fk_cdr_usage_modifier_translation_carrier_translation_context_id	FOREIGN KEY	(carrier_translation_context_id)	REFERENCES carrier_translation_context(id)	ON UPDATE	CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_cdr_usage_modifier_translation_usage_modifier_id					FOREIGN KEY	(usage_modifier_id)					REFERENCES usage_modifier(id)				ON UPDATE	CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	cdr_usage_modifier_translation;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								// POSTGRES: flex_*_cdr
								array
								(
									'sDescription'		=>	"Create the cdr_usage_modifier Table",
									'sAlterSQL'			=>	"	CREATE TABLE	cdr_usage_modifier
																(
																	id					INTEGER	NOT NULL
																	cdr_invoiced_id		INTEGER	NOT NULL	COMMENT '(FK) Invoiced CDR',
																	usage_modifier_id	INTEGER	NOT NULL	COMMENT 'Usage Modifier Id',
																	
																	CONSTRAINT	pk_cdr_usage_modifier_id				PRIMARY KEY	(id),
																	CONSTRAINT	fk_cdr_usage_modifier_cdr_invoiced_id	FOREIGN KEY	(cdr_id)			REFERENCES cdr_invoiced(id)			ON UPDATE	CASCADE	ON DELETE CASCADE,
																);",
									// Rollback should be handled by PostgreSQL natively using Transactions
									//'sRollbackSQL'		=>	"DROP TABLE	cdr_usage_modifier;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_CDR
								),
								array
								(
									'sDescription'		=>	"Comment on cdr_usage_modifier.cdr_invoiced_id",
									'sAlterSQL'			=>	"	COMMENT ON	COLUMN	cdr_usage_modifier.cdr_invoiced_id		IS '(FK) Invoiced CDR';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_CDR
								),
								array
								(
									'sDescription'		=>	"Comment on cdr_usage_modifier.usage_modifier_id",
									'sAlterSQL'			=>	"	COMMENT ON	COLUMN	cdr_usage_modifier.usage_modifier_id	IS '(FK) Usage Modifier';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_CDR
								)
							);
		
		// Perform Batch Rollout
		$iRolloutVersionNumber	= 
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation)
		{
			$iStepNumber++;
			
			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");
			
			// Attempt to apply changes
			$oResult	= Data_Source::get($aOperation['sDataSourceName'])->query($aOperation);
			if (PEAR::isError($oResult))
			{
				throw new Exception(__CLASS__ . " Failed to {$aOperation['sDescription']}. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
			}
			
			// Append to Rollback Script (if on is provided)
			if (array_key_exists('sRollbackSQL', $aOperation) && trim($aOperation['sRollbackSQL']))
			{
				$this->rollbackSQL[] =	$aOperation['sRollbackSQL'];
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