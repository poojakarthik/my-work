<?php

/**
 * Version 204 of database update.
 * This version: -
 *	
 *	1:	Remove the destination_context.const_name Field (so that it can be managed without Flex Releases)
 *	2:	Create the rate_class Table
 *	3:	Add the Rate.rate_class_id Field
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
								array
								(
									'sDescription'		=>	"Remove the destination_context.const_name Field",
									'sAlterSQL'			=>	"	ALTER TABLE	destination_context
																DROP COLUMN	const_name;",
									'sRollbackSQL'		=>	array
															(
																"	ALTER TABLE	destination_context
																	ADD COLUMN	const_name	VARCHAR	(512)	NOT NULL	COMMENT 'Constant Name for the Destination Context';",
																"	UPDATE	destination_context
																	SET		const_name =	CASE
																								WHEN name = 'IDD'	THEN 'DESTINATION_CONTEXT_IDD'
																								WHEN name = 'S&E'	THEN 'DESTINATION_CONTEXT_SERVICE_AND_EQUIPMENT'
																								WHEN name = 'OC'	THEN 'DESTINATION_CONTEXT_OC'
																								WHEN name = '3G'	THEN 'DESTINATION_CONTEXT_3G'
																							END
																	WHERE	1;"
															),
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create the rate_class Table",
									'sAlterSQL'			=>	"	CREATE TABLE	rate_class
																(
																	id				INTEGER	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	name			VARCHAR	(128)		NOT NULL					COMMENT 'Name for the Rate Class',
																	description		VARCHAR	(256)		NOT NULL					COMMENT 'Description of the Rate Class',
																	invoice_code	VARCHAR	(30)		NULL						COMMENT 'Code to identify Usage in this Rate Class on the Invoice',
																	
																	CONSTRAINT	pk_rate_class_id	PRIMARY KEY	(id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"DROP TABLE	rate_class;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the Rate.rate_class_id Field",
									'sAlterSQL'			=>	"	ALTER TABLE		Rate
																ADD COLUMN		rate_class_id	INTEGER	UNSIGNED	NULL	COMMENT '(FK) Rate Class this Rate belongs to',
																ADD CONSTRAINT	fk_rate_rate_class_id	FOREIGN KEY	(rate_class_id)	REFERENCES rate_class(id)	ON UPDATE	CASCADE	ON DELETE	SET NULL;",
									'sRollbackSQL'		=>	"	ALTER TABLE	Rate
																DROP CONSTRAINT	fk_rate_rate_class_id,
																DROP COLUMN		rate_class_id;",
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
			if (MDB2::isError($oResult))
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
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>