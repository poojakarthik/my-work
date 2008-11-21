<?php

/**
 * Version 98 of database update.
 * This version: -
 *	1:	Adds the sale.verified_on Field
 */

class Flex_Rollout_Version_000098 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Adds the sale.verified_on Field
		$strSQL = "ALTER TABLE sale " .
					"ADD verified_on DATETIME NOT NULL COMMENT 'The Date and Time at which this Sale was Verified';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the sale.verified_on Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE sale " .
								"DROP verified_on;";
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
