<?php
class Flex_Rollout_Version_000279 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		// Define operations
		$aOperations = array(

			array(
				'sDescription' => "Add report table",
				'sAlterSQL' => "
					CREATE TABLE `report` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` VARCHAR(256) NOT NULL,
						`summary` VARCHAR(512) NULL,
						`query` VARCHAR(10000) NOT NULL,
						`created_datetime` DATETIME NOT NULL,
						`created_employee_id` BIGINT(20) UNSIGNED NOT NULL,
						`is_enabled` TINYINT NOT NULL,
						PRIMARY KEY (`id`),
						CONSTRAINT `fk_report_created_employee_id`
							FOREIGN KEY (`created_employee_id`)
							REFERENCES `Employee` (`Id`)
							ON DELETE RESTRICT
							ON UPDATE CASCADE)
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_frequency_type table",
				'sAlterSQL' => "
					CREATE TABLE `report_frequency_type` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` VARCHAR(256) NOT NULL,
						`description` VARCHAR(256) NOT NULL,
						`const_name` VARCHAR(1000) NOT NULL,
						PRIMARY KEY (`id`))
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_frequency_type` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Populate report_frequency_type table",
				'sAlterSQL' => "
					INSERT INTO report_frequency_type 
						(name, description, const_name)
					VALUES
						('Day', 'Day', 'REPORT_FREQUENCY_TYPE_DAY'),
						('Month', 'Month', 'REPORT_FREQUENCY_TYPE_MONTH');",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_schedule table",
				'sAlterSQL' => "
					CREATE TABLE `report_schedule` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`report_id` INT UNSIGNED NULL,
						`report_frequency_type_id` INT UNSIGNED NULL,
						`frequency_multiple` INT UNSIGNED NULL,
						`schedule_datetime` DATETIME NOT NULL,
						`is_enabled` TINYINT NOT NULL,
						`compiled_query` VARCHAR(15000) NOT NULL,
						`scheduled_employee_id` BIGINT(20) UNSIGNED NOT NULL,
						`scheduled_datetime` DATETIME NOT NULL,
						PRIMARY KEY (`id`),
						INDEX `fk_report_schedule_report_id` (`report_id` ASC),
						INDEX `fk_report_schedule_report_frequency_type_id` (`report_frequency_type_id` ASC),
						CONSTRAINT `fk_report_schedule_report_id`
							FOREIGN KEY (`report_id`)
							REFERENCES `report` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_schedule_report_frequency_type_id`
							FOREIGN KEY (`report_frequency_type_id`)
							REFERENCES `report_frequency_type` (`id`)
							ON DELETE RESTRICT
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_schedule_scheduled_employee_id`
							FOREIGN KEY (`scheduled_employee_id`)
							REFERENCES `Employee` (`Id`)
							ON DELETE RESTRICT
							ON UPDATE CASCADE)
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_schedule` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_schedule_log table",
				'sAlterSQL' => "
					CREATE TABLE `report_schedule_log` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`report_schedule_id` INT UNSIGNED NOT NULL,
						`executed_datetime` DATETIME NOT NULL,
						`is_error` TINYINT NOT NULL,
						`download_path` VARCHAR(200) NULL,
						PRIMARY KEY (`id`),
						INDEX `fk_report_schedule_log_report_schedule_id` (`report_schedule_id` ASC),
						CONSTRAINT `fk_report_schedule_log_report_schedule_id`
							FOREIGN KEY (`report_schedule_id`)
							REFERENCES `report_schedule` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE)
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_schedule_log` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			/* Constraint Rollout put on hold until business needs them
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
			),
			*/
			array(
				'sDescription' => "Add report_employee table",
				'sAlterSQL' => "
					CREATE TABLE `report_employee` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`report_id` INT UNSIGNED NOT NULL,
						`employee_id` BIGINT UNSIGNED NOT NULL,
						`created_employee_id` BIGINT(20) UNSIGNED NOT NULL,
						`created_datetime` DATETIME NOT NULL,
						PRIMARY KEY (`id`),
						INDEX `fk_report_employee_report_id` (`report_id` ASC),
						CONSTRAINT `fk_report_employee_report_id`
							FOREIGN KEY (`report_id`)
							REFERENCES `report` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_employee_created_employee_id`
							FOREIGN KEY (`created_employee_id`)
							REFERENCES `Employee` (`Id`)
							ON DELETE RESTRICT
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_employee_employee_id`
							FOREIGN KEY (`employee_id`)
							REFERENCES `Employee` (`Id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE)
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_employee` ;",
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