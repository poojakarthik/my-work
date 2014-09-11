<?php

class Flex_Rollout_Version_000260 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription'		=> "Add fullservice_wholesale_plan & preselection_wholesale_plan columns to RatePlan",
				'sAlterSQL'			=> "ALTER TABLE RatePlan
										ADD COLUMN	fullservice_wholesale_plan VARCHAR(1024) NULL,
										ADD COLUMN	preselection_wholesale_plan VARCHAR(1024) NULL;",
				'sRollbackSQL'		=> "ALTER TABLE RatePlan
										DROP COLUMN	fullservice_wholesale_plan,
										DROP COLUMN	preselection_wholesale_plan;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Add scheduled_datetime column to the ProvisioningRequest table",
				'sAlterSQL'			=> "ALTER TABLE ProvisioningRequest
										ADD COLUMN	scheduled_datetime DATETIME NULL AFTER AuthorisationDate;",
				'sRollbackSQL'		=> "ALTER TABLE ProvisioningRequest
										DROP COLUMN	scheduled_datetime;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Populate the scheduled_datetime column in the ProvisioningRequest table",
				'sAlterSQL'			=> "UPDATE 	ProvisioningRequest
										SET		scheduled_datetime = RequestedOn;",
				'sRollbackSQL'		=> "UPDATE 	ProvisioningRequest
										SET		scheduled_datetime = NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "New provisioning types for plan change",
				'sAlterSQL'			=> "INSERT INTO provisioning_type (name, inbound, outbound, provisioning_type_nature, description, const_name)
										VALUES		(
											'Full Service Plan Change', 
											0, 
											1, 
											(
												SELECT 	id 
												FROM 	provisioning_type_nature 
												WHERE	const_name = 'REQUEST_TYPE_NATURE_FULL_SERVICE'
											), 
											'Full Service Plan Change Request', 
											'PROVISIONING_TYPE_FULL_SERVICE_PLAN_CHANGE'
										), (
											'Pre-Selection Plan Change', 
											0, 
											1, 
											(
												SELECT 	id 
												FROM 	provisioning_type_nature 
												WHERE	const_name = 'REQUEST_TYPE_NATURE_PRESELECTION'
											), 
											'Pre-Selection Plan Change Request', 
											'PROVISIONING_TYPE_PRESELECTION_PLAN_CHANGE'
										);",
				'sRollbackSQL'		=> "DELETE FROM provisioning_type
										WHERE		const_name in ('PROVISIONING_TYPE_FULL_SERVICE_PLAN_CHANGE', 'PROVISIONING_TYPE_PRESELECTION_PLAN_CHANGE');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "New resource_types for wholesale provisioning & usage normalisation",
				'sAlterSQL'			=> "INSERT INTO resource_type (name, description, const_name, resource_type_nature)
										VALUES		('Telcoblue Wholesale Repository', 			'Telcoblue Wholesale Repository', 			'RESOURCE_TYPE_FILE_RESOURCE_TELCOBLUE', 			(SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_FILE_REPOSITORY')),
													('Telcoblue Wholesale Provisioning File', 	'Telcoblue Wholesale Provisioning File', 	'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')),
													('Telcoblue Wholesale Usage File',			'Telcoblue Wholesale Usage File', 			'RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE', 			(SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE')),
													('Telcoblue Wholesale Provisioning Export', 'Telcoblue Wholesale Provisioning Export', 	'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_TELCOBLUE', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_EXPORT_FILE'));",
				'sRollbackSQL'		=> "DELETE FROM resource_type
										WHERE		const_name IN ('RESOURCE_TYPE_FILE_RESOURCE_TELCOBLUE', 'RESOURCE_TYPE_FILE_IMPORT_PROVISIONING_TELCOBLUE', 'RESOURCE_TYPE_FILE_IMPORT_CDR_TELCOBLUE', 'RESOURCE_TYPE_FILE_EXPORT_PROVISIONING_TELCOBLUE');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Adding fullservice_provisioning_request_id and preselection_provisioning_request_id to ServiceRatePlan",
				'sAlterSQL' => "ALTER TABLE 	ServiceRatePlan
								ADD COLUMN 		fullservice_provisioning_request_id BIGINT UNSIGNED NULL,
								ADD COLUMN 		preselection_provisioning_request_id BIGINT UNSIGNED NULL,
								ADD CONSTRAINT	fk_servicerateplan_fullservice_provisioning_request_id FOREIGN KEY (fullservice_provisioning_request_id) REFERENCES ProvisioningRequest (Id) ON UPDATE CASCADE ON DELETE RESTRICT,
								ADD CONSTRAINT	fk_servicerateplan_preselection_provisioning_request_id FOREIGN KEY (preselection_provisioning_request_id) REFERENCES ProvisioningRequest (Id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL' => "	ALTER TABLE 		ServiceRatePlan
									DROP FOREIGN KEY	fk_servicerateplan_fullservice_provisioning_request_id,
									DROP FOREIGN KEY	fk_servicerateplan_preselection_provisioning_request_id,
									DROP COLUMN			fullservice_provisioning_request_id,
									DROP COLUMN			preselection_provisioning_request_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			)
		);
		
		// Perform Batch Rollout
		$iRolloutVersionNumber	= self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation) {
			$iStepNumber++;
			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");

			// Attempt to apply changes
			$oResult = Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (MDB2::isError($oResult)) {
				throw new Exception(__CLASS__." Failed to {$aOperation['sDescription']}. ".$oResult->getMessage()." (DB Error: ".$oResult->getUserInfo().")");
			}

			// Append to Rollback Scripts (if one or more are provided)
			if (array_key_exists('sRollbackSQL', $aOperation)) {
				$aRollbackSQL = (is_array($aOperation['sRollbackSQL'])) ? $aOperation['sRollbackSQL'] : array($aOperation['sRollbackSQL']);
				foreach ($aRollbackSQL as $sRollbackQuery) {
					if (trim($sRollbackQuery)) {
						$this->rollbackSQL[] = $sRollbackQuery;
					}
				}
			}
		}
	}

	function rollback() {
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		if (count($this->rollbackSQL)) {
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--) {
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result)) {
					throw new Exception(__CLASS__.' Failed to rollback: '.$this->rollbackSQL[$l].'. '.$result->getMessage());
				}
			}
		}
	}
}

?>
