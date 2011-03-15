<?php

/**
 * Version 231 of database update
 */

class Flex_Rollout_Version_000231 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table cdr_delinquent_writeoff",
									'sAlterSQL'			=>	"	CREATE TABLE cdr_delinquent_writeoff
																(
																	id					BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\", 
																	cdr_id				BIGINT			NOT NULL					COMMENT \"Deliquent CDR record that was written off\", 
																	created_datetime	DATETIME		NOT NULL					COMMENT \"When the write off occured\", 
																	created_employee_id	BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) Employee, who executed the write off\", 
																	PRIMARY KEY	(id),
																	CONSTRAINT fk_cdr_delinquent_writeoff_created_employee_id	FOREIGN KEY (created_employee_id)	REFERENCES Employee (Id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB, COMMENT=\"Log for a deliquent CDR record that has been written off.\";",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS cdr_delinquent_writeoff;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table email_account",
									'sAlterSQL'			=>	"	CREATE TABLE email_account
																(
																	id					BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\", 
																	email_id			BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) email, the email\",																	
																	account_id			BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) Account, the account that the email is linked to\", 
																	PRIMARY KEY	(id),
																	CONSTRAINT fk_email_account_email_id	FOREIGN KEY (email_id)		REFERENCES email (id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT fk_email_account_account_id	FOREIGN KEY (account_id)	REFERENCES Account (Id)	ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB, COMMENT=\"A relationship between an email and an Account.\";",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_account;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add email_queue_status table",
									'sAlterSQL'			=>	"	CREATE TABLE email_queue_status
																(
																	id			INT 			NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
																	name		VARCHAR(128)	NOT NULL					COMMENT \"Name of the status\",
																	description	VARCHAR(128)	NOT NULL					COMMENT \"Description of the status\",
																	const_name	VARCHAR(128)	NOT NULL					COMMENT \"Constant alias for the status\",
																	system_name	VARCHAR(128)	NOT NULL					COMMENT \"System name for the status\",
																	PRIMARY KEY (id)
																) ENGINE=InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_queue_status;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate email_queue_status table",
									'sAlterSQL'			=>	"	INSERT INTO email_queue_status (name, description, const_name, system_name)
																VALUES		('Scheduled', 'Scheduled for delivery', 'EMAIL_QUEUE_STATUS_SCHEDULED', 'SCHEDULED'),
																			('Delivered', 'Successfully delivered', 'EMAIL_QUEUE_STATUS_DELIVERED', 'DELIVERED'),
																			('Cancelled', 'Delivered cancelled', 	'EMAIL_QUEUE_STATUS_CANCELLED', 'CANCELLED');",
									'sRollbackSQL'		=>	"	TRUNCATE email_queue_status;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=> "Turn OFF foreign key checks",
									'sAlterSQL'			=> "SET foreign_key_checks = 0;",
									'sRollbackSQL'		=> "SET foreign_key_checks = 1;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add email_queue_status_id field to the email_queue table",
									'sAlterSQL'			=>	"	ALTER TABLE email_queue
																ADD COLUMN email_queue_status_id INT NOT NULL COMMENT \"(FK) email_queue_status. The status of the queue\";",
									'sRollbackSQL'		=>	"	ALTER TABLE email_queue 
																DROP COLUMN email_queue_status_id;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add FOREIGN KEY to email_queue_status_id field in the email_queue table",
									'sAlterSQL'			=>	"	ALTER TABLE email_queue
																ADD CONSTRAINT fk_email_queue_email_queue_status_id FOREIGN KEY (email_queue_status_id) REFERENCES email_queue_status (id) ON UPDATE CASCADE ON DELETE RESTRICT;",
									'sRollbackSQL'		=>	"	ALTER TABLE 		email_queue 
																DROP FOREIGN KEY 	fk_email_queue_email_queue_status_id;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Set the default email_queue_status_id field values in the email_queue table",
									'sAlterSQL'			=>	"	UPDATE	email_queue eq
																SET		eq.email_queue_status_id = IF(
																			email_queue_batch_id IS NULL,
																			(SELECT	id
																			FROM	email_queue_status
																			WHERE	system_name = 'SCHEDULED'),
																			(SELECT	id
																			FROM	email_queue_status
																			WHERE	system_name = 'DELIVERED')
																		);",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=> "Turn ON foreign key checks",
									'sAlterSQL'			=> "SET foreign_key_checks = 1;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add description field to the email_queue table",
									'sAlterSQL'			=>	"	ALTER TABLE email_queue
																ADD COLUMN description VARCHAR(512) NOT NULL COMMENT \"The description of the Email Queue\";",
									'sRollbackSQL'		=>	"	ALTER TABLE email_queue DROP COLUMN description;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
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