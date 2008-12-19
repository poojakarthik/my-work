<?php

/**
 * Version 116 of database update.
 * This version: -
 *	1:	Remove the MODULE_TYPE_TELEMARKETING Carrier Module Type
 *	2:	Add the TELEMARKETING_PROPOSED_IMPORT, TELEMARKETING_DNCR_EXPORT, TELEMARKETING_DNCR_IMPORT, TELEMARKETING_PERMITTED_EXPORT, TELEMARKETING_DIALLER_IMPORT Carrier Module Types
 */

class Flex_Rollout_Version_000115 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();
	
	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);
		
		// 1:	Remove the MODULE_TYPE_TELEMARKETING Carrier Module Type
		$strSQL = "DELETE FROM carrier_module_type WHERE const_name = 'MODULE_TYPE_TELEMARKETING';";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to remove the MODULE_TYPE_TELEMARKETING Carrier Module Type. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "INSERT INTO carrier_module_type (name, description, const_name) VALUES " .
								"('Telemarketing', 'Telemarketing', 'MODULE_TYPE_TELEMARKETING')";
								
		// 2:	Add the TELEMARKETING_PROPOSED_IMPORT, TELEMARKETING_DNCR_EXPORT, TELEMARKETING_DNCR_IMPORT, TELEMARKETING_PERMITTED_EXPORT, TELEMARKETING_DIALLER_IMPORT Carrier Module Types
		$strSQL = "INSERT INTO carrier_module_type (name, description, const_name) VALUES " .
					"('Telemarketing Proposed FNN Files'	, 'Telemarketing Proposed FNN Files'	, 'MODULE_TYPE_TELEMARKETING_PROPOSED_IMPORT')" . 
					"('Telemarketing DNCR Request Files'	, 'Telemarketing DNCR Request Files'	, 'MODULE_TYPE_TELEMARKETING_DNCR_EXPORT')" . 
					"('Telemarketing DNCR Response Files'	, 'Telemarketing DNCR Response Files'	, 'MODULE_TYPE_TELEMARKETING_DNCR_IMPORT')" . 
					"('Telemarketing Permitted FNN Files'	, 'Telemarketing Permitted FNN Files'	, 'MODULE_TYPE_TELEMARKETING_PERMITTED_EXPORT')" . 
					"('Telemarketing Dialler Report Files'	, 'Telemarketing Dialler Report Files'	, 'MODULE_TYPE_TELEMARKETING_DIALLER_IMPORT)";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the TELEMARKETING_PROPOSED_IMPORT, TELEMARKETING_DNCR_EXPORT, TELEMARKETING_DNCR_IMPORT, TELEMARKETING_PERMITTED_EXPORT, TELEMARKETING_DIALLER_IMPORT Carrier Module Types. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] = "DELETE FROM carrier_module_type WHERE const_name IN ('MODULE_TYPE_TELEMARKETING_PROPOSED_IMPORT', 'MODULE_TYPE_TELEMARKETING_DNCR_EXPORT', 'MODULE_TYPE_TELEMARKETING_DNCR_IMPORT', 'MODULE_TYPE_TELEMARKETING_PERMITTED_EXPORT', 'MODULE_TYPE_TELEMARKETING_DIALLER_IMPORT');";
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