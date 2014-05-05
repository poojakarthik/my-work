<?php

/**
 * Version 271 of database update.
 */

class Flex_Rollout_Version_000271 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {
		// Define operations
		$aOperations = array(

			array(
				'sDescription' => "Add default_record_type_visibility column to CustomerGroup",
				'sAlterSQL' => "ALTER TABLE 	CustomerGroup
								ADD COLUMN 		default_record_type_visibility 		BIGINT UNSIGNED NULL;",
				'sRollbackSQL' => "	ALTER TABLE 		CustomerGroup
									DROP COLUMN 		default_record_type_visibility;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription' => "Set default_record_type_visibility to ON for all CustomerGroups",
				'sAlterSQL' => "UPDATE 		CustomerGroup
								SET			default_record_type_visibility=1;",
				'sRollbackSQL' => "UPDATE 	CustomerGroup
									SET 	default_record_type_visibility=NULL;",
				'sDataSourceName' => FLEX_DATABASE_CONNECTION_ADMIN
			),
			/* Show Bill Itemisation setting for Customer Groups */
			array(
				'sDescription'		=> "Create customer_group_record_type_visibility table",
				'sAlterSQL'			=> "CREATE TABLE customer_group_record_type_visibility (
											id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Customer Group Record Type',
											customer_group_id BIGINT NOT NULL COMMENT 'Customer Group',
											record_type_id BIGINT UNSIGNED NOT NULL COMMENT 'Record Type',
											is_visible TINYINT	UNSIGNED NOT NULL COMMENT 'Visibility Status, 1=Visible, 0=Invisible',

											CONSTRAINT pk_customer_group_record_type_visibility_id PRIMARY KEY (id),
											CONSTRAINT fk_customer_group_record_type_visibility_customer_group_id FOREIGN KEY (customer_group_id) REFERENCES CustomerGroup(Id) ON UPDATE CASCADE ON DELETE RESTRICT,
											CONSTRAINT fk_customer_group_record_type_visibility_record_type_id FOREIGN KEY (record_type_id) REFERENCES RecordType(Id) ON UPDATE CASCADE ON DELETE RESTRICT
										) ENGINE=InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE customer_group_record_type_visibility;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			/* Show Bill Itemisation setting for Accounts */
			array(
				'sDescription'		=> "Create account_record_type_visibility table",
				'sAlterSQL'			=> "CREATE TABLE account_record_type_visibility (
											id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique Id for the Account Record Type',
											account_id BIGINT UNSIGNED NOT NULL COMMENT 'Account',
											record_type_id BIGINT UNSIGNED NOT NULL COMMENT 'Record Type',
											is_visible TINYINT	UNSIGNED NOT NULL COMMENT 'Visibility Status, 1=Visible, 0=Invisible',

											CONSTRAINT pk_account_record_type_visibility_id PRIMARY KEY (id),
											CONSTRAINT fk_account_record_type_visibility_account_id FOREIGN KEY (account_id) REFERENCES Account(Id) ON UPDATE CASCADE ON DELETE RESTRICT,
											CONSTRAINT fk_account_record_type_visibility_record_type_id FOREIGN KEY (record_type_id) REFERENCES RecordType(Id) ON UPDATE CASCADE ON DELETE RESTRICT
										) ENGINE=InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE account_record_type_visibility;",
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