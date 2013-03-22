<?php

/**
 * Version 200 of database update.
 * This version: -
 *	
 *	1:	Add the Telstra Carrier
 *	2:	Add the LinxOnline Daily Event and Monthly Invoice Resource Types
 *
 */

class Flex_Rollout_Version_000200 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		$dbAdmin->setFetchMode(MDB2_FETCHMODE_ASSOC);
		
		//	1:	Add the Telstra Carrier
		$strSQL = "	INSERT INTO	Carrier
						(name, description, const_name, carrier_type)
					VALUES
						('Telstra'	, 'Telstra'	, 'CARRIER_TELSTRA'	, (SELECT id FROM carrier_type WHERE const_name = 'CARRIER_TYPE_TELECOM' LIMIT 1));";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Telstra Carrier. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	Carrier
									WHERE		const_name = 'CARRIER_TELSTRA';";
		
		//	2:	Add the LinxOnline Daily Event and Monthly Invoice Resource Types
		$strSQL = "	INSERT INTO	resource_type
						(name	, description	, const_name	, resource_type_nature)
					VALUES
						('LinxOnline Daily Event File'		, 'LinxOnline Daily Event File'		, 'RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE'		, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1)),
						('LinxOnline Monthly Invoice File'	, 'LinxOnline Monthly Invoice File'	, 'RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE'	, (SELECT id FROM resource_type_nature WHERE const_name = 'RESOURCE_TYPE_NATURE_IMPORT_FILE' LIMIT 1));";
		$result = $dbAdmin->query($strSQL);
		if (MDB2::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the LinxOnline Daily Event and Monthly Invoice Resource Types. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"	DELETE FROM	resource_type
									WHERE		const_name IN ('RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_DAILY_EVENT_FILE', 'RESOURCE_TYPE_FILE_IMPORT_CDR_LINX_MONTHLY_INVOICE_FILE');";
		
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