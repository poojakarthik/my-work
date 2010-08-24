<?php

/**
 * Version 223 of database update.
 * This version: -
 *
 *	1:	Add the new Motorpass Provisioning Import/Export and File Deliver Carrier Module Types
 *	2:	Add the new 'File Deliverer' Resource Type Nature
 *	3:	Add the 'Filesystem' and 'Filesystem_FTP' File Delivery Resource Types
 *
 */

class Flex_Rollout_Version_000223 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the new Motorpass Provisioning Import/Export Carrier Module Types",
									'sAlterSQL'			=>	"	INSERT INTO	carrier_module_type
																	(name								, description						, const_name)
																VALUES
																	('Motorpass Provisioning Import'	, 'Motorpass Provisioning Import'	, 'MODULE_TYPE_MOTORPASS_PROVISIONING_EXPORT'),
																	('Motorpass Provisioning Export'	, 'Motorpass Provisioning Export'	, 'MODULE_TYPE_MOTORPASS_PROVISIONING_IMPORT'),
																	('File Deliver'						, 'File Deliver'					, 'MODULE_TYPE_FILE_DELIVER');",
									'sRollbackSQL'		=>	"	DELETE FROM	carrier_module_type	WHERE const_name IN ('MODULE_TYPE_MOTORPASS_PROVISIONING_EXPORT', MODULE_TYPE_MOTORPASS_PROVISIONING_IMPORT', 'MODULE_TYPE_FILE_DELIVER');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the new 'File Deliverer' Resource Type Nature",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type_nature
																	(name						, description						, const_name)
																VALUES
																	('File Deliverer'			, 'File Deliverer'			, 'RESOURCE_TYPE_NATURE_FILE_DELIVERER');",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type_nature	WHERE const_name IN ('RESOURCE_TYPE_NATURE_FILE_DELIVERER');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the 'Filesystem' and 'Filesystem_FTP' File Delivery Resource Types",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type
																	(name					, description						, const_name									, resource_type_nature)
																VALUES
																	('Filesystem'			, 'Filesystem (fopen stream)'		, 'RESOURCE_TYPE_FILE_DELIVERER_FILESYSTEM'		, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_DELIVERER' LIMIT 1)),
																	('FTP (Filesystem)'		, 'FTP (fopen stream)'				, 'RESOURCE_TYPE_FILE_DELIVERER_FILESYSTEM_FTP'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_DELIVERER' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('RESOURCE_TYPE_FILE_DELIVERER_FILESYSTEM', 'RESOURCE_TYPE_FILE_DELIVERER_FILESYSTEM_FTP');",
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