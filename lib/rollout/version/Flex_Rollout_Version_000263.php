<?php

class Flex_Rollout_Version_000263 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		$oDB = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		$mResult = Query::run("	SELECT 	css.system_name, cssc.collection_scenario_id
								FROM 	collection_scenario_system css
								JOIN	collection_scenario_system_config cssc ON (
											cssc.collection_scenario_system_id = css.id
											AND NOW() BETWEEN cssc.start_datetime AND cssc.end_datetime
											AND css.system_name IS NOT NULL
											AND css.system_name <> ''
										);");
		$aScenarioIdBySystemName = array();
		while ($aRow = $mResult->fetch_assoc()) {
			$aScenarioIdBySystemName[$aRow['system_name']] = $aRow['collection_scenario_id'];
		}

		$sBrokenPromiseScenarioId = $oDB->prepareByPHPType($aScenarioIdBySystemName['BROKEN_PROMISE_TO_PAY']);
		$sDishonouredPaymentScenarioId = $oDB->prepareByPHPType($aScenarioIdBySystemName['DISHONOURED_PAYMENT']);

		$aOperations = array(
			array(
				'sDescription' => "Add broken_promise_collection_scenario_id and dishonoured_payment_collection_scenario_id to collection_scenario (leave nullable until populated).",
				'sAlterSQL' => "ALTER TABLE 	collection_scenario
								ADD COLUMN 		broken_promise_collection_scenario_id 		BIGINT NULL,
								ADD COLUMN 		dishonoured_payment_collection_scenario_id 	BIGINT NULL,
								ADD CONSTRAINT	fk_collection_scenario_broken_promise_collection_scenario_id 		FOREIGN KEY (broken_promise_collection_scenario_id) 		REFERENCES collection_scenario (id) ON UPDATE CASCADE ON DELETE RESTRICT,
								ADD CONSTRAINT 	fk_collection_scenario_dishonoured_payment_collection_scenario_id, 	FOREIGN KEY (dishonoured_payment_collection_scenario_id) 	REFERENCES collection_scenario (id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL' => "	ALTER TABLE 	collection_scenario
									DROP CONSTRAINT fk_collection_scenario_broken_promise_collection_scenario_id,
									DROP CONSTRAINT fk_collection_scenario_dishonoured_payment_collection_scenario_id,
									DROP COLUMN 	broken_promise_collection_scenario_id,
									DROP COLUMN 	dishonoured_payment_collection_scenario_id;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Set initial broken promise and dishonoured payment scenarios to the currently configured system ones",
				'sAlterSQL' => "UPDATE	collection_scenario
								SET		broken_promise_collection_scenario_id = {$sBrokenPromiseScenarioId},
										dishonoured_payment_collection_scenario_id = {$sDishonouredPaymentScenarioId};"
			),
			array(
				'sDescription' => "Make broken_promise_collection_scenario_id and dishonoured_payment_collection_scenario_id nullable",
				'sAlterSQL' => "ALTER TABLE collection_scenario
								CHANGE 		broken_promise_collection_scenario_id 		BIGINT NOT NULL,
								CHANGE 		dishonoured_payment_collection_scenario_id 	BIGINT NOT NULL;"
			)/*,
			array(
				'sDescription' => "Remove the collection_scenario_system_config table",
				'sAlterSQL' => "DROP TABLE collection_scenario_system_config;"
			),
			array(
				'sDescription' => "Remove the collection_scenario_system table",
				'sAlterSQL' => "DROP TABLE collection_scenario_system;"
			)*/
		);
		
		// Perform Batch Rollout
		$iRolloutVersionNumber = self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber = 0;
		foreach ($aOperations as $aOperation) {
			$iStepNumber++;
			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");

			// Attempt to apply changes
			$oResult = Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (PEAR::isError($oResult)) {
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
		$oDB = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		if (count($this->rollbackSQL)) {
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--) {
				$result = $oDB->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result)) {
					throw new Exception(__CLASS__.' Failed to rollback: '.$this->rollbackSQL[$l].'. '.$result->getMessage());
				}
			}
		}
	}
}

?>
