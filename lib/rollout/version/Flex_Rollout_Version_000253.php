<?php

/**
 * Version 253 of database update.
 */

class Flex_Rollout_Version_000253 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations = array(
			array(
				'sDescription'		=> "Adds the 'ispONE' Carrier",
				'sAlterSQL'			=> "
					INSERT INTO	Carrier
						(Name , carrier_type , description , const_name)
					VALUES
						('ispONE' , (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_TELECOM') , 'ispONE' , 'CARRIER_ISPONE');
				",
				'sRollbackSQL'		=> "
					DELETE FROM	Carrier
					WHERE		const_name = 'CARRIER_ISPONE';
				",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Adds ispONE-related Resource Types",
				'sAlterSQL'			=> "
					INSERT INTO	resource_type
						(name, description, const_name, resource_type_nature)
					VALUES
						('ispONE Secure URL Repository', 'ispONE Secure URL Repository', 'RESOURCE_TYPE_FILE_RESOURCE_ISPONE', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY')),
						('ispONE Daily Event File', 'ispONE Daily Event File', 'RESOURCE_TYPE_FILE_IMPORT_CDR_ISPONE', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE'));
				",
				'sRollbackSQL'		=> array("
						DELETE FROM	resource_type
						WHERE		const_name = 'RESOURCE_TYPE_FILE_IMPORT_CDR_ISPONE';
					", "
						DELETE FROM	resource_type
						WHERE		const_name = 'RESOURCE_TYPE_FILE_RESOURCE_ISPONE';
				"),
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