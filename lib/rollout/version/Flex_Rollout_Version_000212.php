<?php

/**
 * Version 212 of database update.
 * This version: -
 *
 *	1:	Add the Motorpass Billing Input and Output Resource Types
 *	2:	Add the 'Invoice Run Export' Carrier Module Type
 *
 */

class Flex_Rollout_Version_000212 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add the Motorpass Billing Input and Output Resource Type",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type
																	(name								, description						, const_name		, resource_type_nature_id)
																VALUES
																	('Motorpass Invoice Payout File'	, 'Motorpass Invoice Payout File'	, 'RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1)),
																	('Motorpass Billing Export File'	, 'Motorpass Billing Export File'	, 'RESOURCE_TYPE_FILE_EXPORT_INVOICE_RUN_MOTORPASS'				, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('RESOURCE_TYPE_FILE_IMPORT_PAYMENT_MOTORPASS_INVOICE_PAYOUT', 'RESOURCE_TYPE_FILE_EXPORT_INVOICE_RUN_MOTORPASS');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add the 'Invoice Run Export' Carrier Module Type",
									'sAlterSQL'			=>	"	INSERT INTO	carrier_module_type
																	(name					, description				, const_name)
																VALUES
																	('Invoice Run Export'	, 'Invoice Run Export'		, 'MODULE_TYPE_INVOICE_RUN_EXPORT');",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('MODULE_TYPE_INVOICE_RUN_EXPORT');;",
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