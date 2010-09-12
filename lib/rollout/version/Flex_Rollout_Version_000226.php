<?php

/**
 * Version 226 of database update.
 */

class Flex_Rollout_Version_000226 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
							/*	array
								(
									'sDescription'		=>	"Add table correspondence_delivery_method",
									'sAlterSQL'			=>	"	CREATE  TABLE IF NOT EXISTS correspondence_delivery_method (
																  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
																  name VARCHAR(255) NOT NULL ,
																  description VARCHAR(255) NOT NULL ,
																  system_name VARCHAR(255) NOT NULL ,
																  const_name VARCHAR(255) NOT NULL ,
																  PRIMARY KEY (id) )
																ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_delivery_method;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table correspondence_run_error",
									'sAlterSQL'			=>	"	CREATE  TABLE IF NOT EXISTS correspondence_run_error (
																  id BIGINT NOT NULL AUTO_INCREMENT ,
																  name VARCHAR(255) NOT NULL ,
																  description VARCHAR(255) NOT NULL ,
																  system_name VARCHAR(255) NOT NULL ,
																  const_name VARCHAR(255) NOT NULL ,
																  PRIMARY KEY (id) )
																ENGINE = InnoDB
																;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_run_error;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"create table correspondence_source_type",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_source_type (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  name VARCHAR(255) NOT NULL ,
															  description VARCHAR(510) NOT NULL ,
															  system_name VARCHAR(255) NOT NULL ,
															  const_name VARCHAR(255) NOT NULL ,
															  class_name VARCHAR(255) NOT NULL ,
															  is_user_selectable TINYINT(4) NOT NULL,
															  PRIMARY KEY (id) )
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_source_type;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table correspondence_source",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_source (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  correspondence_source_type_id BIGINT(20) NOT NULL ,
															  PRIMARY KEY (id) ,
															  INDEX source_type_id (correspondence_source_type_id ASC) ,
															  CONSTRAINT fk_correspondence_source_type_id
															    FOREIGN KEY (correspondence_source_type_id )
															    REFERENCES correspondence_source_type (id )
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_source;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add tablecorrespondence_template",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_template (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  template_code VARCHAR(10) NULL,
															  name VARCHAR(255) NOT NULL ,
															  description VARCHAR(510) NOT NULL ,
															  created_employee_id BIGINT(20) NOT NULL ,
															  created_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
															  correspondence_source_id BIGINT(20) NOT NULL ,
															  carrier_id BIGINT(20) NULL ,
															  status_id TINYINT(4) NOT NULL,
															  PRIMARY KEY (id) ,
															  INDEX correspondence_source_id (correspondence_source_id ASC) ,
															  CONSTRAINT fk_correspondence_template_correspondence_source_id
															    FOREIGN KEY (correspondence_source_id )
															    REFERENCES correspondence_source (id )
															    ON UPDATE CASCADE,
															 CONSTRAINT fk_correspondence_template_carrier_id
															    FOREIGN KEY (carrier_id )
															    REFERENCES Carrier (Id )
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_template;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=> "Add table correspondence_run_batch",
									'sAlterSQL'			=> "CREATE  TABLE IF NOT EXISTS correspondence_run_batch (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  batch_datetime TIMESTAMP NOT NULL ,
															  PRIMARY KEY (id) )
																ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_run_batch;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN

								),
								array
								(
									'sDescription'		=>	"Add table correspondence_run",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_run (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT,
															  correspondence_template_id BIGINT(20) NOT NULL ,
															  processed_datetime TIMESTAMP NULL DEFAULT NULL ,
															  scheduled_datetime DATETIME NOT NULL ,
															  delivered_datetime TIMESTAMP NULL DEFAULT NULL ,
															  created_employee_id INT(11) NOT NULL ,
															  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
															  data_file_export_id BIGINT(20) UNSIGNED NULL ,
															  preprinted TINYINT(3) UNSIGNED NOT NULL,
															  pdf_file_export_id BIGINT(20) UNSIGNED NULL ,
															  correspondence_run_batch_id BIGINT(20) NULL ,
															  correspondence_run_error_id BIGINT NULL,
															  PRIMARY KEY (id) ,
															  INDEX correspondence_template_id (correspondence_template_id ASC) ,
															  INDEX data_file_export_id (data_file_export_id ASC) ,
															  CONSTRAINT fk_correspondence_run_data_file_export
															    FOREIGN KEY (data_file_export_id )
															    REFERENCES FileExport (Id )
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_correspondence_run_pdf_file_export
															    FOREIGN KEY (pdf_file_export_id )
															    REFERENCES FileExport (Id )
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_correspondence_template_id
															    FOREIGN KEY (correspondence_template_id )
															    REFERENCES correspondence_template (id )
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_correspondence_run_correspondence_delivery_batch
															    FOREIGN KEY (correspondence_run_batch_id )
															    REFERENCES correspondence_run_batch (id )
															    ON DELETE RESTRICT
															    ON UPDATE CASCADE,
															    CONSTRAINT fk_correspondence_run_correspondence_error_id
															    FOREIGN KEY (correspondence_run_error_id )
															    REFERENCES correspondence_run_error (id )
															    ON DELETE RESTRICT
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_run;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/
							array
								(
									'sDescription'		=>	"alter correspondence_run, add file import column",
									'sAlterSQL'			=>	"ALTER TABLE correspondence_run ADD COLUMN file_import_id BIGINT(20) UNSIGNED AFTER correspondence_template_id,
															ADD CONSTRAINT fk_correspondence_run_file_import_id FOREIGN KEY fk_correspondence_run_file_import_id (file_import_id)
															    REFERENCES FileImport (Id)
															    ON DELETE RESTRICT
															    ON UPDATE CASCADE;",
									'sRollbackSQL'		=>	"	ALTER TABLE correspondence_run DROP COLUMN file_import_id;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								)
								/*,array
								(
									'sDescription'		=>	"Add table correspondence",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  correspondence_run_id BIGINT(20) NOT NULL ,
															  account_id BIGINT(20) UNSIGNED NULL ,
															  customer_group_id BIGINT(20) NOT NULL,
															  correspondence_delivery_method_id BIGINT(20) NOT NULL ,
															  account_name VARCHAR(255) NOT NULL,
															  title VARCHAR(45) DEFAULT NULL,
															  first_name VARCHAR(255) NOT NULL ,
															  last_name VARCHAR(255) NOT NULL ,
															  address_line_1 VARCHAR(255) NOT NULL ,
															  address_line_2 VARCHAR(255) NULL DEFAULT NULL ,
															  suburb VARCHAR(255) NOT NULL ,
															  postcode CHAR(4) NOT NULL ,
															  state VARCHAR(3) NOT NULL ,
															  email VARCHAR(255) NULL DEFAULT NULL ,
															  mobile VARCHAR(25) NULL DEFAULT NULL ,
															  landline VARCHAR(25) NULL DEFAULT NULL ,
															  pdf_file_path VARCHAR (255) NULL DEFAULT NULL,
															  PRIMARY KEY (id) ,
															  INDEX correspondence_run_id (correspondence_run_id ASC) ,
															  INDEX account_id (account_id ASC) ,
															  INDEX correspondence_delivery_method_id (correspondence_delivery_method_id ASC) ,
															  CONSTRAINT fk_correspondence_account
															    FOREIGN KEY (account_id )
															    REFERENCES Account (Id )
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_correspondence_customergroup_id
															    FOREIGN KEY (customer_group_id )
															    REFERENCES CustomerGroup (Id )
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_correspondence_delivery_method_id
															    FOREIGN KEY (correspondence_delivery_method_id )
															    REFERENCES correspondence_delivery_method (id )
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_correspondence_run
															    FOREIGN KEY (correspondence_run_id )
															    REFERENCES correspondence_run (id )
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create table correspondence_template_column",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_template_column (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  name VARCHAR(255) NOT NULL ,
															  description VARCHAR(500) NOT NULL ,
															  column_index INT(5) NOT NULL ,
															  correspondence_template_id BIGINT(20) NOT NULL ,
															  PRIMARY KEY (id) ,
															  INDEX correspondence_template_id (correspondence_template_id ASC) ,
															  CONSTRAINT fk_correspondence_template_column_correspondence_template
															    FOREIGN KEY (correspondence_template_id )
															    REFERENCES correspondence_template (id )
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_template_column;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Create table correspondence_data",
									'sAlterSQL'			=>	"
																CREATE  TABLE IF NOT EXISTS correspondence_data (
																  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
																  value VARCHAR(2048) NULL DEFAULT NULL ,
																  correspondence_template_column_id BIGINT(20) NOT NULL ,
																  correspondence_id BIGINT(20) NOT NULL ,
																  PRIMARY KEY (id) ,
																  INDEX correspondence_template_column_id (correspondence_template_column_id ASC) ,
																  INDEX correspondence_id (correspondence_id ASC) ,
																  CONSTRAINT fk_correspondence_data_correspondence_template_column
																    FOREIGN KEY (correspondence_template_column_id )
																    REFERENCES correspondence_template_column (id )
																    ON UPDATE CASCADE,
																  CONSTRAINT fk_correspondence_data_correspondence
																    FOREIGN KEY (correspondence_id )
																    REFERENCES correspondence (id )
																    ON DELETE CASCADE
																    ON UPDATE CASCADE)
																ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_data;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),

								array
								(
									'sDescription'		=>	"Add table correspondence_source_sql",
									'sAlterSQL'			=>	"	CREATE  TABLE IF NOT EXISTS correspondence_source_sql (
																  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
																  correspondence_source_id BIGINT(20) NOT NULL ,
																  sql_syntax LONGTEXT NOT NULL ,
																  PRIMARY KEY (id) ,
																  INDEX correspondence_source_id (correspondence_source_id ASC) ,
																  CONSTRAINT fk_correspondence_source_sql_correspondence_source_id
																    FOREIGN KEY (correspondence_source_id )
																    REFERENCES correspondence_source (id )
																    ON UPDATE CASCADE)
																ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_source_sql;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/

							/*	array
								(
									'sDescription'		=>	"Add table correspondence_template_system",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_template_system (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  name VARCHAR(125) NOT NULL ,
															  description VARCHAR(255) NOT NULL ,
															  system_name VARCHAR(255) NOT NULL ,
															  constant_name VARCHAR(255) NOT NULL ,
															  correspondence_template_id BIGINT(20) NULL ,
															  PRIMARY KEY (id) ,
															  INDEX fk_correspondence_system_correspondence_template1 (correspondence_template_id ASC) ,
															  CONSTRAINT fk_correspondence_system_correspondence_template1
															    FOREIGN KEY (correspondence_template_id )
															    REFERENCES correspondence_template (id )
															    ON DELETE SET NULL
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_template_system;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/

								/*array
								(
									'sDescription'		=>	"Add data to carrier_module_type table",
									'sAlterSQL'			=>	"INSERT INTO carrier_module_type (name,  description, const_name)
																VALUES ('Correspondence Export', 'Correspondence Export','MODULE_TYPE_CORRESPONDENCE_EXPORT')",
									'sRollbackSQL'		=>	"	DELETE FROM carrier_module_type WHERE name = 'Correspondence Export';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),

								array
								(
									'sDescription'		=>	"Add data to carrier_type table",
									'sAlterSQL'			=>	"INSERT INTO carrier_type (Name,  description, const_name)
																VALUES ('Mailing House', 'Mailing House','CARRIER_TYPE_MAILINGHOUSE')",
									'sRollbackSQL'		=>	"	DELETE FROM carrier_type WHERE Name = 'Mailing House';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),

								array
								(
									'sDescription'		=>	"Add data to Carrier table",
									'sAlterSQL'			=>	"INSERT INTO Carrier (Name, carrier_type, description, const_name)
																VALUES ('Billprint', (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_MAILINGHOUSE'),'Billprint', 'CARRIER_BILLPRINT')",
									'sRollbackSQL'		=>	"	DELETE FROM Carrier WHERE Name = 'Billprint';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/
							/*	array
								(
									'sDescription'		=>	"Add data to correspondence_run_error table",
									'sAlterSQL'			=>	"INSERT INTO correspondence_run_error (name, description, system_name, const_name) VALUES
																( 'Sql Syntax Error','Sql Syntax Error', 'SQL_SYNTAX', 'CORRESPONDENCE_RUN_ERROR_SQL_SYNTAX'),
																( 'Malformed Input','Malformed Input', 'MALFORMED_INPUT', 'CORRESPONDENCE_RUN_ERROR_MALFORMED_INPUT'),
																( 'No Data','No Data', 'NO_DATA', 'CORRESPONDENCE_RUN_ERROR_NO_DATA');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add data to correspondence_delivery_method table",
									'sAlterSQL'			=>	"INSERT INTO correspondence_delivery_method (name, description, system_name, const_name) VALUES
																( 'Post','Post', 'POST', 'CORRESPONDENCE_DELIVERY_METHOD_POST'),
																( 'Email','Email', 'EMAIL', 'CORRESPONDENCE_DELIVERY_METHOD_EMAIL'),
																( 'SMS','SMS', 'SMS', 'CORRESPONDENCE_DELIVERY_METHOD_SMS');",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add data to correspondence_source_type table",
									'sAlterSQL'			=>	"INSERT INTO correspondence_source_type ( name, description, system_name, const_name, class_name, is_user_selectable) VALUES
																( 'System', 'System', 'SYSTEM', 'CORRESPONDENCE_SOURCE_TYPE_SYSTEM', 'Correspondence_Source_System', 0),
																( 'CSV', 'CSV', 'CSV', 'CORRESPONDENCE_SOURCE_TYPE_CSV', 'Correspondence_Source_CSV', 1),
																( 'SQL', 'SQL', 'SQL', 'CORRESPONDENCE_SOURCE_TYPE_SQL', 'Correspondence_Source_SQL', 1);",

									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add data to correspondence_source table",
									'sAlterSQL'			=>	"INSERT INTO correspondence_source ( correspondence_source_type_id) VALUES
																((SELECT id FROM correspondence_source_type WHERE name = 'System'));",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),

								array
								(
									'sDescription'		=>	"Add data to correspondence_template table",
									'sAlterSQL'			=>	"INSERT INTO correspondence_template ( name, description, created_employee_id, correspondence_source_id, status_id) VALUES
															('Invoice', 'Invoice', 0,  (SELECT id FROM correspondence_source WHERE correspondence_source_type_id = (SELECT id from correspondence_source_type WHERE name = 'System' )),  1);",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),

								array
								(
									'sDescription'		=>	"Add data to correspondence_template_system table",
									'sAlterSQL'			=>	"INSERT INTO correspondence_template_system  (name, description, system_name, constant_name, correspondence_template_id) VALUES
															('invoice', 'invoice','INVOICE', 'CORRESPONDENCE_TEMPLATE_SYSTEM_INVOICE', (SELECT id FROM correspondence_template WHERE name = 'Invoice'));",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/
								/*array
								(
									'sDescription'		=>	"Add data to resource_type table",
									'sAlterSQL'			=>	"INSERT INTO resource_type  (name, description, const_name, resource_type_nature ) VALUES
															('Yellow Billing Correspondence File Export CSV File', 'Yellow Billing Correspondence File Export CSV File', 'RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLING_CSV', 2);															;",
									'sRollbackSQL'		=>	"	DELETE FROM resource_type WHERE name = 'Yellow Billing Correspondence File Export CSV File';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add data to resource_type table",
									'sAlterSQL'			=>	"INSERT INTO resource_type (name, description, const_name, resource_type_nature) VALUES
										('Yellow Billing Correspondence File Export TAR File', 'Yellow Billing Correspondence File Export TAR File', 'RESOURCE_TYPE_FILE_EXPORT_CORRESPONDENCE_YELLOWBILLING_TAR', 2);",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),*/
								/*array
								(
									'sDescription'		=>	"Add data to resource_type table",
									'sAlterSQL'			=>	"INSERT INTO resource_type (name, description, const_name, resource_type_nature) VALUES
										('Yellow Billing Correspondence File Import CSV File', 'Yellow Billing Correspondence File Import CSV File', 'RESOURCE_TYPE_FILE_IMPORT_CORRESPONDENCE_YELLOWBILLING_CSV', 1);",
									'sRollbackSQL'		=>	"	DELETE FROM resource_type WHERE name = 'Yellow Billing Correspondence File Import CSV File';",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								)*/

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