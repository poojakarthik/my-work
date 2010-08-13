<?php

/**
 * Version 220 of database update.
 * This version: -
 *
 *	1:	Add the new AAPT Provisioning Resource Types
 *
 */

class Flex_Rollout_Version_000220 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the new AAPT Provisioning Resource Types",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type
																	(name								, description						, const_name		, resource_type_nature)
																VALUES
																	('AAPT E-Systems Preselection File'			, 'AAPT E-Systems Preselection File'		, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_PRESELECTION'		, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE' LIMIT 1)),
																	('AAPT E-Systems Full Service Rebill File'	, 'AAPT E-Systems Full Service Rebill File'	, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_FULLSERVICEREBILL'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE' LIMIT 1)),
																	('AAPT E-Systems Deactivations File'		, 'AAPT E-Systems Deactivations File'		, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_DEACTIVATIONS'		, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE' LIMIT 1)),
																	('AAPT E-Systems Daily Event File'			, 'AAPT E-Systems Daily Event File'			, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_PRESELECTION', 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_FULLSERVICEREBILL', 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_AAPT_ESYSTEMS_DEACTIVATIONS', 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_AAPT_ESYSTEMS_DAILYEVENT');",
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