<?php

/**
 * Version 255 of database update.
 */

class Flex_Rollout_Version_000255 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		if (!$this->getUserResponseYesNo("Proceed with REMOVING carrier_translation_context.const_name and other carrier/carrier translation related removals?")) {
			throw new Exception("Rollout ".self::getRolloutVersionNumber(__CLASS__)." aborted.");
			//return;
		}

		// Define operations
		$aOperations = array(
			array(
				'sDescription'		=> "Remove const_name from carrier_translation_context",
				'sAlterSQL'			=> "ALTER TABLE carrier_translation_context
										DROP COLUMN const_name;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Remove const_name from Carrier",
				'sAlterSQL'			=> "ALTER TABLE Carrier
										DROP COLUMN const_name;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Drop cdr_call_group_translation table",
				'sAlterSQL'			=> "DROP TABLE cdr_call_group_translation;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Drop cdr_call_type_translation table",
				'sAlterSQL'			=> "DROP TABLE cdr_call_type_translation;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Drop ProvisioningTranslation table",
				'sAlterSQL'			=> "DROP TABLE ProvisioningTranslation;",
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