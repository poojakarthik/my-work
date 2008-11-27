<?php

/**
 * Version 105 of database update.
 * This version: -
 *	1:	Add the 'PDCR' ChargeType
 */

class Flex_Rollout_Version_000105 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		//	1:	Add the 'PDCR' ChargeType
		$strSQL = "INSERT INTO ChargeType (ChargeType, Description, Nature, Fixed, automatic_only, Amount, Archived) VALUES " .
					"('PDCR', 'Plan Data Credit in Arrears', 'CR', 0, 1, 0.0, 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the \'PDCR\' ChargeType. ' . $result->getMessage());
		}
		$this->rollbackSQL[] = "DELETE FROM ChargeType WHERE ChargeType = 'PDCR';";
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