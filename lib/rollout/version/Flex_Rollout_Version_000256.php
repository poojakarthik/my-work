<?php

/**
 * Version 256 of database update, based on template version 250.
 *
 * Description:
 * 1. Adds support for multiple employee message type(s) by adding a new 'employee_messsage_type' table
 * 2. Updates the existing employee_message table to reference a foreign key from the employee_messsage_type table.
 */

class Flex_Rollout_Version_000256 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout() {
		// Define operations
		$aOperations = array(
			// Create employee_message_type table
			array(
				'sDescription'		=> "Create employee_message_type table",
				'sAlterSQL'			=> "CREATE TABLE employee_message_type (
										       id INT UNSIGNED NOT NULL AUTO_INCREMENT,
										       name VARCHAR(50) NOT NULL,
										       description VARCHAR(100) NOT NULL,
										       system_name VARCHAR(50) NOT NULL,
										       const_name VARCHAR(100) NOT NULL,

										       CONSTRAINT pk_employee_message_type_id PRIMARY KEY (id)
										) ENGINE=InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE employee_message_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			// Insert Constant values
			array(
				'sDescription'		=> "Insert Constant values",
				'sAlterSQL'			=> "INSERT INTO employee_message_type
										       (name, description, system_name, const_name)
										VALUES
										       ('General', 'General Notices', 'GENERAL', 'EMPLOYEE_MESSAGE_TYPE_GENERAL'),
										       ('Technical', 'Technical Notices', 'TECHNICAL', 'EMPLOYEE_MESSAGE_TYPE_TECHNICAL');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			// Add employee_message.employee_message_type_id foreign key (NULLable for now)
			array(
				'sDescription'		=> "Add employee_message.employee_message_type_id foreign key (NULLable for now)",
				'sAlterSQL'			=> "ALTER TABLE employee_message
										ADD COLUMN employee_message_type_id INT UNSIGNED NULL,
										ADD CONSTRAINT fk_employee_message_employee_message_type_id FOREIGN KEY (employee_message_type_id) REFERENCES employee_message_type(id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL'		=> "ALTER TABLE employee_message
										DROP FOREIGN KEY fk_employee_message_employee_message_type_id,
										DROP COLUMN employee_message_type_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			// Set all existing records to GENERAL
			array(
				'sDescription'		=> "Set all existing records to GENERAL",
				'sAlterSQL'			=> "UPDATE		employee_message
										SET			employee_message_type_id = (SELECT id FROM employee_message_type WHERE system_name = 'GENERAL');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			// Set employee_message.employee_message_type_id to NOT NULL
			array(
				'sDescription'		=> "Set employee_message.employee_message_type_id to NOT NULL",
				'sAlterSQL'			=> "ALTER TABLE employee_message
										MODIFY COLUMN employee_message_type_id INT UNSIGNED NOT NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
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