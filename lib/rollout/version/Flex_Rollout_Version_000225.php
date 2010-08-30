<?php

/**
 * Version 225 of database update.
 */

class Flex_Rollout_Version_000225 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table correspondence_delivery_method",
									'sAlterSQL'			=>	"	CREATE  TABLE IF NOT EXISTS correspondence_delivery_method (
																  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
																  name VARCHAR(255) NOT NULL ,
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
									'sDescription'		=>	"create table correspondence_source_type",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_source_type (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  name VARCHAR(255) NOT NULL ,
															  description VARCHAR(510) NOT NULL ,
															  system_name VARCHAR(255) NOT NULL ,
															  const_name VARCHAR(255) NOT NULL ,
															  class_name VARCHAR(255) NOT NULL ,
															  user_selectable TINYINT(4) NOT NULL,
															  PRIMARY KEY (id) )
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_data;",
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
															  name VARCHAR(255) NOT NULL ,
															  description VARCHAR(510) NOT NULL ,
															  created_employee_id BIGINT(20) NOT NULL ,
															  created_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
															  correspondence_source_id BIGINT(20) NOT NULL ,
															  carrier_id BIGINT(20) NOT NULL ,
															  system_name VARCHAR(255) NULL,
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
									'sDescription'		=>	"Add table correspondence_run",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_run (
															  id BIGINT(20) NOT NULL ,
															  correspondence_template_id BIGINT(20) NOT NULL ,
															  processed_datetime TIMESTAMP NULL DEFAULT NULL ,
															  scheduled_datetime DATETIME NOT NULL ,
															  delivered_datetime TIMESTAMP NULL DEFAULT NULL ,
															  created_employee_id INT(11) NOT NULL ,
															  created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
															  file_export_id BIGINT(20) UNSIGNED NOT NULL ,
															  preprinted TINYINT(3) UNSIGNED NOT NULL,
															  tar_file_name VARCHAR(255) NULL,
															  PRIMARY KEY (id) ,
															  INDEX correspondence_template_id (correspondence_template_id ASC) ,
															  INDEX file_export_id (file_export_id ASC) ,
															  CONSTRAINT fk_correspondence_run_file_export
															    FOREIGN KEY (file_export_id )
															    REFERENCES FileExport (Id )
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_correspondence_template_id
															    FOREIGN KEY (correspondence_template_id )
															    REFERENCES correspondence_template (id )
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE correspondence_run;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table correspondence",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence (
															  id BIGINT(20) NOT NULL AUTO_INCREMENT ,
															  account_id BIGINT(20) UNSIGNED NOT NULL ,
															  correspondence_run_id BIGINT(20) NOT NULL ,
															  correspondence_delivery_method_id BIGINT(20) NOT NULL ,
															  title VARCHAR(45) DEFAULT NULL,
															  first_name VARCHAR(255) NOT NULL ,
															  last_name VARCHAR(255) NOT NULL ,
															  address_line_1 VARCHAR(255) NOT NULL ,
															  address_line2 VARCHAR(255) NULL DEFAULT NULL ,
															  suburb VARCHAR(255) NOT NULL ,
															  postcode CHAR(4) NOT NULL ,
															  state VARCHAR(3) NOT NULL ,
															  email VARCHAR(255) NULL DEFAULT NULL ,
															  mobile VARCHAR(25) NULL DEFAULT NULL ,
															  landline VARCHAR(25) NULL DEFAULT NULL ,
															  tar_file_path VARCHAR (255) NULL DEFAULT NULL,
															  PRIMARY KEY (id) ,
															  INDEX correspondence_run_id (correspondence_run_id ASC) ,
															  INDEX account_id (account_id ASC) ,
															  INDEX correspondence_delivery_method_id (correspondence_delivery_method_id ASC) ,
															  CONSTRAINT fk_correspondence_account
															    FOREIGN KEY (account_id )
															    REFERENCES Account (Id )
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
									'sRollbackSQL'		=>	"	DROP TABLE orrespondence_template_column;",
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