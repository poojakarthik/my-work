<?php

/**
 * Version 237 of database update.
 */

class Flex_Rollout_Version_000237 extends Flex_Rollout_Version
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
				'sDescription'		=>	"Create template_carrier table",
				'sAlterSQL'			=>	"	CREATE TABLE IF NOT EXISTS correspondence_template_carrier_module
											(
											  id 				BIGINT 					AUTO_INCREMENT 	NOT NULL,
											  carrier_module_id	BIGINT 		UNSIGNED 					NOT NULL,
											  template_code 	VARCHAR(45) 							NOT NULL,
											  PRIMARY KEY (id),
											  CONSTRAINT fk_correspondence_template_carrier_module_carrier_module_id
											    FOREIGN KEY (carrier_module_id )
											    REFERENCES CarrierModule (Id )
											    ON DELETE RESTRICT
											    ON UPDATE CASCADE
											)ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	drop table correspondence_template_carrier_module;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Create table correspondence_template_correspondence_template_carrier_module",
				'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_template_correspondence_template_carrier_module
										(
										  id BIGINT AUTO_INCREMENT NOT NULL ,
										  correspondence_template_id BIGINT(20) NOT NULL ,
										  correspondence_template_carrier_module_id BIGINT(20) NOT NULL ,
										  correspondence_delivery_method_id BIGINT(20) NOT NULL ,
										  PRIMARY KEY (id) ,
										  INDEX correspondence_template_correspondence_template_carrier_module_1 (correspondence_template_id ASC) ,
										  INDEX correspondence_template_correspondence_template_carrier_module_2 (correspondence_template_carrier_module_id ASC) ,
										  INDEX correspondence_template_correspondence_template_carrier_module_3 (correspondence_delivery_method_id ASC) ,
										  CONSTRAINT fk_correspondence_template_correspondence_template_c_m_1
										    FOREIGN KEY (correspondence_template_id )
										    REFERENCES correspondence_template (id )
										    ON DELETE NO ACTION
										    ON UPDATE NO ACTION,
										  CONSTRAINT fk_correspondence_template_correspondence_template_c_m_2
										    FOREIGN KEY (correspondence_template_carrier_module_id )
										    REFERENCES correspondence_template_carrier_module (id )
										    ON DELETE RESTRICT
										    ON UPDATE CASCADE,
										  CONSTRAINT fk_correspondence_template_correspondence_template_c_m_3
										    FOREIGN KEY (correspondence_delivery_method_id )
										    REFERENCES correspondence_delivery_method (id )
										    ON DELETE RESTRICT
										    ON UPDATE CASCADE
										)ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"	DROP table correspondence_template_correspondence_template_carrier_module;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Create table correspondence_run_dispatch",
				'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_run_dispatch (
										  id BIGINT  AUTO_INCREMENT NOT NULL ,
										  correspondence_run_id BIGINT(20) NOT NULL ,
										  correspondence_run_batch_id BIGINT(20),
										  data_file_export_id BIGINT(20) UNSIGNED NULL ,
										  pdf_file_export_id BIGINT(20) UNSIGNED  ,
										  correspondence_template_carrier_module_id BIGINT(20) NOT NULL ,
										  PRIMARY KEY (id) ,
										  CONSTRAINT fk_correspondence_run_dispatch_correspondence_run_id
										    FOREIGN KEY (correspondence_run_id )
										    REFERENCES correspondence_run (id )
										    ON DELETE  RESTRICT
										    ON UPDATE  CASCADE,
										  CONSTRAINT fk_correspondence_run_dispatch_correspondence_delivery_batch
												FOREIGN KEY (correspondence_run_batch_id )
												REFERENCES correspondence_run_batch (id )
												ON DELETE RESTRICT
												ON UPDATE CASCADE,
										  CONSTRAINT fk_correspondence_run_dispatch_file_export_id
										    FOREIGN KEY (data_file_export_id )
										    REFERENCES FileExport (Id )
										    ON DELETE  RESTRICT
										    ON UPDATE  CASCADE,
										  CONSTRAINT fk_correspondence_run_dispatch_pdf_file_export_id
										    FOREIGN KEY (pdf_file_export_id )
										    REFERENCES FileExport (Id )
										   ON DELETE  RESTRICT
										    ON UPDATE  CASCADE,
										  CONSTRAINT fk_correspondence_run_dispatch_ct_carrier_id
										    FOREIGN KEY (correspondence_template_carrier_module_id )
										    REFERENCES correspondence_template_carrier_module (id )
										     ON DELETE  RESTRICT
										    ON UPDATE  CASCADE
										)ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"drop table correspondence_run_dispatch;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Create table correspondence_run_dispatch_delivery_method",
				'sAlterSQL'			=>	"CREATE  TABLE IF NOT EXISTS correspondence_run_dispatch_delivery_method
										(
										  id BIGINT(20) AUTO_INCREMENT NOT NULL ,
										  correspondence_run_dispatch_id BIGINT NOT NULL ,
										  correspondence_delivery_method_id BIGINT(20) NOT NULL ,
										  PRIMARY KEY (id) ,
										  CONSTRAINT fk_correspondence_run_dispatch_deliverymethod_cr_fexport_id
										    FOREIGN KEY (correspondence_run_dispatch_id )
										    REFERENCES correspondence_run_dispatch (id )
										    ON DELETE  RESTRICT
										    ON UPDATE  CASCADE,
										  CONSTRAINT fk_correspondence_run_dispatch_deliverymethod_c_del_mth_id
										    FOREIGN KEY (correspondence_delivery_method_id )
										    REFERENCES correspondence_delivery_method (id )
										   ON DELETE  RESTRICT
										    ON UPDATE CASCADE
										)ENGINE = InnoDB;",
				'sRollbackSQL'		=>	"drop table correspondence_run_dispatch_delivery_method;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Copy data into correspondence_template_carrier_module from correspondence_template",
				'sAlterSQL'			=>	"	INSERT INTO correspondence_template_carrier_module (carrier_module_id, template_code)
												SELECT 	cm.Id, ct.template_code
												FROM 	correspondence_template ct
												JOIN	CarrierModule cm ON
														(
															cm.Id = (
																SELECT	Id
																FROM	CarrierModule
																WHERE	Carrier = ct.carrier_id
																AND 	Type = ".MODULE_TYPE_CORRESPONDENCE_EXPORT."
																ORDER BY Id ASC
																LIMIT 1
															)
														)
												WHERE 	ct.carrier_id IS NOT NULL
												AND 	ct.template_code IS NOT NULL
												AND		cm.Id IS NOT NULL;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"copy data from correspondence_template and correspondence_template_carrier_module to correspondence_template_correspondence_template_carrier_module",
				'sAlterSQL'			=>	"INSERT INTO correspondence_template_correspondence_template_carrier_module (correspondence_template_id, correspondence_template_carrier_module_id, correspondence_delivery_method_id)
											SELECT c.id, ctc.id,  (select id from correspondence_delivery_method cd WHERE cd.const_name = 'CORRESPONDENCE_DELIVERY_METHOD_POST')
											FROM correspondence_template c
												JOIN correspondence_template_carrier_module ctc ON (c.template_code = ctc.template_code);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"copy data from correspondence_run and correspondence_template_carrier_module to correspondence_run_dispatch",
				'sAlterSQL'			=>	"INSERT INTO correspondence_run_dispatch (correspondence_run_id, correspondence_run_batch_id, data_file_export_id, pdf_file_export_id, correspondence_template_carrier_module_id)
											SELECT r.id, r.correspondence_run_batch_id, r.data_file_export_id, r.pdf_file_export_id, ctc.id
											FROM correspondence_run r
												JOIN correspondence_template c ON (r.correspondence_template_id = c.id and r.correspondence_run_batch_id IS NOT NULL)
												JOIN correspondence_template_carrier_module ctc ON (c.carrier_id = ctc.carrier_module_id AND c.template_code = ctc.template_code);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"copy data from correspondence_run_dispatch and correspondence (we're creating a record for each delivery method that the dispatched file contains) to correspondence_run_dispatch_delivery_method",
				'sAlterSQL'			=>	"INSERT INTO correspondence_run_dispatch_delivery_method (correspondence_run_dispatch_id, correspondence_delivery_method_id)
											SELECT distinct rf.id, c.correspondence_delivery_method_id
											FROM correspondence_run_dispatch rf
												JOIN correspondence_run r ON (rf.correspondence_run_id = r.id)
												JOIN correspondence c ON (r.id = c.correspondence_run_id);",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"drop columns in correspondence_run",
				'sAlterSQL'			=>	"ALTER TABLE correspondence_run
											DROP FOREIGN KEY fk_correspondence_run_data_file_export,
											DROP FOREIGN KEY fk_correspondence_run_pdf_file_export,
											DROP FOREIGN KEY fk_correspondence_run_correspondence_delivery_batch,
											DROP COLUMN data_file_export_id ,
											DROP COLUMN pdf_file_export_id,
											DROP COLUMN correspondence_run_batch_id;",
				'sRollbackSQL'		=>	"ALTER TABLE correspondence_run
											ADD COLUMN data_file_export_id BIGINT(20) UNSIGNED  AFTER created ,
											ADD COLUMN pdf_file_export_id BIGINT(20) UNSIGNED  AFTER preprinted ,
											ADD COLUMN correspondence_run_batch_id BIGINT(20) AFTER preprinted,
											ADD CONSTRAINT fk_correspondence_run_data_file_export
												FOREIGN KEY (data_file_export_id )
												REFERENCES FileExport (Id )
												ON DELETE RESTRICT
												ON UPDATE CASCADE,
											ADD CONSTRAINT fk_correspondence_run_correspondence_delivery_batch
												FOREIGN KEY (correspondence_run_batch_id )
												REFERENCES correspondence_run_batch (id )
												ON DELETE RESTRICT
												ON UPDATE CASCADE,
											ADD CONSTRAINT fk_correspondence_run_pdf_file_export
												FOREIGN KEY (data_file_export_id )
												REFERENCES FileExport (Id )
												ON DELETE RESTRICT
												ON UPDATE CASCADE;     ",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"drop columns in correspondence_template",
				'sAlterSQL'			=>	"ALTER TABLE correspondence_template
											DROP FOREIGN KEY fk_correspondence_template_carrier_id,
											DROP COLUMN carrier_id ,
											DROP COLUMN template_code;",
				'sRollbackSQL'		=>	" ALTER TABLE correspondence_template
											ADD COLUMN carrier_id BIGINT(20)   AFTER correspondence_source_id ,
											ADD COLUMN template_code VARCHAR(45)  AFTER id ,
											ADD CONSTRAINT fk_correspondence_template_carrier_id
												FOREIGN KEY (carrier_id )
												REFERENCES Carrier (Id )
												ON DELETE RESTRICT
												ON UPDATE CASCADE;",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"update resource_type nature",
				'sAlterSQL'			=>	"UPDATE resource_type_nature SET name = 'API', description = 'API', const_name = 'RESOURCE_TYPE_NATURE_API'
										WHERE const_name = 'RESOURCE_TYPE_NATURE_SOAP';",
				'sDataSourceName'	=> FLEX_DATABASE_CONNECTION_ADMIN
			),
			array
			(
				'sDescription'		=>	"Add data to resource_type table",
				'sAlterSQL'			=>	"INSERT INTO resource_type  (name, description, const_name, resource_type_nature ) VALUES
										('FLex Correspondence API', 'Flex Correspondence API', 'RESOURCE_TYPE_FLEX_CORRESPONDENCE_API', (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_API'));															;",
				'sRollbackSQL'		=>	"	DELETE FROM resource_type WHERE name = 'FLex Correspondence API';",
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