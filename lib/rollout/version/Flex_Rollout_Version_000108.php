<?php

/**
 * Version 108 of database update.
 * This version: -
 *	1:	Remove PaymentType ConfigConstants
 *	2:	Remove PaymentType ConfigConstantGroup
 */

class Flex_Rollout_Version_000108 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Remove PaymentType ConfigConstants
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
		
		// 2:	Remove PaymentType ConfigConstantGroup
		$strSQL = "DELETE FROM ConfigConstantGroup WHERE Name = 'PaymentType'";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove PaymentType ConfigConstantGroup. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "INSERT INTO `ConfigConstantGroup` (`Name`, `Description`, `Type`, `Special`, `Extendable`) VALUES
								('PaymentType', 'Payment Types', 2, 1, 1);";
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