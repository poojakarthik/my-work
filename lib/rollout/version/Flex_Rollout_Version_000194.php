<?php

/**
 * Version 194 of database update.
 * This version: -
 *	
 *	1:	Add Bank Details to the Customer Group table
 *
 */

class Flex_Rollout_Version_000194 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add Bank Details to the Customer Group table
		$strSQL = "	ALTER TABLE	CustomerGroup
					ADD			bank_account_name	VARCHAR(1024)	NULL	COMMENT 'Bank Account Name',
					ADD			bank_bsb			CHAR(6)			NULL	COMMENT 'Bank BSB',
					ADD			bank_account_number	VARCHAR(20)		NULL	COMMENT 'Bank Account Number';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add Bank Details to the Customer Group table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	ALTER TABLE	CustomerGroup
									DROP		bank_account_name,
									DROP		bank_bsb,
									DROP		bank_account_number;";
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