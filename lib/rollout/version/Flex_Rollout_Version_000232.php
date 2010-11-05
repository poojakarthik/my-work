<?php

/**
 * Version 232 of database update
 */

class Flex_Rollout_Version_000232 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table payment_request_status",
									'sAlterSQL'			=>	"	CREATE TABLE payment_request_status
																(
																	id			INT 			NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
																	name		VARCHAR(128)	NOT NULL					COMMENT \"Name of the status\",
																	description	VARCHAR(128)	NOT NULL					COMMENT \"Description of the status\",
																	const_name	VARCHAR(128)	NOT NULL					COMMENT \"Constant alias for the status\",
																	system_name	VARCHAR(128)	NOT NULL					COMMENT \"System name for the status\",
																	PRIMARY KEY (id)
																) ENGINE=InnoDB, COMMENT=\"The status of a payment request\";",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS payment_request_status;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table payment_request",
									'sAlterSQL'			=>	"	CREATE TABLE payment_request
																(
																	id							BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
																	account_id					BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) Account. The Account that the request is for\",
																	amount						DECIMAL(13,4)	NOT NULL					COMMENT \"The amount of the payment which is being requested\",
																	payment_type_id				BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) payment_type. The type of payment which is being requested\",
																	payment_request_status_id	INT				NOT NULL					COMMENT \"(FK) payment_request_status. The status of the request\",
																	invoice_run_id				BIGINT UNSIGNED	NULL						COMMENT \"(FK) InvoiceRun. An optional Invoice Run that the payment request originated from\",
																	file_export_id				BIGINT UNSIGNED	NULL						COMMENT \"(FK) FileExport. The file export record that shows details of the request post-export\",
																	created_datetime			DATETIME		NOT NULL					COMMENT \"When the record was created\",
																	created_employee_id			BIGINT UNSIGNED NOT NULL					COMMENT \"(FK) Employee. The Employee that created the record\",
																	PRIMARY KEY (id),
																	CONSTRAINT	fk_payment_request_account_id					FOREIGN KEY	(account_id)				REFERENCES	Account (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_payment_request_payment_type_id				FOREIGN KEY	(payment_type_id)			REFERENCES	payment_type (id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_payment_request_payment_request_status_id	FOREIGN KEY	(payment_request_status_id)	REFERENCES	payment_request_status (id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_payment_request_invoice_run_id				FOREIGN KEY	(invoice_run_id)			REFERENCES	InvoiceRun (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_payment_request_file_export_id				FOREIGN KEY	(file_export_id)			REFERENCES	FileExport (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
																	CONSTRAINT	fk_payment_request_created_employee_id			FOREIGN KEY	(created_employee_id)		REFERENCES	Employee (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT
																) ENGINE=InnoDB, COMMENT=\"A payment request, to be dispatched as soon as possible\";",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS payment_request;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Populate the payment_request_status table",
									'sAlterSQL'			=>	"	INSERT INTO	payment_request_status (name, description, const_name, system_name)
																VALUES		('Pending', 	'Request is awaiting dispatch', 						'PAYMENT_REQUEST_STATUS_PENDING', 		'PENDING'),
																			('Dispatched', 	'Request has been dispatched', 							'PAYMENT_REQUEST_STATUS_DISPATCHED', 	'DISPATCHED'),
																			('Cancelled',	'Request has been cancelled, will not be dispatched', 	'PAYMENT_REQUEST_STATUS_CANCELLED', 	'CANCELLED');",
									'sRollbackSQL'		=>	"	TRUNCATE payment_request_status;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add new payment_type 'EFT One-Shot'",
									'sAlterSQL'			=>	"	INSERT INTO	payment_type (name, description, const_name)
																VALUES		('EFT One-Shot', 'EFT One-Shot', 'PAYMENT_TYPE_EFT_ONE_SHOT');",
									'sRollbackSQL'		=>	"	DELETE FROM payment_type WHERE const_name = 'PAYMENT_TYPE_EFT_ONE_SHOT';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add new action_type 'EFT One Time Payment'",
									'sAlterSQL'			=>	"	INSERT INTO	action_type (name, description, action_type_detail_requirement_id, is_automatic_only, is_system, active_status_id)
																VALUES		(
																				'EFT One Time Payment', 
																				'EFT One Time Payment', 
																				(SELECT id FROM action_type_detail_requirement WHERE const_name = 'ACTION_TYPE_DETAIL_REQUIREMENT_REQUIRED'),
																				1, 
																				1, 
																				(SELECT id FROM active_status WHERE const_name = 'ACTIVE_STATUS_ACTIVE')
																			);",
									'sRollbackSQL'		=>	"	DELETE FROM action_type WHERE name = 'EFT One Time Payment';",
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