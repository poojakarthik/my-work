<?php

/**
 * Version 236 of database update.
 */

class Flex_Rollout_Version_000236 extends Flex_Rollout_Version
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
				'sDescription'		=>	"Remove foreign key fk_email_template_email_template_type_id from email_template table in preparation for renaming email_template_type_id to email_template_id",
				'sAlterSQL'			=>	"	ALTER TABLE email_template
											DROP FOREIGN KEY fk_email_template_email_template_type_id;",
				'sRollbackSQL'		=>	"	ALTER TABLE email_template
											ADD CONSTRAINT fk_email_template_email_template_type_id FOREIGN KEY (email_template_type_id) REFERENCES email_template_type (id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Rename email_template_type_id to email_template_id in the email_template table",
				'sAlterSQL'			=>	"	ALTER TABLE email_template
											CHANGE email_template_type_id email_template_id BIGINT NOT NULL;",
				'sRollbackSQL'		=>	"	ALTER TABLE email_template
											CHANGE email_template_id email_template_type_id BIGINT NOT NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Recreate foreign key fk_email_template_email_template_id on email_template",
				'sAlterSQL'			=>	"	ALTER TABLE email_template
											ADD CONSTRAINT fk_email_template_email_template_id FOREIGN KEY (email_template_id) REFERENCES email_template_type (id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL'		=>	"	ALTER TABLE email_template
											DROP FOREIGN KEY fk_email_template_email_template_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Rename email_template to email_template_customer_group",
				'sAlterSQL'			=>	"	RENAME TABLE email_template TO email_template_customer_group;",
				'sRollbackSQL'		=>	"	RENAME TABLE email_template_customer_group TO email_template;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Remove const_name & make system_name nullable from the email_template_type table",
				'sAlterSQL'			=>	"	ALTER TABLE email_template_type
											DROP COLUMN const_name,
											CHANGE system_name system_name VARCHAR(45) NULL,
											CHANGE class_name class_name VARCHAR(255) NULL;",
				'sRollbackSQL'		=>	"	ALTER TABLE email_template_type
											ADD COLUMN const_name VARCHAR(45) NOT NULL,
											CHANGE system_name system_name VARCHAR(45) NOT NULL,
											CHANGE class_name class_name VARCHAR(255) NOT NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Rename email_template_type to email_template",
				'sAlterSQL'			=>	"	RENAME TABLE email_template_type TO email_template;",
				'sRollbackSQL'		=>	"	RENAME TABLE email_template TO email_template_type;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Remove foreign key fk_email_template_details_email_template from email_template_details table in preparation for renaming email_template_id to email_template_customer_group_id",
				'sAlterSQL'			=>	"	ALTER TABLE email_template_details
											DROP FOREIGN KEY fk_email_template_details_email_template;",
				'sRollbackSQL'		=>	"	ALTER TABLE email_template_details
											ADD CONSTRAINT fk_email_template_details_email_template FOREIGN KEY (email_template_id) REFERENCES email_template (id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add email_from & rename email_template_id to email_template_customer_group_id on the email_template_details table",
				'sAlterSQL'			=>	"	ALTER TABLE email_template_details
											ADD COLUMN email_from VARCHAR(128) NOT NULL,
											CHANGE email_template_id email_template_customer_group_id BIGINT NOT NULL;",
				'sRollbackSQL'		=>	"	ALTER TABLE email_template_details
											DROP COLUMN email_from,
											CHANGE email_template_customer_group_id email_template_id BIGINT NOT NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Recreate foreign key fk_email_template_details_email_template_customer_group_id on email_template_details",
				'sAlterSQL'			=>	"	ALTER TABLE email_template_details
											ADD CONSTRAINT fk_email_template_details_email_template_customer_group_id FOREIGN KEY (email_template_customer_group_id) REFERENCES email_template_customer_group (id) ON UPDATE CASCADE ON DELETE RESTRICT;",
				'sRollbackSQL'		=>	"	ALTER TABLE email_template_details
											DROP FOREIGN KEY fk_email_template_details_email_template_customer_group_id;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array(
				'sDescription'		=>	"Set default email_from values for email_template_details",
				'sAlterSQL'			=>	"	UPDATE  email_template_details etd
											JOIN    (
											            select etd.id AS etd_id, cg.email_domain AS email_domain
											            from CustomerGroup cg
											            join email_template_customer_group etcg on (etcg.customer_group_id = cg.Id)
											            join email_template_details etd on (etd.email_template_customer_group_id = etcg.id)
											        ) etd_email_domain ON etd_email_domain.etd_id = etd.id
											SET     etd.email_from = CONCAT('contact@', etd_email_domain.email_domain);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Create email_template_correspondence table",
				'sAlterSQL'			=>	"	CREATE TABLE email_template_correspondence
											(
												id							BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT \"Unique Identifier\",
												email_template_id			BIGINT				NOT NULL					COMMENT \"(FK) email_template\",
												datasource_sql				TEXT				NOT NULL					COMMENT \"Query to retrieve the variables that are required for the email template\",
												correspondence_template_id	BIGINT				NOT NULL					COMMENT \"(FK) correspondence_template\",
												PRIMARY KEY (id),
												CONSTRAINT fk_email_template_correspondence_email_template_id			FOREIGN KEY (email_template_id)				REFERENCES email_template (id)			ON UPDATE CASCADE	ON DELETE RESTRICT,
												CONSTRAINT fk_email_template_correspondence_correspondence_template_id	FOREIGN KEY (correspondence_template_id)	REFERENCES correspondence_template (id)	ON UPDATE CASCADE	ON DELETE RESTRICT	
											) ENGINE=InnoDB;",
				'sRollbackSQL'		=>	"	DROP TABLE email_template_correspondence;",
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