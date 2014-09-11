<?php

class Flex_Rollout_Version_000265 extends Flex_Rollout_Version {
	private $aRollbackSQL = array();

	public function rollout() {
		$aOperations = array(
			array(
				'sDescription' => "Add new mobile provisioning types PROVISIONING_TYPE_MOBILE_ADD, PROVISIONING_TYPE_MOBILE_REMOVE, PROVISIONING_TYPE_MOBILE_PLAN_CHANGE",
				'sAlterSQL' => "INSERT INTO provisioning_type (name, inbound, outbound, provisioning_type_nature, description, const_name)
								VALUES ('Add Mobile', 1, 1, (SELECT id FROM provisioning_type_nature WHERE const_name = 'REQUEST_TYPE_NATURE_MOBILE'), 'Add Mobile request', 'PROVISIONING_TYPE_MOBILE_ADD'),
									('Remove Mobile', 1, 1, (SELECT id FROM provisioning_type_nature WHERE const_name = 'REQUEST_TYPE_NATURE_MOBILE'), 'Remove Mobile request', 'PROVISIONING_TYPE_MOBILE_REMOVE'),
									('Mobile Plan Change', 0, 1, (SELECT id FROM provisioning_type_nature WHERE const_name = 'REQUEST_TYPE_NATURE_MOBILE'), 'Mobile Plan Change Request', 'PROVISIONING_TYPE_MOBILE_PLAN_CHANGE')",
				'sRollbackSQL' => "	DELETE FROM provisioning_type
									WHERE const_name in ('PROVISIONING_TYPE_MOBILE_ADD', 'PROVISIONING_TYPE_MOBILE_REMOVE', 'PROVISIONING_TYPE_MOBILE_PLAN_CHANGE')",
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