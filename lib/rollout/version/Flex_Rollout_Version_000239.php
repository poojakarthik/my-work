<?php

/**
 * Version 239 of database update.
 *
 *	Collections - Schema changes
 *
 */

class Flex_Rollout_Version_000239 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=
		array
		(
			//
			// CONSTANT TABLES
			//
			array
			(
				'sDescription'		=>	"Table barring_level",
				'sAlterSQL'			=>	"	CREATE TABLE barring_level 
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE barring_level;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_invocation",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_invocation
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_invocation;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),							
			array
			(
				'sDescription'		=>	"Table collection_event_type_implementation",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_type_implementation
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												class_name VARCHAR(256) NOT NULL,
												is_scenario_event TINYINT NOT NULL DEFAULT 1,
												enforced_collection_event_invocation_id INT UNSIGNED NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_implementation_collection_event_invocation_id FOREIGN KEY(enforced_collection_event_invocation_id) REFERENCES collection_event_invocation(id)	ON DELETE RESTRICT ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_type_implementation;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_collection_event_status",
				'sAlterSQL'			=>	"	CREATE TABLE account_collection_event_status
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_collection_event_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_promise_completion",
				'sAlterSQL'			=>	"	CREATE TABLE collection_promise_completion
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_promise_completion;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table transaction_nature",
				'sAlterSQL'			=>	"	CREATE TABLE transaction_nature
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(45) NOT NULL,
												description VARCHAR(45) NOT NULL,
												system_name VARCHAR(45) NOT NULL,
												const_name VARCHAR(45) NOT NULL,
												code ENUM('CR','DR') NOT NULL,
												value_multiplier INT NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE transaction_nature;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_type_system",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_type_system
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_type_system;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_nature",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_nature
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												value_multiplier INT NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_nature;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_review_outcome_type",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_review_outcome_type
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_review_outcome_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_status",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_status
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table payment_nature",
				'sAlterSQL'			=>	"	CREATE TABLE payment_nature
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												value_multiplier INT NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE payment_nature;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_type_invoice_visibility",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_type_invoice_visibility
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY (id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_type_invoice_visibility;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_oca_referral_status",
				'sAlterSQL'			=>	"	CREATE TABLE account_oca_referral_status
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_oca_referral_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_report_output",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_report_output
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												file_type_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_report_output_file_type_id	FOREIGN KEY(file_type_id)	REFERENCES file_type(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_report_output;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_scenario_system",
				'sAlterSQL'			=>	"	CREATE TABLE collection_scenario_system
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_scenario_system;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table payment_reversal_type",
				'sAlterSQL'			=>	"	CREATE TABLE payment_reversal_type
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NOT NULL,
												const_name VARCHAR(256) NOT NULL,
												PRIMARY KEY (id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE payment_reversal_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Table working_status",
				'sAlterSQL'			=> "CREATE TABLE working_status 
										(
											id INT UNSIGNED NOT NULL AUTO_INCREMENT,
											name VARCHAR(256) NOT NULL,
											description VARCHAR(256) NOT NULL,
											system_name VARCHAR(256) NOT NULL,
											const_name VARCHAR(256) NOT NULL,
											PRIMARY KEY (id)
										)
										ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"DROP TABLE working_status;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Table collection_restriction",
				'sAlterSQL'			=> "CREATE TABLE collection_restriction 
										(
										  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
										  name VARCHAR(256) NOT NULL,
										  description VARCHAR(256) NOT NULL,
										  system_name VARCHAR(256) NOT NULL,
										  const_name VARCHAR(256) NOT NULL,
										  PRIMARY KEY (id)
										 )
										ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"DROP TABLE collection_restriction;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			
			//
			// CONSTANT DATA
			//
			
			array
			(
				'sDescription'		=>	"Data for table 'collection_event_invocation'",
				'sAlterSQL'			=>	"	INSERT INTO	collection_event_invocation (name, description, system_name, const_name) 
											VALUES		('Automatic', 	'Automatic', 	'AUTOMATIC', 	'COLLECTION_EVENT_INVOCATION_AUTOMATIC'),
														('Manual', 		'Manual', 		'MANUAL', 		'COLLECTION_EVENT_INVOCATION_MANUAL');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_event_type_implementation'",
				'sAlterSQL'			=>	"	INSERT INTO	collection_event_type_implementation (name, description, system_name, const_name, class_name, is_scenario_event, enforced_collection_event_invocation_id) 
											VALUES		('Correspondence', 		'Correspondence', 	'CORRESPONDENCE', 	'COLLECTION_EVENT_TYPE_IMPLEMENTATION_CORRESPONDENCE', 		'Logic_Collection_Event_Correspondence',	1, NULL),
														('Report', 				'Report', 			'REPORT', 			'COLLECTION_EVENT_TYPE_IMPLEMENTATION_REPORT', 				'Logic_Collection_Event_Report', 			1, NULL),
														('Action', 				'Action', 			'ACTION', 			'COLLECTION_EVENT_TYPE_IMPLEMENTATION_ACTION', 				'Logic_Collection_Event_Action', 			1, NULL),
														('Severity', 			'Severity', 		'SEVERITY', 		'COLLECTION_EVENT_TYPE_IMPLEMENTATION_SEVERITY', 			'Logic_Collection_Event_Severity', 			1, NULL),
														('Barring', 			'Barring', 			'BARRING', 			'COLLECTION_EVENT_TYPE_IMPLEMENTATION_BARRING', 			'Logic_Collection_Event_Barring', 			1, NULL),
														('OCA', 				'OCA', 				'OCA', 				'COLLECTION_EVENT_TYPE_IMPLEMENTATION_OCA', 				'Logic_Collection_Event_OCA', 				1, NULL),
														('TDC', 				'TDC', 				'TDC', 				'COLLECTION_EVENT_TYPE_IMPLEMENTATION_TDC', 				'Logic_Collection_Event_TDC', 				1, NULL),
														('Charge', 				'Charge', 			'CHARGE', 			'COLLECTION_EVENT_TYPE_IMPLEMENTATION_CHARGE', 				'Logic_Collection_Event_Charge', 			1, NULL),
														('Exit Collections', 	'Exit Collections',	'EXIT_COLLECTIONS', 'COLLECTION_EVENT_TYPE_IMPLEMENTATION_EXIT_COLLECTIONS', 	'Logic_Collection_Event_ExitCollections', 	0, (SELECT id FROM collection_event_invocation WHERE system_name = 'AUTOMATIC')),
														('Milestone', 			'Milestone',		'MILESTONE', 		'COLLECTION_EVENT_TYPE_IMPLEMENTATION_MILESTONE', 			'Logic_Collection_Event_Milestone', 		1, NULL);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_promise_completion'",
				'sAlterSQL'			=>	"	INSERT INTO collection_promise_completion (name, description, system_name, const_name) 
											VALUES		('Kept', 		'Kept', 		'KEPT', 		'COLLECTION_PROMISE_COMPLETION_KEPT'),
														('Broken', 		'Broken', 		'BROKEN', 		'COLLECTION_PROMISE_COMPLETION_BROKEN'),
														('Cancelled',	'Cancelled',	'CANCELLED', 	'COLLECTION_PROMISE_COMPLETION_CANCELLED');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'payment_nature'",
				'sAlterSQL'			=>	"	INSERT INTO payment_nature (name, description, system_name, const_name, value_multiplier) 
											VALUES		('Payment', 	'Payment', 		'PAYMENT', 		'PAYMENT_NATURE_PAYMENT',	-1),
														('Reversal', 	'Reversal', 	'REVERSAL',		'PAYMENT_NATURE_REVERSAL',	1);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'transaction_nature'",
				'sAlterSQL'			=>	"	INSERT INTO transaction_nature (name, description, system_name, const_name, code, value_multiplier) 
											VALUES		('Debit', 	'Debit', 	'DEBIT', 	'TRANSACTION_NATURE_DEBIT', 	'DR', 1),
														('Credit',	'Credit', 	'CREDIT', 	'TRANSACTION_NATURE_CREDIT', 	'CR', -1);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'adjustment_nature'",
				'sAlterSQL'			=>	"	INSERT INTO adjustment_nature (name, description, system_name, const_name, value_multiplier) 
											VALUES		('Adjustment', 	'Adjustment', 	'ADJUSTMENT',	'ADJUSTMENT_NATURE_ADJUSTMENT', 1),
														('Reversal', 	'Reversal', 	'REVERSAL', 	'ADJUSTMENT_NATURE_REVERSAL',	-1);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'adjustment_review_outcome_type'",
				'sAlterSQL'			=>	"	INSERT INTO adjustment_review_outcome_type (name, description, system_name, const_name) 
											VALUES		('Approved', 'Approved', 'APPROVED', 'ADJUSTMENT_REVIEW_OUTCOME_TYPE_APPROVED'),
														('Declined', 'Declined', 'DECLINED', 'ADJUSTMENT_REVIEW_OUTCOME_TYPE_DECLINED');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'adjustment_status'",
				'sAlterSQL'			=>	"	INSERT INTO adjustment_status (name, description, system_name, const_name) 
											VALUES		('Pending', 	'Pending', 	'PENDING', 	'ADJUSTMENT_STATUS_PENDING'),
														('Approved',	'Approved',	'APPROVED',	'ADJUSTMENT_STATUS_APPROVED'),
														('Declined',	'Declined',	'DECLINED',	'ADJUSTMENT_STATUS_DECLINED'),
														('Deleted', 	'Deleted', 	'DELETED', 	'ADJUSTMENT_STATUS_DELETED');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'adjustment_type_system'",
				'sAlterSQL'			=>	"	INSERT INTO adjustment_type_system (name, description, system_name, const_name) 
											VALUES		('Write-off', 					'Write-off Adjustment',			'WRITE_OFF', 					'ADJUSTMENT_TYPE_SYSTEM_WRITE_OFF'),
														('Write-back',					'Write-back Adjustment',		'WRITE_BACK', 					'ADJUSTMENT_TYPE_SYSTEM_WRITE_BACK'),
														('Rerate', 						'Rerate Adjustment',			'RERATE', 						'ADJUSTMENT_TYPE_SYSTEM_RERATE'),
														('Payment Surcharge Reversal', 	'Payment Surcharge Reversal', 	'PAYMENT_SURCHARGE_REVERSAL',	'ADJUSTMENT_TYPE_SYSTEM_PAYMENT_SURCHARGE_REVERSAL');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'account_collection_event_status'",
				'sAlterSQL'			=>	"	INSERT INTO account_collection_event_status (name, description, system_name, const_name) 
											VALUES		('Scheduled', 'Scheduled', 'SCHEDULED', 'ACCOUNT_COLLECTION_EVENT_STATUS_SCHEDULED'),
														('Completed', 'Completed', 'COMPLETED', 'ACCOUNT_COLLECTION_EVENT_STATUS_COMPLETED'),
														('Cancelled', 'Cancelled', 'CANCELLED', 'ACCOUNT_COLLECTION_EVENT_STATUS_CANCELLED');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'barring_level'",
				'sAlterSQL'			=>	"	INSERT INTO barring_level (name, description, system_name, const_name) 
											VALUES		('Unrestricted', 			'Unrestricted', 			'UNRESTRICTED',				'BARRING_LEVEL_UNRESTRICTED'),
														('Barred', 					'Barred', 					'BARRED', 					'BARRING_LEVEL_BARRED'),
														('Temporary Disconnection',	'Temporary Disconnection',	'TEMPORARY_DISCONNECTION',	'BARRING_LEVEL_TEMPORARY_DISCONNECTION');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'adjustment_type_invoice_visibility'",
				'sAlterSQL'			=>	"	INSERT INTO adjustment_type_invoice_visibility (name, description, system_name, const_name) 
											VALUES 		('Hidden', 		'Not shown on Invoice', 'HIDDEN', 	'ADJUSTMENT_TYPE_INVOICE_VISIBILITY_HIDDEN'),
														('Visible', 	'Shown on Invoice', 	'VISIBLE', 	'ADJUSTMENT_TYPE_INVOICE_VISIBILITY_VISIBLE');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'account_oca_referral_status'",
				'sAlterSQL'			=>	"	INSERT INTO	account_oca_referral_status (name, description, system_name, const_name)
											VALUES 		('Pending',		'Pending',		'PENDING',		'ACCOUNT_OCA_REFERRAL_STATUS_PENDING'),
														('Complete',	'Complete',		'COMPLETE',		'ACCOUNT_OCA_REFERRAL_STATUS_COMPLETE'),
														('Cancelled',	'Cancelled',	'CANCELLED',	'ACCOUNT_OCA_REFERRAL_STATUS_CANCELLED');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_event_report_output'",
				'sAlterSQL'			=>	"	INSERT INTO	collection_event_report_output (name, description, system_name, const_name, file_type_id)
											VALUES 		('CSV',			'CSV',					'CSV',			'COLLECTION_EVENT_REPORT_OUTPUT_CSV',			(SELECT id FROM file_type WHERE name = 'CSV')),
														('Excel',		'MS Excel',				'EXCEL',		'COLLECTION_EVENT_REPORT_OUTPUT_EXCEL',			(SELECT id FROM file_type WHERE name = 'Excel')),
														('Excel 2007',	'MS Excel 2007 XML',	'EXCEL_2007',	'COLLECTION_EVENT_REPORT_OUTPUT_EXCEL_2007',	(SELECT id FROM file_type WHERE name = 'Excel 2007'));",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_scenario_system'",
				'sAlterSQL'			=>	"	INSERT INTO	collection_scenario_system (name, description, system_name, const_name)
											VALUES 		('Broken Promise to Pay',	'Broken Promise to Pay',	'BROKEN_PROMISE_TO_PAY',	'COLLECTION_SCENARIO_SYSTEM_BROKEN_PROMISE_TO_PAY'),
														('Dishonoured Payment',		'Dishonoured Payment',		'DISHONOURED_PAYMENT',		'COLLECTION_SCENARIO_SYSTEM_DISHONOURED_PAYMENT');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "New record for table 'correspondence_source_type'",
				'sAlterSQL'			=> "	INSERT INTO correspondence_source_type (name, description, system_name, const_name, class_name, is_user_selectable)
											VALUES		('SQL Accounts', 'SQL with a placeholder for Account Ids', 'SQL_ACCOUNTS', 'CORRESPONDENCE_SOURCE_TYPE_SQL_ACCOUNTS', 'Correspondence_Source_SQLAccounts', 0);",
				'sRollbackSQL'		=>	"	DELETE FROM correspondence_source_type
											WHERE		system_name = 'SQL_ACCOUNTS';",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Data for table 'payment_reversal_type'",
				'sAlterSQL'			=> "	INSERT INTO payment_reversal_type (name, description, system_name, const_name)
											VALUES		('Agent', 		'Reversed by an Agent', 					'AGENT', 		'PAYMENT_REVERSAL_TYPE_AGENT'),
														('Dishonour', 	'Reversed because of dishonoured payment', 	'DISHONOUR',	'PAYMENT_REVERSAL_TYPE_DISHONOUR');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Data for table 'working_status'",
				'sAlterSQL'			=> "	INSERT INTO working_status (name, description, system_name, const_name)
											VALUES		('Draft', 		'Draft', 	'DRAFT', 	'WORKING_STATUS_DRAFT'),
														('Active', 		'Active', 	'ACTIVE',	'WORKING_STATUS_ACTIVE'),
														('Inactive', 	'Inactive', 'INACTIVE',	'WORKING_STATUS_INACTIVE');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			
			//
			// OTHER TABLES
			//
			
			array
			(
				'sDescription'		=>	"Table account_barring_level",
				'sAlterSQL'			=>	"	CREATE TABLE account_barring_level
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												created_datetime DATETIME NOT NULL,
												created_employee_id BIGINT UNSIGNED NOT NULL,
												authorised_datetime DATETIME NULL,
												authorised_employee_id BIGINT UNSIGNED NULL,
												barring_level_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												INDEX in_account_barring_level_created_datetime (created_datetime),
												INDEX in_account_barring_level_authorised_datetime (authorised_datetime),
												CONSTRAINT fk_account_barring_level_created_employee_id		FOREIGN KEY(created_employee_id)	REFERENCES Employee(Id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_barring_level_authorised_employee_id	FOREIGN KEY(authorised_employee_id)	REFERENCES Employee(Id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_barring_level_account_id				FOREIGN KEY(account_id)				REFERENCES Account(Id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_barring_level_barring_level_id		FOREIGN KEY(barring_level_id)		REFERENCES barring_level(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_barring_level;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table service_barring_level",
				'sAlterSQL'			=>	"	CREATE TABLE service_barring_level
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												service_id BIGINT UNSIGNED NOT NULL,
												created_datetime DATETIME NOT NULL,
												created_employee_id BIGINT UNSIGNED NOT NULL,
												authorised_datetime DATETIME NULL,
												authorised_employee_id BIGINT UNSIGNED NULL,
												actioned_datetime DATETIME NULL,
												actioned_employee_id BIGINT UNSIGNED NULL,
												account_barring_level_id BIGINT UNSIGNED NULL,
												barring_level_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												INDEX in_service_barring_level_created_datetime (created_datetime),
												INDEX in_service_barring_level_authorised_datetime (authorised_datetime),
												INDEX in_service_barring_level_actioned_datetime (actioned_datetime),
												CONSTRAINT fk_service_barring_level_service_id					FOREIGN KEY(service_id)					REFERENCES Service(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_service_barring_level_created_employee_id			FOREIGN KEY(created_employee_id)		REFERENCES Employee(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_service_barring_level_authorised_employee_id		FOREIGN KEY(authorised_employee_id)		REFERENCES Employee(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_service_barring_level_actioned_employee_id		FOREIGN KEY(actioned_employee_id)		REFERENCES Employee(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_service_barring_level_account_barring_level_id	FOREIGN KEY(account_barring_level_id)	REFERENCES account_barring_level(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_service_barring_level_barring_level_id			FOREIGN KEY(barring_level_id)			REFERENCES barring_level(id)			ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE service_barring_level;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_permissions_config",
				'sAlterSQL'			=>	"	CREATE TABLE collection_permissions_config
											(
												id 														INT UNSIGNED NOT NULL AUTO_INCREMENT,
												permissions 											BIGINT UNSIGNED NOT NULL,
												suspension_maximum_days 								INT UNSIGNED NOT NULL,
												suspension_maximum_suspensions_per_collections_period	INT UNSIGNED NOT NULL,
												promise_start_delay_maximum_days 						INT UNSIGNED NOT NULL,
												promise_maximum_days_between_due_and_end 				INT UNSIGNED NOT NULL,
												promise_instalment_maximum_interval_days 				INT UNSIGNED NOT NULL,
												promise_instalment_minimum_promised_percentage			DECIMAL(3,2)UNSIGNED  NOT NULL,
												promise_can_replace 									TINYINT UNSIGNED NOT NULL,
												promise_create_maximum_severity_level 					INT UNSIGNED NOT NULL,
												promise_amount_maximum 									DECIMAL(13,2) UNSIGNED NOT NULL,
												PRIMARY KEY(id)
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_permissions_config;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_severity",
				'sAlterSQL'			=>	"	CREATE TABLE collection_severity
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NULL,
												severity_level INT UNSIGNED NOT NULL,
												working_status_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												UNIQUE INDEX in_collection_severity_severity_level (severity_level),
												CONSTRAINT fk_collection_severity_working_status_id	FOREIGN KEY(working_status_id)	REFERENCES working_status(id)	ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_severity;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_scenario",
				'sAlterSQL'			=>	"	CREATE TABLE collection_scenario
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NULL,
												description VARCHAR(256) NULL,
												day_offset INT UNSIGNED NOT NULL DEFAULT 0,
												working_status_id INT UNSIGNED NOT NULL,
												threshold_percentage INT NOT NULL,
												threshold_amount DECIMAL(13,4) NOT NULL,
												initial_collection_severity_id INT UNSIGNED NULL,
												allow_automatic_unbar TINYINT NOT NULL DEFAULT 1,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_scenario_working_status_id					FOREIGN KEY(working_status_id)				REFERENCES working_status(id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_scenario_initial_collection_severity_id    FOREIGN KEY(initial_collection_severity_id)	REFERENCES collection_severity(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_scenario;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_scenario_system_config",
				'sAlterSQL'			=>	"	CREATE TABLE collection_scenario_system_config
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_scenario_system_id INT UNSIGNED NOT NULL,
												collection_scenario_id BIGINT UNSIGNED NOT NULL,
												start_datetime DATETIME NOT NULL,
												end_datetime DATETIME NOT NULL,
												INDEX in_collection_scenario_system_config_start_datetime (start_datetime),
												INDEX in_collection_scenario_system_config_end_datetime (end_datetime),
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_scenario_system_config_scenario_system_id		FOREIGN KEY(collection_scenario_system_id)	REFERENCES collection_scenario_system(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_scenario_system_config_collection_scenario_id	FOREIGN KEY(collection_scenario_id)			REFERENCES collection_scenario(id)			ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_scenario_system_config;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_collection_scenario",
				'sAlterSQL'			=>	"	CREATE TABLE account_collection_scenario
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												collection_scenario_id BIGINT UNSIGNED NOT NULL,
												created_datetime DATETIME NOT NULL,
												start_datetime DATETIME NOT NULL,
												end_datetime DATETIME NULL DEFAULT '9999-12-31 23:59:59',
												PRIMARY KEY(id),
												INDEX in_account_collection_scenario_created_datetime (created_datetime),
												INDEX in_account_collection_scenario_start_datetime (start_datetime),
												INDEX in_account_collection_scenario_end_datetime (end_datetime),
												CONSTRAINT fk_account_collection_scenario_account_id				FOREIGN KEY(account_id)				REFERENCES Account(Id)				ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_collection_scenario_collection_scenario_id	FOREIGN KEY(collection_scenario_id)	REFERENCES collection_scenario(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_collection_scenario;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_class",
				'sAlterSQL'			=>	"	CREATE TABLE account_class
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NULL,
												collection_scenario_id BIGINT UNSIGNED NOT NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_account_class_collection_scenario_id	FOREIGN KEY(collection_scenario_id)	REFERENCES collection_scenario(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_class_status_id				FOREIGN KEY(status_id) 				REFERENCES status(id)				ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_class;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_suspension_reason",
				'sAlterSQL'			=>	"	CREATE TABLE collection_suspension_reason
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_suspension_reason_status_id	FOREIGN KEY(status_id)	REFERENCES status(id)	ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_suspension_reason;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_suspension_end_reason",
				'sAlterSQL'			=>	"	CREATE TABLE collection_suspension_end_reason
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												collection_suspension_reason_id INT UNSIGNED NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_suspension_end_reason_status_id			FOREIGN KEY(status_id)							REFERENCES status(id)						ON DELETE RESTRICT 	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_suspension_end_reason_suspension_reason_id	FOREIGN KEY(collection_suspension_reason_id)	REFERENCES collection_suspension_reason(id)	ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_suspension_end_reason;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_suspension",
				'sAlterSQL'			=>	"	CREATE TABLE collection_suspension
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												start_datetime DATETIME NOT NULL,
												proposed_end_datetime DATETIME NOT NULL,
												start_employee_id BIGINT UNSIGNED NOT NULL,
												collection_suspension_reason_id INT UNSIGNED NOT NULL,
												effective_end_datetime DATETIME NULL,
												end_employee_id BIGINT UNSIGNED NULL,
												collection_suspension_end_reason_id INT UNSIGNED NULL,
												PRIMARY KEY(id),
												INDEX in_collection_suspension_start_datetime (start_datetime),
												INDEX in_collection_suspension_proposed_end_datetime (proposed_end_datetime),
												INDEX in_collection_suspension_effective_end_datetime (effective_end_datetime),
												CONSTRAINT fk_collection_suspension_collection_suspension_reason_id		FOREIGN KEY(collection_suspension_reason_id)		REFERENCES collection_suspension_reason(id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_suspension_collection_suspension_end_reason_id	FOREIGN KEY(collection_suspension_end_reason_id)	REFERENCES collection_suspension_end_reason(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_suspension_account_id							FOREIGN KEY(account_id)								REFERENCES Account(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_suspension_start_employee_id					FOREIGN KEY(start_employee_id)						REFERENCES Employee(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_suspension_end_employee_id						FOREIGN KEY(end_employee_id)						REFERENCES Employee(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_suspension;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_tio_complaint",
				'sAlterSQL'			=>	"	CREATE TABLE account_tio_complaint
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												collection_suspension_id BIGINT UNSIGNED NOT NULL,
												tio_reference_number VARCHAR(150) NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_account_tio_complaint_account_id					FOREIGN KEY(account_id)					REFERENCES Account(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_tio_complaint_collection_suspension_id	FOREIGN KEY(collection_suspension_id)	REFERENCES collection_suspension(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_tio_complaint;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_type",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_type
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NULL,
												collection_event_type_implementation_id INT UNSIGNED NOT NULL,
												collection_event_invocation_id INT UNSIGNED NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_type_collection_event_type_implementation_id	FOREIGN KEY(collection_event_type_implementation_id)	REFERENCES collection_event_type_implementation(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_type_collection_event_invocation				FOREIGN KEY(collection_event_invocation_id)				REFERENCES collection_event_invocation(id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_type_status_id								FOREIGN KEY(status_id)									REFERENCES status(id)								ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NULL,
												description VARCHAR(256) NULL,
												collection_event_type_id INT UNSIGNED NOT NULL,
												collection_event_invocation_id INT UNSIGNED NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_collection_event_type_id			FOREIGN KEY(collection_event_type_id)		REFERENCES collection_event_type(id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_collection_event_invocation_id	FOREIGN KEY(collection_event_invocation_id)	REFERENCES collection_event_invocation(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_status_id						FOREIGN KEY(status_id)						REFERENCES status(id)						ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_oca",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_oca
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												legal_fee_charge_type_id BIGINT UNSIGNED NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_oca_collection_event_id			FOREIGN KEY(collection_event_id)		REFERENCES collection_event(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_oca_legal_fee_charge_type_id		FOREIGN KEY(legal_fee_charge_type_id)	REFERENCES ChargeType(Id)		ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_oca;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_charge",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_charge
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												charge_type_id BIGINT UNSIGNED NOT NULL,
												minimum_amount DECIMAL(13,4) NULL,
												maximum_amount DECIMAL(13,4) NULL,
												percentage_outstanding_debt DECIMAL(3,2) NULL,
												allow_recharge TINYINT NOT NULL DEFAULT 0,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_charge_collection_event_id	FOREIGN KEY(collection_event_id)	REFERENCES collection_event(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_charge_charge_type_id		FOREIGN KEY(charge_type_id)			REFERENCES ChargeType(Id)		ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_charge;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_correspondence",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_correspondence
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												correspondence_template_id BIGINT NOT NULL,
												document_template_type_id BIGINT UNSIGNED NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_correspondence_correspondence_template_id	FOREIGN KEY(correspondence_template_id)	REFERENCES correspondence_template(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_correspondence_collection_event_id			FOREIGN KEY(collection_event_id)		REFERENCES collection_event(id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_correspondence_document_template_type_id		FOREIGN KEY(document_template_type_id)	REFERENCES DocumentTemplateType(Id)		ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_correspondence;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_report",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_report
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												report_sql VARCHAR(32767) NULL,
												email_notification_id BIGINT UNSIGNED NOT NULL,
												collection_event_report_output_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_report_collection_event_id				FOREIGN KEY(collection_event_id)				REFERENCES collection_event(id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_report_email_notification_id				FOREIGN KEY(email_notification_id)				REFERENCES email_notification(id)				ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_report_collection_event_report_output_id	FOREIGN KEY(collection_event_report_output_id)	REFERENCES collection_event_report_output(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_report;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_action",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_action
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												action_type_id SMALLINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_action_collection_event_id	FOREIGN KEY(collection_event_id)	REFERENCES collection_event(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_action_action_type_id		FOREIGN KEY(action_type_id)			REFERENCES action_type(id)		ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_action;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_warning",
				'sAlterSQL'			=>	"	CREATE TABLE collection_warning
											(
											  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
											  name VARCHAR(256) NOT NULL,
											  message VARCHAR(1024) NOT NULL,
											  status_id BIGINT UNSIGNED NOT NULL,
											  PRIMARY KEY (id),
											  CONSTRAINT fk_collection_warning_status_id FOREIGN KEY (status_id) REFERENCES status (id) ON DELETE RESTRICT ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_warning;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_severity_restriction",
				'sAlterSQL'			=>	"	CREATE TABLE collection_severity_restriction
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_restriction_id INT UNSIGNED NOT NULL,
												collection_severity_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_severity_restriction_collection_severity_id	FOREIGN KEY(collection_severity_id)		REFERENCES collection_severity(id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_severity_restriction_collection_restriction_id	FOREIGN KEY(collection_restriction_id)	REFERENCES collection_restriction(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_severity_restriction;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_severity_warning",
				'sAlterSQL'			=>	"	CREATE TABLE collection_severity_warning
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_warning_id INT UNSIGNED NOT NULL,
												collection_severity_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_severity_warning_collection_warning_id		FOREIGN KEY(collection_warning_id)	REFERENCES collection_warning(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_severity_warning_collection_severity_id	FOREIGN KEY(collection_severity_id)	REFERENCES collection_severity(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_severity_warning;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_event_severity",
				'sAlterSQL'			=>	"	CREATE TABLE collection_event_severity
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												collection_severity_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_event_severity_collection_event_id		FOREIGN KEY(collection_event_id)	REFERENCES collection_event(id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_event_severity_collection_severity_id	FOREIGN KEY(collection_severity_id)	REFERENCES collection_severity(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_event_severity;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_scenario_collection_event",
				'sAlterSQL'			=>	"	CREATE TABLE collection_scenario_collection_event
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_scenario_id BIGINT UNSIGNED NOT NULL,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												collection_event_invocation_id INT UNSIGNED NULL,
												day_offset INT NULL,
												prerequisite_collection_scenario_collection_event_id BIGINT UNSIGNED NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_scenario_collection_event_collection_scenario_id	FOREIGN KEY(collection_scenario_id)									REFERENCES collection_scenario(id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_scenario_collection_event_collection_event_id		FOREIGN KEY(collection_event_id)									REFERENCES collection_event(id)						ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_scenario_collection_event_event_invocation_id		FOREIGN KEY(collection_event_invocation_id)							REFERENCES collection_event_invocation(id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_scenario_collection_event_prerequisite_event_id	FOREIGN KEY(prerequisite_collection_scenario_collection_event_id)	REFERENCES collection_scenario_collection_event(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_scenario_collection_event;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_promise_reason",
				'sAlterSQL'			=>	"	CREATE TABLE collection_promise_reason
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_collection_promise_reason_status_id	FOREIGN KEY(status_id)	REFERENCES status(id)	ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_promise_reason;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_promise",
				'sAlterSQL'			=>	"	CREATE TABLE collection_promise
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												collection_promise_reason_id INT UNSIGNED NOT NULL,
												created_datetime DATETIME NOT NULL,
												created_employee_id BIGINT UNSIGNED NOT NULL,
												use_direct_debit TINYINT NOT NULL DEFAULT 0,
												completed_datetime DATETIME NULL,
												collection_promise_completion_id INT UNSIGNED NULL,
												completed_employee_id BIGINT UNSIGNED NULL,
												PRIMARY KEY(id),
												INDEX in_collection_promise_created_datetime (created_datetime),
												INDEX in_collection_promise_completed_datetime (completed_datetime),
												CONSTRAINT fk_collection_promise_collection_promise_completion_id	FOREIGN KEY(collection_promise_completion_id)	REFERENCES collection_promise_completion(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_promise_completed_employee_id				FOREIGN KEY(completed_employee_id)				REFERENCES Employee(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_promise_created_employee_id				FOREIGN KEY(created_employee_id)				REFERENCES Employee(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_promise_account_id							FOREIGN KEY(account_id)							REFERENCES Account(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_promise_collection_promise_reason_id		FOREIGN KEY(collection_promise_reason_id)		REFERENCES collection_promise_reason(id)		ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_promise;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collection_promise_instalment",
				'sAlterSQL'			=>	"	CREATE TABLE collection_promise_instalment
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												collection_promise_id BIGINT UNSIGNED NOT NULL,
												due_date DATE NULL,
												amount DECIMAL(13,4) NULL,
												created_datetime DATETIME NOT NULL,
												created_employee_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												INDEX in_collection_promise_instalment_due_date (due_date),
												INDEX in_collection_promise_instalment_created_datetime (created_datetime),
												CONSTRAINT fk_collection_promise_instalment_collection_promise_id	FOREIGN KEY (collection_promise_id)	REFERENCES collection_promise(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collection_promise_instalment_created_employee_id    	FOREIGN KEY (created_employee_id)	REFERENCES Employee(Id)    			ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collection_promise_instalment;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collectable",
				'sAlterSQL'			=>	"	CREATE TABLE collectable
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												amount DECIMAL(13,4) NOT NULL,
												balance DECIMAL(13,4) NULL,
												created_datetime DATETIME NOT NULL,
												due_date DATE NOT NULL,
												collection_promise_id BIGINT UNSIGNED NULL,
												invoice_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												INDEX in_collectable_created_datetime (created_datetime),
												INDEX in_collectable_due_date (due_date),
												CONSTRAINT fk_collectable_account_id			FOREIGN KEY(account_id)				REFERENCES Account(Id)				ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collectable_collection_promise_id	FOREIGN KEY(collection_promise_id)	REFERENCES collection_promise(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collectable_invoice_id			FOREIGN KEY(invoice_id)				REFERENCES Invoice(Id)				ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collectable;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_collection_event_history",
				'sAlterSQL'			=>	"	CREATE TABLE account_collection_event_history
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												collectable_id BIGINT UNSIGNED NOT NULL,
												collection_event_id BIGINT UNSIGNED NOT NULL,
												collection_scenario_collection_event_id BIGINT UNSIGNED NULL,
												scheduled_datetime DATETIME NOT NULL,
												completed_datetime DATETIME NULL,
												completed_employee_id BIGINT UNSIGNED NULL,
												account_collection_event_status_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												INDEX in_account_collection_event_history_scheduled_datetime (scheduled_datetime),
												INDEX in_account_collection_event_history_completed_datetime (completed_datetime),
												CONSTRAINT fk_account_collection_event_history_account_id				FOREIGN KEY(account_id)									REFERENCES Account(Id)								ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_collection_event_history_scenario_event_id		FOREIGN KEY(collection_scenario_collection_event_id)	REFERENCES collection_scenario_collection_event(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_collection_event_history_completed_employee_id	FOREIGN KEY(completed_employee_id)						REFERENCES Employee(Id)								ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_collection_event_history_collectable_id			FOREIGN KEY(collectable_id)								REFERENCES collectable(id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_collection_event_history_collection_event_id		FOREIGN KEY(collection_event_id)						REFERENCES collection_event(id)						ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_collection_event_history_account_event_status_id	FOREIGN KEY(account_collection_event_status_id)			REFERENCES account_collection_event_status(id)		ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_collection_event_history;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_oca_referral",
				'sAlterSQL'			=>	"	CREATE TABLE account_oca_referral
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NOT NULL,
												account_collection_event_history_id BIGINT UNSIGNED NOT NULL,
												file_export_id BIGINT UNSIGNED NULL,
												invoice_run_id BIGINT UNSIGNED NULL,
												account_oca_referral_status_id INT UNSIGNED NOT NULL,
												actioned_datetime DATETIME NULL,
												actioned_employee_id BIGINT UNSIGNED NULL,
												PRIMARY KEY(id),
												INDEX in_account_oca_referral_actioned_datetime (actioned_datetime),
											  	CONSTRAINT fk_account_oca_referral_account_id							FOREIGN KEY(account_id)								REFERENCES Account(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_oca_referral_account_collection_event_history_id	FOREIGN KEY(account_collection_event_history_id)	REFERENCES account_collection_event_history(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_oca_referral_file_export_id						FOREIGN KEY(file_export_id)							REFERENCES FileExport(Id)						ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_oca_referral_invoice_run_id						FOREIGN KEY(invoice_run_id)							REFERENCES InvoiceRun(Id)						ON DELETE SET NULL	ON UPDATE CASCADE,
												CONSTRAINT fk_account_oca_referral_account_oca_referral_status_id		FOREIGN KEY(account_oca_referral_status_id)			REFERENCES account_oca_referral_status(id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_account_oca_referral_actioned_employee_id					FOREIGN KEY(actioned_employee_id)					REFERENCES Employee(Id)							ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE account_oca_referral;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collectable_transfer_balance",
				'sAlterSQL'			=>	"	CREATE TABLE collectable_transfer_balance
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												from_collectable_id BIGINT UNSIGNED NOT NULL,
											  	to_collectable_id BIGINT UNSIGNED NOT NULL,
											  	created_datetime DATETIME NOT NULL,
											  	balance DECIMAL(13,4) NOT NULL,
											  	PRIMARY KEY (id),
												INDEX in_collectable_transfer_balance_created_datetime (created_datetime),
											  	CONSTRAINT fk_collectable_transfer_balance_from_collectable_id	FOREIGN KEY (from_collectable_id)	REFERENCES collectable (id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
											  	CONSTRAINT fk_collectable_transfer_balance_to_collectable_id		FOREIGN KEY (to_collectable_id)		REFERENCES collectable (id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collectable_transfer_balance;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collectable_transfer_value",
				'sAlterSQL'			=>	"	CREATE TABLE collectable_transfer_value
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												from_collectable_id BIGINT UNSIGNED NOT NULL,
												to_collectable_id BIGINT UNSIGNED NOT NULL,
												created_datetime DATETIME NOT NULL,
												amount DECIMAL(13,4) NOT NULL,
												balance DECIMAL(13,4) NOT NULL,
												PRIMARY KEY (id),
												INDEX in_collectable_transfer_value_created_datetime (created_datetime),
												CONSTRAINT fk_collectable_transfer_value_from_collectable_id	FOREIGN KEY (from_collectable_id)	REFERENCES collectable (id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collectable_transfer_value_to_collectable_id    	FOREIGN KEY (to_collectable_id)		REFERENCES collectable (id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collectable_transfer_value;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_type",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_type
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												code VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												amount DECIMAL(13,4) NULL,
												is_amount_fixed TINYINT NOT NULL DEFAULT 0,
												transaction_nature_id INT UNSIGNED NOT NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												adjustment_type_invoice_visibility_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_adjustment_type_transaction_nature_id					FOREIGN KEY(transaction_nature_id)					REFERENCES transaction_nature(id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_type_status_id								FOREIGN KEY(status_id)								REFERENCES status(id)								ON DELETE RESTRICT 	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_type_adjustment_type_invoice_visibility_id	FOREIGN KEY(adjustment_type_invoice_visibility_id)	REFERENCES adjustment_type_invoice_visibility(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_type_system_config",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_type_system_config
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												adjustment_type_system_id INT UNSIGNED NOT NULL,
												adjustment_type_id INT UNSIGNED NOT NULL,
												start_datetime DATETIME NOT NULL,
												end_datetime DATETIME NOT NULL, 
												PRIMARY KEY(id),
												INDEX in_adjustment_type_system_config_start_datetime (start_datetime),
												INDEX in_adjustment_type_system_config_end_datetime (end_datetime),
												CONSTRAINT fk_adjustment_type_system_config_adjustment_type_id			FOREIGN KEY(adjustment_type_id)			REFERENCES adjustment_type(id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_type_system_config_adjustment_type_system_id	FOREIGN KEY(adjustment_type_system_id)	REFERENCES adjustment_type_system(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_type_system_config;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_review_outcome",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_review_outcome
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NULL,
												description VARCHAR(256) NULL,
												system_name VARCHAR(256) NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												adjustment_review_outcome_type_id INT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_adjustment_review_outcome_status_id							FOREIGN KEY(status_id)							REFERENCES status(id)							ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_review_outcome_adjustment_review_outcome_type_id	FOREIGN KEY(adjustment_review_outcome_type_id)	REFERENCES adjustment_review_outcome_type(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_review_outcome;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment_reversal_reason",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment_reversal_reason 
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY (id),
												CONSTRAINT fk_adjustment_reversal_reason_status_id FOREIGN KEY (status_id)  REFERENCES status (id) ON DELETE RESTRICT ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment_reversal_reason;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table adjustment",
				'sAlterSQL'			=>	"	CREATE TABLE adjustment
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												adjustment_type_id INT UNSIGNED NOT NULL,
												amount DECIMAL(13,4) UNSIGNED NOT NULL COMMENT \"Inclusive of tax_component\",
												tax_component DECIMAL(13,4) UNSIGNED NOT NULL,
												balance DECIMAL(13,4) UNSIGNED NOT NULL,
												effective_date DATE NULL,
												created_employee_id BIGINT UNSIGNED NOT NULL,
												created_datetime DATETIME NULL,
												reviewed_employee_id BIGINT UNSIGNED NULL,
												reviewed_datetime DATETIME NULL,
												adjustment_nature_id INT UNSIGNED NOT NULL,
												adjustment_review_outcome_id INT UNSIGNED NULL,
												adjustment_status_id INT UNSIGNED NOT NULL,
												reversed_adjustment_id BIGINT UNSIGNED NULL,
												adjustment_reversal_reason_id INT UNSIGNED NULL,
												account_id BIGINT UNSIGNED NOT NULL,
												service_id BIGINT UNSIGNED NULL,
												invoice_id BIGINT UNSIGNED NULL,
												invoice_run_id BIGINT UNSIGNED NULL,
												PRIMARY KEY(id),
												INDEX in_adjustment_created_datetime (created_datetime),
												INDEX in_adjustment_reviewed_datetime (reviewed_datetime),
												INDEX in_adjustment_effective_date (effective_date),
												CONSTRAINT fk_adjustment_adjustment_type_id				FOREIGN KEY(adjustment_type_id)				REFERENCES adjustment_type(id)				ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_created_employee_id			FOREIGN KEY(created_employee_id)   		 	REFERENCES Employee(Id)    					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_reviewed_employee_id    		FOREIGN KEY(reviewed_employee_id)    		REFERENCES Employee(Id)   			 		ON DELETE RESTRICT 	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_adjustment_nature_id  			FOREIGN KEY(adjustment_nature_id)    		REFERENCES adjustment_nature(id)    		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_adjustment_review_outcome_id	FOREIGN KEY(adjustment_review_outcome_id) 	REFERENCES adjustment_review_outcome(id)    ON DELETE RESTRICT 	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_adjustment_status_id    		FOREIGN KEY(adjustment_status_id)    		REFERENCES adjustment_status(id)    		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_reversed_adjustment_id 		FOREIGN KEY(reversed_adjustment_id) 		REFERENCES adjustment(id) 					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_adjustment_reversal_reason_id 	FOREIGN KEY (adjustment_reversal_reason_id) REFERENCES adjustment_reversal_reason (id) 	ON DELETE RESTRICT 	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_account_id    					FOREIGN KEY(account_id)    					REFERENCES Account(Id)   		 			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_service_id    					FOREIGN KEY(service_id)    					REFERENCES Service(Id)    					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_invoice_id    					FOREIGN KEY(invoice_id)    					REFERENCES Invoice(Id)    					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_adjustment_invoice_run_id   			 	FOREIGN KEY(invoice_run_id)   			 	REFERENCES InvoiceRun(Id)    				ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE adjustment;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collectable_adjustment",
				'sAlterSQL'			=>	"	CREATE TABLE collectable_adjustment
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												adjustment_id BIGINT UNSIGNED NOT NULL,
												collectable_id BIGINT UNSIGNED NOT NULL,
												balance DECIMAL(13,4) NULL,
												created_datetime DATETIME NULL,
												PRIMARY KEY(id),
												INDEX in_collectable_adjustment_created_datetime (created_datetime),
												CONSTRAINT fk_collectable_adjustment_adjustment_id	FOREIGN KEY(adjustment_id)	REFERENCES adjustment(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collectable_adjustment_collectable_id	FOREIGN KEY(collectable_id)	REFERENCES collectable(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collectable_adjustment;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table payment_reversal_reason",
				'sAlterSQL'			=>	"	CREATE TABLE payment_reversal_reason
											(
												id INT UNSIGNED NOT NULL AUTO_INCREMENT,
												name VARCHAR(256) NOT NULL,
												description VARCHAR(256) NOT NULL,
												system_name VARCHAR(256) NULL,
												payment_reversal_type_id INT UNSIGNED NOT NULL,
												status_id BIGINT UNSIGNED NOT NULL,
												PRIMARY KEY(id),
												CONSTRAINT fk_payment_reversal_reason_payment_reversal_type_id	FOREIGN KEY(payment_reversal_type_id)	REFERENCES payment_reversal_type(id)	ON DELETE RESTRICT 	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_reversal_reason_status_id					FOREIGN KEY(status_id)					REFERENCES status(id)					ON DELETE RESTRICT 	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE payment_reversal_reason;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table payment",
				'sAlterSQL'			=>	"	CREATE TABLE payment
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												account_id BIGINT UNSIGNED NULL,												
												carrier_id BIGINT NULL,
												created_datetime DATETIME NULL,
												created_employee_id BIGINT UNSIGNED NOT NULL,
												paid_date DATE NOT NULL,
												payment_type_id BIGINT UNSIGNED NULL,
												transaction_reference VARCHAR(128) NULL,
												payment_nature_id INT UNSIGNED NOT NULL,
												amount DECIMAL(13,4) UNSIGNED NOT NULL,
												balance DECIMAL(13,4) UNSIGNED NOT NULL,
												surcharge_charge_id BIGINT UNSIGNED NULL,
												latest_payment_response_id BIGINT UNSIGNED NULL,
												reversed_payment_id BIGINT UNSIGNED NULL,
												payment_reversal_type_id INT UNSIGNED NULL,
												payment_reversal_reason_id INT UNSIGNED NULL,
												PRIMARY KEY(id),
												INDEX in_payment_created_datetime (created_datetime),
												INDEX in_payment_paid_date (paid_date),
												CONSTRAINT fk_payment_tbl_account_id					FOREIGN KEY(account_id)						REFERENCES Account(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_payment_type_id				FOREIGN KEY(payment_type_id)				REFERENCES payment_type(id)				ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_surcharge_charge_id			FOREIGN KEY(surcharge_charge_id)			REFERENCES Charge(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_created_employee_id			FOREIGN KEY(created_employee_id)			REFERENCES Employee(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_latest_payment_response_id	FOREIGN KEY(latest_payment_response_id)		REFERENCES payment_response(id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_carrier_id					FOREIGN KEY(carrier_id)						REFERENCES Carrier(Id)					ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_payment_nature_id				FOREIGN KEY(payment_nature_id)				REFERENCES payment_nature(id)			ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_payment_reversal_type_id		FOREIGN KEY(payment_reversal_type_id)		REFERENCES payment_reversal_type(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_payment_reversal_reason_id	FOREIGN KEY(payment_reversal_reason_id)		REFERENCES payment_reversal_reason(id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_payment_tbl_reversed_payment_id			FOREIGN KEY(reversed_payment_id)			REFERENCES payment(id)					ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE payment;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table payment_transaction_data",
				'sAlterSQL'			=>	"	CREATE TABLE payment_transaction_data
											(
											  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
											  name VARCHAR(256) NOT NULL,
											  value VARCHAR(1024) NOT NULL,
											  data_type_id BIGINT UNSIGNED NOT NULL,
											  payment_id BIGINT UNSIGNED NULL,
											  payment_response_id BIGINT UNSIGNED NULL,
											  PRIMARY KEY (id),
											  CONSTRAINT fk_payment_transaction_data_data_type_id    		FOREIGN KEY (data_type_id)    		REFERENCES data_type (id)    		ON DELETE RESTRICT	ON UPDATE CASCADE,
											  CONSTRAINT fk_payment_transaction_data_payment_id   		 	FOREIGN KEY (payment_id)    		REFERENCES payment (id)    			ON DELETE RESTRICT	ON UPDATE CASCADE,
											  CONSTRAINT fk_payment_transaction_data_payment_response_id	FOREIGN KEY (payment_response_id)	REFERENCES payment_response (id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE payment_transaction_data;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table collectable_payment",
				'sAlterSQL'			=>	"	CREATE TABLE collectable_payment
											(
												id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												payment_id BIGINT UNSIGNED NOT NULL,
												collectable_id BIGINT UNSIGNED NOT NULL,
												balance DECIMAL(13,4) NULL,
												create_datetime DATETIME NULL,
												PRIMARY KEY(id),
												INDEX in_collectable_payment_create_datetime (create_datetime),
												CONSTRAINT fk_collectable_payment_payment_id		FOREIGN KEY(payment_id)		REFERENCES payment(id)		ON DELETE RESTRICT	ON UPDATE CASCADE,
												CONSTRAINT fk_collectable_payment_collectable_id	FOREIGN KEY(collectable_id)	REFERENCES collectable(id)	ON DELETE RESTRICT	ON UPDATE CASCADE
											)
											ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE collectable_payment;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table correspondence_source_sql_accounts",
				'sAlterSQL'			=>	"	CREATE TABLE correspondence_source_sql_accounts
											(
												id							BIGINT UNSIGNED	NOT NULL AUTO_INCREMENT,
												correspondence_source_id	BIGINT 			NOT NULL,
												sql_syntax 					LONGTEXT 		NULL,
												enforce_account_set 		TINYINT 		NOT NULL DEFAULT 1,
												PRIMARY KEY (id),
												CONSTRAINT fk_correspondence_source_sql_accounts_correspondence_source_id FOREIGN KEY (correspondence_source_id) REFERENCES correspondence_source(id) ON UPDATE CASCADE ON DELETE RESTRICT
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE correspondence_source_sql_accounts;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Table collections_config",
				'sAlterSQL'			=> "CREATE TABLE collections_config
										(
											id										BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
											created_datetime						DATETIME		NOT NULL,
											created_employee_id						BIGINT UNSIGNED	NOT NULL,
											promise_instalment_leniency_days 		INT UNSIGNED 	NOT NULL DEFAULT 0,
											direct_debit_due_date_offset			INT 			NOT NULL DEFAULT 0,
											promise_direct_debit_due_date_offset	INT 			NOT NULL DEFAULT 0,
											PRIMARY KEY (id),
											INDEX in_collections_config_created_datetime (created_datetime),
											CONSTRAINT fk_collections_config_created_employee_id FOREIGN KEY (created_employee_id) REFERENCES Employee(Id) ON UPDATE CASCADE ON DELETE RESTRICT
										)
										ENGINE = InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE collections_config;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Table collections_schedule",
				'sAlterSQL'			=> "CREATE TABLE collections_schedule 
										(
										  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
										  day INT UNSIGNED NULL 						COMMENT \"Day of Month\",
										  month INT UNSIGNED NULL 						COMMENT \"Month of Year\",
										  year INT UNSIGNED NULL 						COMMENT \"Full Year (e.g. 2011)\",
										  monday TINYINT UNSIGNED NULL 					COMMENT \"Every Monday\",
										  tuesday TINYINT UNSIGNED NULL 				COMMENT \"Every Tuesday\",
										  wednesday TINYINT UNSIGNED NULL 				COMMENT \"Every Wednesday\",
										  thursday TINYINT UNSIGNED NULL 				COMMENT \"Every Thursday\",
										  friday TINYINT UNSIGNED NULL 					COMMENT \"Every Friday\",
										  saturday TINYINT UNSIGNED NULL 				COMMENT \"Every Saturday\",
										  sunday TINYINT UNSIGNED NULL 					COMMENT \"Every Sunday\",
										  collection_event_id BIGINT UNSIGNED NULL	 	COMMENT \"(Optional) Event to apply to exclusively\",
										  is_direct_debit TINYINT UNSIGNED NULL	 		COMMENT \"(Optional) If given apply direct debiting exclusively\",
										  eligibility TINYINT UNSIGNED NOT NULL 		COMMENT \"1: Collections/Event is eligible, 0: Collections/Event is NOT eligible\",
										  precedence INT UNSIGNED NOT NULL DEFAULT 0 	COMMENT \"Represents the importance of this schedule rule over any conflicting rules, can be any positive number\",
										  status_id BIGINT UNSIGNED NOT NULL 			COMMENT \"(FK) status, if inactive will be ignored\",
										  PRIMARY KEY (id),
										  CONSTRAINT fk_collections_schedule_status_id    			FOREIGN KEY (status_id)   			REFERENCES status (id)   			ON DELETE RESTRICT    ON UPDATE CASCADE,
										  CONSTRAINT fk_collections_schedule_collection_event_id    FOREIGN KEY (collection_event_id)   REFERENCES collection_event (id)	ON DELETE RESTRICT    ON UPDATE CASCADE
										) ENGINE = InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE collections_schedule;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Table adjustment_charge",
				'sAlterSQL'			=> "CREATE TABLE adjustment_charge
										(
										  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
										  adjustment_id BIGINT UNSIGNED NOT NULL,
										  charge_id BIGINT UNSIGNED NOT NULL,
										  PRIMARY KEY (id),
										  CONSTRAINT fk_adjustment_charge_adjustment_id	FOREIGN KEY (adjustment_id)	REFERENCES adjustment (id)	ON DELETE RESTRICT	ON UPDATE CASCADE,
										  CONSTRAINT fk_adjustment_charge_charge_id    	FOREIGN KEY (charge_id)		REFERENCES Charge (Id)		ON DELETE RESTRICT	ON UPDATE CASCADE
										) ENGINE = InnoDB;",
				'sRollbackSQL'		=> "DROP TABLE adjustment_charge;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			
			//
			// DEFAULT CONFIGURATION DATA
			//
			
			array
			(
				'sDescription'		=>	"Data for table 'collection_scenario'",
				'sAlterSQL'			=>	"	INSERT INTO collection_scenario (name, description, day_offset, working_status_id, threshold_percentage, threshold_amount) 
											VALUES 		('Default', 'Default Scenario', 0, (SELECT id FROM working_status WHERE system_name='ACTIVE'), 0.25, 27.01);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'account_class'",
				'sAlterSQL'			=>	"	INSERT INTO account_class (name, description, collection_scenario_id, status_id)
											VALUES 		('Standard', 'Standard Class', (SELECT id FROM collection_scenario WHERE name = 'Default' LIMIT 1), ".STATUS_ACTIVE.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_suspension_reason'",
				'sAlterSQL'			=>	"	INSERT INTO collection_suspension_reason (name, description, system_name, status_id) 
											VALUES 		('Suspension', 					'Suspension', 					'SUSPENSION', 					".STATUS_ACTIVE."),
														('TIO Complaint',				'TIO Complaint',				'TIO_COMPLAINT',				".STATUS_INACTIVE."),
														('Extension', 					'Extension', 					'EXTENSION',					".STATUS_INACTIVE."),
														('Sending to Debt Collection', 	'Sending to Debt Collection', 	'SENDING_TO_DEBT_COLLECTION',	".STATUS_INACTIVE."),
														('With Debt Collection', 		'With Debt Collection', 		'WITH_DEBT_COLLECTION',			".STATUS_INACTIVE."),
														('Win Back', 					'Win Back', 					'WIN_BACK',						".STATUS_INACTIVE."),
														('Payment Plan', 				'Payment Plan', 				'PAYMENT_PLAN', 				".STATUS_INACTIVE."),
														('Cooling Off', 				'Cooling Off', 					'COOLING_OFF',					".STATUS_INACTIVE.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_suspension_end_reason'",
				'sAlterSQL'			=>	"	INSERT INTO collection_suspension_end_reason (name, description, system_name, status_id) 
											VALUES 		('Expired', 	'Suspension Expired', 	'EXPIRED', 		".STATUS_ACTIVE."),
														('Cancelled', 	'Suspension Cancelled', 'CANCELLED',	".STATUS_ACTIVE.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_event_type'",
				'sAlterSQL'			=>	"	INSERT INTO	collection_event_type (name, description, system_name, collection_event_type_implementation_id, status_id) 
											VALUES		('Correspondence', 		'Correspondence', 	'CORRESPONDENCE', 	(SELECT id FROM collection_event_type_implementation WHERE system_name = 'CORRESPONDENCE'),		".STATUS_ACTIVE."),
														('Report', 				'Report', 			'REPORT', 			(SELECT id FROM collection_event_type_implementation WHERE system_name = 'REPORT'),				".STATUS_ACTIVE."),
														('Action', 				'Action', 			'ACTION', 			(SELECT id FROM collection_event_type_implementation WHERE system_name = 'ACTION'),				".STATUS_ACTIVE."),
														('Severity', 			'Severity', 		'SEVERITY', 		(SELECT id FROM collection_event_type_implementation WHERE system_name = 'SEVERITY'),			".STATUS_ACTIVE."),
														('Barring', 			'Barring', 			'BARRING', 			(SELECT id FROM collection_event_type_implementation WHERE system_name = 'BARRING'),			".STATUS_ACTIVE."),
														('OCA', 				'OCA', 				'OCA', 				(SELECT id FROM collection_event_type_implementation WHERE system_name = 'OCA'),				".STATUS_ACTIVE."),
														('TDC', 				'TDC', 				'TDC', 				(SELECT id FROM collection_event_type_implementation WHERE system_name = 'TDC'),				".STATUS_ACTIVE."),
														('Charge', 				'Charge', 			'CHARGE', 			(SELECT id FROM collection_event_type_implementation WHERE system_name = 'CHARGE'),				".STATUS_ACTIVE."),
														('Exit Collections', 	'Exit Collections',	'EXIT_COLLECTIONS', (SELECT id FROM collection_event_type_implementation WHERE system_name = 'EXIT_COLLECTIONS'),	".STATUS_ACTIVE."),
														('Milestone', 			'Milestone',		'MILESTONE', 		(SELECT id FROM collection_event_type_implementation WHERE system_name = 'MILESTONE'),			".STATUS_ACTIVE.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_severity'",
				'sAlterSQL'			=>	"	INSERT INTO collection_severity (name, description, system_name, working_status_id) 
											VALUES 		('Zero', 'Unrestricted', 'UNRESTRICTED', (SELECT id FROM working_status WHERE system_name='ACTIVE'));",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_restriction'",
				'sAlterSQL'			=>	"	INSERT INTO collection_restriction (name, description, system_name, const_name) 
											VALUES 		('Disallow Automatic Unbarring', 	'Disallow Automatic Unbarring', 'DISALLOW_AUTOMATIC_UNBARRING',	'COLLECTION_RESTRICTION_DISALLOW_AUTOMATIC_UNBARRING'),
														('Debt Consolidation', 				'Debt Consolidation', 			'DEBT_CONSOLIDATION', 			'COLLECTION_RESTRICTION_DEBT_CONSOLIDATION');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'collection_promise_reason'",
				'sAlterSQL'			=>	"	INSERT INTO collection_promise_reason (name, description, status_id) 
											VALUES 		('Promise to Pay', 'Promise to Pay', ".STATUS_ACTIVE.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'payment_reversal_reason'",
				'sAlterSQL'			=>	"	INSERT INTO payment_reversal_reason (name, description, system_name, payment_reversal_type_id, status_id) 
											VALUES 		('Agent Reversal', 		'Payment reversed by an Agent', 'AGENT_REVERSAL', 		(SELECT id FROM payment_reversal_type WHERE system_name = 'AGENT'), 	".STATUS_ACTIVE."),
														('Dishonour Reversal', 	'Payment Dishonoured', 			'DISHONOUR_REVERSAL',	(SELECT id FROM payment_reversal_type WHERE system_name = 'DISHONOUR'), ".STATUS_ACTIVE.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'adjustment_review_outcome'",
				'sAlterSQL'			=>	"	INSERT INTO adjustment_review_outcome (name, description, system_name, status_id, adjustment_review_outcome_type_id) 
											VALUES 		('Approved', 'Approved', 'APPROVED', ".STATUS_ACTIVE.", (SELECT id FROM adjustment_review_outcome_type WHERE system_name = 'APPROVED' LIMIT 1)),
														('Declined', 'Declined', 'DECLINED', ".STATUS_ACTIVE.", (SELECT id FROM adjustment_review_outcome_type WHERE system_name = 'DECLINED' LIMIT 1));",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Data for table 'adjustment_reversal_reason'",
				'sAlterSQL'			=>	"	INSERT INTO adjustment_reversal_reason (name, description, system_name, status_id) 
											VALUES 		('Reversal', 'Reversal', 'REVERSAL', ".STATUS_ACTIVE.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Create a account_collection_scenario record for each account",
				'sAlterSQL'			=>	"	INSERT INTO account_collection_scenario (account_id, collection_scenario_id, created_datetime, start_datetime)
												SELECT	Id, (SELECT id FROM collection_scenario WHERE name = 'Default' LIMIT 1), NOW(), NOW()
												FROM	Account;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			
			//
			// EXISTING TABLE ALTERATIONS
			//
			
			array
			(
				'sDescription'		=>	"Table employee_account_log - add accepted_severity_warnings",
				'sAlterSQL'			=>	"	ALTER TABLE	employee_account_log
											ADD COLUMN 	accepted_severity_warnings TINYINT UNSIGNED NULL;",
				'sRollbackSQL'		=>	"	ALTER TABLE	employee_account_log
											DROP COLUMN	accepted_severity_warnings;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table RatePlan - add override_default_rate_plan_id",
				'sAlterSQL'			=>	"	ALTER TABLE	RatePlan
											ADD COLUMN 	override_default_rate_plan_id BIGINT UNSIGNED NULL,
											ADD CONSTRAINT fk_rate_plan_override_default_rate_plan_id FOREIGN KEY (override_default_rate_plan_id) REFERENCES RatePlan(Id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL'		=>	"	ALTER TABLE			RatePlan
											DROP FOREIGN KEY	fk_rate_plan_override_default_rate_plan_id,
											DROP COLUMN 		override_default_rate_plan_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Turn OFF foreign key checks",
				'sAlterSQL'			=>	"	SET foreign_key_checks = 0;",
				'sRollbackSQL'		=>	"	SET foreign_key_checks = 1;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table account_history - add account_class_id",
				'sAlterSQL'			=>	"	ALTER TABLE 	account_history
											ADD COLUMN		account_class_id INT UNSIGNED NOT NULL,
											ADD CONSTRAINT 	fk_account_history_account_class_id FOREIGN KEY(account_class_id) REFERENCES account_class(id) ON DELETE RESTRICT ON UPDATE CASCADE;",
				'sRollbackSQL'		=>	"	ALTER TABLE			account_history
											DROP FOREIGN KEY	fk_account_history_account_class_id,
											DROP COLUMN			account_class_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Give each account_history record an account_class_id",
				'sAlterSQL'			=>	"	UPDATE	account_history
											SET		account_class_id = (SELECT id FROM account_class WHERE name = 'Standard' LIMIT 1);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table CustomerGroup - add default_account_class_id",
				'sAlterSQL'			=>	"	ALTER TABLE		CustomerGroup
											ADD COLUMN		default_account_class_id INT UNSIGNED NOT NULL,
											ADD CONSTRAINT	fk_customer_group_default_account_class_id FOREIGN KEY(default_account_class_id) REFERENCES account_class(id) ON DELETE RESTRICT ON UPDATE CASCADE;",
				'sRollbackSQL'		=>	"	ALTER TABLE			CustomerGroup
											DROP FOREIGN KEY	fk_customer_group_default_account_class_id,
											DROP COLUMN			default_account_class_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Give each customer group a default_account_class_id",
				'sAlterSQL'			=>	"	UPDATE	CustomerGroup
											SET		default_account_class_id = (SELECT id FROM account_class WHERE name = 'Standard' LIMIT 1);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table Account - add account_class_id & collection_severity_id",
				'sAlterSQL'			=>	"	ALTER TABLE 	Account
											ADD COLUMN 		account_class_id 		INT 	UNSIGNED NOT NULL,
											ADD COLUMN 		collection_severity_id 	INT 	UNSIGNED NOT NULL,
											ADD CONSTRAINT	fk_account_account_class_id 		FOREIGN KEY(account_class_id) 		REFERENCES account_class(id) 		ON DELETE RESTRICT	ON UPDATE CASCADE,
											ADD CONSTRAINT	fk_account_collection_severity_id 	FOREIGN KEY(collection_severity_id)	REFERENCES collection_severity(id)	ON DELETE RESTRICT	ON UPDATE CASCADE;",
				'sRollbackSQL'		=>	"	ALTER TABLE			Account
											DROP FOREIGN KEY	fk_account_account_class_id,
											DROP FOREIGN KEY	fk_account_collection_severity_id,
											DROP COLUMN			account_class_id,
											DROP COLUMN			collection_severity_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Give each Account an account_class_id and collection_severity_id",
				'sAlterSQL'			=>	"	UPDATE	Account
											SET		account_class_id = (SELECT id FROM account_class WHERE name = 'Standard' LIMIT 1),
													collection_severity_id = (SELECT id FROM collection_severity WHERE name = 'Zero' LIMIT 1);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Table Invoice - add collectable_id",
				'sAlterSQL'			=>	"	ALTER TABLE 	Invoice
											ADD COLUMN 		collectable_id BIGINT UNSIGNED NULL,
											ADD CONSTRAINT 	fk_invoice_collectable_id FOREIGN KEY(collectable_id) REFERENCES collectable(id) ON DELETE RESTRICT ON UPDATE CASCADE;",
				'sRollbackSQL'		=>	"	ALTER TABLE			Invoice
											DROP FOREIGN KEY	fk_invoice_collectable_id,
											DROP COLUMN			collectable_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Insert a collectable for each Invoice",
				'sAlterSQL'			=>	"	INSERT INTO	collectable (account_id, amount, balance, created_datetime, due_date, invoice_id)
											(SELECT 	Account, (Total + Tax), (Total + Tax), NOW(), DueOn, Id
											FROM		Invoice
											WHERE		Status <> ".INVOICE_TEMP.");",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Update the collectable_id for each Invoice",
				'sAlterSQL'			=>	"	UPDATE	Invoice i
											SET		i.collectable_id = (
														SELECT	id
														FROM 	collectable
														WHERE	invoice_id = i.Id
													);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Create a suspension for each Account with a credit_control_status other than UP_TO_DATE",
				'sAlterSQL'			=>	"	INSERT INTO	collection_suspension (
															account_id,
															start_datetime, 
															proposed_end_datetime, 
															start_employee_id, 
															collection_suspension_reason_id
														)
											SELECT	Id, 
													NOW(), 
													DATE_ADD(NOW(), INTERVAL 14 DAY), 
													".USER_ID.", 
													(CASE
														WHEN 	credit_control_status = ".CREDIT_CONTROL_STATUS_EXTENSION."
														THEN 	(SELECT id FROM collection_suspension_reason WHERE system_name = 'EXTENSION')
														WHEN 	credit_control_status = ".CREDIT_CONTROL_STATUS_SENDING_TO_DEBT_COLLECTION."
														THEN 	(SELECT id FROM collection_suspension_reason WHERE system_name = 'SENDING_TO_DEBT_COLLECTION')
														WHEN 	credit_control_status = ".CREDIT_CONTROL_STATUS_WITH_DEBT_COLLECTION."
														THEN 	(SELECT id FROM collection_suspension_reason WHERE system_name = 'WITH_DEBT_COLLECTION')
														WHEN 	credit_control_status = ".CREDIT_CONTROL_STATUS_WIN_BACK."
														THEN 	(SELECT id FROM collection_suspension_reason WHERE system_name = 'WIN_BACK')
														WHEN 	credit_control_status = ".CREDIT_CONTROL_STATUS_PAYMENT_PLAN."
														THEN 	(SELECT id FROM collection_suspension_reason WHERE system_name = 'PAYMENT_PLAN')
														WHEN 	credit_control_status = ".CREDIT_CONTROL_STATUS_COOLING_OFF."
														THEN 	(SELECT id FROM collection_suspension_reason WHERE system_name = 'COOLING_OFF')
													END)
											FROM	Account 
											WHERE	credit_control_status <> ".CREDIT_CONTROL_STATUS_UP_TO_DATE.";",
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
				'sDescription'		=> "Add new correspondence_run_error DATASET_MISMATCH",
				'sAlterSQL'			=> "INSERT INTO correspondence_run_error(name, description, system_name, const_name)
										VALUES		('Dataset Mismatch', 'Dataset Mismatch', 'DATASET_MISMATCH', 'CORRESPONDENCE_RUN_ERROR_DATASET_MISMATCH');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Add new carrier_module_type MODULE_TYPE_OCA_REFERRAL_FILE",
				'sAlterSQL'			=> "INSERT INTO	carrier_module_type (name, description, const_name)
										VALUES		('OCA Referral File', 'Outside Collection Agency Referral File', 'MODULE_TYPE_OCA_REFERRAL_FILE');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Add new resource_type RESOURCE_TYPE_FILE_EXPORT_DUNN_AND_BRADSTREET_REFERRAL_FILE",
				'sAlterSQL'			=> "INSERT INTO resource_type 	(name, description, const_name, resource_type_nature)
										VALUES 						('Dunn & Bradstreet Referral File', 'Dunn & Bradstreet Referral File', 'RESOURCE_TYPE_FILE_EXPORT_DUNN_AND_BRADSTREET_REFERRAL_FILE', (SELECT id FROM resource_type_nature where const_name='RESOURCE_TYPE_NATURE_EXPORT_FILE'));",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Add new carrier_type CARRIER_TYPE_OCA",
				'sAlterSQL'			=> "INSERT INTO carrier_type	(name, description, const_name) 
										VALUES						('OCA', 'Outside Collections Agency', 'CARRIER_TYPE_OCA');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Add new Carrier CARRIER_DUNN_AND_BRADSTREET",
				'sAlterSQL'			=> "INSERT INTO Carrier	(Name, carrier_type, description, const_name)
										VALUES				('Dunn & Bradstreet', (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_OCA'), 'Dunn & Bradstreet (OCA)', 'CARRIER_DUNN_AND_BRADSTREET');",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=> "Add payment_reversal_type_id and payment_reversal_reason_id to payment_response",
				'sAlterSQL'			=> "	ALTER TABLE		payment_response
											ADD COLUMN		payment_reversal_type_id INT UNSIGNED NULL,
											ADD COLUMN		payment_reversal_reason_id INT UNSIGNED NULL,
											ADD CONSTRAINT 	fk_payment_response_payment_reversal_type_id	FOREIGN KEY (payment_reversal_type_id) 		REFERENCES payment_reversal_type(id)	ON UPDATE CASCADE	ON DELETE RESTRICT,
											ADD CONSTRAINT 	fk_payment_response_payment_reversal_reason_id 	FOREIGN KEY (payment_reversal_reason_id)	REFERENCES payment_reversal_reason(id)	ON UPDATE CASCADE	ON DELETE RESTRICT;",
				'sRollbackSQL'		=> "	ALTER TABLE			payment_response
											DROP FOREIGN KEY	fk_payment_response_payment_reversal_reason_id,
											DROP FOREIGN KEY	fk_payment_response_payment_reversal_type_id,
											DROP COLUMN			payment_reversal_reason_id,											
											DROP COLUMN			payment_reversal_type_id;",
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
			if (MDB2::isError($oResult))
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
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>