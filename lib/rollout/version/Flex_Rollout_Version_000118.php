<?php

/**
 * Version 118 of database update.
 * This version: -
 *	1:	Add the Active Contact and Active Service Telemarketing Withheld Statuses
 */

class Flex_Rollout_Version_000118 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the Active Contact and Active Service Telemarketing Withheld Statuses
		$strSQL = "INSERT INTO telemarketing_fnn_withheld_reason (name, descroption, const_name) VALUES " .
					"('Active Contact', 'Active Contact', 'TELEMARKETING_FNN_WITHHELD_REASON_FLEX_CONTACT'), " .
					"('Active Service', 'Active Service', 'TELEMARKETING_FNN_WITHHELD_REASON_FLEX_SERVICE');";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Active Contact and Active Service Telemarketing Withheld Statuses. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM telemarketing_fnn_withheld_reason WHERE const_name IN ('TELEMARKETING_FNN_WITHHELD_REASON_FLEX_CONTACT', 'TELEMARKETING_FNN_WITHHELD_REASON_FLEX_SERVICE')";
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