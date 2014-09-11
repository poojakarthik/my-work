<?php

/**
 * Version 199 of database update.
 * This version: -
 *	
 *	1:	Add the Clear Telecoms Carrier
 *
 */

class Flex_Rollout_Version_000199 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the Clear Telecoms Carrier
		$strSQL = "	INSERT INTO	Carrier
						(name, description, const_name, carrier_type)
					VALUES
						('Clear Telecoms'	, 'Clear Telecoms'	, 'CARRIER_CLEAR_TELECOMS'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_TELECOM' LIMIT 1));";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Clear Telecoms Carrier. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	Carrier
									WHERE		const_name = 'CARRIER_CLEAR_TELECOMS';";
		
	}

	function rollback()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		if (count($this->rollbackSQL))
		{
			for ($l = count($this->rollbackSQL) - 1; $l >= 0; $l--)
			{
				$result = $dbAdmin->query($this->rollbackSQL[$l]);
				if (MDB2::isError($result))
				{
					throw new Exception(__CLASS__ . ' Failed to rollback: ' . $this->rollbackSQL[$l] . '. ' . $result->getMessage());
				}
			}
		}
	}
}

?>