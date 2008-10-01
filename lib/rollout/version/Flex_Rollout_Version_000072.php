<?php

/**
 * Version 72 (Seventy-One) of database update.
 * This version: -
 * 	1:	Adds the System User (User Id: 0) to the Employee table
 * 	2:	Updates all references to Employee == 999999999 to equal 0 (the system user id)
 *	3:	Creates the account_history table
 *	4:	Populates it with the current state of the Account table
 */

class Flex_Rollout_Version_000072 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	const OLD_SYSTEM_USER_EMPLOYEE_ID = 999999999;
	const NEW_SYSTEM_USER_EMPLOYEE_ID = 0;
	
	public function rollout()
	{
		$intNewSystemUserId = self::NEW_SYSTEM_USER_EMPLOYEE_ID;
		$intOldSystemUserId = self::OLD_SYSTEM_USER_EMPLOYEE_ID;
				
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the System User (User Id: 0) to the Employee table
		// Because of the auto increment field, the record has to be added to the end, and then have its Id changed to 0
		self::outputMessage("Creating System User Employee record with id = $intNewSystemUserId ...\n");
		
		$strSQL = "	INSERT INTO Employee	(	Id, FirstName, LastName, UserName, PassWord, Phone, Mobile, Extension, 
												Email, DOB, SessionId, SessionExpire, Session, Karma, PabloSays, 
												Privileges, Archived, user_role_id
											)
					VALUES 					(	NULL, 'System', '', '', 'no password', NULL , NULL , NULL , 
												'',	'0000-00-00', '', '0000-00-00 00:00:00', '', 0, 0, 
												9223372036854775807, 0, '1'
											);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the system user employee record to the Employee table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE Employee AUTO_INCREMENT = 1;";
		$this->rollbackSQL[] = "DELETE FROM Employee WHERE FirstName = 'System' AND LastName = '' AND UserName = '' AND PassWord = 'no password';";
		
		$strSQL = "	UPDATE Employee
					SET Id = $intNewSystemUserId
					WHERE FirstName = 'System' AND LastName = '' AND UserName = '' AND PassWord = 'no password';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to change the id of the system user employee record to $intNewSystemUserId. " . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM Employee WHERE FirstName = 'System' AND LastName = '' AND UserName = '' AND PassWord = 'no password';";
		// This should recalculate the auto_increment counter properly, if the above 2 queries have to rolled back
		
		$strSQL = "ALTER TABLE Employee AUTO_INCREMENT = 1;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to recalculate the proper auto increment counter for the Employee table. ' . $result->getMessage());
		}
		
		// 2:	Update all references of Employee == OLD_SYSTEM_USER_EMPLOYEE_ID to equal NEW_SYSTEM_USER_EMPLOYEE_ID (the system user id)
		// Note that I am not updating records where the employee record IS NULL, as this could mean that there is no employee record associated with the record
		$arrUpdates = array();
		$arrUpdates[] = array("Table"	=> "CreditCard",					"Column"	=> "employee_id");
		$arrUpdates[] = array("Table"	=> "DataReportSchedule",			"Column"	=> "Employee");
		$arrUpdates[] = array("Table"	=> "DirectDebit",					"Column"	=> "employee_id");
		$arrUpdates[] = array("Table"	=> "EmployeeAccountAudit",			"Column"	=> "Employee");
		$arrUpdates[] = array("Table"	=> "Note",							"Column"	=> "Employee");
		$arrUpdates[] = array("Table"	=> "ProvisioningRequest",			"Column"	=> "Employee");
		$arrUpdates[] = array("Table"	=> "Request",						"Column"	=> "Employee");
		$arrUpdates[] = array("Table"	=> "account_status_history",		"Column"	=> "employee");
		$arrUpdates[] = array("Table"	=> "credit_control_status_history",	"Column"	=> "employee");
		$arrUpdates[] = array("Table"	=> "payment_terms",					"Column"	=> "employee");
		$arrUpdates[] = array("Table"	=> "ticketing_user",				"Column"	=> "employee_id");
		$arrUpdates[] = array("Table"	=> "credit_card_payment_history",	"Column"	=> "employee_id");
		$arrUpdates[] = array("Table"	=> "Charge",						"Column"	=> "CreatedBy");
		$arrUpdates[] = array("Table"	=> "Charge",						"Column"	=> "ApprovedBy");
		$arrUpdates[] = array("Table"	=> "Payment",						"Column"	=> "EnteredBy");
		$arrUpdates[] = array("Table"	=> "RecurringCharge",				"Column"	=> "CreatedBy");
		$arrUpdates[] = array("Table"	=> "RecurringCharge",				"Column"	=> "ApprovedBy");
		$arrUpdates[] = array("Table"	=> "Service",						"Column"	=> "CreatedBy");
		$arrUpdates[] = array("Table"	=> "Service",						"Column"	=> "ClosedBy");
		$arrUpdates[] = array("Table"	=> "ServiceRateGroup",				"Column"	=> "CreatedBy");
		$arrUpdates[] = array("Table"	=> "ServiceRatePlan",				"Column"	=> "CreatedBy");
		$arrUpdates[] = array("Table"	=> "ServiceRecurringCharge",		"Column"	=> "CreatedBy");

		
		foreach ($arrUpdates as $arrDetails)
		{
			$strSQL = "	UPDATE {$arrDetails['Table']}
						SET {$arrDetails['Column']} = $intNewSystemUserId
						WHERE {$arrDetails['Column']} = $intOldSystemUserId;";
			
			self::outputMessage("Updating references to the System User Employee id for {$arrDetails['Table']}.{$arrDetails['Column']} ...\n");
			
			$result = $dbAdmin->query($strSQL);
			if (PEAR::isError($result))
			{
				throw new Exception(__CLASS__ . " Failed to update references to System User Employee id for {$arrDetails['Table']}.{$arrDetails['Column']} - " . $result->getMessage());
			}
			// Rollback should be handled by the transaction
			$this->rollbackSQL[] = "UPDATE {$arrDetails['Table']}
									SET {$arrDetails['Column']} = $intOldSystemUserId
									WHERE {$arrDetails['Column']} = $intNewSystemUserId;";
		}
		
		// 3:	Create the account_history table
		self::outputMessage("Creating account_history table ...\n");
		
		// First drop the table if it already exists
		$strSQL	= "DROP TABLE IF EXISTS account_history;";
		$result	= $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . " Failed to drop (if exists) the account_history table.  (it might already exist but shouldn't) - " . $result->getMessage());
		}
		
		$strSQL = "	CREATE TABLE account_history
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for this record',
						change_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'time at which the change in the account record was made',
						employee_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'reference to the employee who changed the state of the account record',
						account_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'foreign key into Account table',

						billing_type INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'defines how the invoice is paid by the customer',
						credit_card_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'defines the (direct debit) credit card details',
						direct_debit_id BIGINT(20) UNSIGNED NULL DEFAULT NULL COMMENT 'defines the (direct debit) bank account details',
						billing_method INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'defines how the invoice is sent to the customer',
						disable_ddr TINYINT(1) NULL DEFAULT NULL COMMENT 'boolean flagging the disabling of the admin fee. 0 = charge lpf, 1 = don''t charge lpf',
						late_payment_amnesty DATE DEFAULT NULL COMMENT 'If this is set, no late payment notices are generated until after this date',
						tio_reference_number VARCHAR(150) DEFAULT NULL COMMENT 'reference number when dealing with the T.I.O.',

						INDEX account_id (account_id),
						INDEX employee_id (employee_id),
						CONSTRAINT fk_account_id FOREIGN KEY (account_id) REFERENCES Account(Id) ON DELETE CASCADE ON UPDATE CASCADE,
						CONSTRAINT fk_employee_id FOREIGN KEY (employee_id) REFERENCES Employee(Id) ON DELETE CASCADE ON UPDATE CASCADE
					) ENGINE = innodb COMMENT = 'history of account table';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to create account_history table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS account_history;";

		// 4:	Populate it with the current state of the Account table
		self::outputMessage("Populating account_history table with current state of all Account records ...\n");
		$strSQL = "	INSERT INTO account_history (employee_id, change_timestamp, account_id, billing_type, credit_card_id, direct_debit_id, billing_method, disable_ddr, late_payment_amnesty, tio_reference_number)
					SELECT $intNewSystemUserId, NOW(), Id, BillingType, CreditCard, DirectDebit, BillingMethod, DisableDDR, LatePaymentAmnesty, tio_reference_number
					FROM Account;";

		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to perform initial population of the account_history table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM account_history;";
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
