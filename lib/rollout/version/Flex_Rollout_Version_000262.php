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
		$aOperations = array(
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
			array(
				'sDescription'		=> "Insert the logo into the flex_config table.",
				'sAlterSQL'			=> "UPDATE flex_config SET logo='data:image/png;base64,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', logo_mime_type='image/png'",
				'sRollbackSQL'		=> "UPDATE flex_config SET logo=NULL,logo_mime_type=NULL",
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
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		if (count($this->rollbackSQL)) {
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--) {
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result)) {
					throw new Exception(__CLASS__.' Failed to rollback: '.$this->rollbackSQL[$l].'. '.$result->getMessage());
				}
			}
		}
	}
}

?>
