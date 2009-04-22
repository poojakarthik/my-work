<?php

/**
 * Version 170 of database update.
 * This version: -
 *	
 *	1:	Drop the ProvisioningExport, ServiceRecurringCharge, and RatePlanRecurringChargeType Tables
 *	2:	Postgres-ify EmployeeAccountAudit
 *	3:	Postgres-ify RecordTypeTranslation
 *	4:	Add address_locality Table
 *
 */

class Flex_Rollout_Version_000169 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Drop the ProvisioningExport, ServiceRecurringCharge, and RatePlanRecurringChargeType Tables
		$strSQL = "	DROP TABLE IF EXISTS	ProvisioningExport,
											ServiceRecurringCharge,
											RatePlanRecurringChargeType;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the ProvisioningExport, ServiceRecurringCharge, and RatePlanRecurringChargeType Tables. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		//	2:	Postgres-ify EmployeeAccountAudit
		$strSQL = "	ALTER TABLE	EmployeeAccountAudit
					RENAME	employee_account_log,
					
					CHANGE	Id			id			BIGINT	UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
					CHANGE	Employee	employee_id	BIGINT	UNSIGNED	NOT NULL								COMMENT '(FK) Employee who accessed the Account',
					CHANGE	Account		account_id	BIGINT	UNSIGNED	NOT NULL								COMMENT '(FK) Account that was accessed',
					CHANGE	Contact		contact_id	BIGINT	UNSIGNED	NULL									COMMENT '(FK) Contact that was accessed',
					CHANGE	RequestedOn	viewed_on	TIMESTAMP			NOT NULL	DEFAULT CURRENT_TIMESTAMP	COMMENT '(FK) Contact that was accessed',
					
					ADD CONSTRAINT	fk_employee_account_log_employee_id	FOREIGN KEY (employee_id)	REFERENCES Employee(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
					ADD CONSTRAINT	fk_employee_account_log_account_id	FOREIGN KEY (account_id)	REFERENCES Account(Id)	ON UPDATE CASCADE	ON DELETE CASCADE,
					ADD CONSTRAINT	fk_employee_account_log_contact_id	FOREIGN KEY (contact_id)	REFERENCES Contact(Id)	ON UPDATE CASCADE	ON DELETE CASCADE;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to Postgres-ify EmployeeAccountAudit. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	employee_account_log
									RENAME	EmployeeAccountAudit,
									
									DROP FOREIGN KEY	fk_employee_account_log_employee_id,
									DROP FOREIGN KEY	fk_employee_account_log_account_id,
									DROP FOREIGN KEY	fk_employee_account_log_contact_id,
									
									CHANGE	id			Id			BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT,
									CHANGE	employee_id	Employee	BIGINT(20)	UNSIGNED	NOT NULL,
									CHANGE	account_id	Account		BIGINT(20)	UNSIGNED	NOT NULL,
									CHANGE	contact_id	Contact		BIGINT(20)	UNSIGNED	NULL,
									CHANGE	viewed_on	RequestedOn	DATETIME				NOT NULL;";
		
		//	3:	Postgres-ify RecordTypeTranslation
		$strSQL = "	ALTER TABLE	RecordTypeTranslation
					RENAME	cdr_call_group_translation,
					
					CHANGE	Id			id				BIGINT		UNSIGNED	NOT NULL	AUTO_INCREMENT				COMMENT 'Unique Identifier',
					CHANGE	Code		code			VARCHAR(25)				NOT NULL								COMMENT 'Flex Call Group Code',
					CHANGE	Carrier		carrier_id		BIGINT					NOT NULL								COMMENT '(FK) Carrier',
					CHANGE	CarrierCode	carrier_code	VARCHAR(255)			NOT NULL								COMMENT 'Carrier Call Group Code',
					CHANGE	Description	description		VARCHAR(255)			NOT NULL								COMMENT 'Carrier Call Group Description',
					
					ADD CONSTRAINT	fk_cdr_call_group_translation_carrier_id	FOREIGN KEY (carrier_id)	REFERENCES Carrier(Id)	ON UPDATE CASCADE	ON DELETE CASCADE;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to Postgres-ify RecordTypeTranslation. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	cdr_call_group_translation
									RENAME	RecordTypeTranslation,
									
									DROP FOREIGN KEY	fk_cdr_call_group_translation_carrier_id,
									
									CHANGE	id				Id			BIGINT(20)	UNSIGNED	NOT NULL	AUTO_INCREMENT,
									CHANGE	code			Code		VARCHAR(25)				NOT NULL,
									CHANGE	carrier_id		Carrier		BIGINT(20)				NOT NULL,
									CHANGE	carrier_code	CarrierCode	VARCHAR(255)			NOT NULL,
									CHANGE	description		Description	VARCHAR(255)			NOT NULL;";
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