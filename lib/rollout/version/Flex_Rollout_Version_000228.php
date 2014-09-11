<?php

/**
 * Version 228 of database update.
 * This version: -
 *
 *	1:	Add the AAPT CTOP CDR File Format Resource Type
 *
 */

class Flex_Rollout_Version_000228 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Check to see if AAPT CTOP File was already defined as a Resource Type
		$oResult	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)->query("SELECT Id FROM resource_type WHERE const_name = 'RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP'");
		if (MDB2::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to detect if AAPT CTOP File was already defined as a Resource Type. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		elseif ($oResult->numRows())
		{
			// AAPT CTOP File was already defined as a Resource Type
			$this->outputMessage("Skipping ".self::getRolloutVersionNumber(__CLASS__).": AAPT CTOP File already exists as a Resource Type...\n");
			return;
		}
		
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the AAPT CTOP CDR File Format Resource Type",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type
																	(name						, description					, const_name											, resource_type_nature)
																VALUES
																	('AAPT E-Systems CTOP File'	, 'AAPT E-Systems CTOP File'	, 'RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP');",
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