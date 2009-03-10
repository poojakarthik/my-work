<?php

/**
 * Version 153 of database update.
 * This version: -
 *
 *	1:	Drop the MasterState table
 *	2:	Drop the MasterInstructions table
 *	3:	Drop the InvoiceOutput table
 *	4:	Drop the InvoiceOutputArchive table
 *	5:	Drop the Request table
 *	6:	Drop the ErrorLog table
 */

class Flex_Rollout_Version_000153 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Drop the MasterState table
		$strSQL =	"DROP TABLE IF EXISTS MasterState;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the Master Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 2:	Drop the MasterInstructions table
		$strSQL =	"DROP TABLE IF EXISTS MasterInstructions;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the MasterInstructions Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 3:	Drop the InvoiceOutput table
		$strSQL =	"DROP TABLE IF EXISTS InvoiceOutput;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the InvoiceOutput Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 4:	Drop the InvoiceOutputArchive table
		$strSQL =	"DROP TABLE IF EXISTS InvoiceOutputArchive;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the InvoiceOutputArchive Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 5:	Drop the Request table
		$strSQL =	"DROP TABLE IF EXISTS Request;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the Request Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}

		// 6:	Drop the ErrorLog table
		$strSQL =	"DROP TABLE IF EXISTS ErrorLog;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to drop the ErrorLog Table. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
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