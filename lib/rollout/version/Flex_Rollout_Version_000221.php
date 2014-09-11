<?php

/**
 * Version 221 of database update.
 * This version: -
 *
 *	1:	Add the new ReD Motorpass Provisioning Resource Types
 *
 */

class Flex_Rollout_Version_000221 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the new ReD Motorpass Provisioning Resource Types",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type
																	(name								, description						, const_name		, resource_type_nature)
																VALUES
																	('ReD Motorpass Applications File'			, 'ReD Motorpass Applications File'			, 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_RETAILDECISIONS_APPLICATIONS'		, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE' LIMIT 1)),
																	('ReD Motorpass Approvals File'				, 'ReD Motorpass Approvals File'			, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_APPROVALS'		, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1)),
																	('ReD Motorpass Declines File'				, 'ReD Motorpass Declines File'				, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_DECLINES'			, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1)),
																	('ReD Motorpass Withdraws File'				, 'ReD Motorpass Withdraws File'			, 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_WITHDRAWS'		, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_RETAILDECISIONS_APPLICATIONS', RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_APPROVALS', 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_DECLINES', 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_RETAILDECISIONS_WITHDRAWS');",
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