<?php
class Flex_Rollout_Version_000268 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription' => "Add service_rate table",
				'sAlterSQL' => "
					CREATE TABLE service_rate (
						id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						service_id BIGINT(20) UNSIGNED NOT NULL,
						rate_id BIGINT(20) UNSIGNED NOT NULL,
						created_employee_id BIGINT(20) UNSIGNED NOT NULL,
						created_datetime DATETIME NOT NULL,
						start_datetime DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
						end_datetime DATETIME NOT NULL DEFAULT '9999-12-31 23:59:59',

						CONSTRAINT pk_service_rate PRIMARY KEY (id),
						CONSTRAINT fk_service_rate_service_id FOREIGN KEY (service_id) REFERENCES Service(Id) ON UPDATE CASCADE ON DELETE CASCADE,
						CONSTRAINT fk_service_rate_rate_id FOREIGN KEY (rate_id) REFERENCES Rate(Id) ON UPDATE CASCADE ON DELETE RESTRICT,
						CONSTRAINT fk_service_rate_created_employee_id FOREIGN KEY (created_employee_id) REFERENCES Employee(Id) ON UPDATE CASCADE ON DELETE RESTRICT
					) ENGINE=InnoDB;
				",
				'sRollbackSQL' => "DROP TABLE service_rate;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
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