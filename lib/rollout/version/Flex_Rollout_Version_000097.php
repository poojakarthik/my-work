<?php

/**
 * Version 97 of database update.
 * This version: -
 *	1:	Makes the sale.id Column AUTO_INCREMENT
 *	2:	Makes the sale_item.id Column AUTO_INCREMENT
 */

class Flex_Rollout_Version_000097 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Makes the sale.id Column AUTO_INCREMENT
		$strSQL = "ALTER TABLE sale " .
					"MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to make the sale.id Column AUTO_INCREMENT. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE sale " .
								"MODIFY id BIGINT(20) UNSIGNED NOT NULL;";
		
		// 2:	Makes the sale_item.id Column AUTO_INCREMENT
		$strSQL = "ALTER TABLE sale_item " .
					"MODIFY id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to make the sale_item.id Column AUTO_INCREMENT. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE sale_item " .
								"MODIFY id BIGINT(20) UNSIGNED NOT NULL;";
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
