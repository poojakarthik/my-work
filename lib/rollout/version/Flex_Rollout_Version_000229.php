<?php

/**
 * Version 229 of database update - email_queue tables
 */

class Flex_Rollout_Version_000229 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table email_status",
									'sAlterSQL'			=>	"	CREATE TABLE email_status 
																(
																	id 			INT 			NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	name 		VARCHAR(512)	NULL						COMMENT 'Name of the status',
																	description	VARCHAR(512)	NULL						COMMENT 'Description of the status',
																	const_name 	VARCHAR(512)	NULL						COMMENT 'Constant alias for the status',
																	system_name	VARCHAR(512)	NULL						COMMENT 'System name for the status',
																	CONSTRAINT pk_email_status	PRIMARY KEY (id)
																) ENGINE = InnoDB, COMMENT 'The status of an email that has been queued and scheduled for sending';",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_status;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Insert email_status data",
									'sAlterSQL'			=>	"	INSERT INTO email_status (name, description, const_name, system_name) 
																VALUES	('Awaiting Send', 	'Awaiting Send', 	'EMAIL_STATUS_AWAITING_SEND', 	'AWAITING_SEND'),
																		('Sent', 			'Sent', 			'EMAIL_STATUS_SENT', 			'SENT'),
																		('Not Sent', 		'Not Sent', 		'EMAIL_STATUS_NOT_SENT', 		'NOT_SENT'),
																		('Sending Failed', 	'Sending Failed', 	'EMAIL_STATUS_SENDING_FAILED', 	'SENDING_FAILED');",
									'sRollbackSQL'		=>	"	TRUNCATE email_status;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table email_queue_batch",
									'sAlterSQL'			=>	"	CREATE TABLE email_queue_batch 
																(
																	id 					BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	created_datetime 	DATETIME 		NOT NULL					COMMENT 'Timestamp for record creation',
																	CONSTRAINT pk_email_queue_batch	PRIMARY KEY (id)
																) ENGINE = InnoDB, COMMENT 'A batch of email queues that were delivered together';",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_queue_batch;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table email_queue",
									'sAlterSQL'			=>	"	CREATE TABLE email_queue
																(
																	id 						BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	scheduled_datetime 		DATETIME 		NOT NULL					COMMENT 'Datetime the queue is to be delivered',
																	delivered_datetime 		DATETIME 		NULL						COMMENT 'Datetime the queue was delivered',
																	email_queue_batch_id 	BIGINT UNSIGNED	NULL						COMMENT '(FK) email_queue_batch. The batch that the queue was placed in at delivery time',
																	created_datetime 		DATETIME 		NOT NULL					COMMENT 'Timestamp for record creation',
																	created_employee_id		BIGINT UNSIGNED	NOT NULL					COMMENT '(FK) Employee. Employee who created the record',
																	CONSTRAINT	pk_email_queue 						PRIMARY KEY (id),
																	CONSTRAINT 	fk_email_queue_email_queue_batch_id	FOREIGN KEY (email_queue_batch_id)	REFERENCES email_queue_batch (id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
																	CONSTRAINT 	fk_email_queue_created_employee_id 	FOREIGN KEY (created_employee_id) 	REFERENCES Employee (id)			ON DELETE RESTRICT	ON UPDATE CASCADE
																) ENGINE = InnoDB, COMMENT 'A queue of emails that is scheduled for delivery';",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_queue;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table email",
									'sAlterSQL'			=>	"	CREATE TABLE email 
																(
																	id 					BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	recipients			VARCHAR(16384) 	NOT NULL					COMMENT 'Recipients of the email',
																	sender 				VARCHAR(512) 	NOT NULL					COMMENT 'Sender of the email',
																	subject 			VARCHAR(1024) 	NOT NULL					COMMENT 'Subject of the email',
																	text 				VARCHAR(16384)	NOT NULL					COMMENT 'Text body of the email',
																	html 				VARCHAR(16384)	NULL						COMMENT 'HTML body of the email',
																	email_queue_id 		BIGINT UNSIGNED NOT NULL					COMMENT '(FK) email_queue. Queue that the email belongs to',
																	email_status_id 	INT 			NOT NULL					COMMENT '(FK) email_status. Current status',
																	created_datetime 	DATETIME 		NOT NULL					COMMENT 'Timestamp for record creation',
																	created_employee_id	BIGINT UNSIGNED	NOT NULL					COMMENT '(FK) Employee. Employee who created the record',
																	CONSTRAINT pk_email						PRIMARY KEY (id),
																	CONSTRAINT fk_email_created_employee_id	FOREIGN KEY (created_employee_id)	REFERENCES Employee (id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
																	CONSTRAINT fk_email_email_queue_id		FOREIGN KEY (email_queue_id)		REFERENCES email_queue (id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
																	CONSTRAINT fk_email_email_status_id		FOREIGN KEY (email_status_id)		REFERENCES email_status (id)	ON DELETE RESTRICT	ON UPDATE CASCADE
																) ENGINE = InnoDB, COMMENT 'An email that belongs to a queue';",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table email_attachment",
									'sAlterSQL'			=>	"	CREATE TABLE email_attachment 
																(
																	id 					BIGINT 			NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier',
																	content 			MEDIUMBLOB		NOT NULL					COMMENT 'Content of the attachment file',
																	mime_type 			VARCHAR(512) 	NOT NULL					COMMENT 'Mime type of the attachment content',
																	disposition 		VARCHAR(64) 	NULL						COMMENT 'Disposition of the attachment: attachment or inline',
																	encoding 			VARCHAR(128) 	NULL						COMMENT 'Encoding of the attachment: 7bit, 8bit, quoted-printable or base64',
																	filename 			VARCHAR(1024) 	NOT NULL					COMMENT 'Filename of the attachment',
																	email_id 			BIGINT UNSIGNED NOT NULL					COMMENT '(FK) email. The Email that the attachment is part of',
																	created_datetime 	DATETIME 		NOT NULL					COMMENT 'Timestamp for record creation',
																	created_employee_id	BIGINT UNSIGNED	NOT NULL					COMMENT '(FK) Employee. Employee who created the record',
																	CONSTRAINT pk_email_attachment						PRIMARY KEY (id),
																	CONSTRAINT fk_email_attachment_email_id				FOREIGN KEY (email_id)				REFERENCES email (id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
																	CONSTRAINT fk_email_attachment_created_employee_id	FOREIGN KEY (created_employee_id)	REFERENCES Employee (id)	ON DELETE RESTRICT	ON UPDATE CASCADE
																) ENGINE = InnoDB, COMMENT 'An attachment that is part of an email';",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_attachment;",
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