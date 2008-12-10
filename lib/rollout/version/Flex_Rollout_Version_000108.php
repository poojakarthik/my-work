<?php

/**
 * Version 108 of database update.
 * This version: -
 *	1:	Add the payment_status Table
 *	2:	Populate the payment_status Table
 *	3:	Add the payment_type Table
 *	4:	Populate the payment_type Table
 *	5:	Rename the 'BPAY Westpac' Carrier to just 'Westpac'
 *	6:	Remove PaymentType ConfigConstants
 *	7:	Remove PaymentType ConfigConstantGroup
 */

class Flex_Rollout_Version_000108 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 6:	Remove PaymentType ConfigConstants
		$strSQL = "DELETE FROM ConfigConstant WHERE ConstantGroup = (SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove PaymentType ConfigConstants. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "INSERT INTO `ConfigConstant` (`ConstantGroup`, `Name`, `Description`, `Value`, `Type`, `Editable`, `Deletable`) VALUES
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_BILLEXPRESS', 'BillExpress', '1', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_BPAY', 'BPay', '2', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_CHEQUE', 'Cheque', '3', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_SECUREPAY', 'SecurePay', '4', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_CREDIT_CARD', 'Credit Card', '5', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_EFT', 'EFT', '6', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_CASH', 'Cash', '7', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_AUSTRAL', 'Austral', '8', 2, 0, 0),
								((SELECT Id FROM ConfigConstantGroup WHERE Name = 'PaymentType'), 'PAYMENT_TYPE_CONTRA', 'Contra', '9', 2, 0, 0);";
		
		// 7:	Remove PaymentType ConfigConstantGroup
		$strSQL = "DELETE FROM ConfigConstantGroup WHERE Name = 'PaymentType'";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove PaymentType ConfigConstantGroup. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "INSERT INTO `ConfigConstantGroup` (`Name`, `Description`, `Type`, `Special`, `Extendable`) VALUES
								('PaymentType', 'Payment Types', 2, 1, 1);";
				
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
			INSERT INTO payment_status (name, description, const_name) VALUES
				('Imported', 'Imported', 'PAYMENT_IMPORTED'),
				('Waiting', 'Waiting', 'PAYMENT_WAITING'),
				('Paying', 'Paying', 'PAYMENT_PAYING'),
				('Finished', 'Finished', 'PAYMENT_FINISHED'),
				('Import Failed', 'Import Failed', 'PAYMENT_BAD_IMPORT'),
				('Processing Failed', 'Processing Failed', 'PAYMENT_BAD_PROCESS'),
				('Normalisation Failed', 'Normalisation Failed', 'PAYMENT_BAD_NORMALISE'),
				('Header', 'File Header (Ignored)', 'PAYMENT_CANT_NORMALISE_HEADER'),
				('Footer', 'File Footer (Ignored)', 'PAYMENT_CANT_NORMALISE_FOOTER'),
				('Invalid', 'Invalid Data', 'PAYMENT_CANT_NORMALISE_INVALID'),
				('Delinquent', 'Delinquent', 'PAYMENT_BAD_OWNER'),
				('Invalid Check Digit', 'Invalid Check Digit', 'PAYMENT_INVALID_CHECK_DIGIT'),
				('Reversed', 'Reversed', 'PAYMENT_REVERSED')";
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
			INSERT INTO payment_type (name, description, const_name) VALUES
				('BillExpress', 'BillExpress', 'PAYMENT_TYPE_BILLEXPRESS'),
				('BPAY', 'BPAY', 'PAYMENT_TYPE_BPAY'),
				('Cheque', 'Cheque', 'PAYMENT_TYPE_CHEQUE'),
				('SecurePay', 'SecurePay', 'PAYMENT_TYPE_SECUREPAY'),
				('Credit Card', 'Credit Card', 'PAYMENT_TYPE_CREDIT_CARD'),
				('EFT', 'EFT', 'PAYMENT_TYPE_EFT'),
				('Cash', 'Cash', 'PAYMENT_TYPE_CASH'),
				('Austral', 'Austral', 'PAYMENT_TYPE_AUSTRAL'),
				('Contra', 'Contra', 'PAYMENT_TYPE_CONTRA'),
				('Bank Transfer', 'Bank Transfer', 'PAYMENT_TYPE_BANK_TRANSFER')";
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