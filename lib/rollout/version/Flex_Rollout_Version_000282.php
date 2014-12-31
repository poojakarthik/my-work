<?php
class Flex_Rollout_Version_000282 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	const BIGINT_SIGNED_MAX			= '9223372036854775807';
	const BIGINT_UNSIGNED_MAX		= '18446744073709551615';
	const MAX_VALUE_FOR_VALID_UNIT	= '9000000000000000000';

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription' => "UPDATE ServiceTypeTotal.Units value to be within the SIGNED range",
				'sAlterSQL' => "
					UPDATE ServiceTypeTotal
					SET Units = (" . self::BIGINT_SIGNED_MAX . " - (" . self::BIGINT_UNSIGNED_MAX . " - ServiceTypeTotal.Units))
					WHERE Units > " . self::MAX_VALUE_FOR_VALID_UNIT . "
				",
				'sRollbackSQL' => "
					UPDATE ServiceTypeTotal
					SET Units = " . self::BIGINT_UNSIGNED_MAX . " - (" . self::BIGINT_SIGNED_MAX . " - ServiceTypeTotal.Units)
					WHERE Units > " . self::MAX_VALUE_FOR_VALID_UNIT . "
				",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "MODIFY table column Units to SIGNED.",
				'sAlterSQL' => "
					ALTER TABLE ServiceTypeTotal
					MODIFY Units BIGINT SIGNED NOT NULL;
				",
				'sRollbackSQL' => "
					ALTER TABLE ServiceTypeTotal
					MODIFY Units BIGINT(20) UNSIGNED NOT NULL;
				",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Recalculate correct SIGNED values for previously Underflowed values.",
				'sAlterSQL' => "
					UPDATE ServiceTypeTotal
					SET Units = (ServiceTypeTotal.Units - " . self::BIGINT_SIGNED_MAX . ") - 1
					WHERE Units > " . self::MAX_VALUE_FOR_VALID_UNIT . "
				",
				'sRollbackSQL' => "
					UPDATE ServiceTypeTotal
					SET Units = (ServiceTypeTotal.Units + " . self::BIGINT_SIGNED_MAX . ") + 1
					WHERE Units > " . self::MAX_VALUE_FOR_VALID_UNIT . "
				",
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