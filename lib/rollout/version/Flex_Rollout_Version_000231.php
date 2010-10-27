<?php

/**
 * Version 231 of database update
 */

class Flex_Rollout_Version_000231 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table cdr_delinquent_writeoff",
									'sAlterSQL'			=>	"	CREATE TABLE cdr_delinquent_writeoff
																(
																	id					BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\", 
																	cdr_id				BIGINT			NOT NULL					COMMENT \"Deliquent CDR record that was written off\", 
																	created_datetime	DATETIME		NOT NULL					COMMENT \"When the write off occured\", 
																	created_employee_id	BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) Employee, who executed the write off\", 
																	PRIMARY KEY	(id),
																	CONSTRAINT cdr_delinquent_writeoff_created_employee_id	FOREIGN KEY (created_employee_id)	REFERENCES Employee (Id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB, COMMENT=\"Log for a deliquent CDR record that has been written off.\";",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS cdr_delinquent_writeoff;",
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