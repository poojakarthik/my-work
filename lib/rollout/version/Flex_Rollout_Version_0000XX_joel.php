<?php

/**
 * Version XX (ex-ex) of database update.
 * This version: -
 *	1:	Adds Account.tio_reference_number field
 *	2:	Adds customer_status table
 *	3:	Populates the customer_status table
 *	4:	Adds customer_status_action_required table
 *	5:	Adds account_customer_status_history table
 */

class Flex_Rollout_Version_0000XX extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$qryQuery	= new Query(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbaDB		= DataAccess::getDataAccess(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add Account.tio_reference_number field
		$strSQL = "ALTER TABLE `Account` ADD `tio_reference_number` VARCHAR(150) NULL COMMENT 'reference number when dealing with the T.I.O.' AFTER `automatic_barring_datetime`;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to add Account.tio_reference_number field. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "ALTER TABLE Account DROP tio_reference_number;";
		
		// 2:	Add customer_status table
		$strSQL = "CREATE TABLE customer_status
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for this Customer Status',
						name VARCHAR(50) NOT NULL COMMENT 'The status name',
						description VARCHAR(1000) NOT NULL COMMENT 'A description of the criteria required to satisfy this status',
						default_action_description VARCHAR(1000) NOT NULL COMMENT 'Description of what credit control personel should do with customer',
						precedence INT(10) NOT NULL COMMENT 'Order in which the statuses are tested. Customer status with precedence 1 will be tested before customer status with precidence 2.  Customer is assigned the first status which it satisfies',
						test_function VARCHAR(255) NOT NULL COMMENT 'Name of the function which tests for this Customer Status'
					) ENGINE = innodb COMMENT = 'Defines the various Customer Statuses and how they are tested';";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create customer_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS customer_status";
		
		// 3:	Populate the customer_status table
		$strSQL = "INSERT INTO customer_status (id, name, description, default_action_description, precedence, test_function) VALUES
					(1, 'L', 'Lost Customer (all services lost)', 'Collect any outstanding monies and attempt win back.', 1, 'LostCustomer'),
					(2, 'J', 'T.I.O issue pending', 'This account cannot be credit managed or barred.  Handled by Customer Service Manager only.', 2, 'AccountWithTIO'),
					(3, 'I', 'Account has dispute with invoice', 'Collect undisputed amount.  Dispute handled by CSM or TL.', 3, 'AccountInDispute'),
					(4, 'H', 'Account has gone to Austral', 'Customer to be handled by Credit Control department.', 4, 'AccountWithAustral'),
					(5, 'G', 'Account is ready for Austral but has not gone there yet', 'Customer to be handled by Credit Control department.', 5, 'AccountReadyForAustral'),
					(6, 'F', 'Final Demand has been sent (implies the account was still unpaid at day 36 and TDC has been applied)', 'Incoming call from customer must be redirected to Credit Control department.', 6, 'AccountSentFinalDemand'),
					(7, 'E', 'Account has been auto barred (implies the account was still unpaid at day 29)', 'Payment must be received by credit card on the spot to automatically lift barring.', 7, 'AccountHasBeenAutoBarred'),
					(8, 'D', 'Overdue Notice has been sent (implies the account was still unpaid at day 21)', 'Payment must be taken before being assisted with any other enquiry.', 8, 'AccountSentOverdueNotice'),
					(9, 'C', 'Non-contracted customer (doesn''t have at least one 24 month contract that has not transpired) AND not yet sent Overdue Notice', 'Assist customer and get on DD, if they are not already on it.  Promote contracted product.', 9, 'NonContractedAndNotYetSentOverdueNotice'),
					(10, 'B', 'Contracted customer (has at least one 24 month contract that has not transpired) AND not on Direct Debit AND not yet sent Overdue Notice', 'VIP treatment every time', 10, 'ContractedAndNotDirectDebitAndNotYetSentOverdueNotice'),
					(11, 'A', 'Contracted customer (has at least one 24 month contract that has not transpired) AND pays by Direct Debit AND not emailed their bill', 'VIP treatment every time', 11, 'ContractedAndDirectDebitAndNotEmailedInvoice'),
					(12, 'AAA', 'Contracted customer (has at least one 24 month contract that has not transpired) AND pays by Direct Debit AND emailed their bill', 'VIP treatment every time', 12, 'ContractedAndDirectDebitAndEmailedInvoice');";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the customer_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DELETE FROM customer_status WHERE id IN (1,2,3,4,5,6,7,8,9,10,11,12)";

		// 4:	Add customer_status_action_required table
		$strSQL = "CREATE TABLE customer_status
					(
						id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id for this Customer Status',
						name VARCHAR(50) NOT NULL COMMENT 'The status name',
						description VARCHAR(1000) NOT NULL COMMENT 'A description of the criteria required to satisfy this status',
						default_action_required_description VARCHAR(1000) NOT NULL COMMENT 'Description of what credit control personel should do with customer',
						precedence INT(10) NOT NULL COMMENT 'Order in which the statuses are tested. customer status with precedence 1 will be tested before customer status with precidence 2.  Customer is assigned the first status which it satisfies',
						test_function VARCHAR(255) NOT NULL COMMENT 'Name of the function which tests for this Customer Status'
					) ENGINE = innodb;";
		if (!$qryQuery->Execute($strSQL))
		{
			throw new Exception(__CLASS__ . ' Failed to create customer_status table. ' . $qryQuery->Error());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS customer_status";

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
