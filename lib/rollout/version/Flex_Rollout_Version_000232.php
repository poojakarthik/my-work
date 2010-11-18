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
		array(
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
				'sDescription'		=>	"Add table payment_response_status",
				'sAlterSQL'			=>	"	CREATE TABLE payment_response_status
											(
												id			INT 			NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
												name		VARCHAR(128)	NOT NULL					COMMENT \"Name of the status\",
												description	VARCHAR(128)	NOT NULL					COMMENT \"Description of the status\",
												const_name	VARCHAR(128)	NOT NULL					COMMENT \"Constant alias for the status\",
												system_name	VARCHAR(128)	NOT NULL					COMMENT \"System name for the status\",
												PRIMARY KEY (id)
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS payment_response_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add table payment_response_type",
				'sAlterSQL'			=>	"	CREATE TABLE payment_response_type
											(
												id			INT 			NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
												name		VARCHAR(128)	NOT NULL					COMMENT \"Name of the type\",
												description	VARCHAR(128)	NOT NULL					COMMENT \"Description of the type\",
												const_name	VARCHAR(128)	NOT NULL					COMMENT \"Constant alias for the type\",
												system_name	VARCHAR(128)	NOT NULL					COMMENT \"System name for the type\",
												PRIMARY KEY (id)
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS payment_response_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add table payment_response",
				'sAlterSQL'			=>	"	CREATE TABLE payment_response
											(
												id							BIGINT UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
												account_group_id			BIGINT UNSIGNED	NULL						COMMENT \"(FK) AccountGroup\",
												account_id					BIGINT UNSIGNED	NULL						COMMENT \"(FK) Account\",
												paid_date					DATE			NULL						COMMENT \"Effective date of the payment\",
												payment_type_id				BIGINT UNSIGNED	NULL						COMMENT \"(FK) payment_type\",
												amount						DECIMAL(13,4)	NULL						COMMENT \"Amount in dollars\",
												origin_id 					INT UNSIGNED	NULL						COMMENT \"Reference to the origin of the payment, e.g. CreditCard or DirectDebit id\",
												file_import_data_id			BIGINT UNSIGNED	NULL						COMMENT \"(FK) file_import_data, (optional) Raw payment data\",
												transaction_reference		VARCHAR(256)	NULL						COMMENT \"Transaction reference for the payment\",
												payment_id					BIGINT UNSIGNED	NULL						COMMENT \"(FK) Payment. The (optional) payment that this response is associated with\",
												payment_response_type_id	INT				NOT NULL					COMMENT \"(FK) payment_response_type\",												
												payment_response_status_id	INT				NOT NULL					COMMENT \"(FK) payment_response_status\",
												created_datetime			DATETIME		NOT NULL					COMMENT \"Timestamp for creation\",
												PRIMARY KEY (id),
												CONSTRAINT fk_payment_response_account_group_id				FOREIGN KEY	(account_group_id)				REFERENCES	AccountGroup (Id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_account_id					FOREIGN KEY	(account_id)					REFERENCES	Account (Id)					ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_payment_type_id				FOREIGN KEY	(payment_type_id)				REFERENCES	payment_type (id)				ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_file_import_data_id			FOREIGN KEY (file_import_data_id)			REFERENCES	file_import_data (id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_payment_id					FOREIGN KEY	(payment_id)					REFERENCES	Payment (Id)					ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_payment_response_type_id		FOREIGN KEY	(payment_response_type_id)		REFERENCES	payment_response_type (id)		ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_payment_response_payment_response_status_id	FOREIGN KEY	(payment_response_status_id)	REFERENCES	payment_response_status (id)	ON UPDATE CASCADE	ON DELETE RESTRICT
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS payment_response;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add created_datetime & latest_payment_response_id (and foreign key) to the Payment table",
				'sAlterSQL'			=>	"	ALTER TABLE 	Payment	
											ADD COLUMN 		latest_payment_response_id 	BIGINT UNSIGNED NULL 		COMMENT \"(FK) payment_response\",
											ADD COLUMN		created_datetime			DATETIME		NOT NULL	COMMENT \"Timestamp for creation\",
											ADD CONSTRAINT	fk_Payment_latest_payment_response_id FOREIGN KEY (latest_payment_response_id) REFERENCES payment_response (id) ON UPDATE CASCADE ON DELETE RESTRICT",
				'sRollbackSQL'		=>	"	ALTER TABLE 		Payment	
											DROP FOREIGN KEY	fk_Payment_latest_payment_response_id,
											DROP COLUMN			latest_payment_response_id,
											DROP COLUMN			created_datetime;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			)
		);
		
		// Check to see if AAPT COCE File was already defined as a Resource Type
		$oResult	= Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN)->query("SELECT Id FROM resource_type WHERE const_name = 'RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE'");
		if (PEAR::isError($oResult))
		{
			throw new Exception(__CLASS__ . " Failed to detect if AAPT COCE File was already defined as a Resource Type. " . $oResult->getMessage() . " (DB Error: " . $oResult->getUserInfo() . ")");
		}
		elseif ($oResult->numRows())
		{
			// AAPT COCE File was already defined as a Resource Type
			$this->outputMessage("Skipping 'Add the AAPT CTOP CDR File Format Resource Type': AAPT COCE File already exists as a Resource Type...\n");
		}
		else
		{
			// Not already defined, add operation to create it
			$aOperations[]	= 	array
								(
									'sDescription'		=>	"Add the AAPT CTOP CDR File Format Resource Type",
									'sAlterSQL'			=>	"	INSERT INTO	resource_type
																	(name						, description					, const_name											, resource_type_nature)
																VALUES
																	('AAPT E-Systems COCE File'	, 'AAPT E-Systems COCE File'	, 'RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1));",
									'sRollbackSQL'		=>	"	DELETE FROM	resource_type	WHERE const_name IN ('RESOURCE_TYPE_FILE_IMPORT_CDR_AAPT_ESYSTEMS_COCE');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								);
		}
		
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