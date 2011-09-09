<?php

/**
 * Version 249 of database update.
 */

class Flex_Rollout_Version_000249 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations = array(
			array(
				'sDescription'		=> "Creates the `account_user` table, which holds Customer Portal accounts",
				'sAlterSQL'			=> "
					CREATE TABLE account_user (
						id			BIGINT		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						username	VARCHAR(30)				NOT NULL					COMMENT 'Unique Username',
						password	CHAR(40)				NOT NULL					COMMENT 'Password (SHA1 Hash)',
						given_name	VARCHAR(50)				NOT NULL					COMMENT 'Given/First/Christian Name',
						family_name	VARCHAR(50)				NULL						COMMENT 'Family/Last Name',
						email		VARCHAR(256)			NOT NULL					COMMENT 'Email Address (for password recovery)',
						account_id	BIGINT		UNSIGNED	NOT NULL					COMMENT '(FK) Account to which this User has access',
						status_id	BIGINT		UNSIGNED	NOT NULL					COMMENT '(FK) Active/Inactive Status',

						CONSTRAINT	pk_account_user_id			PRIMARY KEY (id),
						CONSTRAINT	fk_account_user_account_id	FOREIGN KEY (account_id)	REFERENCES Account(Id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
						CONSTRAINT	fk_account_user_status_id	FOREIGN KEY (status_id)		REFERENCES status(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					) ENGINE=InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE account_user;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=> "Creates the `account_user_log` table, which holds successful login attempts for Customer Portal users",
				'sAlterSQL'			=> "
					CREATE TABLE account_user_log (
						id					BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
						account_user_id		BIGINT	UNSIGNED	NOT NULL					COMMENT '(FK) Account User that logged in',
						created_datetime	DATETIME			NOT NULL					COMMENT 'Datetime of the login attempt',
						CONSTRAINT	pk_account_user_log_id					PRIMARY KEY (id),
						CONSTRAINT	fk_account_user_log_account_user_idd	FOREIGN KEY (account_user_id)	REFERENCES account_user(id)	ON UPDATE CASCADE	ON DELETE RESTRICT
					) ENGINE=InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE account_user_log;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			)
		);

		// Perform Batch Rollout
		$iRolloutVersionNumber	= self::getRolloutVersionNumber(__CLASS__);
		$iStepNumber			= 0;
		foreach ($aOperations as $aOperation)
		{
			$iStepNumber++;

			$this->outputMessage("Applying {$iRolloutVersionNumber}.{$iStepNumber}: {$aOperation['sDescription']}...\n");

			// Attempt to apply changes
			$oResult	= Data_Source::get($aOperation['sDataSourceName'])->query($aOperation['sAlterSQL']);
			if (PEAR::isError($oResult))
			{
				throw new Exception(__CLASS__ . " Failed to {$aOperation['sDescription']}. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
			}

			// Append to Rollback Scripts (if one or more are provided)
			if (array_key_exists('sRollbackSQL', $aOperation))
			{
				$aRollbackSQL	= (is_array($aOperation['sRollbackSQL'])) ? $aOperation['sRollbackSQL'] : array($aOperation['sRollbackSQL']);

				foreach ($aRollbackSQL as $sRollbackQuery)
				{
					if (trim($sRollbackQuery))
					{
						$this->rollbackSQL[] =	$sRollbackQuery;
					}
				}
			}
		}
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (PEAR::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>