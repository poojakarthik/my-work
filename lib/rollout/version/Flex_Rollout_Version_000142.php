<?php

/**
 * Version 142 of database update.
 * This version: -
 *	1:	Remove the Config Table
 *	2:	Remove the BugReport Table
 *	3:	Remove the BugReportComment Table
 *	4:	Remove the Tip Table
 *	5:	Remove the InvoiceTemp Table
 *	6:	Remove the InvoiceTemp_bk Table
 */

class Flex_Rollout_Version_000142 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Remove the Config Table
		$strSQL =	"DROP TABLE IF EXISTS Config;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the Config Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		// 2:	Remove the BugReport Table
		$strSQL =	"DROP TABLE IF EXISTS BugReport;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the BugReport Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		// 3:	Remove the BugReportComment Table
		$strSQL =	"DROP TABLE IF EXISTS BugReportComment;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the BugReportComment Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		// 4:	Remove the Tip Table
		$strSQL =	"DROP TABLE IF EXISTS Tip;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the Tip Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		// 5:	Remove the InvoiceTemp Table
		$strSQL =	"DROP TABLE IF EXISTS InvoiceTemp;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the InvoiceTemp Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		
		// 6:	Remove the InvoiceTemp_bk Table
		$strSQL =	"DROP TABLE IF EXISTS InvoiceTemp_bk;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the InvoiceTemp_bk Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
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