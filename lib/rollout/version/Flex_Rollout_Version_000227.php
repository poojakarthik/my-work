<?php

/**
 * Version 227 of database update - email template tables
 */

class Flex_Rollout_Version_000227 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		// Define operations
		$aOperations	=	array
							(
								array
								(
									'sDescription'		=>	"Add table email_template",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS email_template (
															  id BIGINT NOT NULL  AUTO_INCREMENT ,
															  customer_group_id BIGINT(20) NOT NULL ,
															  name VARCHAR(255) NOT NULL ,
															  description VARCHAR(255) NOT NULL ,
															  created_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
															  created_employee_id BIGINT NOT NULL ,
															  PRIMARY KEY (id) ,
															  INDEX fk_email_template_customer_group_id (customer_group_id ASC) ,
															  CONSTRAINT fk_email_template_customer_group_id
															    FOREIGN KEY (customer_group_id )
															    REFERENCES CustomerGroup (Id )
															    ON DELETE RESTRICT
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_template;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table email_template_details",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS email_template_details (
															  id BIGINT NOT NULL  AUTO_INCREMENT ,
															  email_template_id BIGINT NOT NULL ,
															  email_text VARCHAR(10000) NOT NULL ,
															  email_html VARCHAR(10000) NULL ,
															  created_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
															  created_employee_id BIGINT NOT NULL ,
															  effective_datetime DATETIME NOT NULL ,
															  PRIMARY KEY (id) ,
															  INDEX fk_email_template_details_email_template (email_template_id ASC) ,
															  CONSTRAINT fk_email_template_details_email_template
															    FOREIGN KEY (email_template_id )
															    REFERENCES email_template (id )
															    ON DELETE RESTRICT
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_template_details;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'		=>	"Add table email_template_ebill",
									'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS email_template_ebill (
															  id BIGINT NOT NULL  AUTO_INCREMENT ,
															  email_template_id BIGINT NOT NULL ,
															  customer_group_id BIGINT(20) NOT NULL ,
															  PRIMARY KEY (id) ,
															  INDEX fk_ebill_email_template_email_template_id (email_template_id ASC) ,
															  INDEX fk_email_template_ebill_CustomerGroup_id (customer_group_id ASC) ,
															  UNIQUE INDEX customerGroup_id_UNIQUE (customer_group_id ASC) ,
															  CONSTRAINT fk_ebill_email_template_email_template_id
															    FOREIGN KEY (email_template_id )
															    REFERENCES email_template (id )
															    ON DELETE RESTRICT
															    ON UPDATE CASCADE,
															  CONSTRAINT fk_email_template_ebill_CustomerGroup_id
															    FOREIGN KEY (customer_group_id )
															    REFERENCES CustomerGroup (Id )
															    ON DELETE RESTRICT
															    ON UPDATE CASCADE)
															ENGINE = InnoDB;",
									'sRollbackSQL'		=>	"	DROP TABLE IF EXISTS email_template_ebill;",
									'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
								),
								array
								(
									'sDescription'	=> 'Add new invoice_run_type RERATE',
									'sAlterSQL'		=> "	INSERT INTO invoice_run_type (name, description, const_name)
															VALUES ('Invoice Rerate', 'Invoice Rerate', 'INVOICE_RUN_TYPE_RERATE');",
									'sRollbackSQL'	=> "	DELETE FROM invoice_run_type WHERE const_name = 'INVOICE_RUN_TYPE_RERATE';"
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