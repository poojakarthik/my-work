<?php

/**
 * Version 109 of database update.
 * This version: -
 *	1:	Add the payment_status Table
 *	2:	Populate the payment_status Table
 *	3:	Add the payment_type Table
 *	4:	Populate the payment_type Table
 *	5:	Rename the 'BPAY Westpac' Carrier to just 'Westpac'
 */

class Flex_Rollout_Version_000109 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
				
		// 1:	Add the payment_status Table
		$strSQL = "CREATE TABLE payment_status " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id', " .
						"name VARCHAR(255) NOT NULL COMMENT 'Name of the Payment Status', " .
						"description VARCHAR(1024) NOT NULL COMMENT 'Description of the Payment Status', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Payment Status' " .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the payment_status Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS payment_status;";
		
		// 2:	Populate the payment_status Table
		$strSQL = "
			INSERT INTO payment_status (id, name, description, const_name) VALUES
				(100, 'Imported', 'Imported', 'PAYMENT_IMPORTED'),
				(101, 'Waiting', 'Waiting', 'PAYMENT_WAITING'),
				(103, 'Paying', 'Paying', 'PAYMENT_PAYING'),
				(150, 'Finished', 'Finished', 'PAYMENT_FINISHED'),
				(200, 'Import Failed', 'Import Failed', 'PAYMENT_BAD_IMPORT'),
				(201, 'Processing Failed', 'Processing Failed', 'PAYMENT_BAD_PROCESS'),
				(202, 'Normalisation Failed', 'Normalisation Failed', 'PAYMENT_BAD_NORMALISE'),
				(203, 'Header', 'File Header (Ignored)', 'PAYMENT_CANT_NORMALISE_HEADER'),
				(204, 'Footer', 'File Footer (Ignored)', 'PAYMENT_CANT_NORMALISE_FOOTER'),
				(205, 'Invalid', 'Invalid Data', 'PAYMENT_CANT_NORMALISE_INVALID'),
				(206, 'Delinquent', 'Delinquent', 'PAYMENT_BAD_OWNER'),
				(207, 'Invalid Check Digit', 'Invalid Check Digit', 'PAYMENT_INVALID_CHECK_DIGIT'),
				(250, 'Reversed', 'Reversed', 'PAYMENT_REVERSED')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the payment_status Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE payment_status;";
				
		// 3:	Add the payment_type Table
		$strSQL = "CREATE TABLE payment_type " .
					"(" .
						"id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Unique Id', " .
						"name VARCHAR(255) NOT NULL COMMENT 'Name of the Payment Status', " .
						"description VARCHAR(1024) NOT NULL COMMENT 'Description of the Payment Status', " .
						"const_name VARCHAR(512) NOT NULL COMMENT 'Constant Name for the Payment Status' " .
					") ENGINE = innodb;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the payment_type Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DROP TABLE IF EXISTS payment_type;";
		
		// 4:	Populate the payment_type Table
		$strSQL = "
			INSERT INTO payment_type (id, name, description, const_name) VALUES
				(1, 'BillExpress', 'BillExpress', 'PAYMENT_TYPE_BILLEXPRESS'),
				(2, 'BPAY', 'BPAY', 'PAYMENT_TYPE_BPAY'),
				(3, 'Cheque', 'Cheque', 'PAYMENT_TYPE_CHEQUE'),
				(4, 'SecurePay', 'SecurePay', 'PAYMENT_TYPE_SECUREPAY'),
				(5, 'Credit Card', 'Credit Card', 'PAYMENT_TYPE_CREDIT_CARD'),
				(6, 'EFT', 'EFT', 'PAYMENT_TYPE_EFT'),
				(7, 'Cash', 'Cash', 'PAYMENT_TYPE_CASH'),
				(8, 'Austral', 'Austral', 'PAYMENT_TYPE_AUSTRAL'),
				(9, 'Contra', 'Contra', 'PAYMENT_TYPE_CONTRA'),
				(10, 'Bank Transfer', 'Bank Transfer', 'PAYMENT_TYPE_BANK_TRANSFER')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to populate the payment_type Table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "TRUNCATE TABLE payment_type;";
		
		// 5:	Rename the 'BPAY Westpac' Carrier to just 'Westpac'
		$strSQL = "UPDATE Carrier SET Name = 'Westpac', description = 'Westpac', const_name = 'CARRIER_WESTPAC' WHERE const_name = 'CARRIER_BPAY_WESTPAC'";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to rename the \'BPAY Westpac\' Carrier to just \'Westpac\'. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "UPDATE Carrier SET Name = 'BPAY Westpac', description = 'BPay Westpac', const_name = 'CARRIER_BPAY_WESTPAC' WHERE const_name = 'CARRIER_WESTPAC'";
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