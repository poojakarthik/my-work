<?php
class Flex_Rollout_Version_000269 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription' => "Add service_type.module & set service_type.const_name to NULLable",
				'sAlterSQL' => "
					ALTER TABLE service_type
						MODIFY COLUMN const_name VARCHAR(512) NULL,
						ADD COLUMN module VARCHAR(256) NULL COMMENT 'Implementation module name'
					;
				",
				'sRollbackSQL' => "
					ALTER TABLE service_type
						DROP COLUMN module,
						MODIFY COLUMN const_name VARCHAR(512) NOT NULL
					;
				",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),

			array(
				'sDescription' => "Populate service_type.module field",
				'sAlterSQL' => "
					UPDATE service_type
					SET module = (CASE
							WHEN const_name = 'SERVICE_TYPE_ADSL' THEN 'ADSL'
							WHEN const_name = 'SERVICE_TYPE_MOBILE' THEN 'Mobile'
							WHEN const_name = 'SERVICE_TYPE_LAND_LINE' THEN 'Landline'
							WHEN const_name = 'SERVICE_TYPE_INBOUND' THEN 'Inbound'
							WHEN const_name = 'SERVICE_TYPE_DIALUP' THEN 'Dialup'
							ELSE NULL
						END)
					;
				",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),

			array(
				'sDescription' => "Set service_type.module to NOT NULLable",
				'sAlterSQL' => "
					ALTER TABLE service_type
						MODIFY COLUMN module VARCHAR(256) NOT NULL
					;
				",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),

			array(
				'sDescription' => "Change Service.FNN to VARCHAR(2048)",
				'sAlterSQL' => "
					ALTER TABLE Service
						MODIFY COLUMN FNN VARCHAR(512) NOT NULL;
				",
				'sRollbackSQL' => "
					ALTER TABLE Service
						MODIFY COLUMN FNN CHAR(25) NOT NULL;
				",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),

			array(
				'sDescription' => "Add service_type_config table",
				'sAlterSQL' => "
					CREATE TABLE service_type_config (
						id BIGINT UNSIGNED NOT NULL,
						service_type_id BIGINT UNSIGNED NOT NULL,
						name VARCHAR(128) NOT NULL,
						value VARCHAR(32768) NULL,

						CONSTRAINT pk_service_type_config PRIMARY KEY (id),
						CONSTRAINT fk_service_type_config_service_type_id
							FOREIGN KEY (service_type_id)
							REFERENCES service_type(id)
							ON UPDATE CASCADE
							ON DELETE CASCADE,
						UNIQUE INDEX uq_service_type_config_servictypeename (service_type_id ASC, name ASC)
					) ENGINE=InnoDB;
				",
				'sRollbackSQL' => "DROP TABLE service_type_config;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),

			array(
				'sDescription' => "Add service_property table",
				'sAlterSQL' => "
					CREATE TABLE service_property (
						id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						service_id BIGINT(20) UNSIGNED NOT NULL,
						name VARCHAR(256) NOT NULL,
						value VARCHAR(32768) NULL,
						modified_employee_id BIGINT(20) UNSIGNED NOT NULL,
						modified_datetime DATETIME NOT NULL,
						UNIQUE INDEX uq_service_property_servicename (service_id ASC, name ASC),
						CONSTRAINT pk_service_property PRIMARY KEY (id),
						CONSTRAINT fk_service_property_service_id
							FOREIGN KEY (service_id)
							REFERENCES Service (Id)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						CONSTRAINT fk_service_property_modified_employee_id
							FOREIGN KEY (modified_employee_id)
							REFERENCES Employee (Id)
							ON DELETE CASCADE
							ON UPDATE CASCADE
					) ENGINE = InnoDB;
				",
				'sRollbackSQL' => "DROP TABLE service_property;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),

			array(
				'sDescription' => "Add service_property_history table",
				'sAlterSQL' => "
					CREATE TABLE service_property_history (
						id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						service_property_id BIGINT UNSIGNED NOT NULL,
						value VARCHAR(32768) NULL,
						modified_employee_id BIGINT(20) UNSIGNED NOT NULL,
						modified_datetime DATETIME NOT NULL,
						CONSTRAINT pk_service_property_history PRIMARY KEY (id),
						CONSTRAINT fk_service_property_history_service_property_id
							FOREIGN KEY (service_property_id)
							REFERENCES service_property (id)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						CONSTRAINT fk_service_property_history_modified_employee_id
							FOREIGN KEY (modified_employee_id)
							REFERENCES Employee (Id)
							ON DELETE CASCADE
							ON UPDATE CASCADE
					) ENGINE = InnoDB;
				",
				'sRollbackSQL' => "DROP TABLE service_property_history;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			)
		);

		// Perform Batch Rollout
		$iRolloutVersionNumber = self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber = 0;
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