<?php
class Flex_Rollout_Version_000283 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription' => "Change CDR.FNN to VARCHAR(512)",
				'sAlterSQL' => "
					ALTER TABLE CDR
						MODIFY COLUMN FNN VARCHAR(512) DEFAULT NULL;",
				'sRollbackSQL' => "
					ALTER TABLE CDR
						MODIFY COLUMN FNN CHAR(25) DEFAULT NULL;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Change ServiceTotal.FNN to VARCHAR(512)",
				'sAlterSQL' => "
					ALTER TABLE ServiceTotal
						MODIFY COLUMN FNN VARCHAR(512) NOT NULL;",
				'sRollbackSQL' => "
					ALTER TABLE ServiceTotal
						MODIFY COLUMN FNN CHAR(25) NOT NULL;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Change ServiceTypeTotal.FNN to VARCHAR(512)",
				'sAlterSQL' => "
					ALTER TABLE ServiceTypeTotal
						MODIFY COLUMN FNN VARCHAR(512) NOT NULL;",
				'sRollbackSQL' => "
					ALTER TABLE ServiceTypeTotal
						MODIFY COLUMN FNN CHAR(25) NOT NULL;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Change ProvisioningRequest.FNN to VARCHAR(512)",
				'sAlterSQL' => "
					ALTER TABLE ProvisioningRequest
						MODIFY COLUMN FNN VARCHAR(512) NOT NULL;",
				'sRollbackSQL' => "
					ALTER TABLE ProvisioningRequest
						MODIFY COLUMN FNN CHAR(25) NOT NULL;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Change ProvisioningResponse.FNN to VARCHAR(512)",
				'sAlterSQL' => "
					ALTER TABLE ProvisioningResponse
						MODIFY COLUMN FNN VARCHAR(512) DEFAULT NULL;",
				'sRollbackSQL' => "
					ALTER TABLE ProvisioningResponse
						MODIFY COLUMN FNN CHAR(25) DEFAULT NULL;",
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