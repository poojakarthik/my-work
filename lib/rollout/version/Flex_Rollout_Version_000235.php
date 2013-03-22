<?php

/**
 * Version 235 of database update.
 * This version: -
 *
 *	1:	Add the Acenet CDR File Format Resource Type
 *
 */

class Flex_Rollout_Version_000235 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$aOperations	= array();
		
		// 1: Check to see if Acenet was already defined as a Carrier
		//--------------------------------------------------------------------//
		$oResult	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)->query("SELECT Id FROM Carrier WHERE const_name = 'CARRIER_ACENET'");
		if (MDB2::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to detect if Acenet was already defined as a Carrier. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		elseif ($oResult->numRows())
		{
			// Acenet CDR File was already defined as a Resource Type
			$this->outputMessage("Skipping ".self::getRolloutVersionNumber(__CLASS__)." Operation: Acenet already exists as a Carrier...\n");
		}
		else
		{
			// Add operation
			$aOperations[]	=	array
								(
									'sDescription'		=>	"Add Acenet as a Carrier",
									'sAlterSQL'			=>	"	INSERT INTO	Carrier
																	(Name		, description	, const_name		, carrier_type)
																VALUES
																	('Acenet'	, 'Acenet'		, 'CARRIER_ACENET'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_TELECOM' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	Carrier	WHERE const_name IN ('CARRIER_ACENET');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								);
		}
		
		// 2: Check to see if Acenet CDR File was already defined as a Resource Type
		//--------------------------------------------------------------------//
		$oResult	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)->query("SELECT Id FROM resource_type WHERE const_name = 'RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET'");
		if (MDB2::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to detect if Acenet CDR File was already defined as a Resource Type. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		elseif ($oResult->numRows())
		{
			// Acenet CDR File was already defined as a Resource Type
			$this->outputMessage("Skipping ".self::getRolloutVersionNumber(__CLASS__)." Operation: Acenet CDR File already exists as a Resource Type...\n");
		}
		else
		{
			// Add operation
			$aOperations[]	=	array
								(
									'sDescription'		=>	"Add the Acenet CDR File Format Resource Type",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type
																	(name						, description					, const_name								, resource_type_nature)
																VALUES
																	('Acenet CDR File'			, 'Acenet CDR File'				, 'RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								);
		}
		
		//--------------------------------------------------------------------//
		// Perform Batch Rollout
		//--------------------------------------------------------------------//
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