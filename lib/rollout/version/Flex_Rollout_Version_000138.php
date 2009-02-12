<?php

/**
 * Version 138 of database update.
 * This version: -
 *	1:	Add the Jayshree IT Consultants Carrier
 */

class Flex_Rollout_Version_000138 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 3:	Add the Jayshree IT Consultants Carrier
		$strSQL = "INSERT INTO Carrier (Name, carrier_type, description, const_name) VALUES " .
					"('Jayshree ITC', (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_SALES_CALL_CENTRE'), 'Jayshree IT Consultants', 'CARRIER_JAYSHREE_ITC')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Jayshree IT Consultants Carrier. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM Carrier WHERE const_name = 'CARRIER_JAYSHREE_ITC';";		
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