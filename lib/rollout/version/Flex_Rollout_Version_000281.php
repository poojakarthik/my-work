<?php
class Flex_Rollout_Version_000281 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		// Define operations
		$aOperations = array(
			array(
				'sDescription' => "Add report_delivery_method table",
				'sAlterSQL' => "
					CREATE TABLE `report_delivery_method` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` VARCHAR(256) NOT NULL,
						`description` VARCHAR(256) NOT NULL,
						`const_name` VARCHAR(1000) NOT NULL,
						PRIMARY KEY (`id`))
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_delivery_method` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Populate report_delivery_method table",
				'sAlterSQL' => "
					INSERT INTO report_delivery_method 
						(name, description, const_name)
					VALUES
						('FTP', 'FTP Upload', 'REPORT_DELIVERY_METHOD_FTP'),
						('Email', 'Send as email', 'REPORT_DELIVERY_METHOD_EMAIL');",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_delivery_format table",
				'sAlterSQL' => "
					CREATE TABLE `report_delivery_format` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` VARCHAR(256) NOT NULL,
						`description` VARCHAR(256) NOT NULL,
						`const_name` VARCHAR(1000) NOT NULL,
						PRIMARY KEY (`id`))
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_delivery_format` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Populate report_delivery_format table",
				'sAlterSQL' => "
					INSERT INTO report_delivery_format 
						(name, description, const_name)
					VALUES
						('CSV', 'CSV file format', 'REPORT_DELIVERY_FORMAT_CSV'),
						('XLS', 'Excel file format', 'REPORT_DELIVERY_FORMAT_XLS');",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_category table",
				'sAlterSQL' => "
					CREATE TABLE `report_category` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`name` VARCHAR(256) NOT NULL,
						`description` VARCHAR(256) NOT NULL,
						PRIMARY KEY (`id`))
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_category` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_delivery_employee table",
				'sAlterSQL' => "
					CREATE TABLE `report_delivery_employee` (
						`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
						`report_schedule_id` INT UNSIGNED NOT NULL,
						`employee_id` BIGINT(20) UNSIGNED NOT NULL,
						`created_employee_id` BIGINT(20) UNSIGNED NOT NULL,
						`created_datetime` DATETIME NOT NULL,
						PRIMARY KEY (`id`),
						CONSTRAINT `fk_report_delivery_employee_report_schedule_id`
							FOREIGN KEY (`report_schedule_id`)
							REFERENCES `report_schedule` (`id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_delivery_employee_created_employee_id`
							FOREIGN KEY (`created_employee_id`)
							REFERENCES `Employee` (`Id`)
							ON DELETE RESTRICT
							ON UPDATE CASCADE,
						CONSTRAINT `fk_report_delivery_employee_employee_id`
							FOREIGN KEY (`employee_id`)
							REFERENCES `Employee` (`Id`)
							ON DELETE CASCADE
							ON UPDATE CASCADE)
					ENGINE = InnoDB;",
				'sRollbackSQL' => "	DROP TABLE `report_delivery_employee` ;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_delivery_format_id, report_delivery_method_id and schedule_end_datetime to report_schedule",
				//Set CSV as default delivery format and FTP as default delivery method
				'sAlterSQL' => 
							"ALTER TABLE report_schedule
								ADD COLUMN 		report_delivery_format_id INT UNSIGNED NOT NULL DEFAULT 1, 
								ADD COLUMN 		report_delivery_method_id INT UNSIGNED NOT NULL DEFAULT 1,
								ADD COLUMN 		schedule_end_datetime DATETIME NULL,
								ADD COLUMN 		filename VARCHAR(100) NULL,
								ADD CONSTRAINT	fk_report_schedule_report_delivery_format_id	FOREIGN KEY (report_delivery_format_id) REFERENCES report_delivery_format (id) 	ON UPDATE CASCADE ON DELETE RESTRICT,
								ADD CONSTRAINT 	fk_report_schedule_report_delivery_method_id 	FOREIGN KEY (report_delivery_method_id) REFERENCES report_delivery_method (id) 	ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL' => 
								"ALTER TABLE report_schedule
									DROP FOREIGN KEY 	fk_report_schedule_report_delivery_format_id,
									DROP FOREIGN KEY 	fk_report_schedule_report_delivery_method_id,
									DROP COLUMN 		report_delivery_format_id,
									DROP COLUMN 		report_delivery_method_id,
									DROP COLUMN 		schedule_end_datetime,
									DROP COLUMN 		filename;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Add report_category_id to report",
				//Set Adhoc as default report category
				'sAlterSQL' => 
							"ALTER TABLE report
								ADD COLUMN 		report_category_id INT UNSIGNED NOT NULL DEFAULT 7,
								ADD CONSTRAINT 	fk_report_report_category_id FOREIGN KEY (report_category_id) 	REFERENCES report_category (id) 		ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL' => 
								"ALTER TABLE report_schedule
									DROP FOREIGN KEY 	fk_report_report_category_id,
									DROP COLUMN 		report_category_id;",
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