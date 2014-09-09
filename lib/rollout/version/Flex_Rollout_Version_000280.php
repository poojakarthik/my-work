<?php
class Flex_Rollout_Version_000280 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		// Define operations
		$aOperations = array(
			array(
				'sDescription' => "Add report_constraint_type table",
				'sAlterSQL' => "
					CREATE TABLE `report_constraint_type` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` VARCHAR(256) NOT NULL,
						`description` VARCHAR(256) NOT NULL,
						`const_name` VARCHAR(1000) NOT NULL,
						PRIMARY KEY (`id`))
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_constraint_type` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Populate report_constraint_type table",
				'sAlterSQL' => "
					INSERT INTO report_constraint_type 
						(name, description, const_name)
					VALUES
						('Free Text', 'Free Text', 'REPORT_CONSTRAINT_TYPE_FREETEXT'),
						('Database List', 'Database List', 'REPORT_CONSTRAINT_TYPE_DATABASELIST'),
						('Date', 'Date', 'REPORT_CONSTRAINT_TYPE_DATE');",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_constraint table",
				'sAlterSQL' => "
					CREATE TABLE `report_constraint` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`report_id` INT UNSIGNED NOT NULL,
						`name` VARCHAR(256) NOT NULL,
						`report_constraint_type_id` INT UNSIGNED NOT NULL,
						`source_query` VARCHAR(5000) NULL,
						`validation_regex` VARCHAR(200) NULL,
						`placeholder` VARCHAR(100) NULL,
						PRIMARY KEY (`id`),
						INDEX `fk_report_constraint_report_id` (`report_id` ASC),
						INDEX `fk_report_constraint_report_constraint_type_id` (`report_constraint_type_id` ASC),
						CONSTRAINT `fk_report_constraint_report_id`
							FOREIGN KEY (`report_id`)
							REFERENCES `report` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_constraint_report_constraint_type_id`
							FOREIGN KEY (`report_constraint_type_id`)
							REFERENCES `report_constraint_type` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE)
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_constraint` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_schedule_constraint_value table",
				'sAlterSQL' => "
					CREATE TABLE `report_schedule_constraint_value` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`report_constraint_id` INT UNSIGNED NOT NULL,
						`report_schedule_id` INT UNSIGNED NOT NULL,
						`value` VARCHAR(1000) NOT NULL,
						PRIMARY KEY (`id`),
						INDEX `fk_report_schedule_constraint_value_report_constraint_id` (`report_constraint_id` ASC),
						INDEX `fk_report_schedule_constraint_value_report_schedule_id` (`report_schedule_id` ASC),
						CONSTRAINT `fk_report_schedule_constraint_value_report_constraint_id`
							FOREIGN KEY (`report_constraint_id`)
							REFERENCES `report_constraint` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_schedule_constraint_value_report_schedule_id`
							FOREIGN KEY (`report_schedule_id`)
							REFERENCES `report_schedule` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE)
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_schedule_constraint_value` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			)
		);


		// Perform Batch Rollout
		$iRolloutVersionNumber	= self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation) {
			$iStepNumber++;
			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");
			// Attempt to apply changes
			$oResult	= Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (MDB2::isError($oResult)) {
				throw new Exception(__CLASS__ . " Failed to {$aOperation['sDescription']}. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
			}
			// Append to Rollback Scripts (if one or more are provided)
			if (array_key_exists('sRollbackSQL', $aOperation)) {
				$aRollbackSQL	= (is_array($aOperation['sRollbackSQL'])) ? $aOperation['sRollbackSQL'] : array($aOperation['sRollbackSQL']);
				foreach ($aRollbackSQL as $sRollbackQuery) {
					if (trim($sRollbackQuery)) {
						$this->rollbackSQL[] =	$sRollbackQuery;
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
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>