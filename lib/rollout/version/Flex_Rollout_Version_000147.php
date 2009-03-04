<?php

/**
 * Version 147 of database update.
 * This version: -
 *
 *	1:	Add the Document Management & Contact List Flex Modules
 */

class Flex_Rollout_Version_000147 extends Flex_Rollout_Version
{
	private $rollbackSQL = array();

	public function rollout()
	{
		$dbAdmin = Data_Source::get(FLEX_DATABASE_CONNECTION_ADMIN);

		// 1:	Add the Document Management & Contact List Flex Modules
		$strSQL = "	INSERT INTO flex_module (id, name, description, const_name, active) VALUES" .
				"	(6, 'Document Management', 'Document Management Module', 'FLEX_MODULE_DOCUMENT_MANAGEMENT', 0), " .
				"	(7, 'Contact List', 'Internal Contact List Module', 'FLEX_MODULE_CONTACT_LIST', 0);";
		$result = $dbAdmin->query($strSQL);
		if (PEAR::isError($result))
		{
			throw new Exception(__CLASS__ . ' Failed to add the Document Management & Contact List Flex Modules. ' . $result->getMessage() . " (DB Error: " . $result->getUserInfo() . ")");
		}
		$this->rollbackSQL[] =	"DELETE FROM flex_module " .
								"WHERE const_name IN ('FLEX_MODULE_DOCUMENT_MANAGEMENT', 'FLEX_MODULE_CONTACT_LIST');";
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