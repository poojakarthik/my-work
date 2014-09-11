<?php

/**
 * Version 254 of database update.
 */

class Flex_Rollout_Version_000254 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations = array(
			array(
				'sDescription'		=> "Create carrier_config table",
				'sAlterSQL'			=> "CREATE TABLE carrier_config (
											id 					INT UNSIGNED NOT NULL AUTO_INCREMENT,
											vendor_carrier_id 	BIGINT NOT NULL,
											created_datetime 	DATETIME NOT NULL,
											PRIMARY KEY (id),
											CONSTRAINT fk_carrier_config_vendor_carrier_id FOREIGN KEY (vendor_carrier_id) REFERENCES Carrier (Id) ON UPDATE CASCADE ON DELETE RESTRICT
										) ENGINE=InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE carrier_config;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Create carrier_translation table",
				'sAlterSQL'			=> "CREATE TABLE carrier_translation (
											id 								BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
											carrier_translation_context_id 	BIGINT UNSIGNED NOT NULL,
											in_value 						VARCHAR(1024) NOT NULL,
											out_value 						VARCHAR(1024) NOT NULL,
											description 					VARCHAR(256) NULL,
											PRIMARY KEY (id),
											CONSTRAINT fk_carrier_translation_carrier_translation_context_id FOREIGN KEY (carrier_translation_context_id) REFERENCES carrier_translation_context (id) ON UPDATE CASCADE ON DELETE RESTRICT
										) ENGINE=InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE carrier_translation;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			)
		);

		$aCarrierTranslationContexts = $this->_carrierTranslationContextInserts($aOperations);
		$this->_carrierModuleConfigInserts($aOperations, $aCarrierTranslationContexts);

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

	private function _carrierTranslationContextInserts(&$aOperations) {
		// Create carrier_translation_contexts for all call types and call groups (one of each per carrier)
		$aCarrierTranslationContexts = array(
			'cdr_call_type_translation' 	=> array(),
			'cdr_call_group_translation' 	=> array()
		);

		$mResult = Query::run("	SELECT	*
								FROM 	Carrier;");
		$aContextInserts 	= array();
		$iCarrierCounter	= 0;
		while ($aRow = $mResult->fetch_assoc()) {
			$iId	 															= $aRow['Id'];
			$aCarrierTranslationContexts['cdr_call_group_translation'][$iId] 	= 200 + $iCarrierCounter;
			$aCarrierTranslationContexts['cdr_call_type_translation'][$iId] 	= 300 + $iCarrierCounter;
			$iCarrierCounter++;
			
			$sCallGroupContext 	= Query::prepareByPHPType($aRow['Name']." Call Groups");
			$aContextInserts[] 	= "({$aCarrierTranslationContexts['cdr_call_group_translation'][$iId]}, {$sCallGroupContext}, {$sCallGroupContext})";
			$sCallTypeContext 	= Query::prepareByPHPType($aRow['Name']." Call Types");
			$aContextInserts[] 	= "({$aCarrierTranslationContexts['cdr_call_type_translation'][$iId]}, {$sCallTypeContext}, {$sCallTypeContext})";
		}

		// Query for context inserts
		$aOperations[] = array(
			'sDescription'		=> "Create carrier_translation_contexts for all call types and call groups (one of each per carrier)",
			'sAlterSQL'			=> "INSERT INTO carrier_translation_context (id, name, description)
									VALUES		".implode(', ', $aContextInserts).";",
			'sRollbackSQL'		=> "DELETE FROM carrier_translation_context
									WHERE		id >= 200;",
			'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
		);

		// Migrate translation data into carrier_translation
		$aTranslationInserts = array();

		// ...cdr_call_group_translation
		$mResult = Query::run("	SELECT	*
								FROM	cdr_call_group_translation;");
		while ($aRow = $mResult->fetch_assoc()) {
			$iContextId		= $aCarrierTranslationContexts['cdr_call_group_translation'][$aRow['carrier_id']];
			$sInValue 		= Query::prepareByPHPType($aRow['carrier_code']);
			$sOutValue		= Query::prepareByPHPType($aRow['code']);
			$sDescription	= Query::prepareByPHPType($aRow['description']);
			$aTranslationInserts[] = "({$iContextId}, {$sInValue}, {$sOutValue}, {$sDescription})";
		}

		// ...cdr_call_type_translation
		$mResult = Query::run("	SELECT	*
								FROM	cdr_call_type_translation;");
		while ($aRow = $mResult->fetch_assoc()) {
			$iContextId 	= $aCarrierTranslationContexts['cdr_call_type_translation'][$aRow['carrier_id']];
			$sInValue 		= Query::prepareByPHPType($aRow['carrier_code']);
			$sOutValue		= Query::prepareByPHPType($aRow['code']);
			$sDescription	= Query::prepareByPHPType($aRow['description']);
			$aTranslationInserts[] = "({$iContextId}, {$sInValue}, {$sOutValue}, {$sDescription})";
		}

		// One query to combine them all...
		$aOperations[] = array(
			'sDescription'		=> "Migrate cdr_call_group_translation and cdr_call_type_translation data into carrier_translation",
			'sAlterSQL'			=> "INSERT INTO carrier_translation (carrier_translation_context_id, in_value, out_value, description)
									VALUES		".implode(', ', $aTranslationInserts).";",
			'sRollbackSQL'		=> "DELETE FROM carrier_translation
									WHERE		carrier_translation_context_id >= 200;",
			'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
		);

		// Migrate ProvisioningTranslation data into carrier_translation
		$aOperations[] = array(
			'sDescription'		=> "Migrate ProvisioningTranslation data into carrier_translation",
			'sAlterSQL'			=> "INSERT INTO carrier_translation (carrier_translation_context_id, in_value, out_value)
									(
										SELECT	Context, CarrierCode, flex_code
										FROM	ProvisioningTranslation
									);",
			'sRollbackSQL'		=> "DELETE FROM carrier_translation
									WHERE		carrier_translation_context_id < 200;",
			'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
		);

		return $aCarrierTranslationContexts;
	}

	private function _carrierModuleConfigInserts(&$aOperations, $aCarrierTranslationContexts) {
		// Update carrier_modules with new configuration properties, necessary now that carrier/carrier translation context constants aren't used in the code
		$aResourceTypeCarrierIds = array(
			RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_STANDARD 			=> CARRIER_AAPT,
			RESOURCE_TYPE_FILE_IMPORT_CDR_ACENET					=> CARRIER_ACENET,
			RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE 		=> CARRIER_AAPT,
			RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_CTOP 		=> CARRIER_AAPT,
			RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_MOBILE 			=> CARRIER_UNITEL,
			RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_ADSL1 				=> CARRIER_ISEEK,
			RESOURCE_TYPE_FILE_IMPORT_CDR_ISEEK_DATA 				=> CARRIER_ISEEK,
			RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE 	=> CARRIER_TELSTRA,
			RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE => CARRIER_TELSTRA,
			RESOURCE_TYPE_FILE_IMPORT_CDR_M2_STANDARD 				=> CARRIER_M2,
			RESOURCE_TYPE_FILE_IMPORT_CDR_OPTUS_STANDARD 			=> CARRIER_OPTUS,
			RESOURCE_TYPE_FILE_IMPORT_CDR_UNITEL_STANDARD 			=> CARRIER_UNITEL
		);
		$iType = DATA_TYPE_INTEGER;
		foreach ($aResourceTypeCarrierIds as $iResourceTypeId => $iCarrierId) {
			$aOperations[] 	= array(
				'sDescription'		=> "Add carrier module configuration to carrier modules (for carrier {$iCarrierId}) with resource type {$iResourceTypeId}. A property called 'CallGroupCarrierTranslationContextId' to replace the use of carrier constants.",
				'sAlterSQL'			=> "INSERT INTO CarrierModuleConfig (CarrierModule, Name, Type, Description, Value)
										(
											SELECT	cm.Id, 'CallGroupCarrierTranslationContextId', {$iType}, 'Call Group (Record Type) Carrier Translation Context Id', {$aCarrierTranslationContexts['cdr_call_group_translation'][$iCarrierId]}
											FROM	CarrierModule cm
											WHERE	cm.FileType = {$iResourceTypeId}
											AND		Active = 1
										);",
				'sRollbackSQL'		=> "DELETE FROM CarrierModuleConfig
										WHERE		Name = 'CallGroupCarrierTranslationContextId';",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			);
			$aOperations[] 	= array(
				'sDescription'		=> "Add carrier module configuration to carrier modules (for carrier {$iCarrierId}) with resource type {$iResourceTypeId}. A property called 'CallTypeCarrierTranslationContextId' to replace the use of carrier constants.",
				'sAlterSQL'			=> "INSERT INTO CarrierModuleConfig (CarrierModule, Name, Type, Description, Value)
										(
											SELECT	cm.Id, 'CallTypeCarrierTranslationContextId', {$iType}, 'Call Type (Destination) Carrier Translation Context Id', {$aCarrierTranslationContexts['cdr_call_type_translation'][$iCarrierId]}
											FROM	CarrierModule cm
											WHERE	cm.FileType = {$iResourceTypeId}
											AND		Active = 1
										);",
				'sRollbackSQL'		=> "DELETE FROM CarrierModuleConfig
										WHERE		Name = 'CallTypeCarrierTranslationContextId';",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			);
		}

		$iResourceTypeId 	= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_OPTUS_PPR;
		$aOperations[] 		= array(
			'sDescription'		=> "Add carrier module configuration to carrier modules with resource type RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_OPTUS_PPR. Two properties called 'EPIDCarrierTranslationContext' and 'RejectCarrierTranslationContext' to replace the use of carrier constants.",
			'sAlterSQL'			=> "INSERT INTO CarrierModuleConfig (CarrierModule, Name, Type, Description, Value)
									(
										SELECT	cm.Id, 'EPIDCarrierTranslationContext', {$iType}, 'Carrier Translation Context Id - Eligible Party ID', 100
										FROM	CarrierModule cm
										WHERE	cm.FileType = {$iResourceTypeId}
										AND		Active = 1
									) UNION (
										SELECT	cm.Id, 'RejectCarrierTranslationContext', {$iType}, 'Carrier Translation Context Id - ACIF Reject Codes', 101
										FROM	CarrierModule cm
										WHERE	cm.FileType = {$iResourceTypeId}
										AND		Active = 1
									);",
			'sRollbackSQL'		=> "DELETE FROM CarrierModuleConfig
									WHERE		Name IN ('EPIDCarrierTranslationContext', 'RejectCarrierTranslationContext');",
			'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
		);

		$iResourceTypeId 	= RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS;
		$aOperations[] 		= array(
			'sDescription'		=> "Add carrier module configuration to carrier modules with resource type RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_UNITEL_DAILY_STATUS. A property called 'UnitelRejectCarrierTranslationContext' to replace the use of carrier constants.",
			'sAlterSQL'			=> "INSERT INTO CarrierModuleConfig (CarrierModule, Name, Type, Description, Value)
									(
										SELECT	cm.Id, 'UnitelRejectCarrierTranslationContext', {$iType}, 'Carrier Translation Context Id - Unitel Reject Codes', 103
										FROM	CarrierModule cm
										WHERE	cm.FileType = {$iResourceTypeId}
										AND		Active = 1
									);",
			'sRollbackSQL'		=> "DELETE FROM CarrierModuleConfig
									WHERE		Name = 'UnitelRejectCarrierTranslationContext';",
			'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
		);
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