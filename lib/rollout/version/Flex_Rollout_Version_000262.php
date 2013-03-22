<?php

/**
 * Version 262 of database update.
 *
 * Description:
 * Alter the flex_config table, to add support for a configurable logo.
 *
 * NEW Columns: 
 *	- logo_mime_type (Char 11, The Image Mime Type)
 *	- logo (Medium BLOB, The Image)
 *
 */

class Flex_Rollout_Version_000262 extends Flex_Rollout_Version {
	private $rollbackSQL = array();

	public function rollout() {

		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$sFlexLogo = file_get_contents(dirname(__FILE__) . "/../support/img/flex_logo.png");

		if ($sFlexLogo === false) { 
			throw new Exception(__CLASS__." Failed to load flex logo content from the support directory.");
		}

		$aOperations = array(
			// Alter table, add columns
			array(
				'sDescription'		=> "Alter the flex_config table, to add support for a configurable logo.",
				'sAlterSQL'			=> "ALTER TABLE flex_config
										ADD COLUMN logo MEDIUMBLOB NULL,
										ADD COLUMN logo_mime_type VARCHAR(50) NULL;",
				'sRollbackSQL'		=> "ALTER TABLE flex_config
										DROP COLUMN	logo,
										DROP COLUMN	logo_mime_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			// Insert new Logo
			array(
				'sDescription'		=> "Insert the logo into the flex_config table.",
				'sAlterSQL'			=> "INSERT INTO	flex_config (created_by, created_on, internal_contact_list_html, logo, logo_mime_type)
										SELECT		" . Employee::SYSTEM_EMPLOYEE_ID . ", NOW(), internal_contact_list_html, " . $dbAdmin->quote($sFlexLogo) . ", 'image/png'
										FROM		flex_config
										ORDER BY	id DESC
										LIMIT		1",
				'sRollbackSQL'		=> "DELETE FROM		flex_config
										ORDER BY		id DESC
										LIMIT			1;",
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

?>
