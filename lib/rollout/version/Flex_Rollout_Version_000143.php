<?php

/**
 * Version 143 of database update.
 * This version: -
 *	1:	Rename AccountLetterLog and its Fields to underscored naming convention
 *	2:	Rename DestinationTranslation and its Fields to underscored naming convention
 *	3:	Rename DocumentResourceTypeFileType and its Fields to underscored naming convention
 */

class Flex_Rollout_Version_000143 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Rename AccountLetterLog and its Fields to underscored naming convention
		$strSQL =	"ALTER TABLE AccountLetterLog " .
					"RENAME account_letter_log, " .
					"CHANGE Id			id							BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier', " .
					"CHANGE Account		account_id					BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) Account this log entry belongs to', " .
					"CHANGE Invoice		invoice_id					BIGINT(20)	UNSIGNED	NULL						COMMENT '(FK) Invoice this log relates to', " .
					"CHANGE LetterType	document_template_type_id	BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) Document Template Type this log refers to', " .
					"CHANGE CreatedOn	created_on					TIMESTAMP				NOT NULL					COMMENT 'Creation Timestamp', " .
					"\n" .
					"ADD CONSTRAINT fk_account_letter_log_account_id				FOREIGN KEY	(account_id) 				REFERENCES Account(Id)				ON UPDATE CASCADE ON DELETE RESTRICT, " .
					"ADD CONSTRAINT fk_account_letter_log_invoice_id				FOREIGN KEY	(invoice_id) 				REFERENCES Invoice(Id)				ON UPDATE CASCADE ON DELETE RESTRICT, " .
					"ADD CONSTRAINT fk_account_letter_log_document_template_type_id	FOREIGN KEY	(document_template_type_id) REFERENCES DocumentTemplateType(Id)	ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename AccountLetterLog and its Fields to underscored naming convention. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE account_letter_log " .
								"DROP FOREIGN KEY fk_account_letter_log_account_id, " .
								"DROP FOREIGN KEY fk_account_letter_log_invoice_id, " .
								"DROP FOREIGN KEY fk_account_letter_log_document_template_type_id, " .
								"\n" .
								"RENAME AccountLetterLog, " .
								"CHANGE id							Id			BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier', " .
								"CHANGE account_id					Account		BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) Account this log entry belongs to', " .
								"CHANGE invoice_id					Invoice		BIGINT(20)	UNSIGNED	NULL						COMMENT '(FK) Invoice this log relates to', " .
								"CHANGE document_template_type_id	LetterType	BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) Document Template Type this log refers to', " .
								"CHANGE created_on					CreatedOn	DATETIME				NOT NULL					COMMENT 'Creation Timestamp';";
		
		// 2:	Rename DestinationTranslation and its Fields to underscored naming convention
		$strSQL =	"ALTER TABLE DestinationTranslation " .
					"RENAME cdr_call_type_translation, " .
					"CHANGE Id			id				BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier', " .
					"CHANGE Code		code			BIGINT(20)		UNSIGNED	NOT NULL					COMMENT 'Flex Call Type Code', " .
					"CHANGE Carrier		carrier_id		BIGINT(20)					NOT NULL					COMMENT '(FK) Carrier', " .
					"CHANGE CarrierCode	carrier_code	VARCHAR(255)				NOT NULL					COMMENT 'Carrier Call Type Code', " .
					"CHANGE Description	description		VARCHAR(255)				NOT NULL					COMMENT 'Carrier Call Type Description', " .
					"\n" .
					"ADD CONSTRAINT fk_cdr_call_type_translation_carrier_id			FOREIGN KEY	(carrier_id) 				REFERENCES Carrier(Id)				ON UPDATE CASCADE ON DELETE RESTRICT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename DestinationTranslation and its Fields to underscored naming convention. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE cdr_call_type_translation " .
								"DROP FOREIGN KEY fk_cdr_call_type_translation_carrier_id, " .
								"\n" .
								"RENAME DestinationTranslation, " .
								"CHANGE id				Id			BIGINT(20)		UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier', " .
								"CHANGE code			Code		BIGINT(20)		UNSIGNED	NOT NULL					COMMENT 'Flex Call Type Code', " .
								"CHANGE carrier_id		Carrier		BIGINT(20)					NOT NULL					COMMENT '(FK) Carrier', " .
								"CHANGE carrier_code	CarrierCode	VARCHAR(255)				NOT NULL					COMMENT 'Carrier Call Type Code', " .
								"CHANGE description		Description	VARCHAR(255)				NOT NULL					COMMENT 'Carrier Call Type Description';";
		
		// 3:	Rename DocumentResourceTypeFileType and its Fields to underscored naming convention
		$strSQL =	"ALTER TABLE DocumentResourceTypeFileType " .
					"RENAME document_resource_type_file_type, " .
					"CHANGE Id				id							BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier', " .
					"CHANGE ResourceType	document_resource_type_id	BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) Document Resource Type', " .
					"CHANGE FileType		file_type_id				BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) File Type', " .
					"\n" .
					"ADD CONSTRAINT fk_document_resource_type_file_type_document_resource_type_id	FOREIGN KEY	(document_resource_type_id) REFERENCES DocumentResourceType(Id)	ON UPDATE CASCADE ON DELETE CASCADE, " .
					"ADD CONSTRAINT fk_document_resource_type_file_type_file_type_id				FOREIGN KEY	(file_type_id) 				REFERENCES FileType(Id)				ON UPDATE CASCADE ON DELETE RESTRICT;" .
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename DocumentResourceTypeFileType and its Fields to underscored naming convention. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"ALTER TABLE document_resource_type_file_type " .
								"DROP FOREIGN KEY fk_document_resource_type_file_type_document_resource_type_id, " .
								"DROP FOREIGN KEY fk_document_resource_type_file_type_file_type_id, " .
								"\n" .
								"RENAME DocumentResourceTypeFileType, " .
								"CHANGE id							Id				BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT	COMMENT 'Unique Identifier', " .
								"CHANGE document_resource_type_id	ResourceType	BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) Document Resource Type', " .
								"CHANGE file_type_id				FileType		BIGINT(20)	UNSIGNED	NOT NULL					COMMENT '(FK) File Type';";
			
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