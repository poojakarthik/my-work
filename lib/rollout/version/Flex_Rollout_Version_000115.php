<?php

/**
 * Version 115 of database update.
 * This version: -
 *	1:	Add the MODULE_TYPE_TELEMARKETING Carrier Module Type
 *	2:	Add the Telecommunications Authority Carrier Type
 *	3:	Add the ACMA Carrier
 */

class Flex_Rollout_Version_000115 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Add the MODULE_TYPE_TELEMARKETING Carrier Module Type
		$strSQL = "INSERT INTO carrier_module_type (name, description, const_name) VALUES " .
					"('Telemarketing', 'Telemarketing', 'MODULE_TYPE_TELEMARKETING')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the MODULE_TYPE_TELEMARKETING Carrier Module Type. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM carrier_module_type WHERE const_name = 'MODULE_TYPE_TELEMARKETING';";
		
		// 2:	Add the Telecommunications Authority Carrier Type
		$strSQL = "INSERT INTO carrier_type (name, description, const_name) VALUES " .
					"('Telecom Authority', 'Telecommunications Authority', 'CARRIER_TYPE_TELECOM_AUTHORITY')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Telecommunications Authority Carrier Type. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM carrier_type WHERE const_name = 'CARRIER_TYPE_TELECOM_AUTHORITY';";
		
		// 3:	Add the ACMA Carrier
		$strSQL = "INSERT INTO Carrier (Name, carrier_type, description, const_name) VALUES " .
					"('ACMA', (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_TELECOM_AUTHORITY'), 'Australian Communications and Media Authority', 'CARRIER_ACMA')";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the ACMA Carrier. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM Carrier WHERE const_name = 'CARRIER_ACMA';";		
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