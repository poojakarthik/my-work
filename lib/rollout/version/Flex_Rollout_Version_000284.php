<?php
class Flex_Rollout_Version_000284 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription' => "Change Service.CappedCharge to DEFAULT 0",
				'sAlterSQL' => "
					ALTER TABLE Service
						MODIFY COLUMN CappedCharge DECIMAL(13,4) NOT NULL DEFAULT 0;",
				'sRollbackSQL' => "
					ALTER TABLE Service
						MODIFY COLUMN CappedCharge DECIMAL(13,4) NOT NULL;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Change Service.UncappedCharge to DEFAULT 0",
				'sAlterSQL' => "
					ALTER TABLE Service
						MODIFY COLUMN UncappedCharge DECIMAL(13,4) NOT NULL DEFAULT 0;",
				'sRollbackSQL' => "
					ALTER TABLE Service
						MODIFY COLUMN UncappedCharge DECIMAL(13,4) NOT NULL;",
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