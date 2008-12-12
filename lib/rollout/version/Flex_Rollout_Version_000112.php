<?php

/**
 * Version 112 of database update.
 * This version: -
 *	1:	Add dealer.carrier_id Field
 */

class Flex_Rollout_Version_000112 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add dealer.carrier_id Field
		$strSQL = "ALTER TABLE dealer ADD carrier_id BIGINT(20) UNSIGNED NULL COMMENT '(FK) Sale Call Centre Carrier that this Dealer belongs to';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add dealer.carrier_id Field. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "ALTER TABLE dealer DROP carrier_id;";
	}
	
	function commit()
	{
		$this->outputMessage("NOTE: If there were entries in the 'dealer' table before this Rollout, you will need to manually set the dealer.carrier_id field for each record.");
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