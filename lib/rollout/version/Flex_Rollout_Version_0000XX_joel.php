<?php

/**
 * Version XX (ex-ex) of database update.
 * This version: -
 *	1:	Adds customer_status table
 *	2:	Populates the customer_status table
 *	3:	Adds customer_status_action table
 *	4:	Adds customer_status_history table
 *	5:	Adds the user_role table
 *	6:	Populates the user_role table
 *	7:	Adds the user_role_id column to the Employee table (FK into user_role table)
 */

class Flex_Rollout_Version_0000XX extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add customer_status table
		$strSQL = "CREATE TABLE customer_status
					(
						id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY COMMENT 'Unique Id for this Customer Status',
						name VARCHAR(50) NOT NULL COMMENT 'The status name',
						description VARCHAR(1000) NOT NULL COMMENT 'A description of the criteria required to satisfy this status',
						default_action_description VARCHAR(1000) NOT NULL COMMENT 'Description of what credit control personel should do with customer',
						precedence INT(10) NOT NULL COMMENT 'Order in which the statuses are tested. Customer status with precedence 1 will be tested before customer status with precidence 2.  Customer is assigned the first status which it satisfies',
						test VARCHAR(255) NOT NULL COMMENT 'Identifies the ''test'' which is used to check that the criteria has been met'
					) ENGINE = innodb COMMENT = 'Defines the various Customer Statuses and how they are tested';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create customer_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS customer_status";
		
		// 2:	Populate the customer_status table
		$strSQL = "INSERT INTO customer_status (id, name, description, default_action_description, precedence, test) VALUES
					(1, 'L', 'Lost Customer (all services lost)', 'Collect any outstanding monies and attempt win back.', 1, 'LostCustomer'),
					(2, 'J', 'T.I.O issue pending', 'This account cannot be credit managed or barred.  Handled by Customer Service Manager only.', 2, 'AccountWithTIO'),
					(3, 'I', 'Account has dispute with invoice', 'Collect undisputed amount.  Dispute handled by CSM or TL.', 3, 'AccountInDispute'),
					(4, 'H', 'Account has gone to Austral', 'Customer to be handled by Credit Control department.', 4, 'AccountWithAustral'),
					(5, 'G', 'Account is ready for Austral but has not gone there yet', 'Customer to be handled by Credit Control department.', 5, 'AccountReadyForAustral'),
					(6, 'F', 'Final Demand has been sent (implies the account was still unpaid at day 36 and TDC has been applied)', 'Incoming call from customer must be redirected to Credit Control department.', 6, 'AccountSentFinalDemand'),
					(7, 'E', 'Account has been auto barred (implies the account was still unpaid at day 29)', 'Payment must be received by credit card on the spot to automatically lift barring.', 7, 'AccountHasBeenAutoBarred'),
					(8, 'D', 'Overdue Notice has been sent (implies the account was still unpaid at day 21)', 'Payment must be taken before being assisted with any other enquiry.', 8, 'AccountSentOverdueNotice'),
					(9, 'C', 'Non-contracted customer (doesn''t have at least one 24 month contract that has not transpired) AND not yet sent Overdue Notice', 'Assist customer and get on DD, if they are not already on it.  Promote contracted product.', 9, 'NotIn24MonthContractAndNotYetSentOverdueNotice'),
					(10, 'B', 'Contracted customer (has at least one 24 month contract that has not transpired) AND not on Direct Debit AND not yet sent Overdue Notice', 'VIP treatment every time', 10, 'In24MonthContractAndNotDirectDebitAndNotYetSentOverdueNotice'),
					(11, 'A', 'Contracted customer (has at least one 24 month contract that has not transpired) AND pays by Direct Debit AND not emailed their bill', 'VIP treatment every time', 11, 'In24MonthContractAndDirectDebitAndNotEmailedInvoice'),
					(12, 'AAA', 'Contracted customer (has at least one 24 month contract that has not transpired) AND pays by Direct Debit AND emailed their bill', 'VIP treatment every time', 12, 'In24MonthContractAndDirectDebitAndEmailedInvoice');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the customer_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM customer_status WHERE id IN (1,2,3,4,5,6,7,8,9,10,11,12)";

		// 3:	Add customer_status_action table
		$strSQL = "CREATE TABLE customer_status_action
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id',
						customer_status_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK customer_status table',
						user_role_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK user_role table',
						description VARCHAR(1000) NOT NULL COMMENT 'description of the required action that the employee must take when of this role/customer_status'
					) ENGINE = innodb COMMENT = 'Defines the action to be taken by a user, for a customer of a given customer status';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create customer_status_action table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS customer_status_action";

		// 4:	Add customer_status_history table
		$strSQL = "CREATE TABLE customer_status_history
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id',
						account_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK Account table',
						invoice_run_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK InvoiceRun table',
						last_updated DATETIME NOT NULL COMMENT 'time at which the status was last calculated',
						customer_status_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK customer_status table'
					) ENGINE = innodb COMMENT = 'Defines most recently calculated customer status for a given invoice run/account';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create customer_status_history table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS customer_status_history";

		// 5:	Add the user_role table
		$strSQL = "CREATE TABLE user_role
					(
						id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY COMMENT 'Unique Id for this user role',
						name VARCHAR(255) NOT NULL COMMENT 'name of the user role',
						description VARCHAR(255) NOT NULL COMMENT 'description of the user role',
						const_name VARCHAR(255) NOT NULL COMMENT 'constant name'
					) ENGINE = innodb COMMENT = 'Defines the various User Roles';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create user_role table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS user_role";

		// 6:	Populate the user_role table
		$strSQL = "INSERT INTO user_role (id, name, description, const_name) VALUES
					(1, 'Flex Admin', 'Flex System Administrator', 'USER_ROLE_FLEX_ADMIN'),
					(2, 'Manager', 'Manager', 'USER_ROLE_MANAGER'),
					(3, 'Team Leader', 'Team Leader', 'USER_ROLE_TEAM_LEADER'),
					(4, 'Customer Service Representitive', 'Customer Service Representitive', 'USER_ROLE_CUSTOMER_SERVICE_REPRESENTITIVE'),
					(5, 'Credit Control Manager', 'Credit Control Manager', 'USER_ROLE_CREDIT_CONTROL_MANAGER'),
					(6, 'Sales', 'Sales', 'USER_ROLE_SALES'),
					(7, 'Admin Manager', 'Admin Manager', 'USER_ROLE_ADMIN_MANAGER'),
					(8, 'Adminion', 'Admin Minion', 'USER_ROLE_ADMINION'),
					(9, 'Accounts', 'Accounts', 'USER_ROLE_ACCOUNTS');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the user_role table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM user_role WHERE id IN (1,2,3,4,5,6,7,8,9)";

		// 7:	Add the user_role_id column to the Employee table (FK into user_role table)
		$strSQL = "ALTER TABLE Employee ADD user_role_id BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK into user_role table, defining the role of the employee' AFTER Archived;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add Employee.user_role_id field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Employee DROP user_role_id;";

	}
	
	function rollback()
	{
		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$qryQuery = new Query(FLEX_DATABASE_CONNECTION_ADMIN);
				if (!$qryQuery->Execute($this->rollbackSQL[$l]))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $qryQuery->Error());
				}
			}
		}
	}
}

?>
