<?php

class Flex_Rollout_Version_000264 extends Flex_Rollout_Version {
	private $aRollbackSQL = array();

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription' => "Drop collection_event_report_output.file_type_id",
				'sAlterSQL' => "ALTER TABLE collection_event_report_output
								DROP FOREIGN KEY fk_collection_event_report_output_file_type_id,
								DROP COLUMN file_type_id",
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
						$this->aRollbackSQL[] = $sRollbackQuery;
					}
				}
			}
		}
	}

	function rollback() {
		$oDB = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		if (count($this->aRollbackSQL)) {
			for ($l = count($this->aRollbackSQL) - 1; $l >= 0; $l--) {
				$result = $oDB->query($this->aRollbackSQL[$l]);
				if (MDB2::isError($result)) {
					throw new Exception(__CLASS__.' Failed to rollback: '.$this->aRollbackSQL[$l].'. '.$result->getMessage());
				}
			}
		}
	}
}

?>
