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
		$aOperations	=	
		array
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
												payment_id					BIGINT UNSIGNED	NULL						COMMENT \"(FK) Payment. The (optional) payment that this request is associated with\",
												created_datetime			DATETIME		NOT NULL					COMMENT \"When the record was created\",
												created_employee_id			BIGINT UNSIGNED NOT NULL					COMMENT \"(FK) Employee. The Employee that created the record\",
												PRIMARY KEY (id),
												CONSTRAINT	fk_payment_request_account_id					FOREIGN KEY	(account_id)				REFERENCES	Account (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT	fk_payment_request_payment_type_id				FOREIGN KEY	(payment_type_id)			REFERENCES	payment_type (id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT	fk_payment_request_payment_request_status_id	FOREIGN KEY	(payment_request_status_id)	REFERENCES	payment_request_status (id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT	fk_payment_request_invoice_run_id				FOREIGN KEY	(invoice_run_id)			REFERENCES	InvoiceRun (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT	fk_payment_request_file_export_id				FOREIGN KEY	(file_export_id)			REFERENCES	FileExport (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT	fk_payment_request_payment_id					FOREIGN KEY	(payment_id)				REFERENCES	Payment (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT	fk_payment_request_created_employee_id			FOREIGN KEY	(created_employee_id)		REFERENCES	Employee (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT
											) ENGINE=InnoDB, COMMENT=\"A payment request, to be dispatched as soon as possible\";",
				'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS payment_request;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add table file_import_data_status",
				'sAlterSQL'			=>	"	CREATE TABLE file_import_data_status
											(
												id			INT 			NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
												name		VARCHAR(128)	NOT NULL					COMMENT \"Name of the status\",
												description	VARCHAR(128)	NOT NULL					COMMENT \"Description of the status\",
												const_name	VARCHAR(128)	NOT NULL					COMMENT \"Constant alias for the status\",
												system_name	VARCHAR(128)	NOT NULL					COMMENT \"System name for the status\",
												PRIMARY KEY (id)
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS file_import_data_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Populate table file_import_data_status",
				'sAlterSQL'			=>	"	INSERT INTO file_import_data_status (name, description, const_name, system_name)
											VALUES		('Imported',				'Imported',				'FILE_IMPORT_DATA_STATUS_IMPORTED',				'IMPORTED'),
														('Normalised',				'Normalised',			'FILE_IMPORT_DATA_STATUS_NORMALISED',			'NORMALISED'),
														('Normalisation Failed',	'Normalisation Failed',	'FILE_IMPORT_DATA_STATUS_NORMALISATION_FAILED',	'NORMALISATION_FAILED'),
														('Ignored',					'Ignored',				'FILE_IMPORT_DATA_STATUS_IGNORED',				'IGNORED');",
				'sRollbackSQL'		=>	"	TRUNCATE file_import_data_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add table file_import_data",
				'sAlterSQL'			=>	"	CREATE TABLE file_import_data
											(
												id 							BIGINT UNSIGNED	NOT NULL 	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
												file_import_id 				BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) FileImport. The source file.\",
												sequence_no 				VARCHAR(128) 	NOT NULL					COMMENT \"Position within the source file.\",
												data 						VARCHAR(32767)	NULL						COMMENT \"Raw data, if null it has been archived and stored on disk\",
												file_import_data_status_id	INT				NOT NULL					COMMENT \"(FK) file_import_data_status\",
												reason						VARCHAR(512)	NULL						COMMENT \"(optional) Reason for the status\",
												PRIMARY KEY (id),
												CONSTRAINT fk_file_import_data_file_import_id 				FOREIGN KEY (file_import_id) 				REFERENCES FileImport (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_file_import_data_file_import_data_status_id 	FOREIGN KEY (file_import_data_status_id) 	REFERENCES file_import_data_status (id)	ON UPDATE CASCADE	ON DELETE RESTRICT
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS file_import_data;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add table payment_response",
				'sAlterSQL'			=>	"	CREATE TABLE payment_response
											(
												id						BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
												account_group_id		BIGINT UNSIGNED	NULL						COMMENT \"(FK) AccountGroup\",
												account_id				BIGINT UNSIGNED	NULL						COMMENT \"(FK) Account\",
												paid_date				DATE			NULL						COMMENT \"Effective date of the payment\",
												payment_type_id			BIGINT UNSIGNED	NULL						COMMENT \"(FK) payment_type\",
												amount					DECIMAL(13,4)	NULL						COMMENT \"Amount in dollars\",
												origin_id 				INT UNSIGNED	NULL						COMMENT \"Reference to the origin of the payment, e.g. CreditCard or DirectDebit id\",
												file_import_data_id		BIGINT UNSIGNED	NULL						COMMENT \"(FK) file_import_data, (optional) Raw payment data\",
												transaction_reference	VARCHAR(256)	NULL						COMMENT \"Transaction reference for the payment\",
												payment_id				BIGINT UNSIGNED	NULL						COMMENT \"(FK) Payment. The (optional) payment that this response is associated with\",
												payment_status_id		BIGINT UNSIGNED	NOT NULL					COMMENT \"(FK) payment_status\",
												created_datetime		DATETIME		NOT NULL					COMMENT \"Timestamp for creation\",
												PRIMARY KEY (id),
												CONSTRAINT fk_payment_response_account_group_id		FOREIGN KEY	(account_group_id)		REFERENCES	AccountGroup (Id)		ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_account_id			FOREIGN KEY	(account_id)			REFERENCES	Account (Id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_payment_type_id		FOREIGN KEY	(payment_type_id)		REFERENCES	payment_type (id)		ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_file_import_data_id	FOREIGN KEY (file_import_data_id)	REFERENCES	file_import_data (id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_payment_id			FOREIGN KEY	(payment_id)			REFERENCES	Payment (Id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_payment_status_id	FOREIGN KEY	(payment_status_id)		REFERENCES	payment_status (id)		ON UPDATE CASCADE	ON DELETE RESTRICT
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS payment_response;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add new payment_types 'Direct Debit via EFT' & 'Direct Debit via Credit Card'",
				'sAlterSQL'			=>	"	INSERT INTO	payment_type (name, description, const_name)
											VALUES		('Direct Debit via EFT', 			'Direct Debit via EFT',			'PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT'),
														('Direct Debit via Credit Card', 	'Direct Debit via Credit Card', 'PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD');",
				'sRollbackSQL'		=>	"	DELETE FROM payment_type WHERE const_name IN ('PAYMENT_TYPE_DIRECT_DEBIT_VIA_EFT', 'PAYMENT_TYPE_DIRECT_DEBIT_VIA_CREDIT_CARD');",
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
			),
			array
			(
				'sDescription'		=>	"Add new action_type_action_association_type for 'EFT One Time Payment' and ACTION_ASSOCIATION_TYPE_ACCOUNT",
				'sAlterSQL'			=>	"	INSERT INTO	action_type_action_association_type (action_type_id, action_association_type_id)
											VALUES		(
															(SELECT id FROM action_type WHERE name = 'EFT One Time Payment'),
															(SELECT id FROM action_association_type WHERE const_name = 'ACTION_ASSOCIATION_TYPE_ACCOUNT')
														);",
				'sRollbackSQL'		=>	"	DELETE FROM	action_type_action_association_type 
											WHERE 		action_type_id = (SELECT id FROM action_type WHERE name = 'EFT One Time Payment');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add created_datetime & latest_payment_response_id to the Payment table",
				'sAlterSQL'			=>	"	ALTER TABLE 	Payment	
											ADD COLUMN 		latest_payment_response_id 	BIGINT UNSIGNED NULL 		COMMENT \"(FK) payment_response\",
											ADD COLUMN		created_datetime			DATETIME		NOT NULL	COMMENT \"Timestamp for creation\";",
				'sRollbackSQL'		=>	"	ALTER TABLE 	Payment	
											DROP COLUMN		latest_payment_response_id,
											DROP COLUMN		created_datetime;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add foreign key constraint for latest_payment_response_id to the Payment table",
				'sAlterSQL'			=>	"	ALTER TABLE 	Payment	
											ADD CONSTRAINT	fk_Payment_latest_payment_response_id FOREIGN KEY (latest_payment_response_id) REFERENCES payment_response (id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL'		=>	"	ALTER TABLE 		Payment	
											DROP FOREIGN KEY	fk_Payment_latest_payment_response_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Update the created_datetime for existing Payment records",
				'sAlterSQL'			=>	"	UPDATE	Payment
											SET		created_datetime = PaidOn;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Temporarily add column payment_id to file_import_data",
				'sAlterSQL'			=>	"	ALTER TABLE	file_import_data
											ADD COLUMN	payment_id BIGINT UNSIGNED NULL;",
				'sRollbackSQL'		=>	"	ALTER TABLE file_import_data
											DROP COLUMN	payment_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Initial file_import_data records. Move all Payment, File & SequenceNo data from Payment to file_import_data, also copy Id (so that the payment can be linked to a payment_response)",
				'sAlterSQL'			=>	"	INSERT INTO	file_import_data (file_import_id, sequence_no, data, file_import_data_status_id, payment_id)
											(
												SELECT	p.File, 
														p.SequenceNo, 
														p.Payment, 
														(
															SELECT	id
															FROM	file_import_data_status
															WHERE	const_name = 'FILE_IMPORT_DATA_STATUS_NORMALISED'
														),
														p.Id
												FROM	Payment p
												JOIN	FileImport fi ON fi.Id = p.File
											);",
				'sRollbackSQL'		=>	"	TRUNCATE file_import_data;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Initial payment_response records. Create a payment_response for each Payment record",
				'sAlterSQL'			=>	"	INSERT INTO	payment_response(account_group_id, account_id, paid_date, payment_type_id, amount, origin_id, file_import_data_id, transaction_reference, payment_id, payment_status_id, created_datetime)
											(
												SELECT		IF(p.AccountGroup <> 0, p.AccountGroup, NULL), 
															IF(a.Id IS NOT NULL, p.Account, NULL), 
															p.PaidOn, 
															IF(pt.id IS NOT NULL, p.PaymentType, NULL), 
															p.Amount, 
															p.OriginId, 
															fid.id, 
															p.TXNReference,
															p.Id, 
															p.Status, 
															p.created_datetime
												FROM		Payment p
												JOIN		file_import_data fid ON fid.payment_id = p.Id
												LEFT JOIN	Account a ON a.Id = p.Account
												LEFT JOIN	payment_type pt ON pt.id = p.PaymentType
											);",
				'sRollbackSQL'		=>	"	TRUNCATE payment_response;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Set the latest_payment_response_id values in the Payment table",
				'sAlterSQL'			=>	"	UPDATE	Payment p
											SET		p.latest_payment_response_id = (
														SELECT	pr.id
														FROM	payment_response pr
														WHERE	pr.payment_id = p.Id
													);",
				'sRollbackSQL'		=>	"	UPDATE	Payment p
											SET		p.latest_payment_response_id = NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Remove temporary column payment_id from file_import_data",
				'sAlterSQL'			=>	"	ALTER TABLE file_import_data
											DROP COLUMN	payment_id;",
				'sRollbackSQL'		=>	"	ALTER TABLE	file_import_data
											ADD COLUMN	payment_id BIGINT UNSIGNED NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Remove the Payment, File & SequenceNo fields from the Payment table",
				'sAlterSQL'			=>	"	ALTER TABLE	Payment
											DROP COLUMN	Payment,
											DROP COLUMN	File,
											DROP COLUMN	SequenceNo;",
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