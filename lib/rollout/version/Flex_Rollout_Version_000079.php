<?php


/**
 * Version 79 of database update.
 * This version: -
 *	1:	Add vip flag field to Account table
 */

class Flex_Rollout_Version_000079 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add vip flag field to Account table
		$strSQL = "	ALTER TABLE Account ADD vip TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT 'VIP status (1 = VIP, 0 = Non-VIP)'; ";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add vip flag field to Account table. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE Account DROP vip;";

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
